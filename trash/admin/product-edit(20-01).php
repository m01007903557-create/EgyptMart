<?php 
//ob_start();
//session_start();
include "../common.php";

check_user_login();

class editProduct{
	
	
	var $msg;
	var $pd_id;
	var $pd_code;
	var $cat_id;
	var $pd_subcat_id;	
	var $pd_title;
	var $pd_payment;
	var $pd_desc;
	var $pd_fob_price;
	var $pd_currency;
	var $pd_preferred_buyer_location;
	var $pd_min_order_qty;
	var $pd_unit;
	var $pd_pod;
	var $pd_pn_capct;
	var $pd_dlv_time;
	var $pd_pck_dets;

	
	function __construct($pd_id){
		$this->pd_id=$pd_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from products,product_category where pd_subcat_id=pc_id and pd_id='".$this->pd_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		global $con;
		$sqlrpl = "select bd_word from bad_word";
		$resrpl = mysqli_query($con, $sqlrpl);
		while($rowrpl = mysqli_fetch_object($resrpl))
		{		
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		$title    = strtoupper($this->pd_title);
		$desc    = strtoupper($this->pd_desc);
		
		$valid=true;
		if($this->cat_id == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose the Product Category here.</div>';
			$valid=false;
		}
		else if($this->pd_subcat_id == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose the Product subcategory here.</div>';
			$valid=false;
		}
		else if($this->pd_title == "")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter the Product Name.</div>';
			$valid=false;
		}
		else if($this->pd_title != "")
		{		
			foreach($letters as $val)
			{
				$pos = strpos($title, $val);
				if ($pos !== false)
				{
					$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> You can\'t post words like '.$val.' in Product Name.</div>';
					$valid=false;
					break;
				} 
			}
			
		}
		else if(strlen($this->pd_desc)>4000)
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please check that Product Description cannot have more than 4000 characters.</div>';
			$valid=false;
		}
		else if($this->pd_desc != "")
		{		
			foreach($letters as $val){
				$pos = strpos($desc, $val);
				if ($pos !== false) {
					$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> You can\'t post words like '.$val.' in Product Description.</div>';
					$valid=false;
				} 
			}
		}	
		else if($this->pd_fob_price == '')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Price.</div>';
			$valid=false;
		}
		else if($this->pd_currency == '')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Currency.</div>';
			$valid=false;
		}
		return $valid;
	}
	
	function update()
	{		
	    global $con;
		$btype1="";
		foreach($this->pd_payment as $val)
		{
			$btype1=$val.",".$btype1;
		}
		$btype=substr($btype1,0,-1);
			
		$sql="update products
				set
					pd_subcat_id='".$this->pd_subcat_id."',
					pd_title='".$this->pd_title."',
					pd_code='".$this->pd_code."',
					pd_desc='".$this->pd_desc."',
					pd_payment='".$btype."',
					pd_fob_price='".$this->pd_fob_price."',
					pd_currency='".$this->pd_currency."',
					pd_preferred_buyer_location='".$this->pd_preferred_buyer_location."',
					pd_min_order_qty='".$this->pd_min_order_qty."',
					pd_unit='".$this->pd_unit."',
					pd_pod='".$this->pd_pod."',
					pd_pn_capct='".$this->pd_pn_capct."',
					pd_dlv_time='".$this->pd_dlv_time."',
					pd_pck_dets='".$this->pd_pck_dets."'
				where
					pd_id='".$this->pd_id."'";
		
		
		
		mysqli_query($con, $sql) or die(mysql_error());
															
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Product updated successfully</div>';	
	}	
}

if(isset($_SESSION['msg'])){
	$msg=$_SESSION['msg'];
	unset($_SESSION['msg']);
}

$ob=new editProduct($_GET['fid']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate']))
{
	$ob->pd_code=addslashes(trim($_POST['pd_code']));
	$ob->cat_id=addslashes(trim($_POST['cat_id']));
	$ob->pd_subcat_id=addslashes(trim($_POST['pd_subcat_id']));
	$ob->pd_title=addslashes(trim($_POST['pd_title']));
	$ob->pd_payment=$_POST['pd_payment'];
	$ob->pd_desc=addslashes(trim($_POST['pd_desc']));
	$ob->pd_fob_price=addslashes(trim($_POST['pd_fob_price']));
	$ob->pd_currency=addslashes(trim($_POST['pd_currency']));
	$ob->pd_preferred_buyer_location=addslashes(trim($_POST['pd_preferred_buyer_location']));
	$ob->pd_min_order_qty=addslashes(trim($_POST['pd_min_order_qty']));
	$ob->pd_unit=addslashes(trim($_POST['pd_unit']));
	$ob->pd_pod=addslashes(trim($_POST['pd_pod']));
	$ob->pd_pn_capct=addslashes(trim($_POST['pd_pn_capct']));
	$ob->pd_dlv_time=addslashes(trim($_POST['pd_dlv_time']));
	$ob->pd_pck_dets=addslashes(trim($_POST['pd_pck_dets']));
	
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	$pid = $_GET['fid'];
		$get_user = "SELECT * FROM user WHERE usr_id = (SELECT pd_uid FROM products WHERE pd_id = '$pid')";
		$res_user = $con->query($get_user);
		$suser = $res_user->fetch_object();
		$suname = $suser->name_prefix." ".$suser->fname." ".$suser->lname;
		$to = $suser->email;

		$get_product = "SELECT * FROM products WHERE pd_id = '$pid'";
		$res_product = $con->query($get_product);
		$sproduct = $res_product->fetch_object();
		$spname = $sproduct->pd_title;
		/*Put Your Email Adress Here*/
			$subject = "Your product is updated from ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			$message = $suname."<br /><br />"."Your Product <b>".$spname."</b> is updated by admin";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";
			if(mail($to, $subject, $message, $headers)){
				//echo "test"; 
				header('Location:../product-email.php?admn_pd_id='.$ob->pd_id);
				//exit;
			//header("location:product-edit.php?fid=".$ob->pd_id);
				}
	
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
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#cat_id').html(data); }); 
}
function showSubcat()
{
	var id=document.getElementById('cat_id').value;
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#pd_subcat_id').html(data);	}); 
}
function myvalid()
{
	var mcat_id=document.getElementById('mcat_id');
	var cat_id=document.getElementById('cat_id');
	var pd_subcat_id=document.getElementById('pd_subcat_id');
	var pd_title=document.getElementById('pd_title');
	var pd_desc=document.getElementById('pd_desc');
	var pd_fob_price=document.getElementById('pd_fob_price');
	var pd_currency=document.getElementById('pd_currency');
	var pd_min_order_qty=document.getElementById('pd_min_order_qty');
	var pd_unit=document.getElementById('pd_unit');
	
    var message="";
    var valid=true;
	
   	if(mcat_id.value=='')
	{
		message="Please choose the Product Main Category here.";
		mcat_id.focus();
		valid=false;
	}
	else if(cat_id.value=='')
	{
		message="Please choose the Product Category here.";
		cat_id.focus();
		valid=false;
	}
	else if(pd_subcat_id.value=='')
	{
		message="Please choose the Product Subcategory here";
		pd_subcat_id.focus();
		valid=false;
	}
	else if(pd_title.value=='')
	{
		message="Please enter the Product Name.";
		pd_title.focus();
		valid=false;
	}
	else if(pd_desc.value.length>4000)
	{
		message="Please check that Product Description cannot have more than 4000 characters.";
		pd_desc.focus();
		valid=false;
	}
	else if(pd_fob_price.value=='' || pd_fob_price.value=='0.00')
	{
		message="Please enter Product Price.";
		pd_fob_price.focus();
		valid=false;
	}
	else if(pd_fob_price.value!='' && isNaN(pd_fob_price.value))
	{
		message="Product price must be numberic.";
		pd_fob_price.focus();
		valid=false;
	}
	else if(pd_fob_price.value!='' && pd_currency.value=='')
	{
		message="Please select Currency.";
		pd_currency.focus();
		valid=false;
	}
	else if(pd_min_order_qty.value=='' || pd_min_order_qty.value=='0')
	{
		message="Please enter Minimum order quantity.";
		pd_min_order_qty.value='';
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(pd_min_order_qty.value!='' && pd_min_order_qty.value!='0' && isNaN(pd_min_order_qty.value))
	{
		message="Minimum order quantity must be numberic.";
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(pd_min_order_qty.value!='' && !isNaN(pd_min_order_qty.value) && pd_unit.value=='0')
	{
		message="Please select Measurement Unit.";
		pd_unit.focus();
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
				<a href="product-view.php">Manage Products</a>
			</li>
			<li class="active">Product Details</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Products
			<small>
				<i class="icon-double-angle-right"></i>
				Product Details
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">

	<div id="msg"><?php echo $msg; ?></div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Date:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo date('d M, y',strtotime($row->pd_date)); ?></label>
		</div>
	</div>
	
    
    
    
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">By:</label>
		<div class="col-sm-9">
   		   <a href="user-details.php?token=<?php echo rand(1000,9999).md5($row->pd_uid);?>"><?php echo ucfirst(user_info($row->pd_uid,'fname')." ".user_info($row->pd_uid,'lname')); ?></a>
		</div>
	</div>
      
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Product Code:</label>
		<div class="col-sm-9">
   		   	<input name="pd_code" id="pd_code" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_code; ?>"/>
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
		<div class="col-sm-9">
        <?php
		$sql_cat="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$mcat_row->pc_id."'";
		?>
        <select id="cat_id" name="cat_id" onChange="showSubcat();">
        <?php
			
			$res_cat=mysqli_query($con, $sql_cat);
			while($row_cat=mysqli_fetch_object($res_cat))
			{	?>
				<option value="<?php echo $row_cat->pc_id; ?>" <?php if($row_cat->pc_id==$row->pc_parent_id){ ?> selected="selected"<?php } ?>><?php echo $row_cat->pc_name; ?></option>
		<?php	}	?>
        </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
		<div class="col-sm-9">
        <select id="pd_subcat_id" name="pd_subcat_id">
        <?php
			$sql_scat="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$row->pc_parent_id."'";
			$res_scat=mysqli_query($con, $sql_scat);
			while($row_scat=mysqli_fetch_object($res_scat))
			{	?>
				<option value="<?php echo $row_scat->pc_id; ?>" <?php if($row_scat->pc_id==$row->pd_subcat_id){ ?> selected="selected"<?php } ?>><?php echo $row_scat->pc_name; ?></option>
		<?php	}	?>
        </select>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Title:</label>
		<div class="col-sm-9">
   		   	<input name="pd_title" id="pd_title" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_title; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Product Payment Through:</label>
        <div class="checkbox col-sm-8">
        <?php
		$pdpayment=explode(",",$row->pd_payment);
        $paymentres=mysqli_query($con, "select * from payment_gateway");
		while($paymentrow=mysqli_fetch_object($paymentres))
		{
			?>
			<label><input class="ace" name="pd_payment[]" id="pd_payment" value="<?php echo $paymentrow->id;?>" <?php if(in_array($paymentrow->id,$pdpayment)){ ?>checked="checked" <?php } ?> type="checkbox" /><span class="lbl"><?php echo ucwords($paymentrow->pg_name);?></span></label>
		<?php
		} ?>
		</div>

	</div>
   <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Description:</label>
		<div class="col-sm-8">
	        <textarea id="pd_desc" name="pd_desc" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 132px;" class="autosize-transition form-control"><?php echo $row->pd_desc; ?></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Price:</label>
		<div class="col-sm-9">
   		   	<input name="pd_fob_price" id="pd_fob_price" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_fob_price; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Currency:</label>
		<div class="col-sm-9">
			<select name="pd_currency" id="pd_currency">
                <option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($currencyrow=mysqli_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($row->pd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?> >
				<?php echo $currencyrow->cn_currency;?> 
                </option>
				<?php } ?>
            </select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Prefrences:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="pd_preferred_buyer_location_1" name="pd_preferred_buyer_location" class="ace" value="abroad" <?php if($row->pd_preferred_buyer_location=='abroad'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="pd_preferred_buyer_location_2" name="pd_preferred_buyer_location" class="ace" value="any" <?php if($row->pd_preferred_buyer_location=='any'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad + Domestic</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="pd_preferred_buyer_location_3" name="pd_preferred_buyer_location" class="ace" value="domestic" <?php if($row->pd_preferred_buyer_location=='domestic'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Domestic Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="pd_preferred_buyer_location_4" name="pd_preferred_buyer_location" class="ace" value="my_city" <?php if($row->pd_preferred_buyer_location=='my_city'){ ?> checked="checked"<?php } ?>/><span class="lbl"> My City Only</span></label>
            
        </div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Minimum Order Quantity:</label>
		<div class="col-sm-9">
			<input name="pd_min_order_qty" id="pd_min_order_qty" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_min_order_qty; ?>" />
		</div>
	</div>
    	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Measurement Unit:</label>
		<div class="col-sm-9">
        <select id="pd_unit" name="pd_unit">
        	<option value="0"> - Select - </option>
        <?php                
			$unitsql=mysqli_query($con, "select * from measurement_unit where mu_status='1'");
			while($unitrow=mysqli_fetch_object($unitsql)){
		?>
			<option value="<?php echo $unitrow->mu_id;?>" <?php if($row->pd_unit==$unitrow->mu_id){ ?> selected="selected" <?php } ?> ><?php echo $unitrow->mu_name;?></option>
        <?php	}	?>
        </select>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Port of Dispatch:</label>
		<div class="col-sm-9">
			<input name="pd_pod" id="pd_pod" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_pod; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Production Capacity:</label>
		<div class="col-sm-9">
			<input name="pd_pn_capct" id="pd_pn_capct" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_pn_capct; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Delivary Time:</label>
		<div class="col-sm-9">
			<input name="pd_dlv_time" id="pd_dlv_time" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->pd_dlv_time; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Packing Details:</label>
		<div class="col-sm-8">
        	<textarea id="pd_pck_dets" name="pd_pck_dets" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 132px;" class="autosize-transition form-control"><?php echo $row->pd_pck_dets; ?></textarea>
		</div>
	</div>
    

	<?php if($row->pd_pdf_attach!=''){	?>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Attached PDF Brochure:</label>
		<div class="col-sm-9">
   		   	<?php
				$file = $row->pd_pdf_attach;
				 echo '<a href=lib/download.php?filename='.$file.' >'.$row->pd_pdf_attach.'</a>'; 
			?>
		</div>
	</div>
    <?php	}	?>  
        
	
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Product Image:</label>
		<div class="col-sm-9">
		<?php $imgarr = explode(',',$row->pd_image);?>

			<img src="../upload/myproduct/<?php if($row->pd_image!=''){ echo $imgarr[0]; }else{ echo "noimage.jpg";	} ?>" width="200px" height="232px"/>
		</div>
	</div>
    
    <div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
			<button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
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