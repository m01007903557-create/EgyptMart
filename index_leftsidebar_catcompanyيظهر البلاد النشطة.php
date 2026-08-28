<?php
require_once 'lib/connect.php';

$selected_country = isset($_COOKIE['loc_id']) ? $_COOKIE['loc_id'] : '';
$current_time = time();
$pc_id = isset($_GET['pc_id']) ? (int)$_GET['pc_id'] : 0;
$rctyp = isset($_GET['rctyp']) ? $_GET['rctyp'] : '';
?>

<div id='cssmenu' style="float:left;width:220px !important;">
<style>
.ar-flags .checkbox { margin: 3px 0; padding: 2px 0; }
.ar-flags label { line-height: 1.3; margin-bottom: 0; font-weight: normal; }

.ar-flags .checkbox label {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ar-flags .checkbox input {
    margin: 0;
}
</style>

<?php if ($rctyp == 'Suppliers'): ?>
    <?php if (empty($selected_country) || $selected_country == 'global'): ?>
        <?php
        // استعلام البلاد التي لديها موردون نشطون في هذا التصنيف
        $sql = "SELECT DISTINCT c.cn_id, c.cn_name, c.cn_flag 
                FROM country c
                WHERE EXISTS (
                    SELECT 1 
                    FROM products p
                    JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                    JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                    JOIN user u ON u.usr_id = p.pd_uid
                    WHERE u.country = c.cn_id
                    AND (p.pd_subcat_id IN (SELECT pc_id FROM product_category WHERE pc_parent_id = $pc_id OR pc_id = $pc_id))
                    AND pm.expiry_date > $current_time
                    AND p.pd_status = '1'
                )
                ORDER BY c.cn_name ASC";
        
        $result = mysqli_query($con, $sql);
        if ($result && mysqli_num_rows($result) > 0):
        ?>
            <header><span class="h4">Supplier Countries</span></header>
            <section class="ar-flags">
                <form action="catcompany.php" method="post">
                    <input type="hidden" name="rctyp" value="Suppliers">
                    <input type="hidden" name="pc_id" value="<?php echo $pc_id; ?>">
                    <?php while($row = mysqli_fetch_object($result)): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="country_id[]" value="<?php echo $row->cn_id; ?>">
                                <?php if($row->cn_flag): ?>
                                    <img src="images/country_flag/<?php echo $row->cn_flag; ?>" height="20" width="20">
                                <?php endif; ?>
                                <span><?php echo $row->cn_name; ?></span>
                            </label>
                        </div>
                    <?php endwhile; ?>
                    <div class="form-group text-center" style="margin-top:10px;">
                        <input type="submit" value="Confirm" class="btn btn-sm btn-warning">
                        <a href="javascript: window.history.go(-1)" style="margin-left:5px;">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
</div>