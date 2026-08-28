<?php
require_once "../common.php";

$sql = "SELECT pd_id, pd_title FROM products LIMIT 5";
$result = mysqli_query($con, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>