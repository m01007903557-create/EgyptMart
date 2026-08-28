<?php
/**
 * File: autocomplete-mn.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: البحث المباشر عن الموظفين للإكمال التلقائي في نماذج الرواتب الشهرية
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/employee_search_errors.log');

session_start();
require_once "../common.php";

/**
 * Class EmployeeAutocomplete
 */
class EmployeeAutocomplete {
    private mysqli $db;
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/employee_search.log';
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
     * @throws InvalidArgumentException
     */
    public function validateQuery(?string $query): string {
        if ($query === null || trim($query) === '') {
            return '';
        }
        
        $cleanQuery = trim($query);
        
        // Remove any potentially harmful characters
        $cleanQuery = preg_replace('/[<>"\'%;()&]/', '', $cleanQuery);
        
        // Limit query length
        if (strlen($cleanQuery) > 50) {
            $cleanQuery = substr($cleanQuery, 0, 50);
        }
        
        return $cleanQuery;
    }
    
    /**
     * Search for employees
     * 
     * @param string $searchTerm Search term
     * @return array List of matching employees
     * @throws RuntimeException
     */
    public function searchEmployees(string $searchTerm): array {
        if (empty($searchTerm)) {
            return [];
        }
        
        // Using prepared statement to prevent SQL injection
        $sql = "SELECT e.emp_id, e.emp_firstName, e.emp_lastName, e.emp_email, 
                       es.es_basicSalary, es.es_empPosition
                FROM employee e
                INNER JOIN employee_salary es ON e.emp_id = es.es_emp_id
                WHERE e.emp_status = 1 
                  AND e.emp_firstName LIKE ? 
                  AND e.emp_firstName != 'admin'
                  AND es.es_payFrequency = 'monthly'
                ORDER BY e.emp_firstName
                LIMIT 20"; // Limit results for performance
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare search statement');
        }
        
        $searchPattern = $searchTerm . '%';
        mysqli_stmt_bind_param($stmt, "s", $searchPattern);
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
                'position' => $row['es_empPosition'] ?? '',
                'salary' => $row['es_basicSalary'] ?? 0
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
        $output = [];
        
        foreach ($employees as $emp) {
            // Capitalize first letter of each name
            $fullName = ucfirst($emp['firstName']) . ' ' . ucfirst($emp['lastName']);
            $output[] = $fullName . '|' . $emp['id'];
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Log search query
     * 
     * @param string $searchTerm Search term
     * @param int $resultCount Number of results
     */
    public function logSearch(string $searchTerm, int $resultCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] EMPLOYEE SEARCH | Term: '%s' | Results: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $searchTerm,
            $resultCount,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check if user has permission to search
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
    }
}

// Main execution
try {
    // Initialize search
    $search = new EmployeeAutocomplete($con);
    
    // Check permission (optional - uncomment if needed)
    // if (!$search->checkPermission()) {
    //     $search->sendHeaders();
    //     echo '';
    //     exit;
    // }
    
    // Validate search query
    $query = $search->validateQuery($_GET['q'] ?? null);
    
    // Send headers
    $search->sendHeaders();
    
    // If query is empty, return nothing
    if (empty($query)) {
        echo '';
        exit;
    }
    
    // Search for employees
    $employees = $search->searchEmployees($query);
    
    // Log the search
    $search->logSearch($query, count($employees));
    
    // Format and output results
    $output = $search->formatOutput($employees);
    echo $output;
    
} catch (InvalidArgumentException $e) {
    error_log("Employee search validation error: " . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo '';
    
} catch (RuntimeException $e) {
    error_log("Employee search runtime error: " . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo '';
    
} catch (Exception $e) {
    error_log("Employee search unexpected error: " . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo '';
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>