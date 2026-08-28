<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$server 	= "localhost";
$user  		= "egyptmar_sgwp";
$db_name 	= "egyptmar_sgwp";
$pass 		= ",FsrL4{vQXYU";

$con = mysqli_connect($server, $user, $pass, $db_name);

$sheet = "Sheet Metal Fabrication";

$sql="select * from product_category where pc_sort_name = '$sheet'";
$query = mysqli_query($con, $sql);


while ($raw = mysqli_fetch_array($query)) {
    //echo "Starting -> ". $raw['pc_id'] . "<br>";
};


// echo "<br> Shut the fk up <br>";

// $raws = mysqli_fetch_array($query);

// echo json_encode($raws) . "<br>";
// echo mysqli_num_rows($query) . "<br>";

// foreach ($raws as $key => $ra) {
// 	echo $key . " => ". $ra . "<br>";
// }

$q2 = mysqli_query($con, "SELECT * FROM products WHERE md5(pd_id) = 'c911241d00294e8bb714eee2e83fa475'");

//$fetch1 = mysqli_fetch_array($q1);
echo "[";
while($fetch2 = mysqli_fetch_array($q2)){
	echo json_encode($fetch2). ", <br>";
}
echo "] <br>" . mysqli_num_rows($q2);
$table = "business_profile_arabyos";
if (mysqli_num_rows(mysqli_query($con, "SELECT * 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_NAME = '$table'")) > 0){
	echo "<br>{$table} Table Exist<br>";
}else{
	echo "<br>{$table} Table Doesn't Exist<br>";
}




//echo "<br> Short Name for ID 1 is \"{$fetch1['pc_sort_name']}\" and name id {$fetch1['pc_name']}";
//echo "<br> Short Name for ID 2 is \"{$fetch2['pc_sort_name']}\" and name is {$fetch2['pc_name']} <br>";

//echo json_encode($fetch1) . "<br>";









?>