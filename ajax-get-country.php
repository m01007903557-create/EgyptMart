<?php
header('Content-Type: application/json');
require_once 'common.php';

$location = getLocationInfoByIp();
if (!empty($location) && !empty($location[0])) {
    echo json_encode(['status' => 'success', 'cn_id' => $location[0]]);
} else {
    echo json_encode(['status' => 'error', 'cn_id' => null]);
}