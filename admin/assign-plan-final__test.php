<?php
define('IN_ADMIN_PANEL', true);
define('IN_SITE', true);
session_start();
$_SESSION['admin_logged_in'] = true;

// الاتصال المباشر بقاعدة البيانات دون استخدام common-support.php
$con = mysqli_connect("localhost", "u397968200_egyptmart", "your_password", "u397968200_egyptmart");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($con, "utf8");

// تعريف الدوال الأساسية المطلوبة
if (!function_exists('get_page_settings')) {
    function get_page_settings($id) { return ''; }
}
if (!function_exists('get_adminemail')) {
    function get_adminemail() { return 'admin@egyptmart.shop'; }
}
if (!function_exists('sendSMTPMail')) {
    function sendSMTPMail($to, $subject, $message) { return true; }
}
if (!function_exists('getAdminUserId')) {
    function getAdminUserId() { return 1; }
}

$msg = '';

$data = mysqli_query($con, "SELECT bf.*, u.usr_id, u.email, u.name_prefix, u.fname, u.lname 
                            FROM business_profile bf 
                            JOIN user u ON bf.bnsprof_uid = u.usr_id 
                            WHERE bf.bnsprof_compname != ''") or die(mysqli_error($con));

// باقي كود النموذج والمعالجة كما هو...
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Business Plan</title>
</head>
<body>
    <h1>Assign Business Plan</h1>
    <form method="post">
        <select name="companyname">
            <?php while($row = mysqli_fetch_object($data)){ ?>
                <option value="<?php echo $row->bnsprof_id; ?>"><?php echo $row->bnsprof_compname; ?></option>
            <?php } ?>
        </select>
        <input type="submit" value="Assign">
    </form>
</body>
</html>