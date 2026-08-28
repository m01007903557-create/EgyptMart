<?php
/**
 * File: autocomplete.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: البحث المباشر عن جميع الموظفين النشطين للإكمال التلقائي
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/employee_search_errors.log');

session_start();
require_once "../common.php";

/**
 * Class EmployeeSearchAll
 */
class EmployeeSearchAll {
    private mysqli $db;
    private string $logFile;
    private int $maxResults = 50;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/employee_search_all.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate and sanitize search query
     * 
     * @param string|null $query Raw search query from GET
     * @return string Sanitized search query
     */
    public function validateQuery(?string $query): string {
        if ($query === null || trim($query) === '') {
            return '';
        }
        
        $cleanQuery = trim($query);
        
        // Remove any potentially harmful characters
        $cleanQuery = preg_replace('/[<>"\'%;()&]/', '', $cleanQuery);
        
        // Limit query length for performance
        if (strlen($cleanQuery) > 50) {
            $cleanQuery = substr($cleanQuery, 0, 50);
        }
        
        return $cleanQuery;
    }
    
    /**
     * Search for all active employees
     * 
     * @param string $searchTerm Search term
     * @return array List of matching employees
     * @throws RuntimeException
     */
    public function searchEmployees(string $searchTerm): array {
        if (empty($searchTerm)) {
            return [];
        }
        
        // Search in first name OR last name for better results
        $sql = "SELECT emp_id, emp_firstName, emp_lastName, emp_email, emp_mobile 
                FROM employee 
                WHERE emp_status = 1 
                  AND emp_firstName != 'admin'
                  AND (emp_firstName LIKE ? OR emp_lastName LIKE ?)
                ORDER BY emp_firstName, emp_lastName
                LIMIT ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare search statement');
        }
        
        $searchPattern = $searchTerm . '%';
        mysqli_stmt_bind_param($stmt, "ssi", $searchPattern, $searchPattern, $this->maxResults);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $employees = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $employees[] = [
                'id' => (int)$row['emp_id'],
                'firstName' => $row['emp_firstName'],
                'lastName' => $row['emp_lastName'],
                'fullName' => trim($row['emp_firstName'] . ' ' . $row['emp_lastName']),
                'email' => $row['emp_email'] ?? '',
                'mobile' => $row['emp_mobile'] ?? ''
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $employees;
    }
    
    /**
     * Search in specific department (optional enhancement)
     * 
     * @param string $searchTerm Search term
     * @param int $departmentId Department ID
     * @return array List of employees in department
     */
    public function searchByDepartment(string $searchTerm, int $departmentId): array {
        if (empty($searchTerm) || $departmentId <= 0) {
            return [];
        }
        
        $sql = "SELECT e.emp_id, e.emp_firstName, e.emp_lastName, e.emp_email, d.dept_name
                FROM employee e
                LEFT JOIN department d ON e.emp_department = d.dept_id
                WHERE e.emp_status = 1 
                  AND e.emp_firstName != 'admin'
                  AND e.emp_department = ?
                  AND (e.emp_firstName LIKE ? OR e.emp_lastName LIKE ?)
                ORDER BY e.emp_firstName, e.emp_lastName
                LIMIT ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return [];
        }
        
        $searchPattern = $searchTerm . '%';
        mysqli_stmt_bind_param($stmt, "issi", $departmentId, $searchPattern, $searchPattern, $this->maxResults);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $employees = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $employees[] = [
                'id' => (int)$row['emp_id'],
                'firstName' => $row['emp_firstName'],
                'lastName' => $row['emp_lastName'],
                'fullName' => trim($row['emp_firstName'] . ' ' . $row['emp_lastName']),
                'email' => $row['emp_email'] ?? '',
                'department' => $row['dept_name'] ?? ''
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $employees;
    }
    
    /**
     * Format output for autocomplete
     * 
     * Format: "Full Name|ID" per line
     * 
     * @param array $employees List of employees
     * @return string Formatted output
     */
    public function formatOutput(array $employees): string {
        if (empty($employees)) {
            return '';
        }
        
        $output = [];
        foreach ($employees as $emp) {
            // Capitalize first letter of each name
            $firstName = ucfirst($emp['firstName']);
            $lastName = ucfirst($emp['lastName']);
            $fullName = $firstName . ' ' . $lastName;
            
            $output[] = $fullName . '|' . $emp['id'];
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Format output with additional info (for enhanced autocomplete)
     * 
     * @param array $employees List of employees
     * @return string Formatted output with email
     */
    public function formatEnhancedOutput(array $employees): string {
        if (empty($employees)) {
            return '';
        }
        
        $output = [];
        foreach ($employees as $emp) {
            $firstName = ucfirst($emp['firstName']);
            $lastName = ucfirst($emp['lastName']);
            $fullName = $firstName . ' ' . $lastName;
            
            // Add email if available
            if (!empty($emp['email'])) {
                $fullName .= ' (' . $emp['email'] . ')';
            }
            
            $output[] = $fullName . '|' . $emp['id'];
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Log search query
     * 
     * @param string $searchTerm Search term
     * @param int $resultCount Number of results
     * @param array $params Additional parameters
     */
    public function logSearch(string $searchTerm, int $resultCount, array $params = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $departmentInfo = isset($params['department']) ? " | Dept: {$params['department']}" : '';
        
        $logEntry = sprintf(
            "[%s] EMPLOYEE SEARCH ALL | Term: '%s' | Results: %d%s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $searchTerm,
            $resultCount,
            $departmentInfo,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Get employee count by first letter
     * 
     * @param string $firstLetter First letter to count
     * @return int Number of employees
     */
    public function getCountByFirstLetter(string $firstLetter): int {
        if (empty($firstLetter)) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM employee 
                WHERE emp_status = 1 
                  AND emp_firstName != 'admin'
                  AND emp_firstName LIKE ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        $pattern = $firstLetter . '%';
        mysqli_stmt_bind_param($stmt, "s", $pattern);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Check if user has permission
     * 
     * @return bool
     */
    public function checkPermission(): bool {
        return isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] > 0;
    }
    
    /**
     * Send response headers
     */
    public function sendHeaders(): void {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
    }
}

// Main execution
try {
    // Initialize search
    $search = new EmployeeSearchAll($con);
    
    // Check permission (optional - uncomment if needed)
    // if (!$search->checkPermission()) {
    //     $search->sendHeaders();
    //     echo '';
    //     exit;
    // }
    
    // Get and validate search query
    $query = $search->validateQuery($_GET['q'] ?? null);
    
    // Get optional department filter
    $departmentId = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;
    
    // Send headers
    $search->sendHeaders();
    
    // If query is empty, return nothing
    if (empty($query)) {
        echo '';
        exit;
    }
    
    // Search for employees (with or without department filter)
    if ($departmentId > 0) {
        $employees = $search->searchByDepartment($query, $departmentId);
        $search->logSearch($query, count($employees), ['department' => $departmentId]);
    } else {
        $employees = $search->searchEmployees($query);
        $search->logSearch($query, count($employees));
    }
    
    // Format and output results
    $output = $search->formatOutput($employees);
    echo $output;
    
} catch (RuntimeException $e) {
    error_log("Employee search all runtime error: " . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo '';
    
} catch (Exception $e) {
    error_log("Employee search all unexpected error: " . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo '';
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>