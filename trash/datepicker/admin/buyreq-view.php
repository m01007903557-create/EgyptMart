<?php 
include "../common.php";
////include "lib/pagination.php";
check_user_login();

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
		mysqli_query($con, "delete from buy_requirement where br_id='".$adid."'");
	}
	function approve_record($aid){
		global $con;
		mysqli_query($con, "update buy_requirement set br_approval_status='1' where br_id='".$aid."'");
	}
	function disapprove_record($aid){
		global $con;
		mysqli_query($con, "update buy_requirement set br_approval_status='2' where br_id='".$aid."'");
        
        
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
			$plink="buyreq-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$id;
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
			$plink="buyreq-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$id;
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
		header("location:buyreq-view.php");
		//header("location:buyreq-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/
/********************approve record*********************/
	if(isset($_GET['action']) && $_GET['action']=="appr"){
		$al->approve_record($_GET['id']);
                
 $pid = $_GET['id'];
                $get_user = "SELECT * FROM user u LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid WHERE u.usr_id = (SELECT br_u_id FROM buy_requirement WHERE br_id = '$pid')";
                $res_user = $con->query($get_user);
		$suser = $res_user->fetch_object();
		
		$contact_details = '<strong>'.$suser->bnsprof_compname.'</strong><br/>'.$suser->bnsprof_address1.'<br/>Mobile/Cell Phone: '.$suser->mobile1.'<br/>Email: '.$suser->email;
		//echo $contact_details;
		//echo "<pre>"; print_r($suser);
		$suname = $suser->name_prefix." ".$suser->fname." ".$suser->lname;
		
		$to = $suser->email;
                
		//$to ='programmer5.techybirds@gmail.com';

		$get_product = "SELECT * FROM buy_requirement WHERE br_id = '$pid'";
		$res_product = $con->query($get_product);
		$sproduct = $res_product->fetch_object();
		$product_img='https://www.arabyos.com/upload/buy_requirement/thumb/no-image.png';
		if($sproduct->br_pic!=''){
		$product_img='https://www.arabyos.com/upload/buy_requirement/thumb/'.$sproduct->br_pic;
		}
		

		//echo "<pre>"; print_r($sproduct); exit;
		$product_title = $spname = $sproduct->br_pd_name;
		$product_moq = $sproduct->br_estimate_qty;
		$unitsql=mysqli_query($con, "select * from measurement_unit where mu_status='1' AND mu_id = ".$sproduct->br_estimate_qty_unit);
		while($unitrow=mysqli_fetch_object($unitsql)){
			$product_type = $unitrow->mu_name;
		}
		$product_price=$sproduct->br_apprx_order_value.' '.$sproduct->br_apprx_order_currency;
		/*Put Your Email Adress Here*/
		        $subject = "Latest Buy Lead Approval from ".get_page_settings(4);
			$from_name = get_page_settings(4);	

			$from_email = get_adminemail();
			$message = $suname."<br /><br />"."Your Buy Lead <b>".$spname."</b> is approved";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";
			$usr_email = $to;
			//echo $to.'</br/>'.$message; exit;
			include "email/admin_buylead_approve.php";
			//echo $message1;
			//exit;
			//$to = 'josyaprabu@gmail.com';
		//	echo $message2;die;
			if(mail($to, $subject, $message2, $headers)){
				//exit;
			//header("location:buyreq-view.php");	
			header('Location:../buylead-alert-mail.php?br_id='.$_GET['id']);
		//header("location:buyreq-view.php");
		//header("location:buyreq-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
		
		}
/*************************************************/
/********************disapprove record*********************/
	if(isset($_GET['action']) && $_GET['action']=="disappr"){
		$al->disapprove_record($_GET['id']);
		header("location:buyreq-view.php");
		//header("location:buyreq-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/

$al->limit=$p->setlimit(10);
$params = $_POST;
$al->setsql("select * from buy_requirement br JOIN measurement_unit mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id WHERE br_status='1' order by br_updated_date desc") ;




$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "buyreq-view.php";

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
		header("location:buyreq-view.php");
	}
	
?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' ,'fixed')}catch(e){}
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
				<a>Manage Buy Requirement</a>
			</li>
			<li class="active">View Buy Requirement</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post">
<div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
 <p style="display: inline-block;float: right;">Go to Page No : <input type="number" name="page_no" id="page_no" class="page_no" min="1" /></p>
 </div>
 <div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace" ><span class="lbl"></span></label></th>
        <th><strong>Image</strong></th>
        <th><strong>Product/Service</strong></th>
        <th><strong>Requirement in Detail</strong></th>

	<th><strong>Posted By</strong></th>
        <th><strong>Country</strong></th>
	<th style="text-align:center"><strong>Membership type</strong></th>
        <th style="text-align:center"><strong>Date</strong></th>
		<th style="text-align:center;"><strong>Status</strong></th>
        <th style="text-align:center"><strong>Action</strong></th>
    </tr>
</thead>


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
				"ajax":{
					url :"buyreq-view-response.php", // json datasource
					type: "post",  // type of method  , by default would be get
					error: function(){  // error handling code
					 $("#sample-table-2").css("display","none");
					}
				  },
				"lengthMenu": [ 100,200,500 ],
				 "bProcessing": true,
				 "serverSide": true,
				"aoColumns": [
			      { "bSortable": false },{ "bSortable": false },
			      null, null,null,null,null,null,
				  { "bSortable": false },{ "bSortable": false }
				],
				"serverParams": function ( aoData ) {

				},
				"drawCallback": function( settings ) {
					$("#overlay").hide();
				}
				} );
				$("#page_no").on('keyup',function(){
					if($(this).val()!=''){
						oTable1.fnPageChange(parseInt($(this).val())-1);
					}
					else{
						oTable1.fnPageChange(0);
					}
					
				});
				
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