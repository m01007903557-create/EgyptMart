<?php
ob_start();
session_start();
include "../common.php";
error_reporting(1);

check_user_login();
if (isset ($_SESSION['nc_subject'])) {
  $nc_subject = $_SESSION['nc_subject'];
  unset ($_SESSION['nc_subject']);
}
else {
  $nc_subject = "";
}
if (isset ($_SESSION['nc_content'])) {
  $nc_content = $_SESSION['nc_content'];
  unset ($_SESSION['nc_content']);
}
else {
  $nc_content = "";
}

//country cn_name cn_id
$sql_usr = "select * from country  order by cn_name asc";
$res_main_category = mysql_query($sql_usr) or die(mysql_error());
$country_array = array();
while ($row_cat = mysql_fetch_object($res_main_category)) {
  $cat_array1 = array();
  $cat_array1['id'] = $row_cat->cn_id;
  ;
  $cat_array1['name'] = $row_cat->cn_name;
  ;
  $country_array[] = $cat_array1;
}

//country cn_name cn_id
$sql_usr = "select * from business_profile where bnsprof_compname != '' order by bnsprof_compname asc";
$res_main_category = mysql_query($sql_usr) or die(mysql_error());
$company_array = array();
while ($row_cat = mysql_fetch_object($res_main_category)) {
  $cat_array1 = array();
  $cat_array1['id'] = $row_cat->bnsprof_uid;
  ;
  $cat_array1['name'] = $row_cat->bnsprof_compname;
  ;
  $company_array[] = $cat_array1;
}


$sql_usr = "select * from product_category where pc_parent_id = '0' order by pc_id asc";
$res_main_category = mysql_query($sql_usr);
$cat_array = array();
while ($row_cat = mysql_fetch_object($res_main_category)) {
  $cat_array1 = array();
  $cat_array1['id'] = $row_cat->pc_id;
  ;
  $cat_array1['name'] = $row_cat->pc_name;
  ;
  $cat_array[] = $cat_array1;
}

class addPlan {
  var $msg;
  var $nc_subject;
  var $nc_content;
  function __construct($nc_subject, $nc_content) {
    $this->nc_subject = $nc_subject;
    $this->nc_content = $nc_content;
    $_SESSION['nc_subject'] = $this->nc_subject;
    $_SESSION['nc_content'] = $this->nc_content;
  }
  function valid() {
    $valid = true;
    if ($this->nc_subject == "") {
      $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Newsletter Subject.</div>';
      $valid = false;
    }
    else
      if ($this->nc_content == "") {
        $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Newsletter Content.</div>';
        $valid = false;
      }
      return $valid;
  }
  function add() {
	//echo "<pre>"; print_r($_POST);exit;
    $categoryassigned = "";
    if (isset ($_POST['categoryassigned'])) {
      $categoryassigned = implode(",", $_POST['categoryassigned']);
    }
    $country = "";
    if (isset ($_POST['country'])) {
      $country = implode(",", $_POST['country']);
    }
	$companies = "";
    if (isset ($_POST['companies'])) {
      $companies = implode(",", $_POST['companies']);
    }
	//echo $this->nc_content;
	
	$doc = new DOMDocument();
	$doc->loadHTML($this->nc_content);
	$imageTags = $doc->getElementsByTagName('img');
	$imageArray = array();
	$img_count = 1;
	//print_r($imageTags);
	foreach($imageTags as $tag) {
		$src = $tag->getAttribute('src');
		$dir = dirname(__FILE__).'/../images/reply/';
		$filename = '';
		if(strpos($src, 'image/png') > 0){
		$src = str_replace('data:image/png;base64,', '', $src);
		$src = str_replace(' ', '+', $src);
		$data = base64_decode($src);
		$filename = uniqid() . '.png';
		}
		else if(strpos($src, 'image/jpeg') > 0 || strpos($src, 'image/jpg') > 0){
		$src = str_replace('data:image/jpeg;base64,', '', $src);
		$src = str_replace(' ', '+', $src);
		$data = base64_decode($src);
		$filename = uniqid() . '.jpg';
		}
		if($filename == '') {
			$this->nc_content = $this->nc_content;
		}
		else {
		$file = $dir . $filename;
		$success = file_put_contents($file, $data);
		$imageArray[$tag->getAttribute('src')] = 'http://arabyos.com/images/reply/'.$filename;
		$this->nc_content = str_replace($tag->getAttribute('src'), 'http://arabyos.com/images/reply/'.$filename, $this->nc_content);
		}
	}
			
	
    $sql = "insert into newsletter_content_arabyos
			set
				nc_subject ='" . $this->nc_subject . "',
				nc_content ='" . $this->nc_content . "',
				nc_category ='" . $categoryassigned . "',
				nc_country ='" . $country . "',
				nc_companies ='" . $companies . "',
				nc_updated_date=now()";
    mysql_query($sql) or die(mysql_error());
/************************ Email sending code ************************/
    $and = "";
    if ($country != '') {
      $and .= " and country in (" . $country . ")";
    }
	if ($companies != '') {
      $and .= " and usr_id in (" . $companies . ")";
    }
    if ($categoryassigned != '') {
      $sql12 = "SELECT CONCAT( GROUP_CONCAT(p1.pc_id),\",\",GROUP_CONCAT(DISTINCT p2.pc_id) ,\",\",GROUP_CONCAT(DISTINCT p3.pc_id) ) as Grandparentname
                 FROM product_category p1
                 LEFT JOIN product_category p2 on p1.pc_parent_id = p2.pc_id
                LEFT JOIN product_category p3 on p2.pc_parent_id = p3.pc_id where p3.pc_id in (".$categoryassigned.")";
      $res_main_category = mysql_query($sql12) or die(mysql_error());
      while ($row_cat1 = mysql_fetch_object($res_main_category)) {
        $categoryassigned = $row_cat1->Grandparentname;

      }
      $and .= " and (
          usr_id in (
          select distinct sac_usr_id from selloffer_alert_category where (
          sac_pc_id in (" . $categoryassigned . ") and sac_status = 1
          )
          )
          or usr_id in  (select distinct so_usr_id from sale_offer where
           ( so_pc_id in (" . $categoryassigned . ")  and
           so_status = 1
           ))
          or usr_id in  (select distinct tac_usr_id from tender_alert_category where
            ( tac_pc_id in (" . $categoryassigned . ")  and
           tac_status = 1)
            )
          or usr_id in
          (select distinct aac_usr_id from auction_alert_category where

           ( aac_pc_id in (" . $categoryassigned . ")  and
           aac_status = 1)
           )
          or usr_id in  (select distinct bac_usr_id from buylead_alert_category


          where

             ( bac_pc_id in (" . $categoryassigned . ") and
           bac_status = 1)


          ))";
    }
    $sql_usr = "select * from user where status='1' " . $and;
	//echo $sql_usr;exit;
//selloffer_alert_category
        
    $res_usr = mysql_query($sql_usr) or die(mysql_error());
    while ($row_usr = mysql_fetch_object($res_usr)) {
      $to = $row_usr->email;

/*Put User's Email Adress Here*/
      $subject = $this->nc_subject;
      $from_name = get_page_settings(4);
      $from_email = get_adminemail();
      include "email/newsletter-send.php"; //email design with content included
/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
$message .= "We are happy you joined.";
$message .= "<br /><br />".get_page_settings(4)." Team";*/

      $headers = "MIME-Version: 1.0\r\n";
      $headers .= "Content-type: text/html; charset=UTF-8; format=flowed\r\n";
	  $headers .= 'Content-Transfer-Encoding: quoted-printable'."\r\n";
      $headers .= "From: $from_name < $from_email >";
	 // echo $message; exit;


  /*if(!mail($to, '=?utf-8?Q?'.($subject).'?=', quoted_printable_encode($message1), $headers)){
    print_r(error_get_last());
     echo "Hello it's me";
      exit;
    }*/
     

     if(!sendSMTPMail($to, $subject, $message1)){
    print_r(error_get_last());
     echo "Hello it's me";
      exit;
    }
     // mail("keshavkalra1990@gmail.com", $subject, $message, $headers);
    }
/************************ Email sending code ************************/
    $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Newsletter send to all registered users successfully.</div>';
    unset ($_SESSION['nc_subject']);
    unset ($_SESSION['nc_content']);
  }
}
if (isset ($_SESSION['msg'])) {
  $msg = $_SESSION['msg'];
  unset ($_SESSION['msg']);
}
if (isset ($_POST['btnAdd'])) {
  $adn = new addPlan(addslashes(trim($_POST['nc_subject'])), trim(($_POST['nc_content'])));
  if ($adn->valid()) {
    $adn->add();
  }
  $_SESSION['msg'] = $adn->msg;
  header("location:newsletter-view.php");
}
?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' , 'fixed')}catch(e){}
	</script>

	<div class="main-container-inner">
		<a class="menu-toggler" id="menu-toggler" href="#">
			<span class="menu-text"></span>
		</a>
<script type="text/javascript">
function myvalid()
{
	filling();
	var nc_subject=document.getElementById('nc_subject');
	var nc_content=document.getElementById('nc_content');


	var message="";
	var valid=true;

	if(nc_subject.value=='' || nc_subject.value == null)
	{
		message='Please enter Newsletter Subject.';
		nc_subject.focus();
		valid=false;
	}
	else if(nc_content.value=='' || nc_content.value == null)
	{
		message='Please enter Newsletter Content.';
		nc_content.focus();
		valid=false;
	}

	if(!valid)
	{
		document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> "+message;
		document.getElementById('msg').className="alert alert-danger";

	}
	return valid;
}
function filling()
{
	/*var copyT = $("#editor1").html();
	var pasteT = $("#nc_content").val(copyT);*/
	return true;
}
</script>
<?php include "includes/admin-left-con.php" ?>
<div class="main-content">
	<div class="breadcrumbs" id="breadcrumbs">
		<script type="text/javascript">
			try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
		</script>

		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
				<a href="welcome.php">Home</a>
			</li>
			<li>
				<a href="newsletter-view.php">Manage Membership Requirements</a>
			</li>
			<li class="active">Send Newsletter</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>

<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Membership Requirements 
			<small>
				<i class="icon-double-angle-right"></i>
				Send Manage Membership Requirements
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
	<em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
	<div id="msg"><?php echo $msg;?></div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Subject <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<input name="nc_subject" id="nc_subject" class="col-xs-10 col-sm-8" type="text" value="<?php echo $nc_subject;?>" />
		</div>
	</div>


     <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Assign to Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select class="col-xs-10 col-sm-8" name="categoryassigned[]" multiple="multiple">
            <?php
            for ($ik = 0; $ik < count($cat_array); $ik++) {
              ?>
              <option value="<?php echo $cat_array[$ik]['id'];?>"><?php echo $cat_array[$ik]['name'];?></option>
                <?php
              }
              ?>
            </select>
		</div>
	</div>



     <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select class="col-xs-10 col-sm-8" name="country[]" multiple="multiple">
            <?php
            for ($ik = 0; $ik < count($country_array); $ik++) {
              ?>
              <option value="<?php echo $country_array[$ik]['id'];?>"><?php echo $country_array[$ik]['name'];?></option>
                <?php
              }
              ?>
            </select>
		</div>
	</div>
	 <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Companies <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select  data-live-search="true" id="companies" data-width="fit" class="col-xs-10 col-sm-8" name="companies[]">
			<option value="">Select</option>
            <?php
            for ($ik = 0; $ik < count($company_array); $ik++) {
              ?>
              <option value="<?php echo $company_array[$ik]['id'];?>"><?php echo $company_array[$ik]['name'];?></option>
                <?php
              }
              ?>
            </select>
		</div>
	</div>


<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Content <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
        	<textarea name="nc_content" id="nc_content"><?php echo stripslashes($nc_content);?></textarea>
            <!--<div class="wysiwyg-editor" id="editor1"><?php //echo stripslashes($nc_content);?></div>-->
		</div>
	</div>

	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd" ><i class="icon-ok bigger-110"></i>Send</button>
			<button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
		</div>
	</div>
	</form>
 			</div>
		</div>

	</div>
	<br clear="all" />
</div>
<?php include "includes/footer.php" ?>
</body>
		<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<!--[if lte IE 8]>
		  <script src="assets/js/excanvas.min.js"></script>
		<![endif]-->

		<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="assets/js/chosen.jquery.min.js"></script>
		<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
		<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
		<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
		<script src="assets/js/date-time/moment.min.js"></script>
		<script src="assets/js/date-time/daterangepicker.min.js"></script>
		<script src="assets/js/bootstrap-colorpicker.min.js"></script>
		<script src="assets/js/jquery.knob.min.js"></script>
		<script src="assets/js/jquery.autosize.min.js"></script>
		<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
		<script src="assets/js/jquery.maskedinput.min.js"></script>
		<script src="assets/js/bootstrap-tag.min.js"></script>

		<!-- ace scripts 

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>-->

		<!-- inline scripts related to this page -->
		<script src="ckeditor/ckeditor.js"></script>
        <script src="assets/js/markdown/markdown.min.js"></script>
		<script src="assets/js/markdown/bootstrap-markdown.min.js"></script>
		<script src="assets/js/jquery.hotkeys.min.js"></script>
		<script src="assets/js/bootstrap-wysiwyg.min.js"></script>
		<script src="assets/js/bootbox.min.js"></script>
		<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>

 <!--<script type="text/javascript">
    function redirect()
    {
    var url = "http://www.arabyos.com/admin/newsletter-view.php";
    window.location(url);
    }
    </script>-->

		<script type="text/javascript">
			jQuery(function($){
$('select#companies').selectpicker({});
	function showErrorAlert (reason, detail) {
		var msg='';
		if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
		else {
			console.log("error uploading file", reason, detail);
		}
		$('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
		 '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
	}
	CKEDITOR.replace( 'nc_content', {
			 extraPlugins: 'imageuploader'
		} );
	//$('#editor1').ace_wysiwyg();//this will create the default editor will all buttons

	//but we want to change a few buttons colors for the third style
	/*$('#editor1').ace_wysiwyg({
		toolbar:
		[
			'font',
			null,
			'fontSize',
			null,
			{name:'bold', className:'btn-info'},
			{name:'italic', className:'btn-info'},
			{name:'strikethrough', className:'btn-info'},
			{name:'underline', className:'btn-info'},
			null,
			{name:'insertunorderedlist', className:'btn-success'},
			{name:'insertorderedlist', className:'btn-success'},
			{name:'outdent', className:'btn-purple'},
			{name:'indent', className:'btn-purple'},
			null,
			{name:'justifyleft', className:'btn-primary'},
			{name:'justifycenter', className:'btn-primary'},
			{name:'justifyright', className:'btn-primary'},
			{name:'justifyfull', className:'btn-inverse'},
			null,
			{name:'createLink', className:'btn-pink'},
			{name:'unlink', className:'btn-pink'},
			null,
			{name:'insertImage', className:'btn-success'},
			null,
			'foreColor',
			null,
			{name:'undo', className:'btn-grey'},
			{name:'redo', className:'btn-grey'}
		],
		'wysiwyg': {
			fileUploadError: showErrorAlert
		}
	}).prev().addClass('wysiwyg-style2');



	$('#editor2').css({'height':'200px'}).ace_wysiwyg({
		toolbar_place: function(toolbar) {
			return $(this).closest('.widget-box').find('.widget-header').prepend(toolbar).children(0).addClass('inline');
		},
		toolbar:
		[
			'bold',
			{name:'italic' , title:'Change Title!', icon: 'icon-leaf'},
			'strikethrough',
			null,
			'insertunorderedlist',
			'insertorderedlist',
			null,
			'justifyleft',
			'justifycenter',
			'justifyright'
		],
		speech_button:false
	});*/


	$('[data-toggle="buttons"] .btn').on('click', function(e){
		var target = $(this).find('input[type=radio]');
		var which = parseInt(target.val());
		var toolbar = $('#editor1').prev().get(0);
		if(which == 1 || which == 2 || which == 3) {
			toolbar.className = toolbar.className.replace(/wysiwyg\-style(1|2)/g , '');
			if(which == 1) $(toolbar).addClass('wysiwyg-style1');
			else if(which == 2) $(toolbar).addClass('wysiwyg-style2');
		}
	});




	//Add Image Resize Functionality to Chrome and Safari
	//webkit browsers don't have image resize functionality when content is editable
	//so let's add something using jQuery UI resizable
	//another option would be opening a dialog for user to enter dimensions.
	if ( typeof jQuery.ui !== 'undefined' && /applewebkit/.test(navigator.userAgent.toLowerCase()) ) {

		var lastResizableImg = null;
		function destroyResizable() {
			if(lastResizableImg == null) return;
			lastResizableImg.resizable( "destroy" );
			lastResizableImg.removeData('resizable');
			lastResizableImg = null;
		}

		var enableImageResize = function() {
			$('.wysiwyg-editor')
			.on('mousedown', function(e) {
				var target = $(e.target);
				if( e.target instanceof HTMLImageElement ) {
					if( !target.data('resizable') ) {
						target.resizable({
							aspectRatio: e.target.width / e.target.height,
						});
						target.data('resizable', true);

						if( lastResizableImg != null ) {//disable previous resizable image
							lastResizableImg.resizable( "destroy" );
							lastResizableImg.removeData('resizable');
						}
						lastResizableImg = target;
					}
				}
			})
			.on('click', function(e) {
				if( lastResizableImg != null && !(e.target instanceof HTMLImageElement) ) {
					destroyResizable();
				}
			})
			.on('keydown', function() {
				destroyResizable();
			});
	    }

		enableImageResize();

		/**
		//or we can load the jQuery UI dynamically only if needed
		if (typeof jQuery.ui !== 'undefined') enableImageResize();
		else {//load jQuery UI if not loaded
			$.getScript($path_assets+"/js/jquery-ui-1.10.3.custom.min.js", function(data, textStatus, jqxhr) {
				if('ontouchend' in document) {//also load touch-punch for touch devices
					$.getScript($path_assets+"/js/jquery.ui.touch-punch.min.js", function(data, textStatus, jqxhr) {
						enableImageResize();
					});
				} else	enableImageResize();
			});
		}
		*/
	}


});

</script>

		<script type="text/javascript">
			jQuery(function($) {
				$('#id-disable-check').on('click', function() {
					var inp = $('#form-input-readonly').get(0);
					if(inp.hasAttribute('disabled')) {
						inp.setAttribute('readonly' , 'true');
						inp.removeAttribute('disabled');
						inp.value="This text field is readonly!";
					}
					else {
						inp.setAttribute('disabled' , 'disabled');
						inp.removeAttribute('readonly');
						inp.value="This text field is disabled!";
					}
				});


				$(".chosen-select").chosen();
				$('#chosen-multiple-style').on('click', function(e){
					var target = $(e.target).find('input[type=radio]');
					var which = parseInt(target.val());
					if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
					 else $('#form-field-select-4').removeClass('tag-input-style');
				});


				$('[data-rel=tooltip]').tooltip({container:'body'});
				$('[data-rel=popover]').popover({container:'body'});

				$('textarea[class*=autosize]').autosize({append: "\n"});
				$('textarea.limited').inputlimiter({
					remText: '%n character%s remaining...',
					limitText: 'max allowed : %n.'
				});

				$.mask.definitions['~']='[+-]';
				$('.input-mask-date').mask('99/99/9999');
				$('.input-mask-phone').mask('(999) 999-9999');
				$('.input-mask-eyescript').mask('~9.99 ~9.99 999');
				$(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){alert("You typed the following: "+this.val());}});



				$( "#input-size-slider" ).css('width','200px').slider({
					value:1,
					range: "min",
					min: 1,
					max: 8,
					step: 1,
					slide: function( event, ui ) {
						var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
						var val = parseInt(ui.value);
						$('#form-field-4').attr('class', sizing[val]).val('.'+sizing[val]);
					}
				});

				$( "#input-span-slider" ).slider({
					value:1,
					range: "min",
					min: 1,
					max: 12,
					step: 1,
					slide: function( event, ui ) {
						var val = parseInt(ui.value);
						$('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
					}
				});


				$( "#slider-range" ).css('height','200px').slider({
					orientation: "vertical",
					range: true,
					min: 0,
					max: 100,
					values: [ 17, 67 ],
					slide: function( event, ui ) {
						var val = ui.values[$(ui.handle).index()-1]+"";

						if(! ui.handle.firstChild ) {
							$(ui.handle).append("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>");
						}
						$(ui.handle.firstChild).show().children().eq(1).text(val);
					}
				}).find('a').on('blur', function(){
					$(this.firstChild).hide();
				});

				$( "#slider-range-max" ).slider({
					range: "max",
					min: 1,
					max: 10,
					value: 2
				});

				$( "#eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
					// read initial values from markup and remove that
					var value = parseInt( $( this ).text(), 10 );
					$( this ).empty().slider({
						value: value,
						range: "min",
						animate: true

					});
				});


				$('#id-input-file-1 , #id-input-file-2').ace_file_input({
					no_file:'No File ...',
					btn_choose:'Choose',
					btn_change:'Change',
					droppable:false,
					onchange:null,
					thumbnail:false //| true | large
					//whitelist:'gif|png|jpg|jpeg'
					//blacklist:'exe|php'
					//onchange:''
					//
				});

				$('#id-input-file-3').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'small'//large | fit
					//,icon_remove:null//set null, to hide remove/reset button
					/**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
					/**,before_remove : function() {
						return true;
					}*/
					,
					preview_error : function(filename, error_code) {
						//name of the file that failed
						//error_code values
						//1 = 'FILE_LOAD_FAILED',
						//2 = 'IMAGE_LOAD_FAILED',
						//3 = 'THUMBNAIL_FAILED'
						//alert(error_code);
					}

				}).on('change', function(){
					//console.log($(this).data('ace_input_files'));
					//console.log($(this).data('ace_input_method'));
				});


				//dynamically change allowed formats by changing before_change callback function
				$('#id-file-format').removeAttr('checked').on('change', function() {
					var before_change
					var btn_choose
					var no_icon
					if(this.checked) {
						btn_choose = "Drop images here or click to choose";
						no_icon = "icon-picture";
						before_change = function(files, dropped) {
							var allowed_files = [];
							for(var i = 0 ; i < files.length; i++) {
								var file = files[i];
								if(typeof file === "string") {
									//IE8 and browsers that don't support File Object
									if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
								}
								else {
									var type = $.trim(file.type);
									if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
											|| ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) )//for android's default browser which gives an empty string for file.type
										) continue;//not an image so don't keep this file
								}

								allowed_files.push(file);
							}
							if(allowed_files.length == 0) return false;

							return allowed_files;
						}
					}
					else {
						btn_choose = "Drop files here or click to choose";
						no_icon = "icon-cloud-upload";
						before_change = function(files, dropped) {
							return files;
						}
					}
					var file_input = $('#id-input-file-3');
					file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon})
					file_input.ace_file_input('reset_input');
				});




				$('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
				.on('change', function(){
					//alert(this.value)
				});
				$('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
				$('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});



				$('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				$('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
					$(this).next().focus();
				});

				$('#timepicker1').timepicker({
					minuteStep: 1,
					showSeconds: true,
					showMeridian: false
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});

				$('#colorpicker1').colorpicker();
				$('#simple-colorpicker-1').ace_colorpicker();


				$(".knob").knob();


				//we could just set the data-provide="tag" of the element inside HTML, but IE8 fails!
				var tag_input = $('#form-field-tags');
				if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) )
				{
					tag_input.tag(
					  {
						placeholder:tag_input.attr('placeholder'),
						//enable typeahead by specifying the source array
						source: ace.variable_US_STATES,//defined in ace.js >> ace.enable_search_ahead
					  }
					);
				}
				else {
					//display a textarea for old IE, because it doesn't support this plugin or another one I tried!
					tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
					//$('#form-field-tags').autosize({append: "\n"});
				}




				/////////
				$('#modal-form input[type=file]').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'large'
				})

				//chosen plugin inside a modal will have a zero width because the select element is originally hidden
				//and its width cannot be determined.
				//so we set the width after modal is show
				$('#modal-form').on('shown.bs.modal', function () {
					$(this).find('.chosen-container').each(function(){
						$(this).find('a:first-child').css('width' , '210px');
						$(this).find('.chosen-drop').css('width' , '210px');
						$(this).find('.chosen-search input').css('width' , '200px');
					});
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/

			});
		</script>
</html>
