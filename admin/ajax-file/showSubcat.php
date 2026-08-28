<?php
/**
 * File: showSubcat.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: جلب التصنيفات الفرعية لتصنيف رئيسي محدد
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "../../common.php";

/**
 * Validate and sanitize category ID
 * 
 * @param mixed $id Raw category ID from POST
 * @return int Validated category ID
 * @throws InvalidArgumentException
 */
function validateCategoryId($id): int {
    if (!isset($id)) {
        throw new InvalidArgumentException('Category ID is required');
    }
    
    $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
    if ($cleanId === false || $cleanId <= 0) {
        throw new InvalidArgumentException('Invalid category ID');
    }
    
    return $cleanId;
}

/**
 * Fetch subcategories from database
 * 
 * @param mysqli $db Database connection
 * @param int $parentId Parent category ID
 * @return array List of subcategories
 * @throws RuntimeException
 */
function fetchSubcategories(mysqli $db, int $parentId): array {
    $sql = "SELECT pc_id, pc_name 
            FROM product_category 
            WHERE pc_parent_id = ? 
            AND pc_parent_id != 0 
            AND pc_status = 1 
            ORDER BY pc_order, pc_name";
    
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($db));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $parentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'id' => (int)$row['pc_id'],
            'name' => htmlspecialchars($row['pc_name'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    mysqli_stmt_close($stmt);
    return $categories;
}

// Main execution
try {
    // Validate input
    $parentId = validateCategoryId($_POST['id'] ?? null);
    
    // Fetch subcategories
    $subcategories = fetchSubcategories($con, $parentId);
    
    // Output options
    ?>
    <option value="">-- اختر التصنيف الفرعي --</option>
    <?php foreach ($subcategories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>">
            <?php echo $cat['name']; ?>
        </option>
    <?php endforeach; ?>
    
    <?php
} catch (InvalidArgumentException $e) {
    error_log("Validation error: " . $e->getMessage());
    ?>
    <option value="">خطأ في البيانات</option>
    <?php
} catch (RuntimeException $e) {
    error_log("Database error: " . $e->getMessage());
    ?>
    <option value="">خطأ في قاعدة البيانات</option>
    <?php
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>