<?php
/**
 * ملف إضافة خطط العضوية (Membership Plans)
 * 
 * @filename    memplan-add.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن إضافة خطط العضوية الجديدة
 *              مع التحقق من صحة البيانات وإدخالها في قاعدة البيانات
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم
check_admin_login();

// استرجاع القيم من الجلسة وتنظيفها
$mp_name = '';
$mp_credits = '';
$mp_amount = '';

if (isset($_SESSION['mp_name'])) {
    $mp_name = htmlspecialchars($_SESSION['mp_name'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['mp_name']);
}

if (isset($_SESSION['mp_credits'])) {
    $mp_credits = htmlspecialchars($_SESSION['mp_credits'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['mp_credits']);
}

if (isset($_SESSION['mp_amount'])) {
    $mp_amount = htmlspecialchars($_SESSION['mp_amount'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['mp_amount']);
}

/**
 * كلاس إضافة خطط العضوية
 */
class addPlan {

    private $msg;
    private $mp_name;
    private $mp_credits;
    private $mp_amount;
    private $errors = [];

    /**
     * constructor
     * @param string $mp_name اسم الخطة
     * @param int $mp_credits عدد الرصيد
     * @param float $mp_amount السعر
     */
    public function __construct($mp_name, $mp_credits, $mp_amount) {
        $this->mp_name = $this->sanitizeText($mp_name, 100);
        $this->mp_credits = $this->validateCredits($mp_credits);
        $this->mp_amount = $this->validateAmount($mp_amount);
        
        // حفظ في الجلسة للاحتفاظ بالقيم عند الخطأ
        $_SESSION['mp_name'] = $this->mp_name;
        $_SESSION['mp_credits'] = $this->mp_credits;
        $_SESSION['mp_amount'] = $this->mp_amount;
    }

    /**
     * تنظيف النص
     * @param string $value
     * @param int $maxLength
     * @return string
     */
    private function sanitizeText($value, $maxLength = 255) {
        $clean = trim(strip_tags($value));
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
        return substr($clean, 0, $maxLength);
    }

    /**
     * التحقق من صحة الرصيد
     * @param mixed $value
     * @return int
     */
    private function validateCredits($value) {
        $credits = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        
        return $credits !== false ? $credits : 0;
    }

    /**
     * التحقق من صحة السعر
     * @param mixed $value
     * @return float
     */
    private function validateAmount($value) {
        $amount = filter_var($value, FILTER_VALIDATE_FLOAT, [
            'options' => ['min_range' => 0.01, 'max_range' => 999999.99]
        ]);
        
        return $amount !== false ? round($amount, 2) : 0;
    }

    /**
     * التحقق من عدم وجود خطة مكررة
     * @return bool
     */
    private function isDuplicate() {
        global $con;
        
        $sql = "SELECT mp_id FROM membership_plan WHERE mp_name = ? AND mp_status = 1";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->mp_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }

    /**
     * التحقق من صحة البيانات
     * @return bool
     */
    public function valid() {
        $this->errors = [];
        
        if (empty($this->mp_name)) {
            $this->errors[] = 'الرجاء إدخال اسم الخطة';
        } elseif ($this->isDuplicate()) {
            $this->errors[] = 'اسم الخطة موجود مسبقاً';
        }
        
        if ($this->mp_credits <= 0) {
            $this->errors[] = 'الرجاء إدخال عدد الرصيد الصحيح';
        } elseif ($this->mp_credits % 20 != 0) {
            $this->errors[] = 'الرصيد يجب أن يكون من مضاعفات العدد 20';
        }
        
        if ($this->mp_amount <= 0) {
            $this->errors[] = 'الرجاء إدخال سعر الخطة';
        }
        
        return empty($this->errors);
    }

    /**
     * إضافة الخطة إلى قاعدة البيانات
     * @return bool
     */
    public function add() {
        global $con;
        
        $sql = "INSERT INTO membership_plan 
                (mp_name, mp_credits, mp_amount, mp_status, mp_created_date, mp_updated_date) 
                VALUES (?, ?, ?, 1, NOW(), NOW())";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sid", 
            $this->mp_name,
            $this->mp_credits,
            $this->mp_amount
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $insert_id = mysqli_insert_id($con);
            mysqli_stmt_close($stmt);
            
            $this->msg = '<div class="alert alert-success">
                <i class="icon-ok"></i> تم إضافة الخطة بنجاح
            </div>';
            
            // مسح الجلسة بعد النجاح
            unset($_SESSION['mp_name']);
            unset($_SESSION['mp_credits']);
            unset($_SESSION['mp_amount']);
            
            return true;
        } else {
            $this->errors[] = 'خطأ في إضافة الخطة: ' . mysqli_error($con);
            mysqli_stmt_close($stmt);
            return false;
        }
    }

    /**
     * الحصول على رسالة النجاح
     * @return string
     */
    public function getMsg() {
        return $this->msg ?? '';
    }

    /**
     * الحصول على أخطاء التحقق
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}

// معالجة الرسائل من الجلسة
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    $adn = new addPlan(
        $_POST['mp_name'] ?? '',
        $_POST['mp_credits'] ?? 0,
        $_POST['mp_amount'] ?? 0
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    // تخزين الأخطاء في الجلسة
    if (!empty($adn->getErrors())) {
        $_SESSION['errors'] = $adn->getErrors();
    }
    
    $_SESSION['msg'] = $adn->getMsg();
    
    header("Location: memplan-add.php");
    exit();
}

// عرض الأخطاء إذا وجدت
$errorMsg = '';
if (isset($_SESSION['errors'])) {
    $errorMsg = '<div class="alert alert-danger"><i class="icon-remove"></i><ul>';
    foreach ($_SESSION['errors'] as $error) {
        $errorMsg .= '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $errorMsg .= '</ul></div>';
    unset($_SESSION['errors']);
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
        // دالة التحقق من النموذج
        function myvalid() {
            var mp_name = document.getElementById('mp_name');
            var mp_credits = document.getElementById('mp_credits');
            var mp_amount = document.getElementById('mp_amount');
            
            var message = "";
            var valid = true;
            
            // التحقق من اسم الخطة
            if(mp_name.value.trim() === '') {
                message = 'الرجاء إدخال اسم الخطة';
                mp_name.focus();
                valid = false;
            }
            // التحقق من الرصيد
            else if(mp_credits.value.trim() === '') {
                message = 'الرجاء إدخال عدد الرصيد';
                mp_credits.focus();
                valid = false;
            }
            else if(isNaN(mp_credits.value) || parseInt(mp_credits.value) <= 0) {
                message = 'الرجاء إدخال عدد رصيد صحيح';
                mp_credits.value = '';
                mp_credits.focus();
                valid = false;
            }
            else if(parseInt(mp_credits.value) % 20 != 0) {
                message = 'الرصيد يجب أن يكون من مضاعفات العدد 20';
                mp_credits.value = '';
                mp_credits.focus();
                valid = false;
            }
            // التحقق من السعر
            else if(mp_amount.value.trim() === '') {
                message = 'الرجاء إدخال سعر الخطة';
                mp_amount.focus();
                valid = false;
            }
            else if(isNaN(mp_amount.value) || parseFloat(mp_amount.value) <= 0) {
                message = 'الرجاء إدخال سعر صحيح';
                mp_amount.value = '';
                mp_amount.focus();
                valid = false;
            }
            
            if(!valid) {
                document.getElementById('msg').innerHTML = 
                    '<div class="alert alert-danger"><i class="icon-remove"></i> ' + message + '</div>';
            }
            
            return valid;
        }
        
        // تنسيق حقول الأرقام
        $(document).ready(function() {
            $('#mp_credits').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            $('#mp_amount').on('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
                // منع أكثر من نقطة عشرية
                if((this.value.match(/\./g) || []).length > 1) {
                    this.value = this.value.slice(0, -1);
                }
            });
        });
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
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="memplan-view.php">إدارة خطط العضوية</a>
                    </li>
                    <li class="active">إضافة خطة جديدة</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="icon-plus"></i> إضافة خطة عضوية جديدة
                        <small>
                            <i class="icon-double-angle-right"></i>
                            أدخل بيانات الخطة
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- رسائل النظام -->
                        <div id="msg"><?php echo $msg . $errorMsg; ?></div>
                        
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                            
                            <em style="display:block; margin:5px;">
                                الحقول التي تحمل <span style="color:#F00">*</span> إجبارية
                            </em>
                            
                            <!-- اسم الخطة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mp_name">
                                    اسم الخطة <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="mp_name" id="mp_name" 
                                           class="col-xs-10 col-sm-5" 
                                           value="<?php echo $mp_name; ?>" 
                                           maxlength="100"
                                           placeholder="أدخل اسم الخطة" />
                                </div>
                            </div>
                            
                            <!-- عدد الرصيد -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mp_credits">
                                    عدد الرصيد <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="mp_credits" id="mp_credits" 
                                           class="col-xs-10 col-sm-5" 
                                           value="<?php echo $mp_credits; ?>" 
                                           placeholder="أدخل عدد الرصيد (مضاعفات 20)" />
                                    <span class="help-block">
                                        <i class="icon-info-sign"></i>
                                        يجب أن يكون من مضاعفات العدد 20 (مثال: 20، 40، 60، ...)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- سعر الخطة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mp_amount">
                                    سعر الخطة <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="input-group col-xs-10 col-sm-5" style="padding:0;">
                                        <input type="text" name="mp_amount" id="mp_amount" 
                                               class="form-control" 
                                               value="<?php echo $mp_amount; ?>" 
                                               placeholder="0.00" />
                                        <span class="input-group-addon">$</span>
                                    </div>
                                    <span class="help-block">
                                        <i class="icon-info-sign"></i>
                                        أدخل السعر بالدولار الأمريكي (مثال: 99.99)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- أزرار التحكم -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd">
                                        <i class="icon-ok bigger-110"></i> إضافة
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> إعادة تعيين
                                    </button>
                                    <a href="memplan-view.php" class="btn btn-default">
                                        <i class="icon-remove bigger-110"></i> إلغاء
                                    </a>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- نهاية ملف memplan-add.php - الإصدار 2.0.0 -->
</body>
</html>