<?php
// sendMultiSupplierMail.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
include "../common.php";
include "../ARABYOS/email/emailVerification.php"; // تصميم البريد الإلكتروني

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    $_SESSION['last_page'] = 'compare.php';
    echo 0;
    exit;
}

$uid_indm = (int)$_SESSION['uid_indm'];

// التحقق من وجود البيانات
if (!isset($_POST['msg_image']) || !isset($_GET['suppId']) || !isset($_GET['productId'])) {
    echo 0;
    exit;
}

$data = unserialize($_POST['msg_image'] ?? '');
if (!is_array($data)) {
    $data = [];
}

// معالجة معرفات الموردين والمنتجات
$supp_arr = !empty($_GET['suppId']) ? array_unique(array_map('intval', explode(",", $_GET['suppId']))) : [];
$prod_arr = !empty($_GET['productId']) ? array_map('intval', explode(",", $_GET['productId'])) : [];

if (empty($supp_arr) || empty($prod_arr)) {
    echo 0;
    exit;
}

// تجميع المنتجات حسب المورد
$supp_prods = [];

foreach ($supp_arr as $supplier_id) {
    $sup_pro = "SELECT pd_id, pd_uid FROM products 
                WHERE pd_id IN (" . implode(',', $prod_arr) . ") 
                AND pd_uid = " . (int)$supplier_id;
    $sup_pro_res = mysqli_query($con, $sup_pro);
    
    while ($sup_product = mysqli_fetch_object($sup_pro_res)) {
        $supp_prods[$sup_product->pd_uid][] = (int)$sup_product->pd_id;
    }
}

if (empty($supp_prods)) {
    echo 0;
    exit;
}

// معالجة البيانات من POST
$msg = isset($_POST['msg_message']) ? $_POST['msg_message'] : '';
$c = isset($_POST['c']) ? mysqli_real_escape_string($con, $_POST['c']) : '';

// جلب بيانات المرسل الحالي
$sql_own = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$uid_indm}' AND bnsprof_uid = usr_id LIMIT 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

if (!$row_own) {
    echo 0;
    exit;
}

$success_count = 0;
$total_suppliers = count($supp_prods);

// حلقة لكل مورد
foreach ($supp_prods as $supplier_id => $product_ids) {
    
    // جلب بيانات المورد
    $sql_to = "SELECT * FROM user, business_profile 
               WHERE usr_id = '" . (int)$supplier_id . "' AND bnsprof_uid = usr_id LIMIT 1";
    $res_to = mysqli_query($con, $sql_to);
    $row_to = mysqli_fetch_object($res_to);
    
    if (!$row_to) {
        continue;
    }
    
    // جلب تفاصيل المنتجات
    $product_ids_string = implode(',', $product_ids);
    $sel_pro = "SELECT * FROM products WHERE pd_id IN ({$product_ids_string})";
    $s_prod = mysqli_query($con, $sel_pro);
    
    $sel_product = [];
    while ($select_product = mysqli_fetch_object($s_prod)) {
        $sel_product[] = $select_product;
    }
    
    if (empty($sel_product)) {
        continue;
    }
    
    // إعداد متغيرات البريد
    $msg_from = (int)$supplier_id;
    $msg_to = (int)$supplier_id;
    $msg_subject = 'Latest Buyer Pricing Request';
    $msg_message = wordwrap($msg, 90, "<br />\n");
    
    // بناء قائمة المنتجات في HTML
    $product_html = "";
    foreach ($sel_product as $selpro) {
        $unit_name = get_measurement_unit((int)($selpro->pd_unit ?? 0));
        $product_html .= '<div style="width:35%; overflow: hidden; float:left; margin-bottom: 20px;">
            <div style="width: 50%; float: left; overflow: hidden;">
                <img height="100" width="150" src="https://' . ($_SERVER['HTTP_HOST'] ?? 'egyptmart.shop') . '/upload/myproduct/' . rawurlencode($selpro->pd_image ?? '') . '">
            </div>
            <div style="width:50%; float: left; overflow: hidden; font-size: 1.2em;">
                <div>
                    <div style="color:rgb(70, 109, 160);">
                        ' . htmlspecialchars($selpro->pd_title ?? '') . '
                    </div>
                    <br>
                    <div>
                        MOQ : ' . (int)($selpro->pd_min_order_qty ?? 0) . ' ' . htmlspecialchars($unit_name) . '
                    </div>
                </div>
            </div>
        </div>';
    }
    
    // بناء قالب البريد الإلكتروني
    $comment = '<div class="b9_m2 b10_m2" id="detable">
        <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
            <tbody>
                <tr class="f5_m2">
                    <td class="sh_m2">
                        <span style="width:750px;word-wrap:break-word;" id="wbr">
                            <div style="height: 100px; width: 100%; float: left;">
                                <div style="height: 100px; width: 30%; float: left;">
                                    <img src="https://egyptmart.shop/images/Mlogo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                                </div>
                                <div style="height:100px;width:43%;float:left;">
                                    <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;"> طلب اليوم <br> تسعير لمشترى</h2>
                                </div>
                                <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                                    <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;"> إستفسار شراء </span>
                                    <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . date("Y/m/d") . '</span>
                                </div>
                            </div>
                            
                            <div style="width:100%;color:#000000;">
                                <p style="font-size:16px;text-align:right;color:#000000">
                                    <strong>' . htmlspecialchars(($row_to->name_prefix ?? '') . '' . ($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')) . ' : الســادة</strong>
                                </p>
                            </div>
                            
                            <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                                    <b>' . htmlspecialchars($row_own->bnsprof_compname ?? '') . ' : إستفسار شراء من</b>
                                </p>
                                
                                <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"> بيانات إتصال الراسل</p>
                                
                                <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                                    ' . htmlspecialchars(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? '')) . '<br>
                                    ' . htmlspecialchars($row_own->bnsprof_address1 ?? '') . '<br>
                                    ' . htmlspecialchars(get_city_name((int)($row_own->bnsprof_city ?? 0))) . ', ' . htmlspecialchars(get_country_name((int)($row_own->country ?? 0))) . '<br>
                                    Mobile/ Cell Phone: ' . htmlspecialchars(($row_own->country_ph_code ?? '') . '-' . ($row_own->mobile1 ?? '')) . '<br>
                                    E-mail: <a href="mailto:' . htmlspecialchars($row_own->email ?? '') . '" target="_blank">' . htmlspecialchars($row_own->email ?? '') . '</a><br>
                                </div>
                                
                                <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em" title="Enquiry Details:"> تفاصـيل الاستفســار</p>
                                
                                <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
                                    ' . $product_html . '
                                    <div style="width: 100%; float: left;">
                                        <span style="font-size:1.0em;font-weight:normal">' . stripslashes($msg_message) . '</span>
                                    </div>
                                </div>
                                
                                <div style="clear:both"></div>
                                <br>
                                
                                <div style="clear:both">
                                    <p style="line-height:1.5em;text-align:center;font-size:1.2em; background-color:#eaeaea; margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold; padding:.4em .4em .4em">
                                        .. يمكنك الرد على هذا الإستفسار من هنا 
                                        <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($row_own->email ?? '') . '&redirect=https://egyptmart.shop/my-enquiries.php" style="margin-left: 130px;"> يمكنك الرد الآن </a>
                                    </p>
                                </div>
                                <br>
                                
                                <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="line-height:20px" valign="top">
                                                <span style="blue">EgyptMART</span> الدعم الفنى
                                                <br>
                                                Call us on ' . htmlspecialchars(get_page_settings(21) ?? '') . '
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of <span style="color:blue">EgyptMART</span>.</span>
                            </div>
                            
                            <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
                            
                            <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                                <a href="https://egyptmart.shop/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">منتجات وخدمات</a> | 
                                <a href="https://egyptmart.shop/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">عروض بيع خاصة</a> | 
                                <a href="https://egyptmart.shop/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">طلبات شراء</a> | 
                                <a href="https://egyptmart.shop/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">مناقصات ومزايدات</a>
                            </div>
                            
                            <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                                <p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for product Enquiry on <font style="color:blue;">EgyptMART</font>.</p>
                                <p style="color:#808080; margin:0px 0px 20px;">
                                    <a href="https://www.egyptmart.shop/manage-buylead-alert.php" style="text-decoration:none;color:blue;">إضغط هنا</a> عند رغبتك فى تغيير أصناف إشعارات الشراء الخاصة بك
                                </p>
                            </div>
                        </span>
                    </td>   
                </tr>
            </tbody>
        </table>
    </div>';
    
    // إدراج الرسالة في قاعدة البيانات
    $sql = "INSERT INTO message
            SET 
                msg_from = '{$msg_from}',
                msg_to = '{$msg_to}',
                msg_subject = '" . mysqli_real_escape_string($con, $msg_subject) . "',
                msg_message = '" . mysqli_real_escape_string($con, $comment) . "',
                msg_date = NOW()";
    
    if (mysqli_query($con, $sql)) {
        $msg_id = mysqli_insert_id($con);
        
        // إدراج مرفقات الرسالة
        foreach ($data as $value) {
            if (isset($value->pd_image, $value->pd_title, $value->pd_min_order_qty, $value->pd_unit)) {
                $sql_ma = "INSERT INTO message_attachment
                          SET 
                              ma_msg_id = '{$msg_id}',
                              ma_file = '" . mysqli_real_escape_string($con, $value->pd_image) . "',
                              ma_file_name = '" . mysqli_real_escape_string($con, $value->pd_title) . "',
                              ma_file_quentity = '" . (int)($value->pd_min_order_qty) . "',
                              ma_file_unit = '" . (int)($value->pd_unit) . "',
                              ma_updated_date = NOW()";
                
                mysqli_query($con, $sql_ma);
            }
        }
        
        // إرسال البريد الإلكتروني
        $from_mail = get_adminemail();
        $to = user_info($msg_to, 'email');
        $from_name = get_page_settings(4);
        $subj = ($row_own->bnsprof_compname ?? '') . ' إستفسار شراء من';
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=UTF-8\n";
        $headers .= "From: " . $from_name . " <" . $from_mail . ">";
        
        if (mail($to, $subj, $comment, $headers)) {
            $success_count++;
        }
    }
}

// إرجاع النتيجة
echo ($success_count > 0) ? 1 : 0;
?>