<?php 
//ob_start();
//session_start(); 
include "../common.php";
$randNo = rand(10000,55555);
	class addsupport{
	var $msg;
	var $fc_name;
	
	function __construct($fc_name)
	{
          $this->fc_name=$fc_name;
		
          $_SESSION['fc_name']=$this->fc_name;
	}
	
	function valid()
	{
		$valid=true;
		if($this->fc_name == "")
		{
			$this->msg= '<font color="#CC0000">Please enter category name</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function add()
	{				   	 		
      global $con;	
	  $sql="insert into faq_categories_arabyos set		
			fc_name ='".$this->fc_name."'";					
			mysqli_query($con, $sql) or die(mysql_error());

		    unset($_SESSION['fc_name']);
			$this->msg='<font color="#009900">Category added successfully</font>';
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg="";    }
if(isset($_SESSION['fc_name'])){ $fc_name=$_SESSION['fc_name']; unset($_SESSION['fc_name']); } else { $fc_name=""; }

if(isset($_POST['btnAdd']))
{
	
	$adn=new addsupport(addslashes(trim($_POST['fc_name'])));

	
	
	if($adn->valid()){	
		$adn->add();		
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:support_add.php");
}
?>

	<?php include "includes/admin-top.php" ?>
	
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<script type="text/javascript">
function myvalid()
{	
	var fc_name=document.getElementById('fc_name');
	var message="";
	var valid=true;
	
	if(fc_name.value=='')
	{
		message='Please enter category name';
		fc_name.focus();
		valid=false;
	}
	if(!valid)
	{
		document.getElementById('message').style.color = "red";
		document.getElementById('message').innerHTML = message;	
	}
	return valid;

}
</script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
 
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Manage Support&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Support</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
<input type="button" class="delete-btn" onClick="window.location ='support-category-list.php'" value="Support List">
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Category Name: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input name="fc_name" id="fc_name" class="reg_txtfld" type="text" value="<?php echo $fc_name?>"/>         
			</div>
		</div>
</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
	</form>    
 			<br clear="all"/>
		</div>
			
	</div>
	</div>
  	<br clear="all" />   	
</div>
<?php include "includes/footer.php" ?>
</body>
</html>