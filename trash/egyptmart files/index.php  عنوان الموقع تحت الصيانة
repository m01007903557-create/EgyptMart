<?php 
date_default_timezone_set("Asia/Dhaka");
$con = mysqli_connect("localhost","egyptmar_test","TestPass24..","egyptmar_test");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>The Site Is Under Developement</title>
</head>
<body>
	<?php
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") ."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$time = date("Y-d-m H:i:s");
	$ip = $_SERVER['REMOTE_ADDR'];
	$location = "Disabled To Keep The CPU usage under controll";
	
	$select = mysqli_query($con, "SELECT count FROM infos WHERE url = '$url'");
	$count = mysqli_num_rows($select); 
	if ($count > 0) {
		$fetch = mysqli_fetch_array($select);
		$new_count = bcadd($fetch['count'], 1);
		mysqli_query($con,"UPDATE infos SET count = '$new_count', ip = '$ip', last_hit = '$time', last_hit_from = '$location' WHERE url = '$url'");
	}else{
		mysqli_query($con, "INSERT INTO infos(url,count,last_hit,last_hit_from,ip) VALUES('$url','1','$time','$location','$ip')");
	}

	?>
	<h2>Sorry, The Site is Under Maintenance. Please check back later!</h2>
</body>
</html>