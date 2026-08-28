<?php
/**
 * File: ajax/showSupportCategory.php
 * Description: جلب الأسئلة الشائعة حسب التصنيف
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف التصنيف
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Invalid category ID");
}

$category_id = (int)$_POST['id'];

global $con;

// جلب الأسئلة الشائعة من قاعدة البيانات
$sql = "SELECT cf_heading, cf_content 
        FROM custom_faq 
        WHERE cf_fc_id = ? 
        ORDER BY cf_id ASC";
        
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$has_faqs = false;

while ($row = mysqli_fetch_object($result)) {
    $has_faqs = true;
    $heading = htmlspecialchars($row->cf_heading ?? '', ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($row->cf_content ?? '', ENT_QUOTES, 'UTF-8');
    $content = nl2br($content); // تحويل السطور الجديدة إلى <br>
?>
    <div class="faq-item" style="margin-bottom: 20px;">
        <p class="press-release">
            <strong style="color: #333; font-weight: bold; font-size:14px;">Q. <?php echo $heading; ?></strong>
        </p> 
        <p class="press-release">
            <span style="color: #666; font-size:12px;"><?php echo $content; ?></span>
        </p>
    </div>
<?php 
}

mysqli_stmt_close($stmt);

// إذا لم توجد أسئلة
if (!$has_faqs) {
    echo '<p class="press-release" style="color: #999; text-align: center;">No FAQs found for this category.</p>';
}
?>
<br />