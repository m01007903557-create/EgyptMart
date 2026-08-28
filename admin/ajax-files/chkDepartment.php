<?php
/**
 * File: chkDepartment.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: التحقق من وجود تبعيات للقسم (موظفين أو أقسام فرعية) قبل الحذف
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/department_check_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../common.php";

/**
 * Class DepartmentDependencyChecker
 */
class DepartmentDependencyChecker {
    private mysqli $db;
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/department_dependency_checks.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate department ID
     * 
     * @param mixed $deptId Raw department ID from POST
     * @return int Validated department ID
     * @throws InvalidArgumentException
     */
    public function validateDepartmentId($deptId): int {
        if (!isset($deptId)) {
            throw new InvalidArgumentException('Department ID is required');
        }
        
        $cleanId = filter_var(trim((string)$deptId), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid department ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Check if department has employees
     * 
     * @param int $deptId Department ID
     * @return bool True if has employees
     */
    public function hasEmployees(int $deptId): bool {
        $sql = "SELECT COUNT(*) as count FROM employee_job WHERE ej_dept_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("Failed to prepare employee check query: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $deptId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get employee count in department
     * 
     * @param int $deptId Department ID
     * @return int Number of employees
     */
    public function getEmployeeCount(int $deptId): int {
        $sql = "SELECT COUNT(*) as count FROM employee_job WHERE ej_dept_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $deptId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Check if department has sub-departments
     * 
     * @param int $deptId Department ID
     * @return bool True if has sub-departments
     */
    public function hasSubDepartments(int $deptId): bool {
        $sql = "SELECT COUNT(*) as count FROM department WHERE dept_under = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("Failed to prepare sub-department check query: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $deptId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get sub-departments count
     * 
     * @param int $deptId Department ID
     * @return int Number of sub-departments
     */
    public function getSubDepartmentCount(int $deptId): int {
        $sql = "SELECT COUNT(*) as count FROM department WHERE dept_under = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $deptId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get department name
     * 
     * @param int $deptId Department ID
     * @return string|null Department name
     */
    public function getDepartmentName(int $deptId): ?string {
        $sql = "SELECT dept_name FROM department WHERE dept_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $deptId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['dept_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get detailed dependencies information
     * 
     * @param int $deptId Department ID
     * @return array Dependencies details
     */
    public function getDependencyDetails(int $deptId): array {
        return [
            'has_employees' => $this->hasEmployees($deptId),
            'employee_count' => $this->getEmployeeCount($deptId),
            'has_sub_departments' => $this->hasSubDepartments($deptId),
            'sub_department_count' => $this->getSubDepartmentCount($deptId)
        ];
    }
    
    /**
     * Log the dependency check
     * 
     * @param int $deptId Department ID
     * @param string $deptName Department name
     * @param array $dependencies Dependencies details
     */
    public function logCheck(int $deptId, ?string $deptName, array $dependencies): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] Department Dependency Check | ID: %d | Name: %s | Has Employees: %s (%d) | Has Sub-depts: %s (%d) | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $deptId,
            $deptName ?? 'Unknown',
            $dependencies['has_employees'] ? 'Yes' : 'No',
            $dependencies['employee_count'],
            $dependencies['has_sub_departments'] ? 'Yes' : 'No',
            $dependencies['sub_department_count'],
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
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
     * Send response (plain text for AJAX)
     * 
     * @param string $response Response (1 for has dependencies, 0 for no dependencies)
     */
    public function sendResponse(string $response): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo $response;
    }
    
    /**
     * Send JSON response for detailed checks
     * 
     * @param array $data Response data
     */
    public function sendJsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

// Main execution
try {
    // Initialize checker
    $checker = new DepartmentDependencyChecker($con);
    
    // Check permission (optional - uncomment if needed)
    // if (!$checker->checkPermission()) {
    //     $checker->sendResponse('0');
    //     exit;
    // }
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $checker->sendResponse('0');
        exit;
    }
    
    // Validate department ID
    $deptId = $checker->validateDepartmentId($_POST['dept_id'] ?? null);
    
    // Get department name for logging
    $deptName = $checker->getDepartmentName($deptId);
    
    // Check dependencies
    $hasEmployees = $checker->hasEmployees($deptId);
    $hasSubDepts = $checker->hasSubDepartments($deptId);
    
    // Get detailed dependencies
    $dependencies = $checker->getDependencyDetails($deptId);
    
    // Log the check
    $checker->logCheck($deptId, $deptName, $dependencies);
    
    // Check if detailed response is requested
    $detailed = isset($_GET['detailed']) && $_GET['detailed'] === '1';
    
    if ($detailed) {
        // Send JSON response with details
        $checker->sendJsonResponse([
            'success' => true,
            'has_dependencies' => ($hasEmployees || $hasSubDepts),
            'department_id' => $deptId,
            'department_name' => $deptName,
            'dependencies' => $dependencies
        ]);
    } else {
        // Send simple response (1 = has dependencies, 0 = no dependencies)
        $response = ($hasEmployees || $hasSubDepts) ? '1' : '0';
        $checker->sendResponse($response);
    }
    
} catch (InvalidArgumentException $e) {
    error_log("Department dependency check validation error: " . $e->getMessage());
    
    if (isset($_GET['detailed']) && $_GET['detailed'] === '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo '0'; // Return 0 on error (safe default)
    }
    
} catch (Exception $e) {
    error_log("Department dependency check unexpected error: " . $e->getMessage());
    
    if (isset($_GET['detailed']) && $_GET['detailed'] === '1') {
        header('Content-Type: application/json', true, 500);
        echo json_encode([
            'success' => false,
            'error' => 'System error occurred'
        ]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo '0'; // Return 0 on error (safe default)
    }
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>