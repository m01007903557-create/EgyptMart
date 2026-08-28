<?php
/**
 * File: admin/ajax-file/city_show.php

 * Version: PHP 8.3
 * Description: تحميل وعرض قائمة المدن لبلد معين مع خيارات الإدارة
 * 
 * هذا الملف يستقبل طلب AJAX لعرض قائمة المدن التابعة لبلد محدد
 * مع إمكانية إضافة وتعديل وحذف المدن
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تعيين نوع المحتوى إلى HTML
header('Content-Type: text/html; charset=UTF-8');

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo '<div class="alert alert-danger">خطأ في الاتصال بقاعدة البيانات</div>';
    exit();
}

// التحقق من وجود معرف البلد
if (!isset($_GET['cid'])) {
    echo '<div class="alert alert-warning">معرف البلد مطلوب</div>';
    exit();
}

// تنظيف المدخلات
$cid = (int)$_GET['cid'];

// جلب قائمة المدن للبلد المحدد
$sql_cn = "SELECT * FROM city 
           WHERE ct_status = 1 
             AND ct_cn_id = " . $cid . " 
           ORDER BY ct_name";
$res_cn = mysqli_query($con, $sql_cn);

if (!$res_cn) {
    error_log("خطأ في جلب المدن: " . mysqli_error($con));
    echo '<div class="alert alert-danger">خطأ في جلب البيانات</div>';
    exit();
}
?>

<table id="sample-table-2" class="table table-striped table-bordered table-hover">
    <tr>
        <td align="center" colspan="4">
            <?php if ($cid > 0): ?>
                <table>
                    <tr id="save_link">
                        <td style="border:0px;">
                            <span>
                                <button class="btn btn-xs btn-success" onclick="ShowaddCity()" type="button">
                                    <i class="icon-plus-sign"></i><b>إضافة مدينة</b>
                                </button>
                            </span>
                        </td>
                    </tr>
                    
                    <tr id="input_add" style="display:none;">
                        <td style="border:0px;">
                            <span>
                                <input type="text" style="width:200px;" name="city_add" id="city_add" 
                                       placeholder="اسم المدينة" class="reg_txtfld form-control" value="" />
                                <select name="city_state" id="city_state" class="form-control" style="width:150px; display:inline-block;">
                                    <option value="">اختر الولاية</option>
                                    <?php 
                                    $st_res = mysqli_query($con, "SELECT * FROM states 
                                                                  WHERE state_status = '1' 
                                                                    AND state_cn_id = " . $cid . " 
                                                                  ORDER BY state_name");
                                    while ($st_row = mysqli_fetch_object($st_res)):
                                    ?>
                                        <option value="<?php echo (int)$st_row->state_id; ?>">
                                            <?php echo htmlspecialchars($st_row->state_name); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </span>
                        </td>
                        <td style="border:0px;">
                            <span>
                                <a href="javascript:addCity()" class="ajax badge badge-success" title="حفظ">
                                    <i class="icon-check"></i>
                                </a>
                            </span>
                        </td>
                        <td style="border:0px;">
                            <span>
                                <a href="javascript:CanCity()" class="badge badge-danger" title="إلغاء">
                                    <i class="icon-trash"></i>
                                </a>
                            </span>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
        </td>
    </tr>
    
    <?php 
    $j = 1;
    while ($rec_cn = mysqli_fetch_object($res_cn)):
        
        if ($j == 1 || $j == 5):
    ?>
        <tr>
    <?php endif; ?>
    
            <td>
                <table>
                    <tr>
                        <td style="width: 86%; border:0px;">
                            <!-- عرض اسم المدينة -->
                            <span id="display_<?php echo (int)$rec_cn->ct_id; ?>">
                                <?php echo htmlspecialchars($rec_cn->ct_name); ?>
                            </span>
                            
                            <!-- حقل تعديل اسم المدينة -->
                            <span id="input_<?php echo (int)$rec_cn->ct_id; ?>" style="display:none;">
                                <input type="text" style="width:150px;" name="city_<?php echo (int)$rec_cn->ct_id; ?>" 
                                       id="city_<?php echo (int)$rec_cn->ct_id; ?>" class="reg_txtfld form-control" 
                                       value="<?php echo htmlspecialchars($rec_cn->ct_name); ?>"/>
                            </span>
                            
                            <!-- حقول تعديل الولاية والمترو -->
                            <span id="input_state_<?php echo (int)$rec_cn->ct_id; ?>" style="display:none;">
                                <select name="state_<?php echo (int)$rec_cn->ct_id; ?>" 
                                        id="state_<?php echo (int)$rec_cn->ct_id; ?>" class="form-control" style="width:150px;">
                                    <option value="">اختر الولاية</option>
                                    <?php 
                                    $st_res = mysqli_query($con, "SELECT * FROM states 
                                                                  WHERE state_status = '1' 
                                                                    AND state_cn_id = " . $cid . " 
                                                                  ORDER BY state_name");
                                    while ($st_row = mysqli_fetch_object($st_res)):
                                        $selected = ($st_row->state_id == $rec_cn->ct_state) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo (int)$st_row->state_id; ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($st_row->state_name); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                
                                <select name="metro_<?php echo (int)$rec_cn->ct_id; ?>" 
                                        id="metro_<?php echo (int)$rec_cn->ct_id; ?>" class="form-control" style="width:150px;">
                                    <option value="1" <?php echo ($rec_cn->ct_metro == 1) ? 'selected' : ''; ?>>مترو</option>
                                    <option value="0" <?php echo ($rec_cn->ct_metro == 0) ? 'selected' : ''; ?>>غير مترو</option>
                                </select>
                            </span>
                        </td>
                        
                        <td style="width: 12%; border:0px;">
                            <span id="edit_<?php echo (int)$rec_cn->ct_id; ?>">
                                <a href="javascript:ShowEditCity(<?php echo (int)$rec_cn->ct_id; ?>)" 
                                   class="ajax badge badge-info" title="تعديل">
                                    <i class="icon-edit"></i>
                                </a>
                            </span>
                            <span id="save_<?php echo (int)$rec_cn->ct_id; ?>" style="display:none;">
                                <a href="javascript:EditCity(<?php echo (int)$rec_cn->ct_id; ?>)" 
                                   class="ajax badge badge-success" title="حفظ">
                                    <i class="icon-check"></i>
                                </a>
                            </span>
                        </td>
                        
                        <td style="width: 4%; border:0px;">
                            <a href="javascript:DelCity(<?php echo (int)$rec_cn->ct_id; ?>);" 
                               class="badge badge-danger" title="حذف" 
                               onclick="return confirm('هل أنت متأكد من حذف هذه المدينة؟')">
                                <i class="icon-trash"></i>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            
    <?php 
        $j++;
        if ($j == 1 || $j == 5):
    ?>
        </tr>
        <?php 
            if ($j == 5) $j = 1;
        endif;
    endwhile; 
    ?>
</table>

<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>