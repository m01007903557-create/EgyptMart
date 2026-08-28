<?php 
//ob_start();
//session_start(); 
include "../common.php";
//include "lib/pagination.php";

check_user_login();
class listProductType{
	var $sqlList="";
	var $start="";
	var $limit="";
	
	function setsql($sql){
		$this->sqlList=$sql;
	}
	function totalrecord(){
		global $con;
		return mysqli_num_rows(mysqli_query($con, $this->sqlList));
	}
	function listview(){
		global $con;
		$sql=$this->sqlList;
		$res=mysqli_query($con, $sql);
		return $res;
	}
	function deleterecord($adid){	
        global $con;	
		mysqli_query($con, "delete from product  where prd_id='".$adid."'");		
	}
	
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&aid=".$id;
		}
		else{
			$dellink="field-view.php?".$_SERVER['QUERY_STRING']."&action=del&aid=".$id;
		}
		return $dellink;
	}		
}



$al=new listProductType;
/********************delete record*********************/
	if($_GET['action']=="del")
	{
		//echo $_GET['aid'];		
		$al->deleterecord($_GET['aid']);
		header("location:field-view.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
	}		
/***********************************************/

$al->setsql("select * from product_type where pt_status=1");

$recObj=$al->listview();
	
if(isset($_POST['btnDelete']))
{ 
	foreach($_POST['cb'] as $cb)
	{				
		$al->deleterecord($cb);			
		mysqli_query($con, "update product_type set pt_status=0 where pt_id='".$cb."'");		
	}
	header("location:field-view.php");
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
<script language="javascript">
$(document).ready(function(){
	showSubcat();
	showFields();
});
function showCategory()
{
	var mcat=$("#mcat_id").val();
	$.get("showCategory.php", {q:mcat},	function(data){	$('#cat_id').html(data);	showFields(); });
}
function showSubcat()
{
	cat=$("#cat_id").val();
	$.get("showSubcat.php", {q:cat},	function(data){	$('#af_pc_id').html(data); showFields(); });
}

function showFields()
{
	scat=$("#af_pc_id").val();
	
	$.get("showField.php", {q:scat},		function(data){	$('#list').html(data);	 });
}

function DelField(af_id)
{
	if(confirm("Are you sure to delete this field?"))
	{
		$.get("field-del.php", {af_id:af_id}, function(data){	showFields(); });
	}
}

function ShowEditField(af_id){
	$('#display_nm_'+af_id).hide();
	$('#display_lbl_'+af_id).hide();
	$('#edit_'+af_id).hide();
	$('#save_'+af_id).show();
	$('#input_nm_'+af_id).show();
	$('#input_lbl_'+af_id).show();
}

function SaveField(af_id){
	var af_name=$('input#af_nm_'+af_id).val();
	var af_label=$('input#af_lbl_'+af_id).val();
	if(af_name!='' && af_label!='')
	{
		$.get("field-save.php", {af_id:af_id,af_name:af_name,af_label:af_label}, function(data){
			$('#display_nm_'+af_id).html(af_name);
			$('#display_lbl_'+af_id).html(af_label);
			$('#display_nm_'+af_id).show();
			$('#display_lbl_'+af_id).show();
			$('#edit_'+af_id).show();
			$('#save_'+af_id).hide();
			$('#input_nm_'+af_id).hide();
			$('#input_lbl_'+af_id).hide();
		 });
	}
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
				<a href="field-view.php">Manage Additional Field</a>
			</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Field List
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" name="college_view" id="college_view" method="post" >
<div id="msg"><?php echo $msg; ?></div>

	<div class="form-group">
    <?php
		$mcat_sql="select * from product_category where pc_parent_id='0' and pc_status='1'"; 
		$mcat_res=mysqli_query($con, $mcat_sql);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="mcat_id" id="mcat_id" type="text" class="width-40" onchange="showCategory();">
            	<option value="0" selected="selected">-Select-</option>
                <?php while($mcat_row=mysqli_fetch_object($mcat_res)) { ?>
                <option value="<?php echo $mcat_row->pc_id; ?>" <?php if($mcat_id==$mcat_row->pc_id) { ?> selected="selected"<?php } ?> ><?php echo ucfirst($mcat_row->pc_name); ?></option>
                <?php } ?> 
			</select>
		</div>
	</div>
    <div class="form-group">
    <?php
		$cat_sql="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$mcat_id."' and pc_status='1'"; 
		$cat_res=mysqli_query($con, $cat_sql);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="cat_id" id="cat_id" type="text" class="width-40" onchange="showSubcat()">
            	<option value="" selected="selected">-Select-</option>
                <?php while($cat_row=mysqli_fetch_object($cat_res)) { ?>
                <option value="<?php echo $cat_row->pc_id; ?>" <?php if($cat_id==$cat_row->pc_id) { ?> selected="selected"<?php } ?> ><?php echo ucfirst($cat_row->pc_name); ?></option>
                <?php } ?> 
			</select>
		</div>
	</div>
	<div class="form-group">
    <?php
		$scat_sql="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$cat_id."' and pc_status='1'";
		$scat_res=mysqli_query($con, $scat_sql);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub Category <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
			<select name="af_pc_id" id="af_pc_id" type="text" class="width-40" onchange="showFields()">
               	<option value="">-Select-</option>
                <?php while($scat_row=mysqli_fetch_object($scat_res)) { ?>
               	<option value="<?php echo $scat_row->pc_id; ?>" <?php if($scat_row->pc_id==$af_pc_id) {?> selected="selected" <?php } ?>><?php echo ucfirst($scat_row->pc_name); ?></option>
                <?php } ?>
			</select>
		</div>
	</div>
</form>
	<div id="list"></div>
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
				
				
				
				
				$('table th input:checkbox').on('click' , function(){
					var that = this;
					$(this).closest('table').find('tr > td:first-child input:checkbox')
					.each(function(){
						this.checked = that.checked;
						$(this).closest('tr').toggleClass('selected');
					});
						
				});
			
			
				$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
				function tooltip_placement(context, source) {
					var $source = $(source);
					var $parent = $source.closest('table')
					var off1 = $parent.offset();
					var w1 = $parent.width();
			
					var off2 = $source.offset();
					var w2 = $source.width();
			
					if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
					return 'left';
				}
				
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
                     
						<!--<div class="admin-hdr-bg">				 
							<div class="eID" style="width:250px"><strong>Field Name</strong></div>
                            <div class="eID" style="width:150px"><strong>Field Type</strong></div>
							<div class="action"><strong>Action</strong></div>
							<div class="clr"></div>
							
							<br clear="all"/>
						</div>

                        <div id="list">
					
                            </div>-->
                             