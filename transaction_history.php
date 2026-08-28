<?php
/**
 * File: transaction_history.php
 * Description: عرض سجل المعاملات المالية (شراء كريديت، استخدامه في طلبات الشراء، إلخ)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "transaction_history.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// =============================================
// إعدادات التصفح (Pagination)
// =============================================
$pageno = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
if ($pageno < 1) $pageno = 1;

// حساب إجمالي المعاملات
$total_sql = "SELECT COUNT(*) as total FROM billing_history WHERE bh_status = '1' AND bh_usr_id = ?";
$stmt_total = mysqli_prepare($con, $total_sql);
mysqli_stmt_bind_param($stmt_total, 'i', $uid);
mysqli_stmt_execute($stmt_total);
$total_result = mysqli_stmt_get_result($stmt_total);
$total_row = mysqli_fetch_assoc($total_result);
$total_transactions = (int)($total_row['total'] ?? 0);
mysqli_stmt_close($stmt_total);

$per_page = 10;
$total_pages = (int)ceil($total_transactions / $per_page);
if ($pageno > $total_pages) $pageno = $total_pages;

$start_limit = $per_page * ($pageno - 1);

// جلب المعاملات للصفحة الحالية
$sql = "SELECT * FROM billing_history 
        WHERE bh_status = '1' AND bh_usr_id = ? 
        ORDER BY bh_updated_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $uid, $start_limit, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$transactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transactions[] = $row;
}
mysqli_stmt_close($stmt);

// حساب إجمالي عدد طلبات الشراء المشتراه
$tot_lead_sql = "SELECT COUNT(*) as total FROM billing_history 
                 WHERE bh_status = '1' AND bh_type = '1' AND bh_usr_id = ?";
$stmt_lead = mysqli_prepare($con, $tot_lead_sql);
mysqli_stmt_bind_param($stmt_lead, 'i', $uid);
mysqli_stmt_execute($stmt_lead);
$tot_lead_result = mysqli_stmt_get_result($stmt_lead);
$tot_lead_row = mysqli_fetch_assoc($tot_lead_result);
$total_leads = (int)($tot_lead_row['total'] ?? 0);
mysqli_stmt_close($stmt_lead);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/eto-buyreq.css" type="text/css" rel="stylesheet">
    
    <style>
        table td { text-align: center; }
        .pagination {
            float: right;
            padding: 0px 10px;
        }
        .pagination a {
            color: #333;
            font-weight: bold;
            text-decoration: none;
            border: 1px solid #333;
            padding: 3px 9px;
            background-color: #f3f3f3;
            border-radius: 3px;
            margin: 0 2px;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination span {
            font-weight: bold;
            padding: 3px 9px;
            border: 1px solid #333;
            background-color: #4CAF50;
            color: white;
            border-radius: 3px;
            margin: 0 2px;
        }
        .tab-head {
            padding: 8px;
            font-size: 12px;
        }
        .lead {
            padding: 5px;
        }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(get_page_settings(4), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; text-align:center; color:blue; margin:0; padding:18px 5px 18px 5px; background-color:#FFFFFF;" title="أدوات المشترى">
                        أدوات المشترى
                    </h3>
                </li>
                
                <li class="np npnew">
                    <a href="post-buy-req.php" title="إرسل طلبات شراء للنشر وتلقى أقل أسعار عروض بيع بخصوصها">
                        »&nbsp;أنشر طلبات شراء
                    </a>
                </li>
                <li class="np npnew">
                    <a href="manage-buy-requirement.php" title="تحكم فى طلبات شرائك المنشورة">
                        »&nbsp;إدارة طلبات شراء
                    </a>
                </li>
                <li class="np npnew">
                    <a href="manage-selloffer-alert.php" title="أكتب أصناف - مشتريات شركتك - المعتاده - لتلقى إشعارات عروض بيع لها - فى بريدك">
                        »&nbsp;إدارة إشعارات عروض بيع
                    </a>
                </li>
                
                <li style="border-bottom:none" title="مشتريات طلبات الشراء الجاهزة">
                    <h3>مشتريات طلبات الشراء</h3>
                </li>
                <li class="np npnew">
                    <a href="subscription.php" title="Purchase Buy Leads">إشترى طلبات شراء</a>
                </li>
                <li class="np npnew">
                    <a href="buyleads.php" title="View Latest Buy Leads">شاهد أخر طلبات الشراء</a>
                </li>
                <li class="np npnew">
                    <a class="leftindi txtcol" href="manage-purchased-buyleads.php" title="View Purchased Buy Leads">شاهد طلبات الشراء المشتراه</a>
                </li>
                <li class="np npnew">
                    <a href="manage-purchased-tenders.php" title="View Purchased Tenders">شاهد المناقصات المشتراه</a>
                </li>
                <li class="np npnew">
                    <a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">إدارة إشعارات طلبات الشراء</a>
                </li>
            </ul>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr_buyreq mfl" id="req_listing">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <form style="margin:0px;" action="" name="form1">
                                <table style="border-collapse:collapse;" align="center" border="1" bordercolor="#f0f0f0" cellpadding="3" cellspacing="0" width="100%">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="19%">
                                                <b>الملخص</b>
                                            </td>
                                            <td colspan="2" class="lead" height="24" align="left" bgcolor="#FFDFDF" valign="middle" width="47%">
                                                <b>عدد إشتراكات الشراء: <?php echo $total_leads; ?></b>
                                            </td>
                                            <td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>المبالغ المدفوعة</b></td>
                                            <td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>النقاط المشتراه</b></td>
                                            <td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>النقاط المستخدمة</b></td>
                                            <td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>الرصيد</b></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="tab-head" height="24" align="center" bgcolor="#f3f3f3" valign="middle"><b>#</b></td>
                                            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>Date</b></td>
                                            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>Description</b></td>
                                            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>From</b></td>
                                            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>Amount</b></td>
                                            <td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b>Purchased</b></td>
                                            <td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b>Used</b></td>
                                            <td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b>Balance</b></td>
                                        </tr>
                                        
                                        <?php if (!empty($transactions)): 
                                            $counter = $start_limit + 1;
                                            foreach ($transactions as $bh_row):
                                                $bh_id = (int)$bh_row['bh_id'];
                                                $bh_type = (int)$bh_row['bh_type'];
                                                $bh_from = (int)$bh_row['bh_from'];
                                                $bh_updated_date = $bh_row['bh_updated_date'] ?? '';
                                                $bh_txn_id = htmlspecialchars($bh_row['bh_txn_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $bh_currency_code = htmlspecialchars($bh_row['bh_currency_code'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $bh_amount = number_format((float)($bh_row['bh_amount'] ?? 0), 2);
                                                $bh_credit_purchased = (int)($bh_row['bh_credit_purchased'] ?? 0);
                                                $bh_credit_used = (int)($bh_row['bh_credit_used'] ?? 0);
                                                $bh_user_balance = (int)($bh_row['bh_user_balance'] ?? 0);
                                                
                                                // تحديد نوع المعاملة
                                                $type_text = '';
                                                $from_text = '';
                                                $expiry_date = '';
                                                
                                                switch ($bh_type) {
                                                    case 1:
                                                        $type_text = 'Credit Purchased';
                                                        $from_text = $bh_from . ' <span style="font-weight:300">Transaction Id</span><br/>(' . $bh_txn_id . ')';
                                                        break;
                                                    case 2:
                                                        $type_text = 'Credit Used For Buy Leads';
                                                        // جلب بيانات طلب الشراء
                                                        $br_sql = "SELECT br.br_id, u.fname, u.lname 
                                                                   FROM buy_requirement br
                                                                   INNER JOIN user u ON u.usr_id = br.br_u_id
                                                                   WHERE br.br_id = ? AND br.br_approval_status = '1' AND br.br_display_status = '1'
                                                                   LIMIT 1";
                                                        $stmt_br = mysqli_prepare($con, $br_sql);
                                                        mysqli_stmt_bind_param($stmt_br, 'i', $bh_from);
                                                        mysqli_stmt_execute($stmt_br);
                                                        $br_result = mysqli_stmt_get_result($stmt_br);
                                                        if (mysqli_num_rows($br_result) > 0) {
                                                            $br_row = mysqli_fetch_assoc($br_result);
                                                            $br_id = (int)$br_row['br_id'];
                                                            $br_name = htmlspecialchars(($br_row['fname'] ?? '') . ' ' . ($br_row['lname'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                            $from_text = "<a target='_blank' href='buyleads-details.php?id=" . rand(1000, 9999) . md5((string)$br_id) . "'>" . $br_name . "</a>";
                                                        } else {
                                                            $from_text = "<font color='red'>Inactive Buy Lead</font>";
                                                        }
                                                        mysqli_stmt_close($stmt_br);
                                                        break;
                                                    case 3:
                                                        $type_text = 'Credit Used For Tender';
                                                        // جلب بيانات المناقصة
                                                        $tnd_sql = "SELECT t.tnd_id, u.fname, u.lname 
                                                                    FROM tender t
                                                                    INNER JOIN user u ON u.usr_id = t.tnd_usr_id
                                                                    WHERE t.tnd_id = ?
                                                                    LIMIT 1";
                                                        $stmt_tnd = mysqli_prepare($con, $tnd_sql);
                                                        mysqli_stmt_bind_param($stmt_tnd, 'i', $bh_from);
                                                        mysqli_stmt_execute($stmt_tnd);
                                                        $tnd_result = mysqli_stmt_get_result($stmt_tnd);
                                                        if (mysqli_num_rows($tnd_result) > 0) {
                                                            $tnd_row = mysqli_fetch_assoc($tnd_result);
                                                            $tnd_id = (int)$tnd_row['tnd_id'];
                                                            $tnd_name = htmlspecialchars(($tnd_row['fname'] ?? '') . ' ' . ($tnd_row['lname'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                            $from_text = "<a target='_blank' href='tender-details.php?id=" . rand(1000, 9999) . md5((string)$tnd_id) . "'>" . $tnd_name . "</a>";
                                                        } else {
                                                            $from_text = "<font color='red'>Inactive Tender</font>";
                                                        }
                                                        mysqli_stmt_close($stmt_tnd);
                                                        break;
                                                    case 4:
                                                        $type_text = 'Credit Used For Auction';
                                                        // جلب بيانات المزاد
                                                        $auc_sql = "SELECT a.auc_id, u.fname, u.lname 
                                                                    FROM auction a
                                                                    INNER JOIN user u ON u.usr_id = a.auc_usr_id
                                                                    WHERE a.auc_id = ?
                                                                    LIMIT 1";
                                                        $stmt_auc = mysqli_prepare($con, $auc_sql);
                                                        mysqli_stmt_bind_param($stmt_auc, 'i', $bh_from);
                                                        mysqli_stmt_execute($stmt_auc);
                                                        $auc_result = mysqli_stmt_get_result($stmt_auc);
                                                        if (mysqli_num_rows($auc_result) > 0) {
                                                            $auc_row = mysqli_fetch_assoc($auc_result);
                                                            $auc_id = (int)$auc_row['auc_id'];
                                                            $auc_name = htmlspecialchars(($auc_row['fname'] ?? '') . ' ' . ($auc_row['lname'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                            $from_text = "<a target='_blank' href='auction-details.php?id=" . rand(1000, 9999) . md5((string)$auc_id) . "'>" . $auc_name . "</a>";
                                                        } else {
                                                            $from_text = "<font color='red'>Inactive Auction</font>";
                                                        }
                                                        mysqli_stmt_close($stmt_auc);
                                                        break;
                                                    case 5:
                                                        $type_text = 'Subscription Payment';
                                                        // جلب تاريخ انتهاء الاشتراك
                                                        $sub_sql = "SELECT pm.expiry_date 
                                                                    FROM plan_member_id pm
                                                                    INNER JOIN business_profile bf ON pm.b_id = bf.bnsprof_id
                                                                    INNER JOIN user u ON u.usr_id = bf.bnsprof_uid
                                                                    WHERE u.usr_id = ?
                                                                    LIMIT 1";
                                                        $stmt_sub = mysqli_prepare($con, $sub_sql);
                                                        mysqli_stmt_bind_param($stmt_sub, 'i', $uid);
                                                        mysqli_stmt_execute($stmt_sub);
                                                        $sub_result = mysqli_stmt_get_result($stmt_sub);
                                                        $sub_row = mysqli_fetch_assoc($sub_result);
                                                        if ($sub_row && !empty($sub_row['expiry_date'])) {
                                                            $expiry_date = date("d M, Y", strtotime($sub_row['expiry_date']));
                                                        }
                                                        mysqli_stmt_close($stmt_sub);
                                                        break;
                                                }
                                                
                                                $formatted_date = !empty($bh_updated_date) ? date('d M, y', strtotime($bh_updated_date)) : 'N/A';
                                        ?>
                                        <tr>
                                            <td class="tab-head" height="24" align="center" valign="middle"><b><?php echo $counter; ?></b></td>
                                            <td class="tab-head" height="24" align="left" valign="middle"><b><?php echo $formatted_date; ?></b></td>
                                            <td class="tab-head" height="24" align="left" valign="middle"><b><?php echo $type_text; ?></b></td>
                                            <td class="tab-head" height="24" align="left" valign="middle"><b><?php echo $from_text; ?></b></td>
                                            <td class="tab-head" height="24" align="left" valign="middle"><b><?php echo $bh_currency_code . $bh_amount; ?></b></td>
                                            <td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_credit_purchased; ?></b></td>
                                            <td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_credit_used; ?></b></td>
                                            <td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_user_balance; ?></b></td>
                                        </tr>
                                        <?php 
                                            $counter++;
                                            endforeach; 
                                        endif; ?>
                                    </tbody>
                                </table>
                                <br />
                                
                                <!-- أزرار التصفح -->
                                <div class="pagination">
                                    <?php if ($pageno > 1): ?>
                                        <a href="transaction_history.php?pageno=<?php echo $pageno - 1; ?>" style="width:65px;">« Prev</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <?php if ($pageno == $i): ?>
                                            <span id="pageno"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="transaction_history.php?pageno=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pageno < $total_pages): ?>
                                        <a style="width:65px;" href="transaction_history.php?pageno=<?php echo $pageno + 1; ?>">التالية »</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <div style="clear:both"><br></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="detail_req" style="display:none;"></div>
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>