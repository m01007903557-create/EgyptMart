<?php
/**
 * File: read_tenders.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: البحث المباشر عن المناقصات والمزايدات للإكمال التلقائي
 * Autocomplete search for tenders and auctions
 * 
 * Features:
 * - بحث في المناقصات النشطة حسب الكلمة المفتاحية
 * - بحث في المزايدات النشطة
 * - تصفية حسب موقع المستخدم (Cookie)
 * - عرض المناقصات ذات تاريخ انتهاء مستقبلي فقط
 * - روابط مباشرة لنتائج البحث
 */

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/search_tenders_errors.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "lib/connect.php";

/**
 * Class TendersAutocomplete
 * 
 * Handles autocomplete search for tenders and auctions
 */
class TendersAutocomplete {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Search keyword */
    private string $keyword;
    
    /** @var int|null Country ID from cookie */
    private ?int $countryId;
    
    /** @var string Current date */
    private string $currentDate;
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $keyword Search keyword
     */
    public function __construct(mysqli $database, string $keyword) {
        $this->db = $database;
        $this->keyword = $keyword;
        $this->currentDate = date('Y-m-d');
        $this->logFile = __DIR__ . '/../../logs/tenders_search.log';
        $this->ensureLogDirectory();
        $this->parseCountryCookie();
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
     * Parse country ID from cookie
     */
    private function parseCountryCookie(): void {
        $this->countryId = isset($_COOKIE['loc_id']) && is_numeric($_COOKIE['loc_id']) 
            ? (int)$_COOKIE['loc_id'] 
            : null;
    }
    
    /**
     * Build country filter string
     * 
     * @return string SQL condition
     */
    private function getCountryFilter(): string {
        if ($this->countryId !== null && $this->countryId > 0) {
            return " AND u.country = " . $this->countryId;
        }
        return '';
    }
    
    /**
     * Search for tenders
     * 
     * @return array List of tenders
     */
    public function searchTenders(): array {
        $countryFilter = $this->getCountryFilter();
        
        $sql = "SELECT td.tnd_id, td.tnd_heading 
                FROM tender td 
                INNER JOIN user u ON td.tnd_usr_id = u.usr_id 
                INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id 
                WHERE bp.bnsprof_status = '1' 
                  AND td.tnd_heading LIKE ? 
                  AND td.tnd_status = '1' 
                  {$countryFilter}
                  AND td.tnd_due_date > ?
                GROUP BY td.tnd_heading 
                ORDER BY td.tnd_heading ASC 
                LIMIT 10";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("Failed to prepare tender search: " . mysqli_error($this->db));
            return [];
        }
        
        $searchTerm = '%' . $this->keyword . '%';
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $this->currentDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $tenders = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tenders[] = [
                'id' => (int)$row['tnd_id'],
                'heading' => $row['tnd_heading']
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $tenders;
    }
    
    /**
     * Search for auctions
     * 
     * @return array List of auctions
     */
    public function searchAuctions(): array {
        $countryFilter = $this->getCountryFilter();
        
        $sql = "SELECT a.auc_id, a.auc_heading 
                FROM auction a 
                INNER JOIN user u ON a.auc_usr_id = u.usr_id 
                INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id 
                WHERE bp.bnsprof_status = '1' 
                  AND a.auc_heading LIKE ? 
                  AND a.auc_status = '1' 
                  {$countryFilter}
                  AND a.auc_due_date > ?
                GROUP BY a.auc_heading 
                ORDER BY a.auc_heading ASC 
                LIMIT 10";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("Failed to prepare auction search: " . mysqli_error($this->db));
            return [];
        }
        
        $searchTerm = '%' . $this->keyword . '%';
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $this->currentDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $auctions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $auctions[] = [
                'id' => (int)$row['auc_id'],
                'heading' => $row['auc_heading']
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $auctions;
    }
    
    /**
     * Format search results as HTML
     * 
     * @param array $tenders List of tenders
     * @param array $auctions List of auctions
     * @return string HTML output
     */
    public function formatResults(array $tenders, array $auctions): string {
        if (empty($tenders) && empty($auctions)) {
            return '<ul id="country-list" class="countrytwo">' .
                   '<li><span style="color: red">لا توجد مناقصات أو مزايدات نشطة</span></li>' .
                   '</ul>';
        }
        
        $html = '<ul id="country-list" class="countrytwo">';
        
        // Add tenders
        foreach ($tenders as $tender) {
            $searchQuery = urlencode(str_replace(' ', '+', trim($tender['heading'])));
            $html .= '<li onclick="selectCountry(\'' . htmlspecialchars($tender['heading'], ENT_QUOTES) . '\');">';
            $html .= '<a href="https://egyptmart.online/search.php?rctyp=tender&keywords=' . $searchQuery . '">';
            $html .= '<span style="color: red">' . htmlspecialchars($tender['heading'], ENT_QUOTES) . '</span>';
            $html .= '</a></li>';
        }
        
        // Add auctions
        foreach ($auctions as $auction) {
            $searchQuery = urlencode(str_replace(' ', '+', trim($auction['heading'])));
            $html .= '<li onclick="selectCountry(\'' . htmlspecialchars($auction['heading'], ENT_QUOTES) . '\');">';
            $html .= '<a href="https://egyptmart.online/search.php?rctyp=tender&keywords=' . $searchQuery . '">';
            $html .= '<span style="color: red">' . htmlspecialchars($auction['heading'], ENT_QUOTES) . '</span>';
            $html .= '</a></li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Log the search query
     * 
     * @param int $tenderCount Number of tenders found
     * @param int $auctionCount Number of auctions found
     */
    public function logSearch(int $tenderCount, int $auctionCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] Tenders Search | Keyword: '%s' | Tenders: %d | Auctions: %d | Country: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $this->keyword,
            $tenderCount,
            $auctionCount,
            $this->countryId ?? 'ALL',
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param string $response HTML response
     */
    public function sendResponse(string $response): void {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo $response;
    }
}

// Main execution
try {
    // Check if keyword is provided
    $keyword = $_POST['keyword'] ?? $_GET['keyword'] ?? '';
    
    if (empty($keyword)) {
        echo '<ul id="country-list" class="countrytwo"><li>Please enter a search term</li></ul>';
        exit;
    }
    
    // Validate keyword length
    if (strlen($keyword) < 2) {
        echo '<ul id="country-list" class="countrytwo"><li>Please enter at least 2 characters</li></ul>';
        exit;
    }
    
    if (strlen($keyword) > 100) {
        echo '<ul id="country-list" class="countrytwo"><li>Search term too long</li></ul>';
        exit;
    }
    
    // Initialize search
    $search = new TendersAutocomplete($con, $keyword);
    
    // Perform searches
    $tenders = $search->searchTenders();
    $auctions = $search->searchAuctions();
    
    // Log the search
    $search->logSearch(count($tenders), count($auctions));
    
    // Format and send response
    $response = $search->formatResults($tenders, $auctions);
    $search->sendResponse($response);
    
} catch (Exception $e) {
    error_log("Tenders autocomplete error: " . $e->getMessage());
    echo '<ul id="country-list" class="countrytwo"><li>An error occurred</li></ul>';
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>