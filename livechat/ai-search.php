<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

$configFile = __DIR__ . '/ai-search-config.php';
if (is_file($configFile)) {
    require_once $configFile;
}

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

$webhookUrl = '';
if (defined('MAKE_AI_SEARCH_WEBHOOK_URL')) {
    $webhookUrl = (string)MAKE_AI_SEARCH_WEBHOOK_URL;
}
if ($webhookUrl === '') {
    $webhookUrl = (string)getenv('MAKE_AI_SEARCH_WEBHOOK_URL');
}

$params = array(
    'keywords' => $requestText,
    'rctyp' => $requestedType,
    'search_mode' => 'scenario'
);

function extract_search_payload($data)
{
    if (!is_array($data)) {
        return array();
    }

    $directKeys = array('keywords', 'keyword', 'product', 'product_name', 'query', 'search', 'redirect_url', 'search_url', 'rctyp', 'type');
    foreach ($directKeys as $key) {
        if (array_key_exists($key, $data)) {
            return $data;
        }
    }

    $textKeys = array('result', 'response', 'text', 'content', 'message', 'output');
    foreach ($textKeys as $key) {
        if (!empty($data[$key]) && is_scalar($data[$key])) {
            $text = trim((string)$data[$key]);
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $decoded = json_decode($matches[0], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
    }

    foreach ($data as $value) {
        if (is_array($value)) {
            $nested = extract_search_payload($value);
            if (!empty($nested)) {
                return $nested;
            }
        }
    }

    return array();
}

function fallback_search_keywords($text)
{
    $clean = trim(preg_replace('/\s+/u', ' ', (string)$text));
    if ($clean === '') {
        return '';
    }

    if (preg_match('/\bbulk\s+([a-z][a-z0-9-]*)/iu', $clean, $matches)) {
        return trim($matches[1]);
    }

    if (preg_match('/\b(?:buy|need|want|order|purchase|source|looking for)\b\s+(?:a|an|the|large|small|big|quantity|amount|of|some|many|bulk|best|supplier|from|with|fast|delivery|here|\s)*/iu', $clean, $matches, PREG_OFFSET_CAPTURE)) {
        $start = $matches[0][1] + strlen($matches[0][0]);
        $candidate = substr($clean, $start);
    } else {
        $candidate = $clean;
    }

    $candidate = preg_replace('/\b(?:from|with|for|near|in|at|by|best|supplier|suppliers|factory|manufacturer|delivery|fast|quantity|large|small|bulk|buy|need|want|order|purchase|source|looking|here|egypt|saudi|uae|china|india)\b/iu', ' ', $candidate);
    $candidate = preg_replace('/[^a-z0-9\x{0600}-\x{06FF} ]+/iu', ' ', $candidate);
    $words = array_values(array_filter(preg_split('/\s+/u', trim($candidate))));
    $keywords = trim(implode(' ', array_slice($words, 0, 3)));

    return $keywords !== '' ? $keywords : $clean;
}

$webhookData = array();
if ($webhookUrl !== '') {
    $baseUrl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'egyptmart.shop');
    $payload = array(
        'request_text' => $requestText,
        'rctyp' => $requestedType,
        'source' => 'egyptmart_search',
        'page_url' => (string)($_POST['page_url'] ?? ''),
        'results_url' => $baseUrl . '/search.php',
        'country' => (string)($_COOKIE['loc_id'] ?? ''),
        'language' => 'ar'
    );

    $response = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_TIMEOUT, 14);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payload),
                'timeout' => 14
            )
        ));
        $response = @file_get_contents($webhookUrl, false, $context);
    }

    if (is_string($response) && trim($response) !== '') {
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            $webhookData = extract_search_payload($decoded);
        } elseif (preg_match('/\{.*\}/s', $response, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                $webhookData = extract_search_payload($decoded);
            }
        }
    }
}

if (!empty($webhookData)) {
    if (!empty($webhookData['redirect_url']) || !empty($webhookData['search_url'])) {
        $url = (string)($webhookData['redirect_url'] ?? $webhookData['search_url']);
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0 || strpos($url, 'search.php') === 0) {
            echo json_encode(array('success' => true, 'redirect_url' => $url, 'source' => 'webhook'));
            exit;
        }
    }

    $keywordKeys = array('keywords', 'keyword', 'product', 'product_name', 'query', 'search');
    foreach ($keywordKeys as $key) {
        if (!empty($webhookData[$key]) && is_scalar($webhookData[$key])) {
            $params['keywords'] = trim((string)$webhookData[$key]);
            break;
        }
    }

    if (!empty($webhookData['rctyp']) && in_array((string)$webhookData['rctyp'], $allowedTypes, true)) {
        $params['rctyp'] = (string)$webhookData['rctyp'];
    } elseif (!empty($webhookData['type']) && in_array((string)$webhookData['type'], $allowedTypes, true)) {
        $params['rctyp'] = (string)$webhookData['type'];
    }

    if (!empty($webhookData['category']) && is_scalar($webhookData['category'])) {
        $params['category'] = trim((string)$webhookData['category']);
    }
    if (!empty($webhookData['country']) && is_scalar($webhookData['country'])) {
        $params['country'] = trim((string)$webhookData['country']);
    }
    if (!empty($webhookData['quantity']) && is_scalar($webhookData['quantity'])) {
        $params['quantity'] = trim((string)$webhookData['quantity']);
    }
}

if (empty($webhookData)) {
    $lower = function_exists('mb_strtolower') ? mb_strtolower($requestText, 'UTF-8') : strtolower($requestText);
    $fallbackKeywords = fallback_search_keywords($requestText);
    if ($fallbackKeywords !== '') {
        $params['keywords'] = $fallbackKeywords;
    }
    if (preg_match('/(find suppliers|search suppliers|supplier companies|supplier list|factory list|manufacturer list|موردين فقط|بحث موردين|شركات فقط)/u', $lower)) {
        $params['rctyp'] = 'Suppliers';
    }
    if (preg_match('/(buy lead|buyer request|buyers request|quotation|quote|rfq|طلب شراء|طلبات شراء|تسعير)/u', $lower)) {
        $params['rctyp'] = 'buy_lead';
    }
    if (preg_match('/(tender|auction|مناقصة|مناقصات|مزايدة|مزادات)/u', $lower)) {
        $params['rctyp'] = 'tender';
    }
}

$redirectUrl = 'search.php?' . http_build_query($params);

echo json_encode(array(
    'success' => true,
    'redirect_url' => $redirectUrl,
    'source' => !empty($webhookData) ? 'webhook' : 'local'
));
