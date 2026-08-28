<?php
echo "هذا ملف اختبار بسيط.<br>";
echo "دليل الملف الحالي: " . __DIR__ . "<br>";
$libPath = dirname(__DIR__) . "/lib/SimpleImage.php";
echo "مسار المكتبة: " . $libPath . "<br>";
if (file_exists($libPath)) {
    echo "الملف موجود.<br>";
    require_once $libPath;
    echo "تم تضمين المكتبة.<br>";
} else {
    echo "الملف غير موجود.<br>";
}
?>