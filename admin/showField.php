<?php
/**
 * File: showField.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة الحقول الإضافية حسب الفئة المحددة
 * Display and manage additional fields by selected category
 * 
 * Features:
 * - جلب الحقول الإضافية المرتبطة بفئة معينة
 * - عرض الحقول في جدول مع خيارات التعديل
 * - تعديل اسم الحقل والتسمية مباشرة
 * - حذف الحقول
 * - دعم DataTables للبحث والترتيب
 */

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/additional_fields_errors.log');

// Include required files
include "../common.php";

/**
 * Class AdditionalFieldsByCategory
 * 
 * Handles displaying additional fields for a specific category
 */
class AdditionalFieldsByCategory {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Category ID */
    private int $categoryId;
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $categoryId Category ID
     */
    public function __construct(mysqli $database, int $categoryId) {
        $this->db = $database;
        $this->categoryId = $categoryId;
        $this->logFile = __DIR__ . '/../../logs/additional_fields_requests.log';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate and sanitize category ID
     * 
     * @param mixed $q Raw category ID from GET
     * @return int Validated category ID
     * @throws InvalidArgumentException If validation fails
     */
    public static function validateCategoryId($q): int {
        if (!isset($q)) {
            throw new InvalidArgumentException('Category ID is required');
        }
        
        $cleanId = filter_var(trim((string)$q), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId < 0) {
            throw new InvalidArgumentException('Invalid category ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Fetch additional fields for category
     * 
     * @return array List of additional fields
     * @throws RuntimeException If database query fails
     */
    public function fetchFields(): array {
        if ($this->categoryId <= 0) {
            return [];
        }
        
        $sql = "SELECT * FROM additional_field 
                WHERE af_pc_id = ? 
                ORDER BY af_name";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $fields = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $fields[] = [
                'id' => (int)$row['af_id'],
                'name' => $row['af_name'],
                'label' => $row['af_label'],
                'type' => $row['af_type']
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $fields;
    }
    
    /**
     * Get category name
     * 
     * @return string|null Category name
     */
    public function getCategoryName(): ?string {
        $sql = "SELECT pc_name FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['pc_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Log the request
     * 
     * @param int $resultCount Number of results
     */
    public function logRequest(int $resultCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $categoryName = $this->getCategoryName() ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] Additional Fields Request | Category ID: %d | Category: %s | Results: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $this->categoryId,
            $categoryName,
            $resultCount,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Generate HTML for field row
     * 
     * @param array $field Field data
     * @param int $index Row index
     * @return string HTML row
     */
    public function generateFieldRow(array $field, int $index): string {
        $fieldId = (int)$field['id'];
        $fieldName = htmlspecialchars($field['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $fieldLabel = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
        $fieldType = htmlspecialchars(ucfirst($field['type'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        return '
        <tr>
            <td class="center">#' . ($index + 1) . '</td>
            <td>
                <span id="display_nm_' . $fieldId . '">' . $fieldName . '</span>
                <span id="input_nm_' . $fieldId . '" style="display:none;">
                    <input type="text" name="af_nm_' . $fieldId . '" 
                           id="af_nm_' . $fieldId . '" class="col-sm-6" 
                           value="' . $fieldName . '"/>
                </span>
            </td>
            <td>
                <span id="display_lbl_' . $fieldId . '">' . $fieldLabel . '</span>
                <span id="input_lbl_' . $fieldId . '" style="display:none;">
                    <input type="text" name="af_lbl_' . $fieldId . '" 
                           id="af_lbl_' . $fieldId . '" class="col-sm-9" 
                           value="' . $fieldLabel . '"/>
                </span>
            </td>
            <td><span id="display_' . $fieldId . '">' . $fieldType . '</span></td>
            <td align="center">
                <span id="edit_' . $fieldId . '">
                    <a href="javascript:ShowEditField(\'' . $fieldId . '\')" style="text-decoration:none;">
                        <img width="15" alt="edit" src="images/edit.jpg" border="0" />
                    </a>
                    &nbsp;
                    <a href="javascript:DelField(\'' . $fieldId . '\')">
                        <img width="15" alt="delete" src="images/delete.jpg" border="0" />
                    </a>
                </span>
                <span id="save_' . $fieldId . '" style="display:none;">
                    <a href="javascript:SaveField(\'' . $fieldId . '\')">
                        <img width="15" alt="save" src="images/save.png" border="0" />
                    </a>
                </span>
            </td>
        </tr>';
    }
    
    /**
     * Send response
     * 
     * @param string $html HTML response
     */
    public function sendResponse(string $html): void {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo $html;
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    public function sendError(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');
        echo '<div class="alert alert-danger">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
        error_log("Additional fields error: " . $message);
    }
}

// Main execution
try {
    // Validate input
    $categoryId = AdditionalFieldsByCategory::validateCategoryId($_GET['q'] ?? null);
    
    // Initialize handler
    $handler = new AdditionalFieldsByCategory($con, $categoryId);
    
    // If ID is 0 or negative, return empty
    if ($categoryId <= 0) {
        $handler->sendResponse('<div class="alert alert-info">Please select a category</div>');
        exit;
    }
    
    // Fetch fields
    $fields = $handler->fetchFields();
    
    // Log the request
    $handler->logRequest(count($fields));
    
    // Generate response
    ob_start();
    ?>
    
    <div class="table-responsive">
        <table id="sample-table-2" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th class="center">&nbsp;</th>
                    <th><strong>Field Name</strong></th>
                    <th><strong>Field Label</strong></th>
                    <th><strong>Field Type</strong></th>
                    <th style="text-align:center"><strong>Action</strong></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $index => $field): ?>
                        <?php echo $handler->generateFieldRow($field, $index); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" align="center" style="color:#F00">
                            <b>No additional fields found for this category</b>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        jQuery(function($) {
            var oTable1 = $('#sample-table-2').dataTable({
                "aoColumns": [
                    { "bSortable": false },
                    null,
                    null,
                    null,
                    { "bSortable": false }
                ]
            });
            
            $('table th input:checkbox').on('click' , function(){
                var that = this;
                $(this).closest('table').find('tr > td:first-child input:checkbox')
                    .each(function(){
                        this.checked = that.checked;
                        $(this).closest('tr').toggleClass('selected');
                    });
            });
        
            $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
            
            function tooltip_placement(context, source) {
                var $source = $(source);
                var $parent = $source.closest('table');
                var off1 = $parent.offset();
                var w1 = $parent.width();
                var off2 = $source.offset();
                var w2 = $source.width();
                
                if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) {
                    return 'right';
                }
                return 'left';
            }
        });
    </script>
    
    <?php
    $html = ob_get_clean();
    $handler->sendResponse($html);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Additional fields validation error: " . $e->getMessage());
    $handler = $handler ?? new AdditionalFieldsByCategory($con, 0);
    $handler->sendError('Invalid request: ' . $e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Additional fields runtime error: " . $e->getMessage());
    $handler = $handler ?? new AdditionalFieldsByCategory($con, 0);
    $handler->sendError('Database error: ' . $e->getMessage(), 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Additional fields unexpected error: " . $e->getMessage());
    $handler = $handler ?? new AdditionalFieldsByCategory($con, 0);
    $handler->sendError('Unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>