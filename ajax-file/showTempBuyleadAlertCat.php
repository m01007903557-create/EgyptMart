<?php
/**
 * File: ajax/displaySelectedBuyCategories.php
 * Description: عرض التصنيفات المحددة لتنبيهات الشراء
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

$user_id = (int)$_SESSION['uid_indm'];

global $con;

// جلب التصنيفات المحددة من الجدول المؤقت
$sql = "SELECT pc.pc_id, pc.pc_name 
        FROM temp_buylead_alert_cat tbac
        INNER JOIN product_category pc ON tbac.tbac_pc_id = pc.pc_id
        WHERE tbac.tbac_usr_id = ?
        AND pc.pc_status = 1
        ORDER BY pc.pc_name ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$has_categories = false;

while ($row = mysqli_fetch_object($result)) {
    $has_categories = true;
    $pc_id = (int)$row->pc_id;
    $pc_name = htmlspecialchars($row->pc_name ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <div class="setcat" id="<?php echo $pc_id; ?>" style="display:block;">
        &bull;&nbsp;<?php echo $pc_name; ?>
        <a href="javascript:remove(<?php echo $pc_id; ?>)">
            <img src="images/remove.gif" height="10" hspace="6" width="44" alt="Remove">
        </a>
    </div>
    <?php
}

mysqli_stmt_close($stmt);

// إذا لم تكن هناك تصنيفات، يمكن عرض رسالة
if (!$has_categories) {
    echo '<div class="setcat" style="display:block; color:#666;">&bull;&nbsp;No categories selected</div>';
}
?>