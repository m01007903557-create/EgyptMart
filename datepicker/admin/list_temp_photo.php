<?php 
include "../common.php";
$usid = $_GET['pid'];

$sql="select * from feature_images where fi_f_id =".$usid."";
$recObj=mysqli_query($con, $sql) or die(mysql_error());
$timage_num=mysqli_num_rows($recObj);

?>
<table>
    
        
<?php
//if($timage_num>0){
$i=1;
while($row=mysqli_fetch_array( $recObj)){

?>
    <?php if($i==1){?><tr><?php }?>

        <td style="width: 300px">
<p><img id="profile_pic" src="../upload/feature/<?php echo $row['fi_image']; ?>" width="112" height="74">
<a href="javascript:DelTempImage_rc(<?php echo $row['fi_id']; ?>)" class="remove"><img src="uploadifive/uploadifive-cancel.png" title="Delete Image"></a></p>
</td>

    <?php if($i==4){?></tr><?php $i=0;}?>
<?php  
	
	
$i++;
} ?>

</table>