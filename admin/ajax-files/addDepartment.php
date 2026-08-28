<?php
/**
 * File: addDepartment.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة قسم جديد في هيكل الأقسام
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/department_errors.log');

session_start();
require_once "../common.php";

/**
 * Class DepartmentManager
 */
class DepartmentManager {
    private mysqli $db;
    private string $logFile;
    private string $tableName = 'department';
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/department_additions.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate department name
     * 
     * @param mixed $name Department name from POST
     * @return string Validated department name
     * @throws InvalidArgumentException
     */
    public function validateDepartmentName($name): string {
        if (!isset($name) || trim((string)$name) === '') {
            throw new InvalidArgumentException('Department name is required');
        }
        
        $cleanName = trim((string)$name);
        
        if (strlen($cleanName) < 2) {
            throw new InvalidArgumentException('Department name must be at least 2 characters');
        }
        
        if (strlen($cleanName) > 100) {
            throw new InvalidArgumentException('Department name must not exceed 100 characters');
        }
        
        // Remove any potentially harmful characters
        $cleanName = htmlspecialchars($cleanName, ENT_QUOTES, 'UTF-8');
        
        return $cleanName;
    }
    
    /**
     * Validate parent department ID
     * 
     * @param mixed $under Parent department ID from POST
     * @return int Validated parent ID (0 for root)
     * @throws InvalidArgumentException
     */
    public function validateParentDepartment($under): int {
        if (!isset($under) || trim((string)$under) === '') {
            return 0; // Root department
        }
        
        $cleanUnder = filter_var(trim((string)$under), FILTER_VALIDATE_INT);
        
        if ($cleanUnder === false) {
            throw new InvalidArgumentException('Invalid parent department ID');
        }
        
        if ($cleanUnder < 0) {
            throw new InvalidArgumentException('Parent department ID cannot be negative');
        }
        
        return $cleanUnder;
    }
    
    /**
     * Check if parent department exists
     * 
     * @param int $parentId Parent department ID
     * @return bool True if exists or parent is 0
     */
    public function parentDepartmentExists(int $parentId): bool {
        if ($parentId === 0) {
            return true; // Root level
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE dept_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if department name already exists under same parent
     * 
     * @param string $name Department name
     * @param int $parentId Parent department ID
     * @return bool True if exists
     */
    public function departmentNameExists(string $name, int $parentId): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE dept_name = ? AND dept_under = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $name, $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get parent department details
     * 
     * @param int $parentId Parent department ID
     * @return object|null Parent department details
     */
    public function getParentDetails(int $parentId): ?object {
        if ($parentId === 0) {
            return (object)['dept_name' => 'Root', 'dept_id' => 0];
        }
        
        $sql = "SELECT dept_id, dept_name FROM {$this->tableName} WHERE dept_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Insert new department
     * 
     * @param string $name Department name
     * @param int $parentId Parent department ID
     * @return int Inserted department ID
     * @throws RuntimeException
     */
    public function insertDepartment(string $name, int $parentId): int {
        $sql = "INSERT INTO {$this->tableName} SET
                dept_under = ?,
                dept_name = ?,
                dept_updated_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare insert statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "is", $parentId, $name);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to insert department: ' . $error);
        }
        
        $insertId = (int)mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);
        
        return $insertId;
    }
    
    /**
     * Log department creation
     * 
     * @param string $name Department name
     * @param int $parentId Parent department ID
     * @param int $newId New department ID
     * @param object|null $parentDetails Parent details
     */
    public function logCreation(string $name, int $parentId, int $newId, ?object $parentDetails = null): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $parentName = $parentDetails->dept_name ?? 'Root';
        
        $logEntry = sprintf(
            "[%s] DEPARTMENT CREATED | ID: %d | Name: %s | Parent ID: %d | Parent Name: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $newId,
            $name,
            $parentId,
            $parentName,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Get department tree structure (optional)
     * 
     * @param int $parentId Starting parent
     * @return array Department tree
     */
    public function getDepartmentTree(int $parentId = 0): array {
        $sql = "SELECT dept_id, dept_name, dept_under 
                FROM {$this->tableName} 
                WHERE dept_under = ? 
                ORDER BY dept_name";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $departments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = [
                'id' => (int)$row['dept_id'],
                'name' => $row['dept_name'],
                'children' => $this->getDepartmentTree((int)$row['dept_id'])
            ];
        }
        
        mysqli_stmt_close($stmt);
        return $departments;
    }
    
    /**
     * Check user permission
     * 
     * @return bool
     */
    public function checkPermission(): bool {
        return isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] > 0;
    }
    
    /**
     * Send JSON response
     * 
     * @param bool $success Success status
     * @param string $message Response message
     * @param array $data Additional data
     */
    public function sendResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Handle redirect for non-AJAX requests
     * 
     * @param bool $success Success status
     * @param string $message Message
     */
    public function handleRedirect(bool $success, string $message): void {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->sendResponse($success, $message);
            exit;
        }
        
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'departments.php';
        $param = $success ? 'success' : 'error';
        header("Location: $redirectUrl?$param=" . urlencode($message));
        exit;
    }
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }
    
    // Initialize manager
    $manager = new DepartmentManager($con);
    
    // Check permission
    if (!$manager->checkPermission()) {
        $manager->handleRedirect(false, 'Unauthorized access');
        exit;
    }
    
    // Validate inputs
    $deptName = $manager->validateDepartmentName($_POST['dept_name'] ?? null);
    $parentId = $manager->validateParentDepartment($_POST['dept_under'] ?? null);
    
    // Check if parent department exists
    if (!$manager->parentDepartmentExists($parentId)) {
        $manager->handleRedirect(false, 'Parent department does not exist');
        exit;
    }
    
    // Check for duplicate department name under same parent
    if ($manager->departmentNameExists($deptName, $parentId)) {
        $manager->handleRedirect(false, 'Department name already exists under this parent');
        exit;
    }
    
    // Get parent details for logging
    $parentDetails = $manager->getParentDetails($parentId);
    
    // Insert department
    $newId = $manager->insertDepartment($deptName, $parentId);
    
    // Log the creation
    $manager->logCreation($deptName, $parentId, $newId, $parentDetails);
    
    // Get updated tree (optional)
    $tree = $manager->getDepartmentTree();
    
    // Handle response
    $manager->handleRedirect(true, 'Department added successfully', [
        'department_id' => $newId,
        'department_name' => $deptName,
        'parent_id' => $parentId
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Department validation error: " . $e->getMessage());
    $manager = $manager ?? new DepartmentManager($con);
    $manager->handleRedirect(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Department runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo 'Method not allowed';
        exit;
    }
    
    $manager = $manager ?? new DepartmentManager($con);
    $manager->handleRedirect(false, 'Failed to add department');
    
} catch (Exception $e) {
    error_log("Department unexpected error: " . $e->getMessage());
    $manager = $manager ?? new DepartmentManager($con);
    $manager->handleRedirect(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>