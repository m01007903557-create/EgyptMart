<?php
session_start();
require_once __DIR__ . '/lib/connect.php';
require_once __DIR__ . '/common.php';

global $con;

function resolveSaleOfferImagePath($so_pic_value, $prefix = '') {
    $so_pic_clean = ltrim(trim((string)$so_pic_value), '/');
    $so_pic_base = basename($so_pic_clean);
    if ($so_pic_base === '' || $so_pic_base === 'no-image.png') {
        return $prefix . 'upload/sale_offer/no-image.png';
    }

    $candidate_paths = array();
    $candidate_paths[] = 'upload/sale_offer/' . $so_pic_base;
    if (strpos($so_pic_clean, '/') !== false) {
        $candidate_paths[] = $so_pic_clean;
    }
    $candidate_paths[] = 'upload/image_gallery/' . $so_pic_base;

    if (preg_match('/^gallery(?:_so|_br)?_\\d+_(.+)$/', $so_pic_base, $legacy_match)) {
        $legacy_base = basename($legacy_match[1]);
        $candidate_paths[] = 'upload/sale_offer/' . $legacy_base;
        $candidate_paths[] = 'upload/image_gallery/' . $legacy_base;
    }

    foreach ($candidate_paths as $candidate_path) {
        $candidate_path = ltrim($candidate_path, '/');
        if (is_file(__DIR__ . '/' . $candidate_path)) {
            $expected_sale_path = __DIR__ . '/upload/sale_offer/' . $so_pic_base;
            if ($so_pic_base !== '' && $candidate_path !== 'upload/sale_offer/' . $so_pic_base && !is_file($expected_sale_path)) {
                $expected_dir = dirname($expected_sale_path);
                if (!is_dir($expected_dir)) {
                    mkdir($expected_dir, 0755, true);
                }
                @copy(__DIR__ . '/' . $candidate_path, $expected_sale_path);
            }
            return $prefix . $candidate_path;
        }
    }

    return $prefix . 'upload/sale_offer/' . $so_pic_base;
}

if (isset($_GET['image_lookup'])) {
    header('Content-Type: application/json; charset=utf-8');
    $lookup_param = isset($_GET['id']) ? (string)$_GET['id'] : '';
    $lookup_hash = '';
    if ($lookup_param !== '') {
        if (preg_match_all('/([a-f0-9]{32})/i', $lookup_param, $matches) && !empty($matches[1])) {
            $lookup_hash = strtolower(end($matches[1]));
        } elseif (is_numeric($lookup_param)) {
            $lookup_hash = md5((string)(int)$lookup_param);
        }
    }
    if ($lookup_hash === '') {
        echo json_encode(array('success' => false));
        exit;
    }
    $sql_lookup = "SELECT so_pic FROM sale_offer WHERE MD5(so_id) = ? LIMIT 1";
    $stmt_lookup = mysqli_prepare($con, $sql_lookup);
    mysqli_stmt_bind_param($stmt_lookup, 's', $lookup_hash);
    mysqli_stmt_execute($stmt_lookup);
    $result_lookup = mysqli_stmt_get_result($stmt_lookup);
    $row_lookup = mysqli_fetch_assoc($result_lookup);
    mysqli_stmt_close($stmt_lookup);
    if (!$row_lookup) {
        echo json_encode(array('success' => false));
        exit;
    }
    echo json_encode(array(
        'success' => true,
        'image' => resolveSaleOfferImagePath($row_lookup['so_pic'])
    ));
    exit;
}

// ============================================================
// ✅ استخراج so_id من الرابط
// ============================================================
$id_param = isset($_GET['id']) ? $_GET['id'] : '';
$so_id = 0;

if (!empty($id_param)) {
    if (is_numeric($id_param)) {
        $so_id = (int)$id_param;
    } else {
        // ✅ التصحيح: sale_offer (بدون s)
        $sql = "SELECT so_id FROM sale_offer WHERE MD5(so_id) = '$id_param' LIMIT 1";
        $result = mysqli_query($con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row_id = mysqli_fetch_assoc($result);
            $so_id = (int)$row_id['so_id'];
        }
    }
}

// التحقق من وجود معرف عرض البيع
if (!isset($_GET['id'])) {
    header("Location: sale-offers.php");
    exit;
}

$so_hash = substr($_GET['id'], 4);

// جلب بيانات عرض البيع (هذا الاستعلام صحيح لأنه يستخدم sale_offer)
$sql = "SELECT so.*, pc.*, u.*, bp.* 
        FROM sale_offer so
        INNER JOIN product_category pc ON so.so_pc_id = pc.pc_id
        INNER JOIN user u ON so.so_usr_id = u.usr_id
        INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE MD5(so.so_id) = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $so_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Sale offer not found");
}

// ✅ إذا لم يتم العثور على so_id من الرابط، استخدم so_id من قاعدة البيانات
if ($so_id == 0 && isset($row->so_id)) {
    $so_id = (int)$row->so_id;
}

// جلب معلومات التصنيف
$sql_pcat = "SELECT m.pc_id as main_id, m.pc_name as main_name,
                    c.pc_id as cat_id, c.pc_name as cat_name,
                    s.pc_name as subcat_name
             FROM product_category m
             INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
             INNER JOIN product_category s ON c.pc_id = s.pc_parent_id
             WHERE s.pc_id = ? 
             LIMIT 1";

$stmt_pcat = mysqli_prepare($con, $sql_pcat);
mysqli_stmt_bind_param($stmt_pcat, 'i', $row->so_pc_id);
mysqli_stmt_execute($stmt_pcat);
$result_pcat = mysqli_stmt_get_result($stmt_pcat);
$row_pcat = mysqli_fetch_assoc($result_pcat);
mysqli_stmt_close($stmt_pcat);

// تنظيف البيانات للعرض
$so_service = htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8');
$so_description = htmlspecialchars(stripslashes($row->so_description ?? ''), ENT_QUOTES, 'UTF-8');
$so_pic = !empty($row->so_pic) ? htmlspecialchars($row->so_pic, ENT_QUOTES, 'UTF-8') : 'no-image.png';
$so_pic_src = resolveSaleOfferImagePath($row->so_pic ?? '');
$so_pic_src = htmlspecialchars($so_pic_src, ENT_QUOTES, 'UTF-8');
$approval_date = !empty($row->so_approval_date) ? date("d M, Y", strtotime($row->so_approval_date)) : 'N/A';
$updated_date = !empty($row->so_updated_date) ? date("d M, Y", strtotime($row->so_updated_date)) : 'N/A';
$bnsprof_compname = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$bnsprof_yoe = (int)($row->bnsprof_yoe ?? 0);
$name_prefix = htmlspecialchars($row->name_prefix ?? '', ENT_QUOTES, 'UTF-8');
$fname = htmlspecialchars($row->fname ?? '', ENT_QUOTES, 'UTF-8');
$lname = htmlspecialchars($row->lname ?? '', ENT_QUOTES, 'UTF-8');
$country_id = (int)($row->country ?? 0);
$country_name = htmlspecialchars(get_country_name($country_id), ENT_QUOTES, 'UTF-8');
$country_flag = htmlspecialchars(get_country_flag($country_id), ENT_QUOTES, 'UTF-8');
$mobile1 = htmlspecialchars($row->mobile1 ?? '', ENT_QUOTES, 'UTF-8');
$country_ph_code = htmlspecialchars($row->country_ph_code ?? '', ENT_QUOTES, 'UTF-8');
$usr_id = (int)($row->usr_id ?? 0);

// حساب سنوات العضوية
$membership_years = 0;
if ($bnsprof_yoe > 0) {
    $membership_years = (int)date("Y") - $bnsprof_yoe;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">

    <link href="css/trade-detail1.css" rel="stylesheet" type="text/css">
    <link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">

    <style>
        .lbx1 { width: 76% !important; }
        .q_f1.wdl { width: 22% !important; }
        .form-container { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .form-caption { font-weight: bold; margin-bottom: 10px; }
        .form-tagname { font-weight: bold; margin-bottom: 5px; }
        .form-textarea { width: 100%; height: 100px; padding: 5px; border: 1px solid #ccc; border-radius: 3px; }
        .sndb { background-color: #4CAF50; color: white; padding: 10px; border: none; border-radius: 3px; cursor: pointer; }
        .sndb:hover { background-color: #45a049; }
    </style>

    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>

    <script>
    function sendEnquiry() {
        var msg_from = document.getElementById('msg_from');
        var msg_to = document.getElementById('msg_to');
        var msg_subject = document.getElementById('msg_subject');
        var msg_message = document.getElementById('msg_message');
        var valid = true;

        if (msg_message.value == '' || msg_message.value == null) {
            alert("من فضلك اكتب الاستفسار");
            valid = false;
        } else if (msg_message.value.length < 20) {
            alert("الرسالة تحتاج على الأقل الى 20 حرف حتى تستطيع إرسالها");
            msg_message.focus();
            valid = false;
        }

        if (valid) {
            $("#enqloading").css("display", "block");
            $("#enqloading1").css("display", "none");

            $.post("ajax-file/sendMessage.php", {
                msg_from: msg_from.value,
                msg_to: msg_to.value,
                msg_subject: msg_subject.value,
                msg_message: msg_message.value
            }, function(data) {
                if (data == 1) {
                    setTimeout(function() {
                        alert('... تم إرسال إستفسارك بنجاح');
                        $("#enqloading").css("display", "none");
                        $("#enqloading1").css("display", "block");
                        msg_message.value = "";
                    }, 500);
                } else {
                    setTimeout(function() {
                        alert('لم يتم إرسال إستفسارك .. رجاء المحاولة مرة أخرى لاحقا');
                        $("#enqloading").css("display", "none");
                        $("#enqloading1").css("display", "block");
                    }, 500);
                }
            });
        }
    }
    </script>
</head>
<body>
    <div class="q_hm1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>

        <br>

        <!-- مسار التنقل (Breadcrumb) -->
        <div class="p4 cbc">
            <a href="sale-offers.php" class="td_n">العروض الخاصة</a> 
             >  
            <a class="td_n cbc"><?php echo htmlspecialchars(ucwords($row_pcat['main_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
             >  
            <a class="td_n cbc"><?php echo htmlspecialchars(ucwords($row_pcat['cat_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
             >  
            <?php echo htmlspecialchars(ucwords($row_pcat['subcat_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div class="thd"><p class="c3"></p></div>

        <div id="tpfrm" class="p4 mt4" style="_position:static !important">
            <!-- القسم الأيسر (تفاصيل المنتج) -->
            <div class="lbx1 q_f1 pr mnh wb" itemscope="" itemid="#product" style="min-height:720px">
                <h1 class="cbc bo f6 mt4 ml14 a1 lh25">
                    <span itemprop="name"><?php echo $so_service; ?></span>
                </h1>
                <br class="m2">
                <br>

                <table class="" align="center" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td class="vam rimgbx big-img">
                                <img src="<?php echo $so_pic_src; ?>" 
                                     alt="<?php echo $so_service; ?>" width="100%" itemprop="image" id="100000">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="pdh bo ml20 mr20">
                    <span class="j1 g5 c4 z1">
                        Offer Active Since: <?php echo $approval_date; ?> | Last Updated: <?php echo $updated_date; ?>
                    </span>
                    <span class="f2">Offer Details:</span>
                </div>

                <div class="w1 lh22 f2 txtn ml20 mr20 cnt_t">
                    <div itemprop="description" style="overflow:auto; font-size:16px;">
                        <?php echo nl2br($so_description); ?>
                    </div>
                </div>
                <br><br><br>
            </div>

            <!-- القسم الأيمن (معلومات المورد) -->
            <div class="q_f1 wdl">
                <p class="c3"></p>

                <div style="position:relative;" id="rtmain1">
                    <div class="rit_ar">
                        <div id="topref" style="_position:static !important">
                            <p class="m2"></p>
                            <p class="tr mr6 w1">
                                <?php if ($membership_years > 0): ?>
                                <span title="<?php echo $membership_years; ?> year of Membership" 
                                      class="opacity b1 vam mems tc">
                                    <span title="<?php echo $membership_years; ?> year of Membership" class="sp-mem1">
                                        <?php echo $membership_years; ?>
                                        <span class="sp-mem2">yr</span>
                                    </span>
                                </span>
                                <?php endif; ?>
                            </p>

                            <p class="vcl txl ef3 lh21 pr2 rd f2 bo ml27">
                                <?php echo $bnsprof_compname; ?>
                                <?php if ($bnsprof_yoe > 0): ?>
                                <span class="estd" style="margin-top:3px">
                                    (Estd. <span style="margin-left:5px"><?php echo $bnsprof_yoe; ?></span>)
                                </span>
                                <?php endif; ?>
                            </p>

                            <p itemprop="address" itemscope="" 
                               class="txl txt1 vcl3 mt5 cn_cl ml27 lh21">
                                <?php echo trim($name_prefix . ' ' . $fname . ' ' . $lname); ?><br>

                                <?php if ($country_id > 0): ?>
                                <span itemprop="addressCountry">
                                    <?php echo $country_name; ?> 
                                    <img src="images/country_flag/<?php echo $country_flag; ?>" 
                                         alt="" class="w4" align="top" height="15" width="23">
                                </span>
                                <?php endif; ?>
                            </p>

                            <!-- ✅ زر "طلب سعر واتساب" -->
                           <<!-- ✅ زر "طلب سعر واتساب" -->
<?php if (isset($_SESSION['uid_indm']) && !empty($mobile1) && $mobile1 != '0' && isset($so_id) && $so_id > 0): ?>
<p class="mt2 ml27 cn_cl">
    <a href="javascript:void(0)" 
       onclick="openWaRfqOffer(<?php echo $so_id; ?>, '<?php echo addslashes($so_service); ?>', '<?php echo (int)$usr_id; ?>')" 
       style="background:#25D366; color:white; padding:8px 18px; border-radius:5px; text-decoration:none; display:inline-block; font-weight:bold; font-size:14px;">
        <i class="fa fa-whatsapp"></i> 📱 طلب سعر واتساب
    </a>
</p>
<?php endif; ?>
                            <br><br>
                            <div class="rot_ar sbg a1"></div>

                            <!-- زر إرسال إيميل -->
                            <?php if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != $usr_id): ?>
                            <div name="logein" id="TP" class="form-container">
                                <a id="clx" style="display:none" class="clx1" href="#TP">
                                    <div id="cls" class="form-close">
                                        <img alt="" src="images/zero.gif" class="bg close-image" border="0" height="16" width="16">
                                    </div>
                                </a>
                                <p class="form-caption">تواصل مع المورد بإرسال ميل</p>

                                <div class="form-block">
                                    <p class="form-tagname">: الرسالة</p>
                                    <input type="hidden" id="msg_from" name="msg_from" 
                                           value="<?php echo (int)$_SESSION['uid_indm']; ?>">
                                    <input type="hidden" id="msg_to" name="msg_to" 
                                           value="<?php echo $usr_id; ?>">
                                    <input type="hidden" id="msg_subject" name="msg_subject" 
                                           value="Enquiry for '<?php echo addslashes($so_service); ?>'">
                                    <textarea id="msg_message" name="msg_message" 
                                              class="form-textarea" maxlength="1000"></textarea>
                                </div>

                                <center>
                                    <div id="enqloading1" style="width:221px;">
                                        <input onClick="sendEnquiry();" class="point sndb" 
                                               value="! تواصل مع هذا المورد الآن" name="" 
                                               style="font-size:15px; width:221px;" type="button">
                                    </div>
                                    <div style="text-align:center; display:none; margin:14px 0;" 
                                         class="bo" id="enqloading">
                                        <img src="images/indicator.gif" align="absmiddle">  ... جارى الإرسال
                                    </div>
                                </center>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="c3"></p>
                </div>
            </div>

            <p class="c3"><img src="images/zero.gif" alt="" height="1" width="1"></p>
        </div>

        <div class="p4 w2">
            <p class="m2"></p>
        </div>

        <p class="c3"></p>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

<!-- ========================================================== -->
<!-- ✅ نوافذ متتالية لعروض البيع - Sale Offer -->
<!-- ========================================================== -->
<script>
// ============================================================
// ✅ متغيرات عامة
// ============================================================
var currentOfferId = 0;
var currentOfferTitle = '';
var currentSupplierId = 0;
var stepData = {};

// ============================================================
// ✅ الدالة الرئيسية - تبدأ النوافذ المتتالية
// ============================================================
function openWaRfqOffer(offerId, offerTitle, supplierId) {
    console.log('📱 فتح نوافذ واتساب للعرض:', offerId, offerTitle);
    
    if (!offerId || offerId == 0) {
        alert('❌ خطأ: معرف العرض غير موجود');
        return;
    }

    currentOfferId = offerId;
    currentOfferTitle = offerTitle;
    currentSupplierId = supplierId;
    stepData = {};

    showStep1();
}

// ============================================================
// ✅ النافذة 1
// ============================================================
function showStep1() {
    removeModal();
    
    var html = `
    <div id="waModalStep" style="position:fixed;z-index:999999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.7);direction:rtl;font-family:Tahoma,Arial,sans-serif;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;width:90%;max-width:400px;padding:35px;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.3);text-align:center;">
            <div style="background:#25D366;width:70px;height:70px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:15px;">
                <i class="fa fa-whatsapp" style="font-size:35px;color:white;"></i>
            </div>
            <h3 style="margin:0 0 10px;color:#075e54;font-size:20px;">🤔 هل تريد الحصول على أفضل سعر لهذا العرض؟</h3>
            <p style="color:#666;font-size:14px;margin-bottom:25px;">العرض: <strong style="color:#25D366;">${currentOfferTitle}</strong></p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button onclick="step1Answer(true)" style="flex:1;background:#25D366;color:#fff;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;">✅ نعم</button>
                <button onclick="step1Answer(false)" style="flex:1;background:#e0e0e0;color:#333;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;">❌ لا</button>
            </div>
            <div style="margin-top:15px;font-size:12px;color:#999;">الخطوة 1 من 3</div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function step1Answer(answer) {
    stepData.wantPrice = answer;
    removeModal();
    showStep2();
}

// ============================================================
// ✅ النافذة 2
// ============================================================
function showStep2() {
    var html = `
    <div id="waModalStep" style="position:fixed;z-index:999999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.7);direction:rtl;font-family:Tahoma,Arial,sans-serif;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;width:90%;max-width:400px;padding:35px;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.3);text-align:center;">
            <div style="background:#075e54;width:70px;height:70px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:15px;">
                <i class="fa fa-comments" style="font-size:35px;color:white;"></i>
            </div>
            <h3 style="margin:0 0 10px;color:#075e54;font-size:20px;">💬 هل تريد محادثة المورد؟</h3>
            <p style="color:#666;font-size:14px;margin-bottom:25px;">سيتم التواصل معك عبر واتساب ولوحة التحكم</p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button onclick="step2Answer(true)" style="flex:1;background:#075e54;color:#fff;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;">✅ نعم</button>
                <button onclick="step2Answer(false)" style="flex:1;background:#e0e0e0;color:#333;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;">❌ لا</button>
            </div>
            <div style="margin-top:15px;font-size:12px;color:#999;">الخطوة 2 من 3</div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function step2Answer(answer) {
    stepData.wantContact = answer;
    removeModal();
    showStep3();
}

// ============================================================
// ✅ النافذة 3: التأكيد
// ============================================================
function showStep3() {
    var html = `
    <div id="waModalStep" style="position:fixed;z-index:999999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.7);direction:rtl;font-family:Tahoma,Arial,sans-serif;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;width:90%;max-width:400px;padding:35px;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.3);text-align:center;">
            <div style="background:#ff9800;width:70px;height:70px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:15px;">
                <i class="fa fa-check-circle" style="font-size:35px;color:white;"></i>
            </div>
            <h3 style="margin:0 0 10px;color:#075e54;font-size:20px;">✅ تم تأكيد طلبك</h3>
            <div style="text-align:right;background:#f5f5f5;padding:15px;border-radius:8px;margin-bottom:20px;font-size:14px;">
                <p style="margin:5px 0;"><strong>📦 العرض:</strong> ${currentOfferTitle}</p>
                <p style="margin:5px 0;"><strong>💰 أفضل سعر:</strong> ${stepData.wantPrice ? '✅ نعم' : '❌ لا'}</p>
                <p style="margin:5px 0;"><strong>💬 محادثة المورد:</strong> ${stepData.wantContact ? '✅ نعم' : '❌ لا'}</p>
            </div>
            <p style="color:#666;font-size:14px;margin-bottom:25px;line-height:1.8;">📱 سيتواصل معك المورد عبر <strong>واتساب</strong> وفي <strong>لوحة التحكم</strong> خلال فترة قصيرة</p>
            <button onclick="submitOfferRequest()" style="width:100%;background:#25D366;color:#fff;border:none;padding:14px 25px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;">📤 إرسال الطلب</button>
            <div style="margin-top:15px;font-size:12px;color:#999;">الخطوة 3 من 3</div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

// ============================================================
// ✅ إزالة النافذة
// ============================================================
function removeModal() {
    var existing = document.getElementById('waModalStep');
    if (existing) existing.remove();
}

// ============================================================
// ✅ إرسال الطلب
// ============================================================
// ✅ الدالة المُصححة
async function submitOfferRequest() {
    // ✅ جمع البيانات
    var formData = new FormData();
    formData.append('offer_id', currentOfferId);
    formData.append('product_name', currentOfferTitle);
    formData.append('supplier_id', currentSupplierId);
    formData.append('request_type', 'saleoffer');
    formData.append('product_id', 0);
    formData.append('qty_from', 1);
    formData.append('qty_to', 1);
    formData.append('delivery_days', 7);

    var details = '';
    details += 'هل تريد أفضل سعر؟ ' + (stepData.wantPrice ? 'نعم' : 'لا') + '\n';
    details += 'هل تريد محادثة المورد؟ ' + (stepData.wantContact ? 'نعم' : 'لا');
    formData.append('requirement_details', details);

    var btn = document.querySelector('#waModalStep button');
    if (btn) {
        btn.disabled = true;
        btn.textContent = '⏳ جاري الإرسال...';
    }

    try {
        var res = await fetch('/saleoffer_whatsapp_handler.php', {
    method: 'POST',
    body: formData
});

        console.log('📥 Status:', res.status);
        var text = await res.text();
        console.log('📥 Raw Response:', text);

        if (!text || text.trim() === '') {
            throw new Error('الاستجابة فارغة من الخادم');
        }

        try {
            var data = JSON.parse(text);
            console.log('📥 JSON:', data);

            if (data.success) {
                alert('✅ تم إرسال طلب السعر بنجاح!');
                if (data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank');
                }
                removeModal();
            } else {
                alert('❌ ' + (data.error || 'حدث خطأ'));
            }
        } catch (e) {
            console.error('❌ JSON Parse Error:', e);
            alert('❌ خطأ في تحليل الاستجابة: ' + text.substring(0, 200));
        }

        if (btn) {
            btn.disabled = false;
            btn.textContent = '📤 إرسال الطلب';
        }
    } catch (error) {
        console.error('❌ Fetch Error:', error);
        alert('❌ خطأ في الاتصال بالخادم: ' + error.message);
        if (btn) {
            btn.disabled = false;
            btn.textContent = '📤 إرسال الطلب';
        }
    }
}

document.addEventListener('click', function(e) {
    var modal = document.getElementById('waModalStep');
    if (modal && e.target === modal) {
        if (confirm('هل تريد إلغاء طلب السعر؟')) {
            removeModal();
        }
    }
});

console.log('✅ WhatsApp RFQ - Sale Offer جاهز');
</script>

<script src="../js/whatsapp_handler.js"></script>
</body>
</html>
