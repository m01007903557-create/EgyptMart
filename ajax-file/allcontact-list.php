<?php
/**
 * File: ajax/allcontact-list.php

 * Description: تحميل وعرض جهات اتصال الشركة مع خيارات التعديل والحذف
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

$uid = (int)$_SESSION['uid_indm'];

global $con;

// استعلام جلب جهات الاتصال
$sql = "SELECT * FROM company_contact WHERE comp_cnt_user = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// الألقاب المسموح بها
$prefixes = ["Mr.", "Ms.", "Mrs.", "Dr."];
?>

<div id="sort_contact">
    <?php while ($row = mysqli_fetch_object($result)): 
        $contact_id = (int)$row->comp_cnt_id;
        $division_id = (int)($row->comp_cnt_division ?? 0);
        
        // جلب اسم القسم
        $division_name = '';
        if ($division_id > 0) {
            $div_sql = "SELECT dvtn_title FROM division WHERE dvtn_id = ? LIMIT 1";
            $div_stmt = mysqli_prepare($con, $div_sql);
            mysqli_stmt_bind_param($div_stmt, 'i', $division_id);
            mysqli_stmt_execute($div_stmt);
            $div_result = mysqli_stmt_get_result($div_stmt);
            $div_row = mysqli_fetch_object($div_result);
            $division_name = $div_row ? htmlspecialchars($div_row->dvtn_title ?? '', ENT_QUOTES, 'UTF-8') : '';
            mysqli_stmt_close($div_stmt);
        }
        
        // جلب قائمة الأقسام للنموذج
        $divisions = [];
        $div_list_sql = "SELECT dvtn_id, dvtn_title FROM division WHERE dvtn_status = '1' ORDER BY dvtn_title ASC";
        $div_list_result = mysqli_query($con, $div_list_sql);
        while ($div_row = mysqli_fetch_assoc($div_list_result)) {
            $divisions[] = $div_row;
        }
        
        // تنظيف البيانات للعرض
        $prefix = htmlspecialchars($row->comp_cnt_prefix ?? '', ENT_QUOTES, 'UTF-8');
        $fname = htmlspecialchars($row->comp_cnt_fname ?? '', ENT_QUOTES, 'UTF-8');
        $lname = htmlspecialchars($row->comp_cnt_lname ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($row->comp_cnt_address ?? '', ENT_QUOTES, 'UTF-8');
        $address1 = htmlspecialchars($row->comp_cnt_address1 ?? '', ENT_QUOTES, 'UTF-8');
        $country_id = (int)($row->comp_cnt_country ?? 0);
        $country_name = htmlspecialchars(get_country_name($country_id), ENT_QUOTES, 'UTF-8');
        $phone_code = htmlspecialchars($row->comp_cnt_phcntode ?? '', ENT_QUOTES, 'UTF-8');
        $area_code = htmlspecialchars($row->comp_cnt_phareacode ?? '', ENT_QUOTES, 'UTF-8');
        $telephone = htmlspecialchars($row->comp_cnt_telephone ?? '', ENT_QUOTES, 'UTF-8');
        $mobile = htmlspecialchars($row->comp_cnt_mobile ?? '', ENT_QUOTES, 'UTF-8');
        $fax_code = htmlspecialchars($row->comp_cnt_faxareacode ?? '', ENT_QUOTES, 'UTF-8');
        $fax = htmlspecialchars($row->comp_cnt_fax ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($row->comp_cnt_email ?? '', ENT_QUOTES, 'UTF-8');
        
        // تكوين أرقام الهاتف
        $telephone_display = !empty($telephone) ? $phone_code . '-' . $area_code . '-' . $telephone : '';
        $mobile_display = !empty($mobile) ? $phone_code . '-' . $mobile : '';
        $fax_display = !empty($fax) ? $phone_code . '-' . $fax_code . '-' . $fax : '';
    ?>
    
    <div class="mp1 tl bx" id="cd_<?php echo $contact_id; ?>">
        <div class="mp10">
            <div class="mz">
                <div class="f1 ac1">
                    <h2 id="divsion_<?php echo $contact_id; ?>"><?php echo $division_name; ?></h2>
                </div>
                
                <div class="fr" id="sedsvbt<?php echo $contact_id; ?>" style="display: none; width: 30%;">
                    <a class="sl f2 bnr c close mt" onclick="cnctdiscard(<?php echo $contact_id; ?>);" style="cursor:pointer;">Discard</a>
                    <a class="sl f2 bnr c sav mt" onclick="editContact(<?php echo $contact_id; ?>); " style="cursor:pointer;">Save</a>
                </div>
                
                <a onclick="delete_contact(<?php echo $contact_id; ?>);" class="sl f2 del mt c bnr" id="sdel<?php echo $contact_id; ?>" style="display: block; cursor:pointer;">Delete</a>
                <a onclick="stedit(<?php echo $contact_id; ?>);" class="sl f2 edit mt c bnr" id="seditbt<?php echo $contact_id; ?>" style="display: block; cursor:pointer;">Edit</a>
                
                <div class="clb"></div>
            </div>
            
            <!-- عرض بيانات جهة الاتصال -->
            <div style="display: block;" class="mp10 abc" id="cnctlist<?php echo $contact_id; ?>">
                <div class="mp8">Contact Person</div>
                <div class="mp7">
                    <strong>
                        <label id="lbl_salute_<?php echo $contact_id; ?>"><?php echo $prefix; ?></label>
                        <label id="lbl_first_name_<?php echo $contact_id; ?>"><?php echo $fname; ?></label>
                        <label id="lbl_last_name_<?php echo $contact_id; ?>"><?php echo $lname; ?></label>
                    </strong>
                </div>
                
                <div class="mp8">Address</div>
                <div class="mp7">
                    <label id="lbl_contact_address_<?php echo $contact_id; ?>">
                        <?php echo $address; ?><br><?php echo $address1; ?>
                    </label>
                </div>
                
                <div class="mp8">Country</div>
                <div class="mp7">
                    <label id="lbl_country_name_<?php echo $contact_id; ?>"><?php echo $country_name; ?></label>
                </div>
                
                <div class="mp8">Telephone</div>
                <div class="mp7">
                    <label id="lbl_phone_<?php echo $contact_id; ?>"><?php echo $telephone_display; ?></label>
                </div>
                
                <div class="mp8">Mobile/Cell Phone</div>
                <div class="mp7">
                    <label id="lbl_mobile_<?php echo $contact_id; ?>"><?php echo $mobile_display; ?></label>
                </div>
                
                <div class="mp8">Fax</div>
                <div class="mp7">
                    <label id="lbl_fax_<?php echo $contact_id; ?>"><?php echo $fax_display; ?></label>
                </div>
                
                <div class="mp8">Email</div>
                <div class="mp7">
                    <label id="lbl_email_<?php echo $contact_id; ?>"><?php echo $email; ?></label>
                </div>
            </div>
            
            <!-- نموذج تعديل جهة الاتصال -->
            <div class="mp10 ct1 hideup" style="display:none;" id="cnctedit<?php echo $contact_id; ?>">
                <form name="frm_<?php echo $contact_id; ?>" method="post">
                    <div>
                        <table align="left" border="0" cellpadding="4" cellspacing="0" width="490">
                            <tbody>
                                <tr>
                                    <td class="label" width="160">Division</td>
                                    <td>
                                        <select name="comp_cnt_division1<?php echo $contact_id; ?>" id="comp_cnt_division1<?php echo $contact_id; ?>" class="a_f" tabindex="1">
                                            <option value="">Select a Division</option>
                                            <?php foreach ($divisions as $div): 
                                                $selected = ((int)$div['dvtn_id'] === $division_id) ? 'selected="selected"' : '';
                                            ?>
                                            <option value="<?php echo (int)$div['dvtn_id']; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($div['dvtn_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">Contact Person</td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td width="53">
                                                        <select name="comp_cnt_prefix1<?php echo $contact_id; ?>" id="comp_cnt_prefix1<?php echo $contact_id; ?>" class="s_s a_f" style="width: 59px;" tabindex="2">
                                                            <?php foreach ($prefixes as $p): 
                                                                $selected = ($p === $prefix) ? 'selected="selected"' : '';
                                                            ?>
                                                            <option value="<?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
                                                                <?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td width="125">
                                                        <input maxlength="20" name="comp_cnt_fname1<?php echo $contact_id; ?>" id="comp_cnt_fname1<?php echo $contact_id; ?>" tabindex="3" class="a_f f_n_wid ml8" value="<?php echo $fname; ?>">
                                                    </td>
                                                    <td width="125">
                                                        <input maxlength="20" name="comp_cnt_lname1<?php echo $contact_id; ?>" id="comp_cnt_lname1<?php echo $contact_id; ?>" tabindex="4" class="a_f f_n_wid ml8" value="<?php echo $lname; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">Address</td>
                                    <td>
                                        <input maxlength="190" name="comp_cnt_address1<?php echo $contact_id; ?>" id="comp_cnt_address1<?php echo $contact_id; ?>" class="a_f rf" tabindex="5" type="text" value="<?php echo $address; ?>">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">&nbsp;</td>
                                    <td>
                                        <input maxlength="200" name="comp_cnt_address2<?php echo $contact_id; ?>" id="comp_cnt_address2<?php echo $contact_id; ?>" class="a_f rf" tabindex="6" type="text" value="<?php echo $address1; ?>">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160"><span>*</span>&nbsp;Country</td>
                                    <td>
                                        <input name="comp_cnt_country1" readonly="readonly" id="comp_cnt_country1" class="a_f rf" tabindex="7" maxlength="100" type="text" value="<?php echo $country_name; ?>">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">&nbsp;Telephone</td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td width="50">
                                                        <input maxlength="6" name="comp_cnt_phcntode1" readonly="readonly" class="ron c_c" value="<?php echo $phone_code; ?>" id="comp_cnt_phcntode" tabindex="8">
                                                    </td>
                                                    <td width="60">
                                                        <input class="a_f ml8 a_c" maxlength="6" name="comp_cnt_phareacode1<?php echo $contact_id; ?>" id="comp_cnt_phareacode1<?php echo $contact_id; ?>" tabindex="9" value="<?php echo $area_code; ?>">
                                                    </td>
                                                    <td>
                                                        <input maxlength="35" name="comp_cnt_telephone1<?php echo $contact_id; ?>" id="comp_cnt_telephone1<?php echo $contact_id; ?>" class="a_f ml8 ph_n" tabindex="10" type="text" value="<?php echo $telephone; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">Mobile/Cell Phone</td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td width="50">
                                                        <input maxlength="6" name="comp_cnt_phcntode1" readonly="readonly" value="<?php echo $phone_code; ?>" id="comp_cnt_phcntode1" class="ron c_c" tabindex="11">
                                                    </td>
                                                    <td>
                                                        <input maxlength="40" name="comp_cnt_mobile1<?php echo $contact_id; ?>" id="comp_cnt_mobile1<?php echo $contact_id; ?>" class="a_f ml8 mo_n" tabindex="12" type="text" value="<?php echo $mobile; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">Fax</td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td width="50">
                                                        <input maxlength="6" readonly="readonly" name="comp_cnt_phcntode1<?php echo $contact_id; ?>" value="<?php echo $phone_code; ?>" id="comp_cnt_phcntode1<?php echo $contact_id; ?>" class="ron c_c" tabindex="13">
                                                    </td>
                                                    <td width="60">
                                                        <input class="a_f ml8 a_c" name="comp_cnt_faxareacode1<?php echo $contact_id; ?>" maxlength="6" id="comp_cnt_faxareacode1<?php echo $contact_id; ?>" tabindex="14" value="<?php echo $fax_code; ?>">
                                                    </td>
                                                    <td>
                                                        <input maxlength="35" name="comp_cnt_fax1<?php echo $contact_id; ?>" id="comp_cnt_fax1<?php echo $contact_id; ?>" class="a_f ml8 ph_n" tabindex="15" type="text" value="<?php echo $fax; ?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" width="160">E-mail</td>
                                    <td>
                                        <input name="comp_cnt_email1<?php echo $contact_id; ?>" id="comp_cnt_email1<?php echo $contact_id; ?>" class="a_f rf" maxlength="200" tabindex="16" type="text" value="<?php echo $email; ?>">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td width="160">&nbsp;</td>
                                    <td align="left">
                                        <input name="save" id="save" class="saps" value="Save" onclick="editContact(<?php echo $contact_id; ?>);" tabindex="17" type="button">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
        <div class="clb"></div>
        <div class="clb">&nbsp;</div>
    </div>
    <?php endwhile; ?>
</div>

<?php
mysqli_stmt_close($stmt);
?>