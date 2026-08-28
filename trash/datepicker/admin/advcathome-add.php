<?php 
ob_start();
//session_start(); 
include "../common.php";

class addAdvertisement{
	var $msg;	
	var $adv_img;
	var $adv_link;
	var $imgsize;
	var $adv_imagewidth;
	var $adv_imageheight;
	var $adv_country;
	var $adv_position;
	var $adv_cat;
	var $adv_subcat_id;	
	var $adv_subsub_cat_id;
	
	function __construct($imgsize, $adv_img, $adv_link,$adv_country,$adv_position,$adv_cat,$adv_subcat_id,$adv_subsub_cat_id)
	{		
		$this->imgsize=$imgsize;
		$this->adv_img=$adv_img;
		$this->adv_link=$adv_link;
		$this->adv_position = $adv_position;
		$this->adv_country = implode(",",$adv_country);
		$this->adv_cat = $adv_cat;
		$this->adv_subcat_id = $adv_subcat_id;
		$this->adv_subsub_cat_id = $adv_subsub_cat_id;

		$_SESSION['imgsize']=$this->imgsize;
		$_SESSION['adv_link']=$this->adv_link;

	}
	function valid()
	{
		$valid=true;	

		if($this->imgsize == "0")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select image size</div>';
			$valid=false;
		}
		else if($this->adv_link == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a link</div>';
			$valid=false;
		}
		else if($this->adv_img == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an image</div>';
			$valid=false;
		}
		return $valid;
	}

	function add()
	{	
	   global $con;
		if($_FILES["adv_img"]["name"] != "")
		{
			if ($_FILES["adv_img"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["adv_img"]["error"] . "<br />";
			}
			else
			{
				$this->adv_imagewidth=substr($this->imgsize,0,4);
				$this->adv_imageheight=substr($this->imgsize,5,4);
				
				$imgSImage = new SimpleImage();			
				$imgSImage->load($_FILES['adv_img']['tmp_name']);			
				$imgSImage->resize($this->adv_imagewidth,$this->adv_imageheight);
				
				$this->adv_img=$this->adv_imagewidth.rand(0,9999).$this->adv_imageheight.trim(addslashes($_FILES['adv_img']['name']));	
				$imgSImage->save("../upload/advertisementcathome/".$this->adv_img);	
				
				//$ds = move_uploaded_file($_FILES["adv_img"]["tmp_name"], "../upload/advertisementhome/".$this->adv_img) or die('error');
															
				$sql="Insert advertisementcathome
						SET
							adv_cat_id='".$this->adv_cat."',
							adv_subcat_id = '".$this->adv_subcat_id."',	
							adv_subsub_cat_id = '".$this->adv_subsub_cat_id."',			
							adv_link ='".$this->adv_link."',
							adv_img ='".$this->adv_img."',
							adv_imagewidth ='".$this->adv_imagewidth."',
							adv_imageheight ='".$this->adv_imageheight."',
							adv_country ='".$this->adv_country."',
							adv_position ='".$this->adv_position."',
							adv_updated_date=now(),
							adv_status = 1";


				mysqli_query($con, $sql) or die(mysql_error());
		

				$this->msg='<div class="alert alert-success"><i class="icon-ok"></i>Category Advertisement added successfully.</div>';
				
				unset($_SESSION['imgsize']);
				unset($_SESSION['adv_link']);
			}
		}
	}	
}

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}
if(isset($_SESSION['imgsize'])){	$imgsize=$_SESSION['imgsize'];	unset($_SESSION['imgsize']); }else{ $imgsize=""; }
if(isset($_SESSION['adv_link'])){	$adv_link=$_SESSION['adv_link'];	unset($_SESSION['adv_link']); }else{ $adv_link=""; }

if(isset($_POST['btnAdd']))
{ 	
	
	$adn=new addAdvertisement(addslashes(trim($_POST['imgsize'])), trim($_FILES['adv_img']['name']), addslashes(trim($_POST['adv_link'])),$_POST['adv_country'],$_POST['adv_position'],$_POST['scat_cat_id'],$_POST['cat_id'],$_POST['subcat_id']);
	if($adn->valid())
	{	
		$adn->add();		
	}
	$_SESSION['msg']=$adn->msg;

	header("location:advcathome-add.php");
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
				<a href="advcathome-view.php">Categories Advertisement Management</a>
			</li>
			<li class="active">Category Advertisement Add</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Categories Advertisement Management
			<small>
				<i class="icon-double-angle-right"></i>
				Category Advertisement Add
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
    <div id="msg"><?php echo $msg; ?></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Width & Height of Image</label>
		<div class="col-sm-9">
			<select name="imgsize" id="imgsize" class="chosen-select">
                    <option value="0791x095" <?php if($imgsize=="0791x0095"){ ?> selected="selected"<?php } ?>>791 x 95</option>
					<option value="0788x0081" <?php if($imgsize=="0166x1318"){ ?> selected="selected"<?php } ?>>788 x 81</option>
					<option value="0168x0698" <?php if($imgsize=="0168x0698"){ ?> selected="selected"<?php } ?>>168 x 698</option>
					<option value="0788x0071" <?php if($imgsize=="0788x0071"){ ?> selected="selected"<?php } ?>>788 x 71</option>
				</select>
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
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="scat_cat_id" id="scat_cat_id" class="chosen-select" required  onchange="showCategory();">
                <option value="">Select</option>
                <?php 
                $catsql = mysqli_query($con, "select * from product_category where pc_parent_id = '0' and pc_status = '1'");
                while($catrow = mysqli_fetch_object($catsql)){
                ?>
               <option value="<?php echo $catrow->pc_id; ?>"><?php echo $catrow->pc_name;?></option>
               <?php }?>
           </select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="cat_id" id="cat_id" onchange="showSubCategory();">
                <option value="">Select Category</option>
                <?php 
                $catsql = mysqli_query($con, "select * from product_category where pc_parent_id != '0' and pc_parent_id='".$mcat_id."' and pc_status = '1'");
                while($catrow = mysqli_fetch_object($catsql)){
                ?>
               <option value="<?php echo $catrow->pc_id; ?>"><?php echo $catrow->pc_name;?></option>
               <?php }?>
           </select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="subcat_id" id="subcat_id">
                <option value="">Select Category</option>
                <?php 
                $catsql = mysqli_query($con, "select * from product_category where pc_parent_id != '0' and pc_parent_id='".$submcat_id."' and pc_status = '1'");
                while($catrow = mysqli_fetch_object($catsql)){
                ?>
               <option value="<?php echo $catrow->pc_id; ?>"><?php echo $catrow->pc_name;?></option>
               <?php }?>
           </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Link</label>
		<div class="col-sm-9">
			<input name="adv_link" id="adv_link" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo $adv_link; ?>" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Banner Position</label>
		<div class="col-sm-9">
		<select name="adv_position" id="adv_position" class="chosen-select">
		 <option value="top">Top</option>
		 <!--<option value="middle">Middle</option>
		 <option value="left">Left</option>-->
		 <option value="bottom">Bottom</option>
		</select>
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
		function showCategory()
		{
			var pc_id=document.getElementById('scat_cat_id').value;
			$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#cat_id').html(data); }); 
		}
		function showSubCategory()
		{
			var pc_id=document.getElementById('cat_id').value;
			$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#subcat_id').html(data); }); 
		}
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