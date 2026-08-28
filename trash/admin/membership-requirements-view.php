<?php 
ob_start();
session_start(); 
include "../common.php";

/*include "lib/pagination.php"; temp*/
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
		mysqli_query($con, "update membership_requirements set status= 0 where mp_req_id='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="membership-requirements-view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}


$p=new Pagination;
$page=$p->setpage(); /*temp*/

$al=new AdminLoginlist;
/********************delete record*********************/
	if($_GET['action']=="del"){
		echo $_GET['fid'];
		$al->deleterecord($_GET['fid']);
		header("location:membership-requirements-view.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from membership_requirements where status = '1' order by mp_req_id DESC");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "membership-requirements-view.php";

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
	
	//echo "<pre>"; print_r($_POST); echo "</pre>";
	//exit;
if(isset($_POST['btnDelete'])){
	foreach($_POST['cb'] as $id){
		$al->deleterecord($id);
	}
	header("location:membership-requirements-view.php");
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
					<a href="membership-requirements-view.php">Membership Requirements</a>
				</li>
				<li class="active">View Membership Requirement</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="con_view" id="con_view" method="post"> 
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
		<th><strong>Date</strong></th>
		<th><strong>Name</strong></th>
		<th><strong>Email</strong></th>
		<th><strong>Mobile</strong></th>
		<th><strong>Country</strong></th>
		<th style="text-align:center"><strong>Details</strong></th>
		<th style="text-align:center"><strong>Action</strong></th>
    </tr>
</thead>
<tbody>
<?php $j=0;
	$count=mysqli_num_rows($recObj);
	if($count >0)
	{
		while($row=mysqli_fetch_object($recObj)){
			if($row->opened == 0){?>
			<tr style="font-weight: bold;">
			<?php } else { ?>
			
		<tr>
			<?php } ?>
	        <td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->mp_req_id; ?>" /><span class="lbl"></span></label></td>
			<td><?php echo date('d/m/Y', strtotime($row->updated_date)); ?></td>
			<td><?php echo ucfirst($row->name); ?></td>
			<td><a href="mailto:<?php echo $row->email ?>"><?php echo $row->email ?></a></td>
			<td><?php echo $row->mobile; ?></td>
			<td><?php echo $row->country; ?></td>
			<td style="text-align:center"><a href="membership-requirement.php?fid=<?php echo $row->mp_req_id ?>" title="Details" style="float: left;"><img src="images/details.png"></a>
			<?php
			if($row->replied == 1){ ?>
			<i class="icon-reply icon-only"></i>
			<?php } ?>
			</td>
			<td style="text-align:center"><a href="<?php echo $al->deletelink($row->mp_req_id)?>" title="Delete" onclick="return confirm('Are you sure to delete the record?')"><img src="images/delete.jpg" border="0"></a></td>
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
			      null,null,null,null,null,
				  { "bSortable": false },{ "bSortable": false }
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