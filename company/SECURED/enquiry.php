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

// التحقق من نجاح الاتصال
if (!isset($con) || !$con) {
    die("خطأ في الاتصال بقاعدة البيانات");
}

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
        <ul class="cb">
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
                    <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $uid; ?>" />
                    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)($row->usr_id ?? 0); ?>" />
                    <input type="hidden" id="msg_subject" name="msg_subject" value="Business Enquiry" />

                    <div id="inq" class="cb">
                        <h1>
                            إلى : <b><?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></b> - 
                            <span><?php echo htmlspecialchars(get_country_name((int)($row->country ?? 0))); ?></span>
                        </h1>
                        <br>

                        <ul class="cb inqT">
                            <li>
                                <div style="float: left">
                                    الإسـم :<span class="star">*</span> 
                                    <select name="S_salute" readonly="readonly">
                                        <option value="Mr." <?php echo (($row_own->name_prefix ?? '') == "Mr.") ? 'selected' : ''; ?>>Mr.</option>
                                        <option value="Ms." <?php echo (($row_own->name_prefix ?? '') == "Ms.") ? 'selected' : ''; ?>>Ms.</option>
                                        <option value="Mrs." <?php echo (($row_own->name_prefix ?? '') == "Mrs.") ? 'selected' : ''; ?>>Mrs.</option>
                                        <option value="Dr." <?php echo (($row_own->name_prefix ?? '') == "Dr.") ? 'selected' : ''; ?>>Dr.</option>
                                    </select>
                                </div>
                                
                                <div style="float: left; width: 200px; margin-left: 10px;">
                                    <input style="float: left; width: 80px;" name="S_name" id="S_name" 
                                           value="<?php echo htmlspecialchars($row_own->fname ?? ''); ?>" 
                                           placeholder="First Name" class="txtfn_p" readonly="readonly">
                                    <input style="float: left; width: 80px; margin-left: 10px;" name="S_lname" id="S_lname" 
                                           value="<?php echo htmlspecialchars($row_own->lname ?? ''); ?>" 
                                           placeholder="Last Name" type="text" readonly="readonly" class="txtfn_p">
                                </div>
                                <br>

                                <p style="float: left" title="Requirement Details">تفاصيـل طلبـك <span class="star">*</span></p>
                                <p><textarea name="msg_message" id="msg_message" rows="5" class="w30"></textarea></p>
                            </li>

                            <li>
                                <h2>إوصف طلبـك للشـركة</h2>
                                <ul>
                                    <li>طلبات المنتجات</li>
                                    <li>المواصفات المطلوبة</li>
                                    <li>طريقة التغليف والتعبئة</li>
                                    <li>مكان وموعد التسليم الخ</li>
                                </ul>
                            </li>
                        </ul>

                        <div class="w100 dt ac-dtr acac-dtc acac-p5px acac-vat">
                            <ul>
                                <li class="w120px">
                                    <label for="email"><b class="fr">:</b>العنـوان البريـدى <span class="star">*</span></label>
                                </li>
                                <li>
                                    <input name="S_email" value="<?php echo htmlspecialchars($row_own->email ?? ''); ?>" 
                                           readonly="readonly" class="txtfn_p">
                                    <span id="loader"></span>
                                </li>
                            </ul>

                            <ul>
                                <li class="w120px">
                                    <label for="name"><b class="fr">:</b> إسـم الشركـة<span class="star">*</span></label>
                                </li>
                                <li>
                                    <input name="S_organization" value="<?php echo htmlspecialchars($row_own->bnsprof_compname ?? ''); ?>" 
                                           readonly="readonly" class="txtfn_p">
                                </li>
                            </ul>

                            <ul>
                                <li class="w120px">
                                    <label for="company"><span class="star"></span><b class="fr">:</b> الـبــلد</label>
                                </li>
                                <li>
                                    <input type="text" style="width:201px; height:18px;" class="ui-autocomplete-input txtfn_p" 
                                           value="<?php echo htmlspecialchars(get_country_name((int)($row_own->country ?? 0))); ?>" 
                                           readonly="readonly" autocomplete="off" id="S_country" name="S_country">
                                </li>
                            </ul>

                            <ul>
                                <li class="w120px">
                                    <label for="country"><b class="fr">:</b> إسـم المدينـة <span class="star">*</span></label>
                                </li>
                                <li>
                                    <?php
                                    $city_name = '';
                                    if (!empty($row_own->bnsprof_city) && $row_own->bnsprof_city != '0') {
                                        $city_name = get_city_name((int)$row_own->bnsprof_city);
                                    }
                                    ?>
                                    <input name="S_city" id="S_city" value="<?php echo htmlspecialchars($city_name); ?>" 
                                           readonly="readonly" class="txtfn_p">
                                </li>
                            </ul>

                            <ul id="state-container" style="display:none;">
                                <li class="w120px">
                                    <label for="state"><b class="fr">:</b> الجوال <span class="star">*</span></label>
                                </li>
                                <li>
                                    <input type="text" name="S_cmobile" id="S_cmobile" size="5" readonly="readonly" 
                                           style="width:50px; height:18px;" class="txtfn_p" 
                                           value="<?php echo htmlspecialchars($row_own->country_ph_code ?? ''); ?>">
                                    <input name="S_mobile" type="text" size="15" 
                                           value="<?php echo htmlspecialchars($row_own->mobile1 ?? ''); ?>" 
                                           readonly="readonly" class="txtfn_p" style="width:138px; height:18px;">
                                </li>
                            </ul>

                            <ul>
                                <li class="w120px">&nbsp;</li>
                                <li>
                                    <?php if (!empty($_SESSION['uid_indm']) && (int)$_SESSION['uid_indm'] != (int)($row->bnsprof_uid ?? 0)): ?>
                                        <div align="center" id="inp_butt">
                                            <p class="jSea">
                                                <a href="javascript:sendEnquiry();" rel="product-send-inquiry" 
                                                   class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px">
                                                    إرسل إستفسارك
                                                </a>
                                            </p>
                                            <br><br>
                                        </div>
                                        <div align="center" style="padding-top:10px; padding-bottom:10px; display:none;" id="loading">
                                            <img src="../images/indicator.gif"/>
                                        </div>
                                        <div id="succ_result" style="display:none; font-weight:bold;" align="center">
                                            <font color="#009900">.. تم الإرسال بنجاح</font>
                                        </div>
                                        <div id="err_result" style="display:none;" align="center">
                                            <font color="#FF0000">حدث خطأ أثناء الإرسال .. رجاء المحاولة لاحقا</font>
                                        </div>
                                    <?php else: ?>
                                        <div align="center">
                                            <p class="jSea">
                                                <a href="javascript:wrng_sendEnquiry();" rel="product-send-inquiry" 
                                                   class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px">
                                                    إرسـل طلبـك
                                                </a>
                                            </p>
                                            <br><br>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>
            </li>

            <?php include "includes/right.php"; ?>
        </ul>
    </div>
    
    <br><br>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>