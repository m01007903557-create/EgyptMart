<?php
/**
 * File: footer.php
 * Description: تذييل صفحة لوحة التحكم
 * Version: 2.0.0
 */

// السماح بالتضمين من أي ملف في مجلد admin
$siteTitle = htmlspecialchars(defined('SITE_NAME') ? SITE_NAME . ' - لوحة التحكم' : 'EgyptMART - Admin', ENT_QUOTES, 'UTF-8');
?>
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
<!-- DataTables JS -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/dataTables.bootstrap.min.js"></script>

<!-- ✅ المسار الصحيح من admin/includes/ إلى js/ في الجذر -->
<script src="../../js/whatsapp_handler.js"></script>

</body>
</html>