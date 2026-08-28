<?php
// ملف test-chatpage-absolute.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    header('Location: /sign-in.php');
    exit;
}
?>

<div style="min-height: 500px; background-color: silver;">
    <p style="padding: 20px;">هذه هي المساحة الفضية. إذا ظهر الهيدر والفوتر بشكل طبيعي حول هذا النص، فهذا يعني أن الحل هو استخدام المسارات المطلقة.</p>
</div>

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>