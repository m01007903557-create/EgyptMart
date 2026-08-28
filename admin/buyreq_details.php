<?php
/**
 * File: admin/buyreq_details.php

 * Version: PHP 8.3
 * Description: عرض تفاصيل طلب الشراء في لوحة التحكم (نسخة قديمة)
 * 
 * تعرض هذه الصفحة جميع تفاصيل طلب الشراء المحدد باستخدام تنسيق قديم
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: buyreq-view.php");
    exit();
}

// تنظيف المدخلات
$token = substr(trim($_GET['token']), 4);
$token = mysqli_real_escape_string($con, $token);

if (empty($token)) {
    header("location: buyreq-view.php");
    exit();
}

// جلب بيانات طلب الشراء
$sql = "SELECT * FROM buy_requirement, measurement_unit 
        WHERE br_estimate_qty_unit = mu_id 
        AND MD5(br_id) = '{$token}' 
        LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("location: buyreq-view.php");
    exit();
}

$row = mysqli_fetch_object($res);
?>
<?php include "includes/admin-top.php" ?>

<link href="style/style.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>Buy Requirement Details</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                <br />
                
                <div class="x2-layout" style="width:980px; height:auto;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- اسم المنتج -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Name:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif; font-size:13px;">
                                                    <?php echo htmlspecialchars($row->br_pd_name ?? ''); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- التفاصيل -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Details:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif; font-size:13px;">
                                                    <?php echo nl2br(htmlspecialchars(stripslashes($row->br_requirement ?? ''))); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- الكمية التقديرية -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Estimated Quantity:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php 
                                                    $qty = !empty($row->br_estimate_qty) ? htmlspecialchars($row->br_estimate_qty) : '';
                                                    $unit = !empty($row->mu_name) ? htmlspecialchars($row->mu_name) : '';
                                                    echo $qty . " " . $unit;
                                                    ?>
                                                </div>
                                            </div>
                                            
                                            <!-- قيمة الطلب التقريبية -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Approximate order value:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php 
                                                    $currency = !empty($row->br_apprx_order_currency) ? htmlspecialchars($row->br_apprx_order_currency) : '';
                                                    $value = !empty($row->br_apprx_order_value) ? htmlspecialchars($row->br_apprx_order_value) : '';
                                                    echo $currency . " " . $value;
                                                    ?>
                                                </div>
                                            </div>
                                            
                                            <!-- استخدام المنتج -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Product application/ usage:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php echo nl2br(htmlspecialchars(stripslashes($row->br_description ?? ''))); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- الموقع الإلكتروني -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Website:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php if (!empty($row->br_website)): ?>
                                                        <a href="<?php echo htmlspecialchars($row->br_website); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($row->br_website); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- عروض الأسعار المطلوبة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Need quotations:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php echo htmlspecialchars($row->br_need_quote_for ?? ''); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- موقع المورد المفضل -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Preferred supplier location:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php
                                                    $location_text = '';
                                                    if ($row->br_preferred_supplier_location == 'abroad') {
                                                        $location_text = "Abroad Only";
                                                    } else if ($row->br_preferred_supplier_location == 'any') {
                                                        $location_text = "Anywhere";
                                                    } else if ($row->br_preferred_supplier_location == 'domestic') {
                                                        $location_text = "Domestic Only";
                                                    } else if ($row->br_preferred_supplier_location == 'my_city') {
                                                        $location_text = "My City Only";
                                                    } else {
                                                        $location_text = $row->br_preferred_supplier_location ?? '';
                                                    }
                                                    echo htmlspecialchars($location_text);
                                                    ?>
                                                </div>
                                            </div>
                                            
                                            <!-- سبب الحاجة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Why need this:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php echo nl2br(htmlspecialchars(stripslashes($row->br_need_for ?? ''))); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- تكرار الطلب -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"><h2>Requirement Frequency:</h2></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <?php echo htmlspecialchars($row->br_requirement_frequency ?? ''); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- زر العودة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:240px;"></label>
                                                <div class="formInputBox" style="width:357px;height:auto;">
                                                    <button class="btn btn-info" type="button" onclick="window.location.href='buyreq-view.php'">
                                                        <i class="icon-reply bigger-110"></i> Back to List
                                                    </button>
                                                </div>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
            
            <br clear="all" />
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>