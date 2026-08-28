<?php
/**
 * File: sendBuyRequirementNotifications.php
 * Description: إرسال إشعارات طلب الشراء للموردين المهتمين بناءً على التصنيفات والموقع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تحديد معرف طلب الشراء
$pd_id = 0;
$token = '';

if (isset($_GET['token'])) {
    $token = substr($_GET['token'], 4);
} elseif (isset($_POST['br_id'])) {
    $pd_id = (int)$_POST['br_id'];
} elseif (isset($_GET['br_id'])) {
    $pd_id = (int)$_GET['br_id'];
} elseif (isset($_GET['admn_br_id'])) {
    $pd_id = (int)$_GET['admn_br_id'];
}

if ($pd_id <= 0 && empty($token)) {
    die("Invalid request parameters");
}

global $con;

// الحصول على معلومات طلب الشراء والدولة
$buy_id = rand(1, 9999) . md5((string)$pd_id);

if (!empty($token)) {
    $sql = "SELECT cn_id, cn_name, u.fname, u.lname 
            FROM user u
            INNER JOIN buy_requirement br ON br.br_u_id = u.usr_id
            INNER JOIN country c ON u.country = c.cn_id
            WHERE MD5(br.br_id) = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
} else {
    $sql = "SELECT cn_id, cn_name, u.fname, u.lname 
            FROM user u
            INNER JOIN buy_requirement br ON br.br_u_id = u.usr_id
            INNER JOIN country c ON u.country = c.cn_id
            WHERE br.br_id = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $pd_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Buy requirement not found");
}

$cn_name = $row['cn_name'] ?? '';
$cn_id = (int)($row['cn_id'] ?? 0);

// جلب الموردين المهتمين (نفس التصنيف ونفس تفضيلات الموقع)
if (!empty($token)) {
    $sql_tbi = "SELECT * FROM buylead_alert_category bac
                INNER JOIN buy_requirement br ON br.br_pc_id = bac.bac_pc_id
                INNER JOIN user u ON bac.bac_usr_id = u.usr_id
                INNER JOIN country c ON u.country = c.cn_id
                WHERE br.br_preferred_supplier_location = u.usr_br_prefLocation 
                AND MD5(br.br_id) = ?";
    
    $stmt_tbi = mysqli_prepare($con, $sql_tbi);
    mysqli_stmt_bind_param($stmt_tbi, 's', $token);
} else {
    $sql_tbi = "SELECT * FROM buy_requirement br
                INNER JOIN buylead_alert_category bac ON br.br_pc_id = bac.bac_pc_id
                INNER JOIN user u ON bac.bac_usr_id = u.usr_id
                INNER JOIN country c ON u.country = c.cn_id
                WHERE br.br_preferred_supplier_location = u.usr_br_prefLocation 
                AND br.br_id = ?";
    
    $stmt_tbi = mysqli_prepare($con, $sql_tbi);
    mysqli_stmt_bind_param($stmt_tbi, 'i', $pd_id);
}

mysqli_stmt_execute($stmt_tbi);
$result_tbi = mysqli_stmt_get_result($stmt_tbi);

// إعداد البريد الإلكتروني
$from_mail = get_adminemail();
$from_name = get_page_settings(4);
$subject = "طلب شراء جديد لمنتجات أوخدمات شركتك";
$headers = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=UTF-8\n";
$headers .= "From: " . $from_name . " <" . $from_mail . ">\n";

$sent_count = 0;

while ($row_mpc = mysqli_fetch_assoc($result_tbi)) {
    if (($row_mpc['br_approval_status'] ?? 0) != 1) {
        continue;
    }
    
    $flag = 0;
    $supplier_country_id = (int)($row_mpc['country'] ?? 0);
    $supplier_pref = $row_mpc['usr_br_prefLocation'] ?? '';
    
    // التحقق من توافق الموقع حسب التفضيلات
    if ($supplier_pref == "any") {
        $flag = 1;
    } elseif ($supplier_pref == "abroad" && $supplier_country_id != $cn_id) {
        $flag = 2;
    } elseif ($supplier_pref == "domestic" && $supplier_country_id == $cn_id) {
        $flag = 3;
    } elseif ($supplier_pref == "my_city" && $supplier_country_id == $cn_id) {
        $flag = 4;
    }
    
    if ($flag == 0) {
        continue;
    }
    
    // جلب معلومات التصنيف
    $sql_pc = "SELECT m.pc_name as main_cat, c.pc_name as cat, s.pc_name as subcat,
                      m.pc_id as main_id, c.pc_id as cat_id, s.pc_id as subcat_id
               FROM product_category m
               INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
               INNER JOIN product_category s ON c.pc_id = s.pc_parent_id
               WHERE s.pc_id = ? 
               LIMIT 1";
    
    $stmt_pc = mysqli_prepare($con, $sql_pc);
    mysqli_stmt_bind_param($stmt_pc, 'i', $row_mpc['bac_pc_id']);
    mysqli_stmt_execute($stmt_pc);
    $result_pc = mysqli_stmt_get_result($stmt_pc);
    $row_pc = mysqli_fetch_assoc($result_pc);
    mysqli_stmt_close($stmt_pc);
    
    // إنشاء معرف فريد للرابط
    $buy_id_link = rand(1, 9999) . md5((string)$pd_id);
    
    // محتوى البريد الإلكتروني
    $image_url = "http://egyptmart.shop/upload/buy_requirement/thumb/" . ($row_mpc['br_pic'] ?? '');
    if (empty($row_mpc['br_pic'])) {
        $image_url = "http://egyptmart.shop/upload/myproduct/noimage.jpg";
    }
    
    $comment = "<div style='width: 628px;height: auto;border: 9px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
    $comment .= "<div style='height: 100px; width: 100%; float: left; '>";
    $comment .= "<div style='height: 100px; width: 30%; float: left;'>";
    $comment .= "<img src='http://egyptmart.shop/images/Mlogo.png' style='width:100%;' alt='EgyptMART'>";
    $comment .= "</div>";
    $comment .= "<div style='height:100px;width:43%;float:left;'>";
    $comment .= "<h2 style='font-size: 20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;'>طلـب شراء جديد <br> من شركة شراء حقيقية</h2>";
    $comment .= "</div>";
    $comment .= "<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'>";
    $comment .= "<span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; color:#000000;'> Notification</span>";
    $comment .= "<span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>" . date('M d, Y') . "</span>";
    $comment .= "</div></div>";
    
    $comment .= "<div style='width:100%;float:center;color:blue;'>";
    $comment .= "<p style='font-size:16px;text-align: right;color:#000000'><strong> " . 
                htmlspecialchars(($row_mpc['name_prefix'] ?? '') . ' ' . ($row_mpc['fname'] ?? '') . ' ' . ($row_mpc['lname'] ?? '')) . 
                " : الســادة</strong><br> <p style='font-size:18px;font-weight:bold;text-align:center;color:#466da0;'> : شركة شراء لدينا تستقسر عن الصنف التالى لديكم</p>";
    $comment .= "</div>";
    
    $comment .= "<div style='height:auto;width:100%;float:left;margin-top:10px;'>";
    $comment .= "<div style='height:auto;width:100%;float:left;'>";
    $comment .= "<div style='width:25%;float:left;padding-top:9px;'>";
    $comment .= "<img src='" . $image_url . "' style='height:116px;width:100%;' alt='Product'>";
    $comment .= "</div>";
    $comment .= "<div style='width:66%'>";
    $comment .= "<div style='width:100%'><h3 style='font-size:18px;'>" . htmlspecialchars($row_mpc['br_pd_name'] ?? '') . "</h3></div>";
    
    $comment .= "<div style='width:100%;margin-top:10px'>";
    $comment .= "<div style='color:#000;display:inline-block'>" . measurement_unit((int)($row_mpc['br_estimate_qty_unit'] ?? 0)) . "</div>";
    $comment .= "<div style='color:#e9582c;display:inline-block'>" . htmlspecialchars($row_mpc['br_estimate_qty'] ?? '') . "</div>";
    $comment .= "<div style='display:inline-block;padding-right:0px;'> <span style='padding-right:15px;'>: أقل كمية</span></div>";
    $comment .= "</div>";
    
    $comment .= "<div style='width:100%;margin-top:10px'>";
    $comment .= "<div style='color:#000;padding-left:5px;display:inline-block;'>" . htmlspecialchars($row_mpc['br_apprx_order_currency'] ?? '') . "</div>";
    $comment .= "<div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>" . htmlspecialchars($row_mpc['br_apprx_order_value'] ?? '') . "</div>";
    $comment .= "<div style='padding-left:0px;display:inline-block;'> <span style='padding-right:15px;'>: القيمة التقريبية</span></div>";
    $comment .= "</div>";
    $comment .= "</div></div>";
    
    $comment .= "<div style='width:100%;font-size:12px;font-weight:bold;text-align:center;padding-top:15px;'>";
    $comment .= "<a href='http://www.egyptmart.shop/buyleads-details.php?id=" . $buy_id_link . "' style='text-decoration:none;color:#466da0;'> شاهد التفاصيل >> </a>";
    $comment .= "</div>";
    
    $comment .= "<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
    $comment .= "<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'>";
    $comment .= "<a href='http://egyptmart.shop/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>منتجات وخدمات</a> | ";
    $comment .= "<a href='http://egyptmart.shop/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>عروض بيع خاصة</a> | ";
    $comment .= "<a href='http://egyptmart.shop/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>طلبات شراء</a> | ";
    $comment .= "<a href='http://egyptmart.shop/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>مناقصات ومزايدات</a>";
    $comment .= "</div>";
    
    $comment .= "<div style='width:100%;padding-left: 0px;float:left;color:#808080;text-align: center;'>";
    $comment .= "<p style='margin:10px 0px 2px'>You have received this mail virtue of your opt-in subscription for product alert on <font style='color:blue;'>EgyptMART</font></p>";
    $comment .= "<p style='color:#808080; margin:0px 0px 20px;'><a href='http://egyptmart.shop/manage-buylead-alert.php' style='text-decoration:none;color:blue;'>إضغط هنا << </a> عند رغبتك تعديل منتجات وأصناف إشعارات طلبات الشراء الوارده الى بريدك</p>";
    $comment .= "</div>";
    $comment .= "</div>";
    
    // محتوى البريد للصندوق الوارد (أبسط)
    $inbox = "<div style='width: 628px;height: auto;border: 9px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
    $inbox .= "<div style='height: 100px; width: 100%; float: left; '>";
    $inbox .= "<div style='height: 100px; width: 30%; float: left;'>";
    $inbox .= "<img src='http://egyptmart.shop/images/Mlogo.png' style='width:100%;' alt='EgyptMART'>";
    $inbox .= "</div>";
    $inbox .= "<div style='height:100px;width:43%;float:left;'>";
    $inbox .= "<h2 style='font-size: 20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Products & Suppliers</h2>";
    $inbox .= "</div>";
    $inbox .= "<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'>";
    $inbox .= "<span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span>";
    $inbox .= "<span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>" . date('M d, Y') . "</span>";
    $inbox .= "</div></div>";
    
    $inbox .= "<div style='width:100%;float:left;color:#000000;'>";
    $inbox .= "<p style='font-size:16px;color:#000000'><strong>Dear " . 
              htmlspecialchars(($row_mpc['name_prefix'] ?? '') . ' ' . ($row_mpc['fname'] ?? '') . ' ' . ($row_mpc['lname'] ?? '')) . 
              "</strong>,<br><br>Latest Products relevant to your subscribed categories on EgyptMART are listed below:</p>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='height:auto;width:100%;float:left;margin-top:10px;'>";
    $inbox .= "<div style='height:auto;width:100%;float:left;'>";
    $inbox .= "<div style='width:25%;float:left;padding-top:9px;'>";
    $inbox .= "<img src='" . $image_url . "' style='height:116px;width:100%;' alt='Product'>";
    $inbox .= "</div>";
    $inbox .= "<div style='width:66%'>";
    $inbox .= "<div style='width:100%'><h3 style='font-size:18px;'>" . htmlspecialchars($row_mpc['br_pd_name'] ?? '') . "</h3></div>";
    $inbox .= "<div style='width:100%;margin-top:10px'>";
    $inbox .= "<div style='display:inline-block;padding-left:0px;'>MOQ<span style='padding-left:20px;'>:</span></div>";
    $inbox .= "<div style='color:#e9582c;display:inline-block'>" . htmlspecialchars($row_mpc['br_estimate_qty'] ?? '') . "</div>";
    $inbox .= "<div style='color:#000;display:inline-block'>" . measurement_unit((int)($row_mpc['br_estimate_qty_unit'] ?? 0)) . "</div>";
    $inbox .= "</div>";
    $inbox .= "<div style='width:100%;margin-top:10px'>";
    $inbox .= "<div style='padding-left:0px;display:inline-block;'> Price <span style='padding-left:20px;'>:</span></div>";
    $inbox .= "<div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>" . htmlspecialchars($row_mpc['br_apprx_order_value'] ?? '') . "</div>";
    $inbox .= "<div style='color:#000;padding-left:5px;display:inline-block;'>" . htmlspecialchars($row_mpc['br_apprx_order_currency'] ?? '') . "</div>";
    $inbox .= "</div></div></div>";
    
    $inbox .= "<div style='width:100%;font-size:12px;font-weight:bold;text-align:center;padding-top:15px;'>";
    $inbox .= "<a href='http://www.egyptmart.shop/buyleads-details.php?id=" . $buy_id_link . "' style='text-decoration:none;color:#466da0;'>Learn More >> </a>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
    $inbox .= "<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'>";
    $inbox .= "<a href='http://egyptmart.shop/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | ";
    $inbox .= "<a href='http://egyptmart.shop/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | ";
    $inbox .= "<a href='http://egyptmart.shop/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | ";
    $inbox .= "<a href='http://egyptmart.shop/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='width:100%;padding-left: 0px;float:left;color:#808080;text-align: center;'>";
    $inbox .= "<p style='margin:10px 0px 2px'>You have received this mail virtue of your opt-in subscription for product alert on <font style='color:blue;'>EgyptMART</font>.</p>";
    $inbox .= "<p style='color:#808080; margin:0px 0px 20px;'><a href='http://egyptmart.shop/manage-buylead-alert.php' style='text-decoration:none;color:blue;'>Click here</a> if you wish to modify to your product alert categories.</p>";
    $inbox .= "</div>";
    $inbox .= "</div>";
    
    // إرسال البريد الإلكتروني
    $to_email = stripslashes($row_mpc['email'] ?? '');
    if (!empty($to_email)) {
        mail($to_email, $subject, $comment, $headers);
        $sent_count++;
    }
    
    // إدراج الرسالة في صندوق الوارد
    $insert_sql = "INSERT INTO message SET
                   msg_from = ?,
                   msg_to = ?,
                   msg_subject = ?,
                   msg_message = ?,
                   msg_to_status = '1',
                   msg_from_status = '0',
                   msg_date = NOW()";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'iiss', 
        $row_mpc['br_u_id'], 
        $row_mpc['usr_id'], 
        $subject, 
        $inbox
    );
    mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);
}

mysqli_stmt_close($stmt_tbi);

// إعادة التوجيه حسب المعاملات
if (isset($_GET['token'])) {
    header("Location: buyreq-edit.php?token=" . urlencode($_GET['token']));
} elseif (isset($_GET['br_id'])) {
    header("Location: admin/buyreq-view.php");
} elseif (isset($_GET['admn_br_id'])) {
    header("Location: admin/buyreq-edit.php?fid=" . urlencode($_GET['admn_pd_id'] ?? ''));
} else {
    echo "Notifications sent to $sent_count recipients.";
}
?>