<?php
/**
 * File: post-buy-req-info.php
 * Description: إضافة معلومات إضافية لطلب الشراء (القيمة التقريبية، الوصف، إلخ)
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

// التحقق من وجود معرف طلب الشراء
if (!isset($_SESSION['new_br_id'])) {
    header("Location: post-buy-req.php");
    exit;
}

global $con;

// =============================================
// استرجاع بيانات الجلسة
// =============================================
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$br_apprx_order_value = $_SESSION['br_apprx_order_value'] ?? '';
$br_apprx_order_currency = $_SESSION['br_apprx_order_currency'] ?? '';
$br_description = $_SESSION['br_description'] ?? '';
$br_website = $_SESSION['br_website'] ?? '';
$br_need_quote_for = $_SESSION['br_need_quote_for'] ?? '';
$br_purchase_time = $_SESSION['br_purchase_time'] ?? '';
$br_need_for = $_SESSION['br_need_for'] ?? '';
$br_requirement_frequency = $_SESSION['br_requirement_frequency'] ?? '';

// =============================================
// كلاس إضافة معلومات طلب الشراء
// =============================================
class AddBuyReqInfo
{
    public $msg;
    public $br_id;
    public $br_apprx_order_value;
    public $br_apprx_order_currency;
    public $br_description;
    public $br_website;
    public $br_need_quote_for;
    public $br_purchase_time;
    public $br_need_for;
    public $br_requirement_frequency;
    private $con;

    public function __construct($br_id, $br_apprx_order_value, $br_apprx_order_currency, $br_description, 
                                $br_website, $br_need_quote_for, $br_purchase_time, $br_need_for, 
                                $br_requirement_frequency, $con)
    {
        $this->br_id = (int)$br_id;
        $this->br_apprx_order_value = $br_apprx_order_value;
        $this->br_apprx_order_currency = $br_apprx_order_currency;
        $this->br_description = $br_description;
        $this->br_website = $br_website;
        $this->br_need_quote_for = $br_need_quote_for;
        $this->br_purchase_time = $br_purchase_time;
        $this->br_need_for = $br_need_for;
        $this->br_requirement_frequency = $br_requirement_frequency;
        $this->con = $con;
        
        $_SESSION['br_apprx_order_value'] = $this->br_apprx_order_value;
        $_SESSION['br_apprx_order_currency'] = $this->br_apprx_order_currency;
        $_SESSION['br_description'] = $this->br_description;
        $_SESSION['br_website'] = $this->br_website;
        $_SESSION['br_need_quote_for'] = $this->br_need_quote_for;
        $_SESSION['br_purchase_time'] = $this->br_purchase_time;
        $_SESSION['br_need_for'] = $this->br_need_for;
        $_SESSION['br_requirement_frequency'] = $this->br_requirement_frequency;
    }

    public function valid(): bool
    {
        $valid = true;
        
        // التحقق من القيمة التقريبية
        if (!empty($this->br_apprx_order_value) && !is_numeric($this->br_apprx_order_value)) {
            $this->msg = 'القيمة التقريبية يجب أن تكون رقماً';
            $valid = false;
        }
        
        return $valid;
    }
    
    public function add(): void
    {
        $update_sql = "UPDATE buy_requirement SET
                        br_apprx_order_value = ?,
                        br_apprx_order_currency = ?,
                        br_description = ?,
                        br_website = ?,
                        br_need_quote_for = ?,
                        br_purchase_time = ?,
                        br_need_for = ?,
                        br_requirement_frequency = ?
                        WHERE br_id = ?";
        
        $stmt = mysqli_prepare($this->con, $update_sql);
        mysqli_stmt_bind_param($stmt, 'ssssssssi', 
            $this->br_apprx_order_value,
            $this->br_apprx_order_currency,
            $this->br_description,
            $this->br_website,
            $this->br_need_quote_for,
            $this->br_purchase_time,
            $this->br_need_for,
            $this->br_requirement_frequency,
            $this->br_id
        );
        
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // مسح بيانات الجلسة
        unset($_SESSION['br_apprx_order_value']);
        unset($_SESSION['br_apprx_order_currency']);
        unset($_SESSION['br_description']);
        unset($_SESSION['br_website']);
        unset($_SESSION['br_need_quote_for']);
        unset($_SESSION['br_need_for']);
        unset($_SESSION['br_requirement_frequency']);
    }
}

// =============================================
// معالجة نموذج الإرسال
// =============================================
if (isset($_POST['submitBuyReqDetails'])) {
    $br_id = (int)($_POST['br_id'] ?? 0);
    
    $adn = new AddBuyReqInfo(
        $br_id,
        trim($_POST['br_apprx_order_value'] ?? ''),
        trim($_POST['br_apprx_order_currency'] ?? ''),
        trim($_POST['br_description'] ?? ''),
        trim($_POST['br_website'] ?? ''),
        trim($_POST['br_need_quote_for'] ?? ''),
        trim($_POST['br_purchase_time'] ?? ''),
        trim($_POST['br_need_for'] ?? ''),
        trim($_POST['br_requirement_frequency'] ?? ''),
        $con
    );
    
    if ($adn->valid()) {
        $adn->add();
        header("Location: post-buy-req-res.php");
        exit;
    } else {
        $_SESSION['msg'] = $adn->msg;
        header("Location: post-buy-req-info.php");
        exit;
    }
}

// جلب العملات
$currencies = [];
$curr_sql = "SELECT DISTINCT cn_currency FROM country WHERE cn_status = '1' ORDER BY cn_currency ASC";
$curr_result = mysqli_query($con, $curr_sql);
while ($row = mysqli_fetch_assoc($curr_result)) {
    $currencies[] = $row['cn_currency'];
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
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
    <link href="css/eto-post-enrich.css" type="text/css" rel="stylesheet">
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <script type="text/javascript">
    function validForm() {
        var br_apprx_order_value = document.getElementById('br_apprx_order_value');
        var br_apprx_order_currency = document.getElementById('br_apprx_order_currency');
        var message = "";
        var valid = true;
        
        if (br_apprx_order_value.value == '') {
            message = "من فضلك أدخل القيمة التقريبية لطلب الشراء";
            br_apprx_order_value.focus();
            valid = false;
        } else if (br_apprx_order_value.value != '' && isNaN(br_apprx_order_value.value)) {
            message = "من فضلك أدخل قيمة صالحة لحقل القيمة التقريبية لطلب الشراء";
            br_apprx_order_value.focus();
            valid = false;
        } else if (br_apprx_order_value.value != '' && br_apprx_order_currency.value == '') {
            message = "من فضلك إختار عملة حقل القيمة التقريبية لطلب الشراء";
            br_apprx_order_currency.focus();
            valid = false;
        }
        
        if (!valid) {
            alert(message);
        }
        
        return valid;
    }
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . '/includes/header_menu.php'; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#FFFFFF;" title="Buyer Tools">
                        أدوات البائع
                    </h3>
                </li>
                
                <li class="np npnew"><a href="post-buy-req.php" title="Post a Buy Requirement">»&nbsp;أنشر طلب شراء</a></li>
                <li class="np npnew"><a href="manage-buy-requirement.php" title="Manage Buy Requirements">»&nbsp;إدارة طلبات الشراء</a></li>
                <li class="np npnew"><a href="manage-selloffer-alert.php" title="Manage Sell Offer Alerts">»&nbsp;إدارة إشعارات عروض بيع</a></li>
                
                <li style="border-bottom:medium none; margin-top:40px;" title="You May Also Like">
                    <h2>ربما تحب أيضا</h2>
                </li>
                <li class="np npnew"><a href="buyleads.php" title="View Latest Buy Leads">أخر طلبات الشراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php" title="Manage Purchased Buy Leads">طلبات الشراء المشتراه</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">إدارة إشعارات طلبات شراء</a></li>
            </ul>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr mfl">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td valign="TOP" width="100%">
                                                <table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="border-right:0px;" valign="top">
                                                                <div>
                                                                    <table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td width="100%">
                                                                                    <ul style="margin-bottom:0px; margin-top:0px; padding:0px;">
                                                                                        <img src="eto-post-ins-buy.mp_files/zero.gif" height="1" width="1"><br>
                                                                                        
                                                                                        <div id="eto_ofr_enrichmt_inside" class="etf_box-area" style="margin-top:1px;">
                                                                                            <div id="enrichment_form_start" style="display:block;">
                                                                                                <form style="margin:0" method="post" name="enrichmentForm" action="" onsubmit="return validForm();">
                                                                                                    <input type="hidden" id="br_id" name="br_id" value="<?php echo (int)$_SESSION['new_br_id']; ?>">
                                                                                                    
                                                                                                    <div class="etf_hm1" title="Please provide more details to get Quick Response from Suppliers.">
                                                                                                        من فضلك سجل معلومات شراء إضافية لتتلقى مزيد من أفضل الأسعار
                                                                                                    </div>
                                                                                                    
                                                                                                    <table style="margin-top:8px" border="0" cellpadding="0" cellspacing="0" width="700px">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td style="text-align:right" class="etf_tp etf_fs" title="Approximate order value:">
                                                                                                                    القيمة التقريبية لطلب الشراء
                                                                                                                </td>
                                                                                                                <td class="etf_tp1 etf_fs1" align="left">
                                                                                                                    <span id="q_qt_help4"></span>
                                                                                                                    <input maxlength="50" name="br_apprx_order_value" id="br_apprx_order_value" 
                                                                                                                           style="width:100px;" class="etf_q_txtb" type="text" 
                                                                                                                           value="<?php echo htmlspecialchars($br_apprx_order_value, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                                                    <select name="br_apprx_order_currency" id="br_apprx_order_currency" 
                                                                                                                            style="margin-left:3px; width:170px; border:1px solid #d7e2e9;">
                                                                                                                        <option selected="selected" value="">-- إختار العملة --</option>
                                                                                                                        <?php foreach ($currencies as $currency): ?>
                                                                                                                        <option value="<?php echo htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                                                                <?php echo ($currency == $br_apprx_order_currency) ? 'selected' : ''; ?>>
                                                                                                                            <?php echo htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'); ?>
                                                                                                                        </option>
                                                                                                                        <?php endforeach; ?>
                                                                                                                    </select>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                            
                                                                                                            <tr>
                                                                                                                <td style="text-align:right" class="etf_tp etf_fs" valign="top" title="Describe product application/ usage:">
                                                                                                                    وصف استخدام منتج أو خدمة الشراء
                                                                                                                </td>
                                                                                                                <td class="etf_tp1 etf_fs1" align="left">
                                                                                                                    <span id="q_qt_help5"></span>
                                                                                                                    <textarea name="br_description" id="br_description" 
                                                                                                                              class="etf_q_txtb1" 
                                                                                                                              style="width:400px; height:50px; vertical-align:baseline;"><?php echo htmlspecialchars($br_description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                            
                                                                                                            <tr id="enrich_websit_label">
                                                                                                                <td style="text-align:right" class="etf_tp etf_fs" title="Website:">
                                                                                                                    موقع الويب للمشترى
                                                                                                                </td>
                                                                                                                <td class="etf_tp1 etf_fs1" align="left">
                                                                                                                    <span id="q_qt_help2"></span>
                                                                                                                    <input maxlength="100" name="br_website" id="br_website" 
                                                                                                                           style="width:272px;" class="etf_q_txtb" type="text" 
                                                                                                                           value="http://<?php echo htmlspecialchars($br_website, ENT_QUOTES, 'UTF-8'); ?>"/>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                            
                                                                                                            <tr>
                                                                                                                <td class="etf_tp etf_fs etf_tbb" align="right" valign="top" title="Need quotations:">
                                                                                                                    التسعيرات المطلوبة
                                                                                                                </td>
                                                                                                                <td class="etf_tp2 etf_fs1 etf_tbb" align="left" width="420">
                                                                                                                    <span id="q_qt_help13"></span>
                                                                                                                    <input value="To Make Purchase" id="br_need_quote_for0" 
                                                                                                                           class="etf_rbmp" name="br_need_quote_for" type="radio" 
                                                                                                                           title="To Make Purchase"
                                                                                                                           <?php echo ($br_need_quote_for == 'To Make Purchase') ? 'checked' : ''; ?>> للشراء
                                                                                                                    <input value="لمعرفة السعر فقط" id="br_need_quote_for1" 
                                                                                                                           class="etf_rbm" name="br_need_quote_for" type="radio" 
                                                                                                                           title="To Know Price Only"
                                                                                                                           <?php echo ($br_need_quote_for == 'لمعرفة السعر فقط') ? 'checked' : ''; ?>> لمعرفة السعر فقط
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                    
                                                                                                    <table class="etf_tba" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td class="etf_tp etf_fs etf_tbb" align="right" valign="top" title="How soon do you want to purchase:">
                                                                                                                    إلى أى مدى تريد سرعة الشراء
                                                                                                                </td>
                                                                                                                <td class="etf_tp2 etf_fs1 etf_tbb" align="left">
                                                                                                                    <span id="q_qt_help7"></span>
                                                                                                                    <input value="Immediate" id="q_timperiod0" class="etf_rbmp" 
                                                                                                                           name="br_purchase_time" type="radio" title="Immediate"
                                                                                                                           <?php echo ($br_purchase_time == 'Immediate') ? 'checked' : ''; ?>> عاجل
                                                                                                                    <input value="Within 15 Days" id="q_timperiod1" 
                                                                                                                           name="br_purchase_time" class="etf_rbm" type="radio" 
                                                                                                                           title="Within 15 Days"
                                                                                                                           <?php echo ($br_purchase_time == 'Within 15 Days') ? 'checked' : ''; ?>> خلال 15 يوم
                                                                                                                    <input value="Within 1 Month" id="q_timperiod2" 
                                                                                                                           name="br_purchase_time" class="etf_rbm" type="radio" 
                                                                                                                           title="Within 1 Month"
                                                                                                                           <?php echo ($br_purchase_time == 'Within 1 Month') ? 'checked' : ''; ?>> خلال شهر
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                            
                                                                                                            <tr>
                                                                                                                <td class="etf_tp etf_fs etf_tbb" align="right" valign="top" title="Why do you need this:">
                                                                                                                    لماذا تحتاج هذا الشراء
                                                                                                                </td>
                                                                                                                <td class="etf_tp2 etf_fs1 etf_tbb" align="left">
                                                                                                                    <span id="q_qt_help8"></span>
                                                                                                                    <input value="For Reselling" id="br_need_for0" class="etf_rbmp" 
                                                                                                                           name="br_need_for" type="radio" title="For Reselling"
                                                                                                                           <?php echo ($br_need_for == 'For Reselling') ? 'checked' : ''; ?>> لتجارة إعادة البيع
                                                                                                                    <input value="للإستخدام النهائى" id="br_need_for1" class="etf_rbm" 
                                                                                                                           name="br_need_for" type="radio" title="For Your End Use"
                                                                                                                           <?php echo ($br_need_for == 'للإستخدام النهائى') ? 'checked' : ''; ?>> للإستخدام النهائى
                                                                                                                    <input value="كمواد خام لإعادة التصنيع" id="br_need_for2" class="etf_rbm" 
                                                                                                                           name="br_need_for" type="radio" title="As Raw Material"
                                                                                                                           <?php echo ($br_need_for == 'كمواد خام لإعادة التصنيع') ? 'checked' : ''; ?>> كمواد خام لإعادة التصنيع
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                            
                                                                                                            <tr>
                                                                                                                <td class="etf_tp etf_fs" align="right" valign="top" title="Is this your:">
                                                                                                                    هل هذا الشراء
                                                                                                                </td>
                                                                                                                <td class="etf_fs1" align="left" height="35">&nbsp;
                                                                                                                    <span id="q_qt_help9"></span>
                                                                                                                    <input value="One Time Requirement" id="br_requirement_frequency1" 
                                                                                                                           name="br_requirement_frequency" class="etf_rbmp" type="radio" 
                                                                                                                           title="One Time Requirement"
                                                                                                                           <?php echo ($br_requirement_frequency == 'One Time Requirement') ? 'checked' : ''; ?>> للشراء مرة واحدة
                                                                                                                    <input style="margin:0 4px 0 7px;" value="Regular Requirement" 
                                                                                                                           id="br_requirement_frequency2" class="etf_rbm" 
                                                                                                                           name="br_requirement_frequency" type="radio" 
                                                                                                                           title="Regular Requirement"
                                                                                                                           <?php echo ($br_requirement_frequency == 'Regular Requirement') ? 'checked' : ''; ?>> للشراء بشكل متكرر
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                    
                                                                                                    <span style="clear:both"></span>
                                                                                                    
                                                                                                    <div align="center">
                                                                                                        <input value="أكد طلب الشراء للنشر" name="submitBuyReqDetails" 
                                                                                                               class="enc_sbt_new" type="submit">
                                                                                                    </div>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    </ul>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div><br><br></div>
                                                <div align="center"><br></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br><br>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>