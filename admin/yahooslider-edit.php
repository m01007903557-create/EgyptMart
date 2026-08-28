<?php
/**
 * File: yahooslider-edit.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: تعديل شريحة في السلايدر
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

// الحصول على معرف السلايدر (يدعم id و aid)
$adv_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['aid']) ? (int)$_GET['aid'] : 0);

if ($adv_id <= 0) {
    die('معرف غير صالح');
}

/**
 * كلاس إدارة الإعلانات
 */
class editAdvertisement {
    public string $msg = '';
    public int $adv_id = 0;
    public string $adv_img = '';
    public string $adv_link = '';
    public int $adv_imagewidth = 0;
    public int $adv_imageheight = 0;
    public string $adv_title = '';
    public string $adv_description = '';
    public string $adv_country = '';
    public int $adv_global = 0;
    
    public function __construct(int $adv_id) {
        $this->adv_id = $adv_id;
    }
    
    /**
     * جلب تفاصيل الإعلان
     */
    public function detailsObj(): ?object {
        global $con;
        $sql = "SELECT * FROM yahoo_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) return null;
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $valid = true;
        // إضافة شروط التحقق إذا لزم الأمر
        return $valid;
    }
    
    /**
     * تحديث الإعلان
     */
    public function update(): void {
        global $con;
        
        if (!empty($_FILES["adv_img"]["name"])) {
            if ($_FILES["adv_img"]["error"] > 0) {
                $this->msg = '<div class="alert alert-danger">Error: ' . $_FILES["adv_img"]["error"] . '</div>';
                return;
            }
            
            // حذف الصورة القديمة
            $sqlImg = "SELECT adv_img FROM yahoo_slider WHERE adv_id = ?";
            $stmtImg = mysqli_prepare($con, $sqlImg);
            mysqli_stmt_bind_param($stmtImg, "i", $this->adv_id);
            mysqli_stmt_execute($stmtImg);
            $resultImg = mysqli_stmt_get_result($stmtImg);
            $rowImg = mysqli_fetch_object($resultImg);
            mysqli_stmt_close($stmtImg);
            
            if ($rowImg && !empty($rowImg->adv_img)) {
                $oldPath = "../upload/yahoo_slider/" . $rowImg->adv_img;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // معالجة الصورة الجديدة
            $ext = strtolower(pathinfo($_FILES['adv_img']['name'], PATHINFO_EXTENSION));
            $validExt = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($ext, $validExt)) {
                $imgSImage = new SimpleImage();
                $imgSImage->load($_FILES['adv_img']['tmp_name']);
                
                if ($this->adv_imagewidth > 0 && $this->adv_imageheight > 0) {
                    $imgSImage->resize($this->adv_imagewidth, $this->adv_imageheight);
                }
                
                $this->adv_img = $this->adv_imagewidth . rand(0, 9999) . $this->adv_imageheight . $_FILES['adv_img']['name'];
                $imgSImage->save("../upload/yahoo_slider/" . $this->adv_img);
            } else {
                $this->msg = '<div class="alert alert-danger">Invalid image format. Please upload JPG, PNG or GIF.</div>';
                return;
            }
            
            // تحديث مع الصورة
            $sql = "UPDATE yahoo_slider SET 
                        adv_img = ?,
                        adv_link = ?,
                        adv_imagewidth = ?,
                        adv_imageheight = ?,
                        adv_title = ?,
                        adv_description = ?,
                        adv_global = ?,
                        adv_country = ?
                    WHERE adv_id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssiissssi", 
                $this->adv_img,
                $this->adv_link,
                $this->adv_imagewidth,
                $this->adv_imageheight,
                $this->adv_title,
                $this->adv_description,
                $this->adv_global,
                $this->adv_country,
                $this->adv_id
            );
        } else {
            // تحديث بدون صورة
            $sql = "UPDATE yahoo_slider SET 
                        adv_link = ?,
                        adv_title = ?,
                        adv_description = ?,
                        adv_global = ?,
                        adv_country = ?
                    WHERE adv_id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", 
                $this->adv_link,
                $this->adv_title,
                $this->adv_description,
                $this->adv_global,
                $this->adv_country,
                $this->adv_id
            );
        }
        
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger">Error updating advertisement</div>';
        }
        
        if ($stmt) mysqli_stmt_close($stmt);
    }
}

// استرجاع رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// تهيئة الكلاس
$ob = new editAdvertisement($adv_id);
$row = $ob->detailsObj();

if (!$row) {
    die('السجل غير موجود');
}

// تعيين القيم الافتراضية
$adv_imagewidth = $row->adv_imagewidth ?? 0;
$adv_imageheight = $row->adv_imageheight ?? 0;
$adv_link = $row->adv_link ?? '';
$adv_title = $row->adv_title ?? '';
$adv_description = $row->adv_description ?? '';
$adv_global = $row->adv_global ?? 0;
$adv_country = explode(',', $row->adv_country ?? '');
$adv_img = $row->adv_img ?? '';

// معالجة التحديث
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    $ob->adv_imagewidth = (int)($_POST['adv_imagewidth'] ?? 0);
    $ob->adv_imageheight = (int)($_POST['adv_imageheight'] ?? 0);
    $ob->adv_link = trim($_POST['adv_link'] ?? '');
    $ob->adv_title = trim($_POST['adv_title'] ?? '');
    $ob->adv_description = trim($_POST['adv_description'] ?? '');
    $ob->adv_global = (int)($_POST['adv_global'] ?? 0);
    $ob->adv_country = isset($_POST['adv_country']) ? implode(',', array_map('intval', $_POST['adv_country'])) : '';
    
    if ($ob->valid()) {
        $ob->update();
    }
    $_SESSION['msg'] = $ob->msg;
    header("Location: yahooslider-edit.php?id=" . $adv_id);
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li><a href="yahooslider-view.php">Yahoo Slider</a></li>
                    <li class="active">Edit Slider</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>Yahoo Slider <small><i class="icon-double-angle-right"></i> Edit Slider</small></h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" id="editForm">
                            <div id="msg"><?php echo $msg; ?></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Image Width & Height</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="adv_imagewidth" value="<?php echo $adv_imagewidth; ?>">
                                    <input type="hidden" name="adv_imageheight" value="<?php echo $adv_imageheight; ?>">
                                    <?php echo $adv_imagewidth . " x " . $adv_imageheight; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Country:</label>
                                <div class="col-sm-8">
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select">
                                        <?php
                                        $countries = mysqli_query($con, "SELECT cn_id, cn_name FROM country WHERE cn_status = 1 ORDER BY cn_name");
                                        while ($cntry = mysqli_fetch_object($countries)) {
                                            $selected = in_array($cntry->cn_id, $adv_country) ? 'selected="selected"' : '';
                                            echo '<option value="' . $cntry->cn_id . '" ' . $selected . '>' . htmlspecialchars($cntry->cn_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <input type="hidden" name="adv_global" value="0">
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Link</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo htmlspecialchars($adv_link); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Title</label>
                                <div class="col-sm-9">
                                    <input name="adv_title" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo htmlspecialchars($adv_title); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Description</label>
                                <div class="col-sm-9">
                                    <textarea name="adv_description" rows="10" cols="60" required><?php echo htmlspecialchars($adv_description); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Image</label>
                                <div class="col-sm-9">
                                    <?php if (!empty($adv_img)): ?>
                                        <img src="../upload/yahoo_slider/<?php echo htmlspecialchars($adv_img); ?>" style="max-width: 200px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload New Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file">
                                    </div>
                                    <span class="help-inline">Leave empty to keep current image</span>
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="reset"><i class="icon-undo bigger-110"></i> Reset</button>
                                    <a href="yahooslider-view.php" class="btn btn-default">Cancel</a>
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

<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
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