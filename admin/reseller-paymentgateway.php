<?php
/**
 * File: reseller-paymentgateway.php
 * Version: 2.0.0
 * Description: إدارة بوابات الدفع للموزع (إعدادات حسابات بوابات الدفع)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "../common.php";

// التحقق من تسجيل الدخول
check_user_login();

// الحصول على معرف الموزع من الجلسة
$reseller_id = $_SESSION['reseller_id'] ?? 0;

if ($reseller_id <= 0) {
    $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
    header("Location: login.php");
    exit();
}

// جلب بوابات الدفع النشطة
$sqlchk = "SELECT * FROM payment_gateway WHERE pg_status = '1' ORDER BY pg_name ASC";
$reschk = mysqli_query($con, $sqlchk);

if (!$reschk) {
    error_log('خطأ في جلب بوابات الدفع: ' . mysqli_error($con));
    $error = 'حدث خطأ في تحميل البيانات';
}

// معالجة رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<script>
/**
 * إضافة/تحديث بوابات الدفع
 * @param {string} gateway - قائمة معرفات البوابات مفصولة بفواصل
 */
function addgateway(gateway) {
    var id = gateway.split(',');
    var total_gateway = '';
    var hasData = false;
    
    for (var i = 0; i < id.length; i++) {
        var inputId = 'resl_pg_cardno' + id[i];
        var value = $('#' + inputId).val();
        
        if (value && value.trim() !== '') {
            var dt = value.trim();
            var total = id[i] + ':' + dt;
            total_gateway = total_gateway + '||' + total;
            hasData = true;
        }
    }
    
    if (hasData && total_gateway !== '') {
        // إظهار مؤشر التحميل
        $('#btnUpdate').val('جاري الحفظ...').prop('disabled', true);
        
        $.get("update_gateway.php", {total_gateway: total_gateway})
            .done(function(data) {
                $('#err_msg').html('<font color="#009900">تم الحفظ بنجاح</font>');
                setTimeout(function() {
                    $('#err_msg').fadeOut();
                }, 3000);
            })
            .fail(function() {
                $('#err_msg').html('<font color="#CC0000">حدث خطأ في الحفظ</font>');
            })
            .always(function() {
                $('#btnUpdate').val('تحديث').prop('disabled', false);
            });
    } else {
        $('#err_msg').html('<font color="#CC0000">الرجاء إدخال بيانات على الأقل لبوابة واحدة</font>');
    }
}

/**
 * التحقق من صحة المدخلات
 */
function validateInputs() {
    var inputs = document.querySelectorAll('input[type="text"]');
    var hasValue = false;
    
    inputs.forEach(function(input) {
        if (input.value && input.value.trim() !== '') {
            hasValue = true;
        }
    });
    
    if (!hasValue) {
        alert('الرجاء إدخال بيانات على الأقل لبوابة واحدة');
        return false;
    }
    
    return true;
}

$(document).ready(function() {
    // تحسين تجربة المستخدم
    $('input[type="text"]').on('keyup', function() {
        $(this).css('border-color', $(this).val().trim() !== '' ? '#82af6f' : '#d15b47');
    });
    
    // دعم زر Enter
    $('input[type="text"]').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addgateway('<?php echo $gateid5 ?? ""; ?>');
        }
    });
});
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية للصفحة */
.formItem {
    margin-bottom: 20px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 5px;
    border-right: 3px solid #4a6a8b;
}

.formItem label {
    font-weight: bold;
    color: #333;
}

.formInputBox input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    transition: all 0.3s;
}

.formInputBox input:focus {
    border-color: #4a6a8b;
    box-shadow: 0 0 5px rgba(74, 106, 139, 0.3);
    outline: none;
}

#err_msg {
    display: block;
    margin: 10px 0;
    padding: 10px;
    border-radius: 3px;
    text-align: center;
}

.x2-button {
    padding: 8px 20px;
    background: #4a6a8b;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
}

.x2-button:hover {
    background: #3a5a7b;
}

.x2-button:disabled {
    background: #999;
    cursor: not-allowed;
}

.gateway-info {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.gateway-icon {
    width: 30px;
    height: 30px;
    vertical-align: middle;
    margin-left: 10px;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;الموزعين&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;بوابات الدفع</h2>
            
            <strong>
                <font color="#CC0000">
                    <label id='err_msg' style="width:200px; color:#D00;"><?php echo $msg; ?></label>
                </font>
            </strong>
            <br />
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="x2-layout" style="width:850px;">
                <div class="formSection showSection">
                    <div class="tableWrapper">
                        <table>
                            <tbody>
                                <tr class="formSectionRow">
                                    <td style="width:678px">
                                        
                                        <?php 
                                        $gateid = "";
                                        if ($reschk && mysqli_num_rows($reschk) > 0):
                                            while($rowchk = mysqli_fetch_assoc($reschk)): 
                                                $gateid .= $rowchk['pg_id'] . ",";
                                                
                                                // جلب بيانات بوابة الدفع للموزع
                                                $sqlchkk = "SELECT * FROM reseller_payment_gateway 
                                                           WHERE resl_pg_resellerid = ? AND resl_pg_gateway = ?";
                                                $stmt = mysqli_prepare($con, $sqlchkk);
                                                $cardNumber = '';
                                                
                                                if ($stmt) {
                                                    mysqli_stmt_bind_param($stmt, "ii", $reseller_id, $rowchk['pg_id']);
                                                    mysqli_stmt_execute($stmt);
                                                    $result = mysqli_stmt_get_result($stmt);
                                                    $rowchkk = mysqli_fetch_assoc($result);
                                                    mysqli_stmt_close($stmt);
                                                    
                                                    if ($rowchkk) {
                                                        $cardNumber = $rowchkk['resl_pg_cardno'] ?? '';
                                                    }
                                                }
                                        ?>       
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">
                                                    <?php 
                                                    $gatewayName = htmlspecialchars($rowchk['pg_name'] ?? '');
                                                    echo $gatewayName; ?>:
                                                </label>
                                                <div class="formInputBox" style="width:440px;height:auto;">
                                                    <input 
                                                        type="text" 
                                                        name="resl_pg_cardno<?php echo (int)$rowchk['pg_id']; ?>" 
                                                        id="resl_pg_cardno<?php echo (int)$rowchk['pg_id']; ?>" 
                                                        class="reg_txtfld" 
                                                        maxlength="255" 
                                                        value="<?php echo htmlspecialchars($cardNumber); ?>"
                                                        placeholder="أدخل رقم الحساب أو البطاقة"
                                                        <?php echo empty($cardNumber) ? 'style="border-color:#d15b47;"' : ''; ?>
                                                    />
                                                    <div class="gateway-info">
                                                        أدخل تفاصيل حساب <?php echo $gatewayName; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php 
                                            endwhile;
                                            $gateid5 = rtrim($gateid, ',');
                                        else:
                                        ?>
                                            <div class="alert alert-warning">
                                                لا توجد بوابات دفع نشطة حالياً
                                            </div>
                                        <?php endif; ?>                   
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if ($reschk && mysqli_num_rows($reschk) > 0): ?>
                <div class="row buttons">
                    <input 
                        type="submit" 
                        name="btnUpdate" 
                        id="btnUpdate" 
                        value="تحديث" 
                        class="x2-button" 
                        style="margin-right:10px;margin-top:5px;" 
                        onclick="if(validateInputs()) addgateway('<?php echo $gateid5; ?>');"
                    />
                    <button 
                        type="button" 
                        class="x2-button" 
                        style="background:#6c757d; margin-top:5px;" 
                        onclick="window.location='welcome.php'"
                    >
                        إلغاء
                    </button>
                </div>
            <?php endif; ?>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
// إضافة تأثيرات بصرية
$(document).ready(function() {
    // إضافة أيقونات للبوابات (اختياري)
    var gatewayIcons = {
        'paypal': '🔵',
        'visa': '💳',
        'mastercard': '💳',
        'bank': '🏦'
    };
    
    $('.formItem label').each(function() {
        var text = $(this).text().toLowerCase();
        for (var key in gatewayIcons) {
            if (text.includes(key)) {
                $(this).prepend('<span class="gateway-icon">' + gatewayIcons[key] + '</span> ');
                break;
            }
        }
    });
    
    // تفعيل الإدخال التلقائي
    var firstInput = $('input[type="text"]').first();
    if (firstInput.length) {
        firstInput.focus();
    }
});

// تأكيد الحفظ
window.onbeforeunload = function() {
    var inputs = document.querySelectorAll('input[type="text"]');
    var hasChanges = false;
    
    inputs.forEach(function(input) {
        var defaultValue = input.defaultValue;
        if (input.value.trim() !== defaultValue) {
            hasChanges = true;
        }
    });
    
    if (hasChanges) {
        return 'لديك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
    }
};
</script>

</body>
</html>
<?php ob_end_flush(); ?>