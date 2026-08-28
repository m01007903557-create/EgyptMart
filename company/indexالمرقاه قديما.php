<?php
// company/index.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// التحقق من اسم الموقع
$siteName = '';
if (isset($_GET['c']) && !empty($_GET['c'])) {
    $sitePostName = $_GET['c'];
    $siteNameCheck = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $sitePostName);
    // يمكن إضافة معالجة إضافية هنا إذا لزم الأمر
}

include "includes/header.php";
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></title>
</head>
<body>
    <div id="body">
        <ul class="cb">
            <?php 
            // جلب محتوى الصفحة الرئيسية للشركة
            $sql_wc = "SELECT * FROM website_content WHERE wc_usr_id = '" . (int)($row->usr_id ?? 0) . "'";
            $res_wc = mysqli_query($con, $sql_wc);
            $row_wc = mysqli_fetch_object($res_wc);
            ?>	
            
            <li id="wideColumn">
                <?php if (!empty($row_wc->wc_homepage_key_desc)): ?>	
                    <section class="box1" title="Company Description">
                        <div class="h2"><h2>الوصـف العــام للشركـة</h2></div>
                        <nav class="comPro">
                            <p><?php echo nl2br(htmlspecialchars($row_wc->wc_homepage_key_desc)); ?></p>
                        </nav>
                    </section>
                    <br>
                <?php endif; ?>
                
                <?php if (!empty($row_wc->wc_homepage_detail_desc)): ?>
                    <section class="box1" title="Company Information">
                        <div class="h2"><h2>معلومات أكثر عن الشركة</h2></div>
                        <nav class="comPro">
                            <p><?php echo nl2br(htmlspecialchars($row_wc->wc_homepage_detail_desc)); ?></p>
                        </nav>
                    </section>
                    <br>
                <?php endif; ?>

                <script src="js/jquery.colorbox.js"></script>
                <link href="css/colorbox.css" type="text/css" rel="stylesheet">
                
                <section class="box1" id="featuredProducts" title="Products & Services">
                    <div class="h2"><h2>منتجات وخدمات الشركة</h2></div>			
                    <nav>
                        <ul>
                            <?php
                            $sql_pd_h = "SELECT * FROM products 
                                         WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                                         AND pd_status = '1'";
                            $res_pd_h = mysqli_query($con, $sql_pd_h);
                            
                            if (mysqli_num_rows($res_pd_h) > 0):
                                $j = 0;
                                while ($row_pd_h = mysqli_fetch_object($res_pd_h)):
                            ?>
                                    <?php if (($j % 3 == 0) || ($j == 0)): ?>
                                        <li class="cb">
                                    <?php endif; ?>
                                    
                                    <div>
                                        <figure class="pr">
                                            <p>
                                                <script>
                                                $(document).ready(function() {
                                                    $("#pic_ajax<?php echo (int)($row_pd_h->pd_id ?? 0); ?>").colorbox({width: "62%", height: "89%"});
                                                });
                                                </script>
                                                
                                                <?php if (!empty($row_pd_h->pd_image)): ?>
                                                    <a href="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($row_pd_h->pd_image); ?>" 
                                                       id="pic_ajax<?php echo (int)($row_pd_h->pd_id ?? 0); ?>">
                                                        <img src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($row_pd_h->pd_image); ?>" 
                                                             title="<?php echo htmlspecialchars($row_pd_h->pd_title); ?>" 
                                                             alt="<?php echo htmlspecialchars($row_pd_h->pd_title); ?>" 
                                                             class="bdr" height="122" width="150">
                                                    </a>
                                                    </p>
                                                    <div class="zoom pa lh11em">
                                                        <a href="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($row_pd_h->pd_image); ?>" 
                                                           id="pic_ajax<?php echo (int)($row_pd_h->pd_id ?? 0); ?>">
                                                            <img src="images/icon_zoom.png" class="vab">
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <img src="https://egyptmart.shop/upload/myproduct/noimage.jpg" 
                                                         title="<?php echo htmlspecialchars($row_pd_h->pd_title); ?>" 
                                                         alt="<?php echo htmlspecialchars($row_pd_h->pd_title); ?>" 
                                                         class="bdr" height="122" width="150">
                                                    </p>
                                                <?php endif; ?>
                                            
                                            <p></p>
                                        </figure>
                                        
                                        <p>
                                            <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5((string)($row_pd_h->pd_id ?? 0)); ?>&c=<?php echo urlencode($c ?? ''); ?>">
                                                <?php echo htmlspecialchars($row_pd_h->pd_title); ?>
                                            </a>
                                        </p>
                                    </div>
                                    
                                    <?php if ((($j + 1) % 3 == 0) && ($j != 0)): ?>
                                        </li>
                                    <?php endif; ?>
                                    
                            <?php 
                                    $j++;
                                endwhile;
                                
                                if (mysqli_num_rows($res_pd_h) % 3 != 0): 
                            ?>
                                </li>
                            <?php 
                                endif;
                            endif; 
                            ?>
                        </ul>
                    </nav>
                </section>
                <br>
            </li>
            
            <?php include "includes/right.php"; ?>
        </ul>
    </div>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>