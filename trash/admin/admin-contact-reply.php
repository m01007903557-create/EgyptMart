<?php 
ob_start();
session_start(); 
include "../common.php";

check_user_login();
class ContactUsDetails{
	var $cu_id;
	var $reply_subject;
	var $reply_content;
	var $mem_name;
	var $msg;
		
	function __construct($cu_id){
		$this->cu_id=$cu_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from contact_us where cu_id=".$this->cu_id;
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
function valid(){
			$valid=true;
			if($this->reply_subject=="")
			{
				$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter subject</div>';
				$valid=false;
			}
			else if($this->reply_content=="")
			{
				$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter message</div>';
				$valid=false;
			}
		
			return $valid;
		}
		function insertMsg(){
			global $con;
			$obj = $this->detailsObj();
			$sql1="UPDATE contact_us SET replied = 1 where cu_id=".$this->cu_id;
			mysqli_query($con, $sql1);
			$doc = new DOMDocument();
			$doc->loadHTML($this->reply_content);
			$imageTags = $doc->getElementsByTagName('img');
			$imageArray = array();
			$img_count = 1;
			foreach($imageTags as $tag) {
				$src = $tag->getAttribute('src');
				$dir = dirname(__FILE__).'/../images/reply/';
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
				$file = $dir . $filename;
				$success = file_put_contents($file, $data);
				$imageArray[$tag->getAttribute('src')] = 'http://arabyos.com/images/reply/'.$filename;
				$this->reply_content = str_replace($tag->getAttribute('src'), 'http://arabyos.com/images/reply/'.$filename, $this->reply_content);
			}
			
			$sql="insert into message 
				set 
					msg_from=".getAdminUserId().",
					msg_to='".$obj->cu_user_id."',
					msg_subject='".$this->reply_subject."',
					msg_message='".$this->reply_content."',
					msg_entity='contact',
					msg_entity_id='".$this->cu_id."',
					msg_date=now()";
					//echo $sql;exit;
			mysqli_query($con, $sql) or die(mysql_error());
			
			$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Replied message sent successfully</div>';
			$this->mem_name = $obj->cu_fname.' '.$obj->cu_lname;
			/********************* Email sending code start here **********************/
		
		$to = $obj->cu_email;  /*Put Your Email Adress Here*/
		$subject = "Reply from admin for contact on ".get_page_settings(4);
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
		$is_contact = 1;
		$enq_details = '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Name: '.$obj->cu_fname.' '.$obj->cu_lname.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Contact Number: '.$obj->cu_contactnumber.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Email: '.$obj->cu_email.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Country/State: '.$obj->cu_country.'-'.$obj->cu_state.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Comments: '.$obj->cu_comments.'</p>';
		include "email/admin-reply.php"; //email design with content included		
		$headers  = "MIME-Version: 1.0\r\n";
	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	$headers .= "From: $from_name < $from_email >\r\n";
    	$headers .= "Reply-To: $from_email";
		mail($to, $subject, $message1, $headers);
		
		/********************* Email sending code end here **********************/
		
		}		
}
if(isset($_SESSION['msg'])){
		$msg=$_SESSION['msg'];
		unset($_SESSION['msg']);
	}

$ob=new ContactUsDetails($_GET['fid']);
$row=$ob->detailsObj();

if(isset($_POST['btnReplyBack']))
{
	header("location:contact-details.php?fid=".$_GET['fid']);
}
else if(isset($_POST['btnReply']))
{
	header("location:admin-contact-reply.php?fid=".$_GET['fid']);
}
else if(isset($_POST['btnReplySubmit']))
{
	$ob->reply_subject=trim(addslashes($_POST['reply_subject']));
	$ob->reply_content=trim(htmlentities($_POST['reply_content']));	
	if($ob->valid()){
		$ob->insertMsg();
	}
	$_SESSION['msg']=$ob->msg;
	
	header("location:admin-contact-reply.php?fid=".$_GET['fid']);
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
				<a href="contact-view.php">Manage Contact Us</a>
			</li>
			<li class="active">Contact Details</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Contact Us
			<small>
				<i class="icon-double-angle-right"></i>
				Contact Details
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Name:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo ucfirst($row->cu_fname)." ".ucfirst($row->cu_lname); ?></label>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><a href="mailto:<?php echo $row->cu_email; ?>"><?php echo $row->cu_email; ?></a></label>
		</div>
	</div>
	<?php if($row->cu_contactnumber != '') { ?>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Contact Number:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row->cu_contactnumber; ?></label>
		</div>
	</div>
    <?php } ?>
<?php if($row->cu_country != '') { ?>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country/State:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row->cu_country.'-'.$row->cu_state ; ?></label>
		</div>
	</div>
    <?php } ?>	 
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Message:</label>
		<div class="col-sm-8">
   		   	<label style="padding-top:4px;"><?php echo $row->cu_comments; ?></label>
		</div>
	</div>  
	<h2 class="col-xs-12"> Reply to this Membership Request</h2>
	<form class="form-horizontal" name="mem_reply" id="mem_reply" method="post" enctype="multipart/form-data" onSubmit="return filling();">
	<div id="msg" class="col-xs-12"><?php echo $msg; ?></div>
		<div class="form-group">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Reply Subject:</label>
			<div class="col-sm-9">
				<input name="reply_subject" id="reply_subject" class="form-control" type="text" value="Reply from Admin for your Contact"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Reply Message:</label>
			<div class="col-sm-9">
				<textarea name="reply_content" id="reply_content" cols="50" rows="10" class="form-control" ><?php echo stripslashes($reply_content);?></textarea>
				<!--<div class="wysiwyg-editor" id="editor1"><?php //echo stripslashes($reply_content);?></div>-->
			</div>
		</div>
		<div class="clearfix form-actions">
			<div class="col-md-offset-3 col-md-9">
				<button class="btn btn-info" type="submit" name="btnReplySubmit" id="btnReplySubmit"><i class="icon-ok icon-only"></i>&nbsp;Submit</button>
				<button class="btn btn-info" type="submit" name="btnReplyBack" id="btnReplyBack"><i class="icon-reply icon-only"></i>&nbsp;Back</button>
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

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>
		
		<script src="assets/js/markdown/markdown.min.js"></script>
		<script src="assets/js/markdown/bootstrap-markdown.min.js"></script>
		<script src="assets/js/jquery.hotkeys.min.js"></script>
		<script src="assets/js/bootstrap-wysiwyg.min.js"></script>
		<script src="assets/js/bootbox.min.js"></script>
		<script src="ckeditor/ckeditor.js"></script>
		<!-- inline scripts related to this page -->
		
		<script type="text/javascript">
		function filling()
		{
			//var copyT = $("#editor1").html();
			//var pasteT = $("#reply_content").val(copyT);
			return true;
		}
					jQuery(function($){

	function showErrorAlert (reason, detail) {
		var msg='';
		if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
		else {
			console.log("error uploading file", reason, detail);
		}
		$('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
		 '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
	}

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
	}).prev().addClass('wysiwyg-style2');*/

	CKEDITOR.replace( 'reply_content', {
		 extraPlugins: 'imageuploader'
	} );

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
	});


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

		<!-- inline scripts related to this page -->

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