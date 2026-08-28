<?php
/**
 * File: ajax/sale-offer-edit.php

 * Description: عرض نموذج تعديل عرض البيع
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
$sql = "SELECT so.*, u.*, bp.*, pc.* 
        FROM sale_offer so
        INNER JOIN user u ON so.so_usr_id = u.usr_id
        INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        INNER JOIN product_category pc ON so.so_pc_id = pc.pc_id
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

// جلب التصنيف الرئيسي
$mcat_sql = "SELECT pc_parent_id FROM product_category 
             WHERE pc_id = ? AND pc_status = '1'";
$stmt_mcat = mysqli_prepare($con, $mcat_sql);
mysqli_stmt_bind_param($stmt_mcat, 'i', $row->so_pc_id);
mysqli_stmt_execute($stmt_mcat);
$result_mcat = mysqli_stmt_get_result($stmt_mcat);
$mcat_row = mysqli_fetch_object($result_mcat);
$parent_cat_id = $mcat_row ? (int)$mcat_row->pc_parent_id : 0;
mysqli_stmt_close($stmt_mcat);

// جلب قائمة التصنيفات الرئيسية
$sql_mcat = "SELECT pc_id, pc_name FROM product_category 
             WHERE pc_parent_id = 0 AND pc_status = '1' 
             ORDER BY pc_order, pc_name ASC";
$res_mcat = mysqli_query($con, $sql_mcat);

// جلب قائمة التصنيفات الفرعية
$sql_pc = "SELECT pc_id, pc_name FROM product_category 
           WHERE pc_parent_id != 0 AND pc_parent_id = ? AND pc_status = '1' 
           ORDER BY pc_order, pc_name ASC";
$stmt_pc = mysqli_prepare($con, $sql_pc);
mysqli_stmt_bind_param($stmt_pc, 'i', $parent_cat_id);
mysqli_stmt_execute($stmt_pc);
$res_pc = mysqli_stmt_get_result($stmt_pc);

// جلب قائمة التصنيفات الفرعية الصغرى
$sql_spc = "SELECT pc_id, pc_name FROM product_category 
            WHERE pc_parent_id = ? AND pc_status = '1' AND pc_parent_id != 0 
            ORDER BY pc_order, pc_name ASC";
$stmt_spc = mysqli_prepare($con, $sql_spc);
mysqli_stmt_bind_param($stmt_spc, 'i', $row->pc_parent_id);
mysqli_stmt_execute($stmt_spc);
$res_spc = mysqli_stmt_get_result($stmt_spc);

// جلب بيانات الشركة (للعرض)
$sql_comp = "SELECT * FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
$stmt_comp = mysqli_prepare($con, $sql_comp);
mysqli_stmt_bind_param($stmt_comp, 'i', $current_user);
mysqli_stmt_execute($stmt_comp);
$result_comp = mysqli_stmt_get_result($stmt_comp);
$row_comp = mysqli_fetch_object($result_comp);
mysqli_stmt_close($stmt_comp);

// تنظيف البيانات للعرض
$so_service = htmlspecialchars(stripslashes($row->so_service ?? ''), ENT_QUOTES, 'UTF-8');
$so_description = htmlspecialchars(stripslashes($row->so_description ?? ''), ENT_QUOTES, 'UTF-8');
$so_pic = !empty($row->so_pic) ? htmlspecialchars($row->so_pic, ENT_QUOTES, 'UTF-8') : 'no-image.png';

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
<script type="text/javascript" src="../js/jquery-1.2.1.min.js"></script>
<link rel="stylesheet" href="css/colorbox.css" />
<script src="js/jquery.colorbox.js"></script>

<script type="text/javascript">
function show_photo(id) {
    $.get("ajax-file/showSaleofferImage.php", {id: id}, function(data) {
        $("#img_disp").html('<img src="' + data + '" alt="" height="100" width="125"/>');
    });
}

function showCategory() {
    var pc_id = document.getElementById('mcat_id').value;
    $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) { 
        $('#pc_id').html(data); 
        showsubcat(); 
    }); 
}

function showSubcat(id) {
    $.post("ajax-file/showSubcat.php", {id: id}, function(data) { 
        $('#so_pc_id').html(data); 
    }); 
}

function updateSaleOffer() {
    var so_id = document.getElementById('so_id');
    var pc_id = document.getElementById('pc_id');
    var so_pc_id = document.getElementById('so_pc_id');
    var so_service = document.getElementById('so_service');
    var so_description = document.getElementById('so_description');
    var so_preferred_buyer_location = $('input:radio[name=so_preferred_buyer_location]:checked').val();
    var change_validity = $('input:checkbox[name=change_validity]:checked').val();
    var so_validity = $('input:radio[name=so_validity]:checked').val();
    var valid = true;
    
    if (pc_id.value == '' || pc_id.value == '0') {
        alert("Kindly select Sale Offer category.");
        valid = false;
    } else if (so_pc_id.value == '' || so_pc_id.value == '0') {
        alert("Kindly select Sale Offer Sub-Category.");
        valid = false;
    } else if (so_service.value == '') {
        alert("Kindly enter Products / Services Name you want to sell.");
        so_service.focus();
        valid = false;
    } else if (!isNaN(so_service.value)) {
        alert("Kindly enter valid Products / Services you want to sell.");
        so_service.focus();
        valid = false;
    } else if (so_description.value == '') {
        alert("Kindly enter valid Product / Service description.");
        so_description.focus();
        valid = false;
    }
    
    if (valid) {
        $.post("ajax-file/updSaleoffer.php", {
            so_id: so_id.value,
            so_pc_id: so_pc_id.value,
            so_service: so_service.value,
            so_description: so_description.value,
            so_preferred_buyer_location: so_preferred_buyer_location,
            change_validity: change_validity,
            so_validity: so_validity
        }, function(data) {
            data = data.trim();
            var dt = data.split("|");
            if (dt[0] == '0') {
                alert(dt[1]);
            } else {
                alert('Sale Offer updated successfully.');
                viewSODetails(so_id.value);
            }
        });
    }
}

function usePhoto(id) {
    var tbl = 'sale_offer_edit';
    var so_id = document.getElementById('so_id').value;
    $.post("ajax-file/addNewImgFrmGallery.php", {id: id, so_id: so_id, tbl: tbl}, function(data) {
        $('#cboxClose').click();
        $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');
        
        setTimeout(function() {
            show_photo(so_id);
        }, 500);
    });
}

function changeValidity() {
    $("#offer_validity").toggle();
}
</script>

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
                                                <td class="ofdt5" align="CENTER" height="25"><b>Offer Types</b></td>
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
                                            
                                            <input type="hidden" id="so_id" name="so_id" value="<?php echo $row->so_id; ?>" />
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Main Category</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <select id="mcat_id" name="mcat_id" onChange="showCategory();">
                                                        <?php while ($row_mcat = mysqli_fetch_object($res_mcat)): ?>
                                                        <option value="<?php echo $row_mcat->pc_id; ?>" 
                                                            <?php echo ($row_mcat->pc_id == $parent_cat_id) ? 'selected="selected"' : ''; ?>>
                                                            <?php echo htmlspecialchars($row_mcat->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Category</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <select id="pc_id" name="pc_id" onchange="showSubcat(this.value)">
                                                        <option value="">--Select Category--</option>
                                                        <?php while ($row_pc = mysqli_fetch_object($res_pc)): ?>
                                                        <option value="<?php echo $row_pc->pc_id; ?>" 
                                                            <?php echo ($row_pc->pc_id == $parent_cat_id) ? 'selected="selected"' : ''; ?>>
                                                            <?php echo htmlspecialchars($row_pc->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                    
                                                    <select id="so_pc_id" name="so_pc_id">
                                                        <option value="">--Select Sub-Category--</option>
                                                        <?php while ($row_spc = mysqli_fetch_object($res_spc)): ?>
                                                        <option value="<?php echo $row_spc->pc_id; ?>" 
                                                            <?php echo ($row_spc->pc_id == $row->so_pc_id) ? 'selected="selected"' : ''; ?>>
                                                            <?php echo htmlspecialchars($row_spc->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Product / Service Title</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <input type="text" id="so_service" name="so_service" 
                                                           value="<?php echo $so_service; ?>" style="width:400px;" />
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Description</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <textarea id="so_description" name="so_description" style="width:400px;"><?php echo $so_description; ?></textarea>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Location Preferences</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <input type="radio" id="so_preferred_buyer_location_1" 
                                                           name="so_preferred_buyer_location" value="abroad" 
                                                           <?php echo ($row->so_preferred_buyer_location == 'abroad') ? 'checked="checked"' : ''; ?> />
                                                    <label style="top:0px;">Abroad Only</label>&nbsp;&nbsp;
                                                    
                                                    <input type="radio" id="so_preferred_buyer_location_2" 
                                                           name="so_preferred_buyer_location" value="any" 
                                                           <?php echo ($row->so_preferred_buyer_location == 'any') ? 'checked="checked"' : ''; ?> />
                                                    <label style="top:0px;">Abroad + Domestic</label>&nbsp;&nbsp;
                                                    
                                                    <input type="radio" id="so_preferred_buyer_location_3" 
                                                           name="so_preferred_buyer_location" value="domestic" 
                                                           <?php echo ($row->so_preferred_buyer_location == 'domestic') ? 'checked="checked"' : ''; ?> />
                                                    <label style="top:0px;">Domestic Only</label>&nbsp;&nbsp;
                                                    
                                                    <input type="radio" id="so_preferred_buyer_location_4" 
                                                           name="so_preferred_buyer_location" value="my_city" 
                                                           <?php echo ($row->so_preferred_buyer_location == 'my_city') ? 'checked="checked"' : ''; ?> />
                                                    <label style="top:0px;">My City Only</label>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td bgcolor="#F1F5FE">
                                                    <div class="ofdt1" align="right"><b>Product Photo</b></div>
                                                </td>
                                                <td bgcolor="#f6fdff">
                                                    <table>
                                                        <tr>
                                                            <td valign="top">
                                                                <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                                                                <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                                                                <script type="text/javascript">
                                                                    jQuery(function() {
                                                                        jQuery('#file_upload').uploadifive({
                                                                            'auto': true,
                                                                            'formData': {'id': '<?php echo $row->so_id; ?>'},
                                                                            'queueID': 'queue',
                                                                            'debug': false,
                                                                            'method': 'post',
                                                                            'uploadScript': 'ajax-file/editSOImg.php',
                                                                            'onAddQueueItem': function(file) {
                                                                                $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
                                                                            },
                                                                            'onUploadComplete': function(file, data) {
                                                                                show_photo(<?php echo $row->so_id; ?>);
                                                                            }
                                                                        });
                                                                    });
                                                                </script>
                                                                
                                                                <div style="padding-left:18px; padding-top:5px;" id="img_disp">
                                                                    <img src="upload/sale_offer/<?php echo $so_pic; ?>" 
                                                                         id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div id="drop" style="padding-left:10px;">
                                                                    <input type="file" id="file_upload" name="file_upload" />
                                                                </div>
                                                                <div id="queue"></div>
                                                            </td>
                                                            <td>
                                                                <script>
                                                                    $(document).ready(function() {
                                                                        $(".ajax").colorbox({width: "72%"});
                                                                        $(".inline").colorbox({inline: true, width: "50%"});
                                                                    });
                                                                </script>
                                                                <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">
                                                                    Select from Image Gallery
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <?php echo $validity_text; ?>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Re-List Offer</b></td>
                                                <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
                                                    <input type="checkbox" id="change_validity" name="change_validity" value="yes" onchange="changeValidity(this.value);" />
                                                </td>
                                            </tr>
                                            
                                            <tr id="offer_validity" style="display:none;">
                                                <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
                                                <td class="ofdt" bgcolor="#F6FDFF" height="38">
                                                    <input name="so_validity" id="so_validity_1" value="30" type="radio" 
                                                           <?php echo ($row->so_validity == '30') ? 'checked="checked"' : ''; ?> /> 1 Month 
                                                    <input name="so_validity" id="so_validity_2" value="90" type="radio" 
                                                           <?php echo ($row->so_validity == '90') ? 'checked="checked"' : ''; ?> /> 3 Months
                                                    <input name="so_validity" id="so_validity_3" value="365" type="radio" 
                                                           <?php echo ($row->so_validity == '365') ? 'checked="checked"' : ''; ?> /> 1 Year
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td colspan="2" style="text-align:center" bgcolor="#F6FDFF">
                                                    <input id="updSO" type="button" value="Update" onclick="updateSaleOffer();" />
                                                </td>
                                            </tr>
                                            
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
                                    <a onClick="viewSODetails('<?php echo $row->so_id; ?>');" style="text-decoration:none;cursor:pointer;">Back</a>
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

<?php
mysqli_stmt_close($stmt_pc);
mysqli_stmt_close($stmt_spc);
?>