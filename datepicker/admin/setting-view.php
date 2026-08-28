<?php 
ob_start();
session_start(); 
include "../common.php";
//include "lib/pagination.php";
check_user_login();
class listCmp{
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
		mysqli_query($con, "update company_admin set ca_status='0' where ca_id='".$adid."'");		
	}
	
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&aid=".$id;
		}
		else{
			$dellink="setting-view.php?".$_SERVER['QUERY_STRING']."&action=del&aid=".$id;
		}
		return $dellink;
	}		
}

$p=new Pagination;
$page=$p->setpage();

$al=new listCmp;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del")
	{
		//echo $_GET['aid'];		
		$al->deleterecord($_GET['aid']);
		header("location:setting-view.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
	}		
/***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from site_settings where st_status = '1'");

$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "setting-view.php";

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
	
if(isset($_POST['btnDelete']))
{ 
	foreach($_POST['cb'] as $cb)
	{				
		$al->deleterecord($cb);			
		mysqli_query($con, "delete from company_admin set where ca_id='".$cb."'");		
	}
	header("location:company-view.php");
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
function changeStatus(id,val)
{
	$.post("editSiteSettings.php", {id:id,val:val},	function(data){	 });
}
function updSettings(id,val)
{
	$.post("updSiteSettings.php", {id:id,val:val},	function(data){	 });
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
				<a href="setting-view.php">Site Settings</a>
			</li>
			<li class="active">View Site Settings</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post"> 
<div class="row">
<div class="col-xs-12">
<!--<div class="table-header">
<button class="btn btn-xs btn-danger" type="submit" onclick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
&nbsp;&nbsp;&nbsp;&nbsp;
</div>-->

<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<!--<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace"><span class="lbl"></span></label></th>
	<th>Company Name</th>
    <th>Contact Person</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Reg. Date</th>
    <th>View</th>
    <th>Visit Company</th>
    <th class="action">Action</th>
</thead>-->
<tbody>
    	<?php $j=0;
		$count=mysqli_num_rows($recObj);
		if($count >0)
		{
		while($row=mysqli_fetch_object($recObj)){?>
        <tr>
        	<td class="center">&nbsp;</td>
            <td><?php
            	$a_c=explode("-",$row->st_field);
				for($i=0;$i<count($a_c);$i++){
                	echo ucfirst($a_c[$i])."&nbsp;";
				} ?>
			</td>
            <td>
            <?php if($row->st_field =='logo' || $row->st_field =='small-logo' || $row->st_field =='footer-logo' || $row->st_field=='left-logo' || $row->st_field=='unit-logo-footer') { ?>
            <img src="../sitelogo/<?php echo $row->st_value; ?>"  />
            <?php }	else if($row->st_field =='email-verification'){	
			?>
            <label><input name="switch-field-1" class="ace ace-switch ace-switch-7" type="checkbox"<?php if($row->st_value=='1'){ ?> checked="checked"<?php } ?> onchange="changeStatus('<?php echo $row->st_id; ?>','<?php echo $row->st_value; ?>');"><span class="lbl"></span></label>
            <?php
			}
			else if($row->st_field =='category-order'){	?>
				<label><input type="radio" id="" name="st_value" class="ace" value="alphabetic" <?php if($row->st_value=='alphabetic'){ ?> checked="checked"<?php } ?> onchange="updSettings('<?php echo $row->st_id; ?>','alphabetic');"/><span class="lbl"> Alphabetic</span></label>
			<label><input type="radio" id="" name="st_value" class="ace" value="manual" <?php if($row->st_value=='manual'){ ?> checked="checked"<?php } ?> onchange="updSettings('<?php echo $row->st_id; ?>','manual');"/><span class="lbl"> Manual</span></label>
				
			<?php
            } else {?>
			<?php echo stripslashes(substr($row->st_value,0,55)); if(strlen($row->st_value)>60) { echo "...&nbsp;&nbsp;"; ?><a href="setting-details.php?sid=<?php echo $row->st_id; ?>">More</a><?php } }?>
            </td>
            <td class="center">
            <?php if($row->st_field !='email-verification' && $row->st_field !='category-order'){	?>
				<a href="setting-edit.php?sid=<?php echo $row->st_id;  ?>" title="Edit" style="text-decoration:none;"><img alt="Edit" src="images/edit.jpg" border="0"></a>
            <?php } ?>
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