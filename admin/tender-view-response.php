<?php
/**
 * File: tender-view-response.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: ملف استجابة AJAX لعرض المناقصات النشطة
 * AJAX response file for active tenders display
 * 
 * Features:
 * - بحث متقدم في المناقصات
 * - ترتيب ديناميكي حسب الأعمدة
 * - ترقيم الصفحات
 * - عرض المناقصات النشطة فقط (تاريخ الانتهاء >= اليوم)
 * - حالة الموافقة
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
 * Class TenderDataTableResponse
 * 
 * Handles DataTable server-side processing for active tenders
 */
class TenderDataTableResponse {
    
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
            1 => 'tnd_due_date',
            2 => 'tnd_heading',
            3 => 'pc_name',
            4 => 'tnd_value',
            5 => 'bnsprof_compname',
            6 => 'cn_name',
            7 => 'mst_name',
            8 => 'expiry_date'
        ];
    }
    
    /**
     * Build base SQL queries
     */
    private function buildBaseQueries(): void {
        $this->baseSql = "SELECT 
                t.tnd_id,
                t.tnd_usr_id,
                t.tnd_heading,
                t.tnd_publish_date,
                t.tnd_due_date,
                t.tnd_approval_status,
                pc.pc_name,
                bf.bnsprof_compname,
                c.cn_name,
                pm.expiry_date,
                sp.mst_name
            FROM tender t 
            JOIN product_category pc ON t.tnd_pc_id = pc.pc_id 
            JOIN user u ON u.usr_id = t.tnd_usr_id 
            JOIN business_profile bf ON bf.bnsprof_uid = t.tnd_usr_id 
            JOIN country c ON c.cn_id = u.country 
            LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
            LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id
            WHERE t.tnd_status = '1' AND t.tnd_due_date >= CURDATE()";
        
        $this->countSql = "SELECT COUNT(*) as count 
            FROM tender t 
            JOIN product_category pc ON t.tnd_pc_id = pc.pc_id 
            JOIN user u ON u.usr_id = t.tnd_usr_id 
            JOIN business_profile bf ON bf.bnsprof_uid = t.tnd_usr_id 
            JOIN country c ON c.cn_id = u.country 
            WHERE t.tnd_status = '1' AND t.tnd_due_date >= CURDATE()";
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
            "t.tnd_heading LIKE '%{$searchValue}%'",
            "pc.pc_name LIKE '%{$searchValue}%'",
            "bf.bnsprof_compname LIKE '%{$searchValue}%'",
            "c.cn_name LIKE '%{$searchValue}%'",
            "t.tnd_due_date LIKE '%{$searchValue}%'",
            "t.tnd_value LIKE '%{$searchValue}%'"
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
     * Format date for display
     * 
     * @param string $date Date string
     * @return string Formatted date
     */
    private function formatDate(string $date): string {
        if (empty($date) || $date === '0000-00-00') {
            return 'N/A';
        }
        
        return date("d-M, Y", strtotime($date));
    }
    
    /**
     * Get action links based on query string
     * 
     * @param int $tenderId Tender ID
     * @return array Array of links [approve, disapprove, delete]
     */
    private function getActionLinks(int $tenderId): array {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            $approveLink = "?action=appr&id={$tenderId}";
            $disapproveLink = "?action=disappr&id={$tenderId}";
            $deleteLink = "?action=del&ad-id={$tenderId}";
        } else {
            $approveLink = "tender-view.php?{$queryString}&action=appr&id={$tenderId}";
            $disapproveLink = "tender-view.php?{$queryString}&action=disappr&id={$tenderId}";
            $deleteLink = "tender-view.php?{$queryString}&action=del&ad-id={$tenderId}";
        }
        
        return [
            'approve' => $approveLink,
            'disapprove' => $disapproveLink,
            'delete' => $deleteLink
        ];
    }
    
    /**
     * Format status actions based on approval status
     * 
     * @param object $row Database row
     * @param array $links Action links
     * @return string HTML for status actions
     */
    private function formatStatusActions(object $row, array $links): string {
        $status = (int)($row->tnd_approval_status ?? 0);
        $tenderId = (int)$row->tnd_id;
        
        switch ($status) {
            case 0: // Pending
                return '<a href="' . htmlspecialchars($links['approve'], ENT_QUOTES, 'UTF-8') . '" 
                        onclick="return confirm(\'Are you sure you want to approve this tender?\')" 
                        title="Approve">
                        <img alt="Approve" src="images/active.jpg">
                    </a>&nbsp;
                    <a href="' . htmlspecialchars($links['disapprove'], ENT_QUOTES, 'UTF-8') . '" 
                       onclick="return confirm(\'Are you sure you want to disapprove this tender?\')" 
                       title="Disapprove">
                        <img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0">
                    </a>';
            
            case 1: // Approved
                return '<span class="label label-success">Approved</span>';
            
            case 2: // Rejected
                return '<span class="label label-danger">Rejected</span>';
            
            default:
                return '<span class="label label-default">Unknown</span>';
        }
    }
    
    /**
     * Format tender row for display
     * 
     * @param object $row Database row
     * @return array Formatted data for DataTable
     */
    private function formatTenderRow(object $row): array {
        $result = [];
        $tenderId = (int)$row->tnd_id;
        $links = $this->getActionLinks($tenderId);
        
        // Checkbox
        $result[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . $tenderId . '" /><span class="lbl"></span>';
        
        // Tender heading
        $result[1] = htmlspecialchars(ucwords(stripslashes($row->tnd_heading ?? '')), ENT_QUOTES, 'UTF-8');
        
        // Company name
        $result[2] = htmlspecialchars(ucwords(stripslashes($row->bnsprof_compname ?? '')), ENT_QUOTES, 'UTF-8');
        
        // Country
        $result[3] = htmlspecialchars(ucwords(stripslashes($row->cn_name ?? '')), ENT_QUOTES, 'UTF-8');
        
        // Publish date
        $result[4] = $this->formatDate($row->tnd_publish_date ?? '');
        
        // Due date
        $result[5] = $this->formatDate($row->tnd_due_date ?? '');
        
        // Details link
        $token = rand(1000, 9000) . md5((string)$tenderId);
        $result[6] = '<a href="tender-details.php?token=' . $token . '" title="View Details">
                        <img src="images/details.png" alt="Details" />
                      </a>';
        
        // Status actions
        $result[7] = $this->formatStatusActions($row, $links);
        
        // Edit link
        $editToken = md5((string)$tenderId);
        $result[8] = '<a href="tender-edit.php?token=' . $editToken . '" title="Edit">
                        <img src="images/edit.jpg" alt="Edit" />
                      </a>';
        
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
            $data[] = $this->formatTenderRow($row);
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
    $response = new TenderDataTableResponse($con, $_REQUEST);
    $jsonData = $response->getResponse();
} catch (Exception $e) {
    error_log("Tender DataTable error: " . $e->getMessage());
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