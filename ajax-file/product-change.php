<?php
/**
 * File: ajax/product-change.php

 * Description: عرض عناصر التحكم في حالة المنتج (دفع للأعلى/منتج مميز)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

$uid = $_SESSION['uid_indm'] ?? 0;

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die("Invalid product ID");
}

$pid = (int)$_GET['id'];
$current_user = (int)$_SESSION['uid_indm'];

global $con;

// جلب بيانات المنتج مع التحقق من ملكية المستخدم
$pdsql = "SELECT pd_id, pd_pushed_top, pd_hot 
          FROM products 
          WHERE pd_id = ? AND pd_uid = ? AND pd_status = '1' 
          LIMIT 1";

$stmt = mysqli_prepare($con, $pdsql);
mysqli_stmt_bind_param($stmt, 'ii', $pid, $current_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pdrow = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$pdrow) {
    die("Product not found or access denied");
}

$site_name = htmlspecialchars(get_page_settings(4), ENT_QUOTES, 'UTF-8');
$pushed_top = (int)($pdrow->pd_pushed_top ?? 0);
$hot_status = (int)($pdrow->pd_hot ?? 0);
?>

<div class="c3 topbox">
    <?php if ($pushed_top == 1): ?>
        <p id="p2tp" class="pued f1" style="">Pushed to Top</p>
    <?php else: ?>
        <a onclick="pushedtotop(<?php echo $pid; ?>);" 
           class="b-img pht f2" 
           title="Push this product to top and get better visibility on <?php echo $site_name; ?> platform." 
           id="p2t_46536582" 
           style="cursor:pointer;"></a>
    <?php endif; ?>
    
    <br>
    
    <p id="p2tw_46536582" class="pw f2" style="display: none;">Please Wait...</p>
    <p id="p2tp_46536582" class="pued f1" style="display: none;">Pushed to Top</p>
    
    <input class="mark cpr" id="hotpd" name="hotpd" type="checkbox" 
           onClick="markhot(<?php echo $pid; ?>)" 
           <?php echo ($hot_status == 1) ? 'checked="checked"' : ''; ?>>
    
    <?php if ($hot_status == 1): ?>
        <label for="hot_47089835" class="hp htgr cpr" id="hotmsg_47089835">HOT Product</label>
    <?php else: ?>
        <label for="hot_46536582" class="hp cpr" id="hotmsg_46536582">Mark as HOT</label>        
    <?php endif; ?>
</div>