<?php
declare(strict_types=1);

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات المطلوبة
require_once __DIR__ . '/common.php';
echo "<!-- بداية index_middle_content.php -->";


include_once __DIR__ . '/index_md_header.php';

echo "<!-- قبل include index_md_search.php -->";
include_once __DIR__ . '/index_md_search.php';
echo "<!-- بعد include index_md_search.php -->";


// يمكن إضافة محتوى إضافي هنا إذا لزم الأمر
?>
<?php echo "<!-- نهاية index_middle_content.php -->"; ?>