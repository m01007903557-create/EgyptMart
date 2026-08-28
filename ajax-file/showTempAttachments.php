<?php
/**
 * File: ajax/showTempAttachments.php
 * Description: عرض المرفقات المؤقتة للرسائل
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المستخدم
if (!isset($_POST['usr']) || empty($_POST['usr'])) {
    http_response_code(400);
    die("User ID is required");
}

// تنظيف معرف المستخدم
$usr = trim($_POST['usr']);
$usr = preg_replace('/[^a-zA-Z0-9_]/', '', $usr);

if (empty($usr)) {
    http_response_code(400);
    die("Invalid user ID");
}

global $con;

// جلب المرفقات من قاعدة البيانات
$sql_tma = "SELECT tma_id, tma_file 
            FROM temp_msg_attachment 
            WHERE tma_usr_id = ? 
            ORDER BY tma_id DESC";

$stmt = mysqli_prepare($con, $sql_tma);
mysqli_stmt_bind_param($stmt, 's', $usr);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
    <div style="margin-bottom:5px;">
        <?php while ($row_tma = mysqli_fetch_object($result)): 
            $tma_id = (int)$row_tma->tma_id;
            $tma_file = htmlspecialchars($row_tma->tma_file ?? '', ENT_QUOTES, 'UTF-8');
        ?>
        <div>
            <span style="float:left;"><?php echo $tma_file; ?></span>
            <span style="padding-left:10px;vertical-align:middle">
                <a onclick="delAttachment('<?php echo $tma_id; ?>', '<?php echo htmlspecialchars($usr, ENT_QUOTES); ?>');" style="cursor:pointer;">
                    <img src="./images/del-attachment.png" alt="Delete" style="border:none;">
                </a>
            </span>
            <div style="clear:both;"></div>
        </div>
        <?php endwhile; ?>
    </div>
<?php 
endif;

mysqli_stmt_close($stmt);
?>