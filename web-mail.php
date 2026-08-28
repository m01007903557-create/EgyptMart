<?php
/**
 * File: testimonials_list.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض قائمة الشهادات والتوصيات
 * Display testimonials list
 * 
 * Features:
 * - عرض جميع الشهادات النشطة
 * - عرض الصورة والاسم والبلد
 * - ترتيب حسب تاريخ التحديث
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "common.php";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    
    <title><?php echo htmlspecialchars(getSiteTitle()); ?> - Testimonials</title>
    
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css/testimonial.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Tahoma, sans-serif;
            background: #f5f5f5;
            direction: rtl;
        }
        
        #wrapper {
            width: 1000px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .top-part {
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #ddd;
        }
        
        .logo-part {
            float: right;
        }
        
        .logo-part a {
            display: inline-block;
            margin-left: 10px;
        }
        
        .logo-part img {
            vertical-align: middle;
            border: none;
        }
        
        .clearer {
            clear: both;
            height: 0;
            overflow: hidden;
        }
        
        .container-blue {
            background: #0066b3;
            padding: 20px;
            min-height: 400px;
        }
        
        .hh {
            background: #004b8f;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .hh a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
        }
        
        .hh a:hover {
            text-decoration: underline;
        }
        
        .icons_m {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: url('images/home-icon.png') no-repeat;
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .hht {
            vertical-align: middle;
        }
        
        .inner_container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
        }
        
        .page-heading {
            color: #0066b3;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0066b3;
        }
        
        .t-part {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            line-height: 1.8;
            color: #333;
            font-size: 14px;
            position: relative;
        }
        
        .t-part.ft {
            border-right: 4px solid #0066b3;
        }
        
        .t-part img {
            float: right;
            margin-left: 20px;
            margin-bottom: 10px;
            border-radius: 50%;
            border: 3px solid #0066b3;
        }
        
        .t-part b.ts {
            display: block;
            margin-top: 10px;
            color: #0066b3;
            font-size: 16px;
        }
        
        .t-part b.ts a {
            color: #0066b3;
            text-decoration: none;
        }
        
        .t-part b.ts a:hover {
            text-decoration: underline;
        }
        
        .t-part::before {
            content: '"';
            font-size: 50px;
            color: #0066b3;
            opacity: 0.2;
            position: absolute;
            top: 10px;
            right: 10px;
            font-family: Georgia, serif;
        }
        
        @media (max-width: 768px) {
            #wrapper {
                width: 100%;
            }
            
            .t-part img {
                float: none;
                display: block;
                margin: 0 auto 15px;
            }
            
            .t-part {
                text-align: center;
            }
        }
        
        /* Loading state */
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        /* Empty state */
        .no-testimonials {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
            background: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <!-- Header -->
        <div class="top-part">
            <div class="logo-part">
                <a href="index.php">
                    <img src="sitelogo/<?php echo htmlspecialchars(getSiteLogo(), ENT_QUOTES, 'UTF-8'); ?>" 
                         alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" 
                         width="227" height="68" border="0" />
                </a>
                <a href="index.php">
                    <img src="images/easybuying-logo.gif" alt="Easy Buying" width="423" height="68" border="0" />
                </a>
            </div>
            <div class="clearer"></div>
        </div>
        
        <!-- Main Content -->
        <div class="container-blue">
            <div class="hh">
                <a href="index.php">
                    <span class="icons_m hpi"></span>
                    <span class="hht">الرئيسية</span>
                </a>
            </div>
            
            <div class="inner_container">
                <h1 class="page-heading">شهادات وتوصيات العملاء</h1>
                
                <?php
                // Fetch all active testimonials
                $sql_testi = "SELECT * FROM testimonials 
                             WHERE testi_status = '1' 
                             ORDER BY testi_updated_date DESC";
                $res_testi = mysqli_query($con, $sql_testi);
                
                if ($res_testi && mysqli_num_rows($res_testi) > 0):
                    $counter = 1;
                    while ($row_testi = mysqli_fetch_object($res_testi)):
                        
                        // Get image path
                        $image_path = 'upload/testimonial_img/' . $row_testi->testi_image;
                        if (!file_exists($image_path) || empty($row_testi->testi_image)) {
                            $image_path = 'upload/testimonial_img/default-avatar.jpg';
                        }
                        
                        // Get country name
                        $country_name = get_country_name((int)($row_testi->testi_cn_id ?? 0));
                        
                        // Clean and format testimonial text
                        $testimonial_text = strip_tags(stripslashes($row_testi->testi_details ?? ''));
                        
                        // Alternating background
                        $class = ($counter % 2 == 0) ? 't-part' : 't-part ft';
                ?>
                        <div class="<?php echo $class; ?>">
                            <img src="<?php echo htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8'); ?>" 
                                 width="76" height="76" 
                                 alt="<?php echo htmlspecialchars($row_testi->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                 onerror="this.src='upload/testimonial_img/default-avatar.jpg'"/>
                            
                            <?php echo htmlspecialchars($testimonial_text, ENT_QUOTES, 'UTF-8'); ?>
                            
                            <br />
                            <b class="ts">
                                <?php echo htmlspecialchars($row_testi->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?><br />
                                <?php if (!empty($country_name)): ?>
                                    <a href="buyers-from-<?php echo urlencode(str_replace(' ', '-', $country_name)); ?>.php">
                                        <?php echo htmlspecialchars($country_name, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endif; ?>
                            </b>
                            
                            <div class="clearer"></div>
                        </div>
                <?php
                        $counter++;
                    endwhile;
                else:
                ?>
                    <div class="no-testimonials">
                        <p>لا توجد شهادات متاحة حالياً</p>
                        <p>No testimonials available at the moment</p>
                    </div>
                <?php endif; ?>
                
                <div class="clearer"></div>
                <br />
            </div>
            <div class="clearer"></div>
        </div>
        
        <div class="clearer"></div>
        <br />
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript for lazy loading (optional) -->
    <script>
        // Lazy load images
        document.addEventListener('DOMContentLoaded', function() {
            var images = document.querySelectorAll('img[data-src]');
            
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(function(img) {
                imageObserver.observe(img);
            });
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>