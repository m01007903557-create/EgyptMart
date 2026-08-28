<?php
/**
 * File: product-email-notification.php
 * Description: إرسال إشعارات المنتج للمستخدمين المهتمين بناءً على التصنيفات والموقع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

global $con;

// تحديد معرف المنتج
$pd_id = 0;
$token = '';
$buss_id = 0;

if (isset($_GET['token'])) {
    $token = substr($_GET['token'], 4);
} elseif (isset($_POST['pd_id']) && is_numeric($_POST['pd_id'])) {
    $pd_id = (int)$_POST['pd_id'];
} elseif (isset($_GET['pd_id']) && is_numeric($_GET['pd_id'])) {
    $pd_id = (int)$_GET['pd_id'];
} elseif (isset($_GET['admn_pd_id']) && is_numeric($_GET['admn_pd_id'])) {
    $pd_id = (int)$_GET['admn_pd_id'];
}

if ($pd_id <= 0 && empty($token)) {
    die("Invalid request parameters");
}

// الحصول على معلومات المنتج والدولة
$buy_id = rand(1, 9999) . md5((string)$pd_id);

if (!empty($token)) {
    $sql = "SELECT bp.bnsprof_id 
            FROM business_profile bp
            INNER JOIN products p ON bp.bnsprof_uid = p.pd_uid
            WHERE MD5(p.pd_id) = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $suser = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $buss_id = (int)($suser['bnsprof_id'] ?? 0);
    
    $sql = "SELECT cn_id, cn_name, u.fname, u.lname 
            FROM user u
            INNER JOIN products p ON p.pd_uid = u.usr_id
            INNER JOIN country c ON u.country = c.cn_id
            WHERE MD5(p.pd_id) = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
} else {
    $buss_id = isset($_GET['buss_id']) ? (int)$_GET['buss_id'] : 0;
    
    $sql = "SELECT cn_id, cn_name, u.fname, u.lname 
            FROM user u
            INNER JOIN products p ON p.pd_uid = u.usr_id
            INNER JOIN country c ON u.country = c.cn_id
            WHERE p.pd_id = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $pd_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Product not found");
}

$cn_name = $row['cn_name'] ?? '';
$cn_id = (int)($row['cn_id'] ?? 0);

// جلب المستخدمين المهتمين (نفس التصنيف ونفس تفضيلات الموقع)
if (!empty($token)) {
    $sql_tbi = "SELECT * FROM selloffer_alert_category sac
                INNER JOIN products p ON p.pd_subcat_id = sac.sac_pc_id
                INNER JOIN user u ON sac.sac_usr_id = u.usr_id
                INNER JOIN country c ON u.country = c.cn_id
                WHERE p.pd_preferred_buyer_location = u.usr_so_prefLocation 
                AND MD5(p.pd_id) = ?";
    
    $stmt_tbi = mysqli_prepare($con, $sql_tbi);
    mysqli_stmt_bind_param($stmt_tbi, 's', $token);
} else {
    $sql_tbi = "SELECT * FROM selloffer_alert_category sac
                INNER JOIN products p ON p.pd_subcat_id = sac.sac_pc_id
                INNER JOIN user u ON sac.sac_usr_id = u.usr_id
                INNER JOIN country c ON u.country = c.cn_id
                INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                WHERE p.pd_preferred_buyer_location = u.usr_so_prefLocation 
                AND p.pd_id = ?";
    
    $stmt_tbi = mysqli_prepare($con, $sql_tbi);
    mysqli_stmt_bind_param($stmt_tbi, 'i', $pd_id);
}

mysqli_stmt_execute($stmt_tbi);
$result_tbi = mysqli_stmt_get_result($stmt_tbi);

// إعداد البريد الإلكتروني
$from_mail = get_adminemail();
$from_name = get_page_settings(4);
$subject = "منتجات جملة وموردين جدد تهمـك";
$headers = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=UTF-8\n";
$headers .= "From: " . $from_name . " <" . $from_mail . ">\n";

$sent_count = 0;

while ($row_mpc = mysqli_fetch_assoc($result_tbi)) {
    if (!in_array($row_mpc['pd_status'] ?? '', ['0', '1'])) {
        continue;
    }
    
    $flag = 0;
    $supplier_country_id = (int)($row_mpc['country'] ?? 0);
    $supplier_pref = $row_mpc['usr_so_prefLocation'] ?? '';
    
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
    mysqli_stmt_bind_param($stmt_pc, 'i', $row_mpc['sac_pc_id']);
    mysqli_stmt_execute($stmt_pc);
    $result_pc = mysqli_stmt_get_result($stmt_pc);
    $row_pc = mysqli_fetch_assoc($result_pc);
    mysqli_stmt_close($stmt_pc);
    
    // إنشاء معرف فريد للرابط
    $product_link_id = rand(1000, 9999) . md5((string)$row_mpc['pd_id']);
    $company_link_id = rand(1000, 9999) . md5((string)$buss_id);
    
    // محتوى البريد الإلكتروني الرئيسي
    $image_url = "https://egyptmart.shop/upload/myproduct/thumb/" . ($row_mpc['pd_image'] ?? '');
    if (empty($row_mpc['pd_image'])) {
        $image_url = "https://egyptmart.shop/upload/myproduct/noimage.jpg";
    }
    
    $comment = "<div style='width:628px; height:auto; border:9px solid #92AED2; float:left; padding:10px; margin-top:10px;'>";
    $comment .= "<div style='height:100px; width:100%; float:left;'>";
    $comment .= "<div style='height:100px; width:30%; float:left;'>";
    $comment .= "<img src='https://egyptmart.shop/images/Mlogo.png' style='width:100%;' alt='EgyptMART'>";
    $comment .= "</div>";
    $comment .= "<div style='height:100px; width:43%; float:left;'>";
    $comment .= "<h2 style='font-size:20px; color:#0e14da; text-align:center; margin-top:0px; margin-bottom:0px;'><br></h2>";
    $comment .= "</div>";
    $comment .= "<div style='min-height:100px; width:27%; float:right; padding-top:3px;'>";
    $comment .= "<span style='font-size:15px; float:right; padding-bottom:0px; clear:both; font-weight:bold; color:#000000;'> Notification</span>";
    $comment .= "<span style='float:right; font-size:13px; padding-top:0px; clear:both; color:#000000;'>" . date('M d, Y') . "</span>";
    $comment .= "</div></div>";
    
    $comment .= "<div style='width:100%; float:center; font-weight:bold; color:#000000;'>";
    $comment .= "<p style='font-size:19px; text-align:right; color:#000000'><strong>" . 
                htmlspecialchars(($row_mpc['name_prefix'] ?? '') . ' ' . ($row_mpc['fname'] ?? '') . ' ' . ($row_mpc['lname'] ?? '')) . 
                ": السادة</strong>,<br><br>: منتج جديد يتم عرضه الآن طبقا لإهتمامات شرائك</p>";
    $comment .= "</div>";
    
    $comment .= "<div style='height:auto; width:100%; float:left; margin-top:10px;'>";
    $comment .= "<div style='height:auto; width:100%; float:left;'>";
    $comment .= "<div style='width:25%; float:left; padding-top:9px;'>";
    $comment .= "<img src='" . $image_url . "' style='height:116px; width:100%;' alt='Product'>";
    $comment .= "</div>";
    $comment .= "<div style='width:66%'>";
    $comment .= "<div style='width:100%'><h3 style='padding-left:20px; display:inline-block; font-size:16px;'>" . htmlspecialchars($row_mpc['pd_title'] ?? '') . "</h3></div>";
    
    $comment .= "<div style='width:100%; margin-top:10px;'>";
    $comment .= "<div style='display:inline-block; padding-left:20px;'>أقل كمية :<span style='padding-left:20px;'>:</span></div>";
    $comment .= "<div style='color:#e9582c; display:inline-block'>" . htmlspecialchars($row_mpc['pd_min_order_qty'] ?? '') . "</div>";
    $comment .= "<div style='color:#000; display:inline-block'>" . measurement_unit((int)($row_mpc['pd_unit'] ?? 0)) . "</div>";
    $comment .= "</div>";
    
    $comment .= "<div style='width:100%; margin-top:10px;'>";
    $comment .= "<div style='padding-left:20px; display:inline-block;'>الـسعـر :<span style='padding-left:20px;'>:</span></div>";
    $comment .= "<div style='color:#e9582c; font-weight:bold; font-size:15px; line-height:15px; display:inline-block;'>" . htmlspecialchars($row_mpc['pd_fob_price'] ?? '') . "</div>";
    $comment .= "<div style='color:#000; padding-left:5px; display:inline-block;'>" . htmlspecialchars($row_mpc['cn_currency'] ?? '') . "</div>";
    $comment .= "</div>";
    $comment .= "</div></div>";
    
    $comment .= "<div style='width:100%; font-size:12px; font-weight:bold; text-align:center; padding-top:15px;'>";
    $comment .= "<a href='https://egyptmart.shop/company/product-details.php?token=" . $product_link_id . "&c=" . $company_link_id . "' ";
    $comment .= "style='text-decoration:none; color:#0e14da; padding-right:2px;'>شاهد التفاصيل >></a>";
    $comment .= "</div>";
    
    $comment .= "<div style='width:100%; float:left; text-align:center; padding-top:10px; padding-bottom:10px;'>";
    $comment .= "<a href='https://www.egyptmart.shop/sign-in.php?email=&redirect=https://www.egyptmart.shop/product-list.php' ";
    $comment .= "style='color:#00c118; text-decoration:none; font-size:18px; font-weight:bold;'>عرض منتجات جديدة</a> | ";
    $comment .= "<a href='https://www.egyptmart.shop/sign-in.php?email=&redirect=https://www.egyptmart.shop/post-sell-offer.php' ";
    $comment .= "style='color:#00c118; text-decoration:none; font-size:18px; font-weight:bold;'>نشـر عروض خاصة</a> | ";
    $comment .= "<a href='https://www.egyptmart.shop/sign-in.php?email=&redirect=https://www.egyptmart.shop/post-buy-req.php' ";
    $comment .= "style='color:#00c118; text-decoration:none; font-size:18px; font-weight:bold;'>نشـر طلب تسعـير</a> | ";
    $comment .= "<a href='https://www.egyptmart.shop/sign-in.php?email=&redirect=https://www.egyptmart.shop/post-tender.php' ";
    $comment .= "style='color:#00c118; text-decoration:none; font-size:18px; font-weight:bold;'>نشـر مناقصات مجانا</a>";
    $comment .= "</div>";
    
    $comment .= "<div style='width:100%; padding-left:0px; float:left; color:#808080; text-align:center;'>";
    $comment .= "<p style='margin:10px 0px 2px'>You have received this mail virtue of your opt-in subscription for product alert on ";
    $comment .= "<font style='color:blue;'>EgyptMART</font>.</p>";
    $comment .= "<p style='color:#808080; margin:0px 0px 20px;'>";
    $comment .= "<a href='https://egyptmart.shop/sign-in.php?email=&redirect=https://egyptmart.shop/manage-selloffer-alert.php' ";
    $comment .= "style='text-decoration:none; color:blue;'>إضغط هنا</a> عند تعديل أصناف أحدث إشعارات منتجات أو خدمات البيع الوارده الى بريدك</p>";
    $comment .= "</div>";
    $comment .= "</div>";
    
    // محتوى البريد للصندوق الوارد
    $inbox = "<div style='width:628px; height:auto; border:9px solid #92AED2; float:left; padding:10px; margin-top:10px;'>";
    $inbox .= "<div style='height:100px; width:100%; float:left;'>";
    $inbox .= "<div style='height:100px; width:30%; float:left;'>";
    $inbox .= "<img src='https://egyptmart.shop/images/logo.png' style='width:100%;' alt='EgyptMART'>";
    $inbox .= "</div>";
    $inbox .= "<div style='height:100px; width:43%; float:left;'>";
    $inbox .= "<h2 style='font-size:20px; color:#0e14da; text-align:center; margin-top:0px; margin-bottom:0px;'>آخر إهتماماتك من<br> المنتجات وشركات الموردون</h2>";
    $inbox .= "</div>";
    $inbox .= "<div style='min-height:100px; width:27%; float:right; padding-top:3px;'>";
    $inbox .= "<span style='font-size:15px; float:right; padding-bottom:0px; clear:both; font-weight:bold; color:#000000;'> Notification</span>";
    $inbox .= "<span style='float:right; font-size:13px; padding-top:0px; clear:both; color:#000000;'>" . date('M d, Y') . "</span>";
    $inbox .= "</div></div>";
    
    $inbox .= "<div style='width:100%; float:left; color:#000000;'>";
    $inbox .= "<p style='font-size:16px; color:#000000'><strong>Dear " . 
              htmlspecialchars(($row_mpc['name_prefix'] ?? '') . ' ' . ($row_mpc['fname'] ?? '') . ' ' . ($row_mpc['lname'] ?? '')) . 
              "</strong>,<br><br>: منتج جديد يتم عرضه الآن طبقا لإهتمامات شرائك</p>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='height:auto; width:100%; float:left; margin-top:10px;'>";
    $inbox .= "<div style='height:auto; width:100%; float:left;'>";
    $inbox .= "<div style='width:25%; float:left; padding-top:9px;'>";
    $inbox .= "<img src='" . $image_url . "' style='height:116px; width:100%;' alt='Product'>";
    $inbox .= "</div>";
    $inbox .= "<div style='width:66%'>";
    $inbox .= "<div style='width:100%'><h3 style='font-size:18px;'>" . htmlspecialchars($row_mpc['pd_title'] ?? '') . "</h3></div>";
    
    $inbox .= "<div style='width:100%; margin-top:10px;'>";
    $inbox .= "<div style='display:inline-block; padding-left:0px;'>MOQ<span style='padding-left:20px;'>:</span></div>";
    $inbox .= "<div style='color:#e9582c; display:inline-block'>" . htmlspecialchars($row_mpc['pd_min_order_qty'] ?? '') . "</div>";
    $inbox .= "<div style='color:#000; display:inline-block'>" . measurement_unit((int)($row_mpc['pd_unit'] ?? 0)) . "</div>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='width:100%; margin-top:10px;'>";
    $inbox .= "<div style='padding-left:0px; display:inline-block;'>Price<span style='padding-left:20px;'>:</span></div>";
    $inbox .= "<div style='color:#e9582c; font-weight:bold; font-size:15px; line-height:15px; display:inline-block;'>" . htmlspecialchars($row_mpc['pd_fob_price'] ?? '') . "</div>";
    $inbox .= "<div style='color:#000; padding-left:5px; display:inline-block;'>" . htmlspecialchars($row_mpc['cn_currency'] ?? '') . "</div>";
    $inbox .= "</div>";
    $inbox .= "</div></div>";
    
    $inbox .= "<div style='width:100%; font-size:12px; font-weight:bold; text-align:center; padding-top:15px;'>";
    $inbox .= "<a href='https://egyptmart.shop/company/product-details.php?token=" . $product_link_id . "&c=" . $company_link_id . "' ";
    $inbox .= "style='text-decoration:none; color:#0e14da; padding-right:2px;'>شاهد التفاصيل >></a>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='height:2px; width:100%; float:left; border-bottom:3px dotted #D8AED8;'></div>";
    $inbox .= "<div style='width:100%; float:left; text-align:center; padding-top:10px; padding-bottom:10px;'>";
    $inbox .= "<a href='https://egyptmart.shop/dir.php' style='color:#0e14da; text-decoration:none; font-size:18px; font-weight:bold;'>Product & Suppliers</a> | ";
    $inbox .= "<a href='https://egyptmart.shop/sale-offers.php' style='color:#0e14da; text-decoration:none; font-size:18px; font-weight:bold;'>Sale Offers</a> | ";
    $inbox .= "<a href='https://egyptmart.shop/buyleads.php' style='color:#0e14da; text-decoration:none; font-size:18px; font-weight:bold;'>Buy Requests</a> | ";
    $inbox .= "<a href='https://egyptmart.shop/tenders.php' style='color:#0e14da; text-decoration:none; font-size:18px; font-weight:bold;'>Tenders</a>";
    $inbox .= "</div>";
    
    $inbox .= "<div style='width:100%; padding-left:0px; float:left; color:#808080; text-align:center;'>";
    $inbox .= "<p style='margin:10px 0px 2px'>You have received this mail virtue of your opt-in subscription for product alert on ";
    $inbox .= "<font style='color:blue;'>EgyptMART</font>.</p>";
    $inbox .= "<p style='color:#808080; margin:0px 0px 20px;'>";
    $inbox .= "<a href='https://egyptmart.shop/manage-selloffer-alert.php' style='text-decoration:none; color:blue;'>Click here</a> if you wish to modify to your product alert categories.</p>";
    $inbox .= "</div>";
    $inbox .= "</div>";
    
    // إرسال البريد الإلكتروني
    $to_email = stripslashes($row_mpc['email'] ?? '');
    if (!empty($to_email) && filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        sendSMTPMail($to_email, $subject, $comment, $headers);
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
        $row_mpc['pd_uid'], 
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
    header("Location: product-edit.php?token=" . urlencode($_GET['token']));
} elseif (isset($_GET['pd_id'])) {
    header("Location: admin/product-view.php");
} elseif (isset($_GET['admn_pd_id'])) {
    header("Location: admin/product-edit.php?fid=" . urlencode($_GET['admn_pd_id']));
} else {
    echo "Notifications sent to $sent_count recipients.";
}
?>