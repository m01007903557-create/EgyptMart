<?php
/**
 * File: help.php

 * Version: PHP 8.3
 * Description: صفحة المساعدة والدعم - عرض فئات الأسئلة الشائعة
 */

include 'common.php';

// الكود الأصلي كان معلقاً - تم الاحتفاظ به كتعليق للتوثيق
/* 
$_SESSION['last_page'] = "contact_us.php";
if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '')
{
    header("Location: sign-in.php");
    exit();
}
*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">

<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/style.css" rel="stylesheet" type="text/css">

<!--include-->
<link rel="stylesheet" type="text/css" href="css/header-style.css">
<!--include end-->

<!--navigation-->
<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu1.css">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

<style type="text/css">
<!--
.style2 {font-weight: bold}
-->
</style>

<script type="text/javascript">
    function ShowCategory(id) {
        $(".allCat").hide();
        
        $.post('ajax-file/showSupportCategory.php', {id: id}, function(data) {
            $("#arrow_style_" + id).css('background', 'url(images/icons-toggle.png) no-repeat scroll 90% 18px rgb(248, 248, 248)');
            $("#category_header_" + id).attr('onClick', 'HideCategory("' + id + '")');
            $("#categoryDiv_" + id).html(data);
            $("#categoryDiv_" + id).slideDown(200);
        }).fail(function() {
            alert("حدث خطأ في تحميل الفئة");
        });
    }
    
    function HideCategory(id) {
        $("#categoryDiv_" + id).slideUp(200);
        $("#categoryDiv_" + id).html("");	
        $("#category_header_" + id).attr('onClick', 'ShowCategory("' + id + '")');		
        $("#arrow_style_" + id).css('background', 'url(images/icons-toggle.png) no-repeat scroll 90% -37px rgb(248, 248, 248)');		
    }
</script>

</head>
<body class="search-show-box">

<!-- عناصر القائمة المنبثقة - تم إزالة القيم المحددة وجعلها ديناميكية -->
<div class="ddshadow toplevelshadow"></div>

<div id="main_container">
    <div class="hm1 bbc">
        <!-- Header start Here::-->
        <?php 
        if (file_exists('includes/header_new.php')) {
            include 'includes/header_new.php';
        }
        ?>
        
        <br>
        <!-- Header End Here::-->
    </div>
    <div class="clr"></div>

    <div id="middle_container">
        <div>
            <img src="images/banner-solution.jpg" width="100%" alt="Banner" />
            <!--navigation start-->
            <span class="left-cor"></span>
            <span class="right-cor"></span>
            
            <?php 
            if (file_exists('includes/contact_head_menu.php')) {
                include 'includes/contact_head_menu.php';
            }
            ?>
        </div>
        <!--navigation close-->
        
        <div class="clr"></div>
        
        <div id="content_area">
           
            <?php 
            if (file_exists('includes/contact_left_menu.php')) {
                include 'includes/contact_left_menu.php';
            }
            ?>
            
            <div class="right-side2">
                <div class="fl shd">
                    <p class="sh1 eto-bg"></p>
                </div>

                <a name="top" id="top"></a>
                <h2>المساعـدة <span>والدعـم</span></h2>
                
                <?php 
                // الحصول على اتصال قاعدة البيانات
                global $con;
                
                // جلب فئات الأسئلة الشائعة
                $res = mysqli_query($con, "SELECT * FROM faq_categories ORDER BY fc_id");
                
                if ($res && mysqli_num_rows($res) > 0) {
                    while ($row = mysqli_fetch_object($res)) {
                        ?>
                        <div class="news-h" style="cursor: pointer;" id="category_header_<?php echo (int)$row->fc_id; ?>" onClick="ShowCategory('<?php echo (int)$row->fc_id; ?>');">
                            <img src="images/site_map_bullet.png" alt="Bullet" /> 
                            <?php echo htmlspecialchars($row->fc_name ?? ''); ?>
                        </div>
                        
                        <div style="display: none; padding-left: 40px" class="category allCat" id="categoryDiv_<?php echo (int)$row->fc_id; ?>"></div>
                        <?php
                    }
                } else {
                    echo '<p style="padding: 20px; text-align: center;">لا توجد فئات متاحة حالياً</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!--footer-->
<?php 
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}
?>
</body>
</html>