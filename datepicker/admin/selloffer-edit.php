<?php 
include "../common.php";

class editSelloffer{	
	var $msg;
	var $so_id;
	var $pc_id;
	var $so_pc_id;
	var $so_service;
	var $so_description;
	var $so_preferred_buyer_location;
	var $so_validity;
	var $so_pic;

	
	function __construct($so_id){
		$this->so_id=$so_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from sale_offer,product_category where so_pc_id=pc_id and md5(so_id)='".$this->so_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		
		$filename = $_FILES['so_pic']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		if($this->so_service == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Service Name.</div>';
			$valid=false;
		}
		else if($this->so_description=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Description.</div>';
			$valid=false;
		}
		else if($this->pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
			$valid=false;
		}
		else if($this->so_pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
			$valid=false;
		}
		else if($this->so_pic!='' && ($ext!='jpg' && $ext!='JPG' && $ext!='gif'  && $ext!='GIF' && $ext!='png'  && $ext!='PNG'))
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid file.</div>';
			$valid=false;
		}
		return $valid;
	}
	
	function update() 
	{
		global $con;
		if($_FILES["so_pic"]["name"] != "")		
		{
			if ($_FILES["so_pic"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["so_pic"]["error"] . "<br />";
			}
			else
			{
				$sqlImg="select * from sale_offer where so_id='".$this->so_id."'";
				$resImg=mysqli_query($con, $sqlImg);
				$rowImg=mysqli_fetch_object($resImg);
				
				
				$this->so_pic='so-'.rand(0,9999).trim(addslashes($_FILES['so_pic']['name']));	
			
				$ds = move_uploaded_file($_FILES["so_pic"]["tmp_name"], "../upload/sale_offer/".$this->so_pic) or die('error');	
				
				if($ds)
				{
					$pathLrg="../upload/sale_offer/".$rowImg->so_pic;
					if(is_file($pathLrg))
					{
						unlink($pathLrg);
					}
				
					$pathThumb="../upload/sale_offer/thumb/".$rowImg->so_pic;
					if(is_file($pathThumb))
					{
						unlink($pathThumb);
					}
					
					/** Thumb image creation **/
					$imgSImage = new SimpleImage();			
					$imgSImage->load("../upload/sale_offer/".$this->so_pic);			
					$imgSImage->resize(100,80);//width,height
				
					$imgSImage->save("../upload/sale_offer/thumb/".$this->so_pic);
					/** Thumb image creation **/
							
					$sql="update sale_offer
					set
						so_pc_id='".$this->so_pc_id."',
						so_service ='".$this->so_service."',
						so_description ='".$this->so_description."',
						so_preferred_buyer_location ='".$this->so_preferred_buyer_location."',
						so_validity ='".$this->so_validity."',
						so_pic ='".$this->so_pic."',
						so_updated_date=now()
					where
						so_id='".$this->so_id."'";
			
					mysqli_query($con, $sql) or die(mysql_error());
			
					unset($_SESSION['pc_id']);
					unset($_SESSION['so_pc_id']);
					unset($_SESSION['so_service']);
					unset($_SESSION['so_description']);
					unset($_SESSION['so_preferred_buyer_location']);
					unset($_SESSION['so_validity']);
			
					$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer posted successfully.</div>';
				}
				else
				{
					$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Error on file upload. Please try after some time.</div>';	
				}
			}
		}
		else
		{
			$sql="update sale_offer
				set
					so_pc_id='".$this->so_pc_id."',
					so_service ='".$this->so_service."',
					so_description ='".$this->so_description."',
					so_preferred_buyer_location ='".$this->so_preferred_buyer_location."',
					so_validity ='".$this->so_validity."',
					so_updated_date=now()
				where
					so_id='".$this->so_id."'";
				
			mysqli_query($con, $sql);
		
			unset($_SESSION['pc_id']);
			unset($_SESSION['so_pc_id']);
			unset($_SESSION['so_service']);
			unset($_SESSION['so_description']);
			unset($_SESSION['so_validity']);
			
			$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer posted successfully.</div>';
		}	   	
	}	
}
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$ob=new editSelloffer($_GET['token']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
	
	$ob->so_id=addslashes(trim($_POST['so_id']));
	$ob->pc_id=addslashes(trim($_POST['pc_id']));
	$ob->so_pc_id=addslashes(trim($_POST['so_pc_id']));
	$ob->so_service=addslashes(trim($_POST['so_service']));
	$ob->so_description=addslashes(trim($_POST['so_description']));
	$ob->so_preferred_buyer_location=addslashes(trim($_POST['so_preferred_buyer_location']));
	
	
	$ob->so_validity=addslashes(trim($_POST['so_validity']));
	$ob->so_pic=trim($_FILES['so_pic']['name']);
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header('Location:../selloffer-email.php?admn_so_id='.$ob->so_id);
	//header("location:selloffer-edit.php?token=".md5($ob->so_id));
}

/*$sql="select * from sale_offer where md5(so_id)='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);*/

if(isset($_POST['btnApprove']))
{
	
	$so_id=addslashes(trim($_POST['so_id']));

	$sql="update sale_offer set
			so_approval_status='1',
			so_approval_date=now()
		where
			so_id='".$so_id."'";
	mysqli_query($con, $sql);
	
	$_SESSION['msg']='<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer Approved successfully.</div>';
	header("Location:selloffer-edit.php?token=".md5($so_id));
}
if(isset($_POST['btnDisApprove']))
{
	
	$so_id=addslashes(trim($_POST['so_id']));

	$sql="update sale_offer set 
			so_approval_status='2',
			so_approval_date=now()
		where
			so_id='".$so_id."'";
	mysqli_query($con, $sql);
	
	$_SESSION['msg']='<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer Disapproved successfully.</div>';
	header("Location:selloffer-edit.php?token=".md5($so_id));
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
function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data); }); 
}
function showSubcat()
{
	var pc_id=document.getElementById('pc_id').value;
	$.get("showSubcat.php",{q:pc_id},	function(data){	$('#so_pc_id').html(data); }); 
}

function validForm()
{
	var so_service=document.getElementById('so_service');
	var pc_id=document.getElementById('pc_id');
	var so_pc_id=document.getElementById('so_pc_id');
	var so_description=document.getElementById('so_description');
	var so_pic=document.getElementById('so_pic');
	var fileName = so_pic.value;
	var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
	
	var message="";
	var valid=true;
	if(so_service.value=='' || so_service.value == null)
	{
		message='Please enter Service Name.';
		so_service.focus();
		valid=false;
	}
	else if(!isNaN(so_service.value))
	{
		message='Please enter valid Service Name.';
		so_service.focus();
		valid=false;
	}
	else if(so_description.value=='' || so_description.value == null)
	{
		message='Please enter Description.';
		so_description.focus();
		valid=false;
	}
	else if(!isNaN(so_description.value))
	{
		message='Please enter valid Description.';
		so_description.focus();
		valid=false;
	}
	else if(pc_id.value=='' || pc_id.value == null)
	{
		message='Please select Category.';
		pc_id.focus();
		valid=false;
	}
	else if(so_pc_id.value=='' || so_pc_id.value == null)
	{
		message='Please select Sub-Category.';
		so_pc_id.focus();
		valid=false;
	}
	else if((fileName!='' && fileName != null) && (ext!="GIF" && ext!="gif" && ext!="PNG" && ext!="png" && ext!="JPG" && ext!="jpg" && ext!="JPEG" && ext!="jpeg"))
	{
		message='Please upload valid Image';
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
				<a href="selloffer-view.php">Manage Sell Offer</a>
			</li>
			<li class="active">Sell Offer Edit</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Sell Offer
			<small>
				<i class="icon-double-angle-right"></i>
				Sell Offer Edit
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return validForm();">
	<input type="hidden" id="so_id" name="so_id" value="<?php echo $row->so_id; ?>" />

	<div id="msg"><?php echo $msg;?></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Service:</label>
		<div class="col-sm-9">
	        <input name="so_service" id="so_service" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->so_service; ?>" />
		</div>
	</div>
	
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Description:</label>
		<div class="col-sm-9">
             <textarea id="so_description" name="so_description" class="col-xs-10 col-sm-7"><?php echo $row->so_description; ?></textarea>
		</div>
	</div>
    <div class="form-group">
    <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row->pc_parent_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysqli_query($con, $mcat_sql);
	$mcat_row=mysqli_fetch_object($mcat_res);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
		<div class="col-sm-9">
        <select id="mcat_id" name="mcat_id" onChange="showCategory();">
        <?php
			$sql_mcat="select * from product_category where pc_parent_id='0' and pc_status='1'";
			$res_mcat=mysqli_query($con, $sql_mcat);
			while($row_mcat=mysqli_fetch_object($res_mcat))
			{	?>
				<option value="<?php echo $row_mcat->pc_id; ?>" <?php if($row_mcat->pc_id==$mcat_row->pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_mcat->pc_name; ?></option>
		<?php	}	?>
        </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
		<div class="col-sm-8">
        <?php
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_pc="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$mcat_row->pc_id."' and pc_status='1'";
			$res_pc=mysqli_query($con, $sql_pc);
			?>
            <select id="pc_id" name="pc_id" onChange="showSubcat();">
            <?php
			while($row_pc=mysqli_fetch_object($res_pc))
			{	?>
				<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$row->pc_parent_id){ ?> selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
		<div class="col-sm-8">
        <?php
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_spc="select * from product_category where pc_parent_id=(select pc_parent_id from product_category where pc_id='".$row->so_pc_id."')";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="so_pc_id" name="so_pc_id">
            	<option value="0"> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->so_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Prefrences:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="so_preferred_buyer_location_1" name="so_preferred_buyer_location" class="ace" value="abroad" <?php if($row->so_preferred_buyer_location=='abroad'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="so_preferred_buyer_location_2" name="so_preferred_buyer_location" class="ace" value="any" <?php if($row->so_preferred_buyer_location=='any'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad + Domestic</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="so_preferred_buyer_location_3" name="so_preferred_buyer_location" class="ace" value="domestic" <?php if($row->so_preferred_buyer_location=='domestic'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Domestic Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="so_preferred_buyer_location_4" name="so_preferred_buyer_location" class="ace" value="my_city" <?php if($row->so_preferred_buyer_location=='my_city'){ ?> checked="checked"<?php } ?>/><span class="lbl"> My City Only</span></label>
            
        </div>
	</div>
    
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Validity:</label>
		<div class="col-sm-8">
        <div class="radio">
			<label><input name="so_validity" id="so_validity_30" class="ace" type="radio" value="30" <?php if($row->so_validity=='30'){ ?> checked="checked"<?php } ?>/><span class="lbl"> 1 Month </span></label>
			<label><input name="so_validity" id="so_validity_90" class="ace" type="radio" value="90" <?php if($row->so_validity=='90'){ ?> checked="checked"<?php } ?>><span class="lbl"> 3 Months </span></label>
			<label><input name="so_validity" id="so_validity_365" class="ace" type="radio" value="365" <?php if($row->so_validity=='365'){ ?> checked="checked"<?php } ?>><span class="lbl"> 1 Year </span></label>
        </div>
	        
			
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posting Date:</label>
		<div class="col-sm-8">
	        <label style="padding-top:4px;"><?php echo date("d-M-Y",strtotime($row->so_posting_date)); ?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Previous :</label>
		<div class="col-sm-8">
	        <?php if($row->so_pic!=''){ ?>
            <img src="../upload/sale_offer/<?php echo $row->so_pic; ?>" width="100px;" height="90px;" />
            <?php	}else{	?>
            <img src="../upload/sale_offer/no-image.png" width="100px;" height="90px;" />
            <?php } ?>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload New:</label>

			<div class="ace-file-input col-sm-5"><input name="so_pic" id="so_pic" type="file"></div>

	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Approval Status:</label>
		<div class="col-sm-8">
	        <label style="padding-top:4px;">
	         <?php 
				if($row->so_approval_status=='1'){ echo "Approved";	}
				if($row->so_approval_status=='0'){	echo "Pending Approval"; }
				if($row->so_approval_status=='2'){	echo "Disapproved"; } 
			?>
            </label>    
		</div>
	</div>            

	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
        <?php	if($row->so_approval_status=='0'){ ?>
			<button class="btn btn-info" type="submit" name="btnApprove" id="btnApprove"><i class="icon-ok bigger-110"></i>Approve</button>
			<button class="btn btn-danger" type="submit" name="btnDisApprove" id="btnDisApprove"><i class="icon-ban-circle bigger-110"></i>Disapprove</button>
        <?php } ?>
        <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
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
			
				
				$('#id-input-file-1 , #id-input-file-2, #so_pic').ace_file_input({
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