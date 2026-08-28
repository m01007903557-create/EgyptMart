<?php 
include "../common.php";
$id=$_REQUEST['id'];
$faqcatsql=mysqli_query($con, "");
$sql=mysqli_query($con, "select * from custom_faq_arabyos where cf_id='".$id."'");
$row=mysqli_fetch_array( $sql);

$faqcatsql=mysqli_query($con, "select * from faq_categories_arabyos where fc_id='".$row['cf_fc_id']."'");
$faqcatrow=mysqli_fetch_array( $faqcatsql);
?>
<link rel="stylesheet" type="text/css" href="style/screen.css" media="screen, projection">
<link rel="stylesheet" type="text/css" href="style/main.css" media="screen, projection">

  <div class="control_Panel">
		<div id="content-container">
		<div id="content">
<h2>Details&nbsp;&nbsp;&nbsp;&nbsp;</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<div class="x2-layout" style="width:980px; height:auto;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>
<tr class="formSectionRow">
<td  style="width:678px">
	
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"><h2>Category: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $faqcatrow['fc_name']; ?>
			</div>
		</div>
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"><h2>Heading: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $row['cf_heading']; ?>
			</div>
		</div>
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"><h2>Topic: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $row['cf_support']; ?>
			</div>
		</div>
        
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"><h2>Topic Link: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $row['cf_support_link']; ?>
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"><h2>Content:</h2></label>
			<div class="formInputBox" style="width:727px;height:auto;">
                       <?php echo $row['cf_content']; ?> 
			</div>
		</div>
</td>
</tr>
</tbody></table></div></div> </div>  		    																																									    
	</form>    
 			<br clear="all"/>
		</div>
			
	</div>
	</div>
  	<br clear="all" />   	