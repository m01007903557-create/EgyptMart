<?php
/**
 * File: footer.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تذييل لوحة تحكم المشرف - إعدادات ACE وسجل حقوق الملكية
 * Admin panel footer - ACE settings and copyright footer
 * 
 * Features:
 * - إعدادات ACE (RTL، حاوية ثابتة)
 * - حقوق الملكية والتطوير
 * - زر العودة للأعلى
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    exit('Direct access not allowed');
}

// Get current year and site title
$currentYear = date('Y');
<?php
$siteTitle = htmlspecialchars(SITE_NAME . ' - لوحة التحكم', ENT_QUOTES, 'UTF-8');
?>

<!-- ACE Settings Container -->
<div class="ace-settings-container" id="ace-settings-container">
    
    <!-- Settings Button -->
    <div class="btn btn-app btn-xs btn-warning ace-settings-btn" id="ace-settings-btn">
        <i class="icon-cog bigger-150"></i>
    </div>

    <!-- Settings Box -->
    <div class="ace-settings-box" id="ace-settings-box">
        <!-- 
        Note: Original skin settings are commented out
        You can uncomment them if needed
        -->
        
        <!-- RTL Setting -->
        <div>
            <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-rtl" />
            <label class="lbl" for="ace-settings-rtl"> Right To Left (RTL)</label>
        </div>

        <!-- Container Setting -->
        <div>
            <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-add-container" />
            <label class="lbl" for="ace-settings-add-container">
                Inside <b>.container</b>
            </label>
        </div>
    </div>
</div>

<!-- Footer -->
<div id="footer">
    <!-- Copyright -->
    <div class="copyright">
        Copyright &copy; <?php echo $currentYear; ?> <?php echo $siteTitle; ?>. All Right Reserved.
    </div>
    
    <br />
    
    <!-- Developer Credit -->
    <p class="dev">
        Site Designed & Developed by &nbsp; 
        <a href="#" target="_blank" rel="noopener noreferrer">
            ARABYOS (MTA) CO,.
        </a>
    </p>
    
    <br clear="all"/>
    
    <!-- Scroll to Top Button -->
    <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse" title="Scroll to top">
        <i class="icon-double-angle-up icon-only bigger-110"></i>
    </a>
</div>

<!-- Styles for footer -->
<style>
/* Footer Styles */
#footer {
    position: relative;
    text-align: center;
    height: auto;
    min-height: 80px;
    padding: 15px 20px;
    color: #999;
    font-size: 12px;
    clear: both;
    margin: 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    border-top: 1px solid #d0d7de;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
}

#footer .copyright {
    font-size: 13px;
    font-weight: 500;
    color: #555;
    margin-bottom: 5px;
}

#footer .dev {
    font-size: 11px;
    margin: 5px 0;
    color: #666;
}

#footer .dev a {
    color: #466da0;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

#footer .dev a:hover {
    color: #2c3e50;
    text-decoration: underline;
}

/* Scroll to Top Button */
#btn-scroll-up {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: none;
    z-index: 999;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    padding: 0;
    line-height: 40px;
    text-align: center;
    background: #2c3e50;
    border: 1px solid #1a2632;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    opacity: 0.8;
    transition: all 0.3s ease;
}

#btn-scroll-up:hover {
    opacity: 1;
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

#btn-scroll-up i {
    color: #fff;
    font-size: 18px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #footer {
        padding: 20px 10px;
        font-size: 11px;
    }
    
    #btn-scroll-up {
        width: 35px;
        height: 35px;
        line-height: 35px;
        bottom: 15px;
        right: 15px;
    }
    
    #btn-scroll-up i {
        font-size: 16px;
    }
}

/* Print styles */
@media print {
    #footer, #btn-scroll-up {
        display: none;
    }
}
</style>

<!-- JavaScript for Scroll to Top Button -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    var btn = document.getElementById('btn-scroll-up');
    var win = window;
    
    // Show/hide button on scroll
    function checkScroll() {
        if (win.scrollY > 300) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    }
    
    // Smooth scroll to top
    function scrollToTop(e) {
        e.preventDefault();
        win.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    win.addEventListener('scroll', checkScroll);
    btn.addEventListener('click', scrollToTop);
    
    // Initial check
    checkScroll();
});
</script>

<!-- Note: The original commented footer is kept for reference -->
<!--
<div class="footer">
    © <?php echo $siteTitle; ?>: All Rights Reserved <?php echo $currentYear; ?>. 
    <span>Terms of Use</span>
    <br />
    <br />
    <br clear="all"/>
</div>
-->

<?php
// Note: This footer should be included after the main content
// Example: include 'admin_footer.php';
?>