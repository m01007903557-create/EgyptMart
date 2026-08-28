<?php 
//ob_start();
//session_start(); 
include "../common.php";
/*include "lib/pagination.php";*/
check_user_login();

class Cmslist{
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
	/********************************function deleterecord($adid){
		mysqli_query($con, "delete from admin_login_details where admin_login_id='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			//$dellink="?action=del&ad-id=".$id;
		}
		else{
			$dellink="cms-view.php?".$_SERVER['QUERY_STRING']."&action=del&ad-id=".$id;
		}
		return $dellink;
	}*******************************/
}

$p=new Pagination;
$page=$p->setpage();

$al=new Cmslist;
/********************delete record*********************
	if($_GET['action']=="del"){
		echo $_GET['ad-id'];
		$al->deleterecord($_GET['ad-id']);
		header("location:welcome.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from cms where cms_status=1 order by cms_title");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "cms-view.php";

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
				<a>Manage CMS</a>
			</li>
			<li class="active">View CMS</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post"> 
<div class="row">
<div class="col-xs-12">
<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center">&nbsp;</th>
    <th><strong>Page Title</strong></th>
    <th><strong>Content</strong></th>
    <th style="text-align:center"><strong>Action</strong></th>
</thead>
<tbody>
    	<?php $j=0;
		$count=mysqli_num_rows($recObj);
		if($count >0)
		{
		while($row=mysqli_fetch_object($recObj)){?>
        <tr>
        <td class="center">&nbsp;</td>
        <td><?php echo stripslashes($row->cms_title); ?></td>
        <td><?php echo substr(strip_tags($row->cms_content),0,70); ?></td>
        <td align="center"><a href="cms-edit.php?pid=<?php echo $row->cms_id ?>" title="edit"><img alt="edit" src="images/edit.jpg" border="0"></a></td>
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
			      null,null,
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