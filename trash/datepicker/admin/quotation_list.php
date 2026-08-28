<?php 
ob_start();
//session_start(); 
include "../common.php";

//check_user_login();
class quotationlist{
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
	function numpage($rowPage){
		 return floor($this->totalrecord()/$rowPage);
	}
	function deleterecord($adid){
		global $con;
		mysqli_query($con, "delete from quotation_request where qr_id ='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&clid=".$id;
		}
		else{
			$dellink="quotation_list.php?".$_SERVER['QUERY_STRING']."&action=del&clid=".$id;
		}
		return $dellink;
	}
          
        function paidlink($id){
		if($_SERVER['QUERY_STRING']==""){
			$plink="?action=paid&did=".$id;
		}
		else{
			$plink="quotation_list.php?".$_SERVER['QUERY_STRING']."&action=paid&did=".$id;
		}
		return $plink;
	}
}
$p=new Pagination;
$page=$p->setpage();

$al=new quotationlist;
/********************delete record*********************/
	if($_GET['action']=="del"){
		//echo $_GET['clid'];
		$al->deleterecord($_GET['clid']);
		header("location:quotation_list.php");
		}
/*************************************************/

	if(isset($_GET['action']) && $_GET['action']=="paid"){
		echo $_GET['did'];
		$al->changereseller($_GET['did']);
		header("location:quotation_list.php");
		}                
                
$al->limit=$p->setlimit(10);
$al->setsql("select * from quotation_request order by qr_updated_date desc");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "quotation_list.php";

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
		header("location:quotation_list.php");
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Administrative Panel</title>
<link rel="shortcut icon" href="" type="image/x-icon">
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<script type="text/javascript">

function Check()
{
var chk=document.myform.cb;
	alert(chk);
if(document.myform.check_all.checked==true)
 {
 for (i = 0; i < chk.length; i++)
 chk[i].checked = true ;
 }
else
 {
 for (i = 0; i < chk.length; i++)
 chk[i].checked = false ;
 }
}
</script>

<script type="text/javascript">
checked=false;
function checkedAll () {
	var aa= document.getElementById('myform');
	 if (checked == false)
          {
           checked = true
          }
        else
          {
          checked = false
          }
	for (var i =0; i < aa.elements.length; i++) 
	{
	 aa.elements[i].checked = checked;
	}
      }
</script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div class="main">
<?php include "includes/admin-top.php" ?>
 <div class="control_Panel">
<?php include "includes/admin-left-con.php" ?>
	<div id="content-container">
		<div id="content">
        
        
<form name="myform" id="myform" method="post"> 
<h2>&rsaquo;&nbsp;&nbsp;Manage Quotation&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Quotation List</h2>
<div id="whatsNew-grid" class="grid-view">
<table><tr><td>
<input name="btnDelete" type="submit" value="Delete" class="delete-btn" onclick="return confirm('Are you sure to delete the record?')" />
</td>
<td><?php echo $showitems ?></td>
<td align="right"><div class="summary">
<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
<select name="limit" id="limit" onchange="javascript:window.location.href='quotation_list.php?page=<?php echo $page ?>&amp;limit='+this.value;">
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
    <th class="usr-name" style="width:140px;"><strong>Name</strong></th>
    <th class="usr-name" style="width:150px;"><strong>Email</strong></th>
    <th class="usr-name" style="width:90px;"><strong>Contact Number</strong></th>
    <th class="usr-name" style="width:80px;"><strong>Detail</strong></th>
    <th class="usr-name" style="width:80px;"><strong>Date</strong></th>
    <th class="action" style="width:90px;"><strong>Action</strong></th>
</thead>

<tbody>
    	<?php $j=1;
		while($row=mysqli_fetch_object($recObj)){       
		?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> > 
        	<td class="checkbox"><input name="cb[]" type="checkbox" value="<?php echo $row->qr_id; ?>" /></td>
        
       <td class="usr-name" style="width:140px; text-align:center;">
	        <?php  echo ucwords($row->qr_name); ?>
	   </td>
       <td class="usr-name" style="width:110px; text-align:center;">
	        <?php  echo $row->qr_email; ?>
	   </td>   
       <td class="usr-name" style="width:90px; text-align:center;">
	       <?php echo $row->qr_contactnumber; ?>
	   </td>    
       <td class="usr-name" style="width:80px; text-align:center">
	       <a class='ajax' href="quotation_details.php?token=<?php echo rand(1000,9999).md5($row->qr_id); ?>" ><strong>View</strong></a>
	   </td> 
       <td class="usr-name" style="width:80px; text-align:center">
	       <?php echo date('M d, Y', strtotime($row->qr_updated_date)); ?>
	   </td>
       <td class="action" style="text-align:center;">
          <a href="<?php echo $al->deletelink($row->qr_id)?>" title="delete" onclick="return confirm('Are you sure to delete the record?')" >
          <img alt="delete" src="images/delete.jpg" border="0" >
          </a>
       </td>
       </tr>
        <?php $j++; } ?>
</tbody>
</table>
<div class="pager"><?php echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring)?></div>
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