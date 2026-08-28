<?php
/**
 * File Name: news-list.php
 * PHP Version: 8.3
 * Description: صفحة عرض وإدارة الأخبار والبيانات الصحفية - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

require_once 'common.php';

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || !$con) {
    die('Database connection error');
}

// معالجة الصفحة الحالية
$pageno = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
if ($pageno < 1) {
    $pageno = 1;
}

// حساب إجمالي الأخبار
$stmt_count = mysqli_prepare($con, "SELECT COUNT(*) FROM news WHERE nws_uid = ? AND nws_status = '1'");
mysqli_stmt_bind_param($stmt_count, 'i', $uid);
mysqli_stmt_execute($stmt_count);
mysqli_stmt_bind_result($stmt_count, $totnews);
mysqli_stmt_fetch($stmt_count);
mysqli_stmt_close($stmt_count);

$totnews = (int)$totnews;
$limits = 30;
$total_pages = ceil($totnews / $limits);
$start_limit = $limits * ($pageno - 1);

// جلب الأخبار مع pagination
$stmt_news = mysqli_prepare($con, "SELECT nws_id, nws_postdate, nws_medianm, nws_mediatyp, nws_headline, 
                                   nws_covgurl, nws_covgdet, nws_smallimg 
                                   FROM news 
                                   WHERE nws_uid = ? AND nws_status = '1' 
                                   ORDER BY nws_id DESC 
                                   LIMIT ?, ?");
$stmt_news->bind_param('iii', $uid, $start_limit, $limits);
$stmt_news->execute();
$result_news = $stmt_news->get_result();

$news_items = [];
while ($row = $result_news->fetch_assoc()) {
    $news_items[] = [
        'id' => (int)$row['nws_id'],
        'postdate' => $row['nws_postdate'],
        'medianm' => htmlspecialchars($row['nws_medianm'] ?? '', ENT_QUOTES, 'UTF-8'),
        'mediatyp' => htmlspecialchars($row['nws_mediatyp'] ?? '', ENT_QUOTES, 'UTF-8'),
        'headline' => htmlspecialchars($row['nws_headline'] ?? '', ENT_QUOTES, 'UTF-8'),
        'covgurl' => htmlspecialchars($row['nws_covgurl'] ?? '', ENT_QUOTES, 'UTF-8'),
        'covgdet' => $row['nws_covgdet'] ?? '',
        'smallimg' => $row['nws_smallimg'] ? htmlspecialchars($row['nws_smallimg'], ENT_QUOTES, 'UTF-8') : null
    ];
}
$stmt_news->close();

// حساب نطاق العرض
$showitems = $start_limit + 1 . "-";
if (($start_limit + $limits) < $totnews) {
    $showitems .= $start_limit + $limits;
} else {
    $showitems .= $totnews;
}
$showitems .= " of " . $totnews . " ";

// استرجاع القيم من الجلسة إذا كانت موجودة
$nws_medianm = htmlspecialchars($_SESSION['nws_medianm'] ?? '', ENT_QUOTES, 'UTF-8');
$nws_mediatyp = $_SESSION['nws_mediatyp'] ?? '';
$nws_headline = htmlspecialchars($_SESSION['nws_headline'] ?? '', ENT_QUOTES, 'UTF-8');
$nws_covgurl = htmlspecialchars($_SESSION['nws_covgurl'] ?? '', ENT_QUOTES, 'UTF-8');
$nws_covgdet = htmlspecialchars($_SESSION['nws_covgdet'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'إدارة الأخبار'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/n.css" type="text/css" rel="stylesheet">
    <link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

    <script src="js/jquery.js"></script>
    <script src="calendar/calendar_js_css/js_calendar.js" type="text/javascript"></script>
</head>
<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <br><br>
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
    </div>

    <?php include 'includes/header_menu.php'; ?>
    
    <!-- القائمة الجانبية -->
    <?php include 'includes/left_menu.php'; ?>
    
    <!-- المحتوى الرئيسي -->
    <div class="w56 f1 p2b p14 blr" id="id_attribute_value" style="width:756px; height:100%;">
        
        <!-- رسالة التأكيد -->
        <div id="message1" style="position: absolute; width: 100%; display: none; top: 0px; left: 0px; z-index: 1000; height: 1189px;" class="sec-open" align="CENTER">
            <div style="height: 0px;" id="divheight"></div>
            <form style="margin:0px;" name="dataform">
                <table style="height: 598px; width: 1344px;" id="tableheight" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <table id="tableheight" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="720">
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="38">
                                            <tr>
                                                <td>
                                                    <a href="javascript:show_alert_off('message1')">
                                                        <img src="images/blue-cross1.gif" border="0" width="24" height="25">
                                                    </a>
                                                    <br>
                                                    <img src="images/blue-cross2.gif" width="24" height="13">
                                                </td>
                                                <td class="p_e" align="LEFT" background="images/blue-bggg.gif" width="100%">
                                                    <div class="tsfrom">الأخبار</div>
                                                </td>
                                                <td class="p_e" align="CENTER" background="newsadd_files/blue-cor.gif" width="70">
                                                    <img src="images/zero.gif" width="70" height="1"><br>
                                                    <a href="javascript:show_alert_off('message1');">
                                                        <img src="images/close-bt.gif" border="0" width="59" height="25" hspace="4">
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="background-image:url(images/bg_w_q Q)">
                                            <div style="display: block;" id="mysaveid">
                                                <table align="center" border="0" cellpadding="4" cellspacing="0" width="490">
                                                    <tr>
                                                        <td class="label" width="135">التاريخ</td>
                                                        <td align="left">
                                                            <input maxlength="60" name="todays_date1" value="<?php echo date('d-M-Y'); ?>" 
                                                                   class="a_f dat" readonly="readonly" type="text">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">اسم الوسيلة</td>
                                                        <td align="left">
                                                            <input maxlength="100" name="n_name" id="n_name" class="a_f rf" value="">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">نوع الوسيلة</td>
                                                        <td align="left">
                                                            <select name="n_type" id="n_type" class="a_f cof">
                                                                <option value="">اختر نوع الوسيلة</option>
                                                                <option value="Newspaper">صحيفة</option>
                                                                <option value="Television">تلفزيون</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">العنوان الرئيسي</td>
                                                        <td align="left">
                                                            <input maxlength="100" id="n_headline" name="n_headline" class="a_f rf" value="">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">رابط التغطية</td>
                                                        <td align="left">
                                                            <input name="n_url" id="n_url" maxlength="93" class="a_f rf" type="text">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label"><span>*</span>&nbsp;تفاصيل التغطية</td>
                                                        <td align="left">
                                                            <input name="n_desc" id="popup_n_desc" value="" type="hidden">
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td><img src="images/white-1.gif" width="24" height="15"></td>
                                                <td align="CENTER" background="newsadd_files/white-bg.gif" width="100%"></td>
                                                <td><img src="images/white-2.gif" width="18" height="15"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        <!-- رأس الصفحة -->
        <div>
            <div id="chg_name" class="f1 chng_a chng_b">
                <h1 class="f1" id="cpf_name">الأخبار</h1>
            </div>
            <p id="pf_change" style="display:none; float:left; margin-top:0px"></p>
            <p class="f2" style="margin-top:11px; color: #222222" id="news_cnt">
                <strong><?php echo $showitems; ?></strong>
            </p>
            <div class="c3"></div>
        </div>

        <!-- زر إضافة خبر جديد -->
        <div class="mt5">
            <p class="aml"></p>
            <div class="utab">
                <span>أضف خبراً إلى كتالوجك الإلكتروني:</span>
                <a style="display: block;" class="f2 fw apr1" onclick="formopend('add');" href="news-list.php#form_tst1" id="edit_addnews">
                    إضافة خبر
                </a>
            </div>
        </div>
        
        <a name="add"></a>
        <div id="add_msg_div">
            <div id="cpd" class="arrow cover_div" style="display:none">
                لا توجد أخبار مضافة إلى كتالوجك الإلكتروني،<br>
                ابدأ بإضافة الأخبار الآن!
            </div>
        </div>
        <div id="gap" class="c3" style="display: block;">&nbsp;</div>
        
        <!-- نموذج إضافة خبر جديد -->
        <div id="form_tst1" style="display:<?php echo $totnews <= 0 ? 'block' : 'none'; ?>;">
            <div id="newspresscov" align="center">
                <form name="ad_newspress" onsubmit="return false;" method="post" action="">
                    <div class="frm_a clb">
                        <div class="clb">
                            <a class="f11 fr" href="javascript:formclose();">إغلاق [x]</a>
                        </div>
                        
                        <table align="center" border="0" cellpadding="4" cellspacing="0" width="490">
                            <tr>
                                <td class="label" width="135">التاريخ</td>
                                <td align="left">
                                    <div id="a1" class="tbp tbm5" style="display:none">
                                        <div class="t1a" align="left">الرجاء اختيار التاريخ.</div>
                                    </div>
                                    <input type="text" maxlength="60" class="a_f dat" id="nws_postdate" name="nws_postdate" value="" 
                                           readonly="readonly" onclick="displayCalendar(document.getElementById('nws_postdate'),'yyyy-mm-dd',this)">
                                </td>
                            </tr>
                            <tr>
                                <td class="label">اسم الوسيلة</td>
                                <td align="left">
                                    <div id="a2" class="tbp cona" style="display:none">
                                        <div class="t1a" align="left">الرجاء ذكر اسم الوسيلة (صحيفة أو قناة تلفزيونية) حيث تم نشر/بث الخبر.</div>
                                    </div>
                                    <input maxlength="100" name="nws_medianm" class="a_f rf" id="nws_medianm" value="<?php echo $nws_medianm; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label">نوع الوسيلة</td>
                                <td align="left">
                                    <div id="a3" class="tbp cona" style="display:none">
                                        <div class="t1a" align="left">الرجاء اختيار نوع الوسيلة من هذه القائمة.</div>
                                    </div>
                                    <select name="nws_mediatyp" id="nws_mediatyp" class="a_f cof">
                                        <option value="">اختر نوع الوسيلة</option>
                                        <option value="Newspaper" <?php echo $nws_mediatyp == "Newspaper" ? 'selected' : ''; ?>>صحيفة</option>
                                        <option value="Television" <?php echo $nws_mediatyp == "Television" ? 'selected' : ''; ?>>تلفزيون</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">العنوان الرئيسي</td>
                                <td align="left">
                                    <div id="a4" class="tbp cona" style="display:none">
                                        <div class="t1a" align="left">الرجاء إدخال العنوان الرئيسي لتغطيتك الإخبارية.</div>
                                    </div>
                                    <input maxlength="100" name="nws_headline" id="nws_headline" class="a_f rf" value="<?php echo $nws_headline; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label">رابط التغطية</td>
                                <td align="left">
                                    <div id="a5" class="tbp cona" style="display:none">
                                        <div class="t1a" align="left">الرجاء إدخال الرابط الذي تم رفع هذا الخبر عليه.</div>
                                    </div>
                                    <input name="nws_covgurl" id="nws_covgurl" maxlength="93" class="a_f rf" type="text" value="<?php echo $nws_covgurl; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label"><span>*</span>&nbsp;تفاصيل التغطية</td>
                                <td align="left">
                                    <textarea name="nws_covgdet" id="nws_covgdet" rows="10" cols="80" class="a_f rf" style="width: 322px;"><?php echo $nws_covgdet; ?></textarea>
                                    <div class="max f11">
                                        <font id="Charcount" color="#ff8000">0 حرف (الحد الأقصى 4000)</font> حرف.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">إضافة صورة</td>
                                <td align="left">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="center" valign="top" width="20%">
                                                صورة مصغرة<br>
                                                <iframe src="upload-save-newspress.php" border="0" framespacing="0" 
                                                        allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125"></iframe>
                                            </td>
                                            <td width="3%">&nbsp;</td>
                                            <td align="center" valign="top" width="20%">
                                                صورة كبيرة<br>
                                                <iframe src="upload-save-newspresslarge.php" border="0" framespacing="0" 
                                                        allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125"></iframe>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td align="left">
                                    <input name="cdate" id="cdate" maxlength="93" class="a_f rf" type="hidden" value="<?php echo date('Y-m-d'); ?>">
                                    <input class="saps mt5" name="submit" value="إضافة خبر / بيان صحفي" type="button" onClick="addnews(<?php echo $uid; ?>)">
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- عرض الأخبار -->
        <?php foreach ($news_items as $news): ?>
        <div id="<?php echo $news['id']; ?>" class="nlist ap4 news_div">		
            <div class="f1" style="width:125px; margin-left: 10px;">		
                <div class="f1 ap3" id="base_n_image_<?php echo $news['id']; ?>" style="height:125px; width:125px;" align="center">
                    <?php if ($news['smallimg']): ?>
                        <img src="upload/mynews/small/<?php echo $news['smallimg']; ?>" 
                             alt="<?php echo $news['headline']; ?>" 
                             class="pro" border="0" style="width:100%; height:auto;">
                    <?php else: ?>
                        <img src="images/noimage.jpg" class="pro" border="0" width="125" height="107">
                    <?php endif; ?>
                </div>
                <div id="base_big_img_<?php echo $news['id']; ?>"></div>
            </div>
            
            <div class="f1 nc wrd-brk p-cont">
                <strong id="base_headline_<?php echo $news['id']; ?>"><?php echo $news['headline']; ?></strong><br>
                <span>تاريخ النشر: </span>
                <span id="base_date_<?php echo $news['id']; ?>"><?php echo date('d-M-Y', strtotime($news['postdate'])); ?></span>
                <div class="c3"></div>
                
                <p class="mt5 lh nde" style="text-align: left">
                    <?php if ($news['medianm'] !== ''): ?>
                        <span id="base_name_pop_<?php echo $news['id']; ?>">
                            <b>اسم الوسيلة: </b>
                            <span id="base_name_<?php echo $news['id']; ?>"><?php echo $news['medianm']; ?></span><br>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($news['mediatyp'] !== ''): ?>
                        <span id="base_type_pop_<?php echo $news['id']; ?>">
                            <b>نوع الوسيلة: </b>
                            <span id="base_type_<?php echo $news['id']; ?>"><?php echo $news['mediatyp']; ?></span><br>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($news['covgurl'] !== ''): ?>
                        <span id="base_url_pop_<?php echo $news['id']; ?>">
                            <b>رابط النشر: </b>
                            <span id="base_url_<?php echo $news['id']; ?>">
                                <a href="<?php echo $news['covgurl']; ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo $news['covgurl']; ?>
                                </a>
                            </span><br>
                        </span>
                    <?php endif; ?>
                </p>
                
                <div id="base_desc_hd<?php echo $news['id']; ?>" style="margin-right:20px; color: #222222; display:none;" class="mt5 lh tl wrd-brk awpf c3">
                    <?php echo nl2br(htmlspecialchars($news['covgdet'], ENT_QUOTES, 'UTF-8')); ?>
                </div>
                
                <div class="mt5 lh tl wrd-brk awpf c3" id="base_desc_sd<?php echo $news['id']; ?>" 
                     style="height:5em; text-align:justify; padding: 5px 0 15px; line-height: 19px; overflow:hidden;">
                    <?php echo htmlspecialchars(substr($news['covgdet'], 0, 290), ENT_QUOTES, 'UTF-8'); ?><br>
                </div>
                
                <?php if (strlen($news['covgdet']) > 290): ?>
                    <a style="padding-right:20px; float:right; font-size:12.5px; text-align:center; text-decoration:underline; cursor:pointer;" 
                       id="less_hd<?php echo $news['id']; ?>" onClick="showdesc(<?php echo $news['id']; ?>)">
                        عرض التفاصيل الكاملة
                    </a>
                    <span id="less_sd<?php echo $news['id']; ?>" style="display:none;">
                        <a style="padding-right:20px; float:right; font-size:12.5px; text-align:center; text-decoration:underline; cursor:pointer;" 
                           onClick="hidedesc(<?php echo $news['id']; ?>)">
                            عرض أقل
                        </a>
                    </span>
                <?php endif; ?>
            </div>
            
            <div style="width: 100px; margin-left: 20px; margin-top: 100px;" class="f1">
                <span style="*margin-bottom:5px" class="link1 cpr">		
                    <a href="" class="edi bnr dl_pf" id="edit_0" style="*float:none; display:block; padding-bottom: 4px;">تعديل</a>
                </span>
                <a style="*float:none; cursor:pointer;" id="delp_<?php echo $news['id']; ?>" 
                   onclick="showdeloption(<?php echo $news['id']; ?>)" class="del bnr dl_pf">حذف</a>
            </div>
            
            <div class="c3"></div>
            
            <div class="info bnr dn" id="dcon<?php echo $news['id']; ?>" style="margin-left: 10px; margin-right: 10px; display:none;">
                <div style="width:125px;" class="f2">
                    <a id="yesp_<?php echo $news['id']; ?>" onclick="delnews(<?php echo $news['id']; ?>)" class="yn" style="cursor:pointer;">نعم</a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a id="nop_<?php echo $news['id']; ?>" onclick="hidedeloption(<?php echo $news['id']; ?>)" class="yn" style="cursor:pointer;">لا</a>
                </div>
                هل أنت متأكد من رغبتك في حذف هذا الخبر / البيان الصحفي؟
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- رسالة الحذف الناجح -->
        <div style="display: block;" class="" id="proce225801" align="center">
            <div class="save bnr mt12 db">
                <strong>تم حذف المحتوى بنجاح!</strong>
            </div>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- تذييل الصفحة -->
    <?php include 'includes/footer.php'; ?>
</div>

<script>
/**
 * دوال JavaScript - متوافقة مع PHP 8.3
 */

/**
 * إضافة خبر جديد
 */
function addnews(id) {
    var nws_postdate = $("#nws_postdate").val();
    var nws_medianm = $("#nws_medianm").val();
    var nws_mediatyp = $("#nws_mediatyp").val();	
    var nws_headline = $("#nws_headline").val();
    var nws_covgurl = $("#nws_covgurl").val();	
    var nws_covgdet = $("#nws_covgdet").val();
    var cdate = $("#cdate").val();	

    // التحقق من صحة التاريخ
    if (nws_postdate !== "" && new Date(nws_postdate).getTime() > new Date(cdate).getTime()) {
        alert("لا يمكنك اختيار تاريخ بعد التاريخ الحالي");
        return false;
    }
    
    // التحقق من صحة الرابط
    if (nws_covgurl !== "" && !nws_covgurl.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
        alert("الرجاء إدخال رابط صحيح");
        return false;
    }
    
    // التحقق من التفاصيل
    if (nws_covgdet === "") {
        alert("تفاصيل الخبر / البيان الصحفي لا يمكن أن تكون فارغة.");
        return false;
    }
    
    if (nws_covgdet.length > 4000) {
        alert("تفاصيل الخبر / البيان الصحفي لا يمكن أن تتجاوز 4000 حرف.");
        return false;
    }
    
    // إرسال الطلب
    $.get("ajax-file/newsadd.php", {
        id: id,
        nws_postdate: nws_postdate,
        nws_medianm: nws_medianm,
        nws_mediatyp: nws_mediatyp,
        nws_headline: nws_headline,
        nws_covgurl: nws_covgurl,
        nws_covgdet: nws_covgdet
    }, function(data) {
        var d = data.split('||');
        if (d[1] == 0) {
            alert(d[0]);
        }
        if (d[1] == 1) {
            location.reload();
        }
    });
    
    return false;
}

/**
 * فتح نموذج الإضافة
 */
function formopend() {
    $("#form_tst1").show();	
}

/**
 * إغلاق نموذج الإضافة
 */
function formclose() {
    $("#form_tst1").hide();	
}

/**
 * عرض التفاصيل الكاملة
 */
function showdesc(id) {
    $("#base_desc_hd" + id).show();	
    $("#less_sd" + id).show();
    $("#base_desc_sd" + id).hide();	
    $("#less_hd" + id).hide();
}

/**
 * إخفاء التفاصيل
 */
function hidedesc(id) {
    $("#base_desc_hd" + id).hide();	
    $("#less_sd" + id).hide();
    $("#base_desc_sd" + id).show();	
    $("#less_hd" + id).show();		
}

/**
 * عرض خيار الحذف
 */
function showdeloption(id) {
    $("#dcon" + id).slideDown('slow');
}

/**
 * إخفاء خيار الحذف
 */
function hidedeloption(id) {
    $("#dcon" + id).slideUp('slow');
}

/**
 * حذف خبر
 */
function delnews(id) {
    $.get("ajax-file/delnews.php", {id: id}, function(data) {
        location.reload();
    });
}

/**
 * تحديث عداد الأحرف
 */
$(document).ready(function() {
    $("#nws_covgdet").on('input', function() {
        var remaining = 4000 - $(this).val().length;
        if (remaining < 0) remaining = 0;
        $("#Charcount").text(remaining + " حرف (الحد الأقصى 4000)");
    });
});
</script>
</body>
</html>