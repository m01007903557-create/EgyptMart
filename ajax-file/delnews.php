<?php
/**
 * File: ajax/delnews.php

 * Description: حذف خبر مع الصور المرتبطة به
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف الخبر
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid news ID";
    exit;
}

$news_id = (int)$_GET['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب معلومات الصور قبل الحذف
    $select_sql = "SELECT nws_smallimg, nws_largeimg FROM news WHERE nws_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $news_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new Exception("News not found");
    }

    $deleted_files = [];
    $failed_files = [];

    // حذف الصورة المصغرة
    if (!empty($row->nws_smallimg)) {
        $small_image = basename($row->nws_smallimg);
        $small_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $small_image);
        
        if (!empty($small_image)) {
            $small_path = __DIR__ . "/../upload/mynews/small/" . $small_image;
            if (file_exists($small_path) && is_file($small_path)) {
                if (unlink($small_path)) {
                    $deleted_files[] = $small_path;
                } else {
                    $failed_files[] = $small_path;
                }
            }
        }
    }

    // حذف الصورة الكبيرة
    if (!empty($row->nws_largeimg)) {
        $large_image = basename($row->nws_largeimg);
        $large_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $large_image);
        
        if (!empty($large_image)) {
            $large_path = __DIR__ . "/../upload/mynews/large/" . $large_image;
            if (file_exists($large_path) && is_file($large_path)) {
                if (unlink($large_path)) {
                    $deleted_files[] = $large_path;
                } else {
                    $failed_files[] = $large_path;
                }
            }
        }
    }

    // حذف سجل الخبر من قاعدة البيانات
    $delete_sql = "DELETE FROM news WHERE nws_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $news_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete news record: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_delete) == 0) {
        throw new Exception("News not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    // تسجيل النتيجة
    if (!empty($failed_files)) {
        error_log("Some files could not be deleted for news ID $news_id: " . implode(', ', $failed_files));
        echo "1|News deleted successfully but some files could not be removed";
    } else {
        echo "1|News deleted successfully";
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete News Error: " . $e->getMessage() . " | News ID: $news_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>