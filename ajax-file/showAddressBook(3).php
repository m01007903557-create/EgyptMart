<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo '<div style="padding:20px; text-align:center;">الرجاء تسجيل الدخول أولاً</div>';
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$start = ($page - 1) * 10;

// استعلام لجلب جهات الاتصال
$sql = "SELECT DISTINCT 
            CASE 
                WHEN m.msg_from = $current_user THEN m.msg_to
                WHEN m.msg_to = $current_user THEN m.msg_from
            END as contact_id,
            u.fname, u.lname, u.email,
            bp.bnsprof_compname,
            MAX(m.msg_date) as last_contact
        FROM message m
        JOIN user u ON u.usr_id = CASE 
            WHEN m.msg_from = $current_user THEN m.msg_to
            WHEN m.msg_to = $current_user THEN m.msg_from
        END
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE m.msg_from = $current_user OR m.msg_to = $current_user
        GROUP BY contact_id
        ORDER BY last_contact DESC
        LIMIT $start, 10";

$res = mysqli_query($con, $sql);

if (!$res) {
    echo '<div style="padding:20px; text-align:center; color:red;">خطأ في الاستعلام</div>';
    exit;
}

if (mysqli_num_rows($res) == 0) {
    echo '<div style="padding:20px; text-align:center;">لا توجد جهات اتصال</div>';
    exit;
}

// عرض النتائج بدون أي شرطات مائلة
?>
<div class="ab2">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td class="ab3" width="390">Contact Name</td>
                <td class="ab3" width="80">Country</td>
                <td class="ab3" width="80">Rank</td>
                <td class="ab3" width="120">Last Contacted</td>
            </tr>
        </tbody>
    </table>
</div>

<?php while ($row = mysqli_fetch_assoc($res)): 
    $contact_id = $row['contact_id'];
    $full_name = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
    $email = htmlspecialchars($row['email'] ?? '');
    $compname = htmlspecialchars($row['bnsprof_compname'] ?? '');
    $last_contact = !empty($row['last_contact']) ? date("d M Y", strtotime($row['last_contact'])) : 'N/A';
?>
<div class="ab4">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td width="414">
                    <a onclick="detailContact(<?php echo $contact_id; ?>, 0, <?php echo $page; ?>)" style="cursor:pointer;">
                        <strong><?php echo htmlspecialchars($full_name); ?></strong> 
                        (<?php echo $email; ?>)<br>
                        <span><?php echo $compname; ?></span>
                    </a>
                </td>
                <td width="80">-</td>
                <td width="100">
                    <ul class="starRating">
                        <li class="starDactive"><span>1</span></li>
                        <li class="starDactive"><span>2</span></li>
                        <li class="starDactive"><span>3</span></li>
                        <li class="starDactive"><span>4</span></li>
                        <li class="starDactive"><span>5</span></li>
                    </ul>
                </td>
                <td width="120"><?php echo $last_contact; ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php endwhile; ?>