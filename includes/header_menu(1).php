<?php
/**
 * اسم الملف: header_menu.php
 * الوصف: قائمة التنقل الرئيسية - النسخة الأصلية مع ترقية PHP 8.3 فقط
 * الإصدار: 1.0.1
 * تاريخ التحديث: 2024-01-26
 * ملاحظة: تم ترقية PHP فقط مع الحفاظ على كل الكود الأصلي كما هو
 */

// التحقق من عدم الوصول المباشر
if (!defined('ACCESS_ALLOWED') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('وصول غير مصرح به');
}

// المتغيرات المطلوبة من الملفات الأخرى
$file = $file ?? '';
$usr_mp_id = $usr_mp_id ?? 0;
?>

<script>
function downMenu(id)
{	

	// if (doWid >= 768)
	// {
	// 	//$("#dropmenu"+id).css('visibility', 'visible')
	// }
}
function toggleMenu(id)
{
	
	// if (doWid <= 768)
	// {
	// 	//$("#dropmenu"+id).fadeToggle()
	// }
}
function upMenu(id)
{
	// if (doWid >= 768)
	// {
	// 	//$("#dropmenu"+id).css('visibility', 'hidden')
	// }
}
$(document).ready(function(){
	
	$('[rel=dropmenu7]').on({
		mouseenter: function(){
			$('#dropmenu7').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu7').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
		}
	})
	$('[rel=dropmenu6]').on({
		mouseenter: function(){
			$('#dropmenu6').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu6').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}
	})
	$('[rel=dropmenu5]').on({
		mouseenter: function(){
			$('#dropmenu5').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu5').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}
	})
	$('[rel=dropmenu4]').on({
		mouseenter: function(){
			$('#dropmenu4').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu4').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}

	})
	$('[rel=dropmenu3]').on({
		mouseenter: function(){
			$('#dropmenu3').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu3').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu2').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}
	})
	$('[rel=dropmenu2]').on({
		mouseenter: function(){
			$('#dropmenu2').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu2').slideToggle('slow')

			$('#dropmenu1').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}
	})
	$('[rel=dropmenu1]').on({
		mouseenter: function(){
			$('#dropmenu1').slideToggle('slow')

			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		},
		mouseleave: function(){
			$('#dropmenu1').slideToggle('slow')

			$('#dropmenu2').slideUp('slow')
			$('#dropmenu3').slideUp('slow')
			$('#dropmenu4').slideUp('slow')
			$('#dropmenu5').slideUp('slow')
			$('#dropmenu6').slideUp('slow')
			$('#dropmenu7').slideUp('slow')
		}
	})
	
})
</script>
<!--feedback widget:ends-->
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>

<div class="n-nmz1_exm1 n-nmz2 bx pns14 ml2" id="chromemenu" style="width:100%">
	<a name="addproduct"></a>
	<a name="inbox"></a>
	<a href="my-dashboard.php" class="n-nmz1 bnr fl" title="لوحة مفاتيح - أعمالى وتجارتى"><img src="sitelogo/<?php echo htmlspecialchars(get_page_settings(22)); ?>" height="41px" width="130px"/></a>
	<ul class="n-hdrn">
		<li class="f2 buss-n fw" rel="dropmenu6" onClick="toggleMenu(6)" onMouseOver="downMenu(6);" onMouseOut="upMenu(6);" style="position:static;" title="Account Details">بيانات الحساب <span class="n-hdrn2">&nbsp;</span>

        	<!--business offers for you-->
        	<div style="height: auto; overflow: hidden; display: none; visibility: visible; right: 0px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu6" class="ddm">
				<a href="my-contactdetails.php">بيانات الاتصال بالشركة</a>
				<a href="change-password.php">تغيير كلمة المرور</a>
                <a href="https://arabyos.com/">ARABYOS.com</a>
		        <?php if(isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])){ ?>
		            <a href="logout.php">تسجيل خروج</a>
		        <?php } ?>
			</div>
        </li>
		<li rel="dropmenu1" class="" style="margin-left:0px" onClick="toggleMenu(1)" onMouseOver="downMenu(1);" onMouseOut="upMenu(1);" style="position:static;" title="Company Profile ">
			صفحات تجارة شركتى 
			<span>&nbsp;</span>
        
        <!--company profile-->
        <div style=" position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 150px; <?php if($file == 'post-buy-req'){?>top: 253px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu1" class="ddm">
        
                <a href="my-contactdetails.php" title="Contact Details">تفاصيل الإتصال بالشركة</a>
				<a href="business-details.php" title="Business Details">معلومات شركتى - تجارتى</a>
   		        <span>صفحات شركتى</span>
				<a href="my-homepage.php" title="My Home Page">ماذا عن شركتى </a>
				<a href="myprofile.php" title="Profile & News"> بروفايل وأخبار شركتى</a>
		</div>
         </li>
         
		<li rel="dropmenu2" class="" onClick="toggleMenu(2)" onMouseOver="downMenu(2);" onMouseOut="upMenu(2);" style="position:static;" title=" Enquiries & Contacts ">
			البريد وبيانات الإتصال 
			<span>&nbsp;</span>
        	<!--Enquiries & Contacts-->
	        <div style=" position:absolute; width: 190px; height: auto; overflow: hidden; visibility: visible; display: none; left: 300px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu2" class="ddm">
				<a href="my-enquiries.php" title="Inbox">البريد الوارد</a>
				<a href="my-enquiries.php" title="Sent Box">البريد الصادر </a>
				<a href="transaction_history.php" title="Transaction History.php "> بيانات دفع المستخدم </a>
				<a href="my-addressbook.php" title="Contacts List">بيانات الإتصال لشركتى</a>
			
			</div>
        </li>
        
		<li rel="dropmenu3" class="" onClick="toggleMenu(3)" onMouseOver="downMenu(3);" onMouseOut="upMenu(3);" style="position:static;" title="Buy Leads">طلبات الشراء <span>&nbsp;</span>
        <!--buy leads-->
		<div style=" position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 480px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu3" class="ddm">
			<span onMouseOver="downMenu(3);" onMouseOut="upMenu(3);" title="Buy Requests Purchases">بيانات طلبات الشراء </span>
            <a href="manage-purchased-buyleads.php" title="Purchased Buy Requests"> طلبات الشراء المشتراه</a>
			<a href="manage-purchased-buyleads.php" title="Purchased Buy Requests ">بيانات طلبات الشراء الجاهزة </a>
			<a href="transaction_history.php" title="Transaction History.php "> بيانات دفع المستخدم </a>
			<a href="myproduct-buy.php" title="Regular Buy Requirements">طلبات الشراء المعتادة</a>
		</div>
        </li>
        
		<li rel="dropmenu4" class="buyercss1 firfox-css" style="padding-right:4px" onClick="toggleMenu(4)" onMouseOver="downMenu(4);" onMouseOut="upMenu(4);" id="myproducts" style="position:static;" title="Seller Tools">أدوات البائع<span>&nbsp;</span>
        <!-- Seller Tools -->
        <div style=" position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 590px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu4" class="ddm" title="Products / Services">
			<span>منتجات وخدمات</span>
			<?php if(function_exists('get_membership_expired') && get_membership_expired() != false){ ?>
				<a href="product-add.php" title="Add New Products">إعرض منتجات أو خدمات</a>
			<?php }else{ ?>
				<a href="membership_plans.php">إعرض منتجات أو خدمات</a>
			<?php } ?>
       
        	<a href="product-list.php" title="Manage Products"> إدارة المنتجات المعروضة</a> 
			<span> طلبات الشراء الجاهزة </span>
			<a href="manage-purchased-buyleads.php" title="Purchased Buy Requests"> بيانات طلبات الشراء المشتراه </a>
        	<a href="manage-buylead-alert.php" title="Manage Buy Requests Alerts ">إدارة إشعارات طلبات الشراء</a>
			<span> عروض البيع </span>
			<a href="post-sell-offer.php" title="Post a Sale Offer">أنشر عرض بيع خاص </a>
			<a href="manage-sell-offer.php" title="Manage Sale Offer">إدارة عروض البيع المسجلة </a>
            <span>منتجات بيع الشركة </span>
          	<a href="myproduct-sell.php" title="Products We Sell "> منتجات بيعى المعتادة </a>
		</div>
        </li>
		
        <li rel="dropmenu5" class="" style="padding-right:4px;" onClick="toggleMenu(5)" onMouseOver="downMenu(5);" onMouseOut="upMenu(5);" title="Buyer Tools">أدوات المشترى <span>&nbsp;</span>
        <!--Buyer Tools-->
        <div style=" position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 710px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu5" class="ddm">
			<a href="post-buy-req.php" title="Post a Buy Requirement">أنشر طلبات شراء</a>
			<a href="manage-buy-requirement.php" title="Manage Buy Requirements">إدارة طلبات الشراء المسجلة</a>
			<a href="manage-selloffer-alert.php" title="Subscribe Sell Offers Alerts ">تلقى إشعارات عروض بيع  </a>
			<a href="search_adv.php" title="Search Products & Suppliers">إبحث عن المنتجات والخدمات </a>
            <a href="myproduct-buy.php" title="Products We Buy"> منتجات شرائنا المعتادة</a>
		</div>
        </li>
        
        <li rel="dropmenu7" class="" style="padding-right:2px;" onClick="toggleMenu(7)" onMouseOver="downMenu(7);" onMouseOut="upMenu(7);" title="Tenders / Auctions">مناقصات ومزايدات <span>&nbsp;</span>
        <!--Tender-->
        <div style=" position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 825px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu7" class="ddm">
			<a href="post-tender.php" title="Post Tender FREE">إنشر مناقصات مجانا</a>
			<a href="manage-tenders.php" title="Manage Tenders">إدارة عروض المناقصات  </a>
            <a href="manage-purchased-tenders.php" title="Purchased Tenders">بيانات المناقصات المشتراة </a>
            <a href="manage-tender-alert.php" title=" Manage Tender Alert">إدارة إشعارات المناقصات </a>
            <span>المزايدات</span>
            <a href="post-auction.php" title="Post Auction FREE">إنشر مزايدة مجانا</a>
			<a href="manage-auctions.php" title="Manage Auctions">إدارة المزايدات  </a>
            <a href="manage-purchased-auctions.php" title="Purchased Auctions">بيانات المزايدات المشتراة </a>
            <a href="manage-auction-alert.php" title=" Manage Auction Alert">إدارة إشعارات المزايدات </a>
		</div>
        </li>
	</ul>
</div>