<?php
session_start();
require_once "../common.php";

require_once __DIR__ . '/../lib/connect.php';

// ✅ ============================================================
// ✅ الكود الجديد لإضافة صورة من الجاليرى (يضاف هنا)
// ✅ ============================================================

// ✅ التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    die(json_encode(['success' => false, 'error' => 'غير مصرح']));
}

// ✅ جلب البيانات من POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$usr = isset($_POST['usr']) ? (int)$_POST['usr'] : 0;
$tbl = isset($_POST['tbl']) ? $_POST['tbl'] : '';
$br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : 0;

if ($id <= 0 || $usr <= 0 || empty($tbl)) {
    die(json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']));
}

// ✅ جلب اسم الصورة من الجاليرى
$sql = "SELECT img_name FROM $tbl WHERE id = $id AND usr_id = $usr";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($res);

if ($row) {
    $image_name = $row['img_name'];
    
    // ✅ تحديث br_pic في buy_requirement (للطلب الحالي)
    if ($br_id > 0) {
        $update_sql = "UPDATE buy_requirement SET br_pic = '$image_name' WHERE br_id = $br_id";
        mysqli_query($con, $update_sql);
        echo json_encode(['success' => true, 'message' => 'تم تحديث الصورة']);
    } else {
        echo json_encode(['success' => false, 'error' => 'معرف الطلب غير موجود']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'الصورة غير موجودة']);
}
exit;

// ✅ ============================================================
// ✅ باقي الكود القديم (إن وجد)
// ✅ ============================================================
?>



header('Content-Type: application/json');

if (empty($_SESSION['uid_indm'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$image_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : 0;
$tbl = isset($_POST['tbl']) ? $_POST['tbl'] : 'temp_buyrequirement_image';
$typ = isset($_POST['typ']) ? $_POST['typ'] : 'product'; // ✅ إضافة نوع الصورة

if ($image_id == 0) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال معرف الصورة']);
    exit;
}

// جلب مسار الصورة
$sql = "SELECT ph_fileName FROM photo WHERE ph_id = ? AND ph_status = '1'";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $image_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$photo = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$photo) {
    echo json_encode(['success' => false, 'error' => 'الصورة غير موجودة']);
    exit;
}

$image_path = "upload/image_gallery/" . $photo['ph_fileName'];

// ✅ حفظ في الجدول المؤقت حسب نوع الصورة
if ($typ == 'logo') {
    // حفظ صورة اللوجو
    $check_sql = "SELECT tpi_id FROM temp_product_image WHERE tpi_usr_id = ? AND tpi_type = 'logo'";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $_SESSION['uid_indm']);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE temp_product_image SET tpi_image = ? WHERE tpi_usr_id = ? AND tpi_type = 'logo'";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'si', $image_path, $_SESSION['uid_indm']);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        $insert_sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_image, tpi_type) VALUES (?, ?, 'logo')";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'is', $_SESSION['uid_indm'], $image_path);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_check);
} elseif ($tbl == 'temp_buyrequirement_image') {
    // حفظ صورة طلب الشراء
    $check_sql = "SELECT tbi_id FROM temp_buyrequirement_image WHERE tbi_usr_id = ?";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $_SESSION['uid_indm']);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE temp_buyrequirement_image SET tbi_image = ? WHERE tbi_usr_id = ?";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'si', $image_path, $_SESSION['uid_indm']);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        $insert_sql = "INSERT INTO temp_buyrequirement_image (tbi_usr_id, tbi_image) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'is', $_SESSION['uid_indm'], $image_path);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_check);
} else {
    // حفظ صورة المنتج (افتراضي)
    $check_sql = "SELECT tpi_id FROM temp_product_image WHERE tpi_usr_id = ? AND (tpi_type IS NULL OR tpi_type = '')";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $_SESSION['uid_indm']);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE temp_product_image SET tpi_image = ? WHERE tpi_usr_id = ? AND (tpi_type IS NULL OR tpi_type = '')";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'si', $image_path, $_SESSION['uid_indm']);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        $insert_sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_image, tpi_type) VALUES (?, ?, 'product')";
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
    'image_path' => $image_path,
    'type' => $typ
]);
?>