<?php
ob_start();
session_start();
include "../common.php";

$pc_parent_id=$_POST['id'];
$type=$_POST['type'];
if($type=="buy"){
$sql="select * from product_category where pc_parent_id='".$pc_parent_id."' and pc_id not in(select bac_pc_id from buylead_alert_category where bac_usr_id='".$_SESSION['uid_indm']."')";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_chk="select * from temp_buylead_alert_cat where tbac_usr_id='".$_SESSION['uid_indm']."' and tbac_pc_id='".$row->pc_id."'";
	$res_chk=mysqli_query($con, $sql_chk);
	$row_num=mysqli_num_rows($res_chk);
?>
<input name="scat_<?php echo $row->pc_id; ?>" id="scat_<?php echo $row->pc_id; ?>" value="<?php echo $row->pc_id; ?>" onclick="scatAddDel('<?php echo $row->pc_id; ?>')" type="checkbox" <?php if($row_num>0){ ?> checked="checked"<?php } ?>><?php echo ucwords($row->pc_name); ?><br>
<?php } 
}

if($type=="sell"){
$sql="select * from product_category where pc_parent_id='".$pc_parent_id."' and pc_id not in(select sac_pc_id from selloffer_alert_category where sac_usr_id='".$_SESSION['uid_indm']."')";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_chk="select * from temp_selloffer_alert_cat where tsac_usr_id='".$_SESSION['uid_indm']."' and tsac_pc_id='".$row->pc_id."'";
	$res_chk=mysqli_query($con, $sql_chk);
	$row_num=mysqli_num_rows($res_chk);
?>
<input name="scat_<?php echo $row->pc_id; ?>" id="scat_<?php echo $row->pc_id; ?>" value="<?php echo $row->pc_id; ?>" onclick="scatAddDel('<?php echo $row->pc_id; ?>')" type="checkbox" <?php if($row_num>0){ ?> checked="checked"<?php } ?>><?php echo ucwords($row->pc_sort_name); ?><br>
<?php } 
}

if($type=="tender"){
$sql="select * from product_category where pc_parent_id='".$pc_parent_id."' and pc_id not in(select tac_pc_id from tender_alert_category where tac_usr_id='".$_SESSION['uid_indm']."')";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_chk="select * from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."' and ttac_pc_id='".$row->pc_id."'";
	$res_chk=mysqli_query($con, $sql_chk);
	$row_num=mysqli_num_rows($res_chk);
?>
<input name="scat_<?php echo $row->pc_id; ?>" id="scat_<?php echo $row->pc_id; ?>" value="<?php echo $row->pc_id; ?>" onclick="scatAddDel('<?php echo $row->pc_id; ?>')" type="checkbox" <?php if($row_num>0){ ?> checked="checked"<?php } ?>><?php echo ucwords($row->pc_name); ?><br>
<?php } 
}

if($type=="auction"){
$sql="select * from product_category where pc_parent_id='".$pc_parent_id."' and pc_id not in(select aac_pc_id from auction_alert_category where aac_usr_id='".$_SESSION['uid_indm']."')";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_chk="select * from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."' and taac_pc_id='".$row->pc_id."'";
	$res_chk=mysqli_query($con, $sql_chk);
	$row_num=mysqli_num_rows($res_chk);
?>
<input name="scat_<?php echo $row->pc_id; ?>" id="scat_<?php echo $row->pc_id; ?>" value="<?php echo $row->pc_id; ?>" onclick="scatAddDel('<?php echo $row->pc_id; ?>')" type="checkbox" <?php if($row_num>0){ ?> checked="checked"<?php } ?>><?php echo ucwords($row->pc_name); ?><br>
<?php } 
}
?>