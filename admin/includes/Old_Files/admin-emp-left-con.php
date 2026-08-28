<?php
/**
 * File: admin-emp-left-con.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: القائمة الجانبية لنظام الموظفين - تعرض خيارات مختلفة حسب صلاحيات المستخدم
 * Employee sidebar menu - displays different options based on user permissions
 * 
 * Features:
 * - قائمة منظمة للموظفين العاديين
 * - قائمة شاملة للمشرفين (admin)
 * - عداد للرسائل غير المقروءة
 * - تتبع الصفحة النشطة
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($_SESSION)) {
    exit('Direct access not allowed');
}

// Start output buffering
ob_start();

// Get current file name for active menu highlighting
$currentPath = $_SERVER['SCRIPT_NAME'] ?? '';
$currentFile = basename($currentPath);
$currentFileBase = pathinfo($currentFile, PATHINFO_FILENAME);

// Get employee ID from session or URL
$employeeId = $_GET['aid'] ?? $_SESSION['empid'] ?? '';
$randomHash = $employeeId ? rand(1000, 9999) . $employeeId : '';

// Base URL for links
$baseUrl = '';

// Helper function to check if current page matches
function isActivePage(string $fileName, string $currentFileBase, array $additionalFiles = []): string {
    if ($fileName === $currentFileBase || in_array($currentFileBase, $additionalFiles, true)) {
        return ' class="active"';
    }
    return '';
}

// Helper function to build menu link
function buildMenuLink(string $page, ?string $params = null, string $label, string $currentFileBase, array $additionalFiles = []): string {
    $url = $page . '.php';
    if ($params !== null) {
        $url .= '?' . $params;
    }
    
    $active = isActivePage($page, $currentFileBase, $additionalFiles);
    
    return '<li><a href="' . htmlspecialchars($url) . '"' . $active . '>' . htmlspecialchars($label) . '</a></li>';
}

// Get unread messages count for non-admin users
$unreadMessages = 0;
if (isset($_SESSION['id']) && (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin')) {
    $sql = "SELECT COUNT(*) as msg_count FROM message WHERE msg_to_id = ? AND msg_read = 0";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        $userId = (int)$_SESSION['id'];
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $unreadMessages = (int)($row['msg_count'] ?? 0);
        }
        mysqli_stmt_close($stmt);
    }
}
$unreadBadge = $unreadMessages > 0 ? ' (' . $unreadMessages . ')' : '';
?>

<div id="sidebar-left-container">
    <div id="sidebar-left">
        
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
            <!-- Admin View - Full Management Access -->
            
            <!-- Manage Employee Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Manage Employee</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        $params = $randomHash ? 'aid=' . urlencode($randomHash) : null;
                        echo buildMenuLink('employee-personal-details', $params, 'Personal Details', $currentFileBase);
                        echo buildMenuLink('employee-contact-details', $params, 'Contact Details', $currentFileBase);
                        echo buildMenuLink('employee-emergency-contacts', $params, 'Emergency Contacts', $currentFileBase, 
                                         ['employee-emergency-contact-add', 'employee-emergency-contact-edit']);
                        echo buildMenuLink('employee-dependents', $params, 'Dependents', $currentFileBase,
                                         ['employee-dependent-add', 'employee-dependent-edit']);
                        echo buildMenuLink('employee-immegration', $params, 'Immigration', $currentFileBase,
                                         ['employee-immegration-add', 'employee-immegration-edit']);
                        echo buildMenuLink('employee-job-details', $params, 'Job', $currentFileBase);
                        echo buildMenuLink('employee-salary-details', $params, 'Salary', $currentFileBase);
                        echo buildMenuLink('employee-report_to', $params, 'Report-to', $currentFileBase,
                                         ['employee-report_to-add', 'employee-report_to-edit']);
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Qualifications Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Qualifications</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        echo buildMenuLink('employee-workexperience', $params, 'Work Experience', $currentFileBase,
                                         ['employee-workexperience-add', 'employee-workexperience-edit']);
                        echo buildMenuLink('employee-education', $params, 'Education', $currentFileBase,
                                         ['employee-education-add', 'employee-education-edit']);
                        echo buildMenuLink('employee-skill', $params, 'Skills', $currentFileBase,
                                         ['employee-skill-add', 'employee-skill-edit']);
                        echo buildMenuLink('employee-language', $params, 'Languages', $currentFileBase,
                                         ['employee-language-add', 'employee-language-edit']);
                        echo buildMenuLink('employee-license', $params, 'License', $currentFileBase,
                                         ['employee-license-add', 'employee-license-edit']);
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Membership Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Membership</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        echo buildMenuLink('employee-membership', $params, 'Membership', $currentFileBase,
                                         ['employee-membership-add', 'employee-membership-edit']);
                        ?>
                        <li><a href="logout.php">Log out</a></li>
                    </ul>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Employee View - Limited Access -->
            
            <!-- Manage Employee Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Manage Employee</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        echo buildMenuLink('employee-personal-details', null, 'Personal Details', $currentFileBase);
                        echo buildMenuLink('employee-contact-details', null, 'Contact Details', $currentFileBase);
                        echo buildMenuLink('employee-emergency-contacts', null, 'Emergency Contacts', $currentFileBase,
                                         ['employee-emergency-contact-add', 'employee-emergency-contact-edit']);
                        echo buildMenuLink('employee-dependents', null, 'Dependents', $currentFileBase,
                                         ['employee-dependent-add', 'employee-dependent-edit']);
                        echo buildMenuLink('employee-immegration', null, 'Immigration', $currentFileBase,
                                         ['employee-immegration-add', 'employee-immegration-edit']);
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Messages Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">
                        Messages<?php echo $unreadBadge; ?>
                    </div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        $inboxActive = isActivePage('message-inbox', $currentFileBase) || 
                                      isActivePage('message-compose', $currentFileBase);
                        ?>
                        <li><a href="message-inbox.php"<?php echo $inboxActive; ?>>Inbox<?php echo $unreadBadge; ?></a></li>
                        <li><a href="message-compose.php">Compose Message</a></li>
                        <li><a href="message-sent.php">Sent Items</a></li>
                        <li><a href="message-trash.php">Trash</a></li>
                        <li><a href="message-archive.php">Archive</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Job Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Job</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php echo buildMenuLink('employee-job-details', null, 'Job', $currentFileBase); ?>
                    </ul>
                </div>
            </div>
            
            <!-- Salary Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Salary</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php echo buildMenuLink('employee-salary-details', null, 'Salary', $currentFileBase); ?>
                    </ul>
                </div>
            </div>
            
            <!-- Report-to Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Report-to</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php echo buildMenuLink('employee-report_to', null, 'Report-to', $currentFileBase,
                                               ['employee-report_to-add', 'employee-report_to-edit']); ?>
                    </ul>
                </div>
            </div>
            
            <!-- Qualifications Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title <?php echo in_array($currentFileBase, [
                        'employee-workexperience', 'employee-workexperience-add', 'employee-workexperience-edit',
                        'employee-education', 'employee-education-add', 'employee-education-edit',
                        'employee-skill', 'employee-skill-add', 'employee-skill-edit',
                        'employee-language', 'employee-language-add', 'employee-language-edit',
                        'employee-license', 'employee-license-add', 'employee-license-edit'
                    ]) ? 'active-section' : ''; ?>">
                        Qualifications
                    </div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        echo buildMenuLink('employee-workexperience', null, 'Work Experience', $currentFileBase,
                                         ['employee-workexperience-add', 'employee-workexperience-edit']);
                        echo buildMenuLink('employee-education', null, 'Education', $currentFileBase,
                                         ['employee-education-add', 'employee-education-edit']);
                        echo buildMenuLink('employee-skill', null, 'Skills', $currentFileBase,
                                         ['employee-skill-add', 'employee-skill-edit']);
                        echo buildMenuLink('employee-language', null, 'Languages', $currentFileBase,
                                         ['employee-language-add', 'employee-language-edit']);
                        echo buildMenuLink('employee-license', null, 'License', $currentFileBase,
                                         ['employee-license-add', 'employee-license-edit']);
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Membership Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Membership</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php
                        $membershipActive = isActivePage('employee-membership', $currentFileBase) ||
                                          isActivePage('employee-membership-add', $currentFileBase) ||
                                          isActivePage('employee-membership-edit', $currentFileBase);
                        ?>
                        <li><a href="employee-membership.php"<?php echo $membershipActive; ?>>Membership</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Leave Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Leave</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <li><a href="leave-apply.php">Apply</a></li>
                        <li><a href="employee-leave-summary.php">Summary</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Attendance Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Punch In/Out</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <?php echo buildMenuLink('employee-attendance', null, 'Punch In/Out', $currentFileBase); ?>
                    </ul>
                </div>
            </div>
            
            <!-- Logout Section -->
            <div class="portlet" id="top-contacts">
                <div class="portlet-decoration">
                    <div class="portlet-title">Log out</div>
                </div>
                <div class="portlet-content">
                    <ul>
                        <li><a href="logout.php">Log out</a></li>
                    </ul>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<style>
/* Sidebar Styles */
#sidebar-left-container {
    width: 250px;
    float: left;
    margin-right: 20px;
}

.portlet {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.portlet-decoration {
    background: linear-gradient(to bottom, #f6f6f6, #e9e9e9);
    border-bottom: 1px solid #ddd;
    padding: 8px 12px;
}

.portlet-title {
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.portlet-title.active-section {
    color: #007bff;
}

.portlet-content {
    background: #fff;
    padding: 10px 0;
}

.portlet-content ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.portlet-content li {
    margin: 0;
    padding: 0;
}

.portlet-content li a {
    display: block;
    padding: 8px 15px;
    color: #555;
    text-decoration: none;
    font-size: 13px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.portlet-content li a:hover {
    background-color: #f5f5f5;
    color: #007bff;
    padding-left: 20px;
}

.portlet-content li a.active {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

.portlet-content li:last-child a {
    border-bottom: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    #sidebar-left-container {
        width: 100%;
        float: none;
        margin-right: 0;
        margin-bottom: 20px;
    }
}
</style>

<?php
// Flush output buffer
ob_end_flush();
?>