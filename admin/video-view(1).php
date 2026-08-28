<?php
/**
 * File: video-view.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة فيديوهات الشركات
 * View and manage company videos
 * 
 * Features:
 * - عرض جميع فيديوهات الشركات النشطة
 * - تفعيل/تعطيل الفيديوهات
 * - إظهار/إخفاء الفيديوهات في الصفحة الرئيسية
 * - حذف الفيديوهات (تعطيل)
 * - حذف متعدد
 * - ترقيم الصفحات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";

// Check if user is logged in
check_admin_login();

/**
 * Class VideoListManager
 * 
 * Handles company video management operations
 */
class VideoListManager {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 20;
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Set SQL query
     * 
     * @param string $sql SQL query
     */
    public function setSql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    /**
     * Get total records count
     * 
     * @return int Total records
     */
    public function getTotalRecords(): int {
        $result = mysqli_query($this->db, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * Get records for current page
     * 
     * @return mysqli_result|false Query result
     */
    public function getRecords() {
        return mysqli_query($this->db, $this->sqlList);
    }
    
    /**
     * Delete (deactivate) video
     * 
     * @param int $id Video ID
     * @return bool Success status
     */
    public function deleteVideo(int $id): bool {
        $sql = "UPDATE company_video SET cv_status = 0 WHERE cv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Update front page display status
     * 
     * @param int $id Video ID
     * @param int $showFront Show on front page (0/1)
     * @return bool Success status
     */
    public function updateFrontStatus(int $id, int $showFront): bool {
        $sql = "UPDATE company_video SET showatfront = ? WHERE cv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $showFront, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Build delete link
     * 
     * @param int $id Video ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "video-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Build front status link
     * 
     * @param int $id Video ID
     * @param int $currentStatus Current front status
     * @return array{url: string, label: string}
     */
    public function getFrontStatusLink(int $id, int $currentStatus): array {
        $newStatus = ($currentStatus == 1) ? 0 : 1;
        $label = ($currentStatus == 1) ? 'Remove from Home' : 'Show at Home';
        
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            $url = "?action=Showfront&showatfront={$newStatus}&fid={$id}";
        } else {
            $url = "video-view.php?{$queryString}&action=Showfront&showatfront={$newStatus}&fid={$id}";
        }
        
        return [
            'url' => $url,
            'label' => $label,
            'new_status' => $newStatus
        ];
    }
    
    /**
     * Extract video ID from various video platforms
     * 
     * @param string $videoLink Video URL
     * @return string Formatted embed code
     */
    public function formatVideoEmbed(string $videoLink): string {
        if (empty($videoLink)) {
            return '<span class="text-muted">No video</span>';
        }
        
        // YouTube
        if (strpos($videoLink, 'youtube.com') !== false || strpos($videoLink, 'youtu.be') !== false) {
            return $this->formatYouTubeEmbed($videoLink);
        }
        
        // Vimeo
        if (strpos($videoLink, 'vimeo.com') !== false) {
            return $this->formatVimeoEmbed($videoLink);
        }
        
        // Dailymotion
        if (strpos($videoLink, 'dailymotion.com') !== false) {
            return $this->formatDailymotionEmbed($videoLink);
        }
        
        // Regular link
        return '<a href="' . htmlspecialchars($videoLink, ENT_QUOTES, 'UTF-8') . '" target="_blank">' 
               . htmlspecialchars(substr($videoLink, 0, 50) . (strlen($videoLink) > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8') 
               . '</a>';
    }
    
    /**
     * Format YouTube embed
     * 
     * @param string $url YouTube URL
     * @return string Embed HTML
     */
    private function formatYouTubeEmbed(string $url): string {
        $videoId = '';
        
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        
        if (!empty($videoId)) {
            return '<iframe width="200" height="150" src="https://www.youtube.com/embed/' . $videoId . '" 
                    frameborder="0" allowfullscreen></iframe>';
        }
        
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">Watch on YouTube</a>';
    }
    
    /**
     * Format Vimeo embed
     * 
     * @param string $url Vimeo URL
     * @return string Embed HTML
     */
    private function formatVimeoEmbed(string $url): string {
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">Watch on Vimeo</a>';
    }
    
    /**
     * Format Dailymotion embed
     * 
     * @param string $url Dailymotion URL
     * @return string Embed HTML
     */
    private function formatDailymotionEmbed(string $url): string {
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">Watch on Dailymotion</a>';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize video manager
$videoManager = new VideoListManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $videoManager->deleteVideo((int)$_GET['fid']);
    header("Location: video-view.php");
    exit;
}

// Handle front status update
if (isset($_GET['action']) && $_GET['action'] === "Showfront" && isset($_GET['fid']) && isset($_GET['showatfront'])) {
    $videoManager->updateFrontStatus((int)$_GET['fid'], (int)$_GET['showatfront']);
    header("Location: video-view.php");
    exit;
}

// Set pagination limits
$videoManager->limit = $pagination->getLimit(20);

// Build base query
$baseQuery = "SELECT cv.*, bf.bnsprof_compname 
              FROM company_video cv
              JOIN business_profile bf ON cv.cv_bnsprof_id = bf.bnsprof_id
              WHERE cv.cv_status = '1'
              ORDER BY cv.cv_id DESC";

// Get total records for pagination
$videoManager->setSql($baseQuery);
$totalRecords = $videoManager->getTotalRecords();

// Set pagination start
$videoManager->start = $pagination->getStart($currentPage, $videoManager->limit, $totalRecords);

// Get records for current page
$records = $videoManager->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $videoManager->start + 1;
$displayEnd = min($videoManager->start + $videoManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "video-view.php";
$pageString = "?limit=" . $videoManager->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $videoManager->deleteVideo((int)$id);
    }
    header("Location: video-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="video-view.php">Company Videos</a>
                    </li>
                    <li class="active">View Videos</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Company Videos
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Total: <?php echo $totalRecords; ?> videos
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected videos?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" id="selectAll">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th class="center"><strong>Video Preview</strong></th>
                                            <th class="center"><strong>Company Name</strong></th>
                                            <th class="center"><strong>Front Page Status</strong></th>
                                            <th class="center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <?php
                                                $frontLink = $videoManager->getFrontStatusLink((int)$row->cv_id, (int)($row->showatfront ?? 0));
                                                ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->cv_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td class="center">
                                                        <?php echo $videoManager->formatVideoEmbed($row->cv_video_link ?? ''); ?>
                                                    </td>
                                                    <td class="center">
                                                        <strong><?php echo htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if (!empty($row->cv_title)): ?>
                                                            <br>
                                                            <small><?php echo htmlspecialchars($row->cv_title, ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <?php if ((int)($row->showatfront ?? 0) == 1): ?>
                                                            <span class="label label-success">Showing on Home</span>
                                                        <?php else: ?>
                                                            <span class="label label-default">Hidden</span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <a href="<?php echo $frontLink['url']; ?>" class="btn btn-xs btn-info" style="margin-top:5px;">
                                                            <?php echo $frontLink['label']; ?>
                                                        </a>
                                                    </td>
                                                    <td class="center">
                                                        <div class="btn-group">
                                                            <a href="<?php echo $videoManager->getDeleteLink((int)$row->cv_id); ?>" 
                                                               class="btn btn-xs btn-danger" title="Delete"
                                                               onclick="return confirm('Are you sure you want to delete this video?')">
                                                                <i class="icon-trash bigger-120"></i> Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    No company videos found.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $displayRange; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    // Generate pagination links
                                    $totalPages = ceil($totalRecords / $videoManager->limit);
                                    if ($totalPages > 1) {
                                        echo '<div class="dataTables_paginate paging_bootstrap">';
                                        echo '<ul class="pagination">';
                                        
                                        // Previous button
                                        if ($currentPage > 1) {
                                            echo '<li class="prev"><a href="?page=' . ($currentPage - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
                                        } else {
                                            echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
                                        }
                                        
                                        // Page numbers
                                        for ($i = 1; $i <= $totalPages; $i++) {
                                            $activeClass = ($i == $currentPage) ? 'active' : '';
                                            echo '<li class="' . $activeClass . '"><a href="?page=' . $i . '">' . $i . '</a></li>';
                                        }
                                        
                                        // Next button
                                        if ($currentPage < $totalPages) {
                                            echo '<li class="next"><a href="?page=' . ($currentPage + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
                                        } else {
                                            echo '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
                                        }
                                        
                                        echo '</ul>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript includes -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!-- DataTables -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },
                { "bSortable": false },
                null,
                null,
                { "bSortable": false }
            ],
            "bPaginate": false, // We're using custom pagination
            "bInfo": false, // We're using custom info
            "bFilter": true,
            "bSort": true
        });
        
        // Select all checkbox functionality
        $('#selectAll').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // Tooltip placement
        $('[data-rel="tooltip"]').tooltip({
            placement: function(context, source) {
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
        
        // Search enhancement
        $('#sample-table-2_filter input').attr('placeholder', 'Search videos...');
    });
</script>

<style>
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-group .btn {
        border-radius: 3px !important;
        padding: 3px 8px;
        font-size: 11px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-header {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e5ec;
        border-radius: 4px;
    }
    
    .table-header button {
        margin-right: 5px;
    }
    
    .pagination {
        margin: 0;
        float: right;
    }
    
    .dataTables_info {
        padding-top: 8px;
        color: #666;
    }
    
    .label {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: normal;
    }
    
    .label-success {
        background-color: #5cb85c;
        color: white;
    }
    
    .label-default {
        background-color: #777;
        color: white;
    }
    
    iframe {
        max-width: 200px;
        max-height: 150px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>