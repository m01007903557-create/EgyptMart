<?php
/**
 * File: field-view.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: عرض وإدارة الحقول الإضافية حسب التصنيفات
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء الجلسة
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

// معالجة الرسائل من الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// جلب التصنيفات للقوائم المنسدلة
$main_categories = [];
$result = mysqli_query($con, "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1' ORDER BY pc_name");
while ($row = mysqli_fetch_assoc($result)) {
    $main_categories[] = $row;
}

// متغيرات التصفية
$mcat_id = isset($_GET['mcat_id']) ? (int)$_GET['mcat_id'] : 0;
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$af_pc_id = isset($_GET['af_pc_id']) ? (int)$_GET['af_pc_id'] : 0;
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
                    <li class="active">Additional Fields</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>Additional Fields Management</h1>
                </div>
                
                <?php if (!empty($msg)): ?>
                    <div id="msg"><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-xs-12">
                        <a href="field-add.php" class="btn btn-primary">
                            <i class="icon-plus"></i> Add New Field
                        </a>
                    </div>
                </div>
                
                <!-- Filter Form -->
                <form class="form-horizontal" method="get">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Main Category</label>
                        <div class="col-sm-4">
                            <select name="mcat_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">-- All --</option>
                                <?php foreach ($main_categories as $cat): ?>
                                    <option value="<?php echo $cat['pc_id']; ?>" <?php echo ($mcat_id == $cat['pc_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['pc_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
                
                <!-- Fields List -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sub Category</th>
                                <th>Field Name</th>
                                <th>Field Label</th>
                                <th>Field Type</th>
                                <th>Actions</th>
                            </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT af.*, pc.pc_name as subcat_name 
                                    FROM additional_field af
                                    LEFT JOIN product_category pc ON af.af_pc_id = pc.pc_id
                                    WHERE 1=1";
                            
                            if ($mcat_id > 0) {
                                $sql .= " AND pc.pc_parent_id IN (SELECT pc_id FROM product_category WHERE pc_parent_id = '$mcat_id' OR pc_id = '$mcat_id')";
                            }
                            
                            $sql .= " ORDER BY af.af_id DESC";
                            
                            $result = mysqli_query($con, $sql);
                            
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo (int)$row['af_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['subcat_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['af_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['af_label']); ?></td>
                                <td>
                                    <?php
                                    $types = [
                                        'text' => 'Text Box',
                                        'textarea' => 'Text Area',
                                        'radio' => 'Radio Button',
                                        'checkbox' => 'Checkbox',
                                        'select' => 'Dropdown Select'
                                    ];
                                    echo $types[$row['af_type']] ?? $row['af_type'];
                                    ?>
                                </td>
                                <td>
                                    <a href="field-edit.php?id=<?php echo $row['af_id']; ?>" class="btn btn-xs btn-info">
                                        <i class="icon-edit"></i> Edit
                                    </a>
                                    <a href="field-del.php?af_id=<?php echo $row['af_id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this field?')">
                                        <i class="icon-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">No additional fields found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>
<?php ob_end_flush(); ?>