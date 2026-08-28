<?php
/**
 * File: ajax/editRequirement.php

 * Description: عرض وتحرير نموذج طلب الشراء
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
$sql = "SELECT br.*, pc.*, mu.* 
        FROM buy_requirement br
        INNER JOIN product_category pc ON br.br_pc_id = pc.pc_id
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

// جلب التصنيف الرئيسي
$mcat_sql = "SELECT pc_parent_id FROM product_category 
             WHERE pc_id = ? AND pc_status = '1' 
             LIMIT 1";
$stmt_mcat = mysqli_prepare($con, $mcat_sql);
mysqli_stmt_bind_param($stmt_mcat, 'i', $row->br_pc_id);
mysqli_stmt_execute($stmt_mcat);
$result_mcat = mysqli_stmt_get_result($stmt_mcat);
$mcat_row = mysqli_fetch_object($result_mcat);
$parent_cat_id = $mcat_row ? (int)$mcat_row->pc_parent_id : 0;
mysqli_stmt_close($stmt_mcat);

// جلب التصنيفات الرئيسية
$sql_mcat = "SELECT pc_id, pc_name FROM product_category 
             WHERE pc_parent_id = 0 AND pc_status = '1' 
             ORDER BY pc_order, pc_name ASC";
$res_mcat = mysqli_query($con, $sql_mcat);

// جلب التصنيفات الفرعية
$sql_pc = "SELECT pc_id, pc_name FROM product_category 
           WHERE pc_parent_id != 0 AND pc_parent_id = ? AND pc_status = '1' 
           ORDER BY pc_order, pc_name ASC";
$stmt_pc = mysqli_prepare($con, $sql_pc);
mysqli_stmt_bind_param($stmt_pc, 'i', $parent_cat_id);
mysqli_stmt_execute($stmt_pc);
$res_pc = mysqli_stmt_get_result($stmt_pc);

// جلب التصنيفات الفرعية الصغرى
$sql_spc = "SELECT pc_id, pc_name FROM product_category 
            WHERE pc_parent_id = ? AND pc_status = '1' 
            ORDER BY pc_order, pc_name ASC";
$stmt_spc = mysqli_prepare($con, $sql_spc);
mysqli_stmt_bind_param($stmt_spc, 'i', $parent_cat_id);
mysqli_stmt_execute($stmt_spc);
$res_spc = mysqli_stmt_get_result($stmt_spc);

// جلب وحدات القياس
$sql_mu = "SELECT mu_id, mu_name FROM measurement_unit WHERE mu_status = '1' ORDER BY mu_name ASC";
$res_mu = mysqli_query($con, $sql_mu);

// جلب العملات
$sql_curr = "SELECT DISTINCT cn_currency FROM country WHERE cn_status = '1' ORDER BY cn_currency ASC";
$res_curr = mysqli_query($con, $sql_curr);

// تنظيف البيانات للعرض
$br_pd_name = htmlspecialchars($row->br_pd_name ?? '', ENT_QUOTES, 'UTF-8');
$br_requirement = htmlspecialchars($row->br_requirement ?? '', ENT_QUOTES, 'UTF-8');
$br_estimate_qty = ($row->br_estimate_qty != '0.00') ? htmlspecialchars($row->br_estimate_qty, ENT_QUOTES, 'UTF-8') : '';
$br_apprx_order_value = ($row->br_apprx_order_value != '0.00') ? htmlspecialchars($row->br_apprx_order_value, ENT_QUOTES, 'UTF-8') : '';
$br_description = htmlspecialchars($row->br_description ?? '', ENT_QUOTES, 'UTF-8');
$br_website = ($row->br_website != 'http://') ? htmlspecialchars($row->br_website, ENT_QUOTES, 'UTF-8') : '';
$br_pic = !empty($row->br_pic) ? htmlspecialchars($row->br_pic, ENT_QUOTES, 'UTF-8') : 'no-image.png';
$br_approval_status = $row->br_approval_status ?? '0';
$posting_date = !empty($row->br_posting_date) ? date("d M, Y", strtotime($row->br_posting_date)) : 'N/A';
?>

<style>
.br_label {
    text-align: right;
    font-weight: bold;
    vertical-align: top;
    padding: 8px;
}
</style>

<script type="text/javascript">
function show_photo(id) {
    $.get("ajax-file/showBuyRequirementImage.php", {id: id}, function(data) {
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
        $('#br_pc_id').html(data); 
    }); 
}

function updateRequirement() {
    var br_id = document.getElementById('br_id');
    var mcat_id = document.getElementById('mcat_id');
    var pc_id = document.getElementById('pc_id');
    var br_pc_id = document.getElementById('br_pc_id');
    var br_pd_name = document.getElementById('br_pd_name');
    var br_requirement = document.getElementById('br_requirement');
    var br_estimate_qty = document.getElementById('br_estimate_qty');
    var br_estimate_qty_unit = document.getElementById('br_estimate_qty_unit');
    var br_preferred_supplier_location = $('input:radio[name=br_preferred_supplier_location]:checked').val();
    var br_apprx_order_value = document.getElementById('br_apprx_order_value');
    var br_apprx_order_currency = document.getElementById('br_apprx_order_currency');
    var br_description = document.getElementById('br_description');
    var br_website = document.getElementById('br_website');
    var br_need_quote_for = $('input:radio[name=br_need_quote_for]:checked').val();
    var br_purchase_time = $('input:radio[name=br_purchase_time]:checked').val();
    var br_need_for = $('input:radio[name=br_need_for]:checked').val();
    var br_requirement_frequency = $('input:radio[name=br_requirement_frequency]:checked').val();
    
    var valid = true;

    if (br_pd_name.value == '') {
        alert("Kindly enter Products / Services you are looking for.");
        br_pd_name.focus();
        valid = false;
    } else if (!isNaN(br_pd_name.value)) {
        alert("Kindly enter valid Products / Services you are looking for.");
        br_pd_name.focus();
        valid = false;
    } else if (mcat_id.value == '') {
        alert("Kindly select Main Category.");
        mcat_id.focus();
        valid = false;
    } else if (pc_id.value == '') {
        alert("Kindly select Category.");
        pc_id.focus();
        valid = false;
    } else if (br_pc_id.value == '' || br_pc_id.value == '0') {
        alert("Kindly select Sub-Category.");
        br_pc_id.focus();
        valid = false;
    } else if (br_requirement.value == "" || br_requirement.value == null) {
        alert("Kindly describe your Buying Requirements in detail.");
        br_requirement.focus();
        valid = false;
    } else if (br_requirement.value.length < 50) {
        alert("Your Buy Requirement description should not be less than 50 characters.");
        br_requirement.focus();
        valid = false;
    } else if (br_estimate_qty.value == '') {
        alert("Kindly enter Estimated Quantity.");
        br_estimate_qty.focus();
        valid = false;
    } else if (isNaN(br_estimate_qty.value)) {
        alert("Kindly enter valid Estimated Quantity.");
        br_estimate_qty.value = '';
        br_estimate_qty.focus();
        valid = false;
    } else if (br_estimate_qty_unit.value == '') {
        alert("Kindly select Estimated Quantity Unit.");
        br_estimate_qty_unit.focus();
        valid = false;
    } else if (br_apprx_order_value.value != '' && isNaN(br_apprx_order_value.value)) {
        alert("Kindly enter valid Approximate Order Value.");
        br_apprx_order_value.focus();
        valid = false;
    }
    
    if (valid) {
        $.post("ajax-file/updRequirement.php", {
            br_id: br_id.value,
            br_pc_id: br_pc_id.value,
            br_pd_name: br_pd_name.value,
            br_requirement: br_requirement.value,
            br_estimate_qty: br_estimate_qty.value,
            br_estimate_qty_unit: br_estimate_qty_unit.value,
            br_preferred_supplier_location: br_preferred_supplier_location,
            br_apprx_order_value: br_apprx_order_value.value,
            br_apprx_order_currency: br_apprx_order_currency.value,
            br_description: br_description.value,
            br_website: br_website.value,
            br_need_quote_for: br_need_quote_for,
            br_purchase_time: br_purchase_time,
            br_need_for: br_need_for,
            br_requirement_frequency: br_requirement_frequency
        }, function(data) {
            console.log(data);
            data = data.trim();
            var dt = data.split("|");
            if (dt[0] == '0') {
                alert(dt[1]);
            } else {
                alert(dt[1]);
                detailRequirement(br_id.value);
            }
        });
    }
}
</script>

<div class="mctr_buyreq mfl">
    <div class="mf18 mc5 mta2 mpb10">
        <div class="mf11 bc mbl mbn"></div>
        <a class="mctr_manage" style="text-decoration:none;">Manage Buy Requirements</a>
        <span style="float:right; color:#929292; font-size:16px; padding-right:87px">
            <a href="javascript:goback()" style="font-size:12px; padding-top:4px; font-weight:bold">&laquo; Back</a>
        </span>
    </div>

    <div class="to_bd">Buy Requirement Details</div>
    <div class="to_ct">
        <div class="to_lp" style="min-height:53px; width:80%;">
            <table>
                <input type="hidden" id="br_id" name="br_id" value="<?php echo (int)$row->br_id; ?>" />
                
                <tr>
                    <td class="br_label">Product/Service:</td>
                    <td><input type="text" id="br_pd_name" name="br_pd_name" value="<?php echo $br_pd_name; ?>" style="width:300px;" /></td>
                </tr>
                
                <tr>
                    <td class="br_label">Main Category:</td>
                    <td>
                        <select id="mcat_id" name="mcat_id" onChange="showCategory();">
                            <?php while ($row_mcat = mysqli_fetch_object($res_mcat)): ?>
                            <option value="<?php echo (int)$row_mcat->pc_id; ?>" 
                                <?php echo ((int)$row_mcat->pc_id == $parent_cat_id) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($row_mcat->pc_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Category:</td>
                    <td>
                        <select id="pc_id" name="pc_id" onChange="showSubcat(this.value);">
                            <?php while ($row_pc = mysqli_fetch_object($res_pc)): ?>
                            <option value="<?php echo (int)$row_pc->pc_id; ?>" 
                                <?php echo ((int)$row_pc->pc_id == $parent_cat_id) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($row_pc->pc_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Sub-Category:</td>
                    <td>
                        <select id="br_pc_id" name="br_pc_id">
                            <option value="">- Select Sub-Category -</option>
                            <?php while ($row_spc = mysqli_fetch_object($res_spc)): ?>
                            <option value="<?php echo (int)$row_spc->pc_id; ?>" 
                                <?php echo ((int)$row_spc->pc_id == (int)$row->br_pc_id) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($row_spc->pc_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Details:</td>
                    <td>
                        <textarea id="br_requirement" name="br_requirement" style="width:400px; height:100px;"><?php echo $br_requirement; ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Estimated Quantity:</td>
                    <td>
                        <input name="br_estimate_qty" id="br_estimate_qty" type="text" value="<?php echo $br_estimate_qty; ?>" style="width:100px;" />
                        <select name="br_estimate_qty_unit" id="br_estimate_qty_unit">
                            <option selected="selected" value="">--Select Unit--</option>
                            <?php while ($row_mu = mysqli_fetch_object($res_mu)): ?>
                            <option value="<?php echo (int)$row_mu->mu_id; ?>" 
                                <?php echo ((int)$row_mu->mu_id == (int)$row->br_estimate_qty_unit) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($row_mu->mu_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Location Preferences:</td>
                    <td>
                        <input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" value="abroad" <?php echo ($row->br_preferred_supplier_location == 'abroad') ? 'checked="checked"' : ''; ?> />
                        <label style="top:0px;">Abroad Only</label>&nbsp;&nbsp;
                        
                        <input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" value="any" <?php echo ($row->br_preferred_supplier_location == 'any') ? 'checked="checked"' : ''; ?> />
                        <label style="top:0px;">Abroad + Domestic</label>&nbsp;&nbsp;
                        
                        <input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" value="domestic" <?php echo ($row->br_preferred_supplier_location == 'domestic') ? 'checked="checked"' : ''; ?> />
                        <label style="top:0px;">Domestic Only</label>&nbsp;&nbsp;
                        
                        <input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" value="my_city" <?php echo ($row->br_preferred_supplier_location == 'my_city') ? 'checked="checked"' : ''; ?> />
                        <label style="top:0px;">My City Only</label>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Image:</td>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <div style="padding-left:5px; padding-top:0px;" id="img_disp">
                                        <img src="upload/buy_requirement/<?php echo $br_pic; ?>" 
                                             id="6390059595_1" border="1" height="100" hspace="0" vspace="0" width="125"
                                             alt="Buy Requirement Image">
                                    </div>
                                </td>
                                <td>
                                    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                                    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                                    <script type="text/javascript">
                                        jQuery('#file_upload').uploadifive({
                                            'auto': true,
                                            'formData': {'id': '<?php echo (int)$row->br_id; ?>'},
                                            'queueID': 'queue',
                                            'debug': false,
                                            'method': 'post',
                                            'uploadScript': 'ajax-file/editBuyRequirementImg.php',
                                            'onAddQueueItem': function(file) {
                                                $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
                                            },
                                            'onUploadComplete': function(file, data) {
                                                show_photo(<?php echo (int)$row->br_id; ?>);
                                            }
                                        });
                                    </script>
                                    <div id="drop" style="padding-left:10px;">
                                        <input type="file" id="file_upload" name="file_upload" />
                                    </div>
                                    <div id="queue"></div>
                                </td>
                                <td>
                                    <script>
                                        $(document).ready(function() {
                                            $(".inline").colorbox({inline: true, width: "50%"});
                                        });
                                    </script>
                                    <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">Select from Image Gallery</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Approximate Order Value:</td>
                    <td>
                        <input name="br_apprx_order_value" id="br_apprx_order_value" type="text" value="<?php echo $br_apprx_order_value; ?>" style="width:100px;" />
                        <select name="br_apprx_order_currency" id="br_apprx_order_currency">
                            <option selected="selected" value="">--Select Currency--</option>
                            <?php while ($row_curr = mysqli_fetch_object($res_curr)): ?>
                            <option value="<?php echo htmlspecialchars($row_curr->cn_currency ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                <?php echo ($row_curr->cn_currency == $row->br_apprx_order_currency) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($row_curr->cn_currency ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Product Application/Usage:</td>
                    <td>
                        <textarea id="br_description" name="br_description" style="width:400px;"><?php echo $br_description; ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Website:</td>
                    <td>
                        <input name="br_website" id="br_website" type="text" value="<?php echo $br_website; ?>" style="width:300px;" />
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Need Quotations:</td>
                    <td>
                        <input name="br_need_quote_for" id="br_need_quote_for0" type="radio" value="To Make Purchase" <?php echo ($row->br_need_quote_for == 'To Make Purchase') ? 'checked="checked"' : ''; ?>/> To Make Purchase 
                        <input name="br_need_quote_for" id="br_need_quote_for1" type="radio" value="To Know Price Only" <?php echo ($row->br_need_quote_for == 'To Know Price Only') ? 'checked="checked"' : ''; ?>/> To Know Price Only 
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">How soon want to purchase:</td>
                    <td>
                        <input type="radio" id="q_timperiod0" name="br_purchase_time" value="Immediate" <?php echo ($row->br_purchase_time == 'Immediate') ? 'checked="checked"' : ''; ?>/> Immediate
                        <input type="radio" id="q_timperiod1" name="br_purchase_time" value="Within 15 Days" <?php echo ($row->br_purchase_time == 'Within 15 Days') ? 'checked="checked"' : ''; ?>/> Within 15 Days
                        <input type="radio" id="q_timperiod2" name="br_purchase_time" value="Within 1 Month" <?php echo ($row->br_purchase_time == 'Within 1 Month') ? 'checked="checked"' : ''; ?>/> Within 1 Month
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Why need this:</td>
                    <td>
                        <input type="radio" id="br_need_for0" name="br_need_for" value="For Reselling" <?php echo ($row->br_need_for == 'For Reselling') ? 'checked="checked"' : ''; ?>/> For Reselling
                        <input type="radio" id="br_need_for1" name="br_need_for" value="For Your End Use" <?php echo ($row->br_need_for == 'For Your End Use') ? 'checked="checked"' : ''; ?>/> For Your End Use
                        <input type="radio" id="br_need_for2" name="br_need_for" value="As Raw Material" <?php echo ($row->br_need_for == 'As Raw Material') ? 'checked="checked"' : ''; ?>/> As Raw Material
                    </td>
                </tr>
                
                <tr>
                    <td class="br_label">Requirement Frequency:</td>
                    <td>
                        <input name="br_requirement_frequency" id="br_requirement_frequency1" type="radio" value="One Time Requirement" <?php echo ($row->br_requirement_frequency == 'One Time Requirement') ? 'checked="checked"' : ''; ?>/> One Time Requirement
                        <input name="br_requirement_frequency" id="br_requirement_frequency2" type="radio" value="Regular Requirement" <?php echo ($row->br_requirement_frequency == 'Regular Requirement') ? 'checked="checked"' : ''; ?>/> Regular Requirement
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="text-align:center; padding-top:15px;">
                        <input name="btnUpdate" id="btnUpdate" value="Update Buy Requirement" 
                               class="saps mt5" type="button" onclick="updateRequirement();" 
                               style="padding:8px 20px; cursor:pointer;" />
                    </td>
                </tr>
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
// إغلاق الـ statements
mysqli_stmt_close($stmt_pc);
mysqli_stmt_close($stmt_spc);
?>