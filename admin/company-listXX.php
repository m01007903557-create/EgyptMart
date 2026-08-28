<?php
/**
 * File: company-list.php
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

// استعلام بسيط
$sql = "SELECT * FROM business_profile ORDER BY bnsprof_id DESC LIMIT 50";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("خطأ في الاستعلام: " . mysqli_error($con));
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
                    <li class="active">Companies</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="table-header">
                    <a href="company-add.php" class="btn btn-xs btn-success">Add Company</a>
                    <span style="float:right;">Total Companies</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                             <th>ID</th>
                             <th>Company Name</th>
                             <th>Address</th>
                             <th>City</th>
                             <th>Date</th>
                             <th>Action</th>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="center"><?php echo $row['bnsprof_id']; ?>\(td)
                                <td><strong><?php echo htmlspecialchars($row['bnsprof_compname']); ?></strong>\(td)
                                <td><?php echo htmlspecialchars($row['bnsprof_address1']); ?>\(td)
                                <td><?php echo htmlspecialchars($row['bnsprof_city']); ?>\(td)
                                <td><?php echo date('d M Y', strtotime($row['bnsprof_creation_date'])); ?>\(td)
                                <td class="center">
                                    <a href="company-edit.php?id=<?php echo $row['bnsprof_id']; ?>" class="btn btn-xs btn-info">Edit</a>
                                    <a href="?action=del&id=<?php echo $row['bnsprof_id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete?')">Delete</a>
                                \(td)
                              </tr>
                            <?php endwhile; ?>
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