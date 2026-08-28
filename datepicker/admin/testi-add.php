<?php 
include "../common.php";
	class editheadersld{
	var $msg;
	var $testi_type;
	var $testi_name;
	var $testi_details;
	var $testi_email;
	var $testi_mobile;
	var $testi_image;
	var $testi_cn_id;
	var $testi_business_name;
	
	function __construct()
	{
	$_SESSION['testi_type']=$this->testi_type;			
	$_SESSION['testi_name']=$this->testi_name;
	$_SESSION['testi_details']=$this->testi_details;			
	$_SESSION['testi_email']=$this->testi_email;
	$_SESSION['testi_mobile']=$this->testi_mobile;			
	$_SESSION['testi_business_name']=$this->testi_business_name;
	$_SESSION['testi_cn_id']=$this->testi_cn_id;
	}
	function valid()
	{
		$valid=true;
		
		if($this->testi_name == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter name</div>';
			$valid=false;
		}
		elseif($this->testi_details == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter details</div>';
			$valid=false;
		}
		elseif($this->testi_email == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Emails</div>';
			$valid=false;
		}		
		elseif(!validate::is_email($this->testi_email))
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid Emails</div>';
			$valid=false;
		}
		else if($this->testi_mobile == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Credits must be in multiple of 20</div>';
			$valid=false;
		}		
		else if(!is_numeric($this->testi_mobile))
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid mobile number</div>';
			$valid=false;
		}
		else if($this->testi_cn_id == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose country</div>';
			$valid=false;
		}		
		else if($this->testi_image == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload image</div>';
			$valid=false;
		}
		
		
		return $valid;
	}
	function update()
	{	
	global $con;
		$ext = end(explode('.',$this->testi_image)); 
        $validEXT = array('jpg','png','jpeg', 'gif', 'pdf');

        if(in_array($ext,$validEXT)) {
            $tempFile = $_FILES['testi_image']['tmp_name'];
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);

            $image = 'TESTIIMG-' . rand(0,9999) . $this->testi_image;

			
            $imgSImage->resize(76,76);
            $imgSImage->save("../upload/testimonial_img/" . $image);
	
				  $sql="insert into testimonials set
						testi_details = '".$this->testi_details."',
						testi_name = '".$this->testi_name."',
						testi_type = '".$this->testi_type."',
                        testi_image ='".$image."',   
						testi_email = '".$this->testi_email."',
						testi_mobile = '".$this->testi_mobile."',
						testi_cn_id = '".$this->testi_cn_id."',
						testi_business_name = '".$this->testi_business_name."',
						testi_updated_date=now()";			
	
				 mysqli_query($con, $sql) or die(mysql_error());	
		 }
        else
        {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an image with valid file extention.</div>';
        }

		unset($_SESSION['testi_type']);
		unset($_SESSION['testi_name']);
		unset($_SESSION['testi_details']);
		unset($_SESSION['testi_email']);
		unset($_SESSION['testi_mobile']);
		unset($_SESSION['testi_business_name']);
		unset($_SESSION['testi_cn_id']);
		
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Testimonials Added successfully</div>';
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }
if(isset($_SESSION['testi_type'])){ $testi_type=$_SESSION['testi_type']; unset($_SESSION['testi_type']); }else{ $testi_type=""; }
if(isset($_SESSION['testi_name'])){ $testi_name=$_SESSION['testi_name']; unset($_SESSION['testi_name']); }else{ $testi_name=""; }
if(isset($_SESSION['testi_details'])){ $testi_details=$_SESSION['testi_details']; unset($_SESSION['testi_details']); }else{ $testi_details=""; }
if(isset($_SESSION['testi_email'])){ $testi_email=$_SESSION['testi_email']; unset($_SESSION['testi_email']); }else{ $testi_email=""; }
if(isset($_SESSION['testi_mobile'])){ $testi_mobile=$_SESSION['testi_mobile']; unset($_SESSION['testi_mobile']); }else{ $testi_mobile=""; }
if(isset($_SESSION['testi_business_name'])){ $testi_business_name=$_SESSION['testi_business_name']; unset($_SESSION['testi_business_name']); }else{ $testi_business_name=""; }
if(isset($_SESSION['testi_cn_id'])){ $testi_cn_id=$_SESSION['testi_cn_id']; unset($_SESSION['testi_cn_id']); }else{ $testi_cn_id=""; }

$ob=new editheadersld();

if(isset($_POST['btnAdd']))
{	
	$ob->testi_type=trim(addslashes($_POST['testi_type']));			
	$ob->testi_name=trim(addslashes($_POST['testi_name']));
	$ob->testi_details=trim(addslashes($_POST['testi_details']));			
	$ob->testi_email=trim(addslashes($_POST['testi_email']));
	$ob->testi_mobile=trim(addslashes($_POST['testi_mobile']));			
	$ob->testi_business_name=trim(addslashes($_POST['testi_business_name']));
	$ob->testi_cn_id=trim(addslashes($_POST['testi_cn_id']));	
	$ob->testi_image=$_FILES['testi_image']['name'];
	
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:testi-add.php");
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
	var testi_details=document.getElementById('testi_details');
	var testi_business_name=document.getElementById('testi_business_name');
	var testi_name=document.getElementById('testi_name');
	var testi_email=document.getElementById('testi_email');
	var testi_mobile=document.getElementById('testi_mobile');
	var testi_cn_id=document.getElementById('testi_cn_id');
	var testi_image=document.getElementById('testi_image');

	var message="";
	var valid=true;
	
	if(testi_name.value=='' || testi_name.value == null)
	{
		message='Please enter Name';
		testi_name.focus();
		valid=false;
	}
	else if(testi_details.value=='')
	{
		message='Please enter details';
		testi_details.focus();
		valid=false;
	}
	else if(testi_business_name.value=='')
	{
		message='Please enter company name';
		testi_business_name.focus();
		valid=false;
	}
	else if(testi_email.value == '')
	{
		message='Please enter email';
		testi_email.value='';
		testi_email.focus();
		valid=false;
	}
	else if(testi_email.value == '' || testi_email.value.indexOf('@') == -1 || testi_email.value.indexOf('.') == -1)
	{
		message='Please enter valid email';
		testi_email.value='';
		testi_email.focus();
		valid=false;
	}
	else if(testi_mobile.value == '')
	{
		message='Please enter contact number';
		testi_mobile.value='';
		testi_mobile.focus();
		valid=false;
	}
	else if(isNaN(testi_mobile.value))
	{
		message='Please enter valid contact number';
		testi_mobile.value='';
		testi_mobile.focus();
		valid=false;
	}
	else if(testi_cn_id.value == '')
	{
		message='Please choose country';
		testi_cn_id.value='';
		valid=false;
	}
	else if(testi_image.value == '')
	{
		message='Please upload a image';
		testi_cn_id.value='';
		valid=false;
	}
	
	if(!valid)
	{
		document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> "+message;
		document.getElementById('msg').className="alert alert-danger";
	}
	return valid;
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
				<a href="testi-view.php">Manage Testimonials</a>
			</li>
			<li class="active">Testimonial Add</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Testimonials
			<small>
				<i class="icon-double-angle-right"></i>
				Testimonial Add
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="ctesti_edit" name="ctesti_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
	<em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
    
    <div id="msg"><?php echo $msg; ?></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Type <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select id="testi_type" name="testi_type" class="chosen-select">
               	<option value="buyer" <?php if($testi_type=='buyer'){?>selected<?php }?>>Buyer</option>
               	<option value="supplier" <?php if($testi_type=='supplier'){?>selected<?php }?>>Supplier</option>
            </select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Name <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<input name="testi_name" id="testi_name" class="col-xs-10 col-sm-5" type="text" value="<?php echo $testi_name; ?>" />
		</div>
	</div>
    
   	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Details <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<textarea type="text" name="testi_details" id="testi_details" class="col-xs-10 col-sm-7"><?php echo $testi_details;?></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Name <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<input name="testi_business_name" id="testi_business_name" class="col-xs-10 col-sm-5" type="text" value="<?php echo $testi_business_name; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<input name="testi_email" id="testi_email" class="col-xs-10 col-sm-5" type="text" value="<?php echo $testi_email; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Contact <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<input name="testi_mobile" id="testi_mobile" class="col-xs-10 col-sm-5" type="text" value="<?php echo $testi_mobile; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select id="testi_cn_id" name="testi_cn_id" class="chosen-select">
               	<option value="">Select</option>
                <?php 
				$cn_res = mysqli_query($con, "select * from country where cn_status = '1'");
				while($cn_row = mysqli_fetch_object($cn_res)){
				?>
               	<option value="<?php echo $cn_row->cn_id?>" <?php if($testi_cn_id==$cn_row->cn_id){?>selected<?php }?>><?php echo $cn_row->cn_name;?></option>
                <?php }?>
    		</select>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload Image</label>
		<div class="col-sm-9">
			<div class="ace-file-input" style="width:400px;"><input name="testi_image" id="id-input-file-2" type="file"></div>
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