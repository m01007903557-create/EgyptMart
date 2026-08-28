<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

$configFile = __DIR__ . '/ai-search-config.php';
if (is_file($configFile)) {
    require_once $configFile;
}
require_once __DIR__ . '/ai-search-lib.php';

$requestText = trim((string)($_POST['request_text'] ?? $_POST['keywords'] ?? ''));
$requestedType = trim((string)($_POST['rctyp'] ?? 'Products'));
$allowedTypes = array('Products', 'Suppliers', 'buy_lead', 'tender');

if (!in_array($requestedType, $allowedTypes, true)) {
    $requestedType = 'Products';
}

if ($requestText === '') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Please enter your request.',
        'redirect_url' => ''
    ));
    exit;
}

$intent = ai_search_extract_intent($requestText, $requestedType);

function ai_search_optional_db()
{
    global $con;
    if (isset($con) && $con instanceof mysqli) {
        return $con;
    }

    $connectFile = __DIR__ . '/lib/connect.php';
    if (is_file($connectFile)) {
        @require_once $connectFile;
    }

    return (isset($con) && $con instanceof mysqli) ? $con : null;
}

function ai_search_company_terms($intent)
{
    $terms = array();
    foreach (array('supplier_company', 'keywords') as $key) {
        $value = isset($intent[$key]) ? ai_search_clean((string)$intent[$key]) : '';
        if ($value === '') {
            continue;
        }
        $value = preg_replace('/\b(show me|find|search|supplier|suppliers|company|companies|products?|catalog|for|of|from|in)\b/iu', ' ', $value);
        $value = preg_replace('/\b(frozen|vegetables|foods?|services?)\b/iu', ' ', $value);
        $value = preg_replace('/(?:شركة|شركات|منتجات|مورد|موردين|خضروات|مجمدة|ل|من|في)/u', ' ', $value);
        $value = ai_search_clean($value);
        if ($value !== '') {
            $terms[] = $value;
        }
    }

    $raw = isset($intent['raw_text']) ? (string)$intent['raw_text'] : '';
    if (preg_match('/\bshow\s+me\s+(.+?)\s+(?:company|supplier)\b/iu', $raw, $match)) {
        $terms[] = ai_search_trim_entity($match[1]);
    }
    if (preg_match('/(?:شركة|مورد)\s+([\x{0600}-\x{06FF}a-z0-9 .&-]{2,60})/iu', $raw, $match)) {
        $terms[] = ai_search_trim_entity($match[1]);
    }

    $clean = array();
    foreach ($terms as $term) {
        $term = ai_search_clean($term);
        if ($term !== '' && !in_array($term, $clean, true)) {
            $clean[] = $term;
            $lowerTerm = function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term);
            if (preg_match('/\baga\b/iu', $lowerTerm)) {
                foreach (array('AGA Company', 'AGA', 'أجا', 'اجا') as $alias) {
                    if (!in_array($alias, $clean, true)) {
                        $clean[] = $alias;
                    }
                }
            }
            if (preg_match('/(?:أجا|اجا)/u', $term)) {
                foreach (array('أجا', 'اجا', 'AGA', 'AGA Company') as $alias) {
                    if (!in_array($alias, $clean, true)) {
                        $clean[] = $alias;
                    }
                }
            }
        }
    }
    return $clean;
}

function ai_search_known_supplier_profile($intent)
{
    $text = ai_search_clean(
        (string)($intent['raw_text'] ?? '') . ' ' .
        (string)($intent['supplier_company'] ?? '') . ' ' .
        (string)($intent['keywords'] ?? '')
    );
    if (preg_match('/\baga\b|(?:أجا|اجا)/iu', $text)) {
        return 'catcompany.php?token=82287f24d240521d99071c93af3917215ef7';
    }
    return '';
}

function ai_search_supplier_profile_url($intent)
{
    $intentType = isset($intent['intent_type']) ? (string)$intent['intent_type'] : '';
    $rctyp = isset($intent['rctyp']) ? (string)$intent['rctyp'] : '';
    if ($intentType !== 'supplier_search' && $rctyp !== 'Suppliers') {
        return '';
    }

    $knownUrl = ai_search_known_supplier_profile($intent);
    if ($knownUrl !== '') {
        return $knownUrl;
    }

    $terms = ai_search_company_terms($intent);
    if (empty($terms)) {
        return '';
    }

    $db = ai_search_optional_db();
    if (!$db) {
        return '';
    }

    foreach ($terms as $term) {
        $like = '%' . $term . '%';
        $exact = $term;

        $sql = "SELECT bnsprof_id, bnsprof_compname FROM business_profile WHERE LOWER(TRIM(bnsprof_compname)) = LOWER(TRIM(?)) LIMIT 1";
        $stmt = mysqli_prepare($db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $exact);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $profileId, $profileName);
            $row = mysqli_stmt_fetch($stmt) ? array('bnsprof_id' => $profileId, 'bnsprof_compname' => $profileName) : null;
            mysqli_stmt_close($stmt);
            if ($row && !empty($row['bnsprof_id'])) {
                return 'catcompany.php?token=' . md5((string)$row['bnsprof_id']);
            }
        }

        $sql = "SELECT bnsprof_id, bnsprof_compname FROM business_profile WHERE bnsprof_compname LIKE ? ORDER BY CASE WHEN LOWER(bnsprof_compname) LIKE LOWER(?) THEN 0 ELSE 1 END, bnsprof_compname ASC LIMIT 1";
        $stmt = mysqli_prepare($db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $like, $term . '%');
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $profileId, $profileName);
            $row = mysqli_stmt_fetch($stmt) ? array('bnsprof_id' => $profileId, 'bnsprof_compname' => $profileName) : null;
            mysqli_stmt_close($stmt);
            if ($row && !empty($row['bnsprof_id'])) {
                return 'catcompany.php?token=' . md5((string)$row['bnsprof_id']);
            }
        }
    }

    return '';
}

$supplierUrl = ai_search_supplier_profile_url($intent);
if ($supplierUrl !== '') {
    $productIntent = $intent;
    $productIntent['rctyp'] = 'Products';
    $productIntent['entity_type'] = 'Products';
    echo json_encode(array(
        'success' => true,
        'redirect_url' => $supplierUrl,
        'products_url' => ai_search_build_url($productIntent),
        'suppliers_url' => $supplierUrl,
        'source' => 'direct_database',
        'parameters' => $intent
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$redirectUrl = ai_search_build_url($intent);
$productIntent = $intent;
$productIntent['rctyp'] = 'Products';
$productIntent['entity_type'] = 'Products';
$supplierIntent = $intent;
$supplierIntent['rctyp'] = 'Suppliers';
$supplierIntent['entity_type'] = 'Suppliers';

echo json_encode(array(
    'success' => true,
    'redirect_url' => $redirectUrl,
    'products_url' => ai_search_build_url($productIntent),
    'suppliers_url' => ai_search_build_url($supplierIntent),
    'source' => 'direct_database',
    'parameters' => $intent
), JSON_UNESCAPED_UNICODE);
