<?php
/**
 * File: field-add.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: إضافة حقل إضافي جديد
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ===== تعريف المتغيرات =====
$mcat_id = isset($_GET['mcat']) ? (int)$_GET['mcat'] : 0;
$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$af_pc_id = isset($_GET['af_pc_id']) ? (int)$_GET['af_pc_id'] : 0;

// ===== بدء الجلسة =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== تضمين الملفات الأساسية =====
require_once dirname(__DIR__) . "/common.php";

// ===== التحقق من تسجيل دخول المشرف =====
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

// ===== التحقق من اتصال قاعدة البيانات =====
global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * دالة لتصفية متغيرات الجلسة
 */
function filter_session_var($var_name, $default = '') {
    if (isset($_SESSION[$var_name])) {
        $value = $_SESSION[$var_name];
        unset($_SESSION[$var_name]);
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    return $default;
}

// استرجاع القيم من الجلسة
$cat_id = filter_session_var('cat_id', $cat_id);
$af_pc_id = filter_session_var('af_pc_id', $af_pc_id);
$af_type = filter_session_var('af_type', '');
$af_name = filter_session_var('af_name', '');
$af_label = filter_session_var('af_label', '');

/**
 * كلاس إدارة الحقول الإضافية
 */
class addField {
    
    private string $msg = '';
    private int $mcat_id = 0;
    private int $cat_id = 0;
    private int $af_pc_id = 0;
    private string $af_type = '';
    private string $af_name = '';
    private string $af_label = '';
    private array $errors = [];
    private mysqli $db;
    
    public function __construct(mysqli $database, $mcat_id, $cat_id, $af_pc_id, $af_type, $af_name, $af_label) {
        $this->db = $database;
        $this->mcat_id = (int)$mcat_id;
        $this->cat_id = (int)$cat_id;
        $this->af_pc_id = (int)$af_pc_id;
        $this->af_type = trim(htmlspecialchars((string)$af_type, ENT_QUOTES, 'UTF-8'));
        $this->af_name = trim(htmlspecialchars((string)$af_name, ENT_QUOTES, 'UTF-8'));
        $this->af_label = trim(htmlspecialchars((string)$af_label, ENT_QUOTES, 'UTF-8'));
        
        $_SESSION['mcat_id'] = $this->mcat_id;
        $_SESSION['cat_id'] = $this->cat_id;
        $_SESSION['af_pc_id'] = $this->af_pc_id;
        $_SESSION['af_type'] = $this->af_type;
        $_SESSION['af_name'] = $this->af_name;
        $_SESSION['af_label'] = $this->af_label;
    }
    
    public function valid(): bool {
        $this->errors = [];
        
        if ($this->af_pc_id <= 0) {
            $this->errors[] = 'الرجاء اختيار تصنيف فرعي صحيح';
        }
        
        $validTypes = ['text', 'textarea', 'radio', 'checkbox', 'select'];
        if (!in_array($this->af_type, $validTypes)) {
            $this->errors[] = 'الرجاء اختيار نوع حقل صحيح';
        }
        
        if (empty($this->af_name)) {
            $this->errors[] = 'الرجاء إدخال اسم الحقل';
        }
        
        if (empty($this->af_label)) {
            $this->errors[] = 'الرجاء إدخال تسمية الحقل';
        }
        
        if ($this->fieldNameExists()) {
            $this->errors[] = 'اسم الحقل موجود مسبقاً لهذا التصنيف';
        }
        
        return empty($this->errors);
    }
    
    private function fieldNameExists(): bool {
        $sql = "SELECT af_id FROM additional_field WHERE af_pc_id = ? AND af_name = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, "is", $this->af_pc_id, $this->af_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }
    
    public function add(): bool {
        $sql = "INSERT INTO additional_field (af_pc_id, af_type, af_name, af_label, af_value) VALUES (?, ?, ?, ?, '')";
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            $this->errors[] = 'خطأ في تحضير الاستعلام: ' . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "isss", $this->af_pc_id, $this->af_type, $this->af_name, $this->af_label);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم إضافة الحقل بنجاح</div>';
            unset($_SESSION['mcat_id'], $_SESSION['cat_id'], $_SESSION['af_pc_id'], $_SESSION['af_type'], $_SESSION['af_name'], $_SESSION['af_label']);
            mysqli_stmt_close($stmt);
            return true;
        } else {
            $this->errors[] = 'خطأ في إضافة الحقل: ' . mysqli_error($this->db);
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    public function getMsg(): string { return $this->msg; }
    public function getErrors(): array { return $this->errors; }
}

// ===== معالجة إرسال النموذج =====
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {
    $field = new addField($con, $_POST['mcat_id'] ?? 0, $_POST['cat_id'] ?? 0, $_POST['af_pc_id'] ?? 0, $_POST['af_type'] ?? '', $_POST['af_name'] ?? '', $_POST['af_label'] ?? '');
    
    if ($field->valid()) {
        if ($field->add()) {
            $message = $field->getMsg();
        } else {
            $errors = $field->getErrors();
        }
    } else {
        $errors = $field->getErrors();
    }
}

// ===== جلب التصنيفات =====
$main_categories = [];
$result_main = mysqli_query($con, "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1' ORDER BY pc_name");
while ($row = mysqli_fetch_assoc($result_main)) { $main_categories[] = $row; }

$categories = [];
if ($mcat_id > 0) {
    $stmt_cat = mysqli_prepare($con, "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1' ORDER BY pc_name");
    mysqli_stmt_bind_param($stmt_cat, "i", $mcat_id);
    mysqli_stmt_execute($stmt_cat);
    $result_cat = mysqli_stmt_get_result($stmt_cat);
    while ($row = mysqli_fetch_assoc($result_cat)) { $categories[] = $row; }
    mysqli_stmt_close($stmt_cat);
}

$sub_categories = [];
if ($cat_id > 0) {
    $stmt_sub = mysqli_prepare($con, "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1' ORDER BY pc_name");
    mysqli_stmt_bind_param($stmt_sub, "i", $cat_id);
    mysqli_stmt_execute($stmt_sub);
    $result_sub = mysqli_stmt_get_result($stmt_sub);
    while ($row = mysqli_fetch_assoc($result_sub)) { $sub_categories[] = $row; }
    mysqli_stmt_close($stmt_sub);
}
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#"><span class="menu-text"></span></a>
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li><a href="field-view.php">Additional Fields</a></li>
                    <li class="active">Add Field</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header"><h1>Add Additional Field</h1></div>
                
                <?php if (!empty($message)) echo $message; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="list-unstyled">
                        <?php foreach ($errors as $error): ?>
                            <li><i class="icon-exclamation-sign"></i> <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul></div>
                <?php endif; ?>
                
                <form method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Main Category</label>
                        <div class="col-sm-9">
                            <select name="mcat_id" class="form-control" onchange="window.location.href='field-add.php?mcat='+this.value">
                                <option value="">-- Select Main Category --</option>
                                <?php foreach ($main_categories as $cat): ?>
                                    <option value="<?php echo $cat['pc_id']; ?>" <?php echo ($mcat_id == $cat['pc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['pc_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Category</label>
                        <div class="col-sm-9">
                            <select name="cat_id" class="form-control" <?php echo ($mcat_id <= 0) ? 'disabled' : ''; ?> onchange="window.location.href='field-add.php?mcat=<?php echo $mcat_id; ?>&cat='+this.value">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['pc_id']; ?>" <?php echo ($cat_id == $cat['pc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['pc_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Sub Category</label>
                        <div class="col-sm-9">
                            <select name="af_pc_id" class="form-control" <?php echo ($cat_id <= 0) ? 'disabled' : ''; ?>>
                                <option value="">-- Select Sub Category --</option>
                                <?php foreach ($sub_categories as $sub): ?>
                                    <option value="<?php echo $sub['pc_id']; ?>"><?php echo htmlspecialchars($sub['pc_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Field Type</label>
                        <div class="col-sm-9">
                            <select name="af_type" class="form-control" required>
                                <option value="">-- Select Field Type --</option>
                                <option value="text">Text Box</option><option value="textarea">Text Area</option>
                                <option value="radio">Radio Button</option><option value="checkbox">Checkbox</option>
                                <option value="select">Dropdown Select</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Field Name</label>
                        <div class="col-sm-9"><input type="text" name="af_name" class="form-control" placeholder="e.g., product_color" required></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Field Label</label>
                        <div class="col-sm-9"><input type="text" name="af_label" class="form-control" placeholder="e.g., Product Color" required></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" name="btnSubmit" class="btn btn-primary"><i class="icon-save"></i> Save Field</button>
                            <a href="field-view.php" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>
<?php ob_end_flush(); ?>