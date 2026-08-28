<?php
/**
 * File: myproduct-sell.php
 * Description: إدارة المنتجات والخدمات التي تبيعها الشركة بانتظام للحصول على تنبيهات طلبات الشراء
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من تسجيل الدخول
if ($uid == 0) {
    header("Location: sign-in.php");
    exit;
}

global $con;

// =============================================
// معالجة تحديث البيانات
// =============================================
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

if (isset($_POST['btnUpdate'])) {
    $pdby_titles = $_POST['pdby_title'] ?? [];
    $usid = (int)($_POST['usid'] ?? 0);
    
    if ($usid != $uid) {
        die("Invalid user ID");
    }
    
    // بدء المعاملة
    mysqli_begin_transaction($con);
    
    try {
        // حذف السجلات القديمة
        mysqli_query($con, "DELETE FROM product_sell WHERE pdby_uid = $usid");
        
        foreach ($pdby_titles as $title) {
            $title = trim($title);
            if (empty($title)) continue;
            
            // البحث عن التصنيف المرتبط
            $cat_sql = "SELECT pc_id FROM product_category WHERE pc_name = ? AND pc_status = '1' LIMIT 1";
            $stmt_cat = mysqli_prepare($con, $cat_sql);
            mysqli_stmt_bind_param($stmt_cat, 's', $title);
            mysqli_stmt_execute($stmt_cat);
            $cat_result = mysqli_stmt_get_result($stmt_cat);
            $cat_row = mysqli_fetch_assoc($cat_result);
            mysqli_stmt_close($stmt_cat);
            
            if ($cat_row) {
                $key_cat_id = (int)$cat_row['pc_id'];
                
                // التحقق من وجود التصنيف في تنبيهات الشراء
                $check_sql = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
                $stmt_check = mysqli_prepare($con, $check_sql);
                mysqli_stmt_bind_param($stmt_check, 'ii', $key_cat_id, $uid);
                mysqli_stmt_execute($stmt_check);
                $check_result = mysqli_stmt_get_result($stmt_check);
                
                if (mysqli_num_rows($check_result) == 0) {
                    $insert_alert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
                    $stmt_alert = mysqli_prepare($con, $insert_alert_sql);
                    mysqli_stmt_bind_param($stmt_alert, 'ii', $uid, $key_cat_id);
                    mysqli_stmt_execute($stmt_alert);
                    mysqli_stmt_close($stmt_alert);
                }
                mysqli_stmt_close($stmt_check);
            }
            
            // إدراج المنتج
            $insert_sql = "INSERT INTO product_sell (pdby_uid, pdby_title) VALUES (?, ?)";
            $stmt_insert = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'is', $uid, $title);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        }
        
        mysqli_commit($con);
        
        $msg = '<div class="save bnr mt12" id="savemsg"><strong>Products / Services You Sell Saved Successfully!</strong></div>';
        $_SESSION['msg'] = $msg;
        header("Location: myproduct-sell.php");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Product Sell Error: " . $e->getMessage() . " | User ID: $uid");
        $msg = '<div class="error bnr mt12" style="color:red;"><strong>An error occurred while saving</strong></div>';
    }
}

// =============================================
// جلب المنتجات المسجلة
// =============================================
$pname = '';
$aid = '';

$sql = "SELECT pdby_id, pdby_title FROM product_sell WHERE pdby_uid = ? ORDER BY pdby_id DESC";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
    $pname .= $row['pdby_title'] . ',';
    $aid .= $row['pdby_id'] . ',';
}
mysqli_stmt_close($stmt);

$pname = rtrim($pname, ',');
$aid = rtrim($aid, ',');
$proname = explode(",", $pname);

// توسيع المصفوفة إلى 20 عنصر
while (count($proname) < 20) {
    $proname[] = '';
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
    
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/b-v-7.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    
    <style>
        .label { color: #000 !important; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <script type="text/javascript">
    function chkalldetails() {
        var bnsprof_regno = document.getElementById('bnsprof_regno');
        var bnsprof_regauthority = document.getElementById('bnsprof_regauthority');
        var message = "";
        var valid = true;
        
        if (bnsprof_regauthority.value != '' && bnsprof_regno.value == '') {
            message = 'Registration Number is compulsory with Registration Authority.';
            bnsprof_regno.focus();
            valid = false;
        } else if (bnsprof_regno.value != "" && bnsprof_regauthority.value == '') {
            message = "Registration Authority is compulsory with Registration Number.";
            bnsprof_regauthority.focus();
            valid = false;
        }
        
        if (!valid) {
            document.getElementById('updatemessage').style.color = "red";
            document.getElementById('updatemessage').innerHTML = message;
        }
        
        return valid;
    }
    
    function add_more(id) {
        $('#hidetbl').hide();
        $('#field1').show();
    }
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <div class="inner_wrapper">
            <!-- Menu -->
            <?php include __DIR__ . '/includes/header_menu.php'; ?>
            
            <!-- القائمة الجانبية اليسرى -->
            <?php include __DIR__ . '/includes/left_menu.php'; ?>
            
            <!-- المحتوى الرئيسي -->
            <div class="w56b f1 p2b p14 blr">
                <div>
                    <h1 style="font-size:22px; font-weight:bold; direction:rtl; text-align:right;">
                        تفاصيل بروفايل الشركة
                    </h1>
                </div>
                
                <?php include __DIR__ . '/includes/business-panel.php'; ?>
                
                <div id="re_link" class="utab">
                    <span style="font-size:20px;" class="f1" 
                          title="Add Products / Services you usually Buy & Get Sell Offer Alerts to your mailbox.">
                        أكتب الأصناف المعتاده التى تبيعها شركتك لتتلقى فى بريدك أفضل إشعارات طلبات شراء لها
                    </span>
                </div>
                
                <div class="tbox" id="div_succ" style="display:none;">
                    <strong style="color:#000;">Saved Successfully!</strong>
                </div>
                
                <div style="text-align:left; width:489px;" class="" id="updatemessage">
                    <?php echo $msg; ?>
                </div>
                
                <div class="mt5">
                    <!-- نموذج تسجيل المنتجات -->
                    <form action="" name="form1" class="f12" method="post" onsubmit="return chkalldetails();">
                        <div class="frm_a clb" style="background-color:#FAF4FF">
                            <table align="left" border="0" cellpadding="4" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td class="label" valign="top">
                                            * أضف أصناف منتجات أو خدمات تجارية<br>تقوم شركتك ببيعها بشكل معتاد طول السنة
                                        </td>
                                        <td>
                                            <!-- الحقول الأساسية (10 حقول) -->
                                            <table border="0" cellpadding="4" cellspacing="0" width="70%">
                                                <tbody>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[0] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="1" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[1] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="2" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[2] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="3" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[3] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="4" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[4] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="5" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[5] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="6" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[6] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="7" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[7] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="8" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[8] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="9" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[9] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="10" maxlength="60" type="text"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <!-- الحقول الإضافية (10 حقول إضافية) -->
                                            <table id="field1" class="" border="0" cellpadding="4" cellspacing="0" width="70%" style="display:none;">
                                                <tbody>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[10] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="11" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[11] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="12" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[12] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="13" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[13] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="14" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[14] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="15" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[15] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="16" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[16] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="17" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[17] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="18" maxlength="60" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[18] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="19" maxlength="60" type="text"></td>
                                                        <td><input name="pdby_title[]" value="<?php echo htmlspecialchars($proname[19] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="a_f s_u" id="pdby_title" tabindex="20" maxlength="60" type="text"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <div class="f1 m5" id="hidetbl">
                                                <a class="f_l" onclick="add_more(<?php echo $uid; ?>);" id="ad_more1" style="cursor:pointer;" title="+ Add More">
                                                    إضافة المزيد +
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td align="left">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td width="118px;">
                                                            <input type="hidden" name="aid" id="aid" value="<?php echo htmlspecialchars($aid, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="usid" id="usid" value="<?php echo $uid; ?>">
                                                            <input name="btnUpdate" title="Update Details" value="إحفظ التغييرات" class="saps mt5" tabindex="31" type="submit">
                                                        </td>
                                                        <td>
                                                            <span id="pf_save" style="display:none; margin-left:15px; margin-top:6px;">
                                                                <img src="https://my.imimg.com/gifs-new/loading.gif" alt="" border="0" width="16" height="11">
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="clb">&nbsp;</div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="c3">&nbsp;</div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script type="text/javascript">
    $(document).ready(function($113) {
        lostFocus();
        $113('.a_f').unbind().live('keyup', function() {
            var type11 = 'Products';
            var currentField = $(this);
            
            $113(this).autocomplete("autocomplete.php", {
                selectFirst: true,
                extraParams: {type: type11},
                width: 410
            }).result(function(event, data, formatted) {
                var arr = data[0].split('>>');
                currentField.val(arr[arr.length - 1]);
            });
        });
    });
    </script>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>