<?php
include 'common.php';

$token=substr($_GET['token'],4);

$pdresk=mysqli_query($con, "select * from products where md5(pd_id)='".$token."' and pd_status='1' ");
$pdrowk=mysqli_fetch_object($pdresk);
?>
<html>
<style>
.dtl-ul {margin:0;display: table;font-family: Verdana, Geneva, sans-serif;font-size: 12px;padding: 0 0 5px 0;text-align: left;}

.dtl-li-1 {color: #000; display: table-cell;float: left;list-style: none outside none;padding: 2px 0px 2px 10px;width: 170px;text-shadow: 0 1px 0 #FFFFFF;}

.dtl-li-2 {color: #000;float: left;font-weight: bold;list-style: none outside none;margin: 0;padding: 2px;}

.dtl-li-3 {border-left: 1px solid #CCCCCC;color: #222;list-style: none outside none;margin-left: 190px;padding-left: 15px;padding-top:2px; padding-bottom:2px;

width:410px;word-wrap: break-word;text-shadow: 0 1px 0 #FFFFFF;}

.dtl-li-4 {list-style:none;width:529px;height:1px; clear:both; margin-bottom:3px;*float:left;}

.body{background-image: url(/gifs/trust.4_01.jpg);background-repeat: repeat-x;height: 91px;width: 100%;}

.value-ul{border-right:1px solid #999;color:#000;font-family:Verdana, Geneva, sans-serif;font-size: 12px;padding: 0 20px 0 8px;text-align:left;width: 320px;margin-top:4px}

.value-ul li{list-style:circle;margin-left:10px;}

.h-div{width: 533px;margin-bottom: 5px;}

.co-nm {width:500px; *width:410px;margin-top:20px; padding-left:5px; padding-bottom:5px; color:#fff; font-family:Verdana, Geneva, sans-serif;font-size:17px;text-align:right; font-weight:bold; line-height:18px}
/*.c-name{height:100px; border:1px solid #dae9f0; background-image:url(/gifs/co-bgn.png); margin:0px; background-repeat: repeat-x;}*/

.c-name { border:1px solid #dae9f0; background-image:url("/gifs/comany-bg.png"); margin:0px; background-repeat: repeat-x; background-color:#96d3f6;}

/*.logo-div{border:4px solid Lavender; solid #ccc; height:80px;width:90px; float:left; margin:5px; background-color:#FFF;}*/
.logo-div {background-color: #FFFFFF;border: 4px solid Lavender;float: left;height: 80px;margin: 5px;text-align: center;width: 90px;}

/*.co-nm{width:500px; *width:410px;height:75px; margin-top:20px; padding-left:5px; padding-bottom:5px; color:#fff; font-family:Verdana, Geneva, sans-serif;font-size:17px;text-align:right; font-weight:bold; line-height:22px}*/

.city-cl{font-size:11px; color:#000;text-align:right; font-weight:bold;}

.ps-info{background-color:#d1d2d4; padding:0 2px;}

.ps-bakgr{background-color:#e7e8e9;height:33px; border-bottom:1px solid #D1D2D4; color:#58585A; text-align:left;font-family: Arial, Helvetica, sans-serif; font-size:16px; font-weight:600; background-image:url("/gifs/cntr-bg.png"); background-repeat:repeat-x;}

.ps-buy{padding:1px 1px 0 0; margin:0; text-align:right; float:right;}

.ps-block{padding:1px 0px; font:Arial; text-align:left;background-color:#f9f9f9;}

.tst-back{background-color:#d1d2d4; padding:0 2px 5px 2px; border:1px solid #d1d2d4;}

.tst-bg-img{background-color:#fff;height:33px; border-bottom:1px solid #D1D2D4; color:#58585A; text-align:left;font-family: Arial, Helvetica, sans-serif; font-size:16px; font-weight:600; background-image:url(/gifs/cntr-bg.png);}

.samp-blck{background-color:#e7e8e9;height:33px; border-bottom:1px solid #D1D2D4; color:#58585A; text-align:left;font-family: Arial, Helvetica, sans-serif; font-size:16px; font-weight:600; background-image:url(/gifs/cntr-bg.png); background-repeat:repeat-x;}

.smp-blck{background-color: #f9f9f9;margin-bottom: 10px;padding:10px 0;width:100%;}

.smp-hd-line {color: #555555;font-family: Verdana,Geneva,sans-serif;font-weight: 600;margin: 0;padding: 5px 5px 5px 11px;text-align: left;}

.sp-dv-blck{font-family: Verdana,Geneva,sans-serif; text-align: left; padding-left: 10px; color: rgb(85, 85, 85); padding-bottom:10px;}

.pdf-blck{float: right; text-align: center; padding: 0px 20px;margin: 0 5px 0 0;}

.pdf-txt{color: rgb(102, 102, 102); width: 120px; text-decoration: none; font-weight: bold; font-family: tahoma; font-size: 14px; border-bottom:1px solid #666;}

.tp-block{padding:1px 0px; font:Arial; text-align:left;background-color:#f9f9f9;margin-bottom:3px; margin:0;}
.ps-header {margin:0;background-color: #e9e9e9;background-image:url(/gifs/triangle1.png);background-position: 190px 1px;*background-position: 213px 1px;background-repeat: no-repeat;height: 19px;padding: 7px 10px;width: 193px;*width: 216px;text-shadow: 0 1px 1px #FFFFFF;}
.tst-block {margin:0;background-color: #e9e9e9;background-image:url(/gifs/triangle1.png);background-position: 190px 1px;*background-position:213px 1px;background-repeat: no-repeat;height: 19px;padding:7px 10px;width: 193px;*width: 216px;text-shadow: 0 1px 1px #FFFFFF;}
.sample-bg {margin:0;background-color: #e9e9e9;background-image:url(/gifs/triangle1.png);background-position: 190px 1px;*background-position: 213px 1px;background-repeat: no-repeat;height: 19px;padding: 7px 10px;width: 193px;*width: 216px;pxtext-shadow: 0 1px 1px #FFFFFF;}

*{margin: 0;}

</style>

<div class="tst-back" style="width:700px;">
<div class="tst-bg-img"><p class="tst-block">Product Details</p></div>
<div class="tp-block">
<ul class="dtl-ul">
<li class="dtl-li-1">Image</li><li class="dtl-li-3"><img src="upload/myproduct/<?php echo $pdrowk->pd_image;?>" width="100"></li>
<li class="dtl-li-4"><img alt="" src="images/div-1.png" align="left" width="100%" height="1"></li>
<li class="dtl-li-1">Title</li><li class="dtl-li-3"><?php echo $pdrowk->pd_title;?></li>
<li class="dtl-li-4"><img alt="" src="images/div-1.png" align="left" width="100%" height="1"></li>
<li class="dtl-li-1">Description</li><li class="dtl-li-3"><?php echo $pdrowk->pd_desc;?></li>
<li class="dtl-li-4"><img alt="" src="images/div-1.png" align="left" width="100%" height="1"></li>
<li class="dtl-li-1">Item Code</li>
<li class="dtl-li-3"><?php echo $pdrowk->pd_code;?></li>
<li class="dtl-li-4"><img alt="" src="images/div-1.png" align="left" width="100%" height="1"></li>
<li class="dtl-li-1">Updated date</li>
<li class="dtl-li-3"><?php echo date('d M, Y', strtotime($pdrowk->pd_date)); ?></li>
</ul>
</div> 
<p style=" clear:both;margin:1px;"></p>   <div class="samp-blck"><div class="ps-buy" align="right">
</div>
</div>
</div>
</div>
</html>