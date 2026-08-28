<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Testing...\n";

// محاولة تضمين الملفات تدريجياً
require_once "../common-support.php";
echo "common-support.php loaded\n";

// التحقق من وجود $con
if (isset($con)) {
    echo "Database connection exists\n";
} else {
    echo "Database connection NOT found\n";
}

// محاولة إنشاء كائن BusinessPlanAssignerComplete
if (class_exists('BusinessPlanAssignerComplete')) {
    echo "Class BusinessPlanAssignerComplete exists\n";
} else {
    echo "Class NOT found\n";
}

echo "Done</pre>";