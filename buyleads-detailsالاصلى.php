<?php
include "common.php";
if (!function_exists('getCurrencySymbol')) { function getCurrencySymbol() { return '$'; } }
$br_id = substr($_GET['id'], 4);
$credit_available = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/trade-7.css" rel="STYLESHEET" type="text/css">
<link href="css/trade-detail1.css" rel="STYLESHEET" type="text/css">
<link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function purchaseLead(id)
{
    if(confirm('هل أنت متأكد أنك تريد الحصول على بيانات هذا المشتري؟'))
    {
        $.post("ajax-file/purchaseBuyLead.php",{id:id},    function(data){ $("#buy_alert_msg").removeClass("doff"); });    
    }
}
function open_alert_close()
{
    window.location.reload();    
}
function showMessage()
{
    alert('رصيدك لا يكفي. يرجى شراء كريديت أولاً');    
}
function choosePackage(id)
{
    window.location.href="payment-option.php?id="+id;
}
function sendEnquiry()
{
    var msg_from=document.getElementById('msg_from');
    var msg_to=document.getElementById('msg_to');
    var msg_subject=document.getElementById('msg_subject');
    var msg_message=document.getElementById('msg_message');
    var lead_headline = document.getElementById('lead_headline').value;
    var msg="";
    var valid=true;
    if(msg_message.value == '' || msg_message.value == null)
    {
        msg="من فضلك اكتب الاستفسار";
        valid=false;
    }
    else if(msg_message.value.length < 20)
    {
        msg="الاستفسار لايقل عن 20 حرف";
        msg_message.focus();
        valid=false;
    }
    if(valid==false)
    {
        alert(msg);
        msg_message.focus();
    }
    else
    {
        $("#enqloading").css("display","block");
        $("#enqloading1").css("display","none");
        $.post("ajax-file/sendMessage.php", {lead_headline:lead_headline,msg_from:msg_from.value,msg_to:msg_to.value,msg_subject:msg_subject.value,msg_message:msg_message.value}, function(data){
            if(data==1)
            {
                setTimeout(function () {
                    alert('تم إرسال استفسارك بنجاح');
                    $("#enqloading").css("display","none");
                    $("#enqloading1").css("display","block");
                    msg_message.value="";
                }, 500);
            }
            else
            {
                setTimeout(function () {
                    alert('لم يتم إرسال الاستفسار. حاول مرة أخرى');
                    $("#enqloading").css("display","none");
                    $("#enqloading1").css("display","block");
                }, 500);
            }
        });    
    }
}
</script>
<script type="text/javascript" src="js/mojozoom.js"></script>  
<link type="text/css" href="css/mojozoom.css" rel="stylesheet" />  
</head>
<body class="search-show-box-buyleads sale-offers search-page-now">
<div class="q_hm1">
<?php include "includes/header_new.php"; ?>
<br>
<?php
// ✅ الاستعلام الرئيسي - تم إصلاحه
$sql = "SELECT * FROM buy_requirement, product_category, user, business_profile 
        WHERE br_pc_id = pc_id 
        AND br_u_id = usr_id 
        AND usr_id = bnsprof_uid 
        AND md5(br_id) = '" . mysqli_real_escape_string($con, $br_id) . "'";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

$sql_pcat = "SELECT m.pc_id, m.pc_name, c.pc_id, c.pc_name, s.pc_name 
             FROM product_category m, product_category c, product_category s 
             WHERE m.pc_id = c.pc_parent_id 
             AND c.pc_id = s.pc_parent_id 
             AND s.pc_id = '" . (int)$row->br_pc_id . "'";
$res_pcat = mysqli_query($con, $sql_pcat);
$row_pcat = mysqli_fetch_array($res_pcat);
?>
<div align="left" style="width:100%; max-width:1170px; margin:0 auto!important;">
<div style="width:100%;">
<div class="p3 pl lf mm"><a href="buyleads.php" class="c12 td">طلبات شراء</a> &nbsp;&gt;&nbsp; 
<a href="category.php?token=<?php echo rand(1000,9999) . md5($row_pcat[0]); ?>" class="c12"><?php echo ucwords($row_pcat[1]); ?></a>
&nbsp;&gt;&nbsp;<a href="catcompany.php?token=<?php echo rand(1000,9999) . md5($row_pcat[2]); ?>" class="c12"><?php echo ucwords($row_pcat[3]); ?></a>
&nbsp;&gt;&nbsp;<?php echo ucwords($row_pcat[4]); ?><br>
</div>
</div>
<div style="float:left;width:70%;text-align:left">
<div class="e5 lbx" id="lftdsc">
<h1 class="f6 cl2" id="lead_headline"><?php echo $row->br_pd_name; ?></h1>
<?php 
$cid = rand(1000,9999) . md5($row->bnsprof_id);
if($row->br_preferred_supplier_location != ''){
?>
- <span class="f5">
<?php
    if($row->br_preferred_supplier_location == 'any') echo "من كل مكان";    
    else if($row->br_preferred_supplier_location == 'abroad') echo "خارج البلاد";    
    else if($row->br_preferred_supplier_location == 'domestic') {
        echo get_country_name($row->country); ?>
        <img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" height="16" width="24">
    <?php } else if($row->br_preferred_supplier_location == 'my_city' && $row->bnsprof_city != '0') {
        echo get_city_name($row->bnsprof_city);
    } ?>
</span> 
<?php } ?>
<?php
$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
if($uid != '' && !empty($row->bnsprof_compname)){
    // ✅ السطر 202 - تم إصلاحه
    $sql_icon = "SELECT sip.mst_icon, sip.mst_name FROM smembership_icon_plan sip 
                 JOIN user u ON sip.mp_id = u.usr_mp_id 
                 WHERE u.usr_id = " . $uid;
    $get_icon = mysqli_query($con, $sql_icon);
    ?>
    <span>
    <?php if(mysqli_num_rows($get_icon) > 0){ 
        $title = 'Junior';
        $icon = mysqli_fetch_array($get_icon);
        if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
            $title = 'Senior';
        } else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
            $title = 'Sponsor';
        }                               
        if($title == 'Junior') {?>
            <img src="admin/images/<?php echo $icon['mst_icon']; ?>" title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;">
        <?php } else { ?>
            <a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>" title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;"></a>
        <?php } ?>
    <?php } ?>
    </span>
<?php } ?>
</span>
<span class="vlogoB1 tooltip2 valb mb1"><span class="g9 d1" style="font-weight:bold;padding:0px 2px 0px 21px; line-height:19px; display:inline-block; background:#0095f9 url('images/verified-sign.jpg') left no-repeat;">متحقق ومحدث</span></span>
<div style="padding-bottom:4px;margin-top:12px">
<p style="color:rgb(185,184,184);float:right;text-align:right;" class="j1 cb"><font style="color:rgb(152,151,151);">: تاريخ النشر</font> <?php echo date("d M, Y", strtotime($row->br_updated_date)); ?> | <font style="color:rgb(152,151,151);">: آخر تحديث</font> <?php echo date("d M, Y", strtotime($row->br_posting_date)); ?></p>
<div>
    <?php 
    // ✅ مسار الصورة - يبحث في image_gallery أولاً
    $image_name = !empty($row->br_pic) ? $row->br_pic : 'no-image.png';
    $thumb_path = 'upload/buy_requirement/thumb/';
    $zoom_path = 'upload/buy_requirement/';
    
    // ✅ التحقق من وجود الصورة في image_gallery
    $gallery_path = 'upload/image_gallery/';
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $gallery_path . $image_name;
    if (file_exists($full_path)) {
        $thumb_path = $gallery_path;
        $zoom_path = $gallery_path;
    } else {
        // ✅ إذا لم تكن في image_gallery، جرب buy_requirement
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $zoom_path . $image_name;
        if (!file_exists($full_path)) {
            $image_name = 'no-image.png';
            $thumb_path = 'upload/buy_requirement/thumb/';
            $zoom_path = 'upload/buy_requirement/';
        }
    }
    ?>
    <img src="<?php echo $thumb_path . $image_name; ?>" 
         id="zoomImage<?php echo $row->br_id; ?>" 
         border="0" height="100" width="125" 
         data-zoomsrc="<?php echo $zoom_path . $image_name; ?>"
         onerror="this.src='upload/buy_requirement/no-image.png'">
</div>
<span class="c12 bo fs">تفاصيل طلب الشراء:</span>
<div class="bdt" id="hdiv1">
    <div class="g2 fs k7">
        <div><?php echo stripslashes($row->br_requirement); ?><br>
        <?php if($row->br_estimate_qty != '0' && $row->br_estimate_qty != ''){ ?>
        <div><span class="c13"><strong>الكمية:</strong> <?php echo $row->br_estimate_qty; ?>&nbsp;<?php echo measurement_unit($row->br_estimate_qty_unit); ?></span></div><br>
        <?php } ?>
        <br>
        <?php
        // جلب خطة العضوية للمستخدم الحالي
        $membership_plan = '';
        if(isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])){
            $uid_plan = (int)$_SESSION['uid_indm'];
            $query_mp = mysqli_query($con, "SELECT mst_name FROM smembership_plan mp JOIN user u ON u.usr_mp_id = mp.mp_id WHERE u.usr_id = $uid_plan");
            if($query_mp && mysqli_num_rows($query_mp) > 0){
                $row_mp = mysqli_fetch_object($query_mp);
                $membership_plan = $row_mp->mst_name;
            }
        }
        
        if(($row->br_apprx_order_value != '' && $row->br_apprx_order_currency != '') || $row->br_description != '' || ($row->br_website != "http://" && $row->br_website != '') || $row->br_need_quote_for != '' || $row->br_purchase_time != '' || $row->br_preferred_supplier_location != '' || $row->br_need_for != '' || $row->br_requirement_frequency != ''){
        ?>
        <span class="bdd bo"><span class="artb sbg"></span> معلومات إضافية يحددها المشتري</span>
        <div class="c15 pt4 f1 pl">
        <?php if($row->br_apprx_order_value != '' && $row->br_apprx_order_currency != ''){ ?>القيمة التقديرية: <?php echo $row->br_apprx_order_currency; ?>&nbsp;<?php echo $row->br_apprx_order_value; ?><br><?php } ?>
        <?php if($row->br_description != ''){ ?>التطبيق/الاستخدام: <?php echo stripslashes($row->br_description); ?><br><?php } ?>
        <?php if($row->br_website != ''){ ?>الموقع الإلكتروني: <?php echo stripslashes($row->br_website); ?><br><?php } ?>
        <?php if($row->br_need_quote_for != ''){ ?>بحاجة إلى عرض سعر: <?php echo stripslashes($row->br_need_quote_for); ?><br><?php } ?>
        <?php if($row->br_purchase_time != ''){ ?>وقت الشراء المطلوب: <?php echo stripslashes($row->br_purchase_time); ?><br><?php } ?>   
        <?php if($row->br_preferred_supplier_location != ''){ ?>مكان التوريد المفضل: 
        <?php 
        if($row->br_preferred_supplier_location == 'any') echo "من كل مكان";    
        else if($row->br_preferred_supplier_location == 'abroad') echo "خارج البلاد";    
        else if($row->br_preferred_supplier_location == 'domestic') {
            echo get_country_name($row->country); ?>
            &nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" height="16" width="24">
        <?php } else if($row->br_preferred_supplier_location == 'my_city' && $row->bnsprof_city != '0') {
            echo get_city_name($row->bnsprof_city);
        } ?><br><?php } ?>
        <?php if($row->br_need_for != ''){ ?>سبب الشراء: <?php echo stripslashes($row->br_need_for); ?><br><?php } ?>
        <?php if($row->br_requirement_frequency != ''){ ?>معدل تكرار الشراء: <?php echo stripslashes($row->br_requirement_frequency); ?><br><?php } ?>
        <br></div>
        <?php } ?>
        </div>
    </div>
</div>
</div>

<div class="doff" id="buy_alert_msg" style="background:#fffdea;width:700px;position:fixed;top:50%;left:50%;font-family:arial;font-size:14px;padding:3px 10px 10px 10px;line-height:23px;border:4px solid #e4be75;z-index:99;margin-left:-300px;margin-top:-130px">
<div style="padding-top:10px">
<b>! لقد حصلت على بيانات طلب الشراء</b><br>
<div style="padding-left:5px;padding-top:3px">
&#8226; تم حفظ طلب الشراء في "<a href="manage-purchased-buyleads.php">طلبات الشراء المشتراة</a>"<br>
&#8226; يمكنك إرسال ردك إلى هذا المشتري من "<a href="manage-purchased-buyleads.php">طلبات الشراء المشتراة</a>"
<?php if(stripos($membership_plan, 'sponsor') === false && stripos($membership_plan, 'senior') === false){ ?>
<div style="height:10px;overflow:hidden"></div>
&#8226; ستظهر هذه العملية في "<a href="transaction_history.php">سجل المعاملات</a>"
<?php } ?>
</div>
</div>
<div style="padding-top:5px" align="center"><input onClick="open_alert_close()" value="موافق" style="font-size:16px;font-weight:bold" type="button"></div>
</div>
</div>
</div>

<div class="wd9 lf gv" style="float:right;">
<?php if($uid > 0){ ?>
<div class="lgnDtl k7 mb3">
<div class="f5 bdd">مرحباً <?php echo user_info($uid, 'name_prefix') . "&nbsp;" . user_info($uid, 'fname'); ?></div>
<?php if(stripos($membership_plan, 'sponsor') !== false || stripos($membership_plan, 'senior') !== false){ ?>
<span class="alrt"><span class="awa sbg"></span>
<span class="c13 bo pl3"><a href="membership_plans.php" style="color:#333;">اشتراك سنوي</a></span>
</span>
<?php } else { 
    $sql_usr = "SELECT usr_credit FROM user WHERE usr_id = $uid";
    $res_usr = mysqli_query($con, $sql_usr);
    $row_usr = mysqli_fetch_object($res_usr);
    if(($row_usr->usr_credit ?? 0) < 20){ ?>
<span class="alrt"><span class="awa sbg"></span>لا يوجد رصيد كافٍ!<br>
<span class="c13 bo pl3"><a href="subscription.php" style="color:#333;">اشتر كريديت الآن</a></span>
</span>
<?php } } ?>
</div>
<?php } ?>

<div id="rtmain" class="lbx1" style="background-color:#FAF4FF;">
<p class="sbg d_bp1 bo f1"><?php echo date("d M, Y", strtotime($row->br_updated_date)); ?></p>
<div class="ef3">
<?php if(empty($row->br_preferred_supplier_location)){ ?>
<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" align="left" height="16" width="24">
<span class="e4 f1 wb e5"><b><?php echo get_country_name($row->country); ?></b></span>
<?php } else { ?>
<span class="e4 f1 wb e5"><b>
<?php
if($row->br_preferred_supplier_location == 'any') echo "من كل مكان";    
else if($row->br_preferred_supplier_location == 'abroad') echo "خارج البلاد";    
else if($row->br_preferred_supplier_location == 'domestic') {
    echo get_country_name($row->country); ?>
    &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" align="left" height="16" width="24">
<?php } else if($row->br_preferred_supplier_location == 'my_city' && $row->bnsprof_city != '0') {
    echo get_city_name($row->bnsprof_city);
} ?></b></span>
<?php } ?>
</div>

<?php if($uid > 0 && $uid != $row->br_u_id){
    $sql_chk = "SELECT * FROM purchased_buy_requirement WHERE pbr_usr_id = $uid AND pbr_br_id = " . (int)$row->br_id;
    $res_chk = mysqli_query($con, $sql_chk);
    if(mysqli_num_rows($res_chk) > 0){
        $row_chk = mysqli_fetch_object($res_chk);
?>
<div id="sourcediv1">
<div class="mt12 l1 k7 mb">
    <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px;padding:5px 27px;" onClick="purchaseLead(<?php echo (int)$row->br_id; ?>);">
        <span class="f1">تم شراؤها في: <?php echo date("d M, Y", strtotime($row_chk->pbr_purchase_date)); ?></span>
        <div class="inAr sbg"></div>
    </div>
</div>
</div>
<?php } else { 
    $sql_usr_chk = "SELECT usr_credit FROM user WHERE usr_id = $uid";
    $res_usr_chk = mysqli_query($con, $sql_usr_chk);
    $row_usr_chk = mysqli_fetch_object($res_usr_chk);
    $credit_available = (($row_usr_chk->usr_credit ?? 0) > 20) ? 1 : 0;
?>
<div id="sourcediv1">
<div class="mt12 l1 k7 mb">
<?php if(stripos($membership_plan, 'sponsor') === false && stripos($membership_plan, 'senior') === false && stripos($membership_plan, 'sponser') === false){ ?>
    <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px;padding:5px 27px;" <?php if(getUserCredit($uid) >= 20){ ?> onClick="purchaseLead(<?php echo (int)$row->br_id; ?>);" <?php } else { ?> onClick="showMessage();" <?php } ?>>
        اشتر هذا الليد الآن<br>
        <span class="f1">واحصل على بيانات المشتري</span>
        <div class="inAr sbg"></div>
        <div id="tps" class="doff sbg g1 k7">شراء هذا الليد سيتيح لك رؤية بيانات المشتري بالكامل</div>
    </div>
</div>
<div class="f3 mt11"> <strong class="z6 f4">20 كريديت</strong></div>
<?php } else { ?>
    <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px;padding:5px 27px;" onClick="purchaseLead(<?php echo (int)$row->br_id; ?>);">
        احصل على بيانات المشتري الآن<br>
        <span class="f1">وتواصل مع المشتري</span>
        <div class="inAr sbg"></div>
        <div id="tps" class="doff sbg g1 k7">حصولك على طلب الشراء سيتيح لك رؤية بيانات المشتري ومراسلته</div>
    </div>
</div>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
</div>

<table cellpadding="0" cellspacing="0" width="71%" style="margin:auto">
<tbody><tr><td>
<div class="c13" id="pkg" style="margin-top:20px;text-align:center;">
<?php if(stripos($membership_plan, 'sponsor') === false && stripos($membership_plan, 'senior') === false && stripos($membership_plan, 'sponser') === false) {
    if(getUserCredit($uid) == 0){ ?>
<h2 class="f4 ts1 w3">اختر خطة الكريديت</h2>
<div class="pkg">
<?php
$sql_mp = "SELECT * FROM membership_plan WHERE mp_status = '1'";
$res_mp = mysqli_query($con, $sql_mp);
while($row_mp = mysqli_fetch_object($res_mp)){
?>
<p class="c13 bdd" style="line-height:26px;font-size:16px;text-align:center;">
<?php echo $row_mp->mp_name; ?><br>
<span class="c12 bo f3"><?php echo $row_mp->mp_credits; ?> كريديت مقابل <?php echo getCurrencySymbol(); ?> <?php echo $row_mp->mp_amount; ?></span><br>              
<a onClick="choosePackage('<?php echo rand(10000,99999) . md5($row_mp->mp_id); ?>');" class="point" style="font-size:14px;padding:2px 8px; background:#0e4ec7; color:#fff; text-decoration:none; margin:5px auto 10px; display:inline-block; width:66px">اشتر الآن</a>
</p>
<?php } ?>
</div>
<?php }
} ?>
<div class="bsSd sbg"></div>
</div>
</td></tr>
</tbody>
</table>
</div>
</div>
<div class="clear"></div>
<?php include 'includes/footer.php'; ?>
</body>
</html>