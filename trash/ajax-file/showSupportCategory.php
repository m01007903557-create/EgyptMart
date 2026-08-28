<?php 
include '../common.php';
$id = $_POST['id'];
$res = mysqli_query($con, "select * from custom_faq_arabyos where cf_fc_id = '".$id."'");
while($row = mysqli_fetch_object($res)){
?>


 <p class="press-release"> <a style="color: ; font-weight: bold; font-size:14px">Q. <?php echo $row->cf_heading;?></a></p> 
   
      <p class="press-release"> <a style="color: #666; font-size:12px"><?php echo $row->cf_content;?></a></p> 




<?php }?>
<br />

  