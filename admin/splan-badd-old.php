<?php

/**
 * @author Keshav Kalra
 * @copyright 2016
 * @description This file is used for assigning the buisness plan to any company 
 */

?>

<?php 
ob_start();
session_start(); 
include "../common.php";
//include "lib/pagination.php";

check_user_login();
$msg = '';
$data = mysql_query("select * from business_profile bf JOIN user u ON bf.bnsprof_uid = u.usr_id where bnsprof_compname!=''") or die(mysql_error());
if($_POST['expiry_date'] != '')
{
   
    //plan_member_id


    $company_name = $_POST['companyname'];
    $data_date = strtotime($_POST['expiry_date']);
    $buisness_plan = $_POST['buisness_plan'];
    $buisness_icon_plan = $_POST['buisness_icon_plan'];
    $sql = "delete from plan_member_id where b_id='".$company_name."';
     ";
   $sql2 = "insert into plan_member_id set b_id='".$company_name."'
    , p_id='".$buisness_plan."',
     icon_id='".$buisness_icon_plan."',
	 start_date='".strtotime(date("Y-m-d"))."',
    expiry_date = '".$data_date."' ";
   // echo  $sql;
    mysql_query($sql) or die(mysql_error());
    //echo $sql2;
   mysql_query($sql2) or die(mysql_error()); 
   $user_type = 1;
   if($buisness_plan > 3) {
   $user_type = 2;
   }
   $sql = "UPDATE user SET usr_mp_id = '".$buisness_plan."', user_type = '".$user_type ."' WHERE usr_id IN (SELECT bnsprof_uid FROM  business_profile WHERE bnsprof_id = '".$company_name."')";
   mysql_query($sql) or die(mysql_error()); 
   $sql = "SELECT u.*, bf.* from user u JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid  WHERE bnsprof_id = '".$company_name."'";
   $query = mysql_query($sql) or die(mysql_error()); 
   $user_detail = mysql_fetch_object($query);
   $to = $user_detail->email;  /*Put Your Email Adress Here*/
   $fullname = $user_detail->name_prefix.$user_detail->fname." ".$user_detail->lname;
   $sql = "SELECT * from smembership_plan WHERE mp_id =".$buisness_plan;
   $query = mysql_query($sql) or die(mysql_error()); 
   $bus_detail = mysql_fetch_object($query);
   $start_date = date("Y-m-d");
   $expiry_date = date("Y-m-d", $data_date);
		
   
   $billing_sql = "SELECT * from billing_history WHERE bh_type = 5 AND bh_usr_id IN (SELECT bnsprof_uid FROM  business_profile WHERE bnsprof_id = '".$company_name."') ORDER BY bh_updated_date DESC LIMIT 1";
   $billing_query = mysql_query($billing_sql) or die(mysql_error()); 
   $billing_detail = mysql_fetch_object($billing_query);

   
   
		$subject = "خطة العمل والتجارة فى إيجبت مارت   ".$bus_detail->mst_name." ";
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
		include "email/plan_start_notification.php"; //email design with content included	
		//echo $message1; exit;		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html\r\n";
           // $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
           //
	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	$headers .= "From: $from_name < $from_email >\r\n";
    	$headers .= "Reply-To: $from_email";
		//echo $message1;
		//exit;

		// Webcast MAil Setting
		sendSMTPMail($to, $subject, $message1);

		
		//mail($to, $subject, $message1, $headers);
		//mail('membership@arabyos.com', $subject, $message1, $headers);

		if(date("Y-m-d", $data_date) == '9999-09-09')
		{
			$message = '<h2 style="text-align:center;"><span style="color: blue">Congratulations!</span></h2><b style="text-align:center;">You are now a <span style="color: blue">'.$bus_detail->mst_name.'</span> with ARABYOS! </b><br> Your promotion is started on '.$start_date.'.You can add products and contact buy leads. The promotion will be Permanent untill you notified.';
		}
		else{
		$message = '<h2 style="text-align:center;"><span style="color: blue">Congratulations!</span></h2><b style="text-align:center;">You are now a <span style="color: blue">'.$bus_detail->mst_name.'</span> with ARABYOS! </b><br> Your promotion is started on '.$start_date.'.You can add products and contact buy leads. The promotion will be expired on '.$expiry_date.'.You will be notified for renewal.';
		}

$sql="insert into message 
				set 
					msg_from=".getAdminUserId().",
					msg_to='".$user_detail->usr_id."',
					msg_subject='". $subject. "',
					msg_message='".$message ."',
					msg_entity='membership_plan',
					msg_entity_id='".$company_name."',
					msg_date=now()";
					//echo $sql;exit;
			mysql_query($sql) or die(mysql_error());
$msg ='<div class="alert alert-success"><i class="icon-ok"></i> Your membership assignment had successfully done.</div>';

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
				<a href="splan-view.php">Assign Buisness Type Plan</a>
			</li>
			<li class="active">Assign Plan</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Assign Buisness Plan
			<small>
				<i class="icon-double-angle-right"></i>
				Assign Plan
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">

	<em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
	<div id="msg"><?php echo $msg; ?></div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Buisness Name <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
        
		<select data-live-search="true" data-width="fit" name="companyname" id="company_name" class="col-xs-10 col-sm-5">
        <?php
        $count=mysql_num_rows($data);
        while($row=mysql_fetch_object($data)){
         ?>
         <option value="<?php echo $row->bnsprof_id; ?>"><?php echo $row->bnsprof_compname; ?></option>
         <?php } ?>
        </select>
        
        
		</div>
	</div>

    

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Side Icon<span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">

         <?php
         $data = mysql_query("select * from smembership_plan where mp_status = '1'");
        $count=mysql_num_rows($data);
        while($row=mysql_fetch_object($data)){
         ?>

         <label class="col-xs-10"> <img src="./images/<?php echo $row->mst_icon; ?>" width="60px" height="60px;"> &nbsp;&nbsp; <input type="radio" name="buisness_plan" value="<?php echo $row->mp_id; ?>"></label>
         <?php } ?>

		</div>
	</div>



    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Plan<span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">

         <?php
         $data = mysql_query("select * from smembership_icon_plan where mp_status = '1'");
        $count=mysql_num_rows($data);
        while($row=mysql_fetch_object($data)){         
         ?>

         <label class="col-xs-10"> <img  src="./images/<?php echo $row->mst_icon; ?>" width="30px" height="30px;"> &nbsp;&nbsp; <input type="radio" name="buisness_icon_plan" value="<?php echo $row->mp_id; ?>"><?php echo $row->mst_name; ?></label>
         <?php } ?>

		</div>
	</div>
    


<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Expiry Date<span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
		<input type="date" name="expiry_date" data-date="" data-date-format="DD MMMM YYYY" id="expiry_date" class="col-xs-10 col-sm-5" />
        </div>
</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Permanent<span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
		<input type="checkbox" name="change_date" id="change_date" class="col-xs-10 col-sm-5" />
        </div>
</div>  

    
	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd"><i class="icon-ok bigger-110"></i>Add</button>
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

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/i18n/defaults-*.min.js"></script>

		<!-- inline scripts related to this page -->

		<script type="text/javascript">

			jQuery(function($) {
				$("#change_date").change(function() {
					    if(this.checked) {
					       $('#expiry_date').val('9999-09-09');
					       $('#expiry_date').prop('readonly', true);
					    }
					    else{
					    	$('#expiry_date').val('');
					       $('#expiry_date').prop('readonly', false);
					    }
					});
			});

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
			
            
            $('select').selectpicker({});
			});
            

		</script>
</html>
