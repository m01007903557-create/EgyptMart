<?php
include "../common.php";

$pc_id=$_POST['id'];
$sql="select * from product_category_arabyos where pc_parent_id='".$pc_id."' and pc_parent_id!='0' and pc_status='1'";
$res=mysqli_query($con, $sql);
?>
<option value="">--Select Category--</option>
<?php
while($row=mysqli_fetch_object($res))
{
	?>
	<option value="<?php echo $row->pc_id; ?>"><?php echo $row->pc_name; ?></option>
<?php	}	?>