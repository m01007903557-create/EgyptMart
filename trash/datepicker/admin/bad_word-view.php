<?php 
ob_start();
session_start(); 
include "../common.php";


check_user_login();
class categorylist{
	
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
	function deleterecord($did){
		global $con;
		mysqli_query($con, "delete from bad_word where bd_id='".$did."'");
	}
	function deletelink($id){
		
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&pid=".$id;
		}
		else{
			$dellink="bad_word-view.php?".$_SERVER['QUERY_STRING']."&action=del&pid=".$id;
		}
		return $dellink;
	}
}

$p=new Pagination;
$page=$p->setpage();

$al=new categorylist;
/********************delete record*********************/
	if($_GET['action']=="del"){
		echo $_GET['pid'];
		$al->deleterecord($_GET['pid']);
		header("location:bad_word-view.php");
		}
/***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("SELECT * from bad_word order by bd_id desc");		
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "bad_word-view.php";

$pagestring ="?limit=".$limit."&page=";

$recObj=$al->listview();

$showitems=$al->start+1 ." - ";
if(($al->start+$limit)<$totalitems){
	$showitems.=$al->start+$limit;
}
else{
	$showitems.=$totalitems;
}
	$showitems.= " of ". $al->totalrecord()." items";
	//echo $_SERVER['QUERY_STRING'];
	
if(isset($_POST['btnDelete']))
{ 
	foreach($_POST['cb'] as $cb)
	{		
		//mysqli_query($con, "update project_management set status = 0 where project_id='".$cb."'");	
		mysqli_query($con, "delete from faq_categories_arabyos where fc_id='".$cb."'");
	}
	header("location:bad_word-view.php");
}
?>

<?php include "includes/admin-top.php" ?>
  <script type="text/javascript">
		try{ace.settings.check('main-container' , 'fixed')}catch(e){}
	</script>

<div class="main-container" id="main-container">
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
					<a>Manage Bad Words</a>
				</li>
				<li class="active">Bad Word List</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
	<form name="test_view" id="test_view" method="post"> 
        <div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
 <button type="button" class="btn btn-xs btn-success" onClick="window.location='bad_word-add.php' "><i class="icon-plus-sign"></i> Add Bad Word</button>
 </div>
 <div class="table-responsive">
   

<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace" ><span class="lbl"></span></label></th>
    <th style="text-align:center"><strong>#</strong></th>
    <th><strong>Words</strong></th>
    
    <th style="text-align:center"><strong>Action</strong></th>
    </tr>
			
	</thead>
        
      <tbody>
    	<?php $j=0;
		$count=mysqli_num_rows($recObj);
		if($count >0)

		{
		while($row=mysqli_fetch_object($recObj)){	
		   	?>
        <tr>
        	<td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->bd_id; ?>" /><span class="lbl"></span></label></td>
            <td style="text-align:center"><?php echo "#".$row->bd_id; ?></td>         
            <td><?php echo ucfirst($row->bd_word); ?></td>
            <td style="text-align:center"><a href="bad_word-edit.php?cid=<?php echo $row->bd_id; ?>" title="edit" class="btn btn-xs btn-info"><i class="icon-edit bigger-120"></i></a>
            <a href="<?php echo $al->deletelink($row->bd_id)?>" title="delete" onClick="return confirm('Are you sure to delete the record?')" class="btn btn-xs btn-danger"><i class="icon-trash bigger-120"></i></a></td>
             </tr>
        <?php $j++; } }?>
</tbody>
</table>
    
</div>



</div></div>
 </form>
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
			      null, null, 
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