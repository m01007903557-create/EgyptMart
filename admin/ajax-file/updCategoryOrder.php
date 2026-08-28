<?php
/**
 * Update Product Category Order
 * 
 * This script handles updating the display order of product categories
 * 
 * PHP Version 8.3
 * 
 * @package ProductCategory
 * @author System Admin
 * @copyright 2025
 */

declare(strict_types=1);

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session if needed for authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "../../common.php";

/**
 * Validate and sanitize input data
 * 
 * @param string $input Raw input data
 * @return int Sanitized integer
 * @throws InvalidArgumentException If input is invalid
 */
function validateAndSanitizeInput(string $input): int {
    $clean = filter_var(trim($input), FILTER_VALIDATE_INT);
    
    if ($clean === false || $clean <= 0) {
        throw new InvalidArgumentException('Invalid category ID or order value');
    }
    
    return $clean;
}

/**
 * Update product category order in database
 * 
 * @param mysqli $db Database connection
 * @param int $categoryId Category ID
 * @param int $order New order value
 * @return bool True on success, false on failure
 */
function updateCategoryOrder(mysqli $db, int $categoryId, int $order): bool {
    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE product_category 
            SET pc_order = ? 
            WHERE pc_id = ?";
    
    $stmt = mysqli_prepare($db, $sql);
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . mysqli_error($db));
        return false;
    }
    
    // Bind parameters (both are integers)
    mysqli_stmt_bind_param($stmt, "ii", $order, $categoryId);
    
    // Execute the statement
    $success = mysqli_stmt_execute($stmt);
    
    if (!$success) {
        error_log("Failed to execute update: " . mysqli_stmt_error($stmt));
    }
    
    // Check if any rows were affected
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    
    // Clean up
    mysqli_stmt_close($stmt);
    
    // Return true if update was successful (even if no rows changed)
    return $success && $affectedRows >= 0;
}

/**
 * Log the category order update for audit purposes
 * 
 * @param int $categoryId Updated category ID
 * @param int $oldOrder Previous order value (if known)
 * @param int $newOrder New order value
 * @return void
 */
function logCategoryUpdate(int $categoryId, ?int $oldOrder, int $newOrder): void {
    $logEntry = sprintf(
        "[%s] Category ID: %d | Old Order: %s | New Order: %d | IP: %s\n",
        date('Y-m-d H:i:s'),
        $categoryId,
        $oldOrder ?? 'UNKNOWN',
        $newOrder,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    );
    
    // Log to file (make sure directory exists and is writable)
    $logFile = __DIR__ . '/../../logs/category_updates.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logEntry, 3, $logFile);
}

/**
 * Verify user has permission to update categories
 * 
 * @return bool True if user has permission
 */
function checkUserPermission(): bool {
    // Implement your authentication logic here
    // This is a placeholder - customize based on your needs
    
    // Check if user is logged in and has admin privileges
    if (!isset($_SESSION['uid_indm'])) {
        return false;
    }
    
    // You can add additional permission checks here
    // For example, check user role from database
    
    return true;
}

// Main execution
try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Invalid request method. Only POST is allowed.');
    }
    
    // Verify user permissions (optional - uncomment if needed)
    // if (!checkUserPermission()) {
    //     throw new RuntimeException('Unauthorized access');
    // }
    
    // Validate required POST parameters
    if (!isset($_POST['id']) || !isset($_POST['pc_order'])) {
        throw new InvalidArgumentException('Missing required parameters');
    }
    
    // Sanitize and validate inputs
    $categoryId = validateAndSanitizeInput($_POST['id']);
    $newOrder = validateAndSanitizeInput($_POST['pc_order']);
    
    // Optional: Get old order value for logging
    $oldOrder = null;
    $stmt = mysqli_prepare($con, "SELECT pc_order FROM product_category WHERE pc_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $oldOrder = (int)$row['pc_order'];
        }
        mysqli_stmt_close($stmt);
    }
    
    // Perform the update
    $success = updateCategoryOrder($con, $categoryId, $newOrder);
    
    if (!$success) {
        throw new RuntimeException('Failed to update category order');
    }
    
    // Log the successful update
    logCategoryUpdate($categoryId, $oldOrder, $newOrder);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Category order updated successfully',
        'data' => [
            'category_id' => $categoryId,
            'new_order' => $newOrder,
            'old_order' => $oldOrder
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Validation error: " . $e->getMessage());
    header('Content-Type: application/json', true, 400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid input: ' . $e->getMessage()
    ]);
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Runtime error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Unexpected error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred'
    ]);
    
} finally {
    // Close database connection if it exists
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>