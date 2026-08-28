<?php
echo "PHP يعمل بشكل طبيعي";
?>


<?php
include "common.php";

$sql="SELECT * FROM `organization` WHERE 1";
echo $sql;
$res=mysqli_query($con, $sql);
if(!$res) die("SQL staement failed<hr>$query");
echo $tot=mysqli_num_rows($res);exit;
$roww=mysqli_fetch_object($result);
?>