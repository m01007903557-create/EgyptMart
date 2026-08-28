<?php
/**
 * File: ajax/detailRequirement.php

 * Description: عرض تفاصيل طلب الشراء
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف طلب الشراء
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Invalid requirement ID");
}

$br_id = (int)$_POST['id'];

global $con;

// جلب بيانات طلب الشراء مع التحقق من ملكية المستخدم
$sql = "SELECT br.*, u.*, bp.*, mu.* 
        FROM buy_requirement br
        INNER JOIN user u ON br.br_u_id = u.usr_id
        INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        INNER JOIN measurement_unit mu ON br.br_estimate_qty_unit = mu.mu_id
        WHERE br.br_id = ? AND br.br_u_id = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $br_id, $current_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Buy requirement not found or access denied");
}

// جلب معلومات التصنيف (مسار التنقل)
$sql_pc = "SELECT m.pc_name as main_cat, c.pc_name as cat, s.pc_name as subcat 
           FROM product_category m
           INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
           INNER JOIN product_category s ON c.pc_id = s.pc_parent_id
           WHERE s.pc_id = ? 
           LIMIT 1";

$stmt_pc = mysqli_prepare($con, $sql_pc);
mysqli_stmt_bind_param($stmt_pc, 'i', $row->br_pc_id);
mysqli_stmt_execute($stmt_pc);
$result_pc = mysqli_stmt_get_result($stmt_pc);
$row_pc = mysqli_fetch_array($result_pc);
mysqli_stmt_close($stmt_pc);

// تنظيف البيانات للعرض
$br_pd_name = htmlspecialchars($row->br_pd_name ?? '', ENT_QUOTES, 'UTF-8');
$br_requirement = htmlspecialchars(stripslashes($row->br_requirement ?? ''), ENT_QUOTES, 'UTF-8');
$br_pic = !empty($row->br_pic) ? htmlspecialchars($row->br_pic, ENT_QUOTES, 'UTF-8') : 'no-image.png';
$br_estimate_qty = htmlspecialchars((string)($row->br_estimate_qty ?? ''), ENT_QUOTES, 'UTF-8');
$mu_name = htmlspecialchars($row->mu_name ?? '', ENT_QUOTES, 'UTF-8');
$br_apprx_order_value = isset($row->br_apprx_order_value) && $row->br_apprx_order_value > 0 
    ? htmlspecialchars(number_format((float)$row->br_apprx_order_value, 2), ENT_QUOTES, 'UTF-8') 
    : '';
$br_apprx_order_currency = htmlspecialchars($row->br_apprx_order_currency ?? '', ENT_QUOTES, 'UTF-8');
$br_description = htmlspecialchars($row->br_description ?? '', ENT_QUOTES, 'UTF-8');
$br_website = ($row->br_website ?? '') !== 'http://' ? htmlspecialchars($row->br_website, ENT_QUOTES, 'UTF-8') : '';
$br_need_quote_for = htmlspecialchars($row->br_need_quote_for ?? '', ENT_QUOTES, 'UTF-8');
$br_need_for = htmlspecialchars($row->br_need_for ?? '', ENT_QUOTES, 'UTF-8');
$br_requirement_frequency = htmlspecialchars($row->br_requirement_frequency ?? '', ENT_QUOTES, 'UTF-8');
$br_preferred_supplier_location = $row->br_preferred_supplier_location ?? '';
$country_id = (int)($row->country ?? 0);
$bnsprof_city = (int)($row->bnsprof_city ?? 0);
$posting_date = !empty($row->br_posting_date) ? date("d M, Y", strtotime($row->br_posting_date)) : 'N/A';
$br_approval_status = $row->br_approval_status ?? '0';

// تحديد نص موقع المورد المفضل
$location_text = '';
if ($br_preferred_supplier_location == 'any') {
    $location_text = "Anywhere";
} elseif ($br_preferred_supplier_location == 'abroad') {
    $location_text = "Foreign";
} elseif ($br_preferred_supplier_location == 'domestic') {
    $location_text = $country_id > 0 ? htmlspecialchars(get_country_name($country_id), ENT_QUOTES, 'UTF-8') : '';
} elseif ($br_preferred_supplier_location == 'my_city' && $bnsprof_city > 0) {
    $location_text = htmlspecialchars(get_city_name($bnsprof_city), ENT_QUOTES, 'UTF-8');
}
?>

<style>
.br_label {
    text-align: right;
    font-weight: bold;
    vertical-align: top;
    padding: 5px;
}
</style>

<div class="mctr_buyreq mfl">
    <div class="mf18 mc5 mta2 mpb10">
        <div class="mf11 bc mbl mbn"></div>
        <a class="mctr_manage" style="text-decoration:none;">Manage Buy Requirements</a>
        <span style="float:right; color:#929292; font-size:16px; padding-right:87px">
            <a onclick="javascript:goback()" style="font-size:12px; padding-top:4px; font-weight:bold; cursor:pointer;">&laquo; Back</a>
            <a onclick="javascript:editRequirement(<?php echo (int)$row->br_id; ?>)" style="font-size:12px; padding-top:4px; font-weight:bold; margin-left:20px; cursor:pointer;">Edit</a>
        </span>
    </div>
    
    <div id="ResBlack" class="resblack">
        <div class="to_viewres" id="responses_main" style="display:none">
            <table style="border-collapse:collapse" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td class="to_reh nlbdr" bgcolor="#2362a5" height="30" width="370"><b>Supplier's Contact Details</b></td>
                        <td rowspan="2" valign="top">
                            <div id="AllResp">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tbody>
                                        <tr>
                                            <td class="to_reh" bgcolor="#2362a5" height="30" width="115"><b>Response Date</b></td>
                                            <td class="to_reh" style="border-left:1px solid #6096cf" bgcolor="#2362a5"><b>Description</b></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <a href="javascript:closeResLayer()">
                                    <img src="../images/rescls.png" style="position:absolute; margin:-46px 0 0 469px" height="25" width="25" alt="Close">
                                </a>
                                <div style="overflow:auto; height:400px">
                                    <div id="ViewResp">
                                        <div style="font-family:arial; font-size:15px; font-weight:bold" align="center">
                                            <br><br>Loading...<br><br>
                                            <img src="../images/loading2.gif" alt="Loading">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="SendResp" style="display:none">
                                <form name="contactMailForm1" method="post">
                                    <input name="modid" value="MY" type="hidden">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td class="to_reh" bgcolor="#2362a5" height="30"><b>Please send reply using form below:</b></td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <b style="font-family:arial; font-size:14px;">
                                                        <span style="color:#ae0000">Subject:</span> Trade.<?php echo htmlspecialchars(get_page_settings(4), ENT_QUOTES, 'UTF-8'); ?>: 
                                                        <span id="OfrTitle"><?php echo $br_pd_name; ?></span>
                                                    </b><br>
                                                    <textarea name="mesg" id="mesg" rows="12" style="width:420px; margin:10px 0 10px 0" cols="40" placeholder="Please enter feedback here."></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="CENTER">
                                                    <input name="mail" value="Submit Response" onclick="return sendinfo1('contactMailForm1');" 
                                                           style="font-size:14px; font-weight:bold; font-family:arial; color:#247500; padding:5px 10px 5px 10px;" type="button">&nbsp;
                                                    <input name="button" value="Cancel" onclick="SendMailResCan();" 
                                                           style="font-size:14px; font-weight:bold; font-family:arial; color:#9a0000; padding:5px 10px 5px 10px;" type="button">
                                                    <input name="action" value="domail" type="hidden">
                                                    <input name="exp" value="0" type="hidden">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br><br>
                                </form>
                            </div>
                            <div id="h_div1"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="nlbdr" valign="top" width="370">
                            <div class="resdesc" id="SuppContactInfo"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="to_bd">Buy Requirement Details</div>
    <div class="to_ct">
        <div class="to_lp" style="min-height:53px; width:80%">
            <b style="color:#1973B9; font-size:18px; padding-left:10px;"><?php echo $br_pd_name; ?></b><br>
            
            <table>
                <tr>
                    <td class="br_label">Image:</td>
                    <td>
                        <img src="upload/buy_requirement/<?php echo $br_pic; ?>" 
                             id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125"
                             alt="Buy Requirement Image">
                    </td>
                </tr>
                
                <?php if (!empty($row_pc[0])): ?>
                <tr>
                    <td class="br_label">Main Category:</td>
                    <td><?php echo htmlspecialchars($row_pc[0] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($row_pc[1])): ?>
                <tr>
                    <td class="br_label">Category:</td>
                    <td><?php echo htmlspecialchars($row_pc[1] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($row_pc[2])): ?>
                <tr>
                    <td class="br_label">Sub-Category:</td>
                    <td><?php echo htmlspecialchars($row_pc[2] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <td class="br_label">Details:</td>
                    <td><?php echo nl2br($br_requirement); ?></td>
                </tr>
                
                <tr>
                    <td class="br_label">Estimated Quantity:</td>
                    <td><?php echo $br_estimate_qty . " " . $mu_name; ?></td>
                </tr>
                
                <?php if (!empty($br_apprx_order_value) && $br_apprx_order_value !== '0.00'): ?>
                <tr>
                    <td class="br_label">Approximate Order Value:</td>
                    <td><?php echo $br_apprx_order_currency . " " . $br_apprx_order_value; ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_description)): ?>
                <tr>
                    <td class="br_label">Product Application/Usage:</td>
                    <td><?php echo nl2br($br_description); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_website)): ?>
                <tr>
                    <td class="br_label">Website:</td>
                    <td><a href="<?php echo $br_website; ?>" target="_blank"><?php echo $br_website; ?></a></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_need_quote_for)): ?>
                <tr>
                    <td class="br_label">Need Quotations:</td>
                    <td><?php echo $br_need_quote_for; ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_preferred_supplier_location)): ?>
                <tr>
                    <td class="br_label">Preferred Supplier Location:</td>
                    <td><?php echo $location_text; ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_need_for)): ?>
                <tr>
                    <td class="br_label">Why need this:</td>
                    <td><?php echo $br_need_for; ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($br_requirement_frequency)): ?>
                <tr>
                    <td class="br_label">Requirement Frequency:</td>
                    <td><?php echo $br_requirement_frequency; ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <p class="to_rp1"><b>Posted on:</b> <?php echo $posting_date; ?><br></p>
        <div style="clear:both;"></div>
    </div>
    
    <?php if ($br_approval_status == '0'): ?>
    <br>
    <div id="NotiFicationDivSuccmsg"></div>
    <div class="NotiFicationDiv" id="NotiFicationDiv">
        <h1>Your Buy Requirement is under review by our system</h1>
        After approval, your Buy Requirement will be Live and we will send you a confirmation mail.
    </div>
    <?php endif; ?>
</div>

<?php
try {
    if ($stmt_pc instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt_pc);
    }
} catch (Error $e) {
    // تجاهل الخطأ إذا كان الكائن مغلقاً بالفعل
}
?>