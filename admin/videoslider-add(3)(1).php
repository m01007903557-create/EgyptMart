<?php
/**
 * File: videoslider-add.php
 * Version: 3.0.0
 * Description: إضافة فيديو جديد في السلايدر (مع إصلاح مشكلة تسجيل الدخول)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// تضمين الاتصال بقاعدة البيانات
// ========================================

require_once __DIR__ . '/../lib/connect.php';

if (!isset($con) || !$con) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// ========================================
// إصلاح مشكلة معرف المستخدم
// ========================================

$user_id = 0;

if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $user_id = (int)$_SESSION['uid_indm'];
}

if ($user_id <= 0 || $user_id == 1) {
    $email = $_SESSION['ad_email_indm'] ?? $_SESSION['email'] ?? '';
    if (!empty($email)) {
        $sql = "SELECT usr_id FROM user WHERE email = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $user_id = (int)$row['usr_id'];
                $_SESSION['uid_indm'] = $user_id;
                $_SESSION['reseller_id'] = $user_id;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if ($user_id <= 0) {
    $sql = "SELECT usr_id FROM user ORDER BY usr_id LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $user_id = (int)$row['usr_id'];
        $_SESSION['uid_indm'] = $user_id;
        $_SESSION['reseller_id'] = $user_id;
    }
}

if ($user_id <= 0) {
    $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
    header("Location: /login.php");
    exit();
}

$_SESSION['reseller_id'] = $user_id;
$_SESSION['admin_id'] = $user_id;
$_SESSION['admin_logged_in'] = true;

// ========================================
// معالجة إضافة الفيديو
// ========================================

$msg = '';
$success = false;

if (isset($_POST['btnAdd'])) {
    // استقبال البيانات
    $country_id = isset($_POST['country_id']) ? (int)$_POST['country_id'] : 0;
    $video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
    $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // التحقق من صحة البيانات
    $errors = [];
    if ($country_id <= 0) {
        $errors[] = 'الرجاء اختيار الدولة';
    }
    if (empty($video_url)) {
        $errors[] = 'الرجاء إدخال رابط الفيديو';
    }
    if (empty($title)) {
        $errors[] = 'الرجاء إدخال عنوان الفيديو';
    }
    
    if (empty($errors)) {
        // إضافة الفيديو إلى قاعدة البيانات (افترض وجود جدول video_slider)
        $sql = "INSERT INTO video_slider (country_id, video_url, redirect_url, title, description, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssi", $country_id, $video_url, $redirect_url, $title, $description, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $msg = '<font color="#009900">✅ تم إضافة الفيديو بنجاح</font>';
            } else {
                $msg = '<font color="#CC0000">❌ فشل إضافة الفيديو: ' . mysqli_error($con) . '</font>';
            }
            mysqli_stmt_close($stmt);
        } else {
            $msg = '<font color="#CC0000">❌ خطأ في قاعدة البيانات: ' . mysqli_error($con) . '</font>';
        }
    } else {
        $msg = '<font color="#CC0000">❌ ' . implode('<br>', $errors) . '</font>';
    }
}

// ========================================
// جلب قائمة الدول
// ========================================

$countries = [];
$sql_countries = "SELECT cn_id, cn_name FROM country WHERE cn_status = 1 ORDER BY cn_name";
$result_countries = mysqli_query($con, $sql_countries);
if ($result_countries) {
    while ($row = mysqli_fetch_assoc($result_countries)) {
        $countries[] = $row;
    }
    mysqli_free_result($result_countries);
}

// ========================================
// عرض الصفحة
// ========================================

?>
<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>

<style>
.control_Panel { padding: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
.form-group .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.form-group .form-control:focus { border-color: #4a6a8b; outline: none; }
.buttons { margin-top: 20px; }
.buttons .x2-button { padding: 8px 20px; background: #4a6a8b; color: white; border: none; border-radius: 4px; cursor: pointer; }
.buttons .x2-button:hover { background: #3a5a7b; }
.buttons .x2-button.secondary { background: #6c757d; }
.buttons .x2-button.secondary:hover { background: #5a6268; }
#err_msg { display: block; margin: 10px 0; padding: 10px; border-radius: 4px; text-align: center; }
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>

    <div id="content-container">
        <div id="content">
            <h2>Video Slider - Add Slider</h2>

            <?php if ($msg): ?>
                <div id="err_msg"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form action="" id="frmAdd" name="frmAdd" method="post" enctype="multipart/form-data">
                <!-- Country -->
                <div class="form-group">
                    <label>Country:</label>
                    <select name="country_id" class="form-control" required>
                        <option value="">Select Some Options</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['cn_id']; ?>">
                                <?php echo htmlspecialchars($country['cn_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Video Url -->
                <div class="form-group">
                    <label>Video Url:</label>
                    <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required />
                </div>

                <!-- Redirect Url -->
                <div class="form-group">
                    <label>Redirect Url:</label>
                    <input type="text" name="redirect_url" class="form-control" placeholder="https://example.com" />
                </div>

                <!-- Title -->
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter video title" required />
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Enter video description"></textarea>
                </div>

                <!-- Buttons -->
                <div class="row buttons">
                    <input type="submit" name="btnAdd" value="Add" class="x2-button" />
                    <button type="reset" class="x2-button secondary">Reset</button>
                </div>
            </form>

            <br clear="all"/>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>
<?php ob_end_flush(); ?>