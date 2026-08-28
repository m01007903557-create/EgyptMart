<?php
/**
 * File: admin/buyreq-edit.php
 * Version: PHP 8.3
 * Description: تعديل طلب شراء في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بتعديل طلب الشراء الموجود
 * مع إمكانية تغيير جميع البيانات بما في ذلك الصور والتصنيفات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";




// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس تعديل طلب الشراء
 */
class editBuyReq
{
    public $msg;
    public $br_id;
    public $pc_id;
    public $br_pc_id;
    public $br_pd_name;
    public $br_requirement;
    public $br_estimate_qty;
    public $br_estimate_qty_unit;
    public $br_preferred_supplier_location;

    public $br_apprx_order_value;
    public $br_apprx_order_currency;
    public $br_description;
    public $br_website;
    public $br_need_quote_for;
    public $br_purchase_time;

    public $br_need_for;
    public $br_requirement_frequency;
    public $br_pic;
    public $con;

    /**
     * Constructor
     * @param string $br_id معرف طلب الشراء (MD5)
     */
    public function __construct($br_id)
    {
        global $con;
        $this->con = $con;
        $this->br_id = $br_id;
    }

    /**
     * جلب تفاصيل طلب الشراء
     * @return object|null بيانات طلب الشراء
     */
    public function detailsObj()
    {
        $sql = "SELECT * FROM buy_requirement, product_category, measurement_unit 
                WHERE br_pc_id = pc_id 
                  AND br_estimate_qty_unit = mu_id 
                  AND MD5(br_id) = '" . mysqli_real_escape_string($this->con, $this->br_id) . "'";
        $res = mysqli_query($this->con, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_object($res);
        }
        return null;
    }

    /**
     * التحقق من صحة البيانات
     * @return bool true إذا كانت البيانات صحيحة
     */
    public function valid(): bool
    {
        $valid = true;

        if (empty($this->pc_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
            $valid = false;
        } else if (empty($this->br_pc_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
            $valid = false;
        } else if (empty($this->br_pd_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Products / Services.</div>';
            $valid = false;
        } else if (empty($this->br_requirement)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please describe Buying Requirements.</div>';
            $valid = false;
        } else if (strlen($this->br_requirement) < 50) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Buying Requirements must be at least 50 characters length.</div>';
            $valid = false;
        } else if (empty($this->br_estimate_qty)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Estimated Quantity.</div>';
            $valid = false;
        } else if (empty($this->br_estimate_qty_unit)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Estimated Quantity Unit.</div>';
            $valid = false;
        }

        return $valid;
    }

    /**
     * تحديث طلب الشراء
     */
    public function update()
    {
        if (!empty($_FILES["br_pic"]["name"])) {
            $this->updateWithImage();
        } else {
            $this->updateWithoutImage();
        }
    }

    /**
     * تحديث طلب الشراء مع تغيير الصورة
     */
    private function updateWithImage(): void
    {
        if ($_FILES["br_pic"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Upload Error: ' . $_FILES["br_pic"]["error"] . '</div>';
            error_log("خطأ في رفع ملف طلب الشراء: " . $_FILES["br_pic"]["error"]);
            return;
        }

        // جلب معلومات الصورة القديمة
        $sqlImg = "SELECT * FROM buy_requirement WHERE br_id = '" . mysqli_real_escape_string($this->con, $this->br_id) . "'";
        $resImg = mysqli_query($this->con, $sqlImg);
        $rowImg = mysqli_fetch_object($resImg);

        // حذف الصور القديمة إذا كانت موجودة
       // حذف الصور القديمة إذا كانت موجودة (من جميع المجلدات)
if (!empty($rowImg->br_pic)) {
    $possible_paths = [
        "../upload/buy_requirement/" . $rowImg->br_pic,
        "../upload/buy_requirement/thumb/" . $rowImg->br_pic,
        "../upload/image_gallery/" . $rowImg->br_pic
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

        // إنشاء اسم فريد للصورة
        $file_extension = pathinfo($_FILES['br_pic']['name'], PATHINFO_EXTENSION);
        $safe_filename = preg_replace('/[^a-zA-Z0-9.]/', '_', $_FILES['br_pic']['name']);
        $this->br_pic = "br-" . rand(1000, 9999) . '_' . $safe_filename;

        // حفظ الصورة الأصلية
        $upload_path = "../upload/buy_requirement/" . $this->br_pic;
        if (!move_uploaded_file($_FILES["br_pic"]["tmp_name"], $upload_path)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to save image</div>';
            return;
        }

        // إنشاء الصورة المصغرة
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($upload_path);
            $imgSImage->resize(100, 80);
            $imgSImage->save("../upload/buy_requirement/thumb/" . $this->br_pic);
        } catch (Exception $e) {
            error_log("استثناء في معالجة الصورة: " . $e->getMessage());
        }

        // تحديث قاعدة البيانات مع الصورة الجديدة
        $this->updateDatabase(true);
    }

    /**
     * تحديث طلب الشراء بدون تغيير الصورة
     */
    private function updateWithoutImage(): void
    {
        $this->updateDatabase(false);
    }

    /**
     * تحديث قاعدة البيانات
     * @param bool $includeImage هل يتم تضمين الصورة في التحديث
     */
    private function updateDatabase(bool $includeImage): void
    {
        $sql = "UPDATE buy_requirement SET
                br_pc_id = '" . mysqli_real_escape_string($this->con, $this->br_pc_id) . "',
                br_pd_name = '" . mysqli_real_escape_string($this->con, $this->br_pd_name) . "',
                br_requirement = '" . mysqli_real_escape_string($this->con, $this->br_requirement) . "',
                br_estimate_qty = '" . mysqli_real_escape_string($this->con, $this->br_estimate_qty) . "',
                br_estimate_qty_unit = '" . mysqli_real_escape_string($this->con, $this->br_estimate_qty_unit) . "',
                br_preferred_supplier_location = '" . mysqli_real_escape_string($this->con, $this->br_preferred_supplier_location) . "',
                br_apprx_order_value = '" . mysqli_real_escape_string($this->con, $this->br_apprx_order_value) . "',
                br_apprx_order_currency = '" . mysqli_real_escape_string($this->con, $this->br_apprx_order_currency) . "',
                br_description = '" . mysqli_real_escape_string($this->con, $this->br_description) . "',
                br_website = '" . mysqli_real_escape_string($this->con, $this->br_website) . "',
                br_need_quote_for = '" . mysqli_real_escape_string($this->con, $this->br_need_quote_for) . "',
                br_purchase_time = '" . mysqli_real_escape_string($this->con, $this->br_purchase_time) . "',
                br_need_for = '" . mysqli_real_escape_string($this->con, $this->br_need_for) . "',
                br_requirement_frequency = '" . mysqli_real_escape_string($this->con, $this->br_requirement_frequency) . "',
                br_updated_date = NOW()";

        if ($includeImage) {
            $sql .= ", br_pic = '" . mysqli_real_escape_string($this->con, $this->br_pic) . "'";
        }

        $sql .= " WHERE br_id = '" . mysqli_real_escape_string($this->con, $this->br_id) . "'";

        $result = mysqli_query($this->con, $sql);

        if (!$result) {
            error_log("خطأ في تحديث طلب الشراء: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
            return;
        }

        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Buy Requirement updated successfully.</div>';
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: buyreq-view.php");
    exit();
}

$token = trim($_GET['token']);

// إنشاء كائن التعديل
$ob = new editBuyReq($token);
$row = $ob->detailsObj();

if (!$row) {
    header("location: buyreq-view.php");
    exit();
}

// جلب بيانات الشركة
$bsql = "SELECT bu.* FROM buy_requirement r 
         JOIN business_profile bu ON r.br_u_id = bu.bnsprof_uid 
         WHERE MD5(r.br_id) = '" . mysqli_real_escape_string($con, $token) . "'";
$bres = mysqli_query($con, $bsql);
$brow = $bres ? mysqli_fetch_object($bres) : null;

// معالجة إرسال النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->br_id = trim($_POST['br_id'] ?? '');
    $ob->pc_id = trim($_POST['pc_id'] ?? '');
    $ob->br_pc_id = trim($_POST['br_pc_id'] ?? '');
    $ob->br_pd_name = trim($_POST['br_pd_name'] ?? '');
    $ob->br_requirement = trim($_POST['br_requirement'] ?? '');
    $ob->br_estimate_qty = trim($_POST['br_estimate_qty'] ?? '');
    $ob->br_estimate_qty_unit = trim($_POST['br_estimate_qty_unit'] ?? '');
    $ob->br_preferred_supplier_location = trim($_POST['br_preferred_supplier_location'] ?? '');

    $ob->br_apprx_order_value = trim($_POST['br_apprx_order_value'] ?? '');
    $ob->br_apprx_order_currency = trim($_POST['br_apprx_order_currency'] ?? '');
    $ob->br_description = trim($_POST['br_description'] ?? '');
    $ob->br_website = trim($_POST['br_website'] ?? '');
    $ob->br_need_quote_for = trim($_POST['br_need_quote_for'] ?? '');
    $ob->br_purchase_time = trim($_POST['br_purchase_time'] ?? '');
    $ob->br_need_for = trim($_POST['br_need_for'] ?? '');
    $ob->br_requirement_frequency = trim($_POST['br_requirement_frequency'] ?? '');
    $ob->br_pic = $_FILES['br_pic']['name'] ?? '';

    if ($ob->valid()) {
        $ob->update();
    }

    $_SESSION['msg'] = $ob->msg;
    header('Location: ../post-buy-req-email.php?admn_br_id=' . $ob->br_id);
    exit();
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
        /**
         * عرض الفئات بناءً على الفئة الرئيسية
         */
        function showCategory() {
            var pc_id = document.getElementById('mcat_id').value;
            $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
                $('#pc_id').html(data);
                showSubcat();
            });
        }
        
        /**
         * عرض الفئات الفرعية
         */
        function showSubcat() {
            var pc_id = document.getElementById('pc_id').value;
            $.get("showSubcat.php", {q: pc_id}, function(data) {
                $('#br_pc_id').html(data);
            });
        }
        
        /**
         * التحقق من صحة النموذج قبل الإرسال
         * @return {boolean} true إذا كانت البيانات صحيحة
         */
        function validForm() {
            var mcat_id = document.getElementById('mcat_id');
            var pc_id = document.getElementById('pc_id');
            var br_pc_id = document.getElementById('br_pc_id');
            var br_pd_name = document.getElementById('br_pd_name');
            var br_requirement = document.getElementById('br_requirement');
            var br_estimate_qty = document.getElementById('br_estimate_qty');
            var br_estimate_qty_unit = document.getElementById('br_estimate_qty_unit');
            
            var message = "";
            var valid = true;
            
            if (mcat_id.value == '') {
                message = "Please select the Main Category.";
                mcat_id.focus();
                valid = false;
            } else if (pc_id.value == '' || pc_id.value == null) {
                message = 'Please select Category.';
                pc_id.focus();
                valid = false;
            } else if (br_pc_id.value == '' || br_pc_id.value == null) {
                message = 'Please select Sub-Category.';
                br_pc_id.focus();
                valid = false;
            } else if (br_pd_name.value == '' || br_pd_name.value == null) {
                message = 'Please enter Products / Services.';
                br_pd_name.focus();
                valid = false;
            } else if (!isNaN(br_pd_name.value)) {
                message = 'Please enter valid Products / Services Name.';
                br_pd_name.focus();
                valid = false;
            } else if (br_requirement.value == '' || br_requirement.value == null) {
                message = 'Please describe Buying Requirements.';
                br_requirement.focus();
                valid = false;
            } else if (!isNaN(br_requirement.value)) {
                message = 'Please describe valid Buying Requirements.';
                br_requirement.focus();
                valid = false;
            } else if (br_requirement.value.length < 50) {
                message = 'Buying Requirements must be at least 50 characters length.';
                br_requirement.focus();
                valid = false;
            } else if (br_estimate_qty.value == '' || br_estimate_qty.value == null) {
                message = 'Please enter Estimated Quantity.';
                br_estimate_qty.focus();
                valid = false;
            } else if (isNaN(br_estimate_qty.value)) {
                message = 'Please enter valid Estimated Quantity.';
                br_estimate_qty.focus();
                valid = false;
            } else if (br_estimate_qty_unit.value == '' || br_estimate_qty_unit.value == null) {
                message = 'Please select Estimated Quantity measurement Unit.';
                br_estimate_qty_unit.focus();
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
                        <a href="buyreq-view.php">Manage Buy Requirement</a>
                    </li>
                    <li class="active">Buy Requirement Edit</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Buy Requirement
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Buy Requirement Edit
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validForm();">
                            
                         
                            
                            
                            
                            
                            
                            <input type="hidden" id="br_id" name="br_id" value="<?php echo htmlspecialchars($row->br_id ?? ''); ?>" />

                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>
                            
                            <!-- Main Category -->
                            <div class="form-group">
                                <?php
                                $mcat_sql = "SELECT * FROM product_category 
                                             WHERE pc_id = (SELECT pc_parent_id FROM product_category 
                                                            WHERE pc_id = " . (int)($row->pc_parent_id ?? 0) . " 
                                                              AND pc_status = '1') 
                                               AND pc_status = '1'";
                                $mcat_res = mysqli_query($con, $mcat_sql);
                                $mcat_row = $mcat_res ? mysqli_fetch_object($mcat_res) : null;
                                ?>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
                                <div class="col-sm-9">
                                    <select id="mcat_id" name="mcat_id" onchange="showCategory();" class="form-control" style="width:auto;">
                                        <?php
                                        $sql_mcat = "SELECT * FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1'";
                                        $res_mcat = mysqli_query($con, $sql_mcat);
                                        while ($row_mcat = mysqli_fetch_object($res_mcat)) {
                                            $selected = ($row_mcat->pc_id == ($mcat_row->pc_id ?? 0)) ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$row_mcat->pc_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($row_mcat->pc_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
                                <div class="col-sm-8">
                                    <select id="pc_id" name="pc_id" onchange="showSubcat();" class="form-control" style="width:auto;">
                                        <?php
                                        $sql_pc = "SELECT * FROM product_category 
                                                   WHERE pc_parent_id != '0' 
                                                     AND pc_parent_id = " . (int)($mcat_row->pc_id ?? 0);
                                        $res_pc = mysqli_query($con, $sql_pc);
                                        while ($row_pc = mysqli_fetch_object($res_pc)) {
                                            $selected = ($row_pc->pc_id == ($row->pc_parent_id ?? 0)) ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$row_pc->pc_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($row_pc->pc_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Sub-Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
                                <div class="col-sm-8">
                                    <select id="br_pc_id" name="br_pc_id" class="form-control" style="width:auto;">
                                        <option value="0"> - Select Sub-Category - </option>
                                        <?php
                                        $sql_spc = "SELECT * FROM product_category 
                                                    WHERE pc_parent_id = (SELECT pc_parent_id FROM product_category 
                                                                         WHERE pc_id = " . (int)($row->br_pc_id ?? 0) . ")";
                                        $res_spc = mysqli_query($con, $sql_spc);
                                        while ($row_spc = mysqli_fetch_object($res_spc)) {
                                            $selected = ($row_spc->pc_id == ($row->br_pc_id ?? 0)) ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$row_spc->pc_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($row_spc->pc_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Products / Services -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products / Services:</label>
                                <div class="col-sm-9">
                                    <input name="br_pd_name" id="br_pd_name" class="form-control" type="text" 
                                           value="<?php echo htmlspecialchars($row->br_pd_name ?? ''); ?>" style="width:400px;" />
                                </div>
                            </div>
                            
                            <!-- Requirement in Detail -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Requirement in Detail:</label>
                                <div class="col-sm-8">
                                    <textarea id="br_requirement" name="br_requirement" class="form-control" rows="5" style="width:400px;"><?php echo htmlspecialchars($row->br_requirement ?? ''); ?></textarea>
                                    <span class="help-block">Minimum 50 Characters.</span>
                                </div>
                            </div>

                            <!-- Estimated Quantity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Estimated Quantity:</label>
                                <div class="col-sm-8">
                                    <input name="br_estimate_qty" id="br_estimate_qty" class="form-control" type="text" 
                                           value="<?php echo ($row->br_estimate_qty ?? '0.00') != '0.00' ? htmlspecialchars($row->br_estimate_qty) : ''; ?>" 
                                           style="width:150px; display:inline-block;" />
                                    <select name="br_estimate_qty_unit" id="br_estimate_qty_unit" class="form-control" style="width:150px; display:inline-block;">
                                        <option value="">--Select Unit--</option>
                                        <?php
                                        $sql_mu = "SELECT * FROM measurement_unit WHERE mu_status = '1'";
                                        $res_mu = mysqli_query($con, $sql_mu);
                                        while ($row_mu = mysqli_fetch_object($res_mu)) {
                                            $selected = ($row->br_estimate_qty_unit ?? 0) == $row_mu->mu_id ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$row_mu->mu_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($row_mu->mu_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Location Preferences -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Preferences:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="br_preferred_supplier_location" class="ace" value="abroad" 
                                                   <?php echo ($row->br_preferred_supplier_location == 'abroad') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Abroad Only</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_preferred_supplier_location" class="ace" value="any" 
                                                   <?php echo ($row->br_preferred_supplier_location == 'any') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Abroad + Domestic</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_preferred_supplier_location" class="ace" value="domestic" 
                                                   <?php echo ($row->br_preferred_supplier_location == 'domestic') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Domestic Only</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_preferred_supplier_location" class="ace" value="my_city" 
                                                   <?php echo ($row->br_preferred_supplier_location == 'my_city') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> My City Only</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Approximate Order Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Approximate Order Value:</label>
                                <div class="col-sm-8">
                                    <input name="br_apprx_order_value" id="br_apprx_order_value" class="form-control" type="text" 
                                           value="<?php echo ($row->br_apprx_order_value ?? '0.00') != '0.00' ? htmlspecialchars($row->br_apprx_order_value) : ''; ?>" 
                                           style="width:150px; display:inline-block;" />
                                    <select name="br_apprx_order_currency" id="br_apprx_order_currency" class="form-control" style="width:100px; display:inline-block;">
                                        <option value="">--Currency--</option>
                                        <?php
                                        $sql_curr = "SELECT DISTINCT cn_currency FROM country WHERE cn_currency != '' AND cn_status = '1'";
                                        $res_curr = mysqli_query($con, $sql_curr);
                                        while ($row_curr = mysqli_fetch_object($res_curr)) {
                                            $selected = ($row_curr->cn_currency == ($row->br_apprx_order_currency ?? '')) ? 'selected="selected"' : '';
                                            echo '<option value="' . htmlspecialchars($row_curr->cn_currency) . '" ' . $selected . '>' . 
                                                 htmlspecialchars($row_curr->cn_currency) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Product Application/Usage -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Describe product application/usage:</label>
                                <div class="col-sm-8">
                                    <textarea id="br_description" name="br_description" class="form-control" rows="3" style="width:400px;"><?php echo htmlspecialchars($row->br_description ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Website -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
                                <div class="col-sm-8">
                                    <input name="br_website" id="br_website" class="form-control" type="text" 
                                           value="<?php echo htmlspecialchars($row->br_website ?? ''); ?>" style="width:400px;" />
                                </div>
                            </div>
                            
                            <!-- Need Quotations -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Need Quotations:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input name="br_need_quote_for" class="ace" type="radio" value="To Make Purchase" 
                                                   <?php echo ($row->br_need_quote_for == 'To Make Purchase') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> To Make Purchase</span>
                                        </label>
                                        <label>
                                            <input name="br_need_quote_for" class="ace" type="radio" value="To Know Price Only" 
                                                   <?php echo ($row->br_need_quote_for == 'To Know Price Only') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> To Know Price Only</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Purchase Time -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">How soon want to purchase:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="br_purchase_time" class="ace" value="Immediate" 
                                                   <?php echo ($row->br_purchase_time == 'Immediate') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Immediate</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_purchase_time" class="ace" value="Within 15 Days" 
                                                   <?php echo ($row->br_purchase_time == 'Within 15 Days') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Within 15 Days</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_purchase_time" class="ace" value="Within 1 Month" 
                                                   <?php echo ($row->br_purchase_time == 'Within 1 Month') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Within 1 Month</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Why need this -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Why need this:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="br_need_for" class="ace" value="For Reselling" 
                                                   <?php echo ($row->br_need_for == 'For Reselling') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> For Reselling</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_need_for" class="ace" value="For Your End Use" 
                                                   <?php echo ($row->br_need_for == 'For Your End Use') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> For Your End Use</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="br_need_for" class="ace" value="As Raw Material" 
                                                   <?php echo ($row->br_need_for == 'As Raw Material') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> As Raw Material</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Requirement Frequency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Is this your:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input name="br_requirement_frequency" class="ace" type="radio" value="One Time Requirement" 
                                                   <?php echo ($row->br_requirement_frequency == 'One Time Requirement') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> One Time Requirement</span>
                                        </label>
                                        <label>
                                            <input name="br_requirement_frequency" class="ace" type="radio" value="Regular Requirement" 
                                                   <?php echo ($row->br_requirement_frequency == 'Regular Requirement') ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> Regular Requirement</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                          
                              <!-- Current Image -->
                              
                              
                            <div class="form-group">
                                <!-- Current Image -->
<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Current Image</label>
    <div class="col-sm-9">
        <?php
        // جلب الصورة من قاعدة البيانات
        $br_id = isset($_GET['br_id']) ? (int)$_GET['br_id'] : 0;
        $img_sql = "SELECT br_pic FROM buy_requirement WHERE br_id = $br_id";
        $img_res = mysqli_query($con, $img_sql);
        $img_row = mysqli_fetch_assoc($img_res);
        $current_image_name = $img_row['br_pic'] ?? '';

        if (!empty($current_image_name)) {
            // ✅ البحث عن الصورة في المسارات الممكنة
            $image_paths = [
                '../upload/buy_requirement/',
                '../upload/image_gallery/',
                '../upload/buy_requirement/thumb/'
            ];
            
            $found = false;
            foreach ($image_paths as $path) {
                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path . $current_image_name;
                if (file_exists($full_path)) {
                    echo '<div><img src="' . $path . htmlspecialchars($current_image_name) . '" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px;" /></div>';
                    echo '<p class="help-block">ملف الصورة الحالي: ' . htmlspecialchars($current_image_name) . '</p>';
                    echo '<p class="help-block">المسار: ' . htmlspecialchars($path) . '</p>';
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo '<p class="text-muted">الصورة غير موجودة على الخادم (قد تكون محذوفة)</p>';
            }
        } else {
            echo '<p class="text-muted">لا توجد صورة حالية مرفوعة.</p>';
        }
        ?>
    </div>
</div>
                                </div>
                            </div>
                            
                            <!-- Upload New Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload New Image (Optional)</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
  
  <!-- عرض الصورة الحالية -->
<!-- عرض الصورة الحالية -->
<?php
$br_id = isset($_GET['br_id']) ? (int)$_GET['br_id'] : 0;
if ($br_id > 0) {
    $img_sql = "SELECT br_pic FROM buy_requirement WHERE br_id = $br_id";
    $img_res = mysqli_query($con, $img_sql);
    if ($img_res && mysqli_num_rows($img_res) > 0) {
        $img_row = mysqli_fetch_assoc($img_res);
        $current_image = $img_row['br_pic'] ?? '';

        echo '<!-- br_pic from DB: ' . ($current_image ?: 'EMPTY') . ' -->';

        if (!empty($current_image)) {
            // ✅ البحث عن الصورة في المسارات الممكنة
            $image_paths = [
                '../upload/buy_requirement/',
                '../upload/image_gallery/',
                '../upload/buy_requirement/thumb/'
            ];
            
            $found = false;
            foreach ($image_paths as $path) {
                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path . $current_image;
                if (file_exists($full_path)) {
                    echo '<div style="margin-bottom: 15px;">';
                    echo '<label>الصورة الحالية:</label><br>';
                    echo '<img src="' . $path . htmlspecialchars($current_image) . '" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;" />';
                    echo '<p style="font-size: 12px; margin-top: 5px;">' . htmlspecialchars($current_image) . '</p>';
                    echo '</div>';
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo '<p>الصورة غير موجودة على الخادم (قد تكون محذوفة)</p>';
            }
        } else {
            echo '<p>لا توجد صورة حالية</p>';
        }
    }
}
?>

<!-- حقل رفع صورة جديدة -->
<div class="form-group">
    <label>Upload New Image (Optional)</label>
    <input name="br_pic" id="id-input-file-2" type="file" accept="image/*">
    <p class="help-block">اتركه فارغاً للحفاظ على الصورة الحالية.</p>
</div>
                            </div>
                            
                            <!-- Posted By -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posted By:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($brow->bnsprof_compname ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn btn-default" type="button" onclick="window.location.href='buyreq-view.php'">
                                        <i class="icon-reply bigger-110"></i> Back to List
                                    </button>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <br clear="all" />
                </div>
            </div>
            <br clear="all" />
        </div>
        
        <?php include "includes/footer.php" ?>
    </div>
</div>

<!-- JavaScript Libraries -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!--[if lte IE 8]>
<script src="assets/js/excanvas.min.js"></script>
<![endif]-->

<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>

<!-- ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    $('#id-disable-check').on('click', function() {
        var inp = $('#form-input-readonly').get(0);
        if(inp.hasAttribute('disabled')) {
            inp.setAttribute('readonly' , 'true');
            inp.removeAttribute('disabled');
            inp.value="This text field is readonly!";
        }
        else {
            inp.setAttribute('disabled' , 'disabled');
            inp.removeAttribute('readonly');
            inp.value="This text field is disabled!";
        }
    });

    $(".chosen-select").chosen(); 
    $('#chosen-multiple-style').on('click', function(e){
        var target = $(e.target).find('input[type=radio]');
        var which = parseInt(target.val());
        if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
        else $('#form-field-select-4').removeClass('tag-input-style');
    });

    $('[data-rel=tooltip]').tooltip({container:'body'});
    $('[data-rel=popover]').popover({container:'body'});

    $('textarea[class*=autosize]').autosize({append: "\n"});
    $('textarea.limited').inputlimiter({
        remText: '%n character%s remaining...',
        limitText: 'max allowed : %n.'
    });

    $.mask.definitions['~']='[+-]';
    $('.input-mask-date').mask('99/99/9999');
    $('.input-mask-phone').mask('(999) 999-9999');
    $('.input-mask-eyescript').mask('~9.99 ~9.99 999');
    $(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){
        alert("You typed the following: "+this.val());
    }});

    $( "#input-size-slider" ).css('width','200px').slider({
        value:1,
        range: "min",
        min: 1,
        max: 8,
        step: 1,
        slide: function( event, ui ) {
            var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
            var val = parseInt(ui.value);
            $('#form-field-4').attr('class', sizing[val]).val('.'+sizing[val]);
        }
    });

    $( "#input-span-slider" ).slider({
        value:1,
        range: "min",
        min: 1,
        max: 12,
        step: 1,
        slide: function( event, ui ) {
            var val = parseInt(ui.value);
            $('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
        }
    });

    $( "#slider-range" ).css('height','200px').slider({
        orientation: "vertical",
        range: true,
        min: 0,
        max: 100,
        values: [ 17, 67 ],
        slide: function( event, ui ) {
            var val = ui.values[$(ui.handle).index()-1]+"";
            if(! ui.handle.firstChild ) {
                $(ui.handle).append("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>");
            }
            $(ui.handle.firstChild).show().children().eq(1).text(val);
        }
    }).find('a').on('blur', function(){
        $(this.firstChild).hide();
    });

    $( "#slider-range-max" ).slider({
        range: "max",
        min: 1,
        max: 10,
        value: 2
    });

    $( "#eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
        var value = parseInt( $( this ).text(), 10 );
        $( this ).empty().slider({
            value: value,
            range: "min",
            animate: true
        });
    });

    $('#id-input-file-1 , #id-input-file-2').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false
    });

    $('#id-input-file-3').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'icon-cloud-upload',
        droppable:true,
        thumbnail:'small',
        preview_error : function(filename, error_code) {
            //error_code values: 1 = 'FILE_LOAD_FAILED', 2 = 'IMAGE_LOAD_FAILED', 3 = 'THUMBNAIL_FAILED'
        }
    }).on('change', function(){
        //console.log($(this).data('ace_input_files'));
    });

    $('#id-file-format').removeAttr('checked').on('change', function() {
        var before_change;
        var btn_choose;
        var no_icon;
        if(this.checked) {
            btn_choose = "Drop images here or click to choose";
            no_icon = "icon-picture";
            before_change = function(files, dropped) {
                var allowed_files = [];
                for(var i = 0 ; i < files.length; i++) {
                    var file = files[i];
                    if(typeof file === "string") {
                        if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
                    }
                    else {
                        var type = $.trim(file.type);
                        if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
                                || ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) ) ) 
                            continue;
                    }
                    allowed_files.push(file);
                }
                if(allowed_files.length == 0) return false;
                return allowed_files;
            }
        }
        else {
            btn_choose = "Drop files here or click to choose";
            no_icon = "icon-cloud-upload";
            before_change = function(files, dropped) {
                return files;
            }
        }
        var file_input = $('#id-input-file-3');
        file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon});
        file_input.ace_file_input('reset_input');
    });

    $('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
        .on('change', function(){
            //alert(this.value)
        });
    $('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
    $('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});

    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });
    $('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
        $(this).next().focus();
    });

    $('#timepicker1').timepicker({
        minuteStep: 1,
        showSeconds: true,
        showMeridian: false
    }).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });

    $('#colorpicker1').colorpicker();
    $('#simple-colorpicker-1').ace_colorpicker();

    $(".knob").knob();

    var tag_input = $('#form-field-tags');
    if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) 
    {
        tag_input.tag({
            placeholder: tag_input.attr('placeholder'),
            source: ace.variable_US_STATES,
        });
    }
    else {
        tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
    }

    $('#modal-form input[type=file]').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'icon-cloud-upload',
        droppable:true,
        thumbnail:'large'
    });

    $('#modal-form').on('shown.bs.modal', function () {
        $(this).find('.chosen-container').each(function(){
            $(this).find('a:first-child').css('width' , '210px');
            $(this).find('.chosen-drop').css('width' , '210px');
            $(this).find('.chosen-search input').css('width' , '200px');
        });
    });
});
</script>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>