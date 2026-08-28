<?php 
include "../common.php";
$token=$_GET['token'];

$sql="select * from user,business_profile where bnsprof_uid=usr_id and md5(bnsprof_id) = '".$token."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_array( $res);

if(isset($_POST['btnBack']))
{
	header("location:company-list.php");
}
if(isset($_POST['btnEdit']))
{
	$bnsprof_id=addslashes(trim($_POST['bnsprof_id']));
	header("location:company-edit.php?id=".md5($bnsprof_id));
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
				<a href="company-list.php">Manage Company</a>
			</li>
			<li class="active">Company Details</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Company
			<small>
				<i class="icon-double-angle-right"></i>
                <?php if($row['bnsprof_compname']!=''){ ?>
				Details of <strong><?php echo ucfirst($row['bnsprof_compname']);?></strong>
                <?php }else{ ?>
                Company Details
                <?php } ?>
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
<input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php  echo $row['bnsprof_id'];?>" />
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Name:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php  echo $row['bnsprof_compname'];?></label>
		</div>
	</div>
   <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Ownership Type:</label>
		<div class="col-sm-9">
        <?php
		$bo_row = mysqli_fetch_object(mysqli_query($con, "select owntyp_title from ownership_type where owntyp_id = '".$row['bnsprof_owntype']."' and owntyp_status = '1'"));
		?>
   		   	<label style="padding-top:4px;"><?php  echo $bo_row->owntyp_title; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CEO:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo ucfirst($row['bnsprof_ceoprefix']." ".$row['bnsprof_ceofname']." ".$row['bnsprof_ceolname']);?></label>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Username:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo ucwords($row['name_prefix']." ".$row['lname']." ".$row['fname']);?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Address:</label>
		<div class="col-sm-9">
   		   	<?php echo $row['bnsprof_address1']; ?><br />
<?php echo $row['bnsprof_address2']; ?><br />
<?php echo get_city_name($row['bnsprof_city']); ?><br />
<?php echo get_state_name($row['bnsprof_state']); ?><br />
<?php echo $row['bnsprof_zipcode']; ?>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Phone Number:</label>
		<div class="col-sm-9">
			<?php if($row['bnsprof_ph1'] != ''){echo "&nbsp;".$row['bnsprof_phcode1']." ".$row['bnsprof_ph1'];} ?><br />
            <?php if($row['bnsprof_ph2'] != ''){echo " &nbsp;".$row['bnsprof_phcode2']." ".$row['bnsprof_ph2'];} ?><br />
            <?php if($row['bnsprof_ph3'] != ''){echo " &nbsp;".$row['bnsprof_phcode3']." ".$row['bnsprof_ph3'];} ?><br />
            <?php if($row['bnsprof_ph4'] != ''){echo " &nbsp;".$row['bnsprof_phcode4']." ".$row['bnsprof_ph4'];} ?>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Fax Number:</label>
		<div class="col-sm-9">
			<?php if($row['bnsprof_fax1'] != ''){echo "&nbsp;".$row['bnsprof_faxcode1']." ".$row['bnsprof_fax1'];} ?><br />
            <?php if($row['bnsprof_fax2'] != ''){echo " &nbsp;".$row['bnsprof_faxcode2']." ".$row['bnsprof_fax2'];} ?>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
		<div class="col-sm-9">
			<?php if($row['bnsprof_emailalt1'] != ''){echo "&nbsp;".$row['bnsprof_emailalt1'];} ?><br />
            <?php if($row['bnsprof_emailalt2'] != ''){echo "&nbsp;".$row['bnsprof_emailalt2'];} ?><br />
            <?php if($row['bnsprof_emailalt3'] != ''){echo " &nbsp;".$row['bnsprof_emailalt3'];} ?>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_website_alt']; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Type:</label>
		<div class="col-sm-9">
   		   	<?php
				$bt = explode(',',$row['bnsprof_businesstype']); 
				$c=0;
				foreach($bt as $btval)
				{
					if($c>0){	echo ", ";	}
					$bt_row = mysqli_fetch_object(mysqli_query($con, "select bsntyp_title from business_type where bsntyp_id = '".$btval."' and bsntyp_status = '1'"));
					echo  $bt_row->bsntyp_title;
					$c++;
				}
			?>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Year of Establishment:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_yoe']; ?></label>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">No of Employees:</label>
		<div class="col-sm-9">
        <?php
		 if($row['bnsprof_comemp']>0){
		 $noemp=mysqli_fetch_array( mysqli_query($con, "select * from employee_range where emprange_id='".$row['bnsprof_comemp']."' and emprange_status='1'"));
		?>
   		   	<label style="padding-top:4px;"><?php echo $noemp['emprange_type']; ?></label>
        <?php } ?>
		</div>
	</div>
       
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Revenue Sales Turnover:</label>
		<div class="col-sm-9">
        <?php
		$rs_row = mysqli_fetch_object(mysqli_query($con, "select revturnover_title from revenue_turnover where revturnover_id = '".$row['bnsprof_turnover']."' and revturnover_status = '1'"));
		?>
   		   	<label style="padding-top:4px;"><?php echo $rs_row->revturnover_title; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_regno']; ?></label>
		</div>
	</div>
                 
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration Authority:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_regauthority']; ?></label>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CIN No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_cin_no']; ?></label>
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TAN No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_tan_no']; ?></label>
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">PAN No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_pan_no']; ?></label>
		</div>
	</div>      

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Service Tax No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_svtax_no']; ?></label>
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Excise Reg. No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_excisereg_no']; ?></label>
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TIN No. / VAT No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_vat_no']; ?></label>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TDGFT/IE Code:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_ie_code']; ?></label>
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CST No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_cst_no']; ?></label>
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">SSI No. / MSME No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_msme_no']; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">EPF No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_epf_no']; ?></label>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">ESI No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_esi_no']; ?></label>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">SCT No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_sct_no']; ?></label>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">DNB No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_dnb_no']; ?></label>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">RBI No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_rbi_no']; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">FSSAI-LICENSE No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_fssailic_no']; ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">N.S.I.C No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_nsic_no']; ?></label>
		</div>
	</div>
          
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">S.S.T No.:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo $row['bnsprof_sst_no']; ?></label>
		</div>
	</div>

	<?php if($row['bnsprof_complogo']!=''){ ?>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Logo:</label>
		<div class="col-sm-9">
			<img src="../upload/companylogo/<?php echo $row['bnsprof_complogo'];?>" width="200px" height="232px"/>
		</div>
	</div>
	<?php } ?>
    
    <div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnBack" id="btnBack"><i class="icon-reply"></i>&nbsp;Back</button>
			<button class="btn btn-yellow" type="submit" name="btnEdit" id="btnEdit"><i class="icon-edit"></i>&nbsp;Edit</button>
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