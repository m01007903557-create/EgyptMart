<?php
include "../common.php";

$bu_blockBy=trim($_POST['blockBy']);
$bu_blocked=trim($_POST['blocked']);

$sql="delete from blocked_user
	where
		bu_blockBy='".$bu_blockBy."' and bu_blocked='".$bu_blocked."'";
mysqli_query($con, $sql);
?>