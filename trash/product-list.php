<?php
include 'common.php';

$_SESSION['last_page']="product-list.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
  header("Location:sign-in.php"); 
}
$uid=$_SESSION['uid_indm'];
if(isset($_GET['pageno'])) 
{
$pageno = $_GET['pageno'];
}
else 
{
$pageno = 1;
} 
$pdsqlk=mysqli_query($con, "select * from products where pd_uid ='".$uid."' and pd_status ='1' order by pd_id desc");
$totproduct=mysqli_num_rows($pdsqlk);
if($totproduct==0)
{
  header("location:product-add.php");
}

$limits = 10;
$total_pages = ceil($totproduct/$limits); 
$start_limit=$limits *($pageno-1);

$pdsql=mysqli_query($con, "select * from products where pd_uid ='".$uid."' and pd_status ='1' order by pd_id desc limit $start_limit,$limits");

$showitems=$start_limit+1 ."-";
if(($start_limit+$limits)<$totproduct)
{
  $showitems.=$start_limit+$limits;
}
else
{
  $showitems.=$totproduct;
}
  $showitems.= " of ". $totproduct." "; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="css/pro.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script>
function showdeloption(id)
{
  $(".abouteditdv").hide();
  $(".abtListdv").show(); 
$("#dcon"+id).slideDown('slow');
}
function hidedeloption(id)
{
  $("#dcon"+id).slideUp('slow');
}
function delmprofile(id)
{
  $.get("ajax-file/delproduct.php", {id:id},function(data){ location.reload();  });   
}

function showdesc(id)
{
$("#base_desc_hd"+id).show(); 
$("#less_sd"+id).show();
$("#base_desc_sd"+id).hide(); 
$("#less_hd"+id).hide();
}

function hidedesc(id)
{
$("#base_desc_hd"+id).hide(); 
$("#less_sd"+id).hide();
$("#base_desc_sd"+id).show(); 
$("#less_hd"+id).show();    
}

function prostatchange(id)
{
  $.get("ajax-file/product-change.php", {id:id},function(data){ $("#pstch"+id).html(data);    });   
}

function markhot(id)
{
  $('#pstch'+id).html('<img src=images/indicator.gif border=0>');
  $.get("ajax-file/markhot-add.php", {id:id}, function(data){   prostatchange(id);    });
}

function pushedtotop(id)
{
  $.get("ajax-file/pushedtotop.php", {id:id}, function(data){   prostatchange(id);    });
}
$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://arabyos.com/server/php/';
    jQuery('#fileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
               jQuery('#business_documents').val(file.name);
			   jQuery('#business_doc').attr('src',file.thumbnailUrl);
                       list_photo();	

            });
        }
       
    })
});

</script>
</head>

<body>

<?php include "includes/header_new.php"; ?>

<div class="bt" style="padding-top: 0px;margin-top: -30px;"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

<!-- Header End Here::-->
<div class="inner_wrapper">
    <?php include 'includes/header_menu.php';?> 
    <!--left navigation:start-->
    <?php include 'includes/left_menu.php';?>
    <!--left navigation:ends--> 
  <div class="w56b f1 p2b p14 blr" style=" height:auto">
  <h1>Manage Products ( Edit / Delete / Add )</h1>
  <div class="mt5"><!--manage products/groups:start-->
    <div class="ap1">
      
    <p class="f2 mt12" id="page_str"><span class="cpr link1"><strong><?php echo $showitems;?></strong></span></p>
    
    <div class="c3">
    <?php if($totproduct>0){ ?>   
    <?php
    if ($pageno>1){
  ?>  
   <a href="product-list.php?pageno=<?php echo $page=($pageno-1);?>" class="vu">Previous</a> 
  <?php 
  }
   if($totproduct>$limits)
   {
   for ($i = 1; $i <= $total_pages; $i++){
   if ($pageno == $i)
   {
   ?>
   <span class="b3 w1 p2 w3 p4 mg"><?php echo $i;?></span>
   <?php } else { ?>
   <a href="product-list.php?pageno=<?php echo $i;?>" class="b3 b2 w1 p2 w3 p4 vu mg"><?php echo $i;?></a>
   <?php } } }
   if($pageno<$total_pages)
   {
   ?>
   <a href="product-list.php?pageno=<?php echo $page=($pageno+1);?>" class="vu">Next</a>           
   <?php     
   }
   ?>
   
    <?php } ?>
        </div>

    </div>
    <!--manage products/groups:ends--><input name="pcid" id="pcid" value="3115715" type="hidden">
  <!--you are here:start-->
      
  
      <p class="urh psrh2">
  <span class="fr" style="padding:0;margin-right:0"><span style="padding:0;margin-right:0" class="allp"><span class="cpr" style="padding:0;margin-right:0;display:none"><span style="margin-right:0">&nbsp;</span></span></span></span>
    <a class="f2 fw pro-colr apr" href="product-add.php">Add Product</a>
  <a href="javascript:void(0);" class="f11 pl4 f1 cls-gry" id="pgcls_close" style="display:none">x&nbsp;close</a>
    &nbsp;<span id="wait_img"></span></p>
  
      <p class="urh" style="display:none"><span class="f1" style="font-size:14px;">Current Product Group:</span> <strong style="color:#444444">electronics (2)</strong></p>
      <!--you are here:end-->
      <!--<strong id="curr_act">All Products</strong>-->
    <div id="list_view">
  <?php
  while($pdrow = mysqli_fetch_object($pdsql))
  {
  ?>
        <link href="css/colorbox.css" type="text/css" rel="stylesheet">
        <script src="js/jquery.colorbox.js"></script>

    <script>
      $(document).ready(function(){
        //Examples of how to assign the ColorBox event to elements
        
        $(".ajax").colorbox();
        $(".inline").colorbox({inline:true, width:"50%"});
        //Example of preserving a JavaScript event for inline calls.
        $("#click").click(function(){ 
          $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
          return false;
        });
      });
    </script>
    <div class="plst mse wid abtListdv" id="pro_46536582">
    <ul>
    <li class="f1 prt" style="width:125px; height:125px;">
    <div style="position: relative;">
        <?php if($pdrow->pd_image!=""){ 
             $img=explode(',',$pdrow->pd_image);
        ?>
        <a><img src="upload/myproduct/<?php echo $img[0]; ?>" alt="<?php echo $pdrow->pd_title;?>" class="pro" border="0" style="width:100%; height:auto;"></a>
        <?php } else { ?>
        <a><img src="images/noimage.jpg"  class="pro" border="0" width="125" height="107"></a>
         <?php } ?>

         <?php if(count($pdrow->pd_imagelogo)){ 
             $logo=explode(',',$pdrow->pd_imagelogo);
        ?>
        <a>
        <?php if($logo[0]!=''){ ?>
            
        <img src="upload/myproduct/<?php echo $logo[0]; ?>"   border="0" style="position: absolute;    top: 82px;left:0px; width: 42px;height: 41px;"></a>   <?php }else { ?>
			<img src="upload/myproduct/pic-logo.png" border="0" style="position: absolute;top: 82px;left:0px; width: 42px;height: 41px;">
        <?php } }?>
        </div>
    </li>
    <li class="f2" style="margin-top:20px;">
    <div class="f1 p-cont mrgleft">
    <h1 class="f1 itm_colr" id="itemname_46536582"><?php echo $pdrow->pd_title;?></h1>
    <div class="c3"></div>
    <span class="f1 msenew">Last modified on: <?php echo date('d M, Y', strtotime($pdrow->pd_date)); ?></span>
  
        <div  id="base_desc_hd<?php echo $pdrow->pd_id; ?>" style="margin-right:20px;color: #222222; display:none;" class="disc c3"><?php echo htmlentities($pdrow->pd_desc); ?></div> 
    <div id="base_desc_sd<?php echo $pdrow->pd_id; ?>" style="margin-right:20px;color: #222222;" class="disc c3">
    <?php echo htmlentities(substr($pdrow->pd_desc,0,296)); ?>
        </div>
        
    <?php if(strlen($pdrow->pd_desc)>296) { ?>
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline; cursor:pointer;" id="less_hd<?php echo $pdrow->pd_id; ?>" onClick="showdesc(<?php echo $pdrow->pd_id; ?>)">
View Complete Details</a>
          <?php } ?>
            <span id="less_sd<?php echo $pdrow->pd_id; ?>" style="display:none;"> 
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline;cursor:pointer;" onClick="hidedesc(<?php echo $pdrow->pd_id; ?>)">
Less</a></span>
    </div>
    <div class="f2" style="width:100px;margin-left:20px">
        <script language="javascript">prostatchange(<?php echo $pdrow->pd_id; ?>)</script>
    <div id="pstch<?php echo $pdrow->pd_id; ?>"></div>
        
    <div>
    <span class="link1 cpr" style="*margin-bottom:5px">   
    <a class="b-img edi f1" href="product-edit.php?token=<?php echo rand(1000,9999).md5($pdrow->pd_id)?>" style="*float:none">Edit</a></span>
    <a class="del b-img f1 c3" onclick="showdeloption(<?php echo $pdrow->pd_id; ?>)" style="*float:none; cursor:pointer;">Delete</a></div>
    </div>
    </li>
        <?php if($pdrow->pd_image!=""){ ?>
    <li class="wtmp wtmpie">
        <a href="productzoomimage.php?token=<?php echo rand(1000,9999).md5($pdrow->pd_id);?>" class="ajax" style="cursor:pointer;">
        <div class="f2 zoom2 mrgzoom"></div>
        </a>
        </li>
        <?php } ?>
    </ul>
    <div id="actb_46536582" class="c3 pddng"></div>
    
    <div class="info bnr mt12 c3 abouteditdv" id="dcon<?php echo $pdrow->pd_id; ?>" style="display:none;height:33px;margin-bottom:5px">
    <div style="width:125px;" class="f2">   
    <a onclick="delmprofile(<?php echo $pdrow->pd_id; ?>);" class="yn" id="yes_46536582" style="cursor:pointer;">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a onclick="hidedeloption(<?php echo $pdrow->pd_id; ?>)" class="yn" id="no_46536582" style="cursor:pointer;">No</a></div>
    Do you really want to delete this product?
    </div>
        </div>
        <?php } ?>
    <!--products listing for 46536582:end-->

  </div>
    

  </div></div>
    <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
        </div>
    <!--footer:start-->
    <?php include 'includes/footer.php'; ?>