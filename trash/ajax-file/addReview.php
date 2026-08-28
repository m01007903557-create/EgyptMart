<?php
ob_start();
session_start();
include "../common.php";

$rr_id = trim($_POST['id']);
$rr_rating = trim($_POST['r']);
$rr_review = addslashes(trim($_POST['rv']));

	$sql="update review_rating
		set
			rr_rating = '".$rr_rating."',
			rr_review = '".$rr_review."',
			rr_updated_date=now()
		where
			rr_id='".$rr_id."'";
			echo $sql;
	mysqli_query($con, $sql);

?>