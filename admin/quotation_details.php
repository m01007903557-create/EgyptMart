<?php
/**
 * File: quotation-details.php
 * Version: 2.0.0
 * Description: عرض تفاصيل طلب تسعير محدد
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "../common.php";

// التحقق من تسجيل الدخول
check_user_login();

/**
 * جلب تفاصيل طلب التسعير
 * 
 * @param mysqli $con اتصال قاعدة البيانات
 * @param string $token الرمز المميز
 * @return array|null بيانات الطلب أو null إذا لم يوجد
 */
function getQuotationDetails(mysqli $con, string $token): ?array {
    // استخراج المعرف من الرمز (إزالة أول 4 أحرف)
    $id = substr($token, 4);
    
    if (empty($id) || strlen($id) !== 32) { // md5 ينتج 32 حرف
        return null;
    }
    
    $sql = "SELECT * FROM quotation_request WHERE md5(qr_id) = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($con));
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row ?: null;
}

// التحقق من وجود token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error_msg'] = 'رابط غير صالح';
    header("Location: quotation-view.php");
    exit();
}

$token = trim($_GET['token']);
$row = getQuotationDetails($con, $token);

if (!$row) {
    $_SESSION['error_msg'] = 'طلب التسعير غير موجود';
    header("Location: quotation-view.php");
    exit();
}

// تحديث حالة القراءة إذا لم تكن مقروءة
if (isset($row['qr_status']) && $row['qr_status'] == 0) {
    $updateSql = "UPDATE quotation_request SET qr_status = 1 WHERE qr_id = ?";
    $updateStmt = mysqli_prepare($con, $updateSql);
    
    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "i", $row['qr_id']);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }
}
?>

<?php include "includes/admin-top.php" ?>

<style>
/* تحسينات إضافية للصفحة */
.details-container {
    direction: rtl;
    text-align: right;
    font-family: 'Tahoma', 'Arial', sans-serif;
}

.formItem {
    margin-bottom: 20px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 5px;
    border-right: 3px solid #4a6a8b;
}

.formItem label {
    font-weight: bold;
    color: #333;
}

.formItem .formInputBox {
    background: #fff;
    padding: 10px;
    border-radius: 3px;
    border: 1px solid #ddd;
}

h2 {
    color: #4a6a8b;
    font-size: 18px;
    margin: 0 0 10px 0;
    padding-bottom: 5px;
    border-bottom: 2px solid #4a6a8b;
}

.back-button {
    display: inline-block;
    padding: 8px 15px;
    background: #4a6a8b;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    margin-bottom: 20px;
}

.back-button:hover {
    background: #3a5a7b;
}

.quotation-content {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    line-height: 1.6;
    max-height: 400px;
    overflow-y: auto;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: normal;
}

.status-read {
    background: #5cb85c;
    color: white;
}

.status-unread {
    background: #f0ad4e;
    color: white;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px dashed #eee;
}

.info-label {
    width: 150px;
    font-weight: bold;
    color: #666;
}

.info-value {
    flex: 1;
    color: #333;
}

@media print {
    .back-button, .control_Panel .leftCon, .admin-top {
        display: none;
    }
    .formItem {
        break-inside: avoid;
    }
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <div class="details-container">
                
                <div style="margin-bottom: 20px;">
                    <a href="quotation-view.php" class="back-button">
                        <i class="icon-arrow-right"></i> العودة إلى القائمة
                    </a>
                    
                    <a href="#" onclick="window.print();" class="back-button" style="background: #5cb85c; margin-right: 10px;">
                        <i class="icon-print"></i> طباعة
                    </a>
                </div>
                
                <h2>تفاصيل طلب التسعير</h2>
                
                <div class="formItem">
                    <div class="info-row">
                        <span class="info-label">الاسم:</span>
                        <span class="info-value"><?php echo htmlspecialchars(ucwords($row['qr_name'] ?? '')); ?></span>
                    </div>
                </div>
                
                <div class="formItem">
                    <div class="info-row">
                        <span class="info-label">البريد الإلكتروني:</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($row['qr_email'] ?? ''); ?>">
                                <?php echo htmlspecialchars($row['qr_email'] ?? ''); ?>
                            </a>
                        </span>
                    </div>
                </div>
                
                <div class="formItem">
                    <div class="info-row">
                        <span class="info-label">رقم الاتصال:</span>
                        <span class="info-value">
                            <?php 
                            $phone = $row['qr_contactnumber'] ?? '';
                            if (!empty($phone)) {
                                echo '<a href="tel:' . htmlspecialchars($phone) . '">' . htmlspecialchars($phone) . '</a>';
                            } else {
                                echo 'غير محدد';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="formItem">
                    <div class="info-row">
                        <span class="info-label">التاريخ:</span>
                        <span class="info-value">
                            <?php 
                            if (!empty($row['qr_updated_date'])) {
                                echo date('Y/m/d - h:i A', strtotime($row['qr_updated_date']));
                            } else {
                                echo 'غير محدد';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="formItem">
                    <div class="info-row">
                        <span class="info-label">الحالة:</span>
                        <span class="info-value">
                            <?php if (isset($row['qr_status']) && $row['qr_status'] == 1): ?>
                                <span class="status-badge status-read">مقروء</span>
                            <?php else: ?>
                                <span class="status-badge status-unread">جديد</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <h2 style="margin-top: 30px;">طلب التسعير</h2>
                
                <div class="formItem">
                    <div class="quotation-content">
                        <?php 
                        $quotation = $row['qr_quotation'] ?? 'لا يوجد نص للطلب';
                        // تحويل النص إلى HTML مع الحفاظ على التنسيق
                        $quotation = nl2br(htmlspecialchars($quotation));
                        echo $quotation;
                        ?>
                    </div>
                </div>
                
                <?php if (!empty($row['qr_notes'])): ?>
                    <h2 style="margin-top: 30px;">ملاحظات إضافية</h2>
                    
                    <div class="formItem">
                        <div class="quotation-content">
                            <?php 
                            $notes = nl2br(htmlspecialchars($row['qr_notes']));
                            echo $notes;
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
// تحديث حالة الطلب كمقروء عند الخروج
window.addEventListener('beforeunload', function() {
    // يمكن إضافة كود AJAX هنا إذا أردت تحديث الحالة عند الخروج
});

// طباعة الصفحة
function printPage() {
    window.print();
}

// تكبير/تصغير النص
function changeFontSize(delta) {
    var content = document.querySelector('.quotation-content');
    if (content) {
        var currentSize = parseInt(window.getComputedStyle(content).fontSize);
        content.style.fontSize = (currentSize + delta) + 'px';
    }
}

// إضافة أزرار التحكم بحجم الخط
document.addEventListener('DOMContentLoaded', function() {
    var controls = document.createElement('div');
    controls.style.margin = '10px 0';
    controls.innerHTML = `
        <button onclick="changeFontSize(2)" class="btn btn-xs btn-info">
            <i class="icon-font"></i> + تكبير
        </button>
        <button onclick="changeFontSize(-2)" class="btn btn-xs btn-info" style="margin-right: 5px;">
            <i class="icon-font"></i> - تصغير
        </button>
    `;
    
    var h2Elements = document.querySelectorAll('h2');
    if (h2Elements.length > 1) {
        h2Elements[1].parentNode.insertBefore(controls, h2Elements[1].nextSibling);
    }
});
</script>

</body>
</html>

<?php ob_end_flush(); ?>