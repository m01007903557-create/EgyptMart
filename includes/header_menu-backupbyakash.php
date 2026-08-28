<script>
var doWid = $('body').width()
function downMenu(id)
{	
	if (doWid >= 768){
		$("#dropmenu"+id).css('visibility', 'visible')
	}
}
function toggleMenu(id)
{
	
	if (doWid <= 768)
	{
		$("#dropmenu"+id).fadeToggle()
	}
}
function upMenu(id)
{
	if (doWid >= 768)
	{
		$("#dropmenu"+id).css('visibility', 'hidden')
	}
}
</script>
<!--feedback widget:ends-->


<div class="n-nmz1_exm1 n-nmz2 bx pns14 ml2" id="chromemenu" style="width:100%">
	<a name="addproduct"></a>
	<a name="inbox"></a>
	<a href="my-dashboard.php" class="n-nmz1 bnr fl"title="لوحة مفاتيح - أعمالى وتجارتى"><img src="sitelogo/<?php echo get_page_settings(22); ?>" height="41px" width="130px"/></a>
	<ul class="n-hdrn">
		<li class="f2 buss-n fw" rel="dropmenu6" onClick="toggleMenu(6)"  onMouseOver="downMenu(6);" onMouseOut="upMenu(6);" style="position:static;"title="بيانات الحساب  ">
			Account Details
			<span class="n-hdrn2">&nbsp;</span>

        	<!--business offers for you-->
        	<div style="height: auto; overflow: hidden; visibility: hidden; right: 0px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu6" class="ddm" onMouseOver="downMenu(6);" onMouseOut="upMenu(6);">
				<a href="my-contactdetails.php">Contact Details</a>
				<a href="change-password.php">Change Password</a>
		        <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){	?>
		        <a href="logout.php">Sign Out</a>
		        <?php } ?>
			</div>
        </li>
		<li rel="dropmenu1" class="" style="margin-left:0px" onClick="toggleMenu(1)" onMouseOver="downMenu(1);" onMouseOut="upMenu(1);" style="position:static;"title="Company Profile ">
			البروفايل والموقع المصغر 
			<span>&nbsp;</span>
        
        <!--company profile-->
        <div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 150px; <?php if($file == 'post-buy-req'){?>top: 253px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu1" class="ddm" onMouseOver="downMenu(1);" onMouseOut="upMenu(1);">
                <a href="my-contactdetails.php"title="Contact Details">تفاصيل الإتصال بالشركة</a>
		<a href="business-details.php"title="Business Details">معلومات شركتى - تجارتى</a>
   		<span>B2B صفحات موقع شركتى المصغر</span>
		<a href="my-homepage.php"title="My Home Page">ماذا عن شركتى </a>
		
		<a href="myprofile.php"title="Profile & News"> بروفايل وأخبار الشركة </a>
		</div>
         </li>
         
		<li rel="dropmenu2" class="" onClick="toggleMenu(2)" onMouseOver="downMenu(2);" onMouseOut="upMenu(2);" style="position:static;"title=" Enquiries & Contacts ">ا
			لبريد وبيانات الإتصال 
			<span>&nbsp;</span>
        	<!--Enquiries & Contacts-->
	        <div style=" position:absolute; width: 190px; height: auto; overflow: hidden; visibility: hidden; left: 300px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu2" class="ddm" onMouseOver="downMenu(2);" onMouseOut="upMenu(2);">
				<a href="my-enquiries.php"title="Inbox">البريد الوارد</a>
				<a href="my-enquiries.php"title="Sent Box">البريد الصادر </a>
				
				<a href="my-addressbook.php"title="Contacts List">بيانات الإتصال للشركة</a>
			
			</div>
        </li>
        
		<li rel="dropmenu3" class="" onClick="toggleMenu(3)" onMouseOver="downMenu(3);" onMouseOut="upMenu(3);" style="position:static;"title="Buy Leads">طلبات الشراء <span>&nbsp;</span>
        <!--buy leads-->
		<div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 480px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu3" class="ddm" onMouseOver="downMenu(3);" onMouseOut="upMenu(3);">
			<span onMouseOver="downMenu(3);" onMouseOut="upMenu(3);"title="Buy Requests Purchases">بيانات طلبات الشراء المشتراه</span>
                        <a href="manage-purchased-buyleads.php"title="Purchased Buy Requests"> طلبات الشراء المشتراه</a>
			<a href="manage-purchased-buyleads.php"title="Purchased Buy Requests ">بيانات طلبات الشراء الجاهزة </a>
			<a href="myproduct-buy.php"title="Regular Buy Requirements">طلبات الشراء المعتادة</a>
		</div>
        </li>
        
		<li rel="dropmenu4" class="buyercss1 firfox-css" style="padding-right:4px" onClick="toggleMenu(4)" onMouseOver="downMenu(4);" onMouseOut="upMenu(4);" id="myproducts" style="position:static;"title="Seller Tools">أدوات البائع<span>&nbsp;</span>
        <!-- Seller Tools --><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 590px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu4" class="ddm" onMouseOver="downMenu(4);" onMouseOut="upMenu(4);"title="Products / Services">
		<span>منتجات وخدمات</span>
			<?php if(get_membership_expired()!=false){ ?>
						 <a href="product-add.php"title="Add New Products">إعرض منتجات أو خدمات</a>
					<?php }else{ ?>
						 <a href="membership_plans.php">Add New Products</a>

					<?php } ?>
       
        <a href="product-list.php"title="Manage Products"> إدارة المنتجات المعروضة</a> 
		<span> طلبات الشراء الجاهزة </span>
		<a href="manage-purchased-buyleads.php"title="Purchased Buy Requests"> بيانات طلبات الشراء المشتراه </a>
        <a href="manage-buylead-alert.php"title="Manage Buy Requests Alerts ">إدارة إشعارات طلبات الشراء</a>
		<span> عروض البيع </span>
		<a href="post-sell-offer.php"title="Post a Sale Offer">أنشر عرض بيع خاص </a>
		<a href="manage-sell-offer.php"title="Manage Sale Offer">إدارة عروض البيع المسجلة </a>
              <span>منتجات بيع الشركة </span>
          <a href="myproduct-sell.php"title="Products We Sell "> منتجات بيعى المعتادة </a>
		</div>
         </li>
		
        <li rel="dropmenu5" class="" style="padding-right:4px;" onClick="toggleMenu(5)" onMouseOver="downMenu(5);" onMouseOut="upMenu(5);"title="Buyer Tools">أدوات المشترى <span>&nbsp;</span>
        <!--Buyer Tools--><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 710px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu5" class="ddm" onMouseOver="downMenu(5);" onMouseOut="upMenu(5);">
			<a href="post-buy-req.php"title="Post a Buy Requirement">أنشر طلبات شراء</a>
			<a href="manage-buy-requirement.php"title="Manage Buy Requirements">إدارة طلبات الشراء المسجلة</a>
			<a href="manage-selloffer-alert.php"title="Subscribe Sell Offers Alerts ">تلقى إشعارات عروض بيع لمشترياتك </a>
			<a href="search_adv.php"title="Search Products & Suppliers">إبحث عن المنتجات والخدمات </a>
                <a href="myproduct-buy.php"title="Products We Buy
"> منتجات شرائنا المعتادة</a>

		</div>
        </li>
        
        <li rel="dropmenu7" class="" style="padding-right:2px;" onClick="toggleMenu(7)" onMouseOver="downMenu(7);" onMouseOut="upMenu(7);"title="Tenders / Auctions">مناقصات ومزايدات <span>&nbsp;</span>
        <!--Tender--><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 825px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu7" class="ddm" onMouseOver="downMenu(7);" onMouseOut="upMenu(7);">
			<a href="post-tender.php"title="Post Tender FREE">إنشر مناقصات مجانا</a>
			<a href="manage-tenders.php"title="Manage Tenders">إدارة عروض المناقصات المنشورة </a>
             <a href="manage-purchased-tenders.php"title="Purchased Tenders">بيانات المناقصات المشتراة </a>
             <a href="manage-tender-alert.php"title=" Manage Tender Alert">إدارة إشعارات المناقصات </a>
             
		</div>
        </li>
        
       
		</div>
        </li>
		<!--[if IE 8]><style>.buyercss1{padding-right:1px!important;}</style><![endif]-->
	</ul>
</div>
