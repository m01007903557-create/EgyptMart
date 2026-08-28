<?php
/**
 * getAdditionalFields.php
 * جلب الحقول الإضافية للتصنيف الفرعي
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . "/common.php";

$subcat_id = isset($_POST['subcat_id']) ? (int)$_POST['subcat_id'] : 0;

if ($subcat_id <= 0) {
    echo '';
    exit;
}

global $con;
$sql = "SELECT af_id, af_name, af_label, af_type FROM additional_field WHERE af_pc_id = ? ORDER BY af_id";
$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    echo '';
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $subcat_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$fields = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fields[] = $row;
}
mysqli_stmt_close($stmt);

if (empty($fields)) {
    echo '';
    exit;
}

// عرض الحقول الإضافية بتنسيق جميل
?>
<div class="additional-fields-section" style="background: #f9f9ff; border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 5px;">
    <h3 style="margin: 0 0 10px 0; color: #333;">خصائص إضافية للمنتج</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 15px;">
        <?php foreach ($fields as $field): ?>
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">
                <?php echo htmlspecialchars($field['af_label'], ENT_QUOTES, 'UTF-8'); ?>:
            </label>
            <?php switch ($field['af_type']):
                case 'text': ?>
                    <input type="text" name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                           class="form-control" style="width: 100%; padding: 5px;" 
                           placeholder="أدخل <?php echo htmlspecialchars($field['af_label'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php break; ?>
                    
                <?php case 'textarea': ?>
                    <textarea name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                              rows="3" class="form-control" style="width: 100%; padding: 5px;" 
                              placeholder="أدخل <?php echo htmlspecialchars($field['af_label'], ENT_QUOTES, 'UTF-8'); ?>"></textarea>
                    <?php break; ?>
                    
                <?php case 'radio': ?>
                    <div class="radio-group">
                        <label style="margin-right: 15px;"><input type="radio" name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" value="yes"> نعم</label>
                        <label><input type="radio" name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" value="no" checked> لا</label>
                    </div>
                    <?php break; ?>
                    
                <?php case 'checkbox': ?>
                    <label>
                        <input type="checkbox" name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" value="1">
                        تفعيل
                    </label>
                    <?php break; ?>
                    
                <?php case 'select': ?>
                    <select name="af_<?php echo htmlspecialchars($field['af_name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" style="width: 100%; padding: 5px;">
                        <option value="">-- اختر --</option>
                        <option value="option1">خيار 1</option>
                        <option value="option2">خيار 2</option>
                        <option value="option3">خيار 3</option>
                    </select>
                    <?php break; ?>
            <?php endswitch; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.additional-fields-section {
    background: #f9f9ff;
    border: 1px solid #ddd;
    padding: 15px;
    margin-top: 15px;
    border-radius: 5px;
}
.additional-fields-section h3 {
    margin: 0 0 10px 0;
    color: #333;
}
.form-control {
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 5px;
}
.radio-group label {
    margin-right: 15px;
}
</style>
<?php
?>