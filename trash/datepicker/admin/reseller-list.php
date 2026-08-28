<?php 
ob_start();
session_start(); 
include "../common.php";
$reseller_id=$_SESSION['reseller_id'];
//check_user_login();

$resellersql="select * from reseller where reseller_id='".$reseller_id."'";
$resellerres=mysqli_query($con, $resellersql);
$resellerrow=mysqli_fetch_object($resellerres);
class resellerlist{

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
		mysqli_query($con, "delete from admin_login_details where admin_login_id='".$adid."'");
	}
        
        
      function changereseller($adid){
		  global $con;
          mysqli_query($con, "update reseller set reseller_status='0' where reseller_id='".$adid."'");  
        }
      
      function changesuspend($adid){
		  global $con;
         mysqli_query($con, "update reseller set reseller_status='1' where reseller_id='".$adid."'");    
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
			$plink="reseller-list.php?".$_SERVER['QUERY_STRING']."&action=paid&did=".$id;
		}
		return $plink;
	}
        
                function suspendlink($id){
		if($_SERVER['QUERY_STRING']==""){
			$plink="?action=suspend&did=".$id;
		}
		else{
			$plink="reseller-list.php?".$_SERVER['QUERY_STRING']."&action=suspend&did=".$id;
		}
		return $plink;
	}
}

$p=new Pagination;
$page=$p->setpage();

$al=new resellerlist;
/********************delete record*********************/
	if($_GET['action']=="del"){
		echo $_GET['ad-id'];
		$al->deleterecord($_GET['ad-id']);
		header("location:reseller-list.php");
		//header("location:reseller-list.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/
	if($_GET['action']=="paid"){
		$al->changereseller($_GET['did']);
		header("location:reseller-list.php");
		}
                
       	if($_GET['action']=="suspend"){
		$al->changesuspend($_GET['did']);
		header("location:reseller-list.php");
		}         
                
$al->limit=$p->setlimit(10);
$al->setsql("select * from reseller order by reseller_creation_date");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "reseller-list.php";

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
		header("location:reseller-list.php");
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
<h2>&rsaquo;&nbsp;&nbsp;Admin Management&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Reseller List</h2>
<div id="whatsNew-grid" class="grid-view">
<table><tr>
<TD>
<input type="button" class="delete-btn" onClick="window.location ='reseller-registration.php' " value="Reseller Registration">
</TD>        
<td align="right"><div class="summary">
<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
<select name="limit" id="limit" onchange="javascript:window.location.href='reseller-list.php?page=<?php echo $page ?>&amp;limit='+this.value;">
        <?php for($i=10; $i<=30; $i=$i+10){ 
        	if($i==$limit){?>
            <option value="<?php echo $i ?>" selected="selected" ><?php echo $i ?></option>
            <?php }else{?>
            <option value="<?php echo $i ?>" ><?php echo $i ?></option>
 <?php }
 } ?>
        </select></div>results per page.</div>
</td>
		<td></td>
		</tr>
	</table>
   
<table class="items">
<thead>
<tr>
    <th align="left" style="width:40px;">#</th>
    <th class="usr-name" style="width:140px;"><strong>Logo</strong></th>
    <th class="usr-name" style="width:140px;"><strong>Full Name</strong></th>
    <th class="usr-name" style="width:170px;"><strong>email</strong></th>
    <th class="usr-name" style="width:140px;"><strong>User name</strong></th>
    <th class="usr-name" style="width:170px;"><strong>Domain</strong></th>
    <th class="usr-name" style="width:100px;"><strong>Status</strong></th>
    <th class="usr-name" style="width:100px;"><strong>Action</strong></th>
</thead>
<tbody>
    	<?php $j=1;
		$cursql=mysqli_query($con, "select * from site_settings_arabyos where st_field ='currency-symbol'");
		$currow=mysqli_fetch_object($cursql);
		while($row=mysqli_fetch_object($recObj)){ 
                    $sqlmchk="select * from reselluser_specification where uspf_uid='".$reseller_id."' and uspf_pdid='".$row->pd_id."'";
                    $resmchk=mysqli_query($con, $sqlmchk);
                    $rowmchk=mysqli_fetch_object($resmchk);
		?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
        	<td class="checkbox">#<?php echo $j;?></td>
                
            <td class="usr-name" style="padding-left:30px;">
	        <?php if($row->reseller_logo!=""){ ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row->reseller_logo); ?>" width="100"/>
                <?php } else { ?>
                <img src="../products_images/il_75x75.jpg" width="100">
                <?php }?>
	   </td>
                
            <td class="usr-name" style="padding-left:30px;">
	   <?php echo ucwords($row->reseller_fullname); ?>
	   </td>
           <td class="usr-name" style="padding-left:1px;">
	   <?php echo ucwords($row->reseller_email); ?>
	   </td>    
            <td class="usr-name" style="padding-left:60px;">
	    <?php echo ucwords($row->reseller_uname); ?>
	    </td>
            <td class="usr-name" style="padding-left:10px;">
	    <?php echo ucwords($row->reseller_website); ?>
	    </td>
                
            <td class="usr-name" style="padding-left:35px;">
	    <?php if(($row->reseller_status)==1){ ?> 
  <a href="<?php echo $al->paidlink($row->reseller_id) ?>" title="Pending" onclick="return confirm('Are you sure to change this status?')">
  <strong>Active</strong></a>
		   <?php } else { ?>
                  <a href="<?php echo $al->suspendlink($row->reseller_id) ?>" title="Pending" onclick="return confirm('Are you sure to change this status?')">
                <strong>Suspend</strong></a>
                <?php } ?>
	    </td>    
            <td class="usr-name" style="padding-left:50px;">
	    <a href="reseller-edit.php?r=<?php echo rand(1000,9999).md5($row->reseller_id);?>" title="Edit">
            <img alt="edit" src="images/edit.jpg"></a>
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