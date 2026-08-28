<?php
/**
 * File: ajax/sale-offer-details.php

 * Description: عرض تفاصيل عرض البيع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header('Location: sign-in.php');
    exit;
}

// التحقق من وجود معرف العرض
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: manage-saleoffer.php');
    exit;
}

$offer_id = (int)$_POST['id'];
$current_user = (int)$_SESSION['uid_indm'];

global $con;

// جلب بيانات عرض البيع مع التحقق من ملكية المستخدم
$sql = "SELECT so.*, u.*, bp.* 
        FROM sale_offer so
        INNER JOIN user u ON so.so_usr_id = u.usr_id
        INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE so.so_id = ? AND so.so_usr_id = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $offer_id, $current_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('Location: manage-saleoffer.php');
    exit;
}

// جلب بيانات الشركة (للعرض)
$sql_comp = "SELECT mobile1 FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
$stmt_comp = mysqli_prepare($con, $sql_comp);
mysqli_stmt_bind_param($stmt_comp, 'i', $current_user);
mysqli_stmt_execute($stmt_comp);
$result_comp = mysqli_stmt_get_result($stmt_comp);
$row_comp = mysqli_fetch_object($result_comp);
mysqli_stmt_close($stmt_comp);

// تنظيف البيانات للعرض
$so_service = htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8');
$so_description = htmlspecialchars(stripslashes($row->so_description ?? ''), ENT_QUOTES, 'UTF-8');
$so_pic = !empty($row->so_pic) ? htmlspecialchars($row->so_pic, ENT_QUOTES, 'UTF-8') : '';

// تحديد نص موقع التفضيل
$location_text = '';
if ($row->so_preferred_buyer_location == 'any') {
    $location_text = "Anywhere";
} elseif ($row->so_preferred_buyer_location == 'abroad') {
    $location_text = "Foreign";
} elseif ($row->so_preferred_buyer_location == 'domestic') {
    $location_text = ($row->country ?? 0) > 0 ? htmlspecialchars(get_country_name((int)$row->country), ENT_QUOTES, 'UTF-8') : '';
} elseif ($row->so_preferred_buyer_location == 'my_city' && !empty($row->bnsprof_city) && $row->bnsprof_city != '0') {
    $location_text = htmlspecialchars(get_city_name((int)$row->bnsprof_city), ENT_QUOTES, 'UTF-8');
}

// تحديد نص مدة الصلاحية
$validity_text = '';
if ($row->so_validity == '365') {
    $validity_text = "1 year";
} elseif ($row->so_validity == '90') {
    $validity_text = "3 months";
} elseif ($row->so_validity == '30') {
    $validity_text = "1 month";
}

// بيانات الشركة للعرض
$comp_name = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$comp_contact = trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? ''));
$comp_address = trim(($row->bnsprof_address1 ?? '') . ', ' . ($row->bnsprof_address2 ?? ''), ', ');
$comp_city = ($row->bnsprof_city ?? 0) > 0 ? htmlspecialchars(get_city_name((int)$row->bnsprof_city), ENT_QUOTES, 'UTF-8') : '';
$comp_state = ($row->bnsprof_state ?? 0) > 0 ? htmlspecialchars(get_state_name((int)$row->bnsprof_state), ENT_QUOTES, 'UTF-8') : '';
$comp_country = ($row->country ?? 0) > 0 ? htmlspecialchars(get_country_name((int)$row->country), ENT_QUOTES, 'UTF-8') : '';
$comp_mobile = ($row_comp->mobile1 ?? '') ? '0' . htmlspecialchars($row_comp->mobile1, ENT_QUOTES, 'UTF-8') : '';
?>

<div class="mctr mfl mpt8">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td>
                    <table class="mpr10" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td style="border-right:0px;" align="LEFT" valign="top" width="99%">
                                    <div class="mf18 mc10 mta2 mpt8 mpb10">
                                        <a class="mtd mc5">Manage your Offers</a> &gt;&gt; Offer
                                    </div>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td valign="BOTTOM" width="140">
                                                    <div class="o_detail">OFFER DETAILS</div>
                                                    <img src="images/zero_002.gif" height="6" width="150">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <table style="border-collapse:collapse" border="1" bordercolor="#CCEEFF" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td colspan="4" bgcolor="#DFF2FF" height="25">
                                                    <div class="ofdt4">
                                                        <b><font color="#800000">Offer Title:</font></b>
                                                        <font color="#800000">&nbsp; <?php echo $so_service; ?></font>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ofdt5" align="CENTER" height="25"><b>Offer Type</b></td>
                                                <td class="ofdt5" align="CENTER"><b>Original Posting Date</b></td>
                                                <td class="ofdt5" align="CENTER"><b>Updated/Refreshed Date</b></td>
                                                <td class="ofdt5" align="CENTER"><b>Expiry Date</b></td>
                                            </tr>
                                            <tr>
                                                <td class="o-testrd" align="CENTER" height="25">Sell</td>
                                                <td class="o-testrd" align="CENTER" height="25">
                                                    <?php echo date("d M Y", strtotime($row->so_posting_date)); ?>
                                                </td>
                                                <td class="o-testrd" align="CENTER" height="25">
                                                    <?php echo date("d M Y", strtotime($row->so_updated_date)); ?>
                                                </td>
                                                <td class="o-testrd" align="CENTER" height="25">
                                                    <?php echo date('d M Y', strtotime($row->so_posting_date . ' +' . $row->so_validity . ' day')); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    
                                    <table class="td-padd" style="border-collapse: collapse;" align="left" border="0" bordercolor="#cceeff" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td class="adss" style="border-top: 0px none;"><img src="images/zero.gif" height="1" width="160"></td>
                                                <td width="100%"></td>
                                            </tr>
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Description</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <?php echo $so_description; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Location Preference</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <?php echo $location_text; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
                                                <td class="ofdt" bgcolor="#F6FDFF" height="38">
                                                    <?php echo $validity_text; ?>
                                                </td>
                                            </tr>
                                            
                                            <?php if (!empty($so_pic)): ?>
                                            <tr>
                                                <td bgcolor="#F1F5FE">
                                                    <div class="ofdt1" align="right"><b>Product Photo</b></div>
                                                </td>
                                                <td bgcolor="#f6fdff">
                                                    <form style="margin:0px;">
                                                        <table style="border-collapse:collapse;" border="0" bordercolor="#F0F9FF" cellpadding="4" cellspacing="0">
                                                            <tbody>
                                                                <tr>
                                                                    <th valign="MIDDLE" width="33%">
                                                                        <div style="padding-left:18px; padding-top:5px;">
                                                                            <div style="border:1px solid #71A3C5; background:#FFFFFF; cursor:pointer;">
                                                                                <img src="upload/sale_offer/<?php echo $so_pic; ?>" 
                                                                                     id="6390059595_1" border="0" height="auto" hspace="0" vspace="0" width="125">
                                                                            </div>
                                                                            <div id="6390059595_1_H" vspace="0" hspace="0" 
                                                                                 style="display:none; position:absolute; top:0; left:0; width:0; height:0; background:#FFFFFF;" 
                                                                                 height="90"></div>
                                                                        </div>
                                                                    </th>
                                                                    <th valign="MIDDLE" width="33%"></th>
                                                                    <th valign="MIDDLE" width="33%"></th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            
                                            <tr>
                                                <td align="left"><br><div class="o_detail">COMPANY DETAILS</div></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <table style="BORDER-COLLAPSE: collapse" class="td-padd" align="center" border="0" bordercolor="#F2F2F2" cellpadding="0" cellspacing="0" width="95%">
                        <tbody>
                            <tr>
                                <td class="adss" style="border-top: 0px none;"><img src="images/zero.gif" height="1" width="160"></td>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Company Name</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30" width="100%">&nbsp;<?php echo $comp_name; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Contact Person</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $comp_contact; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Address</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $comp_address; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>City/Town</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $comp_city; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>State</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $comp_state; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Country</b>&nbsp;</td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $comp_country; ?></td>
                            </tr>
                            <tr>
                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Mobile / Cell Phone</b></td>
                                <td class="ofdt" bgcolor="#F6FDFF" height="30" width="100%">&nbsp;<?php echo $comp_mobile; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" height="20" width="100%">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2" bgcolor="#F6FDFF" width="100%" style="text-align:center">
                                    <a onClick="backToListing();" style="text-decoration:none; cursor:pointer;">Back</a>
                                    &nbsp;&nbsp;
                                    <a onClick="editSaleOffer(<?php echo $row->so_id; ?>);" style="text-decoration:none; cursor:pointer;">Edit</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div><br></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>