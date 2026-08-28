<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$msg_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

echo "msg_id: " . $msg_id . "<br>";
echo "type: " . $type;
?>