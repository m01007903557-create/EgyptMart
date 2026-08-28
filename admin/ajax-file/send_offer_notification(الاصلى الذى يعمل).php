<?php
// سطر تشخيب - تأكد من أن الملف يتم تنفيذه
file_put_contents(__DIR__ . '/debug_log.txt', date('Y-m-d H:i:s') . " - تم استدعاء الملف\n", FILE_APPEND);

session_start();
require_once "../../lib/connect.php";

header('Content-Type: application/json');

echo json_encode(['success' => true, 'message' => 'test']);
?>