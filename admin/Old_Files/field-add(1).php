<?php
// كود تشخيصي مؤقت
error_reporting(E_ALL);
ini_set('display_errors', '1');

// تسجيل جميع الأخطاء
$debug_log = '/home/u397968200/domains/egyptmart.shop/public_html/logs/field_add_debug.log';
file_put_contents($debug_log, date('Y-m-d H:i:s') . " - بدء تنفيذ field-add.php\n", FILE_APPEND);
file_put_contents($debug_log, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
/**
 * ملف إضافة حقل إضافي جديد
 * 
 * @filename    field-add.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن إضافة حقول إضافية للمنتجات حسب التصنيفات
 *              مع التحقق من المدخلات وحفظها في قاعدة البيانات
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
$cat_id = filter_session_var('cat_id', 0);
$af_pc_id = filter_session_var('af_pc_id', 0);
$af_type = filter_session_var('af_type', '');
$af_name = filter_session_var('af_name', '');
$af_label = filter_session_var('af_label', '');

/**
 * دالة لتصفية متغيرات الجلسة
 */
function filter_session_var($var_name, $default = '') {
    if (isset($_SESSION[$var_name])) {
        $value = $_SESSION[$var_name];
        unset($_SESSION[$var_name]);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    return $default;
}

/**
 * كلاس إدارة الحقول الإضافية
 */
class addField {
    
    private $msg;
    private $mcat_id;
    private $cat_id;
    private $af_pc_id;
    private $af_type;
    private $af_name;
    private $af_label;
    private $errors = [];
    
    /**
     * constructor
     */
    public function __construct($mcat_id, $cat_id, $af_pc_id, $af_type, $af_name, $af_label) {
        $this->mcat_id = $this->validateInt($mcat_id);
        $this->cat_id = $this->validateInt($cat_id);
        $this->af_pc_id = $this->validateInt($af_pc_id);
        $this->af_type = $this->sanitizeString($af_type);
        $this->af_name = $this->sanitizeFieldName($af_name);
        $this->af_label = $this->sanitizeString($af_label);
        
        // حفظ في الجلسة للاحتفاظ بالقيم عند الخطأ
        $_SESSION['mcat_id'] = $this->mcat_id;
        $_SESSION['cat_id'] = $this->cat_id;
        $_SESSION['af_pc_id'] = $this->af_pc_id;
        $_SESSION['af_type'] = $this->af_type;
        $_SESSION['af_name'] = $this->af_name;
        $_SESSION['af_label'] = $this->af_label;
    }
    
    /**
     * التحقق من صحة الرقم
     */
    private function validateInt($value, $default = 0) {
        $filtered = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 999999]
        ]);
        return $filtered !== false ? $filtered : $default;
    }
    
    /**
     * تنظيف النص
     */
    private function sanitizeString($value) {
        return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * تنظيف اسم الحقل (يسمح فقط بالأحرف والأرقام والشرطة السفلية)
     */
    private function sanitizeFieldName($value) {
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $value);
        return substr($clean, 0, 50); // تحديد الطول الأقصى
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid() {
        $this->errors = [];
        
        // التحقق من وجود التصنيفات في قاعدة البيانات
        if ($this->mcat_id <= 0 || !$this->categoryExists($this->mcat_id, true)) {
            $this->errors[] = 'الرجاء اختيار تصنيف رئيسي صحيح';
        }
        
        if ($this->cat_id <= 0 || !$this->categoryExists($this->cat_id, false)) {
            $this->errors[] = 'الرجاء اختيار تصنيف صحيح';
        }
        
        if ($this->af_pc_id <= 0 || !$this->subcategoryExists($this->af_pc_id)) {
            $this->errors[] = 'الرجاء اختيار تصنيف فرعي صحيح';
        }
        
        $validTypes = ['text', 'textarea', 'radio', 'checkbox', 'select'];
        if (!in_array($this->af_type, $validTypes)) {
            $this->errors[] = 'الرجاء اختيار نوع حقل صحيح';
        }
        
        if (empty($this->af_name)) {
            $this->errors[] = 'الرجاء إدخال اسم الحقل';
        } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->af_name)) {
            $this->errors[] = 'اسم الحقل يجب أن يبدأ بحرف ويحتوي فقط على أحرف وأرقام وشرطة سفلية';
        }
        
        if (empty($this->af_label)) {
            $this->errors[] = 'الرجاء إدخال تسمية الحقل';
        } elseif (strlen($this->af_label) > 100) {
            $this->errors[] = 'تسمية الحقل طويلة جداً (الحد الأقصى 100 حرف)';
        }
        
        // التحقق من عدم تكرار اسم الحقل
        if ($this->fieldNameExists($this->af_pc_id, $this->af_name)) {
            $this->errors[] = 'اسم الحقل موجود مسبقاً لهذا التصنيف';
        }
        
        return empty($this->errors);
    }
    
    /**
     * التحقق من وجود التصنيف الرئيسي/الفرعي
     */
    private function categoryExists($pc_id, $isParent = true) {
        global $con;
        
        $sql = "SELECT pc_id FROM product_category 
                WHERE pc_id = ? AND pc_parent_id " . ($isParent ? "= '0'" : "!= '0'") . " AND pc_status = '1'";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $pc_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }
    
    /**
     * التحقق من وجود التصنيف الفرعي
     */
    private function subcategoryExists($pc_id) {
        global $con;
        
        $sql = "SELECT pc_id FROM product_category 
                WHERE pc_id = ? AND pc_parent_id != '0' AND pc_status = '1'";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $pc_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }
    
    /**
     * التحقق من عدم تكرار اسم الحقل
     */
    private function fieldNameExists($pc_id, $field_name) {
        global $con;
        
        $sql = "SELECT af_id FROM additional_field 
                WHERE af_pc_id = ? AND af_name = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "is", $pc_id, $field_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }
    
    /**
     * إضافة الحقل إلى قاعدة البيانات
     */
    public function add() {
        global $con;
        
        $sql = "INSERT INTO additional_field 
                (af_pc_id, af_type, af_name, af_label, af_created_date) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isss", 
            $this->af_pc_id, 
            $this->af_type, 
            $this->af_name, 
            $this->af_label
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success">
                <i class="icon-ok"></i> تم إضافة الحقل بنجاح
            </div>';
            
            // مسح الجلسة بعد النجاح
            unset($_SESSION['mcat_id']);
            unset($_SESSION['cat_id']);
            unset($_SESSION['af_pc_id']);
            unset($_SESSION['af_type']);
            unset($_SESSION['af_name']);
            unset($_SESSION['af_label']);
            
            mysqli_stmt_close($stmt);
            return true;
        } else {
            $this->errors[] = 'خطأ في إضافة الحقل: ' . mysqli_error($con);
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    /**
     * الحصول على رسالة النجاح
     */
    public function getMsg() {
        return $this->msg ?? '';
    }
    
    /**
     * الحصول على أخطاء التحقق
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
    
    // التحقق من رمز CSRF (يوصى بإضافته)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('طلب غير مصرح به');
    // }
    
    $adn = new addField(
        $_POST['mcat_id'] ?? 0,
        $_POST['cat_id'] ?? 0,
        $_POST['af_pc_id'] ?? 0,
        $_POST['af_type'] ?? '',
        $_POST['af_name'] ?? '',
        $_POST['af_label'] ?? ''
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    // تخزين الأخطاء في الجلسة
    if (!empty($adn->getErrors())) {
        $_SESSION['errors'] = $adn->getErrors();
    }
    
    $_SESSION['msg'] = $adn->getMsg();
    
    // إعادة التوجيه لتجنب إعادة الإرسال
    header("Location: field-add.php");
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
        
        <script language="javascript">
        // دالة عرض التصنيفات
        function showCategory(str) {
            if(str === '' || str === '0') {
                $('#cat_id').html('<option value="0">- اختر التصنيف الرئيسي أولاً -</option>');
                $('#af_pc_id').html('<option value="0">- اختر التصنيف أولاً -</option>');
                return;
            }
            $.get("showCategory.php", {q:str}, function(data) {
                $('#cat_id').html(data);
            });
        }
        
        // دالة عرض التصنيفات الفرعية
        function showSubcat(str) {
            if(str === '' || str === '0') {
                $('#af_pc_id').html('<option value="0">- اختر التصنيف أولاً -</option>');
                return;
            }
            $.get("showSubcat.php", {q:str}, function(data) {
                $('#af_pc_id').html(data);
            });
        }
        
        // دالة التحقق من النموذج
        function validForm() {
            var mcat_id = document.getElementById('mcat_id');
            var cat_id = document.getElementById('cat_id');
            var af_pc_id = document.getElementById('af_pc_id');
            var af_type = document.getElementById('af_type');
            var af_name = document.getElementById('af_name');
            var af_label = document.getElementById('af_label');
            
            var msg = "";
            var valid = true;
            
            // التحقق من التصنيف الرئيسي
            if(!mcat_id.value || mcat_id.value === '0') {
                msg = 'الرجاء اختيار التصنيف الرئيسي';
                mcat_id.focus();
                valid = false;
            }
            // التحقق من التصنيف
            else if(!cat_id.value || cat_id.value === '0') {
                msg = 'الرجاء اختيار التصنيف';
                cat_id.focus();
                valid = false;
            }
            // التحقق من التصنيف الفرعي
            else if(!af_pc_id.value || af_pc_id.value === '0') {
                msg = 'الرجاء اختيار التصنيف الفرعي';
                af_pc_id.focus();
                valid = false;
            }
            // التحقق من نوع الحقل
            else if(!af_type.value || af_type.value === '0') {
                msg = 'الرجاء اختيار نوع الحقل';
                af_type.focus();
                valid = false;
            }
            // التحقق من اسم الحقل
            else if(!af_name.value.trim()) {
                msg = 'الرجاء إدخال اسم الحقل';
                af_name.focus();
                valid = false;
            }
            // التحقق من تسمية الحقل
            else if(!af_label.value.trim()) {
                msg = 'الرجاء إدخال تسمية الحقل';
                af_label.focus();
                valid = false;
            }
            // التحقق من صيغة اسم الحقل
            else if(!/^[a-zA-Z][a-zA-Z0-9_]*$/.test(af_name.value.trim())) {
                msg = 'اسم الحقل يجب أن يبدأ بحرف ويحتوي على أحرف وأرقام وشرطة سفلية فقط';
                af_name.focus();
                valid = false;
            }
            
            if(!valid) {
                document.getElementById('msg').innerHTML = "<div class='alert alert-danger'><i class='icon-remove'></i> " + msg + "</div>";
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
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="field-view.php">إدارة الحقول الإضافية</a>
                    </li>
                    <li class="active">إضافة حقل جديد</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="icon-plus"></i> إضافة حقل إضافي جديد
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <!-- رسائل النجاح/الخطأ -->
                        <div id="msg"><?php echo $msg . $errorMsg; ?></div>

                        <form class="form-horizontal" action="" method="post" onsubmit="return validForm()">
                            <!-- حماية CSRF - يوصى بإضافتها -->
                            <!-- <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>"> -->
                            
                            <!-- التصنيف الرئيسي -->
                            <div class="form-group">
                                <?php
                                $mcat_sql = "SELECT * FROM product_category 
                                            WHERE pc_parent_id = '0' AND pc_status = '1' 
                                            ORDER BY pc_name";
                                $mcat_res = mysqli_query($con, $mcat_sql);
                                ?>
                                <label class="col-sm-3 control-label no-padding-right" for="mcat_id">
                                    التصنيف الرئيسي <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="mcat_id" id="mcat_id" class="width-40" onchange="showCategory(this.value)">
                                        <option value="0">- اختر -</option>
                                        <?php while($mcat_row = mysqli_fetch_object($mcat_res)): ?>
                                        <option value="<?php echo $mcat_row->pc_id; ?>" 
                                            <?php echo ($mcat_id == $mcat_row->pc_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mcat_row->pc_name); ?>
                                        </option>
                                        <?php endwhile; ?> 
                                    </select>
                                </div>
                            </div>
                            
                            <!-- التصنيف -->
                            <div class="form-group">
                                <?php
                                if ($mcat_id > 0) {
                                    $cat_sql = "SELECT * FROM product_category 
                                                WHERE pc_parent_id != '0' AND pc_parent_id = ? AND pc_status = '1'
                                                ORDER BY pc_name";
                                    $cat_stmt = mysqli_prepare($con, $cat_sql);
                                    mysqli_stmt_bind_param($cat_stmt, "i", $mcat_id);
                                    mysqli_stmt_execute($cat_stmt);
                                    $cat_res = mysqli_stmt_get_result($cat_stmt);
                                }
                                ?>
                                <label class="col-sm-3 control-label no-padding-right" for="cat_id">
                                    التصنيف <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="cat_id" id="cat_id" class="width-40" onchange="showSubcat(this.value)">
                                        <option value="0">- اختر -</option>
                                        <?php if (isset($cat_res)): ?>
                                            <?php while($cat_row = mysqli_fetch_object($cat_res)): ?>
                                            <option value="<?php echo $cat_row->pc_id; ?>" 
                                                <?php echo ($cat_id == $cat_row->pc_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat_row->pc_name); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- التصنيف الفرعي -->
                            <div class="form-group">
                                <?php
                                if ($cat_id > 0) {
                                    $scat_sql = "SELECT * FROM product_category 
                                                WHERE pc_parent_id != '0' AND pc_parent_id = ? AND pc_status = '1'
                                                ORDER BY pc_name";
                                    $scat_stmt = mysqli_prepare($con, $scat_sql);
                                    mysqli_stmt_bind_param($scat_stmt, "i", $cat_id);
                                    mysqli_stmt_execute($scat_stmt);
                                    $scat_res = mysqli_stmt_get_result($scat_stmt);
                                }
                                ?>
                                <label class="col-sm-3 control-label no-padding-right" for="af_pc_id">
                                    التصنيف الفرعي <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="af_pc_id" id="af_pc_id" class="width-40">
                                        <option value="0">- اختر -</option>
                                        <?php if (isset($scat_res)): ?>
                                            <?php while($scat_row = mysqli_fetch_object($scat_res)): ?>
                                            <option value="<?php echo $scat_row->pc_id; ?>" 
                                                <?php echo ($af_pc_id == $scat_row->pc_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($scat_row->pc_name); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- نوع الحقل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="af_type">
                                    نوع الحقل <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="af_type" id="af_type" class="width-40">
                                        <option value="0">- اختر -</option>
                                        <option value="text" <?php echo ($af_type == "text") ? 'selected' : ''; ?>>نص (text)</option>
                                        <option value="textarea" <?php echo ($af_type == "textarea") ? 'selected' : ''; ?>>منطقة نص (textarea)</option>
                                        <option value="radio" <?php echo ($af_type == "radio") ? 'selected' : ''; ?>>اختيار منفرد (radio)</option>
                                        <option value="checkbox" <?php echo ($af_type == "checkbox") ? 'selected' : ''; ?>>اختيار متعدد (checkbox)</option>
                                        <option value="select" <?php echo ($af_type == "select") ? 'selected' : ''; ?>>قائمة منسدلة (select)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- اسم الحقل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="af_name">
                                    اسم الحقل <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="af_name" id="af_name" class="col-xs-10 col-sm-5" 
                                           type="text" value="<?php echo htmlspecialchars($af_name); ?>" 
                                           placeholder="example_field_name" dir="ltr" />
                                    <span class="help-block">يستخدم في البرمجة - أحرف إنجليزية فقط وشرطة سفلية</span>
                                </div>
                            </div>
                            
                            <!-- تسمية الحقل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="af_label">
                                    تسمية الحقل <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="af_label" id="af_label" class="col-xs-10 col-sm-5" 
                                           type="text" value="<?php echo htmlspecialchars($af_label); ?>" 
                                           placeholder="اسم الحقل الذي يظهر للمستخدم" />
                                </div>
                            </div>
                            
                            <!-- أزرار التحكم -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd">
                                        <i class="icon-ok bigger-110"></i> إضافة
                                    </button>
                                    <button class="btn" type="button" onclick="location.href='field-view.php'">
                                        <i class="icon-remove bigger-110"></i> إلغاء
                                    </button>
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

</body>
</html>