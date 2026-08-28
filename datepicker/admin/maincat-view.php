<?php 
include "../common.php";

check_user_login();
class productlist{
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
	function numpage($rowPage){
		 return floor($this->totalrecord()/$rowPage);
	}
	function deleterecord($adid){
		global $con;
		mysqli_query($con, "delete from product_category where pc_id='".$adid."'");
		mysqli_query($con, "delete from product_category where pc_parent_id='".$adid."'");
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
        
       
        
        function paidlink($id){
		if($_SERVER['QUERY_STRING']==""){
			$plink="?action=paid&did=".$id;
		}
		else{
			$plink="maincat-view.php?".$_SERVER['QUERY_STRING']."&action=paid&did=".$id;
		}
		return $plink;
	}
}
$p=new Pagination;
$page=$p->setpage();

$al=new productlist;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del"){
		//echo $_GET['ad-id'];
		$al->deleterecord($_GET['ad-id']);
		header("location:maincat-view.php");
		}
/*************************************************/

	if(isset($_GET['action']) && $_GET['action']=="paid"){
		echo $_GET['did'];
		$al->changereseller($_GET['did']);
		header("location:maincat-view.php");
		}                
                
$al->limit=$p->setlimit(10);
$al->setsql("select * from product_category where pc_parent_id = '0' order by pc_id asc");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "maincat-view.php";

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
		header("location:maincat-view.php");
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
<script type="text/javascript">
function updOrder(id)
{
	var pc_order=document.getElementById('inp_order_'+id);
	if(pc_order.value=='' || isNaN(pc_order.value) || pc_order.value<0)
	{
		alert('Please enter a valid positive number.')	
	}
	else
	{
		$.post("ajax-file/updCategoryOrder.php",{id:id,pc_order:pc_order.value},function(data){
			alert("Record updated successfully.");
		});
	}
}
</script>
		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
					<a href="welcome.php">Home</a>
				</li>
				<li>
					<a href="maincat-view.php">Category Management</a>
				</li>
				<li class="active">Category View</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post"> 
<div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
 <button type="button" class="btn btn-xs btn-success" onClick="window.location='maincat-add.php' "><i class="icon-pencil align-top bigger-120"></i>Add Category</button>
 </div>
 <div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace" ><span class="lbl"></span></label></th>
    <th align="center"><strong>Image</strong></th>
    <th><strong>Category Name</strong></th>
    <th style="text-align:center"><strong>Order</strong></th>
    <th style="text-align:center"><strong>Action</strong></th>
</thead>
<tbody>
<?php $j=0;
   $count=mysqli_num_rows($recObj);
   if($count >0)
   {			
		while($row=mysqli_fetch_object($recObj)){ 
               
		?>
        <tr>
        	<td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->pc_id; ?>" /><span class="lbl"></span></label></td>
			<td><img src="../upload/category/<?php  echo $row->pc_image;?>" width="70" height="70"/></td>
			<td><?php  echo $row->pc_name;?></td>
            <td style="text-align:center">
            	<input type="text" id="inp_order_<?php echo $row->pc_id; ?>" style="width:40px;" value="<?php echo $row->pc_order; ?>" />
                <button type="button" onClick="updOrder('<?php echo $row->pc_id; ?>');" class="btn btn-xs btn-info"><i class="icon-ok"></i></button>
			</td>
	       <td align="center">
            <a href="maincat-edit.php?token=<?php echo rand(1000,9999).md5($row->pc_id);?>" title="Edit"><img alt="edit" src="images/edit.jpg"></a>
            <a href="<?php echo $al->deletelink($row->pc_id)?>" title="delete" onClick="return confirm('Are you sure to delete the record?')"><img alt="delete" src="images/delete.jpg" border="0"></a>
       </td>
       
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
			       { "bSortable": false },null,
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