<?php 
//ob_start();
//session_start(); 
include "../common.php";
	class editheadersld{
	var $msg;
	var $hs_status;
	var $hs_text;
	var $hs_image;
	
	function __construct($hs_id)
	{
          $this->hs_id=$hs_id;     
	}
	function detailsObj(){
		global $con;
		$sql="select * from header_slider where md5(hs_id)='".$this->hs_id."'";
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
	if($this->hs_image != '')
	{
		$ext = end(explode('.',$this->hs_image)); 
        $validEXT = array('jpg','png','jpeg', 'gif', 'pdf');

        if(in_array($ext,$validEXT)) {
            $tempFile = $_FILES['hs_image']['tmp_name'];
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);

            $image = 'SLDIMG-' . rand(0,9999) . $this->hs_image;

			$lstImg = mysqli_fetch_object(mysqli_query($con, "select hs_image from header_slider where md5(hs_id) = '".$this->hs_id."'"));
			unlink("../upload/slider/".$lstImg->hs_image);
            $imgSImage->resize(511,308);
            $imgSImage->save("../upload/slider/" . $image);
	
				  $sql="update header_slider set
						hs_status = '".$this->hs_status."',
						hs_text = '".$this->hs_text."',
                        hs_image ='".$image."',   
						hs_updated_date=now()
						where md5(hs_id) = '".$this->hs_id."'";				
	
				 mysqli_query($con, $sql) or die(mysql_error());	
		 }
        else
        {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extention</font>';
        }

		}
		else
		{		
				  $sql="update header_slider set
						hs_status = '".$this->hs_status."',
						hs_text = '".$this->hs_text."',   
						hs_updated_date=now()
						where md5(hs_id) = '".$this->hs_id."'";	
						
						mysqli_query($con, $sql) or die(mysql_error());	
		}
		
		$this->msg='<font color="#009900">Header slider Updated successfully</font>';	
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$token = substr($_GET['token'], 4);
$ob=new editheadersld($token);
$row=$ob->detailsObj();

if(isset($_POST['btnAdd']))
{	
	$ob->hs_status=trim(addslashes($_POST['hs_status']));			
	$ob->hs_text=trim(addslashes($_POST['hs_text']));
	$ob->hs_image=$_FILES['hs_image']['name'];
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:header_slider_edit.php?token=".$_GET['token']);
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
<h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Update Header Slider</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
<input type="button" class="delete-btn" onClick="window.location ='header_slider_view.php'" value="Header Links">
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Status :</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                <input type="radio" name="hs_status" <?php if($row->hs_status=='1'){?>checked<?php }?> value="1">&nbsp;Yes&nbsp;&nbsp;
                <input type="radio" value="0" name="hs_status" <?php if($row->hs_status=='0'){?>checked<?php }?>>&nbsp;No
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Content: </label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <input type="text" name="hs_text" id="hs_text" value="<?php echo $row->hs_text;?>"  class="reg_txtfld" />
			</div>
		</div>
        
        
                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Current Image: </label>
			<div class="formInputBox" style="width:387px;height:auto;">
            <img src="../upload/slider/<?php echo $row->hs_image; ?>" width="200px;" height="150px;"/><br>
			</div>
		</div
        
        ><div id="uploadImageDiv" <?php if($row->f_image=='0'){?>style="display: none"<?php }?>>
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Upload Images:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input type="file" name="hs_image" id="hs_image" style="cursor:pointer"/>
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