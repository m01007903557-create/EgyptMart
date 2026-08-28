<?php 
//ob_start();
//session_start(); 
include "../common.php";
	class editheadersld{
	var $msg;
	var $isv_link;
	var $isv_image;
	
	function __construct($isv_id)
	{
          $this->isv_id=$isv_id;     
	}
	function detailsObj(){
		global $con;
		$sql="select * from index_software_version where md5(isv_id)='".$this->isv_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		
		return $valid;
	}
	function update()
	{	
	global $con;
	if($this->isv_image != '')
	{
		$ext = end(explode('.',$this->isv_image)); 
        $validEXT = array('jpg','png','jpeg', 'gif', 'pdf');

        if(in_array($ext,$validEXT)) {
            $tempFile = $_FILES['isv_image']['tmp_name'];
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);

            $image = 'SLDIMG-' . rand(0,9999) . $this->isv_image;

			$lstImg = mysqli_fetch_object(mysqli_query($con, "select isv_image from index_software_version where md5(isv_id) = '".$this->isv_id."'"));
			unlink("../image/".$lstImg->isv_image);
            $imgSImage->resize(263,63);
            $imgSImage->save("../image/" . $image);
	
				  $sql="update index_software_version set
						isv_link = '".$this->isv_link."',
                        isv_image ='".$image."'
						where md5(isv_id) = '".$this->isv_id."'";				
	
				 mysqli_query($con, $sql) or die(mysql_error());	
		 }
        else
        {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extention</font>';
        }

		}
		else
		{		
				  $sql="update index_software_version set
						isv_link = '".$this->isv_link."'
						where md5(isv_id) = '".$this->isv_id."'";	
						
						mysqli_query($con, $sql) or die(mysql_error());	
		}
		
		$this->msg='<font color="#009900">Software version Updated successfully</font>';	
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$token = substr($_GET['token'], 4);
$ob=new editheadersld($token);
$row=$ob->detailsObj();

if(isset($_POST['btnAdd']))
{			
	$ob->isv_link=trim(addslashes($_POST['isv_link']));
	$ob->isv_image=$_FILES['isv_image']['name'];
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:software_version_edit.php?token=".$_GET['token']);
}
?>

	<?php include "includes/admin-top.php" ?>
	
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Manage Software Version&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Update Software Version</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
<input type="button" class="delete-btn" onClick="window.location ='software_version_list.php'" value="Software Version List">
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Link: </label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <input type="text" name="isv_link" id="isv_link" value="<?php echo $row->isv_link;?>"  class="reg_txtfld" />
			</div>
		</div>
        
                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Current Image: </label>
			<div class="formInputBox" style="width:387px;height:auto;">
            <img src="../image/<?php echo $row->isv_image; ?>" width="200px;" height="150px;"/><br>
			</div>
		</div
        
        ><div id="uploadImageDiv" <?php if($row->f_image=='0'){?>style="display: none"<?php }?>>
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Upload Images:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input type="file" name="isv_image" id="isv_image" style="cursor:pointer"/>
			</div>
 		</div>
        </div>
</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnAdd" id="btnAdd" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
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