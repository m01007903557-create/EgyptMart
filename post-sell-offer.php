<?php
// post-sell-offer.php - ترقية إلى PHP 8.3
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "common.php";

$_SESSION['last_page'] = "post-sell-offer.php";
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}
$uid = (int)$_SESSION['uid_indm'];

if(isset($_SESSION['pc_id'])){ $pc_id=$_SESSION['pc_id']; unset($_SESSION['pc_id']); }else{ $pc_id=""; }
if(isset($_SESSION['so_pc_id'])){ $so_pc_id=$_SESSION['so_pc_id']; unset($_SESSION['so_pc_id']); }else{ $so_pc_id=""; }
if(isset($_SESSION['so_service'])){ $so_service=$_SESSION['so_service']; unset($_SESSION['so_service']); }else{ $so_service=""; }
if(isset($_SESSION['so_description'])){ $so_description=$_SESSION['so_description']; unset($_SESSION['so_description']); }else{ $so_description=""; }
if(isset($_SESSION['so_validity'])){ $so_validity=$_SESSION['so_validity']; unset($_SESSION['so_validity']); }else{ $so_validity=""; }

class addSaleOffer
{
    var $msg;
    var $so_usr_id;
    var $main_cat;
    var $pc_id;
    var $so_pc_id;
    var $so_service;
    var $so_description;
    var $so_preferred_buyer_location;
    var $so_validity;
        
    function __construct($so_usr_id, $main_cat, $pc_id, $so_pc_id, $so_service, $so_description, $so_preferred_buyer_location, $so_validity)
    {    
        $this->so_usr_id=$so_usr_id;
        $this->main_cat=$main_cat;
        $this->pc_id=$pc_id;
        $this->so_pc_id=$so_pc_id;
        $this->so_service=$so_service;
        $this->so_description=$so_description;
        $this->so_preferred_buyer_location=$so_preferred_buyer_location;
        $this->so_validity=$so_validity;

        $_SESSION['main_cat']=$this->main_cat;
        $_SESSION['pc_id']=$this->pc_id;
        $_SESSION['so_pc_id']=$this->so_pc_id;
        $_SESSION['so_service']=$this->so_service;
        $_SESSION['so_description']=$this->so_description;
        $_SESSION['so_preferred_buyer_location']=$this->so_preferred_buyer_location;
        $_SESSION['so_validity']=$this->so_validity;        
    }
    
    function checkBadWord($param)
    {
        global $con;
        $valid=true;
        $sqlrpl = "SELECT bd_word FROM bad_word";
        $resrpl = mysqli_query($con, $sqlrpl);
        $letters = array();
        while($rowrpl = mysqli_fetch_object($resrpl))
        {        
            $letters[] = strtoupper($rowrpl->bd_word);
        }
        foreach($letters as $val)
        {
            $pos = strpos($param, $val);
            if ($pos !== false)
            {
                $valid=false;
            } 
        }
        return $valid;
    }
    
    function valid()
    {
        $valid=true;
        if($this->main_cat=="")
        {
            $this->msg='<font color="#FF0000">Kindly select Main Category.</font>';
            $valid=false;
        }
        else if($this->pc_id=="")
        {
            $this->msg='<font color="#FF0000">Kindly select Category.</font>';
            $valid=false;
        }
        else if($this->so_pc_id=="")
        {
            $this->msg='<font color="#FF0000">Kindly select Sub-Category.</font>';
            $valid=false;
        }
        else if($this->so_service=="")
        {
            $this->msg='<font color="#FF0000">Kindly enter Products / Services you want to Sell.</font>';
            $valid=false;
        }
        else if($this->so_service!="" && $this->checkBadWord(strtoupper($this->so_service))==false)
        {
            $this->msg= "<font color='#FF0000'>You can't post this Product / Service Name. It contains some Bad words.</font>";
            $valid=false;
        }
        else if($this->so_description == "")
        {
            $this->msg= '<font color="#FF0000">Kindly describe your Products / Services in detail.</font>';
            $valid=false;
        }
        else if($this->so_description!="" && $this->checkBadWord(strtoupper($this->so_description))==false)
        {
            $this->msg= "<font color='#FF0000'>You can't post this Product / Services in detail. It contains some Bad words.</font>";
            $valid=false;
        }
        return $valid;
    }
    
   function add()
{    
    global $con;
    
    if(isset($_FILES["so_pic"]["name"]) && $_FILES["so_pic"]["name"] != "")        
    {
        if ($_FILES["so_pic"]["error"] > 0)
        {
            $msg = "Return Code: " . $_FILES["so_pic"]["error"] . "<br />";
        }
        else
        {
            $this->so_pic='so-'.rand(0,9999).trim(addslashes($_FILES['so_pic']['name']));    
            $ds = move_uploaded_file($_FILES["so_pic"]["tmp_name"], "upload/sale_offer/".$this->so_pic) or die('error');    
                        
            $sql="INSERT INTO sale_offer
            SET
                so_usr_id='".$this->so_usr_id."',
                so_pc_id='".$this->so_pc_id."',
                so_service ='".$this->so_service."',
                so_description ='".$this->so_description."',
                so_preferred_buyer_location ='".$this->so_preferred_buyer_location."',
                so_validity ='".$this->so_validity."',
                so_pic ='".$this->so_pic."',
                so_approval_status='0',
                so_posting_date=NOW(),
                so_updated_date=NOW()";
        
            mysqli_query($con, $sql) or die(mysqli_error($con));
        }
    }
    else
    {
        $imgFile = "";
        
        if(isset($_POST['selected_gallery_image']) && !empty($_POST['selected_gallery_image'])) {
            $imgFile = basename(trim((string)$_POST['selected_gallery_image']));
            $imgFile = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $imgFile);
        }

        if($imgFile == "") {
            $sql_tsi = "SELECT * FROM temp_selloffer_image WHERE tsi_usr_id='" . $this->so_usr_id . "'";
            $res_tsi = mysqli_query($con, $sql_tsi);
            if(mysqli_num_rows($res_tsi)) {
                $row_tsi = mysqli_fetch_object($res_tsi);
                $imgFile = basename(trim((string)$row_tsi->tsi_image));
                $imgFile = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $imgFile);
            }
        }
        mysqli_query($con, "DELETE FROM temp_selloffer_image WHERE tsi_usr_id='" . $this->so_usr_id . "'");

        $sql = "INSERT INTO sale_offer
            SET
                so_usr_id='" . $this->so_usr_id . "',
                so_pc_id='" . $this->so_pc_id . "',
                so_service ='" . $this->so_service . "',
                so_description ='" . $this->so_description . "',
                so_preferred_buyer_location ='" . $this->so_preferred_buyer_location . "',
                so_validity ='" . $this->so_validity . "',
                so_pic ='" . $imgFile . "',
                so_approval_status='0',
                so_posting_date=NOW(),
                so_updated_date=NOW()";
                
        mysqli_query($con, $sql) or die(mysqli_error($con));
    }
    
    // ✅ تنظيف الجلسات (مرة واحدة فقط)
    unset($_SESSION['main_cat']);
    unset($_SESSION['pc_id']);
    unset($_SESSION['so_pc_id']);
    unset($_SESSION['so_service']);
    unset($_SESSION['so_description']);
    unset($_SESSION['so_preferred_buyer_location']);
    unset($_SESSION['so_validity']);
    
    $this->msg = '<font color="#009900">Sale Offer posted successfully.</font>';
}
}
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

if(isset($_POST['submitSaleOffrButt']))
{
    $typeofselection = isset($_POST['typeofselection']) ? $_POST['typeofselection'] : '';
    $keywordsFilter = isset($_POST['keywordsFilter1']) ? $_POST['keywordsFilter1'] : '';
    $valid = false;
    
    if($typeofselection){
        $valid = true;
        if($keywordsFilter=="")
        {
            $data[0]="0";
            $data[1]='Kindly enter Keyword.';
            $valid=false;
        }

        $searchedproducts = isset($_SESSION['searchedproducts']) ? $_SESSION['searchedproducts'] : array();

        if(!$searchedproducts || !array_key_exists($keywordsFilter, $searchedproducts)) {
            $data[0]="0";
            $data[1]='No category found with given keywords';
            $valid=false;
        }
        
        $keywordsFilterArr = explode(">>", $keywordsFilter);
        $keywordsFilter1 = end($keywordsFilterArr);
        $tnd_pc_id = isset($searchedproducts[$keywordsFilter1]) ? $searchedproducts[$keywordsFilter1] : 0;
        $_POST['so_pc_id'] = $tnd_pc_id;
        $_POST['pc_id'] = isset($searchedproducts[$keywordsFilterArr[1]]) ? $searchedproducts[$keywordsFilterArr[1]] : 0;
        $_POST['main_cat'] = isset($searchedproducts[$keywordsFilterArr[0]]) ? $searchedproducts[$keywordsFilterArr[0]] : 0;
        
        if(!$tnd_pc_id){
            $data[0]="0";
            $data[1]='No category found with given keywords';
            $valid=false;
        }
    }
    
    $adn=new addSaleOffer(
        addslashes(trim($_POST['so_usr_id'])), 
        addslashes(trim($_POST['main_cat'])), 
        addslashes(trim($_POST['pc_id'])), 
        addslashes(trim($_POST['so_pc_id'])), 
        addslashes(trim($_POST['so_service'])),
        addslashes(trim($_POST['so_description'])),
        addslashes(trim($_POST['so_preferred_buyer_location'])),
        addslashes(trim($_POST['so_validity']))
    );

    $key_cat_id = isset($_POST['so_pc_id']) ? $_POST['so_pc_id'] : 0;
    $uid = (int)$_SESSION['uid_indm'];

    $query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id='$key_cat_id' AND bac_usr_id='$uid'";    
    $r = mysqli_query($con, $query);    
    if(mysqli_num_rows($r) == 0){        
        $SQL_BUY_ALERT="INSERT INTO buylead_alert_category SET 
                        bac_usr_id=".$uid.",
                        bac_pc_id=".$key_cat_id.",
                        bac_updated_date=NOW()";
        mysqli_query($con, $SQL_BUY_ALERT) or die('Error in query while saving');
    }

    if($adn->valid() || $valid)
    {
        $adn->add();
        $sql_exist="SELECT * FROM buylead_alert_category WHERE bac_usr_id='".$_SESSION['uid_indm']."' AND bac_pc_id='". $_POST['so_pc_id']."'";
        $res12 = mysqli_query($con, $sql_exist);
        if(mysqli_num_rows($res12)==0){
            $sql_ins="INSERT INTO buylead_alert_category
                SET
                    bac_usr_id='".$_SESSION['uid_indm']."',
                    bac_pc_id='". $_POST['so_pc_id']."',
                    bac_updated_date=NOW()";
            mysqli_query($con, $sql_ins);
        }
        header("Location: post-sell-offer-res.php");
        exit;
    }
    else
    {
        $_SESSION['msg']=$adn->msg;
        header("Location: post-sell-offer.php");    
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/eto-post-sell.css" type="text/css" rel="STYLESHEET">
<link href="css/my-v1.css" type="text/css" rel="stylesheet">
<link href="css/c.css" type="text/css" rel="STYLESHEET">
<link href="css/jquery.css" type="text/css" rel="stylesheet">
<link href="css/ui.css" rel="stylesheet">
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/dir-new.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
});
function showCategory()
{
    var pc_id=document.getElementById('main_cat').value;
    $.post("ajax-file/showSubcat.php",{id:pc_id}, function(data){ $('#pc_id').html(data); showSubcat(); }); 
}
function showSubcat()
{
    var id=document.getElementById('pc_id').value;
    $.post("ajax-file/showSubcat.php",{id:id}, function(data){ $('#so_pc_id').html(data); }); 
}
function validSaleOffer()
{
    var typeofselection=document.getElementById('typeofselection');
    var keywordsFilter1=document.getElementById('keywordsFilter1');
    var main_cat=document.getElementById('main_cat');
    var pc_id=document.getElementById('pc_id');
    var so_pc_id=document.getElementById('so_pc_id');
    var so_service=document.getElementById('so_service');
    var so_description=document.getElementById('so_description');

    var message="";
    var valid=true;
    var typeofselectionvalue = typeofselection.value *1;
    
    if(typeofselectionvalue==0){
        if(main_cat.value=='')
        {
            message="Kindly select Main Category.";
            main_cat.focus();
            valid=false;
        }
        else if(pc_id.value=='')
        {
            message="Kindly select Category.";
            pc_id.focus();
            valid=false;
        }
        else if(so_pc_id.value=='')
        {
            message="Kindly select Sub-Category.";
            so_pc_id.focus();
            valid=false;
        }
    }
    else if(typeofselectionvalue && keywordsFilter1.value=='') {
        message="Kindly enter valid Search for category";
        keywordsFilter1.focus();
        valid=false;
    }
    else if(so_service.value=='')
    {
        message="Kindly enter Products / Services you want to Sell.";
        so_service.focus();
        valid=false;
    }
    else if(so_description.value == '')
    {
        message="Kindly describe your Products / Services in detail.";
        so_description.focus();
        valid=false;
    }
    if(!valid)
    {
        alert(message);
    }
    return valid;
}
</script>
<style>
#login_frm1
{
    border:1px solid #6F0000;color:#fff;text-decoration:none;font-size:14px; font-weight:bold; padding:5px;text-align:center;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;background-color:#DF0000;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');background:-webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));background:-moz-linear-gradient(top,  #DF0000,  #DF0000);cursor:pointer;font-family:Arial, Helvetica, sans-serif
}
</style>
</head>

<body class="search-show-box">
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>

    <div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

    <div class="inner_wrapper">
        <?php include "includes/header_menu.php"; ?>
        
        <div class="f1 w61n tb lh ml m2" id="lnav" style="display: block;">
            <ul class="nln1" style="margin: 0px; padding: 0px;" title="عروض البيع وطلبات الشراء">
                <li style="border-bottom: medium none;" title="Hot Sell Offers">
                    <h3>إعلانات عروض البيع الخاصة</h3>
                </li>
                <li class="lp"><a href="post-sell-offer.php" title="Post a New Sell Offer">»&nbsp; أنشـر عروض بيـع جديـدة </a></li>
                <li class="lp"><a href="manage-sell-offer.php" title="Manage Sell Offers">»&nbsp;إدارة عروض البيع </a></li>
                <li style="border-bottom: medium none;" title="Buy Lead Alerts">
                    <h3>إشعارات طلبات شراء الى بريدى</h3>
                </li>
                <li class="lp"><a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts ">»&nbsp;إدارة طلبات الشراء  </a></li>
            </ul>
        </div>
        
        <div class="w57 b1_m2 f1 wd797" id="ldiv">
            <div id="div2" style="display:block;">
                <div><img src="post-sell_offer_files/zero.gif" width="1" height="19"></div>
                <table width="100%" align="center">
                <tbody><tr>
                    <td>
                        <div align="left">
                            <div class="tw2l fl" id="formmain" style="margin-left:8px;background-color:#FAF4FF">
                                <div class="" id="lgn1" dir="ltr" style="text-align: right;">
                                    <p class="c-1 g2 fs bo1" title="Post Business Ads FREE" style="text-align: right;">أنشر عروض بيع خاصة  <span class="p6 q4 tm1 cbc fsz1"><i class="co"></i></span></p>
                                    <p class="ts1 ptp"></p>
                                </div>
                                <div>
                                    <form method="post" name="postForm1" action="" onsubmit="return validSaleOffer();" enctype="multipart/form-data">
                                        <div id="error_msg" style=""><?php echo $msg; ?></div>
                                        <input type="hidden" id="so_usr_id" name="so_usr_id" value="<?php echo $_SESSION['uid_indm']; ?>"/>

                                        <script type="text/javascript">
                                        function searchcat()
                                        {
                                            $("#scs").removeClass("tabclose").addClass("tabopen");
                                            $("#bcs").removeClass("tabopen").addClass("tabclose");
                                            $('#typeofselection').val(1);
                                            $(".bcc").css("display","none");
                                            $(".scc").removeAttr('style');
                                        }
                                        function beowswcat()
                                        {
                                            $("#bcs").removeClass("tabclose").addClass("tabopen");
                                            $("#scs").removeClass("tabopen").addClass("tabclose");
                                            $('#typeofselection').val(0);
                                            $(".scc").css("display","none");
                                            $(".bcc").removeAttr('style');
                                        }
                                        </script>
                                        
                                        <input type="hidden" value="0" id="typeofselection" name="typeofselection" />

                                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"><tbody>
                                        <tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
                                        <tr>
                                            <td class="tabclose" onclick="searchcat()" id="scs" width="152" title="Search Categories">حدد الأصناف تلقائيا</td>
                                            <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                            <td class="tabopen" onclick="beowswcat()" id="bcs" width="155" title="Browse Categories">تصفح وإختار الأصناف</td>
                                            <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                        </tr>
                                        </tbody></table></td></tr>
                                        </tbody></table>
                                        
                                        <table class="frm mt5" width="100%">
                                        <tbody>
                                        <tr class="scc" id="r0" style="display: none;">
                                            <td valign="middle" width="30%"><p class="pd15"><b style="font-size:13px;"><font color="#E95801"></font></b></p></td>
                                            <td valign="TOP">
                                                <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" name="keywordsFilter1" id="keywordsFilter1" style="width: 450px;float: left;" type="text" maxlength="60" size="33">
                                            </td>
                                        </tr>
                                        
                                        <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
                                        <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
                                        <script type="text/javascript">
                                        $(document).ready(function($113){
                                            lostFocus();
                                            $113('#keywordsFilter1').unbind().on('keyup', function() {
                                                var type11='Products';
                                                $113("#keywordsFilter1").autocomplete("autocomplete.php", {
                                                    selectFirst: true,
                                                    extraParams: {type:type11},
                                                    width: 407
                                                }).result(function(event, data, formatted) {
                                                    $("input#keywordsFilter1").val(data);
                                                });
                                            });
                                        });
                                        </script>

                                        <tr id="r0" style="height: 48px;" class="bcc">
                                            <td valign="middle" width="40%"><p class="pd15" title="Main Category: "><i>*</i><b>إختار التصنيف العام  </b></p></td>
                                            <td valign="TOP">
                                                <select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" style="height:30px;" onchange="showCategory()" title="إختار - التصنيف  العام -  الذى يندرج تحته منتجك أو خدمتك ">
                                                    <option value="">-- التصنيف  العام --</option>
                                                    <?php
                                                    $sql_pc="SELECT * FROM product_category WHERE pc_parent_id='0' AND pc_status='1'";
                                                    $res_pc=mysqli_query($con, $sql_pc);
                                                    while($row_pc=mysqli_fetch_object($res_pc)){
                                                    ?>
                                                    <option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr id="r1" style="height: 48px;" class="bcc">
                                            <td valign="middle" width="40%"><p class="pd15" title="Category: "><i>*</i><b>إختار التصنيف الرئيسى </b></p></td>
                                            <td valign="TOP">
                                                <select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" style="height:30px;" onchange="showSubcat()" title="إختار - التصنيف  الرئيسى - الذى يندرج تحته منتجك أو خدمتك">
                                                    <option value="">-- التصنيف الرئيسى --</option>
                                                    <?php
                                                    $sql_pc="SELECT * FROM product_category WHERE pc_parent_id!='0' AND pc_parent_id='".$main_cat."' AND pc_status='1'";
                                                    $res_pc=mysqli_query($con, $sql_pc);
                                                    while($row_pc=mysqli_fetch_object($res_pc)){
                                                    ?>
                                                    <option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <select class="bd4 hw6 mr3 htb" id="so_pc_id" name="so_pc_id" style="height:30px;" title="إختار - التصنيف  الفرعى - الذى يندرج تحته منتجك أو خدمتك">
                                                    <option value="" title="Select Sub Category ">-- إخـتار التصنيف الفرعى --</option>
                                                    <?php
                                                    $sql_spc="SELECT * FROM product_category WHERE pc_parent_id='".$pc_id."' AND pc_status='1' AND pc_parent_id!='0'";
                                                    $res_spc=mysqli_query($con, $sql_spc);
                                                    while($row_spc=mysqli_fetch_object($res_spc)){
                                                    ?>
                                                    <option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$so_pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>                        
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr id="r2" style="height: 48px;">
                                            <td valign="TOP" width="40%"><p class="pd15" title="Products / Services you want to Sell:"><i>*</i><b>أكتب عنوان المنتج أو الخدمة التى تريد بيعها </b></p><img src="post-sell_offer_files/zero.gif" width="190" height="1"></td>
                                            <td valign="TOP"><input name="so_service" id="so_service" style="width:450px;" class="bd4 hw6 mr3 htb" maxlength="90" value="<?php echo htmlspecialchars($so_service); ?>"/><div class="displayoff" id="hlp" style="line-height:14px;height:14px;"></div></td>
                                        </tr>
                                        
                                        <tr id="r3">
                                            <td valign="TOP" width="40%"><p class="pd15" title="Describe Your Products /Services in Detail:"><i>*</i><b>إوصف تفاصيل منتجك أو خدمتك بطريقة تساعد على جذب المشتريين</b><br /><b class="q4"></b><font class="co1" id="Charcount" color="#ff8000">2000</font><b class="fwn cbc">Characters Remaining : عدد الحروف لايقل الوصف عن</b></p></td>
                                            <td onmouseover="document.getElementById('tt2').style.display='block';" onmouseout="document.getElementById('tt2').style.display='none';" valign="TOP">
                                                <div id="lgn6" style="width: 360px; height: 105px;"><textarea aria-hidden="true" name="so_description" id="so_description" style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" rows="5" cols="30"><?php echo htmlspecialchars($so_description); ?></textarea></div>
                                            </td>
                                        </tr>
                                        
                                        <tr id="r4">
                                            <td valign="TOP" width="40%"><p class="pd15" title="Location Preferences:"><b>حدد أماكن بيع المنتج / الخدمة </b></p></td>
                                            <td valign="TOP">
                                                <div style="vertical-align:bottom">
                                                    <input type="radio" id="so_preferred_buyer_location_1" name="so_preferred_buyer_location" value="abroad" /><label style="top:0px;" title="Abroad Only "> هذا المنتج للتصدير فقط   </label>&nbsp;&nbsp;
                                                    <input type="radio" id="so_preferred_buyer_location_2" name="so_preferred_buyer_location" value="any" checked="checked"/><label style="top:0px;" title=" Abroad + Domestic"> هذا المنتج للتصدير أو للبيع الداخلى  </label>&nbsp;&nbsp;
                                                    <input type="radio" id="so_preferred_buyer_location_3" name="so_preferred_buyer_location" value="domestic"/><label style="top:0px;" title=" Domestic Only"> هذا المنتج للبيع داخل بلدى فقط  </label>&nbsp;&nbsp;
                                                    <input type="radio" id="so_preferred_buyer_location_4" name="so_preferred_buyer_location" value="my_city"/><label style="top:0px;" title=" My City Only ">  هذا المنتج للبيع داخل مدينتى فقط  </label>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                        <tr><td class="j1"><i class="co fl" style="height:26px;"> </i></td></tr>
                                        <tr><td colspan="2">&nbsp;</td></tr>
                                        
                                        <tr id="r4">
                                            <td class="pb1 pt2" valign="top"><b class="q4"></b><b>Product Picture:</b><br/>(Upload Images in .jpg, .jpeg, .png or .gif file format)</td>
                                            <td class="s pb" align="left">
                                                <table width="100%">
                                                <tr>
                                                    <td>
                                                        <div id="main" class="po-com1">
                                                            <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                                                            <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                                                            <script type="text/javascript">
                                                            jQuery(function(){
                                                                jQuery('#file_upload').uploadifive({
                                                                    'auto'     : true,
                                                                    'formData' : {'usr' : '<?php echo $_SESSION['uid_indm']; ?>'},
                                                                    'queueID'  : 'queue',
                                                                    'debug'    : true,
                                                                    'method'   : 'post',
                                                                    'uploadScript' : 'ajax-file/addTempSOImg.php',
                                                                    'onAddQueueItem' : function(file) {
                                                                        $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
                                                                    },
                                                                    'onUploadComplete' : function(file,data) {
                                                                        showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
                                                                    }
                                                                });
                                                            });
                                                            </script>
                                                            <div style="padding-left:18px;padding-top:5px;" id="img_disp">
                                                                <img src="https://egyptmart.shop/upload/sale_offer/no-image.png" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div id="drop" style="padding-left:10px;float:right">
                                                            <input type="file" id="file_upload" name="file_upload"/>
                                                        </div>
                                                        <div id="queue"></div>
                                                    </td>
                                                    <td>
                                                        <link rel="stylesheet" href="css/colorbox.css" />
                                                        <script src="js/jquery.colorbox.js"></script>
                                                        <script>
                                                        $(document).ready(function(){
                                                            $('.ajax').on('click', function() {
                                                                $.colorbox({href:$(this).attr('href'), open:true});
                                                                return false;
                                                            });
                                                            $(".inline").colorbox({inline:true, width:"50%"});
                                                            $("#click").click(function(){ 
                                                                $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
                                                                return false;
                                                            });
                                                        });
                                                        </script>
                                                        <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">Select from Image Gallery</a>
                                                    </td>
                                                </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                        </table>
                                        
                                        <p id="add_dtl" style="padding:5px 13px;color:#0000ff;cursor:pointer;float:right" onclick="javascript:document.getElementById('dtl').style.display='block';this.style.display='none';" title="Additional Information "><b style="color:#0000ff;">+</b>حدد مدة عرض الإعلان</p>
                                        <div style="display:none" id="dtl">
                                            <table class="frm" width="100%">
                                            <tbody><tr id="r20">
                                                <td><b class="q4" title="Validity of your product:"></b>حدد مدة عرض الاعلان<b></b></td>
                                                <td class="v">
                                                    <input name="so_validity" value="30" type="radio" title="1 Month "> شهر واحد
                                                    <input name="so_validity" value="90" type="radio">ثلاثة شهور
                                                    <input name="so_validity" value="365" checked="checked" type="radio">سنة كاملة
                                                    <span class="cc j1" style="display:block; margin-left:6px">(مدة عرض الاعلان التى اخترتها)</span>
                                                </td>
                                            </tr>
                                            </tbody></table>
                                        </div>
                                        <br>
                                        
<div class="a2 pt pb" id="loginsubmit" style="display: block;">
    <input name="frmsubmitbutton" value="login" type="hidden">
    <input name="submitSaleOffrButt" id="login_frm1" class="cr bo1 fsz1" style="height: 32px; width: 170px;" title="Submit your Offer" value="أنشـر الإعــلان  للبيــع " type="SUBMIT">
</div>

<!-- ✅ الحقل المخفي لصورة الجاليري -->
<input type="hidden" name="selected_gallery_image" id="selected_gallery_image" value="">

</form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var imageBasket = [];
function usePhotoToUpload(id){
    if(jQuery.inArray(id,imageBasket) != -1){
        imageBasket= $.grep(imageBasket, function(value) {
            return value != id;
        });
    }else{
        imageBasket.push(id);
    }
}
function usePhoto(id)
{
    var tbl='temp_selloffer_image';
    var usr=document.getElementById('so_usr_id').value;
    if(imageBasket.length > 0){  
        id = imageBasket.pop();
    }
    $.post("ajax-file/addNewImgFrmGallery.php", {id:id, usr:usr, tbl:tbl}, function(data){
        var result = data;
        if (typeof result === 'string') {
            try {
                result = JSON.parse(result);
            } catch(e) {
                result = {};
            }
        }
        if (result && result.success) {
            $('#selected_gallery_image').val(result.image_file || '');
            $('#cboxClose').click();
            var previewPath = result.image_path || '';
            if (previewPath) {
                $("#img_disp").html('<img src="'+previewPath+'?v='+(new Date().getTime())+'" alt="" height="100" width="125"/>');
            } else {
                showTempPhoto(usr);
            }
        } else {
            alert((result && result.error) ? result.error : 'Image could not be selected');
        }
    }, 'json');
}
        
function showTempPhoto(usr)
{
    $.get("ajax-file/showTempSaleofferImage.php", {usr:usr}, function(data){
        $("#img_disp").html('<img src="'+data+'?v='+(new Date().getTime())+'" alt="" height="100" width="125"/>');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
