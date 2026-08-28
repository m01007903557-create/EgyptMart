<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء الجلسة (لأننا سنستخدم $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين اتصال قاعدة البيانات
require_once __DIR__ . '/../lib/connect.php';

// استعلام لجلب صور البانر (سلايدر المنتجات) - مع التحقق من وجود الجدول
$banner = array();
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'banner'");
if (mysqli_num_rows($table_check) > 0) {
    $sql_banner = "SELECT * FROM banner WHERE status = 1 ORDER BY order_id ASC";
    $res_banner = mysqli_query($con, $sql_banner);
    if ($res_banner && mysqli_num_rows($res_banner) > 0) {
        while ($row = mysqli_fetch_object($res_banner)) {
            $banner[] = $row->image_path; // تأكد من اسم العمود في جدول banner
        }
    }
} else {
    // صور افتراضية لتجربة السلايدر (يمكنك تعديلها أو حذفها)
    $banner = [
        '../images/default1.jpg',
        '../images/default2.jpg',
        '../images/default3.jpg'
    ];
}
$file = 'enquiry';
include "includes/header.php";

$uid = $_SESSION['uid_indm'] ?? 0;
// باقي كود الملف...

// جلب بيانات المستخدم الحالي
$sql_own = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$uid}' AND bnsprof_uid = usr_id LIMIT 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إرسال إستفسار للشركة</title>
</head>
<body>
    <div id="body">
        <ul class="cb wide_thin_col_parent">
            <li id="wideColumn" title="Send Inquiry">
                <div id="h1"><h1>إرسـل أى إستفســار لهـذه الشـركـة</h1></div>

                <div id="breadcrumb">
                    <ul>
                        <li>
                            <a href="<?php echo htmlspecialchars($row->bnsprof_comp_url ?? ''); ?>/index.php?c=<?php echo urlencode($c ?? ''); ?>">
                                الرئيسية
                            </a>
                            <b>»</b>
                        </li>
                        <li>تواصـل مع الشركـة</li>
                    </ul>
                </div>
                <br><br>

                <script>
                function sendEnquiry() {
                    var msg_message = document.getElementById('msg_message');
                    var msg = "";
                    var valid = true;

                    if (msg_message.value == '' || msg_message.value == null) {
                        msg = "من فضلك إكتب رسالتك";
                        valid = false;
                    } else if (msg_message.value.length < 50) {
                        msg = "! رجاء لاتقل الرسالة عن 50 حرف";
                        msg_message.focus();
                        valid = false;
                    }

                    if (valid == false) {
                        alert(msg);
                        msg_message.focus();
                    } else {
                        $("#msg_message").attr('readonly', 'readonly');
                        $("#inp_butt").css("display", "block");
                        $("#loading").css("display", "block");

                        $.post("../email/contactsendMessage.php", {
                            msg_from: $('#msg_from').val(),
                            msg_to: $('#msg_to').val(),
                            msg_subject: $('#msg_subject').val(),
                            msg_message: msg_message.value
                        }, function(data) {
                            if (data == 1) {
                                setTimeout(function() {
                                    $("#loading").css("display", "none");
                                    $("#succ_result").css("display", "block");
                                }, 500);
                            } else {
                                setTimeout(function() {
                                    $("#loading").css("display", "none");
                                    $("#err_result").css("display", "block");
                                }, 500);
                            }
                        });
                    }
                }

                function wrng_sendEnquiry() {
                    alert("لايمكـن أن ترسـل رسـالة لنفسـك");
                }
                </script>

                <form action="" method="POST" name="dataform" enctype="multipart/form-data">
                    <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $uid; ?>">
                    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)($row->usr_id ?? 0); ?>">
                    <input type="hidden" id="msg_subject" name="msg_subject" value="Business Enquiry">

                    <section id="inq" class="cb">
                        <h1>
                            إلى :
                            <b><?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></b>
                            -
                            <span><?php echo htmlspecialchars(get_country_name((int)($row->country ?? 0))); ?></span>
                        </h1>

                        <div class="inqT cb">
                            <div class="inq-details">
                                <div class="inq-field">
                                    <label for="S_salute">
                                        الإسـم <span class="star">*</span> <b class="fr">:</b>
                                    </label>

                                    <select name="S_salute" id="S_salute" readonly="readonly">
                                        <option value="Mr." <?php echo (($row_own->name_prefix ?? '') == 'Mr.') ? 'selected' : ''; ?>>Mr.</option>
                                        <option value="Ms." <?php echo (($row_own->name_prefix ?? '') == 'Ms.') ? 'selected' : ''; ?>>Ms.</option>
                                        <option value="Mrs." <?php echo (($row_own->name_prefix ?? '') == 'Mrs.') ? 'selected' : ''; ?>>Mrs.</option>
                                        <option value="Dr." <?php echo (($row_own->name_prefix ?? '') == 'Dr.') ? 'selected' : ''; ?>>Dr.</option>
                                    </select>
                                    <input
                                        name="S_name"
                                        id="S_name"
                                        value="<?php echo htmlspecialchars($row_own->fname ?? ''); ?>"
                                        placeholder="First Name"
                                        class="txtfn_p"
                                        readonly="readonly"
                                    >

                                    <input
                                        name="S_lname"
                                        id="S_lname"
                                        value="<?php echo htmlspecialchars($row_own->lname ?? ''); ?>"
                                        placeholder="Last Name"
                                        type="text"
                                        class="txtfn_p"
                                        readonly="readonly"
                                    >
                                </div>

                                <!-- <div class="inq-name-fields">
                                    
                                </div> -->

                                <div class="inq-message">
                                    <label for="msg_message">
                                        تفاصيـل طلبـك <span class="star">*</span>
                                    </label>

                                    <textarea
                                        name="msg_message"
                                        id="msg_message"
                                        rows="5"
                                        class="w30"
                                    ></textarea>
                                </div>
                            </div>

                        </div>

                        <div class="inq-contact">
                            <div class="dte">
                                <div class="inq-contact-row">
                                    <div class="inq-label">
                                        <label for="S_email">
                                            <b class="fr">:</b>
                                            العنـوان البريـدى <span class="star">*</span>
                                        </label>
                                    </div>

                                    <div class="inq-control">
                                        <input
                                            type="email"
                                            name="S_email"
                                            id="S_email"
                                            value="<?php echo htmlspecialchars($row_own->email ?? ''); ?>"
                                            readonly="readonly"
                                            class="txtfn_p"
                                        >
                                        <span id="loader"></span>
                                    </div>
                                </div>

                                <div class="inq-contact-row">
                                    <div class="inq-label">
                                        <label for="S_organization">
                                            <b class="fr">:</b>
                                            إسـم الشركـة <span class="star">*</span>
                                        </label>
                                    </div>

                                    <div class="inq-control">
                                        <input
                                            name="S_organization"
                                            id="S_organization"
                                            value="<?php echo htmlspecialchars($row_own->bnsprof_compname ?? ''); ?>"
                                            readonly="readonly"
                                            class="txtfn_p"
                                        >
                                    </div>
                                </div>

                                <div class="inq-contact-row">
                                    <div class="inq-label">
                                        <label for="S_country">
                                            <b class="fr">:</b>
                                            الـبــلد
                                        </label>
                                    </div>

                                    <div class="inq-control">
                                        <input
                                            type="text"
                                            class="ui-autocomplete-input txtfn_p"
                                            value="<?php echo htmlspecialchars(get_country_name((int)($row_own->country ?? 0))); ?>"
                                            readonly="readonly"
                                            autocomplete="off"
                                            id="S_country"
                                            name="S_country"
                                        >
                                    </div>
                                </div>

                                <div class="inq-contact-row">
                                    <div class="inq-label">
                                        <label for="S_city">
                                            <b class="fr">:</b>
                                            إسـم المدينـة <span class="star">*</span>
                                        </label>
                                    </div>

                                    <div class="inq-control">
                                        <?php
                                        $city_name = '';

                                        if (!empty($row_own->bnsprof_city) && $row_own->bnsprof_city != '0') {
                                            $city_name = get_city_name((int)$row_own->bnsprof_city);
                                        }
                                        ?>

                                        <input
                                            name="S_city"
                                            id="S_city"
                                            value="<?php echo htmlspecialchars($city_name); ?>"
                                            readonly="readonly"
                                            class="txtfn_p"
                                        >
                                    </div>
                                </div>

                                <div id="state-container" class="inq-contact-row">
                                    <div class="inq-label">
                                        <label for="S_mobile">
                                            <b class="fr">:</b>
                                            الجوال <span class="star">*</span>
                                        </label>
                                    </div>

                                    <div class="inq-control inq-phone">
                                        <input
                                            type="text"
                                            name="S_cmobile"
                                            id="S_cmobile"
                                            class="txtfn_p"
                                            value="<?php echo htmlspecialchars($row_own->country_ph_code ?? ''); ?>"
                                            readonly="readonly"
                                        >

                                        <input
                                            name="S_mobile"
                                            id="S_mobile"
                                            type="text"
                                            class="txtfn_p"
                                            value="<?php echo htmlspecialchars($row_own->mobile1 ?? ''); ?>"
                                            readonly="readonly"
                                        >
                                    </div>
                                </div>

                                <div class="inq-submit">
                                    <?php if (!empty($_SESSION['uid_indm']) && (int)$_SESSION['uid_indm'] != (int)($row->bnsprof_uid ?? 0)): ?>

                                        <div id="loading">
                                            <img src="../images/indicator.gif" alt="">
                                        </div>

                                        <div id="succ_result">
                                            <font color="#009900">.. تم الإرسال بنجاح</font>
                                        </div>

                                        <div id="err_result">
                                            <font color="#FF0000">حدث خطأ أثناء الإرسال .. رجاء المحاولة لاحقا</font>
                                        </div>

                                        <div id="inp_butt" style="margin-top:10px;">
                                            <p class="jSea">
                                                <a
                                                    href="javascript:sendEnquiry();"
                                                    rel="product-send-inquiry"
                                                    class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px"
                                                >
                                                    إرسل إستفسارك
                                                </a>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <p class="jSea">
                                                <a
                                                    href="javascript:wrng_sendEnquiry();"
                                                    rel="product-send-inquiry"
                                                    class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px"
                                                >
                                                    إرسـل طلبـك
                                                </a>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <aside class="inq-help">
                                <h2>إوصف طلبـك للشـركة</h2>

                                <ul>
                                    <li>طلبات المنتجات</li>
                                    <li>المواصفات المطلوبة</li>
                                    <li>طريقة التغليف والتعبئة</li>
                                    <li>مكان وموعد التسليم الخ</li>
                                </ul>
                            </aside>
                        </div>
                    </section>
                </form>
                
            </li>

            <?php include "includes/right.php"; ?>
        </ul>
    </div>
    
    <br><br>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>