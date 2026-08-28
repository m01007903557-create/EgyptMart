<?php
$path=$_SERVER['SCRIPT_NAME'];
$pos=strrpos($path,'/');
$file=substr($path,($pos+1));
//$file = strstr($file, '.', true);
$dotpos=strrpos($file,'.');
$file=substr($file,0,($dotpos));
?>
<div class="sidebar" id="sidebar">
	<script type="text/javascript">
		try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
	</script>
	<div class="sidebar-shortcuts" id="sidebar-shortcuts">
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<a class="btn btn-success" href="welcome.php" title="Home"><i class="icon-home"></i></a>
			<a class="btn btn-info" href="change-pass.php" title="Change Password"><i class="icon-key"></i></a>
			<a class="btn btn-warning" href="memplan-view.php" title="Membership Plan"><i class="icon-group"></i></a>
			<a class="btn btn-danger" href="setting-view.php" title="Site Settings"><i class="icon-cogs"></i></a>
		</div>

		<!--<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
			<span class="btn btn-success"></span>
			<span class="btn btn-info"></span>
			<span class="btn btn-warning"></span>
			<span class="btn btn-danger"></span>
		</div>-->
	</div>
	<ul class="nav nav-list">
		<li <?php if($file=="change-user" || $file=="change-email" || $file=="change-pass"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-dashboard"></i>
				<span class="menu-text"> Manage Admin </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="change-user"){ ?> class="active" <?php } ?>><a href="change-user.php">Change User Name</a></li>
				<li <?php if($file=="change-email"){ ?> class="active" <?php } ?>><a href="change-email.php">Change Email</a></li>
				<li <?php if($file=="change-pass"){ ?> class="active" <?php } ?>><a href="change-pass.php">Change Password</a></li>
			</ul>
		</li>
        <li <?php if($file=="setting-view" || $file=="setting-edit" || $file=="country" || $file=="states" || $file=="city" || $file=="social-view" || $file=="social-edit" || $file=="social-details"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-cog"></i>
				<span class="menu-text"> Manage Settings </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="setting-view" || $file=="setting-edit"){ ?> class="active" <?php } ?>><a href="setting-view.php">View Settings</a></li>
                <li <?php if($file=="social-view" || $file=="social-edit" || $file=="social-details"){ ?> class="active" <?php } ?>><a href="social-view.php">Social Media</a></li>
				<li <?php if($file=="country"){ ?> class="active" <?php } ?>><a href="country.php">Add/Edit Country/Curency</a></li>
				<li <?php if($file=="states"){ ?> class="active" <?php } ?>><a href="states.php">Add/Edit State</a></li>
				<li <?php if($file=="city"){ ?> class="active" <?php } ?>><a href="city.php">Add/Edit City</a></li>
			</ul>
		</li>
        <li <?php if($file=="cms-view" || $file=="cms-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-list-alt"></i>
				<span class="menu-text"> Manage CMS </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="cms-view"){ ?> class="active" <?php } ?>><a href="cms-view.php">View CMS</a></li>
			</ul>
		</li>
        <li <?php if($file=="support-category-list" || $file=="support-category-edit" || $file=="support-category-add" || $file=="support_change"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-lightbulb"></i>
				<span class="menu-text"> Manage Support </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="support-category-list" || $file=="support-category-edit" || $file=="support-category-add"){ ?> class="active" <?php } ?>><a href="support-category-list.php">Support Category</a></li>
				<li <?php if($file=="support_change"){ ?> class="active" <?php } ?>><a href="support_change.php">Add/Edit Support</a></li>
			</ul>
		</li>
        <li <?php if($file=="maincat-view" || $file=="maincat-add" || $file=="maincat-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-list-alt"></i>
				<span class="menu-text"> Main Category </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="maincat-view" || $file=="maincat-edit"){ ?> class="active" <?php } ?>><a href="maincat-view.php">Main-Category List</a></li>
				<li <?php if($file=="maincat-add"){ ?> class="active" <?php } ?>><a href="maincat-add.php">Add Main-Category</a></li>
			</ul>
		</li>
        <li <?php if($file=="category-view" || $file=="category-add" || $file=="category-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-list-alt"></i>
				<span class="menu-text"> Category </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="category-view" || $file=="category-edit"){ ?> class="active" <?php } ?>><a href="category-view.php">View Category</a></li>
				<li <?php if($file=="category-add"){ ?> class="active" <?php } ?>><a href="category-add.php">Add Category</a></li>
			</ul>
		</li>
        <li <?php if($file=="subcat-view" || $file=="subcat-add" || $file=="subcat-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-list-alt"></i>
				<span class="menu-text"> Sub Category </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="subcat-view" || $file=="subcat-edit"){ ?> class="active" <?php } ?>><a href="subcat-view.php">Subcategory List</a></li>
				<li <?php if($file=="subcat-add"){ ?> class="active" <?php } ?>><a href="subcat-add.php">Add Subcategory</a></li>
			</ul>
		</li>
        <li <?php if($file=="field-add" || $file=="field-view" || $file=="field-option"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<span class="icon-plus-sign"></span>
				<span class="menu-text"> Additional Fields </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="field-add"){ ?> class="active" <?php } ?>><a href="field-add.php">Add Field</a></li>
                <li <?php if($file=="field-view"){ ?> class="active" <?php } ?>><a href="field-view.php">View Field</a></li>
                <li <?php if($file=="field-option"){ ?> class="active" <?php } ?>><a href="field-option.php">View Option</a></li>
			</ul>
		</li>
        <li <?php if($file=="user-list" || $file=="user-details"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-user"></i>
				<span class="menu-text"> Manage User </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="user-list" || $file=="user-details"){ ?> class="active" <?php } ?>><a href="user-list.php">User List</a></li>
			</ul>
		</li>
        <li <?php if($file=="company-list" || $file=="company-details"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-flag-alt"></i>
				<span class="menu-text"> Company Profile </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="company-list" || $file=="company-details"){ ?> class="active" <?php } ?>><a href="company-list.php">Company List</a></li>
			</ul>
		</li>
        <li <?php if($file=="product-view" || $file=="product-details" || $file=="product-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text"> Manage Product </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="product-view" || $file=="product-details" || $file=="product-edit"){ ?> class="active" <?php } ?>><a href="product-view.php">View Products</a></li>
			</ul>
		</li>
        <li <?php if($file=="buyreq-view" || $file=="buyreq-details" || $file=="buyreq-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text">Buy Requirement</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="buyreq-view" || $file=="buyreq-details" || $file=="buyreq-edit"){ ?> class="active" <?php } ?>><a href="buyreq-view.php">View Buy Requirement</a></li>
			</ul>
		</li>
        <li <?php if($file=="selloffer-view" || $file=="selloffer-details" || $file=="selloffer-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text">Sell Offer</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="selloffer-view" || $file=="selloffer-details" || $file=="selloffer-edit"){ ?> class="active" <?php } ?>><a href="selloffer-view.php">View Sell Offers</a></li>
			</ul>
		</li>
        <li <?php if($file=="tender-view" || $file=="tender-details" || $file=="tender-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text">Tender</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="tender-view" || $file=="tender-details" || $file=="tender-edit"){ ?> class="active" <?php } ?>><a href="tender-view.php">View Tenders</a></li>
			</ul>
		</li>
        <li <?php if($file=="auction-view" || $file=="auction-details" || $file=="auction-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text">Auction</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="auction-view" || $file=="auction-details" || $file=="auction-edit"){ ?> class="active" <?php } ?>><a href="auction-view.php">View Auctions</a></li>
			</ul>
		</li>
	<li <?php if($file=="measurements" || $file=="business_type" || $file == "ownership_type" || $file == "revenue_turnover" || $file == "employee_range" || $file == "profile_heading"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-book"></i>
				<span class="menu-text"> Measurements </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="measurements" ){ ?> class="active" <?php } ?>><a href="measurements.php"> Measurements Units</a></li>
				<li <?php if($file=="business_type"){ ?> class="active" <?php } ?>><a href="business_type.php">Business Type</a></li>
				<li <?php if($file=="ownership_type"){ ?> class="active" <?php } ?>><a href="ownership_type.php">Ownership Type</a></li>
				<li <?php if($file=="profile_heading"){ ?> class="active" <?php } ?>><a href="profile_heading.php">profile_heading</a></li>
				<li <?php if($file=="revenue_turnover"){ ?> class="active" <?php } ?>><a href="revenue_turnover.php">Revenue Turnover</a></li>
				<li <?php if($file=="employee_range"){ ?> class="active" <?php } ?>><a href="employee_range.php">Employee Range</a></li>
				<li <?php if($file=="payment_method"){ ?> class="active" <?php } ?>><a href="payment_method.php">Payment Methods</a></li>
			</ul>
		</li>
<li <?php if($file=="splan-view" || $file=="splan-add"  || $file=="splan-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-unlock"></i>
				<span class="menu-text">Buisness Plan</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="splan-view" || $file=="splan-edit"){ ?> class="active" <?php } ?>><a href="splan-view.php">View Plans</a></li>
				<li <?php if($file=="splan-add"){ ?> class="active" <?php } ?>><a href="splan-add.php">Add Special Plans</a></li>


                	<li <?php if($file=="splan_icon-view" || $file=="splan-edit"){ ?> class="active" <?php } ?>><a href="splan_icon-view.php">Products Icon</a></li>
				<li <?php if($file=="splan_icon-add"){ ?> class="active" <?php } ?>><a href="splan_icon-add.php">Add Product type icon</a></li>

<li <?php if($file=="splan-badd"){ ?> class="active" <?php } ?>><a href="splan-badd.php">Assign to Vendor</a></li>			</ul>
		</li>
        <li <?php if($file=="memplan-view" || $file=="memplan-add"  || $file=="memplan-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-unlock"></i>
				<span class="menu-text">Membership Plans</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="memplan-view" || $file=="memplan-edit"){ ?> class="active" <?php } ?>><a href="memplan-view.php">View Plans</a></li>
				<li <?php if($file=="memplan-add"){ ?> class="active" <?php } ?>><a href="memplan-add.php">Add Plans</a></li>
			</ul>
		</li>
        <li <?php if($file=="transaction-view"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-credit-card"></i>
				<span class="menu-text">Payment Tracker</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="transaction-view"){ ?> class="active" <?php } ?>><a href="transaction-view.php">View Transactions</a></li>
			</ul>
		</li>
        <li <?php if($file=="video-view"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-film"></i>
				<span class="menu-text">Company Video</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="video-view"){ ?> class="active" <?php } ?>><a href="video-view.php">View Video</a></li>
			</ul>
		</li>
        <li <?php if($file=="testi-view" || $file=="testi-edit" || $file=="testi-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-comments-alt"></i>
				<span class="menu-text">Testimonials</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="testi-view" || $file=="testi-edit"){ ?> class="active" <?php } ?>><a href="testi-view.php">View Testimonials</a></li>
				<li <?php if($file=="testi-add"){ ?> class="active" <?php } ?>><a href="testi-add.php">Add Testimonial</a></li>
			</ul>
		</li>
        <li <?php if($file=="paymethod-add" || $file=="paymethod-view" || $file=="paymethod-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-credit-card"></i>
				<span class="menu-text"> Payment Methods </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
                <li <?php if($file=="paymethod-view" || $file=="paymethod-edit"){ ?> class="active" <?php } ?>><a href="paymethod-view.php">View Method</a></li>
			</ul>
		</li>
        <li <?php if($file=="adv-view" || $file=="adv-edit" || $file=="adv-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Advertisements</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="adv-view" || $file=="adv-edit"){ ?> class="active" <?php } ?>><a href="adv-view.php">View Advertisements</a></li>
				<li <?php if($file=="adv-add"){ ?> class="active" <?php } ?>><a href="adv-add.php">Add Advertisements</a></li>
			</ul>
		</li>
		 
		<li <?php if($file=="yahooslider-view" || $file=="yahooslider-edit" || $file=="yahooslider-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Yahoo Slider Mgmt</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="yahooslider-view" || $file=="yahooslider-edit"){ ?> class="active" <?php } ?>><a href="yahooslider-view.php">View Slider</a></li>
				<li <?php if($file=="yahooslider-add"){ ?> class="active" <?php } ?>><a href="yahooslider-add.php">Add Slider</a></li>
			</ul>
		</li>
		<li <?php if($file=="videoslider-view" || $file=="videoslider-edit" || $file=="videoslider-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Video Slider Mgmt</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="videoslider-view" || $file=="videoslider-edit"){ ?> class="active" <?php } ?>><a href="videoslider-view.php">View Slider</a></li>
				<li <?php if($file=="videoslider-add"){ ?> class="active" <?php } ?>><a href="videoslider-add.php">Add Slider</a></li>
			</ul>
		</li>
		<li <?php if($file=="productslider-view" || $file=="productslider-edit" || $file=="productslider-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Product Slider Mgmt</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="productslider-view" || $file=="productslider-edit"){ ?> class="active" <?php } ?>><a href="productslider-view.php">View Slider</a></li>
				<li <?php if($file=="productslider-add"){ ?> class="active" <?php } ?>><a href="productslider-add.php">Add Slider</a></li>
			</ul>
		</li>
		<li <?php if($file=="serviceslider-view" || $file=="serviceslider-edit" || $file=="serviceslider-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Services Slider Mgmt</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="serviceslider-view" || $file=="serviceslider-edit"){ ?> class="active" <?php } ?>><a href="serviceslider-view.php">View Slider</a></li>
				<li <?php if($file=="serviceslider-add"){ ?> class="active" <?php } ?>><a href="serviceslider-add.php">Add Slider</a></li>
			</ul>
		</li>
		<li <?php if($file=="supp-view" || $file=="supp-edit" || $file=="supp-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Suppliers Logo</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="supp-view" || $file=="supp-edit"){ ?> class="active" <?php } ?>><a href="supp-view.php">View Suppliers Logo</a></li>
				<li <?php if($file=="supp-add"){ ?> class="active" <?php } ?>><a href="supp-add.php">Add Suppliers Logo</a></li>
			</ul>
		</li>
		<li <?php if($file=="advhome-view" || $file=="advhome-edit" || $file=="advhome-add" || $file=="advcathome-view" || $file=="advcathome-edit" || $file=="advcathome-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">H Advertisements</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="advhome-view" || $file=="advhome-edit"){ ?> class="active" <?php } ?>><a href="advhome-view.php">View Advertisements</a></li>
				<li <?php if($file=="advhome-add"){ ?> class="active" <?php } ?>><a href="advhome-add.php">Add Advertisements</a></li>
				<li <?php if($file=="advcathome-view" || $file=="advcathome-edit"){ ?> class="active" <?php } ?>><a href="advcathome-view.php">View Categorywise Advertisements</a></li>
				<li <?php if($file=="advcathome-add"){ ?> class="active" <?php } ?>><a href="advcathome-add.php">Add Categorywise Advertisements</a></li>
			</ul>
		</li>
        <li <?php if($file=="adsense-view" || $file=="adsense-edit" || $file=="adsense-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-desktop"></i>
				<span class="menu-text">Google Adsense</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="adsense-view" || $file=="adsense-edit"){ ?> class="active" <?php } ?>><a href="adsense-view.php">View Adsense</a></li>
				<li <?php if($file=="adsense-add"){ ?> class="active" <?php } ?>><a href="adsense-add.php">Add Adsense</a></li>
			</ul>
		</li>
        <li <?php if($file=="bad_word-view" || $file=="bad_word-edit" || $file=="bad_word-add"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-ban-circle"></i>
				<span class="menu-text">Bad Words</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="bad_word-view" || $file=="bad_word-edit"){ ?> class="active" <?php } ?>><a href="bad_word-view.php">Bad Word List</a></li>
				<li <?php if($file=="bad_word-add"){ ?> class="active" <?php } ?>><a href="bad_word-add.php">Add Bad Word</a></li>
			</ul>
		</li>
        <li <?php if($file=="message-view" || $file=="message-edit"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-envelope-alt"></i>
				<span class="menu-text">Manage Messages</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<li <?php if($file=="message-view" || $file=="message-edit"){ ?> class="active" <?php } ?>><a href="message-view.php">View Messages</a></li>
			</ul>
		</li>
        <li <?php if($file=="contact-view" || $file=="contact-details"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-comment-alt"></i>
				<span class="menu-text"> Manage Contact </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
                <li <?php if($file=="contact-view" || $file=="contact-details"){ ?> class="active" <?php } ?>><a href="contact-view.php">View Contact</a></li>
			</ul>
		</li>
		<li <?php if($file=="membership-requirements-view" || $file=="membership-requirement"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-unlock"></i>
				<span class="menu-text"> Membership Req</span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
                <li <?php if($file=="membership-requirements-view" || $file=="membership-requirement"){ ?> class="active" <?php } ?>><a href="membership-requirements-view.php">View Membership Req</a></li>
			</ul>
		</li>
        <li <?php if($file=="newsletter-view" || $file=="newsletter-send"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-comment-alt"></i>
				<span class="menu-text"> Manage Newsletter </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
                <li <?php if($file=="newsletter-view"){ ?> class="active" <?php } ?>><a href="newsletter-view.php">View Newsletter</a></li>
                <li <?php if($file=="newsletter-send"){ ?> class="active" <?php } ?>><a href="newsletter-send.php">Send Newsletter</a></li>
			</ul>
		</li>
		<li <?php if($file=="connect-us"){ ?> class="active open" <?php } ?>>
			<a href="#" class="dropdown-toggle">
				<i class="icon-comment-alt"></i>
				<span class="menu-text">Connect with us </span>
				<b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
                <li <?php if($file=="connect-us"){ ?> class="active" <?php } ?>><a href="connect-us.php">Edit Connet us</a></li>
			</ul>
		</li>
	</ul>
    <div class="sidebar-collapse" id="sidebar-collapse">
						<i class="icon-double-angle-left" data-icon1="icon-double-angle-left" data-icon2="icon-double-angle-right"></i>
					</div>
</div>
