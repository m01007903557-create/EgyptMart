<?php
/**
 * File: user-list-response.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: ملف استجابة AJAX لقائمة المستخدمين
 * AJAX response file for user list
 * 
 * Features:
 * - بحث متقدم في المستخدمين
 * - ترتيب ديناميكي حسب الأعمدة
 * - ترقيم الصفحات
 * - عرض معلومات المستخدمين والشركات
 * - روابط للتعديل والعرض
 */

declare(strict_types=1);

// Enable error reporting for debugging
ini_set("display_errors", "1");
error_reporting(E_ALL);

// Include required files
include "../common.php";

// Initialize database connection
global $con;

/**
 * Class UserListDataTableResponse
 * 
 * Handles DataTable server-side processing for user list
 */
class UserListDataTableResponse {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var array Request parameters */
    private array $params;
    
    /** @var array Column definitions for sorting */
    private array $columns;
    
    /** @var string Base SQL query */
    private string $baseSql;
    
    /** @var string Count SQL query */
    private string $countSql;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param array $params Request parameters
     */
    public function __construct(mysqli $database, array $params) {
        $this->db = $database;
        $this->params = $params;
        $this->initializeColumns();
        $this->buildBaseQueries();
    }
    
    /**
     * Initialize column definitions for sorting
     */
    private function initializeColumns(): void {
        $this->columns = [
            1 => 'usr_id',
            2 => 'date',
            3 => 'fname',
            4 => 'lname',
            5 => 'email',
            6 => 'bnsprof_compname',
            7 => 'cn_name'
        ];
    }
    
    /**
     * Build base SQL queries
     */
    private function buildBaseQueries(): void {
        $this->baseSql = "SELECT 
                u.*,
                c.cn_name,
                bf.bnsprof_compname,
                sp.mst_name
            FROM user u
            JOIN country c ON u.country = c.cn_id
            JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
            JOIN smembership_plan sp ON sp.mp_id = u.usr_mp_id
            WHERE u.status = '1'";
        
        $this->countSql = "SELECT COUNT(*) as count 
            FROM user u
            JOIN country c ON u.country = c.cn_id
            JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
            JOIN smembership_plan sp ON sp.mp_id = u.usr_mp_id
            WHERE u.status = '1'";
    }
    
    /**
     * Build search condition based on user input
     * 
     * @return string SQL WHERE clause
     */
    private function buildSearchCondition(): string {
        if (empty($this->params['search']['value'])) {
            return '';
        }
        
        $searchValue = $this->db->real_escape_string($this->params['search']['value']);
        
        $conditions = [
            "u.fname LIKE '%{$searchValue}%'",
            "u.lname LIKE '%{$searchValue}%'",
            "bf.bnsprof_compname LIKE '%{$searchValue}%'",
            "c.cn_name LIKE '%{$searchValue}%'",
            "u.date LIKE '%{$searchValue}%'",
            "sp.mst_name LIKE '%{$searchValue}%'",
            "u.email LIKE '%{$searchValue}%'"
        ];
        
        return " AND (" . implode(" OR ", $conditions) . ")";
    }
    
    /**
     * Get total records count
     * 
     * @param string $where WHERE clause
     * @return int Total records
     */
    private function getTotalRecords(string $where = ''): int {
        $sql = $this->countSql . $where;
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            error_log("Count query error: " . mysqli_error($this->db));
            return 0;
        }
        
        $row = mysqli_fetch_object($result);
        return (int)($row->count ?? 0);
    }
    
    /**
     * Get user full name
     * 
     * @param object $row User row
     * @return string Full name
     */
    private function getUserFullName(object $row): string {
        $parts = [];
        if (!empty($row->name_prefix)) {
            $parts[] = ucwords($row->name_prefix);
        }
        if (!empty($row->fname)) {
            $parts[] = ucwords($row->fname);
        }
        if (!empty($row->lname)) {
            $parts[] = ucwords($row->lname);
        }
        return implode(' ', $parts);
    }
    
    /**
     * Get user token for details link
     * 
     * @param int $userId User ID
     * @return string Token
     */
    private function getUserToken(int $userId): string {
        return rand(1000, 9999) . md5((string)$userId);
    }
    
    /**
     * Get edit token for user
     * 
     * @param int $userId User ID
     * @return string Token
     */
    private function getEditToken(int $userId): string {
        return rand(1000, 9999) . $userId;
    }
    
    /**
     * Build delete link
     * 
     * @param int $userId User ID
     * @return string Delete URL
     */
    private function getDeleteLink(int $userId): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$userId}";
        }
        
        return "user-list.php?{$queryString}&action=del&ad-id={$userId}";
    }
    
    /**
     * Format user row for display
     * 
     * @param object $row Database row
     * @return array Formatted data for DataTable
     */
    private function formatUserRow(object $row): array {
        $result = [];
        $userId = (int)$row->usr_id;
        
        // Checkbox
        $result[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . $userId . '" /><span class="lbl"></span>';
        
        // Full name
        $result[1] = htmlspecialchars($this->getUserFullName($row), ENT_QUOTES, 'UTF-8');
        
        // Email
        $result[2] = '<a href="mailto:' . htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8') . '">' 
                    . htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8') . '</a>';
        
        // Credit
        $result[3] = '<span class="badge badge-info">' . (int)($row->usr_credit ?? 0) . '</span>';
        
        // Country
        $result[4] = htmlspecialchars($row->cn_name ?? '', ENT_QUOTES, 'UTF-8');
        
        // Membership plan
        $result[5] = htmlspecialchars($row->mst_name ?? 'N/A', ENT_QUOTES, 'UTF-8');
        
        // Details link
        $detailsToken = $this->getUserToken($userId);
        $result[6] = '<a href="user-details.php?token=' . $detailsToken . '" title="View Details">
                        <img src="images/details.png" alt="Details" />
                      </a>';
        
        // Action buttons (Edit & Delete)
        $editToken = $this->getEditToken($userId);
        $deleteLink = $this->getDeleteLink($userId);
        
        $result[7] = '<div class="btn-group">' .
                     '<a href="user-edit.php?token=' . $editToken . '" title="Edit" class="btn btn-xs btn-info">' .
                     '<i class="icon-edit bigger-120"></i>' .
                     '</a>' .
                     '<a href="' . $deleteLink . '" title="Delete" class="btn btn-xs btn-danger" ' .
                     'onclick="return confirm(\'Are you sure you want to delete this user?\')">' .
                     '<i class="icon-trash bigger-120"></i>' .
                     '</a>' .
                     '</div>';
        
        return $result;
    }
    
    /**
     * Generate response data for DataTables
     * 
     * @return array JSON response
     */
    public function getResponse(): array {
        // Build search condition
        $where = $this->buildSearchCondition();
        
        // Get total records
        $totalRecords = $this->getTotalRecords();
        
        // Build order by clause
        $orderColumn = $this->params['order'][0]['column'] ?? 0;
        if ($orderColumn == 0) {
            $orderColumn = 1;
            $orderDir = 'desc';
        } else {
            $orderDir = $this->params['order'][0]['dir'] ?? 'asc';
        }
        
        $orderBy = '';
        if (isset($this->columns[$orderColumn])) {
            $orderBy = " ORDER BY " . $this->columns[$orderColumn] . " " . $orderDir;
        }
        
        // Build limit clause
        $start = (int)($this->params['start'] ?? 0);
        $length = (int)($this->params['length'] ?? 10);
        $limit = " LIMIT {$start}, {$length}";
        
        // Build final query
        $sql = $this->baseSql . $where . $orderBy . $limit;
        
        // Execute query
        $result = mysqli_query($this->db, $sql);
        if (!$result) {
            error_log("Data query error: " . mysqli_error($this->db));
            return [
                "draw" => (int)($this->params['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ];
        }
        
        // Format data
        $data = [];
        while ($row = mysqli_fetch_object($result)) {
            $data[] = $this->formatUserRow($row);
        }
        
        return [
            "draw" => (int)($this->params['draw'] ?? 0),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data
        ];
    }
    
    /**
     * Get filtered records count (for future use)
     * 
     * @param string $where WHERE clause
     * @return int Filtered count
     */
    private function getFilteredRecords(string $where = ''): int {
        $sql = $this->countSql . $where;
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_object($result);
        return (int)($row->count ?? 0);
    }
}

// Initialize and execute response
try {
    $response = new UserListDataTableResponse($con, $_REQUEST);
    $jsonData = $response->getResponse();
} catch (Exception $e) {
    error_log("User list DataTable error: " . $e->getMessage());
    $jsonData = [
        "draw" => (int)($_REQUEST['draw'] ?? 0),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($jsonData);
?>