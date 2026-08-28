<?php 
include "../common.php";

class editUser{
	var $msg;
	var $usr_id;
	var $email;
	var $name_prefix;
	var $fname;
	var $lname;
	var $website;
	var $country;
	var $country_ph_code;
	var $mobile1;
	
	function __construct($usr_id)
	{
          $this->usr_id=$usr_id;     
	}
	
	function detailsObj()
	{
		global $con;
		$sql="select * from user where usr_id='".$this->usr_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		
		$ob->fname=trim(addslashes($_POST['fname']));
	$ob->lname=trim(addslashes($_POST['lname']));
	$ob->email=trim(addslashes($_POST['email']));
	$ob->country=trim(addslashes($_POST['country']));
	$ob->country_ph_code=trim(addslashes($_POST['country_ph_code']));
	$ob->mobile1=trim(addslashes($_POST['mobile1']));
	$ob->website=trim(addslashes($_POST['website']));
		
		if($this->fname == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter First Name.</div>';
			$valid=false;
		}
		elseif($this->lname == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Last Name.</div>';
			$valid=false;
		}
		elseif($this->email == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Email Address.</div>';
			$valid=false;
		}		
		elseif(!validate::is_email($this->email))
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid Email Address.</div>';
			$valid=false;
		}
		else if($this->country == "" || $this->country == "0")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Country.</div>';
			$valid=false;
		}
		else if($this->mobile1 == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a Mobile Number.</div>';
			$valid=false;
		}		
		else if(!is_numeric($this->mobile1))
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid Mobile Number.</div>';
			$valid=false;
		}
		
		return $valid;
	}
	function update()
	{	
	  global $con;
		$sql="update user
			set
				name_prefix='".$this->name_prefix."',
				fname='".$this->fname."',
				lname='".$this->lname."',
				email='".$this->email."',
				country='".$this->country."',
				country_ph_code='".$this->country_ph_code."',
				mobile1='".$this->mobile1."',
				website='".$this->website."'
			where
				usr_id = '".$this->usr_id."'";	
						
			mysqli_query($con, $sql) or die(mysql_error());	
		
		
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> User updated successfully.</div>';	
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$token = substr($_GET['token'], 4);
$ob=new editUser($token);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate']))
{	

	$ob->name_prefix=trim(addslashes($_POST['name_prefix']));
	$ob->fname=trim(addslashes($_POST['fname']));
	$ob->lname=trim(addslashes($_POST['lname']));
	$ob->email=trim(addslashes($_POST['email']));
	$ob->country=trim(addslashes($_POST['country']));
	$ob->country_ph_code=trim(addslashes($_POST['country_ph_code']));
	$ob->mobile1=trim(addslashes($_POST['mobile1']));
	$ob->website=trim(addslashes($_POST['website']));
	
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:user-edit.php?token=".rand(1000,9999).$ob->usr_id);
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
function checkvalid()
{
	var fname=document.getElementById('fname');
	var lname=document.getElementById('lname');
    var email=document.getElementById('email');
	var country=document.getElementById('country');
    var mobile1=document.getElementById('mobile1');
    var website=document.getElementById('website');

    var message="";
    var valid=true;
	var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
 	
   	if(fname.value=='')
	{
		message="Please enter First Name";
		fname.focus();
		valid=false;
	}
   	else if(!isNaN(fname.value))
	{
		message="Please enter valid First Name";
		fname.value='';
		fname.focus();
		valid=false;
	}
   	else if(lname.value=='')
	{
		message="Please enter Last Name";
		lname.focus();
		valid=false;
	}
   	else if(!isNaN(lname.value))
	{
		message="Please enter valid Last Name";
		lname.value='';
		lname.focus();
		valid=false;
	}
	else if(email.value == "" || email.value == null)
	{
		message="Please enter Email Address";
		email.focus();
		valid=false;
	}
	else if (!email.value.match(is_email))
    {
		message="Please enter valid Email Address";
		email.value="";
        email.focus();
        valid = false;		
    }
	else if(country.value=='' || country.value=='0')
	{
		message="Please select Country";
		country.focus();
		valid=false;
	}
	else if(mobile1.value=='')
	{
		message="Please enter Mobile Number";
		mobile1.focus();
		valid=false;
	}
	else if(isNaN(mobile1.value))
	{
		message="Mobile number must be numeric";
		mobile1.focus();
		valid=false;
	}
    else if(website.value != '' && !website.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
	{
		message='Please enter valid Website Link';
		website.focus();
		valid=false;
	}

	if(!valid)
	{
		document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> "+message;
		document.getElementById('msg').className="alert alert-danger";
	}
	return valid;
}
function getCountryPhCode(id)
{
	$.post("ajax-file/getCountryPhCode.php",{id:id},    function(data){
		$("#country_ph_code").val(data);
		});
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
				<a href="user-list.php">Manage User</a>
			</li>
			<li class="active">User Edit</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage User
			<small>
				<i class="icon-double-angle-right"></i>
				User Edit
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return checkvalid();">
	<div id="msg"><?php echo $msg; ?></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Name:</label>
		<div class="col-sm-9">
        <select name="name_prefix" id="name_prefix" class="col-sm-1">
        <?php
        $arr=array("Mr.","Ms.","Mrs.","Dr.");
		foreach($arr as $val)
		{
		?>
        <option value="<?php echo $val;?>" <?php if($val==$row->name_prefix) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
        <?php } ?>
        </select>
			<input name="fname" id="fname" class="col-xs-10 col-sm-4" type="text" value="<?php echo $row->fname; ?>" placeholder="First Name"/>
			<input name="lname" id="lname" class="col-xs-10 col-sm-4" type="text" value="<?php echo $row->lname; ?>" placeholder="Last Name"/>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
		<div class="col-sm-9">
        	<input name="email" id="email" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->email; ?>"/>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
		<div class="col-sm-9">
			<select id="country" name="country" class="chosen-select" onchange="getCountryPhCode(this.value);">
            <?php
				$sql_cn="select * from country where cn_status='1'";
				$res_cn=mysqli_query($con, $sql_cn);
				while($row_cn=mysqli_fetch_object($res_cn)){
			?>
            	<option value="<?php echo $row_cn->cn_id; ?>" <?php if($row_cn->cn_id==$row->country){	?> selected="selected"<?php } ?>><?php echo $row_cn->cn_name; ?></option>
            <?php	}	?>
            </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country Phone Code:</label>
		<div class="col-sm-9">
			<input name="country_ph_code" id="country_ph_code" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->country_ph_code; ?>" readonly="readonly"/>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Mobile / Cell Phone:</label>
		<div class="col-sm-9">
			<input name="mobile1" id="mobile1" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->mobile1; ?>"/>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
		<div class="col-sm-9">
        	<input name="website" id="website" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->website; ?>"/>
		</div>
	</div>
              
	
    
    <div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok"></i>&nbsp;Update</button>
		</div>
	</div>
 
</form>    
 			</div>		<br clear="all"/>
		</div>
			
	</div>
	<br clear="all" />	
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
	</body>
</html>