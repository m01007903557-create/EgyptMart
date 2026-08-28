<?php 
ob_start();
//session_start(); 
include "../common.php";
check_user_login();
	class editAdvertisement{
	
	var $msg;
	var $adv_id;
	var $adv_img;
	var $adv_link;
	var $adv_imagewidth;
	var $adv_imageheight;
	var $adv_country;
	var $adv_global;
	
	function __construct($adv_id,$adv_country){
		$this->adv_id=$adv_id;
		$this->adv_country = implode(",",$adv_country);
	}
	function detailsObj(){
		global $con;
		$sql="select * from supplier_logo where adv_id=".$this->adv_id;
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid(){
	
		$valid=true;
			
		return $valid;
	}
	
	function update()
	{			
	   global $con;
		if($_FILES["adv_img"]["name"] != '')
		{
			if ($_FILES["adv_img"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["adv_img"]["error"] . "<br />";
			}
			else
			{	
				$sqlImg="select * from supplier_logo where adv_id='".$this->adv_id."'";
				$resImg=mysqli_query($con, $sqlImg);
				$rowImg=mysqli_fetch_object($resImg);
				
//				$pathS="../upload/logo/small/".$rowImg->ht_logo;	
				$pathB="../upload/supplier_logo/".$rowImg->adv_img;
	
				if(is_file($pathS))
				{
					unlink($pathS);
				}
				
				if(is_file($pathB))
				{
					unlink($pathB);
				}	
				
				$imgSImage = new SimpleImage();			
				$imgSImage->load($_FILES['adv_img']['tmp_name']);			
				$imgSImage->resize($this->adv_imagewidth,$this->adv_imageheight);
				
				$this->adv_img=$this->adv_imagewidth.rand(0,9999).$this->adv_imageheight.trim(addslashes($_FILES['adv_img']['name']));	
				$imgSImage->save("../upload/supplier_logo/".$this->adv_img);											
				
//				$ds = move_uploaded_file($_FILES["adv_img"]["tmp_name"], "../upload/supplier_logo/".$this->adv_img) or die('error');
				
				/*if($ds)
				{*/	
										
					$sql="update supplier_logo
							set
								adv_img ='".$this->adv_img."',
								adv_link ='".$this->adv_link."',
								adv_imagewidth ='".$this->adv_imagewidth."',
								adv_imageheight ='".$this->adv_imageheight."',
								adv_country ='".implode(",",$this->adv_country)."',
								adv_global ='".$this->adv_global."'
							where adv_id='".$this->adv_id."'";							
					mysqli_query($con, $sql) or die(mysql_error());
														
					$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';	
				/*}*/
			}		
		}
		else
		{
			echo $sql="update supplier_logo
								     set
									 adv_link ='".$this->adv_link."',
									 adv_country ='".implode(",",$this->adv_country)."',
									 adv_global ='".$this->adv_global."'
								    where adv_id='".$this->adv_id."'";
								
			mysqli_query($con, $sql) or die(mysql_error());
					
			$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';
			//exit();
		}						
	}	
}
	
if(isset($_SESSION['msg'])){
	$msg=$_SESSION['msg'];
	unset($_SESSION['msg']);
}

$ob=new editAdvertisement($_GET['aid']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
	
	$ob->adv_imagewidth=addslashes(trim($_POST['adv_imagewidth']));	
	$ob->adv_imageheight=addslashes(trim($_POST['adv_imageheight']));	
	$ob->adv_img=trim($_FILES['adv_img']['name']);
	$ob->adv_link=addslashes(trim($_POST['adv_link']));	
	$ob->adv_country=$_POST['adv_country'];
	$ob->adv_global=$_POST['adv_global'];
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	
	header("location:supp-edit.php?aid=".$ob->adv_id);
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
				<a href="supp-view.php">Supplier Logo</a>
			</li>
			<li class="active">Logo Edit</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Supplier Logo
			<small>
				<i class="icon-double-angle-right"></i>
				Logo Edit
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
	<div id="msg"><?php echo $msg; ?></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Image Width & Height</label>
		<div class="col-sm-9">
			<input type="hidden" name="adv_imagewidth" value="<?php echo $row->adv_imagewidth; ?>" />
            <input type="hidden" name="adv_imageheight" value="<?php echo $row->adv_imageheight; ?>" />
           	<?php echo $row->adv_imagewidth." x ".$row->adv_imageheight; ?>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
		<div class="col-sm-8">
            <?php
			$sqlcnty="select * from country where cn_status=1 order by cn_id desc";
			$rscontry=mysqli_query($con, $sqlcnty);
			
			$adv_country = explode(",",$row->adv_country);
			?>
            <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select" required>
			<?php 
			while($row_cntry=mysqli_fetch_object($rscontry))
			{
				$selected="";
				$countryid=$row_cntry->cn_id;
				foreach($adv_country as $singcntryid)
				{
					if($countryid==$singcntryid)
					{
						$selected = 'selected="selected"';
					}
				}
			?>
             
				<option value="<?php echo $countryid;?>" <?php echo $selected;?>><?php echo $row_cntry->cn_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
	<input type="hidden" name="adv_global" value="0">
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Link</label>
		<div class="col-sm-9">
			<input name="adv_link" id="adv_link" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo $row->adv_link; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Current Image</label>
		<div class="col-sm-9">
			<img src="../upload/supplier_logo/<?php  echo $row->adv_img;?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload Image</label>
		<div class="col-sm-9">
			<div class="ace-file-input" style="width:400px;"><input name="adv_img" id="id-input-file-2" type="file"></div>
		</div>
	</div>
        
	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
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