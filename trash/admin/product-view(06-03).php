<?php 
include "../common.php";
////include "lib/pagination.php";
//check_user_login();

class AdminLoginlist{
	/*var $start="";
	var $limit="";*/
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
	/*function fetchRecord(){
		return mysqli_fetch_object($this->listview());
	}*/
	function numpage($rowPage){
		 return floor($this->totalrecord()/$rowPage);
	}
	function deleterecord($adid){
		global $con;
		mysqli_query($con, "delete from products where pd_id='".$adid."'");
	}
	function approve_record($aid){
		global $con;
		mysqli_query($con, "update products set pd_status='1' where pd_id='".$aid."'");
	}
	function disapprove_record($aid){
		global $con;
		mysqli_query($con, "update products set pd_status='2' where pd_id='".$aid."'");
	}
	function LeadingStatus($aid,$Leadstatus){
		global $con;
		mysqli_query($con, "update products set leadingprod_status='$Leadstatus' where pd_id='".$aid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&ad-id=".$id;
		}
		else{
			$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$id;
		}
		return $dellink;
	}
	function approve($id)
	{
		if($_SERVER['QUERY_STRING']==""){
			$plink="?action=appr&id=".$id;
		}
		else
		{
			$plink="product-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$id;
		}
		return $plink;
	}
	function disapprove($id)
	{
		if($_SERVER['QUERY_STRING']==""){
			$plink="?action=disappr&id=".$id;
		}
		else
		{
			$plink="product-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$id;
		}
		return $plink;
	}
}


$p=new Pagination;
$page=$p->setpage();

$al=new AdminLoginlist;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del"){
		$al->deleterecord($_GET['ad-id']);
		header("location:product-view.php");
		//header("location:product-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/
/********************approve record*********************/
	if(isset($_GET['action']) && $_GET['action']=="appr"){
		$al->approve_record($_GET['id']);
		$pid = $_GET['id'];
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
			$subject = "Product Approve from ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			$message = $suname."<br /><br />"."Your Product <b>".$spname."</b> is approved";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";
			//echo $to.'</br/>'.$message; exit;
			if(mail($to, $subject, $message, $headers)){
			//header("location:product-view.php");	
			header('Location:../product-email.php?pd_id='.$_GET['id']);	
				}

		//header("location:product-view.php");
		//header("location:product-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/
/********************disapprove record*********************/
	if(isset($_GET['action']) && $_GET['action']=="disappr"){
		$al->disapprove_record($_GET['id']);
		//header("location:product-view.php");
		header('Location:../product-email.php?pd_id='.$_GET['id']);	
		//header("location:product-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/
## for add and remove in/from Leading Product 
if(isset($_GET['action']) && $_GET['action']=="Leading")
{
	$al->LeadingStatus($_GET['id'],$_GET['leadingsts']);
	header("location:product-view.php");
}
$al->limit=$p->setlimit(10);
$al->setsql("select * from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id order by pd_date desc");

$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "product-view.php";

$pagestring ="?limit=".$limit."&page=";

$recObj=$al->listview();

$showitems=$al->start+1 ."-";
if(($al->start+$limit)<$totalitems){
	$showitems.=$al->start+$limit;
}
else{
	$showitems.=$totalitems;
}
	$showitems.= " of ". $al->totalrecord()." items";
	//echo $_SERVER['QUERY_STRING'];
	if(isset($_POST['btnDelete'])){
		foreach($_POST['cb'] as $id){
			$al->deleterecord($id);
		}
		header("location:product-view.php");
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
					<a href="product-view.php">Manage Products</a>
				</li>
				<li class="active">Product View</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post">
<div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
 </div>
 <div class="table-responsive">

<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace" ><span class="lbl"></span></label></th>
    <th align="center"><strong>Date</strong></th>
    <th><strong>Image</strong></th>
    <th><strong>Title</strong></th>
    <th><strong>Category</strong></th>
    <th><strong>Price</strong></th>
	<th style="text-align:center"><strong>Posted by</strong></th>
    <th style="text-align:center"><strong>Membership type</strong></th>
	<th>&nbsp;</th>	
    <th style="text-align:center"><strong>Status</strong></th>
    <th style="text-align:center"><strong>Action</strong></th>
	
    </tr>
</thead>

<tbody>
<?php
	$j=0;
   $count=mysqli_num_rows($recObj);
   if($count >0)
   {			
		while($row=mysqli_fetch_object($recObj))
		{
			if($row->leadingprod_status==0){
			   $leading = 1; 
			   $leadinglabel = "Add in 
			   Leading Product";				
			}else{ $leading = 0; $leadinglabel = "Remove From 
			   Leading Product"; }
           $leadingprodurl = $plink="product-view.php?action=Leading&leadingsts=".$leading."&id=".$row->pd_id;
		  $img=$row->pd_image;
                  $makearr=explode(',', $img);
                  
                  if(count($row->pd_imagelogo)){

                     $logoarr = explode(',',$row->pd_imagelogo);
                   
                    }
                    
		?>
        <tr>
        	<td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->pd_id; ?>" /><span class="lbl"></span></label></td>
            <td nowrap><?php echo date('d M, y',strtotime($row->pd_date)); ?></td>
           
            <td>
           <div style=" position: relative;">
            <a href="#"> <img src="../upload/myproduct/<?php echo $logoarr[0];?>" style="position: absolute; top:48px; width:30px;height:29px;" /> </a>
            <a href="#"> <img src="../upload/myproduct/<?php if($row->pd_image!=''){ echo $makearr[0]; }else{ echo 'noimage.jpg'; } ?>" style="width:80px; height:78px;"/></a>
            
            </div></td>
            <td><?php echo ucwords(stripslashes($row->pd_title)); ?></td>
            <td><?php echo ucwords(stripslashes($row->pc_name)); ?></td>
            <td><?php echo get_product_detail($row->pd_id,'pd_currency')." ".$row->pd_fob_price; ?></td>
			<td><?php echo $row->bnsprof_compname; ?></td>
			<td><?php echo $row->mst_name; ?></td>
			<td><a href="product-details.php?token=<?php echo rand(1000,9999).md5($row->pd_id); ?>"><img src="images/details.png" /></a></td>
            <td class="center" style="text-align:center">
            <?php	if($row->pd_status=='0'){	?>
            <a href="<?php echo $al->approve($row->pd_id); ?>" onclick="return confirm('Are you sure to approve the Product?')" title="Approve">
            <img alt="Approve" src="images/active.jpg"></a>&nbsp;
            <a href="<?php echo $al->disapprove($row->pd_id); ?>" onclick="return confirm('Are you sure to dissapprove the Product?')" title="Disapprove">
            <img alt="Disapprove" src="images/reject.png" width="19" height="19"  border="0"></a>
            
            <?php	}elseif($row->pd_status=='1'){	?>
            <font color="#009933" weight='800'>Approved</font>
            <?php }elseif($row->pd_status=='2'){	?>
            <font color="#CC0000" weight='800'>Rejected</font>
            <?php }?>
            </td>
            <td class="action" style="text-align:center" nowrap>
			<!--<a href="<?php //echo $leadingprodurl;?>" title="<?php //echo $leadinglabel;?>"><?php //echo nl2br($leadinglabel);?></a>&nbsp;&nbsp;-->
            <a href="product-edit.php?fid=<?php echo $row->pd_id; ?>" title="Edit"><img src="images/edit.jpg"/></a>
            <a href="<?php echo $al->deletelink($row->pd_id); ?>" onclick="return confirm('Are you sure to Delete the Product?')" title="Delete"><img src="images/delete.jpg"/></a></td>
        </tr>
<?php $j++; } }?>
</tbody>
</table>
</div></div></div></form>
	<br clear="all"/>
 </div>

 </div>
 </div>
 <br clear="all" />

 </div>  
  <?php include "includes/footer.php" ?>
<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>
 <script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<script src="assets/js/jquery.dataTables.min.js"></script>
		<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!-- inline scripts related to this page -->

		<script type="text/javascript">
			jQuery(function($) {
				var oTable1 = $('#sample-table-2').dataTable( {
				"aoColumns": [
			      { "bSortable": false },
				  null,
				  { "bSortable": false },
					null,null,null,null,null,
				  { "bSortable": false },
				  { "bSortable": false },
				  { "bSortable": false }
				] } );
				
				
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
			})
		</script>
</body>
</html>