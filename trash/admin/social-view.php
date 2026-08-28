<?php 
ob_start();
session_start(); 
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
		mysqli_query($con, "update social_media_login_info_arabyos set al_status = '0' where al_id='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="social-view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}

$p=new Pagination;
$page=$p->setpage();

$al=new listCat;
/********************delete record*********************/
	if($_GET['action']=="del"){
		echo $_GET['fid'];
		$al->deleterecord($_GET['fid']);
		header("location:social-view.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from social_media_login_info_arabyos where smli_status = '1'");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "social-view.php";

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
		mysqli_query($con, "update airlines set al_status = 0 where al_id='".$cb."'");		
	}
	header("location:social-view.php");
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
				<a href="social-view.php">Manage Social Media</a>
			</li>
			<li class="active">View Social Media</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="test_view" id="test_view" method="post"> 
<div class="row">
<div class="col-xs-12">
<!--<div class="table-header">
<button class="btn btn-xs btn-danger" type="submit" onclick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
&nbsp;&nbsp;&nbsp;&nbsp;
</div>-->

<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<tbody>
	   <?php $j=0;
	   $count=mysqli_num_rows($recObj);
	   if($count >0)
	   {		  
	   		while($row=mysqli_fetch_object($recObj)){
		   	?>
    	<tr>
        	<td class="center">&nbsp;</td>
			                     
            <td>
				<?php 
                    $a_c=explode("-",$row->smli_field);
//                    echo ucfirst($a_c[0])."&nbsp;".ucfirst($a_c[1]);
					foreach($a_c as $sf)
					{
						echo ucfirst($sf)."&nbsp;";
					}
				?>
            </td>
			<td>
            <?php if($row->smli_field =='logo') { ?>
            <img src="../sitelogo/<?php echo $row->smli_value; ?>"  />
            <?php } else {?>
			<?php echo stripslashes(substr($row->smli_value,0,55)); if(strlen($row->smli_value)>60) { echo "...&nbsp;&nbsp;"; ?><a href="social-details.php?sid=<?php echo $row->smli_id; ?>">More</a><?php } }?>
            </td>
            <td>
			<a href="social-edit.php?sid=<?php echo $row->smli_id  ?>" title="edit" style="text-decoration:none;">Change<!--<img alt="edit" src="images/edit.jpg" border="0">--></a>
			</td>
</tr>
        <?php $j++;  } } else { ?>
        <table class="items">
		<thead>
         <tr class="row-clr"><td style="text-align:center;" class="action"><font color="#EE0000">No Records.</font></tr>
         </thead></table>
        <?php } ?>
</tbody>
</table>
</div></div></div></form>
	<br clear="all"/>
 </div>

 </div>
 </div>
 <br clear="all" />

 </div>   <?php include "includes/footer.php" ?></div>
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

		
</body>
</html>