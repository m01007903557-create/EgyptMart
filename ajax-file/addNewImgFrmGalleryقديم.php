<?php
/**
 * File: ajax/addNewImgFrmGallery.php

 * Description: إضافة صورة من معرض الصور إلى مختلف أنواع المحتوى (عروض بيع، منتجات، طلبات شراء)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود البيانات الأساسية
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("0|Invalid image ID");
}

if (!isset($_POST['tbl'])) {
    http_response_code(400);
    die("0|Invalid table name");
}

$ph_id = (int)$_POST['id'];
$tbl = $_POST['tbl'];
$so_id = isset($_POST['so_id']) ? (int)$_POST['so_id'] : 0;
$br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : 0;
$usr = isset($_POST['usr']) ? (int)$_POST['usr'] : 0;
$pd_id = isset($_POST['pd_id']) ? (int)$_POST['pd_id'] : 0;
$typ = $_POST['typ'] ?? 'product';
$imgArr = $_POST['imgArr'] ?? [];

global $con;

// دالة مساعدة لإنشاء صورة مصغرة
function createThumbnail(string $sourcePath, string $thumbPath, int $width = 100, int $height = 80): bool {
    try {
        if (!class_exists('SimpleImage')) {
            throw new RuntimeException('SimpleImage class not found');
        }
        
        $imgSImage = new SimpleImage();
        $imgSImage->load($sourcePath);
        $imgSImage->resize($width, $height);
        $imgSImage->save($thumbPath);
        chmod($thumbPath, 0644);
        return true;
    } catch (Exception $e) {
        error_log("Thumbnail creation error: " . $e->getMessage());
        return false;
    }
}

// دالة مساعدة للحصول على صورة من المعرض
function getGalleryImage(mysqli $con, int $imageId): ?object {
    $sql = "SELECT ph_fileName FROM photo WHERE ph_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $imageId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt);
    return $row;
}

// دالة مساعدة لنسخ الصورة مع إنشاء اسم جديد
function copyImageWithThumb(string $sourcePath, string $targetDir, string $thumbDir, string $prefix, string $originalName): ?string {
    $fileInfo = pathinfo($originalName);
    $extension = strtolower($fileInfo['extension'] ?? '');
    $timestamp = time();
    $randomString = bin2hex(random_bytes(4));
    
    $newFileName = sprintf(
        '%s-%d-%s.%s',
        $prefix,
        $timestamp,
        $randomString,
        $extension
    );
    
    $targetPath = $targetDir . $newFileName;
    $thumbPath = $thumbDir . $newFileName;
    
    if (!copy($sourcePath, $targetPath)) {
        return null;
    }
    
    chmod($targetPath, 0644);
    
    if (!createThumbnail($targetPath, $thumbPath)) {
        unlink($targetPath);
        return null;
    }
    
    return $newFileName;
}

try {
    mysqli_begin_transaction($con);
    
    switch ($tbl) {
        case 'sale_offer_edit':
            // إضافة صورة إلى عرض بيع محدد
            if ($so_id <= 0) {
                throw new Exception("Invalid sale offer ID");
            }
            
            $row_ph = getGalleryImage($con, $ph_id);
            if (!$row_ph) {
                throw new Exception("Gallery image not found");
            }
            
            // جلب معلومات عرض البيع الحالي
            $so_sql = "SELECT so_pic FROM sale_offer WHERE so_id = ? LIMIT 1";
            $stmt_so = mysqli_prepare($con, $so_sql);
            mysqli_stmt_bind_param($stmt_so, 'i', $so_id);
            mysqli_stmt_execute($stmt_so);
            $result_so = mysqli_stmt_get_result($stmt_so);
            $row_so = mysqli_fetch_object($result_so);
            mysqli_stmt_close($stmt_so);
            
            if (!$row_so) {
                throw new Exception("Sale offer not found");
            }
            
            $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
            if (!file_exists($sourcePath)) {
                throw new Exception("Source image file not found");
            }
            
            $newFileName = copyImageWithThumb(
                $sourcePath,
                __DIR__ . "/../upload/sale_offer/",
                __DIR__ . "/../upload/sale_offer/thumb/",
                'so',
                $row_ph->ph_fileName
            );
            
            if (!$newFileName) {
                throw new Exception("Failed to copy image");
            }
            
            // حذف الصورة القديمة
            if (!empty($row_so->so_pic)) {
                $oldPath = __DIR__ . "/../upload/sale_offer/" . $row_so->so_pic;
                $oldThumb = __DIR__ . "/../upload/sale_offer/thumb/" . $row_so->so_pic;
                
                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldThumb)) unlink($oldThumb);
            }
            
            // تحديث قاعدة البيانات
            $update_sql = "UPDATE sale_offer SET so_pic = ? WHERE so_id = ?";
            $stmt_update = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($stmt_update, 'si', $newFileName, $so_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            break;
            
        case 'temp_selloffer_image':
            // إضافة صورة إلى عرض بيع مؤقت
            if ($usr <= 0) {
                throw new Exception("Invalid user ID");
            }
            
            $row_ph = getGalleryImage($con, $ph_id);
            if (!$row_ph) {
                throw new Exception("Gallery image not found");
            }
            
            // جلب الصورة المؤقتة الحالية
            $tsi_sql = "SELECT tsi_image FROM temp_selloffer_image WHERE tsi_usr_id = ? LIMIT 1";
            $stmt_tsi = mysqli_prepare($con, $tsi_sql);
            mysqli_stmt_bind_param($stmt_tsi, 'i', $usr);
            mysqli_stmt_execute($stmt_tsi);
            $result_tsi = mysqli_stmt_get_result($stmt_tsi);
            $row_tsi = mysqli_fetch_object($result_tsi);
            mysqli_stmt_close($stmt_tsi);
            
            $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
            if (!file_exists($sourcePath)) {
                throw new Exception("Source image file not found");
            }
            
            $newFileName = copyImageWithThumb(
                $sourcePath,
                __DIR__ . "/../upload/sale_offer/",
                __DIR__ . "/../upload/sale_offer/thumb/",
                'so',
                $row_ph->ph_fileName
            );
            
            if (!$newFileName) {
                throw new Exception("Failed to copy image");
            }
            
            // حذف الصورة المؤقتة القديمة
            if ($row_tsi && !empty($row_tsi->tsi_image)) {
                $oldPath = __DIR__ . "/../upload/sale_offer/" . $row_tsi->tsi_image;
                $oldThumb = __DIR__ . "/../upload/sale_offer/thumb/" . $row_tsi->tsi_image;
                
                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldThumb)) unlink($oldThumb);
            }
            
            // حذف السجل القديم وإدراج الجديد
            mysqli_query($con, "DELETE FROM temp_selloffer_image WHERE tsi_usr_id = $usr");
            
            $insert_sql = "INSERT INTO temp_selloffer_image (tsi_usr_id, tsi_image, tsi_upload_date) VALUES (?, ?, NOW())";
            $stmt_insert = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'is', $usr, $newFileName);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
            
            break;
            
        case 'temp_product_image':
            // إضافة صور متعددة إلى منتج مؤقت
            if ($usr <= 0) {
                throw new Exception("Invalid user ID");
            }
            
            if (empty($imgArr)) {
                throw new Exception("No images selected");
            }
            
            $items = [];
            foreach ($imgArr as $imgId) {
                $imgId = (int)$imgId;
                if ($imgId <= 0) continue;
                
                $row_ph = getGalleryImage($con, $imgId);
                if (!$row_ph) continue;
                
                $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
                if (!file_exists($sourcePath)) continue;
                
                $newFileName = copyImageWithThumb(
                    $sourcePath,
                    __DIR__ . "/../upload/myproduct/",
                    __DIR__ . "/../upload/myproduct/thumb/",
                    'prd',
                    $row_ph->ph_fileName
                );
                
                if ($newFileName) {
                    $items[] = $newFileName;
                }
            }
            
            if (empty($items)) {
                throw new Exception("No images were copied successfully");
            }
            
            $imgNames = implode(',', $items);
            
            // التحقق من وجود سجل للمستخدم
            $check_sql = "SELECT tpi_id FROM temp_product_image WHERE tpi_usr_id = ? LIMIT 1";
            $stmt_check = mysqli_prepare($con, $check_sql);
            mysqli_stmt_bind_param($stmt_check, 'i', $usr);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $record_exists = mysqli_num_rows($result_check) > 0;
            mysqli_stmt_close($stmt_check);
            
            if ($record_exists) {
                if ($typ === 'product') {
                    $update_sql = "UPDATE temp_product_image SET tpi_image = ? WHERE tpi_usr_id = ?";
                } else {
                    $update_sql = "UPDATE temp_product_image SET tpi_logo = ? WHERE tpi_usr_id = ?";
                }
                $stmt_update = mysqli_prepare($con, $update_sql);
                mysqli_stmt_bind_param($stmt_update, 'si', $imgNames, $usr);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            } else {
                // حذف أي سجلات قديمة
                mysqli_query($con, "DELETE FROM temp_product_image WHERE tpi_usr_id = $usr");
                
                if ($typ === 'product') {
                    $insert_sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_image, tpi_upload_date) VALUES (?, ?, NOW())";
                } else {
                    $insert_sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_logo, tpi_upload_date) VALUES (?, ?, NOW())";
                }
                $stmt_insert = mysqli_prepare($con, $insert_sql);
                mysqli_stmt_bind_param($stmt_insert, 'is', $usr, $imgNames);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
            
            break;
            
        case 'products_edit':
            // إضافة صور إلى منتج محدد
            if ($pd_id <= 0) {
                throw new Exception("Invalid product ID");
            }
            
            if (empty($imgArr)) {
                throw new Exception("No images selected");
            }
            
            // جلب معلومات المنتج الحالي
            $pd_sql = "SELECT pd_image, pd_imagelogo FROM products WHERE pd_id = ? LIMIT 1";
            $stmt_pd = mysqli_prepare($con, $pd_sql);
            mysqli_stmt_bind_param($stmt_pd, 'i', $pd_id);
            mysqli_stmt_execute($stmt_pd);
            $result_pd = mysqli_stmt_get_result($stmt_pd);
            $row_pd = mysqli_fetch_object($result_pd);
            mysqli_stmt_close($stmt_pd);
            
            if (!$row_pd) {
                throw new Exception("Product not found");
            }
            
            $items = [];
            foreach ($imgArr as $imgId) {
                $imgId = (int)$imgId;
                if ($imgId <= 0) continue;
                
                $row_ph = getGalleryImage($con, $imgId);
                if (!$row_ph) continue;
                
                $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
                if (!file_exists($sourcePath)) continue;
                
                $newFileName = copyImageWithThumb(
                    $sourcePath,
                    __DIR__ . "/../upload/myproduct/",
                    __DIR__ . "/../upload/myproduct/thumb/",
                    'prd',
                    $row_ph->ph_fileName
                );
                
                if ($newFileName) {
                    $items[] = $newFileName;
                }
            }
            
            if (empty($items)) {
                throw new Exception("No images were copied successfully");
            }
            
            $imgNames = implode(',', $items);
            
            if ($typ === 'product') {
                $currentImages = $row_pd->pd_image ?? '';
                $allImages = !empty($currentImages) ? $imgNames . ',' . $currentImages : $imgNames;
                $update_sql = "UPDATE products SET pd_image = ? WHERE pd_id = ?";
            } else {
                $currentImages = $row_pd->pd_imagelogo ?? '';
                $allImages = !empty($currentImages) ? $imgNames . ',' . $currentImages : $imgNames;
                $update_sql = "UPDATE products SET pd_imagelogo = ? WHERE pd_id = ?";
            }
            
            $stmt_update = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($stmt_update, 'si', $allImages, $pd_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            break;
            
        case 'temp_buyrequirement_image':
            // إضافة صورة إلى طلب شراء مؤقت
            if ($usr <= 0) {
                throw new Exception("Invalid user ID");
            }
            
            $row_ph = getGalleryImage($con, $ph_id);
            if (!$row_ph) {
                throw new Exception("Gallery image not found");
            }
            
            // جلب الصورة المؤقتة الحالية
            $tbi_sql = "SELECT tbi_image FROM temp_buyrequirement_image WHERE tbi_usr_id = ? LIMIT 1";
            $stmt_tbi = mysqli_prepare($con, $tbi_sql);
            mysqli_stmt_bind_param($stmt_tbi, 'i', $usr);
            mysqli_stmt_execute($stmt_tbi);
            $result_tbi = mysqli_stmt_get_result($stmt_tbi);
            $row_tbi = mysqli_fetch_object($result_tbi);
            mysqli_stmt_close($stmt_tbi);
            
            $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
            if (!file_exists($sourcePath)) {
                throw new Exception("Source image file not found");
            }
            
            $newFileName = copyImageWithThumb(
                $sourcePath,
                __DIR__ . "/../upload/buy_requirement/",
                __DIR__ . "/../upload/buy_requirement/thumb/",
                'br',
                $row_ph->ph_fileName
            );
            
            if (!$newFileName) {
                throw new Exception("Failed to copy image");
            }
            
            // حذف الصورة المؤقتة القديمة
            if ($row_tbi && !empty($row_tbi->tbi_image)) {
                $oldPath = __DIR__ . "/../upload/buy_requirement/" . $row_tbi->tbi_image;
                $oldThumb = __DIR__ . "/../upload/buy_requirement/thumb/" . $row_tbi->tbi_image;
                
                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldThumb)) unlink($oldThumb);
            }
            
            // حذف السجل القديم وإدراج الجديد
            mysqli_query($con, "DELETE FROM temp_buyrequirement_image WHERE tbi_usr_id = $usr");
            
            $insert_sql = "INSERT INTO temp_buyrequirement_image (tbi_usr_id, tbi_image, tbi_upload_date) VALUES (?, ?, NOW())";
            $stmt_insert = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'is', $usr, $newFileName);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
            
            break;
            
        case 'buy_requirement_edit':
            // إضافة صورة إلى طلب شراء محدد
            if ($br_id <= 0) {
                throw new Exception("Invalid buy requirement ID");
            }
            
            $row_ph = getGalleryImage($con, $ph_id);
            if (!$row_ph) {
                throw new Exception("Gallery image not found");
            }
            
            // جلب معلومات طلب الشراء الحالي
            $br_sql = "SELECT br_pic FROM buy_requirement WHERE br_id = ? LIMIT 1";
            $stmt_br = mysqli_prepare($con, $br_sql);
            mysqli_stmt_bind_param($stmt_br, 'i', $br_id);
            mysqli_stmt_execute($stmt_br);
            $result_br = mysqli_stmt_get_result($stmt_br);
            $row_br = mysqli_fetch_object($result_br);
            mysqli_stmt_close($stmt_br);
            
            if (!$row_br) {
                throw new Exception("Buy requirement not found");
            }
            
            $sourcePath = __DIR__ . "/../upload/image_gallery/" . $row_ph->ph_fileName;
            if (!file_exists($sourcePath)) {
                throw new Exception("Source image file not found");
            }
            
            $newFileName = copyImageWithThumb(
                $sourcePath,
                __DIR__ . "/../upload/buy_requirement/",
                __DIR__ . "/../upload/buy_requirement/thumb/",
                'br',
                $row_ph->ph_fileName
            );
            
            if (!$newFileName) {
                throw new Exception("Failed to copy image");
            }
            
            // حذف الصورة القديمة
            if (!empty($row_br->br_pic)) {
                $oldPath = __DIR__ . "/../upload/buy_requirement/" . $row_br->br_pic;
                $oldThumb = __DIR__ . "/../upload/buy_requirement/thumb/" . $row_br->br_pic;
                
                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldThumb)) unlink($oldThumb);
            }
            
            // تحديث قاعدة البيانات
            $update_sql = "UPDATE buy_requirement SET br_pic = ? WHERE br_id = ?";
            $stmt_update = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($stmt_update, 'si', $newFileName, $br_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            break;
            
        default:
            throw new Exception("Invalid table name: $tbl");
    }
    
    mysqli_commit($con);
    echo "1|Image(s) added successfully";
    
} catch (Exception $e) {
    mysqli_rollback($con);
    error_log("Add Image From Gallery Error: " . $e->getMessage());
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>