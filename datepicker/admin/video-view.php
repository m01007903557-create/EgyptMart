<?php 
//ob_start();
//session_start(); 
include "../common.php";
//include "lib/pagination.php";

check_user_login();
class listCat{
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
		//mysqli_query($con, "delete from news_details where news_id='".$adid."'");
		mysqli_query($con, "update company_video set cv_status = '0' where cv_id='".$adid."'");
	}
	function Frontstatus($adid,$frontstatus)
	{
		global $con;
		mysqli_query($con, "update company_video set showatfront = '$frontstatus' where cv_id='".$adid."'");
	}
	function deletelink($id,$front=""){
		if($_SERVER['QUERY_STRING']=="")
		{
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="video-view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}

$p=new Pagination;
$page=$p->setpage();

$al=new listCat;
/********************delete record*********************/
	if($_GET['action']=="del")
	{
		echo $_GET['fid'];
		$al->deleterecord($_GET['fid']);
		header("location:video-view.php");
		//header("location:video-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
    }elseif($_GET['action']=="Showfront")
	{
		$al->Frontstatus($_GET['fid'],$_GET['showatfront']);
		header("location:video-view.php");
	}
/***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from company_video,business_profile where cv_bnsprof_id=bnsprof_id and cv_status = '1'");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "video-view.php";

$pagestring ="?limit=".$limit."&page=";

$recObj=$al->listview();
$count=mysqli_num_rows($recObj);

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
		mysqli_query($con, "update company_video set cv_status = 0 where cv_id='".$cb."'");		
	}
	header("Location:video-view.php");
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
					<a href="video-view.php">Manage Company Video</a>
				</li>
				<li class="active">View Video</li>
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
    <th style="text-align:center"><strong>Video</strong></th>
    <th style="text-align:center"><strong>Company Name</strong></th>
    <th><strong>Action</strong></th>
    </tr>
</thead>

<tbody>
<?php $j=0;
$count=mysqli_num_rows($recObj);
	if($count >0)
	{
		while($row=mysqli_fetch_object($recObj))
		{
			if($row->showatfront==0)
			{
				$showatfront = 1;$label="Show at Home";
			}else{ $showatfront = 0;$label="Remove from Home";}
			
			$showfronturl="video-view.php?action=Showfront&showatfront=".$showatfront."&fid=".$row->cv_id;
		?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
	        <td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->cv_id; ?>" /><span class="lbl"></span></label></td>
            <td style="text-align:center"><?php echo $row->cv_video_link; ?></td>
            <td style="text-align:center"><?php echo $row->bnsprof_compname; ?></td>
            <td style="text-align:center">
			<a href="<?php echo $showfronturl;?>"><?php echo $label;?></a>&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="<?php echo $al->deletelink($row->cv_id)?>" title="Delete" onclick="return confirm('Are you sure to delete the plan?')"><img alt="Delete" src="images/delete.jpg" border="0"></a>
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
					{ "bSortable": false },
			      null,	
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