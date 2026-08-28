<?php 
ob_start();
//session_start(); 
include "../common.php";

//check_user_login();
class listGig{
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
		$sql=$this->sqlList." limit ".$this->start.",".$this->limit;
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
		mysqli_query($con, "update gig set g_status = '0' where g_id='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="active_gig-view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}
$p=new Pagination;
$page=$p->setpage();

$al=new listGig;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del"){
		//echo $_GET['ad-id'];
		$al->deleterecord($_GET['fid']);
		header("location:active_gig-view.php");
		}
/*************************************************/

$al->limit=$p->setlimit(10);
$al->setsql("select * from gig,subcategory,category where g_scat_id=scat_id and scat_cat_id=cat_id and g_status='1' order by g_id");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "active_gig-view.php";

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
		header("location:active_gig-view.php");
	}	
?>

<?php include "includes/admin-top.php" ?>
<script type="text/javascript">
function changeStatus(id,stat)
{
//	alert(id+' '+stat);
	$.post("change_gig_status.php",{id:id,stat:stat},function(data){		alert(data);	});
			//  location.reload();
}
</script>
 <div class="control_Panel">
<?php include "includes/admin-left-con.php" ?>
	<div id="content-container">
		<div id="content">
<form name="myform" id="myform" method="post"> 
<h2>&rsaquo;&nbsp;&nbsp;Manage Gigs&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Active-Gig List</h2>
<div id="whatsNew-grid" class="grid-view">
<table><tr><td>
<input name="btnDelete" type="submit" value="Delete" class="delete-btn" onClick="return confirm('Are you sure to delete the record?')" />
</td>
<TD>
<input type="button" class="delete-btn" onClick="window.location ='gig-add.php' " value="Add Gig">
</TD>
<td><?php echo $showitems ?></td>
<td align="right"><div class="summary">
<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
<select name="limit" id="limit" onChange="javascript:window.location.href='active_gig-view.php?page=<?php echo $page ?>&amp;limit='+this.value;">
<?php 
for($i=10; $i<=40; $i=$i+10){ 
if($i==$limit){ ?>
<option value="<?php echo $i ?>" selected="selected" ><?php echo $i ?></option>
<?php } else { ?>
<option value="<?php echo $i ?>" ><?php echo $i ?></option>
<?php } } ?>
</select></div>results per page.</div></td>
<td></td>
</tr></table>
<table class="items">
<thead>
<tr>
    <th class="checkbox" align="left" style="width:40px;"><input name="check_all" value="yes" id="check_all" type="checkbox" onClick="return checkedAll();"></th>
    <th class="usr-name" style="width:180px;"><strong>Title</strong></th>
    <th class="usr-name" style="width:250px;"><strong>Category</strong></th>
    <th class="usr-name" style="width:400px;"><strong>Description</strong></th>
    <th class="action"><strong>Action</strong></th>
</thead>
<tbody>
    	<?php $j=1;
		if(mysqli_num_rows($recObj)>0){
		while($row=mysqli_fetch_object($recObj)){
               
		?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
        	<td class="checkbox"><input name="cb[]" type="checkbox" value="<?php echo $row->g_id; ?>" /></td>
            <td class="usr-name" style="width:180px;"><?php echo $row->g_title; ?></td>
            <td class="usr-name" style="width:250px;"><?php  echo stripslashes($row->scat_name)." (".stripslashes($row->cat_name).")"; ?></td>
            <td class="usr-name" style="width:400px;"><?php  echo $row->g_description;?></td>
            <td class="action">
            <a href="gig-details.php?token=<?php echo md5($row->g_id); ?>">Details</a>
			<!--<select onchange="changeStatus('',this.value);">
	            <option value="sel">Select</option>
            	<option value="2">Active</option>
            	<option value="4">Suspend</option>
                <option value="3">Require Modification</option>
                <option value="5">Deny</option>
                <option value="0">Delete</option>
            </select>-->
            </td>
        </tr>
        <?php $j++; }
            }
            else
            {    ?>
    <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> align="center" >
        <td colspan="4" align="center" style="color: #D00">No Record.</td>
    </tr>
    <?php    }     ?>
</tbody>
</table>
<div class="pager"><?php echo $p->getPaginationString($page,$totalitems,$limit,$adjacents,$targetpage,$pagestring)?></div>
</div>  
      <br clear="all"/>
 </div>
</form>
 </div>
 </div>
 <br clear="all" />
 </div>
  <?php include "includes/footer.php" ?>
</body>
</html>