<?php
/**
 * File: footer.php
 * Description: تذييل صفحة لوحة التحكم
 * Version: 2.0.0
 */

$siteTitle = htmlspecialchars(defined('SITE_NAME') ? SITE_NAME . ' - لوحة التحكم' : 'EgyptMART - Admin', ENT_QUOTES, 'UTF-8');
?>

<!-- ===== تحميل jQuery أولاً ===== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="footer" style="text-align: center;">
    <div class="footer-inner">
        <div class="footer-content">
            <span class="bigger-120">
                &copy; <?php echo date('Y'); ?> <?php echo $siteTitle; ?>
            </span>
            <br>
            <small class="text-muted">Site Designed & Developed by ARABYOS (MTA) CO.</small>
        </div>
    </div>
</div>

<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
    <i class="icon-double-angle-up icon-only bigger-110"></i>
</a>

<style>
.footer {
    text-align: center !important;
    padding: 15px 0;
    background: #fff;
    border-top: 1px solid #e5e5e5;
    margin-top: 20px;
}
.footer-content {
    text-align: center;
}
.text-muted {
    color: #999;
    font-size: 12px;
}
</style>

<script type="text/javascript">
// التأكد من تحميل jQuery قبل التنفيذ
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        // تفعيل القوائم المنسدلة
        $('.nav-list .dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            var $li = $(this).closest('li');
            
            // إغلاق القوائم الأخرى
            $('.nav-list li.open').not($li).removeClass('open').find('.submenu').slideUp(200);
            
            // فتح/إغلاق القائمة الحالية
            $li.toggleClass('open');
            $li.find('.submenu').slideToggle(200);
        });
        
        // فتح القائمة النشطة تلقائياً
        var currentFile = window.location.pathname.split('/').pop();
        $('.nav-list a').each(function() {
            var href = $(this).attr('href');
            if (href === currentFile) {
                $(this).closest('li').addClass('active');
                $(this).closest('.submenu').parent().addClass('open');
                $(this).closest('.submenu').show();
            }
        });
    });
}
</script>