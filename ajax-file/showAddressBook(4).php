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
            u.fname, u.lname, u.email, u.country,
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
    echo '<div style="padding:20px; text-align:center; color:red;">خطأ في الاستعلام: ' . mysqli_error($con) . '</div>';
    exit;
}

if (mysqli_num_rows($res) == 0) {
    echo '<div style="padding:20px; text-align:center;">لا توجد جهات اتصال. قم بإرسال رسائل إلى أعضاء آخرين لتظهر هنا.</div>';
    exit;
}

// عرض النتائج بتنسيق منسق
?>
<style>
/* تنسيقات مدمجة لجدول جهات الاتصال */
.addressbook-table {
    width: 100%;
    border-collapse: collapse;
}
.addressbook-table th {
    background: #f2f2f2;
    padding: 12px;
    text-align: right;
    border-bottom: 2px solid #ddd;
    font-weight: bold;
}
.addressbook-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    vertical-align: top;
}
.contact-name {
    font-weight: bold;
    color: #333;
}
.contact-email {
    font-size: 12px;
    color: #666;
}
.contact-company {
    font-size: 12px;
    color: #999;
}
.contact-link {
    cursor: pointer;
    text-decoration: none;
}
.contact-link:hover {
    color: #25D366;
}
.star-rating {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
}
.star-rating li {
    display: inline-block;
    font-size: 16px;
    margin: 0 2px;
}
.starActive {
    color: #ffc107;
}
.starDactive {
    color: #ddd;
}
</style>

<div style="padding: 10px;">
    <table class="addressbook-table">
        <thead>
            <tr>
                <th>Contact Name</th>
                <th>Country</th>
                <th>Rank</th>
                <th>Last Contacted</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)): 
                $contact_id = $row['contact_id'];
                $full_name = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                if (empty($full_name)) $full_name = 'User ' . $contact_id;
                $email = htmlspecialchars($row['email'] ?? '');
                $compname = htmlspecialchars($row['bnsprof_compname'] ?? '');
                $last_contact = !empty($row['last_contact']) ? date("d M Y", strtotime($row['last_contact'])) : 'N/A';
                $country_name = htmlspecialchars(get_country_name((int)($row['country'] ?? 0)));
            ?>
            <tr>
                <td>
                    <a onclick="detailContact(<?php echo $contact_id; ?>, 0, <?php echo $page; ?>)" class="contact-link">
                        <div class="contact-name"><?php echo htmlspecialchars($full_name); ?></div>
                        <div class="contact-email"><?php echo $email; ?></div>
                        <?php if ($compname): ?>
                        <div class="contact-company"><?php echo $compname; ?></div>
                        <?php endif; ?>
                    </a>
                </td>
                <td><?php echo $country_name ?: '-'; ?></td>
                <td>
                    <ul class="star-rating">
                        <li class="starDactive">★</li>
                        <li class="starDactive">★</li>
                        <li class="starDactive">★</li>
                        <li class="starDactive">★</li>
                        <li class="starDactive">★</li>
                    </ul>
                </td>
                <td><?php echo $last_contact; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>