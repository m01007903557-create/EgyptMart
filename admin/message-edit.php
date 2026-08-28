<?php 
declare(strict_types=1);

ob_start();
session_start(); 
require_once "../common.php";
require_once "../lib/pagination.php";

check_admin_login();

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

$msg = '';
$msg_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
$row = null;

// جلب بيانات الرسالة
if ($msg_id > 0) {
    $sql = "SELECT * FROM message WHERE msg_id = $msg_id";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_object($result);
    
    if (!$row) {
        die('الرسالة غير موجودة');
    }
} else {
    die('معرف الرسالة غير صحيح');
}

// معالجة تحديث البيانات
if (isset($_POST['btnUpdate'])) {
    $msg_subject = addslashes(trim($_POST['msg_subject']));
    $msg_message = addslashes(trim($_POST['msg_message']));
    $current_msg_id = intval($_POST['msg_id']);
    
    // التحقق من صحة البيانات
    $valid = true;
    if ($msg_subject == "") {
        $msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Subject</div>';
        $valid = false;
    } elseif ($msg_message == "" || $msg_message == " ") {
        $msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Message Content</div>';
        $valid = false;
    }
    
    if ($valid && $current_msg_id > 0) {
        $sql = "UPDATE message SET 
                    msg_subject = '$msg_subject',
                    msg_message = '$msg_message'
                WHERE msg_id = $current_msg_id";
        
        if (mysqli_query($con, $sql)) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم تحديث الرسالة بنجاح</div>';
            header("location: message-edit.php?fid=" . $current_msg_id);
            exit();
        } else {
            $msg = '<div class="alert alert-danger">خطأ في التحديث: ' . mysqli_error($con) . '</div>';
        }
    }
}

// عرض رسالة الجلسة إن وجدت
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>

<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        <script type="text/javascript">
            function myvalid() {    
                var msg_subject = document.getElementById('msg_subject');
                var msg_message = document.getElementById('msg_message');
                var message = "";
                var valid = true;
                
                if (msg_subject.value == '' || msg_subject.value == null) {
                    message = 'Please enter Subject';
                    msg_subject.focus();
                    valid = false;
                } else if (msg_message.value == '' || msg_message.value == null) {
                    message = 'Please enter Message Content';
                    msg_message.focus();
                    valid = false;
                }
                
                if (!valid) {
                    document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                    document.getElementById('msg').className = "alert alert-danger";
                }
                return valid;
            }
        </script>
        <?php include "includes/admin-left-con.php" ?>
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>
                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="message-view.php">Manage Message</a>
                    </li>
                    <li class="active">Message Edit</li>
                </ul>
            </div>
                    
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Message
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Message Edit
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Date </label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if (isset($row->msg_date) && !empty($row->msg_date) && $row->msg_date != '0000-00-00 00:00:00') {
                                            echo date("d-M-Y (h:iA)", strtotime($row->msg_date)); 
                                        } else {
                                            echo 'تاريخ غير محدد';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">From </label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $msg_from_id = isset($row->msg_from) ? (int)$row->msg_from : 0;
                                        if ($msg_from_id > 0) {
                                            echo getUserInfo($msg_from_id, 'name_prefix') . " " . 
                                                 getUserInfo($msg_from_id, 'fname') . " " . 
                                                 getUserInfo($msg_from_id, 'lname');
                                        } else {
                                            echo 'مرسل غير معروف';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">To </label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $msg_to_id = isset($row->msg_to) ? (int)$row->msg_to : 0;
                                        if ($msg_to_id > 0) {
                                            echo getUserInfo($msg_to_id, 'name_prefix') . " " . 
                                                 getUserInfo($msg_to_id, 'fname') . " " . 
                                                 getUserInfo($msg_to_id, 'lname');
                                        } else {
                                            echo 'مستلم غير معروف';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Subject <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="msg_subject" id="msg_subject" class="col-xs-10 col-sm-6" type="text" value="<?php echo htmlspecialchars($row->msg_subject ?? ''); ?>" />
                                    <input type="hidden" name="msg_id" id="msg_id" value="<?php echo $row->msg_id; ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Content <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-8">
                                    <textarea id="msg_message" name="msg_message" class="autosize-transition form-control" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 69px;"><?php echo htmlspecialchars($row->msg_message ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
                                    <button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
                                </div>
                            </div>    
                        </form>    
                    </div>
                </div>
            </div>
            <br clear="all" />    
        </div>
        <?php include "includes/footer.php" ?>
    </div>
</div>
</body>
</html>