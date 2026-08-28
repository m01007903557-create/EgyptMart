<?php
/**
 * File: product-doc-list.php

 * Description: عرض رابط ملف PDF الخاص بالمنتج مع خيار الحذف
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("<!-- Unauthorized -->");
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    http_response_code(400);
    die("<!-- Invalid product ID -->");
}

$product_id = (int)$_GET['pid'];
$user_id = (int)$_SESSION['uid_indm'];

global $con;

// جلب معلومات الملف مع التحقق من ملكية المنتج
$sql = "SELECT pd_id, pd_pdf_attach, pd_uid FROM products WHERE pd_id = ? AND pd_uid = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $product_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

// التحقق من وجود المنتج والملف
if ($row && !empty($row->pd_pdf_attach)) {
    $pd_pdf_attach = htmlspecialchars($row->pd_pdf_attach, ENT_QUOTES, 'UTF-8');
    $file_name = basename($pd_pdf_attach);
    $display_name = htmlspecialchars(substr($file_name, 0, 27), ENT_QUOTES, 'UTF-8');
    
    // التحقق من وجود الملف فعلياً
    $file_path = __DIR__ . "/upload/productdoc/" . $file_name;
    $file_exists = file_exists($file_path) && is_file($file_path);
    
    if ($file_exists):
?>
<span id="old_doc_form0" style="width:100px;">
    <p id="filename" class="margin" style="font-family: arial; font-size: 12px;">
        <b>
            <img src="images/att.gif" width="16" height="17" alt="Attachment">
            <a href="product-doc-download.php?pid=<?php echo $product_id; ?>" target="_blank">
                <?php echo $display_name; ?>.pdf
            </a>
        </b>&nbsp;&nbsp;&nbsp;
        <a onclick="DelTempdoc(<?php echo $product_id; ?>)" 
           style="text-decoration:none; text-align:center; cursor:pointer;"
           title="Delete PDF">
            <img src="images/remove.gif" align="absmiddle" width="44" height="10" alt="Delete">
        </a>
    </p>
</span>
<?php 
    endif;
} else {
    // يمكن عرض رسالة عدم وجود ملف (اختياري)
    // echo '<p class="margin" style="font-family:arial; font-size:12px; color:#999;">No PDF attached</p>';
}
?>