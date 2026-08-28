<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];

if(isset($_GET['pageno'])) 
{
$pageno = $_GET['pageno'];
}
else 
{
$pageno = 1;
} 

$newsql=mysqli_query($con, "select * from news where nws_uid ='".$uid."' and nws_status='1' order by nws_id desc"); 
$totnews=mysqli_num_rows($newsql);

$limits = 30;
$total_pages = ceil($totnews/$limits); 
$start_limit=$limits *($pageno-1);

$newsqlk=mysqli_query($con, "select * from news where nws_uid ='".$uid."' and nws_status='1' order by nws_id desc limit $start_limit,$limits");

$showitems=$start_limit+1 ."-";
if(($start_limit+$limits)<$totnews)
{
	$showitems.=$start_limit+$limits;
}
else
{
	$showitems.=$totnews;
}
	$showitems.= " of ". $totnews." ";	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/n.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script>
function addnews(id)
{
var nws_postdate=$("#nws_postdate").val();
var nws_medianm=$("#nws_medianm").val();
var nws_mediatyp=$("#nws_mediatyp").val();	
var nws_headline=$("#nws_headline").val();
var nws_covgurl=$("#nws_covgurl").val();	
var nws_covgdet=$("#nws_covgdet").val();
var cdate=$("#cdate").val();	

if(nws_postdate!="" && (new Date(nws_postdate).getTime() > new Date(cdate).getTime()))
{
    alert("You can't select the date after Current Date");
}
else if(nws_covgurl!= '' && !nws_covgurl.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
{
	alert("Please Enter a Valid url link");
}
else if(nws_covgdet=="")
{
	alert("News / Press Release Detail cannot be blank.");
}
else if(nws_covgdet!="" && nws_covgdet.length>4000)
{
	alert("News / Press Release Detail cannot have more than 4000 characters.");
}
else
{
	$.get("ajax-file/newsadd.php", {id:id,
	nws_postdate:nws_postdate,nws_medianm:nws_medianm,nws_mediatyp:nws_mediatyp,nws_headline:nws_headline,nws_covgurl:nws_covgurl,nws_covgdet:nws_covgdet},
	function(data){
	var d=data.split('||');
	if(d[1]==0)
	{
	alert(d[0]);
	}
	if(d[1]==1)
	{
	location.reload();
	}
	});		
}
}
</script>

<script>
function formopend()
{
$("#form_tst1").show();	
}

function formclose()
{
$("#form_tst1").hide();	
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

function showdeloption(id)
{
$("#dcon"+id).slideDown('slow');
}
function hidedeloption(id)
{
$("#dcon"+id).slideUp('slow');
}
function delnews(id)
{
$.get("ajax-file/delnews.php", {id:id},
	function(data){
	location.reload();
	//alert(data);
	});		
}

</script>



</head>

<body>
<div class="hm1 bbc" id="res-mob1">
<?php include "includes/header_new.php"; ?>
	<br><br>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>


<!-- Header End Here::-->
<?php include 'includes/header_menu.php';?>
		<!--company profile--
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56 f1 p2b p14 blr" id="id_attribute_value" style="width:756px;hight:100%;">
        <div id="message1" style="position: absolute; width: 100%; display: none; top: 0px; left: 0px; z-index: 1000; height: 1189px;" class="sec-open" align="CENTER">
	<div style="height: 0px;" id="divheight"></div>
	<form style="margin:0px;" name="dataform">
	<table style="height: 598px; width: 1344px;" id="tableheight" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
		<td>
		<table id="tableheight" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="720">
		<tbody><tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" height="38">
			<tbody><tr>
				<td><a href="javascript:show_alert_off('message1')"><img src="images/blue-cross1.gif" border="0" width="24" height="25"></a><br>
				<img src="images/blue-cross2.gif" width="24" height="13"></td>
				<td class="p_e" align="LEFT" background="images/blue-bggg.gif" width="100%">
				<div class="tsfrom">News</div></td>

				<td class="p_e" align="CENTER" background="newsadd_files/blue-cor.gif" width="70"><img src="images/zero.gif" width="70" height="1"><br>
				<a href="javascript:show_alert_off('message1');"><img src="images/close-bt.gif" border="0" width="59" height="25" hspace="4"></a></td>
			</tr>
			</tbody></table>

			<div style="background-image:url(images/bg_w.gif)">

			<div style="display: block;" id="mysaveid">
			<table align="center" border="0" cellpadding="4" cellspacing="0" width="490">
		
			<tbody><tr>
				<td class="label" width="135">Date</td>
				<td align="left"><input maxlength="60" name="todays_date1" value="28-JAN-2014" class="a_f dat" onfocus="displayCalendar(document.dataform.todays_date1,'dd-mm-yyyy',this,'','','todays_date')" onclick="displayCalendar(document.dataform.todays_date1,'dd-mm-yyyy',this,'','','todays_date')" id="todays_date" readonly="readonly" type="text"></td>
			</tr>

			<tr>
				<td class="label">Media Name</td>
				<td align="left"><input maxlength="100" name="n_name" id="n_name" class="a_f rf" value="sbp anand"></td>
			</tr>

			<tr>
				<td class="label">Media Type</td>
				<td align="left">
					<select name="n_type" id="n_type" class="a_f cof">
					<option value="">Select Media Type</option>
					<option value="Newspaper">Newspaper</option>
					<option selected="selected" value="Television">Television</option></select>
				</td>
			</tr>

			<tr>
				<td class="label">Headline</td>
				<td align="left"><input maxlength="100" id="n_headline" name="n_headline" class="a_f rf" value="test news"></td>
			</tr>

			<tr>
				<td class="label">News Coverage URL</td>
				<td align="left"><input name="n_url" id="n_url" maxlength="93" class="a_f rf" type="text"></td>
			</tr>

			<tr>
				<td class="label"><span>*</span>&nbsp;News Coverage Detail</td>
				<td align="left"><textarea aria-hidden="true" rows="10" cols="80" name="n_desc1" id="n_desc1" class="a_f rf" style="width: 322px; display: none;"></textarea>			                                 <input name="n_desc" id="popup_n_desc" value="test news" type="hidden">
				<div class="max f11"><font id="Charcount1" color="#ff8000">9 character (maximum of 4000)</font> character(s).</div></td>
			</tr>

			<tr>
				<td class="label">Add Image</td>
				<td align="left">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody><tr>
					<td align="center" valign="top" width="20%">Small Image
					<div class="small_imgnew">
					<div id="old_img_form"><img src="images/add-image.gif" width="125" height="125"></div>
					</div>


					<iframe allowtransparency="true" src="newsadd_files/upload-save-newspress.htm" id="img_upload_iframe" border="0" framespacing="0" margintop="0" marginleft="0" scrolling="no" width="107" frameborder="0" height="30">
					</iframe>

					<div style="display: none;" id="delete_smallimg_popup" class="remove_img"><a href="javascript:delete_smallimg()" style="text-decoration:none"><font size="1px"><b>remove</b></font></a></div>
					</td>

					<td width="3%">&nbsp;</td>
		
					<td align="center" valign="top">Large Image
					<div class="upl-imag-blo">
					<div id="new_img_form"><img src="images/620121103103511.jpg" id="img_large_form_225801" onload="resize_img(this,'http://3.imimg.com/data3/JP/QD/MY-9061497/620121103103511.jpg',125,125);" width="125" height="125"></div>
					</div>
					<iframe allowtransparency="true" src="newsadd_files/upload-save-newspress-blowup.htm" id="img_upload_iframe_blowup" border="0" framespacing="0" margintop="0" marginleft="0" scrolling="no" width="107" frameborder="0" height="26">
					</iframe>				

					<div style="display: block;" id="delete_largeimg_popup" class="remove_img"><a href="javascript:delete_largeimg()" style="text-decoration:none"><font size="1px"><b>remove</b></font></a></div>
					</td>
				</tr>
				</tbody></table>
				</td>
			</tr>

			<tr>
				<td>&nbsp;</td>
				<td align="left"><iframe name="edit-save" src="newsadd_files/newspress_save.htm" marginwidth="0" marginheight="0" noresize="noresize" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="170" frameborder="0" height="40"></iframe></td>
			</tr>

			</tbody></table>
			</div>
			<div id="myhideid" style="display: none;">
			<table style="border:0px;" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="95%"> <tbody><tr> <td style="padding-top:0px;" align="CENTER" bgcolor="#F4FBFF" width="100%" height="190"> <div class="prossessing">Processing.....<br> <img src="images/loading2.gif" vspace="4"></div></td> </tr> </tbody></table>
			</div>
			</div>
			<table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody><tr>
				<td><img src="images/white-1.gif" width="24" height="15"></td>
				<td align="CENTER" background="newsadd_files/white-bg.gif" width="100%"></td>
				<td><img src="images/white-2.gif" width="18" height="15"></td>
			</tr>
			</tbody></table>
			</td>
		</tr>
		</tbody></table>
		</td>
	</tr>
	</tbody>
    </table>
	</form>
	</div>
	<div>
	<div id="chg_name" class="f1 chng_a chng_b"><h1 class="f1" id="cpf_name">News</h1></div><p id="pf_change" style="display:none;float:left;margin-top:0px"></p>
		
	<p class="f2" style="margin-top:11px; color: #222222" id="news_cnt"><strong><?php echo $showitems;?></strong></p>
	<div class="c3"></div>
	</div>
		<div class="mt5"><p class="aml"></p><div class="utab"><span>Add News to your Online Catalog:</span>
        <a style="display: block;" class="f2 fw apr1" onclick="formopend('add');" href="news-list.php#form_tst1" id="edit_addnews">Add News</a></div></div>
		<a name="add"></a>
		<div style="display: block;" id="add_msg_div">
		<div id="cpd" class="arrow cover_div" style="display:none">
			There is no News added to your Online Catalog,<br>
			Start Adding News Now!
			</div>
		</div>
		<div id="gap" class="c3" style="display: block;">&nbsp;</div>
        
	<!--add news form:start-->
	<div id="form_tst1" style="display:<?php if($totnews<=0){ ?>block<?php } else { ?> none <?php }?>;">
	<div id="newspresscov" align="center">
	<form name="ad_newspress" onsubmit="return Check_Validation('ad_newspress','n_desc');" method="post" action="">

	<div class="frm_a clb">
	<div class="clb"><a class="f11 fr" href="javascript:formclose();">close [x]</a></div>
    
	<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
	<script src="calendar/calendar_js_css/js_calendar.js" type="text/javascript"></script>
	<table align="center" border="0" cellpadding="4" cellspacing="0" width="490">
	<tbody>
    <tr>
		<td class="label" width="135">Date</td>
		<td align="left"><div id="a1" class="tbp tbm5" style="display:none"><div class="t1a" align="left">Please pick a date.</div></div>
		       
         <input type="text" maxlength="60" class="a_f dat" id="nws_postdate" name="nws_postdate" value="" readonly="readonly" onclick="displayCalendar(document.getElementById('nws_postdate'),'yyyy-mm-dd',this)"/>
        </td>
	</tr>

	<tr>
		<td class="label">Media Name</td>
		<td align="left"><div id="a2" class="tbp cona" style="display:none"><div class="t1a" align="left">Please mention the name of the media (newspaper or TV Channel) where the news has been published/broadcasted.</div></div>
        <input maxlength="100" name="nws_medianm" class="a_f rf" id="nws_medianm" value="<?php echo $nws_medianm; ?>"></td>
	</tr>

	<tr>
		<td class="label">Media Type</td>
		<td align="left">
			<div id="a3" class="tbp cona" style="display:none"><div class="t1a" align="left">Please select the media from this list.</div></div>
			<select name="nws_mediatyp" id="nws_mediatyp" class="a_f cof">
			<option value="">Select Media Type</option>
			<option value="Newspaper" <?php if($nws_mediatyp=="Newspaper"){ ?> selected="selected" <?php } ?> >Newspaper</option>
			<option value="Television" <?php if($nws_mediatyp=="Television"){ ?> selected="selected" <?php } ?> >Television</option>
            </select>
		</td>
	</tr>

	<tr>
		<td class="label">Headline</td>
		<td align="left"><div id="a4" class="tbp cona" style="display:none"><div class="t1a" align="left">Please enter the headline of your news coverage.</div></div>
        <input maxlength="100" name="nws_headline" id="nws_headline" class="a_f rf" value="<?php echo $nws_headline; ?>"></td>
	</tr>

	<tr>
		<td class="label">News Coverage URL</td>
		<td align="left"><div id="a5" class="tbp cona" style="display:none"><div class="t1a" align="left">Please enter the URL where this news has been uploaded.</div></div><input name="nws_covgurl" id="nws_covgurl" maxlength="93" class="a_f rf"  type="text" value="<?php echo $nws_covgurl; ?>"></td>
	</tr> 

	<tr>
		<td class="label"><span>*</span>&nbsp;News Coverage Detail</td>
		<td align="left">
  <textarea name="nws_covgdet" id="nws_covgdet" rows="10" cols="80"  class="a_f rf" style="width: 322px;"><?php echo $nws_covgdet; ?></textarea>
		<div class="max f11"><font id="Charcount" color="#ff8000">0 character (maximum of 4000)</font> character(s).</div></td>
	</tr>

	<tr>
		<td class="label">Add Image</td>
		<td align="left">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
        
			<td align="center" valign="top" width="20%">Small Image<br>
			<iframe src="upload-save-newspress.php" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125"></iframe>
			</td>

			<td width="3%">&nbsp;</td>

			<td align="center" valign="top" width="20%">Large Image
			<iframe src="upload-save-newspresslarge.php" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125"></iframe>
			</td>
		</tr>
		</tbody></table>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td align="left">
        <input name="cdate" id="cdate" maxlength="93" class="a_f rf"  type="hidden" value="<?php echo date("Y-m-d"); ?>">
        <input class="saps mt5" name="submit" value="Add News / Press Releases" type="button" onClick="addnews(<?php echo $uid; ?>)"></td>
	</tr> 
 
	</tbody></table>
	</div>
    </form>
	</div>
	</div>
	<!--add news form:ends-->
    <?php
    while($newsrowk=mysqli_fetch_object($newsqlk))
	{
	?> 
    <div id="228882" class="nlist ap4 news_div">		
		<div class="f1" style="width:125px; margin-left: 10px;">		
		<div class="f1 ap3" id="base_n_image_228882" style="height:125px; width:125px;" align="center">
        
       <?php if($newsrowk->nws_smallimg!=""){ ?>
      <img src="upload/mynews/small/<?php echo $newsrowk->nws_smallimg; ?>" alt="<?php echo $newsrowk->nws_headline;?>" class="pro" border="0" style="width:100%; height:auto;">
      <?php } else { ?>
      <img src="images/noimage.jpg"  class="pro" border="0" width="125" height="107">
      <?php } ?>        
        </div>
		<div id="base_big_img_228882"></div>
		
		</div>
		<div class="f1 nc wrd-brk p-cont">
			<strong id="base_headline_228882"><?php echo $newsrowk->nws_headline; ?></strong><br>
		      <span>Posted on: </span><span id="base_date_228882"><?php echo date('d-M-Y', strtotime($newsrowk->nws_postdate)); ?></span>
		      <div class="c3"></div>
			
			<p class="mt5 lh nde" style="text-align: left">
            <?php if($newsrowk->nws_medianm!=""){ ?>
			<span id="base_name_pop_228882"><b>Media Name: </b><span id="base_name_228882"><?php echo $newsrowk->nws_medianm;?></span><br></span>
            <?php } if($newsrowk->nws_mediatyp!=""){ ?>
            <span id="base_type_pop_228882"><b>Media Type: </b><span id="base_type_228882"><?php echo $newsrowk->nws_mediatyp;?></span><br></span>
            <?php } if($newsrowk->nws_covgurl!=""){ ?>
            <span id="base_type_pop_228882"><b>Release URL: </b><span id="base_type_228882"><a href="<?php echo $newsrowk->nws_covgurl;?>"><?php echo $newsrowk->nws_covgurl;?></a></span>
            <?php } ?>
            <br></span><span id="base_url_pop_228882" style="text-align: left"></span></p>
            <div  id="base_desc_hd<?php echo $newsrowk->nws_id; ?>" style="margin-right:20px;color: #222222; display:none;" class="mt5 lh tl wrd-brk awpf c3"><?php echo $newsrowk->nws_covgdet; ?></div>
                        
			<div class="mt5 lh tl wrd-brk awpf c3" id="base_desc_sd<?php echo $newsrowk->nws_id; ?>" style="height:5em;text-align:justify;padding: 5px 0 15px; line-height: 19px; overflow:hidden;">
 			<?php echo substr($newsrowk->nws_covgdet,0,290); ?> <br></div>
            
            <?php if(strlen($newsrowk->nws_covgdet)>290) { ?>
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline; cursor:pointer;" id="less_hd<?php echo $newsrowk->nws_id; ?>" onClick="showdesc(<?php echo $newsrowk->nws_id; ?>)">
View Complete Details</a>
        	<?php } ?>
            
            <span id="less_sd<?php echo $newsrowk->nws_id; ?>" style="display:none;"> 
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline;cursor:pointer;" onClick="hidedesc(<?php echo $newsrowk->nws_id; ?>)">
Less</a></span>
            
			
		</div>
		<div style="width: 100px; margin-left: 20px; margin-top: 100px;" class="f1">
			<span style="*margin-bottom:5px" class="link1 cpr">		
			<a href="" class="edi bnr dl_pf" id="edit_0" style="*float:none;display:block;padding-bottom: 4px;">Edit</a></span>
			<a style="*float:none;cursor:pointer;" id="delp_228882" onclick="showdeloption(<?php echo $newsrowk->nws_id; ?>)" class="del bnr dl_pf">Delete</a></div>
							
		    <div class="c3"></div>
		    <div class="info bnr dn" id="dcon<?php echo $newsrowk->nws_id; ?>" style="margin-left: 10px; margin-right: 10px; display:none;">
		    <div style="width:125px;" class="f2"><a id="yesp_228882" onclick="delnews(<?php echo $newsrowk->nws_id; ?>)"  class="yn" style="cursor:pointer;">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a id="nop_228882" onclick="hidedeloption(<?php echo $newsrowk->nws_id; ?>)" class="yn" style="cursor:pointer;">No</a></div>Do you really want to delete this News / Press Release?
		</div>
		</div>
    <?php } ?>
    
    
		<!-- news id:225801 :: start-->
		<div style="display: block;" class="" id="proce225801" align="center">
        <div class="save bnr mt12 db"><strong>Content has been deleted successfully!</strong></div></div> 
		</div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
        <?php include 'includes/footer.php'; ?>