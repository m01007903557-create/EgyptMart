<?php 
ob_start();
//session_start(); 
include "../common.php";

	class addheadersld{
	var $msg;
	var $hs_status;
	var $hs_text;
	var $hs_image;
	
	function __construct($hs_status,$hs_text,$hs_image)
	{
		$this->hs_status=$hs_status;
		$this->hs_text=$hs_text;
		$this->hs_image=$hs_image;
	
		$_SESSION['hs_status']=$this->hs_status;
		$_SESSION['hs_text']=$this->hs_text;
	}
	
	function valid()
	{
		$valid=true;
		if($this->hs_image == "")
		{
			$this->msg= '<font color="#CC0000">Please chose an image</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function add()
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
            $imgSImage->resize(511,308);
            $imgSImage->save("../upload/slider/" . $image);
	
				  $sql="insert into header_slider set
						hs_status = '".$this->hs_status."',
						hs_text = '".$this->hs_text."',
                        hs_image ='".$image."',   
						hs_updated_date=now()";				
	
				 mysqli_query($con, $sql) or die(mysql_error());	
				 
		 }
        else
        {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extention</font>';
        }
		
	}
		$this->msg='<font color="#009900">Header slider added successfully</font>';

				unset($_SESSION['hs_status']);
				unset($_SESSION['hs_text']);
	}
}
	
	if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }
	if(isset($_SESSION['hs_status'])){ $hs_status=$_SESSION['hs_status']; unset($_SESSION['hs_status']); } else { $hs_status=""; }
	if(isset($_SESSION['hs_text'])){ $hs_text=$_SESSION['hs_text']; unset($_SESSION['hs_text']); } else { $hs_text=""; }

if(isset($_POST['btnAdd']))
{
	$adn=new addheadersld(addslashes(trim($_POST['hs_status'])),addslashes(trim($_POST['hs_text'])),$_FILES["hs_image"]["name"]);
	
	$_SESSION['hs_status']= addslashes(trim($_POST['hs_status']));			
	$_SESSION['hs_text']=addslashes(trim($_POST['hs_text']));
	
	if($adn->valid()){	
		$adn->add();	
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:header_slider_add.php");
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
<h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Header Slider</h2>
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
                <input type="radio" name="hs_status" checked = "checked" value="1">&nbsp;Yes&nbsp;&nbsp;
                <input type="radio" value="0" name="hs_status" <?php if($hs_status=='0'){?>checked<?php }?>>&nbsp;No
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Content: </label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <input type="text" name="hs_text" id="hs_text" value="<?php echo $hs_text;?>"  class="reg_txtfld" />
			</div>
		</div>
        
        <div id="uploadImageDiv" <?php if($row->f_image=='0'){?>style="display: none"<?php }?>>
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