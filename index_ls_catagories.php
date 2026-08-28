<?php
/**
 * File: includes/index_ls_catagories.php

 * Description: عرض قائمة التصنيفات الرئيسية مع خيارات التحديد (Checkbox)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

// التحقق من وجود ملف common.php
$common_path = __DIR__ . '/../common.php';
if (file_exists($common_path)) {
    require_once $common_path;
} else {
    // إذا كان الملف في مسار مختلف
    require_once __DIR__ . '/common.php';
}

global $con;

// جلب التصنيفات الرئيسية
$view_category = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = 0 ORDER BY pc_order, pc_name ASC";
$result = mysqli_query($con, $view_category);

$counter = 0;
$has_results = mysqli_num_rows($result) > 0;
?>

<div class="box">
    <header style="font-size: 18px; color: #00297c; font-weight: 700;">Categories</header>
    
    <section class="ar-flags">
        <?php if ($has_results): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $counter++;
                $pc_id = (int)$row['pc_id'];
                $pc_name = htmlspecialchars($row['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
                
                // بدء قسم الانهيار عند الوصول للتصنيف الخامس
                if ($counter == 5):
            ?>
                <div class="collapse" id="categories">
            <?php endif; ?>
            
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="search_filter" name="category_id[]" value="<?php echo $pc_id; ?>">
                    <span><?php echo $pc_name; ?></span>
                </label>
            </div>
            
            <?php endwhile; ?>
            
            <?php if ($counter > 5): ?>
                <!-- إغلاق قسم الانهيار إذا تم فتحه -->
                </div>
                
                <!-- زر عرض المزيد -->
                <div class="text-right">
                    <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#categories" 
                       aria-expanded="false" aria-controls="collapseExample">
                        + View More
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">No categories found</div>
        <?php endif; ?>
    </section>
</div>