<?php
/**
 * File: testi-add.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: إضافة شهادات تقدير (Testimonials) إلى النظام
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة شهادات التقدير
 */
class editheadersld {
    private string $msg = '';
    private string $testi_type = '';
    private string $testi_name = '';
    private string $testi_details = '';
    private string $testi_email = '';
    private string $testi_mobile = '';
    private string $testi_image = '';
    private int $testi_cn_id = 0;
    private string $testi_business_name = '';
    
    public function __construct() {
        $_SESSION['testi_type'] = $this->testi_type;
        $_SESSION['testi_name'] = $this->testi_name;
        $_SESSION['testi_details'] = $this->testi_details;
        $_SESSION['testi_email'] = $this->testi_email;
        $_SESSION['testi_mobile'] = $this->testi_mobile;
        $_SESSION['testi_business_name'] = $this->testi_business_name;
        $_SESSION['testi_cn_id'] = $this->testi_cn_id;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $valid = true;
        
        if (empty($this->testi_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter name</div>';
            $valid = false;
        } elseif (empty($this->testi_details)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter details</div>';
            $valid = false;
        } elseif (empty($this->testi_email)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Emails</div>';
            $valid = false;
        } elseif (!filter_var($this->testi_email, FILTER_VALIDATE_EMAIL)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid Emails</div>';
            $valid = false;
        } elseif (empty($this->testi_mobile)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Credits must be in multiple of 20</div>';
            $valid = false;
        } elseif (!is_numeric($this->testi_mobile)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid mobile number</div>';
            $valid = false;
        } elseif ($this->testi_cn_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose country</div>';
            $valid = false;
        } elseif (empty($this->testi_image)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload image</div>';
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
     * إضافة شهادة التقدير
     */
    public function update(): void {
        global $con;
        
        $ext = strtolower(pathinfo($this->testi_image, PATHINFO_EXTENSION));
        $validEXT = ['jpg', 'png', 'jpeg', 'gif', 'pdf'];
        
        if (in_array($ext, $validEXT)) {
            $tempFile = $_FILES['testi_image']['tmp_name'];
            
            if (file_exists($tempFile) && is_uploaded_file($tempFile)) {
                $imgSImage = new SimpleImage();
                $imgSImage->load($tempFile);
                
                $image = 'TESTIIMG-' . rand(0, 9999) . $this->testi_image;
                $imgSImage->resize(76, 76);
                $imgSImage->save("../upload/testimonial_img/" . $image);
                
                $sql = "INSERT INTO testimonials SET
                        testi_details = ?,
                        testi_name = ?,
                        testi_type = ?,
                        testi_image = ?,
                        testi_email = ?,
                        testi_mobile = ?,
                        testi_cn_id = ?,
                        testi_business_name = ?,
                        testi_updated_date = NOW()";
                
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssis", 
                    $this->testi_details,
                    $this->testi_name,
                    $this->testi_type,
                    $image,
                    $this->testi_email,
                    $this->testi_mobile,
                    $this->testi_cn_id,
                    $this->testi_business_name
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Testimonials Added successfully</div>';
                } else {
                    $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Error adding testimonial</div>';
                }
                mysqli_stmt_close($stmt);
            } else {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload a valid image file.</div>';
            }
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an image with valid file extension (jpg, png, jpeg, gif, pdf).</div>';
        }
        
        // مسح الجلسة
        unset($_SESSION['testi_type'], $_SESSION['testi_name'], $_SESSION['testi_details'],
              $_SESSION['testi_email'], $_SESSION['testi_mobile'], $_SESSION['testi_business_name'],
              $_SESSION['testi_cn_id']);
    }
    
    public function getMsg(): string {
        return $this->msg;
    }
    
    // Setters
    public function setTestiType(string $value): void { $this->testi_type = trim($value); }
    public function setTestiName(string $value): void { $this->testi_name = trim($value); }
    public function setTestiDetails(string $value): void { $this->testi_details = trim($value); }
    public function setTestiEmail(string $value): void { $this->testi_email = trim($value); }
    public function setTestiMobile(string $value): void { $this->testi_mobile = trim($value); }
    public function setTestiBusinessName(string $value): void { $this->testi_business_name = trim($value); }
    public function setTestiCnId(int $value): void { $this->testi_cn_id = $value; }
    public function setTestiImage(string $value): void { $this->testi_image = $value; }
}

// استرجاع قيم الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$testi_type = $_SESSION['testi_type'] ?? '';
$testi_name = $_SESSION['testi_name'] ?? '';
$testi_details = $_SESSION['testi_details'] ?? '';
$testi_email = $_SESSION['testi_email'] ?? '';
$testi_mobile = $_SESSION['testi_mobile'] ?? '';
$testi_business_name = $_SESSION['testi_business_name'] ?? '';
$testi_cn_id = (int)($_SESSION['testi_cn_id'] ?? 0);

unset($_SESSION['testi_type'], $_SESSION['testi_name'], $_SESSION['testi_details'],
      $_SESSION['testi_email'], $_SESSION['testi_mobile'], $_SESSION['testi_business_name'],
      $_SESSION['testi_cn_id']);

$ob = new editheadersld();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    $ob->setTestiType($_POST['testi_type'] ?? '');
    $ob->setTestiName($_POST['testi_name'] ?? '');
    $ob->setTestiDetails($_POST['testi_details'] ?? '');
    $ob->setTestiEmail($_POST['testi_email'] ?? '');
    $ob->setTestiMobile($_POST['testi_mobile'] ?? '');
    $ob->setTestiBusinessName($_POST['testi_business_name'] ?? '');
    $ob->setTestiCnId((int)($_POST['testi_cn_id'] ?? 0));
    $ob->setTestiImage($_FILES['testi_image']['name'] ?? '');
    
    if ($ob->valid()) {
        $ob->update();
    }
    $_SESSION['msg'] = $ob->getMsg();
    header("Location: testi-add.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>
<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <script type="text/javascript">
        function myvalid() {
            var testi_details = document.getElementById('testi_details');
            var testi_business_name = document.getElementById('testi_business_name');
            var testi_name = document.getElementById('testi_name');
            var testi_email = document.getElementById('testi_email');
            var testi_mobile = document.getElementById('testi_mobile');
            var testi_cn_id = document.getElementById('testi_cn_id');
            var testi_image = document.getElementById('testi_image');
            
            var message = "";
            var valid = true;
            
            if (testi_name.value == '' || testi_name.value == null) {
                message = 'Please enter Name';
                testi_name.focus();
                valid = false;
            } else if (testi_details.value == '') {
                message = 'Please enter details';
                testi_details.focus();
                valid = false;
            } else if (testi_business_name.value == '') {
                message = 'Please enter company name';
                testi_business_name.focus();
                valid = false;
            } else if (testi_email.value == '') {
                message = 'Please enter email';
                testi_email.focus();
                valid = false;
            } else if (testi_email.value.indexOf('@') == -1 || testi_email.value.indexOf('.') == -1) {
                message = 'Please enter valid email';
                testi_email.focus();
                valid = false;
            } else if (testi_mobile.value == '') {
                message = 'Please enter contact number';
                testi_mobile.focus();
                valid = false;
            } else if (isNaN(testi_mobile.value)) {
                message = 'Please enter valid contact number';
                testi_mobile.focus();
                valid = false;
            } else if (testi_cn_id.value == '') {
                message = 'Please choose country';
                valid = false;
            } else if (testi_image.value == '') {
                message = 'Please upload an image';
                valid = false;
            }
            
            if (!valid) {
                document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                document.getElementById('msg').className = "alert alert-danger";
            }
            return valid;
        }
        </script>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li><a href="testi-view.php">Manage Testimonials</a></li>
                    <li class="active">Testimonial Add</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>Manage Testimonials <small><i class="icon-double-angle-right"></i> Testimonial Add</small></h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="ctesti_edit" name="ctesti_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Type <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <select id="testi_type" name="testi_type" class="chosen-select">
                                        <option value="buyer" <?php echo ($testi_type == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                                        <option value="supplier" <?php echo ($testi_type == 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Name <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="testi_name" id="testi_name" class="col-xs-10 col-sm-5" type="text" value="<?php echo htmlspecialchars($testi_name); ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Details <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <textarea name="testi_details" id="testi_details" class="col-xs-10 col-sm-7"><?php echo htmlspecialchars($testi_details); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Company Name <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="testi_business_name" id="testi_business_name" class="col-xs-10 col-sm-5" type="text" value="<?php echo htmlspecialchars($testi_business_name); ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Email <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="testi_email" id="testi_email" class="col-xs-10 col-sm-5" type="text" value="<?php echo htmlspecialchars($testi_email); ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Contact <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="testi_mobile" id="testi_mobile" class="col-xs-10 col-sm-5" type="text" value="<?php echo htmlspecialchars($testi_mobile); ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Country <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <select id="testi_cn_id" name="testi_cn_id" class="chosen-select">
                                        <option value="">Select</option>
                                        <?php
                                        $cn_res = mysqli_query($con, "SELECT cn_id, cn_name FROM country WHERE cn_status = '1' ORDER BY cn_name");
                                        while ($cn_row = mysqli_fetch_object($cn_res)) {
                                            $selected = ($testi_cn_id == $cn_row->cn_id) ? 'selected' : '';
                                            echo '<option value="' . (int)$cn_row->cn_id . '" ' . $selected . '>' . htmlspecialchars($cn_row->cn_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="testi_image" id="id-input-file-2" type="file">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd"><i class="icon-ok bigger-110"></i>Add</button>
                                    <button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        $(".chosen-select").chosen();
        $('[data-rel=tooltip]').tooltip({container:'body'});
        
        $('#id-input-file-2').ace_file_input({
            no_file:'No File ...',
            btn_choose:'Choose',
            btn_change:'Change',
            droppable:false,
            thumbnail:false
        });
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>