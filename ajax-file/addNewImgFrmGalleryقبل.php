<?php
session_start();
require_once "../common.php";

header('Content-Type: application/json');

if (empty($_SESSION['uid_indm'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

// ✅ قبول المتغيرات من POST أو GET
$image_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : (isset($_GET['br_id']) ? (int)$_GET['br_id'] : 0);
$tbl = isset($_POST['tbl']) ? $_POST['tbl'] : (isset($_GET['tbl']) ? $_GET['tbl'] : 'temp_buyrequirement_image');

// ✅ للتأكد من استلام البيانات (سجل في error_log)
error_log("===== addNewImgFrmGallery =====");
error_log("id: " . $image_id);
error_log("br_id: " . $br_id);
error_log("tbl: " . $tbl);
error_log("جميع POST: " . print_r($_POST, true));
error_log("جميع GET: " . print_r($_GET, true));

if ($image_id == 0) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال معرف الصورة']);
    exit;
}

// جلب مسار الصورة من قاعدة البيانات
$sql = "SELECT ph_fileName FROM photo WHERE ph_id = ? AND ph_status = '1'";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $image_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$photo = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$photo) {
    echo json_encode(['success' => false, 'error' => 'الصورة غير موجودة في قاعدة البيانات']);
    exit;
}

$image_path = "upload/image_gallery/" . $photo['ph_fileName'];

// حفظ الصورة في جدول مؤقت (إذا كان الجدول هو temp_buyrequirement_image)
if ($tbl == 'temp_buyrequirement_image' && $br_id > 0) {
    // تحديث أو إدراج الصورة المؤقتة
    $check_sql = "SELECT tbi_id FROM temp_buyrequirement_image WHERE tbi_usr_id = ?";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $_SESSION['uid_indm']);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) > 0) {
        // تحديث
        $update_sql = "UPDATE temp_buyrequirement_image SET tbi_image = ? WHERE tbi_usr_id = ?";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'si', $image_path, $_SESSION['uid_indm']);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        // إدراج جديد
        $insert_sql = "INSERT INTO temp_buyrequirement_image (tbi_usr_id, tbi_image) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'is', $_SESSION['uid_indm'], $image_path);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_check);
}

echo json_encode([
    'success' => true,
    'message' => 'تم اختيار الصورة بنجاح',
    'image_id' => $image_id,
    'image_path' => $image_path
]);
?>