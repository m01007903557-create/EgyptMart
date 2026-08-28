<?php
// company/include/right-panel.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// دالة الحصول على معرف التصنيف الأب
function getParentId(int $id, mysqli $con): int {
    $sql = "SELECT pc_parent_id FROM product_category WHERE pc_id = '{$id}'";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_object($res);
    return (int)($row->pc_parent_id ?? 0);
}
?>
<script>
function show_list(v) {
    if ($("#pl" + v).hasClass("pr1")) {
        $("#pl" + v).removeClass("pr1").addClass("prd");
        $("#link" + v).removeClass("on").addClass("off");
    } else {
        $("#pl" + v).removeClass("prd").addClass("pr1");
        $("#link" + v).removeClass("off").addClass("on");
    }
}
</script>

<div class="rt1">
    <p class="sp r2 r"></p>
    <p class="sp r1 fl"></p>
</div>

<div class="rt2" id="ms1">
    <div class="b c15 fn4 rpd3 sp r8">
        <a href="products.php?c=<?php echo rand(1000, 9999) . md5((string)($row->bnsprof_id ?? '')); ?>" target="_top">
            المنتجات والخدمات
        </a>
    </div>

    <?php
    // استعلام التصنيفات الرئيسية
    if (isset($_SESSION['uid_indm']) && (int)$_SESSION['uid_indm'] == (int)($row->usr_id ?? 0)) {
        $sql_pc = "SELECT * FROM product_category 
                   WHERE pc_id IN (
                       SELECT DISTINCT pc_parent_id FROM product_category 
                       WHERE pc_id IN (
                           SELECT DISTINCT pd_subcat_id FROM products 
                           WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                           AND pd_status = '1'
                       )
                   )";
    } else {
        $sql_pc = "SELECT * FROM product_category 
                   WHERE pc_id IN (
                       SELECT DISTINCT pc_parent_id FROM product_category 
                       WHERE pc_id IN (
                           SELECT DISTINCT pd_subcat_id FROM products 
                           WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                           {$sql_pd_ck} AND pd_status = '1'
                       )
                   )";
    }
    
    $res_pc = mysqli_query($con, $sql_pc);
    $i = 1;
    
    while ($row_pc = mysqli_fetch_object($res_pc)):
        $sc_param = isset($_GET['sc']) ? substr($_GET['sc'], 5) : '';
        $parent_id = !empty($sc_param) ? getParentId((int)$sc_param, $con) : 0;
        
        $pl_class = 'prd';
        $link_class = 'off';
        
        if (($parent_id == (int)($row_pc->pc_id ?? 0)) || (!isset($_GET['sc']) && $i == 1)) {
            $pl_class = 'pr1';
            $link_class = 'on';
        }
    ?>
        <p class="cl"></p>
        <div class="sp r5 b">
            <div class="nav_g b">
                <div class="<?php echo $pl_class; ?> lh2 c6 a b p9" id="pl<?php echo $i; ?>">
                    <p onclick="show_list('<?php echo $i; ?>')" class="hi1 wi1 fl"></p>
                    <p class="fl wn6 c4 r7 b">
                        <a target="_top" onclick="show_list('<?php echo $i; ?>')" style="cursor:pointer;">
                            <?php echo htmlspecialchars($row_pc->pc_name ?? ''); ?>
                        </a>
                    </p>
                    <p class="cl"></p>
                </div>
            </div>
        </div>

        <div id="link<?php echo $i; ?>" class="<?php echo $link_class; ?>">
            <?php
            // استعلام التصنيفات الفرعية
            if (isset($_SESSION['uid_indm']) && (int)$_SESSION['uid_indm'] == (int)($row->usr_id ?? 0)) {
                $sql_pc_sub = "SELECT * FROM product_category 
                               WHERE pc_id IN (
                                   SELECT DISTINCT pd_subcat_id FROM products 
                                   WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                                   AND pd_status = '1'
                               ) 
                               AND pc_parent_id = '" . (int)($row_pc->pc_id ?? 0) . "'";
            } else {
                $sql_pc_sub = "SELECT * FROM product_category 
                               WHERE pc_id IN (
                                   SELECT DISTINCT pd_subcat_id FROM products 
                                   WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                                   {$sql_pd_ck} AND pd_status = '1'
                               ) 
                               AND pc_parent_id = '" . (int)($row_pc->pc_id ?? 0) . "'";
            }
            
            $res_pc_sub = mysqli_query($con, $sql_pc_sub);
            
            while ($row_pc_sub = mysqli_fetch_object($res_pc_sub)):
            ?>
                <ul>
                    <li>
                        <a href="products.php?c=<?php echo rand(1000, 9999) . md5((string)($row->bnsprof_id ?? '')); ?>&sc=<?php echo rand(10000, 99999) . (int)($row_pc_sub->pc_id ?? 0); ?>">
                            <?php echo htmlspecialchars($row_pc_sub->pc_name ?? ''); ?>
                        </a>
                    </li>
                </ul>
            <?php endwhile; ?>
        </div>
    <?php 
        $i++;
    endwhile; 
    ?>
    
    <p class="cl"></p>
</div>

<div class="rt3 sp">
    <p class="sp r3 fl"></p>
    <p class="sp r4 r"></p>
</div>