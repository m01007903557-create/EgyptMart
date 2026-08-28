<?php  
include "../common.php";


check_user_login();

class addCountry
{	
	var $msg;	
	var $cn_name;
	var $cn_code;
	var $cn_currency;
	var $cn_ph;
	var $cn_flag;

	function __construct($cn_name, $cn_code, $cn_currency, $cn_ph, $cn_flag)
	{		
		$this->cn_name=$cn_name;
		$this->cn_code=$cn_code;
		$this->cn_currency=$cn_currency;
		$this->cn_ph=$cn_ph;
		$this->cn_flag=$cn_flag;
	}

	function add()
	{	
	global $con;
		if($_FILES["cn_flag"]["name"] != "")
		{
			if ($_FILES["cn_flag"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["cn_flag"]["error"] . "<br />";
			}
			else
			{
				
				$imgSImage = new SimpleImage();			
				$imgSImage->load($_FILES['cn_flag']['tmp_name']);			
				$imgSImage->resize(20,20);
				
				$this->cn_flag=$this->cn_name.$this->cn_currency.trim(addslashes($_FILES['cn_flag']['name']));	
				$imgSImage->save("../images/country_flag/".$this->cn_flag);	
				
				//$ds = move_uploaded_file($_FILES["cn_flag"]["tmp_name"], "../upload/advertisement/".$this->cn_flag) or die('error');
															
				$sql="insert into country
						set			
							cn_name='".$this->cn_name."',
							cn_code='".strtoupper($this->cn_code)."',
							cn_currency='".strtoupper($this->cn_currency)."',
							cn_ph='".$this->cn_ph."',
							cn_flag ='".$this->cn_flag."',
							cn_status = 1";
				mysqli_query($con, $sql) or die(mysql_error());
			
				$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Country added successfully.</div>';
				
			}
		}
	}	
}

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}

if(isset($_POST['cn_name']) && isset($_POST['cn_name']) && isset($_POST['cn_name']))
{
	$adn=new addCountry(addslashes(trim($_POST['cn_name'])), addslashes(trim($_POST['cn_code'])), addslashes(trim($_POST['cn_currency'])), addslashes(trim($_POST['cn_ph'])), trim($_FILES['cn_flag']['name']));
	$adn->add();		
	$_SESSION['msg']=$adn->msg;

	header("Location:country.php");
}

if(isset($_POST['cn_id']))
{
	print_r($_POST);
	exit;
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
				<a href="setting-view.php">Manage Settings</a>
			</li>
			<li class="active">Country List</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Settings
			<small>
				<i class="icon-double-angle-right"></i>
				Country List
			</small>
		</h1>
	</div>
    
<script type="text/javascript">
function DelCountry(hid)
{
	var conf = confirm("Are your sure you want to delete this country?");
	if(conf == true)
	{	
		$.get("del_country.php", {hid:hid}, function(data){	location.reload();	});
	}
	return;
}
function validCountry()
{
	var cn_name=document.getElementById('cn_name');
	var cn_code=document.getElementById('cn_code');
	var cn_currency=document.getElementById('cn_currency');
	var cn_ph=document.getElementById('cn_ph');
	
	var cn_flag = document.getElementById('cn_flag');
	var fileName = cn_flag.value;
	var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
	
	var message="";
    var valid=true;
	
	if(cn_name.value == '')
	{
		alert("Please enter Country Name.");
		cn_name.focus();
		valid=false;
	}
	else if(cn_code.value == '')
	{
		alert("Please enter Country Code.");
		cn_code.focus();
		valid=false;
	}
	else if(cn_currency.value == '')
	{
		alert("Please enter Currency Code.");
		cn_currency.focus();
		valid=false;
	}
	else if(!isNaN(cn_currency.value))
	{
		alert("Currency Code cannot be a number. Please enter valid Code.");
		cn_currency.focus();
		valid=false;
	}
	else if(cn_ph.value == '')
	{
		alert("Please enter Phone Code.");
		cn_ph.focus();
		valid=false;
	}
	else if(isNaN(cn_ph.value))
	{
		alert("Please enter valid Phone Code.");
		cn_ph.focus();
		valid=false;
	}
	else if(cn_flag.value=='')
	{
		alert("Please upload Country Flag.");
		valid=false;
	}
	else if(ext != "png" && ext != "PNG")
	{
		alert("Please upload a png file.");
		valid=false;
	}
	/*else if(cn_flag.size>5)
	{
		alert("File size cannot be greater than 5kb.");
		valid=false;
	}*/
	else
	{
		$.post("checkNewCountry.php", {cn_name:cn_name.value,cn_currency:cn_currency.value,cn_ph:cn_ph.value}, function(data){
			if(data==1)
			{
				alert("Records already exist. Please try with different data.");	
			}
			else
			{
				alert("New country added successfully.");
				$("#Add_New_Country").submit();
			}
		});	
	}
}
function updCountry(id)
{

	var cn_id=id;
	var cn_name=document.getElementById('cn_name_'+id);
	var cn_code=document.getElementById('cn_code_'+id);
	var cn_currency=document.getElementById('cn_currency_'+id);
	var cn_ph=document.getElementById('cn_ph_'+id);
	
	/*var cn_flag = document.getElementById('cn_flag_'+id);
	var fileName = cn_flag.value;
	var ext = fileName.substring(fileName.lastIndexOf('.') + 1);*/
	
	var message="";
    var valid=true;
	
	if(cn_name.value == '')
	{
		alert("Please enter Country Name.");
		cn_name.focus();
		valid=false;
	}
	else if(cn_code.value == '')
	{
		alert("Please enter Country Code.");
		cn_code.focus();
		valid=false;
	}
	else if(!isNaN(cn_code.value))
	{
		alert("Country Code cannot be a number. Please enter valid Code.");
		cn_code.focus();
		valid=false;
	}
	else if(cn_currency.value == '')
	{
		alert("Please enter Currency Code.");
		cn_currency.focus();
		valid=false;
	}
	else if(!isNaN(cn_currency.value))
	{
		alert("Currency Code cannot be a number. Please enter valid Code.");
		cn_currency.focus();
		valid=false;
	}
	else if(cn_ph.value == '')
	{
		alert("Please enter Phone Code.");
		cn_ph.focus();
		valid=false;
	}
	else if(isNaN(cn_ph.value))
	{
		alert("Please enter valid Phone Code.");
		cn_ph.focus();
		valid=false;
	}
	/*else if(cn_flag.value=='')
	{
		message="Please upload Country Flag.";
		valid=false;
	}
	else if(ext != "png" && ext != "PNG")
	{
		message="Please upload a png file.";
		valid=false;
	}
	else if(cn_flag.size>2)
	{
		message="File size cannot be greater than 2kb.";
		valid=false;
	}*/
	else
	{
		$.post("checkOldCountry.php", {cn_id:cn_id,cn_name:cn_name.value,cn_code:cn_code.value,cn_currency:cn_currency.value,cn_ph:cn_ph.value}, function(data){
			if(data==1)
			{
				alert("Records already exist. Please try with different data.");	
			}
			else
			{
				$.post("updCountry.php", {cn_id:cn_id,cn_name:cn_name.value,cn_code:cn_code.value,cn_currency:cn_currency.value,cn_ph:cn_ph.value}, function(data){	
					alert('Record updated successfully.');
					$("#job_form"+cn_id).fadeOut(200);
					$(".background_overlay").fadeOut(200);
					showCountryList();
				});	
				
			}
		});	
	}
	
}
function showCountryImg(id)
{
	$.get("showCountryImage.php", {id:id},	function(data){
		$("#img_disp_"+id).html('');												 
		$("#img_disp_"+id).html('<img src="'+data+'" alt="" height="18" width="26"/>');
	});
}
function showCountryList()
{
	$.get("showCountryList.php",  function(data){	$("#countryList").html(data);	});	
}
function CanCountry()
{
	$('#save_link').show("fast");
	$('#save_add').hide("fast");
	$('#input_add').hide("fast");
	$('#cancel_add').hide("fast");
}

function ShowaddCountry()
{
	$('#save_link').hide("fast");
	$('#save_add').show("fast");
	$('#input_add').show("fast");
	$('#cancel_add').show("fast");
}

function ShowEditCountry(hid)
{
	$('#display_'+hid).hide();
	$('#edit_'+hid).hide();
	$('#del_'+hid).hide();
	
	$('#input_'+hid).show();
	$('#save_'+hid).show();
	$('#cancel_'+hid).show();
}
function CancelEditCountry(hid)
{
	$('#display_'+hid).show();
	$('#edit_'+hid).show();
	$('#del_'+hid).show();
	
	$('#input_'+hid).hide();
	$('#save_'+hid).hide();
	$('#cancel_'+hid).hide();
}

function EditCountry(hid)
{
	var country_inp=$('input#country_'+hid).val();
	var currency_inp=$('input#currency_'+hid).val();
	var phone_inp=$('input#phone_'+hid).val();
	
	if($.trim($('input#country_'+hid).val())=='')
	{
		alert("Please enter Country Name.");	
	}
	else if($.trim($('input#currency_'+hid).val())=='')
	{
		alert("Please enter Currency Code.");	
	}
	else if($.trim($('input#phone_'+hid).val())=='')
	{
		alert("Please enter Phone Code.");	
	}
	else
	{
		$.get("country_edit.php", {hid:hid,country_inp:country_inp,currency_inp:currency_inp},	function(data){
			$('#display_'+hid).html(data);
			$('#display_'+hid).show("fast");
			$('#edit_'+hid).show("fast");
			$('#save_'+hid).hide("fast");
			$('#input_'+hid).hide("fast");
		});
	}
}
</script>

<div class="row">
<div class="col-xs-12">
<form name="test_view" id="test_view" method="post"> 
    <div id="msg"><?php echo $msg; ?></div>

 <div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
	<tr>
		<TD style="border:0px;" align="center"><!--<span><button class="btn btn-xs btn-success" onclick="ShowaddCountry()" type="button"><i class="icon-plus-sign"></i><b>ADD COUNTRY/CURRENCY</b></button></span>-->
           <a href="#modal-form" role="button" data-toggle="modal" class="btn btn-xs btn-success"><i class="icon-plus-sign"></i><b>ADD COUNTRY</b></a>
        </TD>
	</tr>
</table>
<div id="countryList">
<div align="center"><img src="images/loader_anim.gif" align="middle"/></div>
</div>





</div>
</form>

<!---->

<div id="modal-form" class="modal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
   			<form id="Add_New_Country" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="blue bigger">Please fill the following fields</h4>
			</div>

			<div class="modal-body overflow-visible">
				<div class="row">
					<div class="col-xs-12 col-sm-5">
						<div class="space"></div>
						<input type="file" id="cn_flag" name="cn_flag"/>
					</div>

					<div class="col-xs-12 col-sm-7">
						<div class="form-group">
							<label for="form-field-username">Country Name</label>
							<div>
                                <input id="cn_name" name="cn_name" class="input-large" type="text" placeholder="Country Name" value="" />
                            </div>
                        </div>

                        <div class="space-4"></div>
                        
                        <div class="form-group">
							<label for="form-field-username">Country Code</label>
							<div>
                                <input id="cn_code" name="cn_code" class="input-large" type="text" placeholder="Country Code" value="" />
                            </div>
                        </div>
                        
                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-username">Currency Code</label>

							<div>
                                <input id="cn_currency" name="cn_currency" class="input-medium" type="text" placeholder="Currency Code" value="" />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-first">Phone Code</label>
                            <div>
                                <input id="cn_ph" name="cn_ph" class="input-medium" type="text" placeholder="Phone Code" value="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-sm" data-dismiss="modal">
                    <i class="icon-remove"></i>
                    Cancel
                </button>

                <button class="btn btn-sm btn-primary" type="button" onClick="validCountry();">
                    <i class="icon-ok"></i>
                    Save
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
<!---->

</div></div>



    
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
				
				showCountryList();
							
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
				/////////
				$('#modal-form-edit input[type=file]').ace_file_input({
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
				$('#modal-form-edit').on('shown.bs.modal', function () {
					$(this).find('.chosen-container').each(function(){
						$(this).find('a:first-child').css('width' , '210px');
						$(this).find('.chosen-drop').css('width' , '210px');
						$(this).find('.chosen-search input').css('width' , '200px');
					});
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form-edit').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/
			
			});
		</script>
</html>