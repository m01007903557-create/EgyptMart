<?php  
include "../common.php";

$cf_fc_id=$_GET['hid'];
$pid=$_GET['pid'];
$mode=$_GET['mode'];

if($mode=='edit'){
$recObj_sql="select * from custom_faq where cf_id=".$pid;
$recObj=mysqli_query($con, $recObj_sql);
$row=mysqli_fetch_array( $recObj);
$cf_fc_id=$row['cf_fc_id'];
$cf_heading=$row['cf_heading'];
$cf_content=$row['cf_content'];
}
class addCustomPages{

	var $msg;
	var $pid;	
	var $mode;
	var $cf_fc_id;	
	var $cf_heading;
	var $cf_content;

    function __construct($pid,$mode,$cf_fc_id,$cf_heading,$cf_content) 
	{
		$this->pid=$pid;
		$this->mode=$mode;
		$this->cf_fc_id=$cf_fc_id;	
		$this->cf_heading=$cf_heading;
		$this->cf_content=$cf_content;
		
		$_SESSION['cf_heading']= $this->cf_heading;
		$_SESSION['cf_content']= $this->cf_content;
	}
	function valid()
	{
		$valid=true;
		if($this->cf_fc_id =="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a Category.</div>';
			$valid=false;
		}
		else if($this->cf_heading =="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a page heading.</div>';
			$valid=false;
		}
		else if($this->cf_content =="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a page content.</div>';
			$valid=false;
		}		
		return $valid;
	}
	
	function add()
	{				
	    global $con;
		$sql="insert into custom_faq 
			set												
				cf_fc_id ='".$this->cf_fc_id."',	
				cf_heading ='".$this->cf_heading."',
				cf_content ='".$this->cf_content."'";							
		
		mysqli_query($con, $sql) or die(mysql_error());
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Page added successfully.</div>';
		unset($_SESSION['cf_heading']);
		unset($_SESSION['cf_content']);
	}
	function edit()
	{				
	    global $con;
		$sql="update custom_faq 
			set												
				cf_fc_id ='".$this->cf_fc_id."',	
				cf_heading ='".$this->cf_heading."',
				cf_content ='".$this->cf_content."' 
				where cf_id='".$this->pid."'";							
	
		mysqli_query($con, $sql) or die(mysql_error());
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Page edited successfully.</div>';		
	}
}
if(isset($_SESSION['msg'])){ 	$msg=$_SESSION['msg'];	unset($_SESSION['msg']); }
 if(isset($_SESSION['cf_heading'])){ $cf_heading=$_SESSION['cf_heading']; unset($_SESSION['cf_heading']); } else { $cf_heading=""; }
 if(isset($_SESSION['cf_content'])){ $cf_content=$_SESSION['cf_content']; unset($_SESSION['cf_content']); } else { $cf_content=""; }
if(isset($_POST['btnAdd']))
{ 	
//echo addslashes(trim($_POST['cf_fc_id'])); exit;
	$adn=new addCustomPages(addslashes(trim($_POST['pid'])), addslashes(trim($_POST['mode'])), addslashes(trim($_POST['cf_fc_id'])), addslashes(trim($_POST['cf_heading'])),  addslashes(trim($_POST['cf_content'])));

	if($adn->valid())
	{
			$adn->add();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:support_change.php?hid=".$_POST['cf_fc_id']);
}

if(isset($_POST['btnUpdate']))
{ 	
	$adn=new addCustomPages(addslashes(trim($_POST['pid'])), addslashes(trim($_POST['mode'])), addslashes(trim($_POST['cf_fc_id'])), addslashes(trim($_POST['cf_heading'])),  addslashes(trim($_POST['cf_content'])));

	if($adn->valid())
	{
			$adn->edit();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:support_change.php?pid=".$_POST['pid']."&mode=".$_POST['mode']);
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
function GetPageList()
{
	var hid=$("#cf_fc_id").val();
	if((hid!='')&&(hid!=undefined))
	{
		$('#cus_pages').html('<img src=images/loader.gif border=0>');
		$.get("cus_support.php", {hid:hid}, function(data){
			$('#cus_pages').html(data);
		});
	}
	else
	{
		$('#cus_pages').html('');	
	}
}
function DelPageList(hid)
{
	$.get("del_cus_faq.php", {hid:hid}, function(data){	GetPageList(); });
}
GetPageList();
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
				<a href="support_change.php">Manage Support</a>
			</li>
			<li class="active">Support Add/Edit</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Support
			<small>
				<i class="icon-double-angle-right"></i>
				Support Add/Edit
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
 <div id="msg"><?php echo $msg; ?></div>

<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Support Category</label>
		<div class="col-sm-9">
			<?php
				$sql_ho="SELECT * from faq_categories";
				$res_ho=mysqli_query($con, $sql_ho);	
			?>     
			<select name="cf_fc_id" id="cf_fc_id" class="chosen-select" onchange="GetPageList()">
			  <option value="">Select</option>
		<?php  while($rec_ho=mysqli_fetch_object($res_ho)) {  ?>
      		<option value="<?php echo $rec_ho->fc_id; ?>" <?php if($cf_fc_id==$rec_ho->fc_id) { ?> selected="selected" <?php } ?>><?php echo $rec_ho->fc_name; ?></option>
		<?php  }  ?>
    	</select>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Support Heading</label>
		<div class="col-sm-9">
			<input name="cf_heading" id="cf_heading" class="col-xs-10 col-sm-5" type="text" <?php if($mode=='edit'){ ?> value="<?php echo $row['cf_heading']; ?>" <?php } else { ?>   value="<?php echo $cf_heading; ?>" <?php } ?> />
		</div>
	</div>
        
	
    
    
		         
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Support Content</label>
		<div class="col-sm-9">
            <textarea name="cf_content" id="cf_content"class="col-xs-10 col-sm-5"><?php if($mode=='edit'){  echo $row['cf_content']; } else { echo stripslashes($cf_content); } ?></textarea>
		</div>
	</div>
           
	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<input type="hidden" name="pid" id="pid" class="reg_txtfld" value="<?php echo $pid; ?>"/>
			<input type="hidden" name="mode" id="mode" class="reg_txtfld" value="<?php echo $mode; ?>"/>
            
            <?php if($mode=='edit'){ ?>
			<button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
			<?php } else { ?>		
			<button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd"><i class="icon-ok bigger-110"></i>Add</button>
			<?php } ?>    
			<button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
		</div>
	</div>	                              
</form>
<div id="cus_pages"></div>
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