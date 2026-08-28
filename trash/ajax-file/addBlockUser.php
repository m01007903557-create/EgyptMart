<?php
include "../common.php";

$bu_blockBy=trim($_POST['blockBy']);
$bu_blocked=trim($_POST['blocked']);

$sql="insert into blocked_user
	set
		bu_blockBy='".$bu_blockBy."',
		bu_blocked='".$bu_blocked."',
		bu_updated_date=now()";
mysqli_query($con, $sql);
?>