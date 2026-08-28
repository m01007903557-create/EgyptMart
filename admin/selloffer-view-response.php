<?php
declare(strict_types=1);

// Enable error reporting for debugging
ini_set("display_errors", "1");
error_reporting(E_ALL);

// Include required files
include "../common.php";

// Initialize database connection
global $con;

/**
 * Class SaleOfferDataTableResponse
 */
class SaleOfferDataTableResponse {
    
    private mysqli $db;
    private array $params;
    private array $columns;
    private string $baseSql;
    private string $countSql;
    
    public function __construct(mysqli $database, array $params) {
        $this->db = $database;
        $this->params = $params;
        $this->initializeColumns();
        $this->buildBaseQueries();
    }
    
    private function initializeColumns(): void {
        $this->columns = [
            1 => 'so_posting_date',
            2 => 'so_service',
            3 => 'bnsprof_compname',
            4 => 'cn_name',
            5 => 'mst_name'
        ];
    }
    
    private function buildBaseQueries(): void {
        $this->baseSql = "SELECT 
            s.so_id,
            s.so_usr_id,
            s.so_pic,
            s.so_posting_date,
            s.so_service,
            s.so_approval_status,
            pc.pc_name,
            c.cn_name,
            bf.bnsprof_compname,
            pm.expiry_date,
            sp.mst_name
        FROM sale_offer s 
        JOIN product_category pc ON s.so_pc_id = pc.pc_id 
        JOIN user u ON u.usr_id = s.so_usr_id 
        JOIN business_profile bf ON bf.bnsprof_uid = s.so_usr_id 
        JOIN country c ON c.cn_id = u.country 
        LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
        LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
        
        $this->countSql = "SELECT COUNT(*) as count 
        FROM sale_offer s 
        JOIN product_category pc ON s.so_pc_id = pc.pc_id 
        JOIN user u ON u.usr_id = s.so_usr_id 
        JOIN business_profile bf ON bf.bnsprof_uid = s.so_usr_id 
        JOIN country c ON c.cn_id = u.country 
        LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
        LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
    }
    
    private function buildSearchCondition(): string {
        if (empty($this->params['search']['value'])) {
            return '';
        }
        
        $searchValue = $this->db->real_escape_string($this->params['search']['value']);
        
        $conditions = [
            "s.so_service LIKE '{$searchValue}%'",
            "s.so_posting_date LIKE '{$searchValue}%'",
            "bf.bnsprof_compname LIKE '{$searchValue}%'",
            "c.cn_name LIKE '{$searchValue}%'",
            "sp.mst_name LIKE '{$searchValue}%'"
        ];
        
        return " WHERE (" . implode(" OR ", $conditions) . ")";
    }
    
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
    
    private function formatDate(string $date): string {
        if (empty($date)) {
            return 'N/A';
        }
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format('jS F Y');
        } catch (Exception $e) {
            return $date;
        }
    }
    
    private function getActionLinks(object $row): array {
        $soId = (int)$row->so_id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            $approveLink = "?action=appr&id={$soId}";
            $disapproveLink = "?action=disappr&id={$soId}";
            $deleteLink = "?action=del&ad-id={$soId}";
        } else {
            $approveLink = "selloffer-view.php?{$queryString}&action=appr&id={$soId}";
            $disapproveLink = "selloffer-view.php?{$queryString}&action=disappr&id={$soId}";
            $deleteLink = "selloffer-view.php?{$queryString}&action=del&ad-id={$soId}";
        }
        
        return [
            'approve' => $approveLink,
            'disapprove' => $disapproveLink,
            'delete' => $deleteLink
        ];
    }
    
    private function formatStatusActions(object $row, array $links): string {
        $status = (int)($row->so_approval_status ?? 0);
        
        switch ($status) {
            case 0: // Pending
                return '<a href="' . htmlspecialchars($links['approve'], ENT_QUOTES, 'UTF-8') . '" 
                        onclick="return confirm(\'Are you sure to approve this sale offer?\')" 
                        title="Approve">
                        <img alt="Approve" src="images/active.jpg">
                    </a>&nbsp;
                    <a href="' . htmlspecialchars($links['disapprove'], ENT_QUOTES, 'UTF-8') . '" 
                       onclick="return confirm(\'Are you sure to disapprove this sale offer?\')" 
                       title="Disapprove">
                        <img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0">
                    </a>';
            case 1:
                return '<span class="label label-success">Approved</span>';
            case 2:
                return '<span class="label label-danger">Rejected</span>';
            default:
                return '<span class="label label-default">Unknown</span>';
        }
    }
    
    // ✅ تم تغيير اسم الدالة من formatSaleOfferRow إلى formatRow لتجنب أي تعارض (ومحتواها الأصلي)
    private function formatRow(object $row): array {
        $result = [];
        $soId = (int)$row->so_id;
        $links = $this->getActionLinks($row);
        
        $result[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . $soId . '" /><span class="lbl"></span>';
        
                // ✅ عرض الصورة (يدعم مجلدي sale_offer و image_gallery)
        $image_url = '';
        $image_path = $row->so_pic ?? '';
        
        if (!empty($image_path)) {
            // تحقق مما إذا كان المسار يبدو كصورة من المعرض
            if (strpos($image_path, 'upload/image_gallery/') !== false) {
                $full_path = '../' . $image_path;
                if (file_exists($full_path)) {
                    $image_url = $full_path;
                }
            } 
            // تحقق مما إذا كان المسار يبدو كصورة مرفوعة مباشرة
            elseif (strpos($image_path, 'upload/sale_offer/') !== false) {
                $full_path = '../' . $image_path;
                if (file_exists($full_path)) {
                    $image_url = $full_path;
                }
            }
            // إذا كان مجرد اسم ملف، حاول البحث عنه في مجلد sale_offer
            elseif (file_exists("../upload/sale_offer/" . $image_path)) {
                $image_url = "../upload/sale_offer/" . $image_path;
            }
        }
        
        if (!empty($image_url)) {
            $result[1] = '<img src="' . $image_url . '" width="70px" height="62px" style="object-fit:cover;" alt="Sale Offer Image"/>';
        } else {
            $result[1] = '<img src="../upload/sale_offer/no-image.png" width="70px" height="62px" alt="No Image"/>';
        }
        
        $result[2] = htmlspecialchars(ucwords(stripslashes($row->so_service ?? '')), ENT_QUOTES, 'UTF-8');
        $result[3] = htmlspecialchars(ucwords(stripslashes($row->bnsprof_compname ?? '')), ENT_QUOTES, 'UTF-8');
        $result[4] = htmlspecialchars($row->cn_name ?? '', ENT_QUOTES, 'UTF-8');
        $result[5] = htmlspecialchars($row->mst_name ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $result[6] = $this->formatDate($row->so_posting_date ?? '');
        $result[7] = $this->formatStatusActions($row, $links);
        
        $token = rand(1000, 9000) . md5((string)$soId);
        $editToken = md5((string)$soId);
        
        $result[8] = '<a href="selloffer-details.php?token=' . $token . '" title="View Details">
                        <img src="images/details.png" alt="Details" />
                      </a>
                      <a href="selloffer-edit.php?token=' . $editToken . '" title="Edit">
                        <img src="images/edit.jpg" alt="Edit" />
                      </a>';
        
        return $result;
    }
    
    public function getResponse(): array {
        $where = $this->buildSearchCondition();
        $totalRecords = $this->getTotalRecords();
        
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
        
        $start = (int)($this->params['start'] ?? 0);
        $length = (int)($this->params['length'] ?? 10);
        $limit = " LIMIT {$start}, {$length}";
        
        $sql = $this->baseSql . $where . $orderBy . $limit;
        
        $result = mysqli_query($this->db, $sql);
        if (!$result) {
            error_log("Data query error: " . mysqli_error($this->db));
            return ["draw" => (int)($this->params['draw'] ?? 0), "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []];
        }
        
        $data = [];
        while ($row = mysqli_fetch_object($result)) {
            // ✅ استدعاء الدالة الجديدة formatRow
            $data[] = $this->formatRow($row);
        }
        
        return [
            "draw" => (int)($this->params['draw'] ?? 0),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data
        ];
    }
}

try {
    $response = new SaleOfferDataTableResponse($con, $_REQUEST);
    $jsonData = $response->getResponse();
} catch (Exception $e) {
    error_log("Sale offer DataTable error: " . $e->getMessage());
    $jsonData = ["draw" => (int)($_REQUEST['draw'] ?? 0), "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []];
}

header('Content-Type: application/json');
echo json_encode($jsonData);
?>