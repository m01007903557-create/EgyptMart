<?php
ob_start();
session_start();
include "../common.php";

$sql="select * from temp_tender_alert_cat,product_category where ttac_pc_id=pc_id and ttac_usr_id='".$_SESSION['uid_indm']."'";
$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
?>
<div class="setcat" id="<?php echo $row->pc_id; ?>" style="display:block;">&bull;&nbsp;<?php echo $row->pc_name; ?><a href="javascript:remove(<?php echo $row->pc_id; ?>)"><img src="images/remove.gif" height="10" hspace="6" width="44"></a></div>
<?php } ?>