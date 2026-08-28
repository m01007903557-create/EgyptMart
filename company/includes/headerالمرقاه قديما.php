<?php
// company/includes/header.php - نسخة PHP 8.3 متوافقة مع ltr للهيدر فقط
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || $con === null) {
    die("خطأ: اتصال قاعدة البيانات غير موجود");
}

$c = isset($_GET['c']) ? mysqli_real_escape_string($con, $_GET['c']) : '';
$id = isset($_GET['c']) ? substr($_GET['c'], 4) : '';
$flag = isset($_GET['flag']) ? mysqli_real_escape_string($con, $_GET['flag']) : '';

// جلب بيانات الشركة
$sql = "SELECT * FROM business_profile, user, ownership_type, revenue_turnover 
        WHERE bnsprof_uid = usr_id AND md5(bnsprof_id) = '{$id}'";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

// جلب بيانات المستخدم الحالي
$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$sql_usr = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$uid}' AND bnsprof_uid = '{$uid}'";
$res_usr = mysqli_query($con, $sql_usr);
$row_usr = mysqli_fetch_object($res_usr);

// تحديد اسم الملف الحالي
$path = $_SERVER['SCRIPT_NAME'];
$pos = strrpos($path, '/');
$file = substr($path, ($pos + 1));
$dotpos = strrpos($file, '.');
$file = substr($file, 0, ($dotpos));

$uid = (int)($row->usr_id ?? 0);
$banner = [];

// جلب البانرات حسب نوع الصفحة
if ($file == "profile") {
    $bsql = "SELECT * FROM company_banner cb 
             JOIN business_profile bf ON cb.cb_bnsprof_id = bf.bnsprof_id 
             WHERE bnsprof_uid = '{$row->bnsprof_uid}' AND cb_image != ''";
    $bres = mysqli_query($con, $bsql);
    
    while ($row_t = mysqli_fetch_object($bres)) {
        $img = "https://egyptmart.shop/upload/company_banner/" . $row_t->cb_image;
        $img_2 = "/home/u397968200/domains/egyptmart.shop/public_html/upload/company_banner/" . $row_t->cb_image;
        
        if (file_exists($img_2)) {
            $banner[] = $img;
        }
    }
    
    $bstyp = explode(",", $row->bnsprof_businesstype ?? '');
    $business_type = [];
    
    if (!empty($bstyp) && $bstyp[0] != '') {
        $bt = "SELECT * FROM business_type WHERE bsntyp_status = '1' AND bsntyp_id IN (" . implode(',', array_map('intval', $bstyp)) . ")";
        $btype = mysqli_query($con, $bt);
        while ($bus_type = mysqli_fetch_object($btype)) {
            $business_type[] = $bus_type->bsntyp_title;
        }
    }
} else {
    $bsql = "SELECT * FROM products WHERE pd_uid = '{$row->bnsprof_uid}' AND pd_image != ''";
    $bres = mysqli_query($con, $bsql);
    
    while ($row_t = mysqli_fetch_object($bres)) {
        $pdImage = explode(',', $row_t->pd_image);
        if (!empty($pdImage[0])) {
            $img = "https://egyptmart.shop/upload/myproduct/thumb/" . $pdImage[0];
            $img_2 = "/home/u397968200/domains/egyptmart.shop/public_html/upload/myproduct/thumb/" . $pdImage[0];
            
            if (file_exists($img_2)) {
                $banner[] = $img;
            }
        }
    }
}

// جلب قائمة الدول
$country = [];
$coun = "SELECT * FROM country";
$count = mysqli_query($con, $coun);
while ($countr = mysqli_fetch_object($count)) {
    $country[] = $countr->cn_name;
}

// جلب معلومات المدينة
$ct = "SELECT * FROM city WHERE ct_id = '" . (int)($row->bnsprof_city ?? 0) . "'";
$cit = mysqli_query($con, $ct);
$citys = mysqli_fetch_object($cit);

// جلب معلومات الولاية
$st = "SELECT * FROM states WHERE state_id = '" . (int)($row->bnsprof_state ?? 0) . "'";
$sta = mysqli_query($con, $st);
$states = mysqli_fetch_object($sta);

// جلب المنتجات
$prod = [];
$bsql = "SELECT DISTINCT pd_subcat_id FROM products WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "'";
$bres = mysqli_query($con, $bsql);
while ($row_t = mysqli_fetch_object($bres)) {
    $asd = "SELECT * FROM product_category WHERE pc_id = '" . (int)($row_t->pd_subcat_id ?? 0) . "'";
    $def = mysqli_query($con, $asd);
    while ($row_c = mysqli_fetch_object($def)) {
        $prod[] = $row_c->pc_name;
    }
}

// معالجة صفحة الاستفسارات
if ($file == "enquiry") {
    $_SESSION['last_page'] = "company/enquiry.php?c=" . urlencode($c);
    
    if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
        header("Location: ../sign-in.php");
        exit;
    }
}

$company = empty($row->bnsprof_comp_url) ? 'company' : $row->bnsprof_comp_url;

// تسجيل آخر صفحة
if ($file == "index") {
    $_SESSION['last_page'] = "company/index.php?c=" . urlencode($c);
} elseif ($file == "products") {
    $_SESSION['last_page'] = "company/products.php?c=" . urlencode($c);
} elseif ($file == "profile") {
    $_SESSION['last_page'] = "company/profile.php?c=" . urlencode($c);
} elseif ($file == "video") {
    $_SESSION['last_page'] = "company/video.php?c=" . urlencode($c);
}
?>


<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></title>
    <base href="../company/">
    
    <meta name="title" content="<?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    
    <link href="css/company.css?t=<?php echo rand(); ?>" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/jquery.bxslider.css" rel="stylesheet">
    
    <script src="js/jquery.js"></script>
    <script src="js/analytics.js" async></script>
    <script src="ls/html5.js"></script>
    <script src="js/tabbing.js"></script>
    <script src="js/mojozoom.js"></script>
    <link href="css/mojozoom.css" rel="stylesheet" />
    <script src="js/functions.js?t=<?php echo rand(); ?>"></script>
    
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.css">
    
    <style>
    label, input { display: block; }
    input.text { margin-bottom: 12px; width: 95%; padding: .4em; }
    fieldset { padding: 0; border: 0; margin-top: 25px; }
    h1 { font-size: 1.2em; margin: .6em 0; }
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: right; }
    .ui-dialog .ui-state-error { padding: .3em; overflow: visible; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    #ui-id-1 {
        text-align: center; background-color: #e73a00; height: 23px; width: 100%;
        color: white; padding-top: 20px; font-size: 20px;
    }
    .ui-resizable { overflow: visible !important; }
    .ui-dialog-buttonset { float: right !important; margin-top: 10px !important; margin-right: 131px; }
    .ui-dialog-buttonpane { margin-top: -63px !important; }
    #dialog-form { height: 420px; display: none; }
    .ui-draggable .ui-dialog-titlebar { padding: 0 !important; }
    .ui-corner-all { overflow: visible !important; }
    .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
        border-radius: 9px !important;
        top: -3%; left: -2px;
    }
    
    .slider { width: 55%; margin: 0 130px; }
    .slick-slide { margin: 10px 20px; }
    .slick-slide img {
        width: 100%; border: 1px solid #9c9;
        box-shadow: 0 0 10px #6c6; z-index: 9999;
    }
    .slick-prev:before, .slick-next:before { color: black; }
    .slick-dotted .slick-current img {
        transform: scale(1.5);
        border-bottom: 1px solid #9c9 !important;
        border-top: 1px solid #9c9 !important;
    }
    
    #wideColumn { width: 753px; float: right; }
    #thinColumn { width: 192px; float: left; }
    
    #wideColumn.cust .hot-product .grids_list section { height: 500px; }
    .user_profile_link { width: 190px; }
    .pop-up-position { left: -332px !important; right: auto !important; }
    
    span.red-icon-product {
        background: red; position: absolute; width: 30px; height: 30px;
        border-radius: 50%; color: #fff; left: 29px; z-index: 999;
        top: 5px; font-size: 13px; text-align: center;
    }
    #wideColumn.cust .hot-product { overflow: hidden; }
    
    .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button {
        background: rgba(0, 149, 255, 0.85);
        color: #fff; margin-top: 10px;
    }
    
    /* ============================================
       منع تأثير ltr على منطقة المنتجات
       ============================================ */
    #thinColumn,
    #thinColumn * {
        direction: ltr !important;
        text-align: left !important;
        unicode-bidi: normal !important;
    }
    
    /* الهيدر يبقى ltr */
    header, header * {
        direction: ltr !important;
        text-align: left !important;
    }
    
    /* استثناء القائمة العلوية لتظهر بشكل صحيح */
    .company_nav_head ul li {
        float: right !important;
    }
    
    .company_video_link {
        float: left !important;
    }
    </style>
    
    <script>
    $(function() {
        dialog = $("#dialog-form").dialog({
            autoOpen: false,
            height: 450,
            width: 400,
            resizable: false,
            modal: true,
        });
        $("#create-user").button().on("click", function() {
            dialog.dialog("open");
        });
    });
    </script>
</head>

<body>
    <header dir="ltr">
        <div id="logo" style="margin: 0px -2px 0px;">
            <section>
                <div class="company_profile_top_first">
                    <ul class="cb" dir="ltr">
                        <?php if (!empty($row->bnsprof_complogo)): ?>
                            <?php if (is_file("../upload/companylogo/" . $row->bnsprof_complogo)): ?>
                                <li>
                                    <img src="https://egyptmart.shop/upload/companylogo/<?php echo htmlspecialchars($row->bnsprof_complogo); ?>" 
                                         style="max-height:76px; margin-left:10px; padding-top:10px;" />
                                </li>
                            <?php elseif (file_exists(dirname(__FILE__) . "/../../server/php/files/" . $row->bnsprof_complogo)): ?>
                                <li>
                                    <img src="<?php echo '/server/php/files/' . htmlspecialchars($row->bnsprof_complogo); ?>" 
                                         style="max-height:76px; margin-left:10px; padding-top:10px;" />
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <li>
                            <h1 style="color: #fff; text-shadow: 1px 1px #060; font: 30px/.7em Arial, Helvetica, sans-serif; text-transform: capitalize;">
                                <?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?>
                            </h1>
                            <p style="padding-right:10px; color: #eeff1d !important; text-shadow: 1px 1px #060; margin-top: -6px; font-size: 16px;">
                                <span style="padding-left: 7px; margin-top: -6px;">
                                    <img src="<?php echo BASE_URL ?>/images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row->country ?? 0))); ?>" 
                                         alt="<?php echo htmlspecialchars(get_country_name((int)($row->country ?? 0))); ?>" 
                                         class="w4" align="top" height="30" width="35"/>
                                </span>
                                <span style="line-height: 27px;">
                                    <?php echo htmlspecialchars(get_country_name((int)($row->country ?? 0))); ?> - 
                                    <?php echo htmlspecialchars($states->state_name ?? ''); ?> - 
                                    <?php echo htmlspecialchars($citys->ct_name ?? ''); ?>
                                </span>
                            </p>
                        </li>
                    </ul>
                </div>
                
                <div class="company_profile_top_sec">
                    <div class="company_info">
                        <div class="header_top_div" style="box-shadow: 4px 6px 7px #4C4646;">
                            <?php if (!empty($row->bnsprof_id)): ?>
                                <?php
                                $sql_icon = "SELECT sip.mst_icon, sp.mst_name, sp.mp_id 
                                             FROM smembership_icon_plan sip 
                                             JOIN plan_member_id pm ON sip.mp_id = pm.p_id 
                                             JOIN smembership_plan sp ON sp.mp_id = sip.mp_id 
                                             WHERE pm.b_id = '" . (int)($row->bnsprof_id ?? 0) . "'";
                                $get_icon = mysqli_query($con, $sql_icon);
                                
                                if (mysqli_num_rows($get_icon) > 0):
                                    $icon = mysqli_fetch_array($get_icon);
                                ?>
                                    <div class="top-text1">
                                        <span><img src="<?php echo BASE_URL ?>/admin/images/<?php echo htmlspecialchars($icon['mst_icon'] ?? ''); ?>" /></span>
                                        <span>
                                            <?php 
                                            if (strpos(strtolower($icon['mst_name'] ?? ''), 'verified') !== false) {
                                                echo "JUNIOR Member";
                                            } else {
                                                echo htmlspecialchars($icon['mst_name'] ?? '');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <?php
                                    $sql_icon2 = "SELECT icon_id, p_id FROM plan_member_id WHERE b_id = '" . (int)($row->bnsprof_id ?? 0) . "'";
                                    $get_icon2 = mysqli_query($con, $sql_icon2);
                                    $icon2 = mysqli_fetch_array($get_icon2);
                                    
                                    $sql_icon1 = "SELECT * FROM smembership_icon_plan WHERE mp_id = '" . (int)($icon2['icon_id'] ?? 0) . "'";
                                    $get_icon1 = mysqli_query($con, $sql_icon1);
                                    $icon1 = mysqli_fetch_array($get_icon1);
                                    ?>
                                    <div class="top-text1">
                                        <span><img src="<?php echo BASE_URL ?>/admin/images/<?php echo htmlspecialchars($icon1['mst_icon'] ?? ''); ?>" /></span>
                                        <span><?php echo htmlspecialchars($icon1['mst_name'] ?? ''); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="top-text1">
                                    <span><img src="<?php echo BASE_URL ?>/admin/images/1455182389VERIDIED.jpg" /></span>
                                    <span>JUNIOR Member</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <nav class="cb company_nav_head" id="tml" style="height:44px !important;" dir="ltr">
            <ul class="company_menu pop-up-position-main" style="position: relative;" dir="ltr">
                <li><a href="./index.php?c=<?php echo urlencode($c); ?>" title="Home" <?php echo ($file == "index") ? 'class="on"' : ''; ?>>عن الشركة</a></li>
                <li><a href="./products.php?c=<?php echo urlencode($c); ?>" title="Company Products" <?php echo ($file == "products") ? 'class="on"' : ''; ?>>المنتجات</a></li>
                <li><a href="./profile.php?c=<?php echo urlencode($c); ?>" title="Profile" <?php echo ($file == "profile") ? 'class="on"' : ''; ?>>بروفايل</a></li>
                <li><a href="./enquiry.php?c=<?php echo urlencode($c); ?>" title="Contact Us" <?php echo ($file == "enquiry") ? 'class="on"' : ''; ?>>الإتصال بنا</a></li>
                
                <div class="pop-up-position" style="position: absolute; top: 10px; left: -310px; right: auto;">
                    <ul class="user_profile_link" style="margin-top: 0px; position: inherit;">
                        <li>
                            <a href="javascript:void(0)" style="padding: 6px 10px 6px 7px !important; color: #eeff1d !important;" 
                               title="Communicate with Supplier">Contact Company Admins</a>
                            <div id="profile_sub_menu">
                                <div class="profile_list_value">
                                    <div class="user_profile_image">
                                        <?php if (!empty($row->image)): ?>
                                            <img style="object-fit:cover" 
                                                 src="data:image/jpg;base64,<?php echo base64_encode(getUserInfo($uid, 'profileImage')); ?>" 
                                                 width="70" id="profilephoto1" height="70">
                                        <?php else: ?>
                                            <img style="object-fit:cover" 
                                                 src="<?php echo BASE_URL ?>/server/php/files/thumbnail/upload.jpg" 
                                                 width="70" height="70">
                                        <?php endif; ?>
                                    </div>
                                    <div class="user_name_value">
                                        <p style="line-height:1.5em;text-align:right;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;color:#002757;">
                                            <div class="menu_text1" title="How can I help you?">How Can I Help You?</div>
                                            <div class="user_name"><?php echo htmlspecialchars(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '')); ?></div>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="profile_list_value">
                                    <div onclick="checklogin()" class="contact_div" id="create-user" 
                                         style="font-size:13px; color: darkblue;">
                                        <span style="padding-left: 5px; float:right;">
                                            <img src="<?php echo BASE_URL ?>/company/images/mail_box.png" width="25">
                                        </span>
                                        <span style="padding-right: 0px; font-size: 12px;" title="Send Inquiry">
                                            <b>Contact Supplier</b>
                                        </span>
                                    </div>
                                    <?php if ($flag == "success"): ?>
                                        <br><br>
                                        <div class="contact_div">
                                            <span style="text-align: center; color: green; font-size: 14px;">
                                                <b>Your Message Sent Successfully ..</b>
                                            </span>
                                        </div>
                                        <br>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="profile_list_value">
                                    <div class="chat">
                                        <span><img src="<?php echo BASE_URL ?>/company/images/chatnow.png" width="55"></span>
                                        <span><?php echo htmlspecialchars(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '')); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="abcdefgh">
                                <div id="dialog-form" title="Contact Supplier" style="background-color: #fbfbda; overflow: visible;">
                                    <img style="position: absolute; right:-125px; top:-33px;" src="images/girls_PNG6471.png"/>
                                    <img style="position: absolute; right: 44px; top: -49px; width: 60px; height: 50px;" src="images/popar.png"/>
                                    
                                    <form method="post" action="smsMail.php">
                                        <fieldset>
                                            <input type="hidden" value="<?php echo htmlspecialchars($row->bnsprof_uid ?? ''); ?>" name="msg_to" id="msg_to">
                                            
                                            <label for="country">Country :</label>
                                            <select name="country" id="country" class="text ui-widget-content ui-corner-all" style="width:98%">
                                                <option value=""><?php echo htmlspecialchars(get_country_name((int)($row_usr->country ?? 0))); ?></option>
                                            </select>
                                            
                                            <label for="name">Company :</label>
                                            <input type="text" name="name" id="name" 
                                                   value="<?php echo htmlspecialchars($row_usr->bnsprof_compname ?? ''); ?>" 
                                                   placeholder="Your Company Name" class="text ui-widget-content ui-corner-all">
                                            
                                            <label for="email" style="margin-top: 20px;">Email :</label>
                                            <input type="hidden" value="<?php echo htmlspecialchars($c); ?>" id="c" name="c">
                                            <input type="hidden" value="<?php echo htmlspecialchars($row_usr->usr_id ?? ''); ?>" id="company" name="company">
                                            <input type="text" name="email" id="email" placeholder="email" 
                                                   value="<?php echo htmlspecialchars($row_usr->email ?? ''); ?>" 
                                                   class="text ui-widget-content ui-corner-all">
                                            
                                            <label for="mobile">Mobile :</label>
                                            <div class="flag-div" style="position:relative;">
                                                <input type="text" name="country_code" id="country_code" 
                                                       value="<?php echo htmlspecialchars($row_usr->country_ph_code ?? ''); ?>" 
                                                       class="text ui-widget-content ui-corner-all" 
                                                       style="float:right; width: 15%; padding-right: 36px;">
                                                <img style="position:absolute; right: 4px; top:5px;" 
                                                     src="<?php echo BASE_URL ?>/images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row_usr->country ?? 0))); ?>">
                                            </div>
                                            <input type="text" name="mobile" id="mobile" placeholder="Enter Your Mobile Number" 
                                                   value="<?php echo htmlspecialchars($row_usr->mobile1 ?? ''); ?>" 
                                                   class="text ui-widget-content ui-corner-all" 
                                                   style="float:right; width: 67%; margin-right: 5px;">
                                            
                                            <label for="description">Describe Your Requirements:</label>
                                            <textarea placeholder="" name="description" id="description" 
                                                      rows="4" cols="43" 
                                                      style="background-color:white; resize: none; text-align: center;"></textarea>
                                            
                                            <input type="button" id="popbutton" tabindex="-1" 
                                                   style="position:absolute; top:-1100px; background: #000">
                                        </fieldset>
                                        
                                        <input type="button" id="pop-sms" value="Send Inquiry" 
                                               style="padding: .4em 1em; line-height: normal; background: #c33100; color: #fff; font-weight:bold; margin-right: 130px;" 
                                               onclick="sendSMSEnquiry()">
                                    </form>
                                    
                                    <div id="loading" style="display:none; padding-right:192px; color:#1045B0; padding-top:16px;" 
                                         class="g9 bo off">
                                        <img class="loading" src="../images/loading-small.gif" alt="loading" height="16" width="16">
                                        <b>Wait Please ...</b>
                                    </div>
                                    
                                    <div id="sms_succ_result" style="display:none; padding-right:105px; color:#009700; padding-top:16px;" 
                                         class="g9 bo off">
                                        <b>Your Message was sent successfully ...</b>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </ul>
            
            <ul class="company_video_link">
                <li><span><a href="./video.php?c=<?php echo urlencode($c); ?>" title="Company Video">فيديو الشركة</a></span></li>
            </ul>
        </nav>
        
        <section id="header">
            <?php if ($file == "profile"): ?>
                <section class="center slider">
                    <?php if (count($banner) > 0): ?>
                        <?php foreach ($banner as $aBan): ?>
                            <div>
                                <img class="img-current-border" src="<?php echo htmlspecialchars($aBan); ?>" alt="Banner">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for ($i = 1; $i < 7; $i++): ?>
                            <div>
                                <img class="img-current-border" alt="No Images" src="../images/noimage.jpg">
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </section>
                
                <p style="padding-right:140px; padding-bottom:2px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;">
                    <?php
                    $size = count($business_type);
                    foreach ($business_type as $index => $btp) {
                        if ($index < $size - 1) {
                            echo '<span>' . htmlspecialchars($btp) . ' - </span>';
                        } else {
                            echo htmlspecialchars($btp);
                        }
                    }
                    ?>
                </p>
            <?php else: ?>
                <?php if (count($banner) > 0 && isset($banner[0]) && !empty($banner[0])): ?>
                    <figure>
                        <ul id="products" class="cb">
                            <?php foreach ($banner as $aBan): ?>
                                <li>
                                    <img width="100" style="object-fit:cover" height="100" alt="Product" 
                                         src="<?php echo htmlspecialchars($aBan); ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </figure>
                    
                    <p style="padding-right:60px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;">
                        <?php
                        $size = count($prod);
                        $pi = 1;
                        foreach ($prod as $index => $pro) {
                            if ($pi == 25) {
                                echo '</p><p style="display:none; padding-right:60px; padding-bottom:5px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;" id="id1">';
                            }
                            
                            if ($index < $size - 1) {
                                echo '<span>' . htmlspecialchars($pro) . ' , </span>';
                            } else {
                                echo htmlspecialchars($pro);
                            }
                            $pi++;
                        }
                        ?>
                    </p>
                    
                    <?php if ($pi > 25): ?>
                        <p style="padding-right:60px; padding-bottom:5px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;">
                            <span onclick="showMore(this)" style="padding:3px; cursor:pointer; font-size:12px">
                                <i class="fa fa-plus"></i>&nbsp;view more..
                            </span>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <figure>
                        <ul id="products" class="cb">
                            <?php for ($i = 1; $i < 7; $i++): ?>
                                <li>
                                    <img width="100" style="object-fit:cover" height="100" alt="No Images" 
                                         src="../images/noimage.jpg">
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </figure>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </header>
    
    <br>
    
    <script>
    function checklogin() {
        var user = "<?php echo isset($_SESSION['uid_indm']) ? $_SESSION['uid_indm'] : ''; ?>";
        if (user == '') {
            window.location.href = "../sign-in.php";
        }
    }
    
    function sendSMSEnquiry() {
        var msg_to = document.getElementById('msg_to').value;
        var email = document.getElementById('email').value;
        var company = document.getElementById('company').value;
        var name = document.getElementById('name').value;
        var c = document.getElementById('c').value;
        var country_code = document.getElementById('country_code').value;
        var mobile = document.getElementById('mobile').value;
        var description = document.getElementById('description').value;
        var e = document.getElementById("country");
        var country = e.options[e.selectedIndex].value;
        
        $("#pop-sms").css("display", "none");
        $("#loading").css("display", "block");

        $.post("../company/smsMail.php", {
            email: email,
            company: company,
            name: name,
            c: c,
            country_code: country_code,
            mobile: mobile,
            description: description,
            country: country,
            msg_to: msg_to
        }, function(data) {
            if (data == 1) {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#sms_succ_result").css("display", "block");
                }, 500);
            } else {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#err_result").css("display", "block");
                }, 500);
            }
        });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>
    
    <script>
    $(document).on('ready', function() {
        if ($(".center.slider").length > 0) {
            $(".center.slider").slick({
                dots: true,
                infinite: true,
                centerMode: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                arrows: true,
            });
        }
    });
    </script>
</body>
</html>