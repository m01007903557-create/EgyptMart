<?php
/**
 * File: admin-left-con.php
 * PHP Version: 8.3
 * Description: القائمة الجانبية للوحة تحكم المشرف - نظام إدارة المحتوى بالكامل
 * 
declare(strict_types=1);

// Prevent direct access
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    exit('Direct access not allowed');
}

// Get current file name for active menu highlighting
$currentPath = $_SERVER['SCRIPT_NAME'] ?? '';
$currentFile = basename($currentPath);
$dotPos = strrpos($currentFile, '.');
$currentFileBase = $dotPos !== false ? substr($currentFile, 0, $dotPos) : $currentFile;
// التأكد من أن المتغير غير فارغ
if (empty($currentFileBase)) {
    $currentFileBase = '';
}
/**
 * Helper function to check if current page is active
 * 
 * @param string|array $pageNames Single page or array of pages to check
 * @param string $currentFile Current file base name
 * @return string ' class="active open"' if active
 */
function isActive($pageNames, string $currentFile): string {
    if (is_string($pageNames)) {
        $pageNames = [$pageNames];
    }
    
    if (in_array($currentFile, $pageNames, true)) {
        return ' class="active open"';
    }
    return '';
}

/**
 * Helper function to check if submenu item is active
 * 
 * @param string|array $pageNames Single page or array of pages to check
 * @param string $currentFile Current file base name
 * @return string ' class="active"' if active
 */
function isActiveItem($pageNames, string $currentFile): string {
    if (is_string($pageNames)) {
        $pageNames = [$pageNames];
    }
    
    if (in_array($currentFile, $pageNames, true)) {
        return ' class="active"';
    }
    return '';
}

// Define common page groups for easier maintenance
$pageGroups = [
    'manage_admin' => ['change-user', 'change-email', 'change-pass'],
    'manage_settings' => ['setting-view', 'setting-edit', 'country', 'states', 'city', 'social-view', 'social-edit', 'social-details'],
    'manage_cms' => ['cms-view', 'cms-edit'],
    'manage_support' => ['support-category-list', 'support-category-edit', 'support-category-add', 'support_change'],
    'main_category' => ['maincat-view', 'maincat-add', 'maincat-edit'],
    'category' => ['category-view', 'category-add', 'category-edit'],
    'subcategory' => ['subcat-view', 'subcat-add', 'subcat-edit'],
    'additional_fields' => ['field-add', 'field-view', 'field-option'],
    'manage_user' => ['user-list', 'user-details'],
    'company_profile' => ['company-list', 'company-details'],
    'manage_product' => ['product-view', 'product-details', 'product-edit'],
    'buy_requirement' => ['buyreq-view', 'buyreq-details', 'buyreq-edit'],
    'sell_offer' => ['selloffer-view', 'selloffer-details', 'selloffer-edit'],
    'tender' => ['tender-view', 'tender-details', 'tender-edit'],
    'auction' => ['auction-view', 'auction-details', 'auction-edit'],
    'measurements' => ['measurements', 'business_type', 'ownership_type', 'revenue_turnover', 'employee_range', 'profile_heading', 'payment_methods'],
    'business_plan' => ['splan-view', 'splan-add', 'splan-edit', 'splan_icon-view', 'splan_icon-add', 'splan-badd'],
    'membership_plan' => ['memplan-view', 'memplan-add', 'memplan-edit'],
    'payment_tracker' => ['transaction-view'],
    'company_video' => ['video-view'],
    'testimonials' => ['testi-view', 'testi-edit', 'testi-add'],
    'payment_methods_admin' => ['paymethod-view', 'paymethod-add', 'paymethod-edit'],
    'advertisements' => ['adv-view', 'adv-edit', 'adv-add'],
    'yahoo_slider' => ['yahooslider-view', 'yahooslider-edit', 'yahooslider-add'],
    'video_slider' => ['videoslider-view', 'videoslider-edit', 'videoslider-add'],
    'product_slider' => ['productslider-view', 'productslider-edit', 'productslider-add'],
    'service_slider' => ['serviceslider-view', 'serviceslider-edit', 'serviceslider-add'],
    'suppliers_logo' => ['supp-view', 'supp-edit', 'supp-add'],
    'home_advertisements' => ['advhome-view', 'advhome-edit', 'advhome-add', 'advcathome-view', 'advcathome-edit', 'advcathome-add'],
    'google_adsense' => ['adsense-view', 'adsense-edit', 'adsense-add'],
    'bad_words' => ['bad_word-view', 'bad_word-edit', 'bad_word-add'],
    'manage_messages' => ['message-view', 'message-edit'],
    'manage_contact' => ['contact-view', 'contact-details'],
    'membership_req' => ['membership-requirements-view', 'membership-requirement'],
    'newsletter' => ['newsletter-view', 'newsletter-send'],
    'connect_us' => ['connect-us']
];
?>

<div class="sidebar" id="sidebar">
    <script type="text/javascript">
        try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
    </script>
    
    <!-- Sidebar Shortcuts -->
    <div class="sidebar-shortcuts" id="sidebar-shortcuts">
        <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
            <a class="btn btn-success" href="welcome.php" title="Home">
                <i class="icon-home"></i>
            </a>
            <a class="btn btn-info" href="change-pass.php" title="Change Password">
                <i class="icon-key"></i>
            </a>
            <a class="btn btn-warning" href="memplan-view.php" title="Membership Plan">
                <i class="icon-group"></i>
            </a>
            <a class="btn btn-danger" href="setting-view.php" title="Site Settings">
                <i class="icon-cogs"></i>
            </a>
        </div>
    </div>

    <!-- Main Navigation Menu -->
    <ul class="nav nav-list">
        
        <!-- Manage Admin Section -->
        <li<?php echo isActive($pageGroups['manage_admin'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-dashboard"></i>
                <span class="menu-text"> Manage Admin </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('change-user', $currentFileBase); ?>>
                    <a href="change-user.php">Change User Name</a>
                </li>
                <li<?php echo isActiveItem('change-email', $currentFileBase); ?>>
                    <a href="change-email.php">Change Email</a>
                </li>
                <li<?php echo isActiveItem('change-pass', $currentFileBase); ?>>
                    <a href="change-pass.php">Change Password</a>
                </li>
            </ul>
        </li>

        <!-- Manage Settings Section -->
        <li<?php echo isActive($pageGroups['manage_settings'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-cog"></i>
                <span class="menu-text"> Manage Settings </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['setting-view', 'setting-edit'], $currentFileBase); ?>>
                    <a href="setting-view.php">View Settings</a>
                </li>
                <li<?php echo isActiveItem(['social-view', 'social-edit', 'social-details'], $currentFileBase); ?>>
                    <a href="social-view.php">Social Media</a>
                </li>
                <li<?php echo isActiveItem('country', $currentFileBase); ?>>
                    <a href="country.php">Add/Edit Country/Currency</a>
                </li>
                <li<?php echo isActiveItem('states', $currentFileBase); ?>>
                    <a href="states.php">Add/Edit State</a>
                </li>
                <li<?php echo isActiveItem('city', $currentFileBase); ?>>
                    <a href="city.php">Add/Edit City</a>
                </li>
            </ul>
        </li>

        <!-- Manage CMS Section -->
        <li<?php echo isActive($pageGroups['manage_cms'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-list-alt"></i>
                <span class="menu-text"> Manage CMS </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('cms-view', $currentFileBase); ?>>
                    <a href="cms-view.php">View CMS</a>
                </li>
            </ul>
        </li>

        <!-- Manage Support Section -->
        <li<?php echo isActive($pageGroups['manage_support'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-lightbulb"></i>
                <span class="menu-text"> Manage Support </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['support-category-list', 'support-category-edit', 'support-category-add'], $currentFileBase); ?>>
                    <a href="support-category-list.php">Support Category</a>
                </li>
                <li<?php echo isActiveItem('support_change', $currentFileBase); ?>>
                    <a href="support_change.php">Add/Edit Support</a>
                </li>
            </ul>
        </li>

        <!-- Main Category Section -->
        <li<?php echo isActive($pageGroups['main_category'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-list-alt"></i>
                <span class="menu-text"> Main Category </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['maincat-view', 'maincat-edit'], $currentFileBase); ?>>
                    <a href="maincat-view.php">Main-Category List</a>
                </li>
                <li<?php echo isActiveItem('maincat-add', $currentFileBase); ?>>
                    <a href="maincat-add.php">Add Main-Category</a>
                </li>
            </ul>
        </li>

        <!-- Category Section -->
        <li<?php echo isActive($pageGroups['category'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-list-alt"></i>
                <span class="menu-text"> Category </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['category-view', 'category-edit'], $currentFileBase); ?>>
                    <a href="category-view.php">View Category</a>
                </li>
                <li<?php echo isActiveItem('category-add', $currentFileBase); ?>>
                    <a href="category-add.php">Add Category</a>
                </li>
            </ul>
        </li>

        <!-- Sub Category Section -->
        <li<?php echo isActive($pageGroups['subcategory'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-list-alt"></i>
                <span class="menu-text"> Sub Category </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['subcat-view', 'subcat-edit'], $currentFileBase); ?>>
                    <a href="subcat-view.php">Subcategory List</a>
                </li>
                <li<?php echo isActiveItem('subcat-add', $currentFileBase); ?>>
                    <a href="subcat-add.php">Add Subcategory</a>
                </li>
            </ul>
        </li>

        <!-- Additional Fields Section -->
        <li<?php echo isActive($pageGroups['additional_fields'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-plus-sign"></i>
                <span class="menu-text"> Additional Fields </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('field-add', $currentFileBase); ?>>
                    <a href="field-add.php">Add Field</a>
                </li>
                <li<?php echo isActiveItem('field-view', $currentFileBase); ?>>
                    <a href="field-view.php">View Field</a>
                </li>
                <li<?php echo isActiveItem('field-option', $currentFileBase); ?>>
                    <a href="field-option.php">View Option</a>
                </li>
            </ul>
        </li>

        <!-- Manage User Section -->
        <li<?php echo isActive($pageGroups['manage_user'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-user"></i>
                <span class="menu-text"> Manage User </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['user-list', 'user-details'], $currentFileBase); ?>>
                    <a href="user-list.php">User List</a>
                </li>
            </ul>
        </li>

        <!-- Company Profile Section -->
        <li<?php echo isActive($pageGroups['company_profile'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-flag-alt"></i>
                <span class="menu-text"> Company Profile </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['company-list', 'company-details'], $currentFileBase); ?>>
                    <a href="company-list.php">Company List</a>
                </li>
            </ul>
        </li>

        <!-- Manage Product Section -->
        <li<?php echo isActive($pageGroups['manage_product'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text"> Manage Product </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['product-view', 'product-details', 'product-edit'], $currentFileBase); ?>>
                    <a href="product-view.php">View Products</a>
                </li>
            </ul>
        </li>

        <!-- Buy Requirement Section -->
        <li<?php echo isActive($pageGroups['buy_requirement'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text">Buy Requirement</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['buyreq-view', 'buyreq-details', 'buyreq-edit'], $currentFileBase); ?>>
                    <a href="buyreq-view.php">View Buy Requirement</a>
                </li>
            </ul>
        </li>

        <!-- Sell Offer Section -->
        <li<?php echo isActive($pageGroups['sell_offer'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text">Sell Offer</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['selloffer-view', 'selloffer-details', 'selloffer-edit'], $currentFileBase); ?>>
                    <a href="selloffer-view.php">View Sell Offers</a>
                </li>
            </ul>
        </li>

        <!-- Tender Section -->
        <li<?php echo isActive($pageGroups['tender'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text">Tender</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['tender-view', 'tender-details', 'tender-edit'], $currentFileBase); ?>>
                    <a href="tender-view.php">View Tenders</a>
                </li>
            </ul>
        </li>

        <!-- Auction Section -->
        <li<?php echo isActive($pageGroups['auction'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text">Auction</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['auction-view', 'auction-details', 'auction-edit'], $currentFileBase); ?>>
                    <a href="auction-view.php">View Auctions</a>
                </li>
            </ul>
        </li>

        <!-- Measurements Section -->
        <li<?php echo isActive($pageGroups['measurements'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-book"></i>
                <span class="menu-text"> Measurements </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('measurements', $currentFileBase); ?>>
                    <a href="measurements.php">Measurements Units</a>
                </li>
                <li<?php echo isActiveItem('business_type', $currentFileBase); ?>>
                    <a href="business_type.php">Business Type</a>
                </li>
                <li<?php echo isActiveItem('ownership_type', $currentFileBase); ?>>
                    <a href="ownership_type.php">Ownership Type</a>
                </li>
                <li<?php echo isActiveItem('profile_heading', $currentFileBase); ?>>
                    <a href="profile_heading.php">Profile Heading</a>
                </li>
                <li<?php echo isActiveItem('revenue_turnover', $currentFileBase); ?>>
                    <a href="revenue_turnover.php">Revenue Turnover</a>
                </li>
                <li<?php echo isActiveItem('employee_range', $currentFileBase); ?>>
                    <a href="employee_range.php">Employee Range</a>
                </li>
                <li<?php echo isActiveItem('payment_methods', $currentFileBase); ?>>
                    <a href="payment_methods.php">Payment Method</a>
                </li>
            </ul>
        </li>

        <!-- Business Plan Section -->
        <li<?php echo isActive($pageGroups['business_plan'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-unlock"></i>
                <span class="menu-text">Business Plan</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['splan-view', 'splan-edit'], $currentFileBase); ?>>
                    <a href="splan-view.php">View Plans</a>
                </li>
                <li<?php echo isActiveItem('splan-add', $currentFileBase); ?>>
                    <a href="splan-add.php">Add Special Plans</a>
                </li>
                <li<?php echo isActiveItem(['splan_icon-view', 'splan_icon-edit'], $currentFileBase); ?>>
                    <a href="splan_icon-view.php">Products Icon</a>
                </li>
                <li<?php echo isActiveItem('splan_icon-add', $currentFileBase); ?>>
                    <a href="splan_icon-add.php">Add Product Type Icon</a>
                </li>
                <li<?php echo isActiveItem('splan-badd', $currentFileBase); ?>>
                    <a href="splan-badd.php">Assign to Vendor</a>
                </li>
            </ul>
        </li>

        <!-- Membership Plan Section -->
        <li<?php echo isActive($pageGroups['membership_plan'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-unlock"></i>
                <span class="menu-text">Membership Plan</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['memplan-view', 'memplan-edit'], $currentFileBase); ?>>
                    <a href="memplan-view.php">View Plans</a>
                </li>
                <li<?php echo isActiveItem('memplan-add', $currentFileBase); ?>>
                    <a href="memplan-add.php">Add Plans</a>
                </li>
            </ul>
        </li>

        <!-- Payment Tracker Section -->
        <li<?php echo isActive($pageGroups['payment_tracker'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-credit-card"></i>
                <span class="menu-text">Payment Tracker</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('transaction-view', $currentFileBase); ?>>
                    <a href="transaction-view.php">View Transactions</a>
                </li>
            </ul>
        </li>

        <!-- Company Video Section -->
        <li<?php echo isActive($pageGroups['company_video'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-film"></i>
                <span class="menu-text">Company Video</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('video-view', $currentFileBase); ?>>
                    <a href="video-view.php">View Video</a>
                </li>
            </ul>
        </li>

        <!-- Testimonials Section -->
        <li<?php echo isActive($pageGroups['testimonials'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-comments-alt"></i>
                <span class="menu-text">Testimonials</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['testi-view', 'testi-edit'], $currentFileBase); ?>>
                    <a href="testi-view.php">View Testimonials</a>
                </li>
                <li<?php echo isActiveItem('testi-add', $currentFileBase); ?>>
                    <a href="testi-add.php">Add Testimonial</a>
                </li>
            </ul>
        </li>

        <!-- Payment Methods Section -->
        <li<?php echo isActive($pageGroups['payment_methods_admin'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-credit-card"></i>
                <span class="menu-text"> Payment Methods </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['paymethod-view', 'paymethod-edit'], $currentFileBase); ?>>
                    <a href="paymethod-view.php">View Method</a>
                </li>
            </ul>
        </li>

        <!-- Advertisements Section -->
        <li<?php echo isActive($pageGroups['advertisements'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Advertisements</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['adv-view', 'adv-edit'], $currentFileBase); ?>>
                    <a href="adv-view.php">View Advertisements</a>
                </li>
                <li<?php echo isActiveItem('adv-add', $currentFileBase); ?>>
                    <a href="adv-add.php">Add Advertisements</a>
                </li>
            </ul>
        </li>

        <!-- Yahoo Slider Section -->
        <li<?php echo isActive($pageGroups['yahoo_slider'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Yahoo Slider Mgmt</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['yahooslider-view', 'yahooslider-edit'], $currentFileBase); ?>>
                    <a href="yahooslider-view.php">View Slider</a>
                </li>
                <li<?php echo isActiveItem('yahooslider-add', $currentFileBase); ?>>
                    <a href="yahooslider-add.php">Add Slider</a>
                </li>
            </ul>
        </li>

        <!-- Video Slider Section -->
        <li<?php echo isActive($pageGroups['video_slider'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Video Slider Mgmt</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['videoslider-view', 'videoslider-edit'], $currentFileBase); ?>>
                    <a href="videoslider-view.php">View Slider</a>
                </li>
                <li<?php echo isActiveItem('videoslider-add', $currentFileBase); ?>>
                    <a href="videoslider-add.php">Add Slider</a>
                </li>
            </ul>
        </li>

        <!-- Product Slider Section -->
        <li<?php echo isActive($pageGroups['product_slider'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Product Slider Mgmt</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['productslider-view', 'productslider-edit'], $currentFileBase); ?>>
                    <a href="productslider-view.php">View Slider</a>
                </li>
                <li<?php echo isActiveItem('productslider-add', $currentFileBase); ?>>
                    <a href="productslider-add.php">Add Slider</a>
                </li>
            </ul>
        </li>

        <!-- Services Slider Section -->
        <li<?php echo isActive($pageGroups['service_slider'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Services Slider Mgmt</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['serviceslider-view', 'serviceslider-edit'], $currentFileBase); ?>>
                    <a href="serviceslider-view.php">View Slider</a>
                </li>
                <li<?php echo isActiveItem('serviceslider-add', $currentFileBase); ?>>
                    <a href="serviceslider-add.php">Add Slider</a>
                </li>
            </ul>
        </li>

        <!-- Suppliers Logo Section -->
        <li<?php echo isActive($pageGroups['suppliers_logo'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Suppliers Logo</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['supp-view', 'supp-edit'], $currentFileBase); ?>>
                    <a href="supp-view.php">View Suppliers Logo</a>
                </li>
                <li<?php echo isActiveItem('supp-add', $currentFileBase); ?>>
                    <a href="supp-add.php">Add Suppliers Logo</a>
                </li>
            </ul>
        </li>

        <!-- Home Advertisements Section -->
        <li<?php echo isActive($pageGroups['home_advertisements'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">H Advertisements</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['advhome-view', 'advhome-edit'], $currentFileBase); ?>>
                    <a href="advhome-view.php">View Advertisements</a>
                </li>
                <li<?php echo isActiveItem('advhome-add', $currentFileBase); ?>>
                    <a href="advhome-add.php">Add Advertisements</a>
                </li>
                <li<?php echo isActiveItem(['advcathome-view', 'advcathome-edit'], $currentFileBase); ?>>
                    <a href="advcathome-view.php">View Categorywise Advertisements</a>
                </li>
                <li<?php echo isActiveItem('advcathome-add', $currentFileBase); ?>>
                    <a href="advcathome-add.php">Add Categorywise Advertisements</a>
                </li>
            </ul>
        </li>

        <!-- Google Adsense Section -->
        <li<?php echo isActive($pageGroups['google_adsense'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text">Google Adsense</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['adsense-view', 'adsense-edit'], $currentFileBase); ?>>
                    <a href="adsense-view.php">View Adsense</a>
                </li>
                <li<?php echo isActiveItem('adsense-add', $currentFileBase); ?>>
                    <a href="adsense-add.php">Add Adsense</a>
                </li>
            </ul>
        </li>

        <!-- Bad Words Section -->
        <li<?php echo isActive($pageGroups['bad_words'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-ban-circle"></i>
                <span class="menu-text">Bad Words</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['bad_word-view', 'bad_word-edit'], $currentFileBase); ?>>
                    <a href="bad_word-view.php">Bad Word List</a>
                </li>
                <li<?php echo isActiveItem('bad_word-add', $currentFileBase); ?>>
                    <a href="bad_word-add.php">Add Bad Word</a>
                </li>
            </ul>
        </li>

        <!-- Manage Messages Section -->
        <li<?php echo isActive($pageGroups['manage_messages'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-envelope-alt"></i>
                <span class="menu-text">Manage Messages</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['message-view', 'message-edit'], $currentFileBase); ?>>
                    <a href="message-view.php">View Messages</a>
                </li>
            </ul>
        </li>

        <!-- Manage Contact Section -->
        <li<?php echo isActive($pageGroups['manage_contact'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-comment-alt"></i>
                <span class="menu-text"> Manage Contact </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['contact-view', 'contact-details'], $currentFileBase); ?>>
                    <a href="contact-view.php">View Contact</a>
                </li>
            </ul>
        </li>

        <!-- Membership Requirements Section -->
        <li<?php echo isActive($pageGroups['membership_req'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-unlock"></i>
                <span class="menu-text"> Membership Req</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem(['membership-requirements-view', 'membership-requirement'], $currentFileBase); ?>>
                    <a href="membership-requirements-view.php">View Membership Req</a>
                </li>
            </ul>
        </li>

        <!-- Newsletter Section -->
        <li<?php echo isActive($pageGroups['newsletter'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-comment-alt"></i>
                <span class="menu-text"> Manage Newsletter </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('newsletter-view', $currentFileBase); ?>>
                    <a href="newsletter-view.php">View Newsletter</a>
                </li>
                <li<?php echo isActiveItem('newsletter-send', $currentFileBase); ?>>
                    <a href="newsletter-send.php">Send Newsletter</a>
                </li>
            </ul>
        </li>

        <!-- Connect with Us Section -->
        <li<?php echo isActive($pageGroups['connect_us'], $currentFileBase); ?>>
            <a href="#" class="dropdown-toggle">
                <i class="icon-comment-alt"></i>
                <span class="menu-text">Connect with us</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li<?php echo isActiveItem('connect-us', $currentFileBase); ?>>
                    <a href="connect-us.php">Edit Connect us</a>
                </li>
            </ul>
        </li>

    </ul>

    <!-- Sidebar Collapse Button -->
    <div class="sidebar-collapse" id="sidebar-collapse">
        <i class="icon-double-angle-left" data-icon1="icon-double-angle-left" data-icon2="icon-double-angle-right"></i>
    </div>
    <script type="text/javascript">
jQuery(function($) {
    // تفعيل القوائم المنسدلة
    $('.nav-list').on('click', '.dropdown-toggle', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $parent = $this.closest('li');
        var $submenu = $parent.find('> .submenu');
        
        // إغلاق القوائم الأخرى
        $('.nav-list li').not($parent).removeClass('active open').find('.submenu').slideUp(200);
        
        // فتح/إغلاق القائمة الحالية
        if ($parent.hasClass('open')) {
            $parent.removeClass('active open');
            $submenu.slideUp(200);
        } else {
            $parent.addClass('active open');
            $submenu.slideDown(200);
        }
    });
    
    // تحديد الصفحة النشطة
    var currentPage = window.location.pathname.split('/').pop();
    $('.nav-list a').each(function() {
        var href = $(this).attr('href');
        if (href && href.indexOf(currentPage) !== -1) {
            $(this).closest('li').addClass('active');
            $(this).closest('.submenu').parent().addClass('active open');
            $(this).closest('.submenu').show();
        }
    });
});
</script>
</div>

<style>
/* Additional sidebar styling for better UX */
.sidebar .nav-list li.active > a {
    background: #2c3e50 !important;
    color: #fff !important;
}

.sidebar .nav-list li.active.open > a {
    background: #2c3e50 !important;
}

.sidebar .nav-list li.active .submenu li.active > a {
    background: #34495e !important;
    color: #fff !important;
    font-weight: bold;
}

.sidebar .nav-list li .submenu {
    background: #f8f9fa;
}

.sidebar .nav-list li .submenu a {
    font-size: 13px;
    padding: 8px 0 8px 42px;
}

.sidebar-shortcuts-large .btn {
    margin: 2px;
    padding: 6px 10px;
}
</style>