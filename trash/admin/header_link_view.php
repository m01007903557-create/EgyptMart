<?php 
ob_start();
//session_start(); 
include "../common.php";

//check_user_login();
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
		$sql=$this->sqlList." limit ".$this->start.",".$this->limit;
		$res=mysqli_query($con, $sql);
		return $res;
	}
	function numpage($rowPage){
		 return floor($this->totalrecord()/$rowPage);
	}
	function deleterecord($adid){
		global $con;
		mysqli_query($con, "delete from header_link where hl_id='".$adid."'");
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
			$plink="header_link_view.php?".$_SERVER['QUERY_STRING']."&action=paid&did=".$id;
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
		header("location:header_link_view.php");
		}
/*************************************************/

	if(isset($_GET['action']) && $_GET['action']=="paid"){
		echo $_GET['did'];
		$al->changereseller($_GET['did']);
		header("location:header_link_view.php");
		}                
                
$al->limit=$p->setlimit(10);
$al->setsql("select * from header_link where 1 order by hl_id asc");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "header_link_view.php";

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
		header("location:header_link_view.php");
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
<h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Header Link</h2>
<div id="whatsNew-grid" class="grid-view">
<table><tr><td>
<input name="btnDelete" type="submit" value="Delete" class="delete-btn" onclick="return confirm('Are you sure to delete the record?')" />
</td>
<TD>
</TD>
<td><?php echo $showitems ?></td>
<td align="right"><div class="summary">
<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
<select name="limit" id="limit" onchange="javascript:window.location.href='header_link_view.php?page=<?php echo $page ?>&amp;limit='+this.value;">
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
    <th class="usr-name" style="width:200px;"><strong>Upper Text</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Lower Text</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Tooltip Content</strong></th>
    <th class="usr-name" style="width:90px;"><strong>Status</strong></th>
    <th class="usr-name" style="width:90px;"></th>
    <th class="action" style="width:90px;"><strong>Action</strong></th>
</thead>
<tbody>
    	<?php $j=1;
		
		while($row=mysqli_fetch_object($recObj)){ 
               
		?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
        	<td class="checkbox"><input name="cb[]" type="checkbox" value="<?php echo $row->hl_id; ?>" /></td>
        
       <td class="usr-name" style="width:110px;">
	        <?php  echo $row->hl_upper_text;?>
	   </td>   
       <td class="usr-name" style="width:110px;">
	        <?php  echo $row->hl_lower_text;?>
	   </td>   
       <td class="usr-name" style="width:110px;">
	        <?php  echo $row->hl_content;?>
	   </td>      
           <td class="usr-name" style="width:90px; text-align:center">
	       <?php if($row->hl_status == '1'){echo '<font color=green>Active</font>';}elseif($row->hl_status == '0'){echo '<font color=red>Inactive</font>';} ?>
	   </td>   
       <td class="usr-name" style="width:90px; text-align:center">
	     <select id="" onchange="changeStatus(this.value,'<?php echo $row->hl_id;?>')">
         <option value="">Select</option>
          <?php if($row->hl_status == '1'){?>
          <option value="0">Deactivate</option>
          <?php }else{ ?>
          <option value="1">Activate</option>
          <?php }?>
          <script>
          	function changeStatus(stat,id)
			{
				$.post(
						"ajax-files/changeheaderLinkStatus.php", 
        			{stat:stat,id:id}, 
         				function(data){
				location.reload();		
        });
				
			}
		  </script>
          
	   </td>   
       
       <td class="action" style="text-align:center">
            <a href="header_link_edit.php?token=<?php echo rand(1000,9999).md5($row->hl_id);?>" title="Edit">
            	<img alt="edit" src="images/edit.jpg">
            </a>
            <a href="<?php echo $al->deletelink($row->hl_id)?>" title="delete" onclick="return confirm('Are you sure to delete the record?')">
            <img alt="delete" src="images/delete.jpg" border="0"></a>
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