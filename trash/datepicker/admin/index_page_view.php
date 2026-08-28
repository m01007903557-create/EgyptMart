<?php 
//ob_start();
//session_start(); 
include "../common.php";
//include "lib/pagination.php";

//check_user_login();
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
		mysqli_query($con, "update airlines set al_status = '0' where al_id='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="index_page_view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}

$p=new Pagination;
$page=$p->setpage();

$al=new listCat;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del"){
		$al->deleterecord($_GET['fid']);
		header("location:index_page_view.php");
		//header("location:welcome.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/***********************************************/

$al->limit=$p->setlimit(20);
$al->setsql("select * from index_content where 1");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "index_page_view.php";

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

	<h1>Manage Webpage&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Index Page</h1>



<div id="whatsNew-grid" class="grid-view">
<table><tr><td>
<input name="btnDelete" type="submit" value="Delete" class="delete-btn" onclick="return confirm('Are you sure to delete the record?')" />
</td>
<td><?php echo $showitems ?></td>
<td align="right"><div class="summary">
<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
<select name="limit" id="limit" 
        			onchange="javascript:window.location.href='index_page_view.php?page=<?php echo $page ?>&amp;limit='+this.value;">
        <?php for($i=10; $i<=30; $i=$i+10){ 
        	if($i==$limit){?>
            <option value="<?php echo $i ?>" selected="selected" ><?php echo $i ?></option>
            <?php }else{?>
            <option value="<?php echo $i ?>" ><?php echo $i ?></option>
 <?php }
 } ?>
        </select></div>results per page.</div></td>
		<td></td>
		</tr>
	</table>
<table class="items">
<thead>
<tr>
   <th class="checkbox" align="left" style="width: 10px;"><!--<input name="check_all" value="yes" id="check_all" type="checkbox" onClick="return checkedAll();">--></th>
    <th class="usr-name" style="width: 200px;"><strong>Heading</strong></th>
    <th class="usr-name"  style="width: 200px;"><strong>Content</strong></th>
    <th class="usr-name" style="width:90px;"><strong>Status</strong></th>
    <th class="usr-name" style="width:90px;"></th>
    <th class="action" style="width: 50px;"><strong></strong></th>
</thead>

<tbody>
    	<?php $j=0;
		while($row=mysqli_fetch_object($recObj)){?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
        	<td class="checkbox">
                </td>
            <td class="usr-name"><b>
                <?php 
                     $a_c=explode("-",$row->ic_heading);
                     $i=0;
                     foreach($a_c as $v)
                     {
                         if($i>0){  echo "&nbsp;";  }
                         echo ucfirst($v);
                         $i++;
                     }
                     //echo ucfirst($a_c[0])."&nbsp;".ucfirst($a_c[1]);
                ?>
                </b></td>
            <td class="usr-name" style="width:200px; text-align:center">
             
			<?php echo $row->ic_content;?>
            </td>
            
            
                <td class="usr-name" style="width:90px; text-align:center">
	       <?php if($row->ic_status == '1'){echo '<font color=green>Active</font>';}elseif($row->ic_status == '0'){echo '<font color=red>Inactive</font>';} ?>
	   </td>   
       <td class="usr-name" style="width:90px; text-align:center">
	     <select id="" onchange="changeStatus(this.value,'<?php echo $row->ic_id;?>')">
         <option value="">Select</option>
          <?php if($row->ic_status == '1'){?>
          <option value="0">Deactivate</option>
          <?php }else{ ?>
          <option value="1">Activate</option>
          <?php }?>
          <script>
          	function changeStatus(stat,id)
			{
				$.post(
						"ajax-files/changeIndexContentStatus.php", 
        			{stat:stat,id:id}, 
         				function(data){
				location.reload();		
        });
				
			}
		  </script>
	   </td>  
            
            
            <td class="action" align="center">
            <a href="index_page_edit.php?sid=<?php echo $row->ic_id ?>" title="edit"><img alt="edit" src="images/edit.jpg" border="0"></a>
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
