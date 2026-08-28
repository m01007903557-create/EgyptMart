<base href="/">
    
    <script>
function downMenu(id) {
    // تم تعطيلها - سيتم استخدام click بدلاً من hover
}
function toggleMenu(id) {
    // سيتم التعامل معها عبر click
}
function upMenu(id) {
    // تم تعطيلها
}

$(document).ready(function(){
    // تحويل جميع القوائم من hover إلى click
    
    // Dropmenu 7
    $('[rel=dropmenu7]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu7').slideToggle('slow');
        $('#dropmenu1,#dropmenu2,#dropmenu3,#dropmenu4,#dropmenu5,#dropmenu6').slideUp('slow');
    });
    
    // Dropmenu 6
    $('[rel=dropmenu6]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu6').slideToggle('slow');
        $('#dropmenu1,#dropmenu2,#dropmenu3,#dropmenu4,#dropmenu5,#dropmenu7').slideUp('slow');
    });
    
    // Dropmenu 5
    $('[rel=dropmenu5]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu5').slideToggle('slow');
        $('#dropmenu1,#dropmenu2,#dropmenu3,#dropmenu4,#dropmenu6,#dropmenu7').slideUp('slow');
    });
    
    // Dropmenu 4
    $('[rel=dropmenu4]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu4').slideToggle('slow');
        $('#dropmenu1,#dropmenu2,#dropmenu3,#dropmenu5,#dropmenu6,#dropmenu7').slideUp('slow');
    });
    
    // Dropmenu 3
    $('[rel=dropmenu3]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu3').slideToggle('slow');
        $('#dropmenu1,#dropmenu2,#dropmenu4,#dropmenu5,#dropmenu6,#dropmenu7').slideUp('slow');
    });
    
    // Dropmenu 2
    $('[rel=dropmenu2]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu2').slideToggle('slow');
        $('#dropmenu1,#dropmenu3,#dropmenu4,#dropmenu5,#dropmenu6,#dropmenu7').slideUp('slow');
    });
    
    // Dropmenu 1
    $('[rel=dropmenu1]').off('mouseenter mouseleave').on('click', function(e){
        e.stopPropagation();
        $('#dropmenu1').slideToggle('slow');
        $('#dropmenu2,#dropmenu3,#dropmenu4,#dropmenu5,#dropmenu6,#dropmenu7').slideUp('slow');
    });
    
    // إغلاق القوائم عند النقر في أي مكان آخر في الصفحة
    $(document).on('click', function(e) {
        if (!$(e.target).closest('[rel^=dropmenu], .ddm').length) {
            $('.ddm').slideUp('slow');
        }
    });
});
</script>

<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>

<div class="n-nmz1_exm1 n-nmz2 bx pns14 ml2" id="chromemenu" style="width:100%">
    <a name="addproduct"></a>
    <a name="inbox"></a>
    <a href="my-dashboard.php" class="n-nmz1 bnr fl" title="لوحة مفاتيح - أعمالى وتجارتى">
        <img src="sitelogo/<?php echo htmlspecialchars(get_page_settings(22)); ?>" height="41px" width="130px" alt="Logo"/>
    </a>
    <ul class="n-hdrn">
        <li class="f2 buss-n fw" rel="dropmenu6" style="position:static; cursor:pointer;" title="Account Details">
            بيانات الحساب <span class="n-hdrn2">&nbsp;</span>
            <div style="height: auto; overflow: hidden; display: none; visibility: visible; right: 0px; top: 253px;" id="dropmenu6" class="ddm">
                <a href="my-contactdetails.php">بيانات الاتصال بالشركة</a>
                <a href="change-password.php">تغيير كلمة المرور</a>
                <a href="https://arab-mart.com/">Arab-Mart.com</a>
                <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''): ?>
                <a href="logout.php">تسجيل خروج</a>
                <?php endif; ?>
            </div>
        </li>
        
        <li rel="dropmenu1" style="margin-left:0px; position:static; cursor:pointer;" title="Company Profile">
            صفحات تجارة شركتى 
            <span>&nbsp;</span>
            <div style="position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 150px; top: 253px;" id="dropmenu1" class="ddm">
                <a href="my-contactdetails.php" title="Contact Details">تفاصيل الإتصال بالشركة</a>
                <a href="business-details.php" title="Business Details">معلومات شركتى - تجارتى</a>
                <span>صفحات شركتى</span>
                <a href="my-homepage.php" title="My Home Page">ماذا عن شركتى</a>
                <a href="myprofile.php" title="Profile & News">بروفايل وأخبار شركتى</a>
             <a href="company-video.php" title="Company Videos">فيديوهات الشركة</a>
            </div>
        </li>
         
        <li rel="dropmenu2" style="position:static; cursor:pointer;" title="Enquiries & Contacts">
            البريد وبيانات الإتصال 
            <span>&nbsp;</span>
            <div style="position:absolute; width: 190px; height: auto; overflow: hidden; visibility: visible; display: none; left: 300px; top: 253px;" id="dropmenu2" class="ddm">
                <a href="my-enquiries.php" title="Inbox">البريد الوارد</a>
                <a href="my-enquiries.php" title="Sent Box">البريد الصادر</a>
                <a href="transaction_history.php" title="Transaction History">بيانات دفع المستخدم</a>
                <a href="my-addressbook.php" title="Contacts List">بيانات الإتصال لشركتى</a>
            </div>
        </li>
        
        <li rel="dropmenu3" style="position:static; cursor:pointer;" title="Buy Leads">
            طلبات الشراء <span>&nbsp;</span>
            <div style="position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 480px; top: 253px;" id="dropmenu3" class="ddm">
                <span title="Buy Requests Purchases">بيانات طلبات الشراء</span>
                <a href="manage-purchased-buyleads.php" title="Purchased Buy Requests">طلبات الشراء المشتراه</a>
                <a href="manage-purchased-buyleads.php" title="Purchased Buy Requests">بيانات طلبات الشراء الجاهزة</a>
                <a href="transaction_history.php" title="Transaction History">بيانات دفع المستخدم</a>
                <a href="myproduct-buy.php" title="Regular Buy Requirements">طلبات الشراء المعتادة</a>
            </div>
        </li>
        
        <li rel="dropmenu4" class="buyercss1 firfox-css" style="padding-right:4px; position:static; cursor:pointer;" id="myproducts" title="Seller Tools">
            أدوات البائع<span>&nbsp;</span>
            <div style="position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 590px; top: 253px;" id="dropmenu4" class="ddm" title="Products / Services">
                <span>منتجات وخدمات</span>
                
                <?php 
// الحصول على معرف خطة العضوية
$uid_test = $_SESSION['uid_indm'];
$sql_test = "SELECT usr_mp_id FROM user WHERE usr_id = '$uid_test'";
$res_test = mysqli_query($con, $sql_test);
$row_test = mysqli_fetch_assoc($res_test);
$mp_id = $row_test['usr_mp_id'];

if ($mp_id == 3) {
    $test_link = 'membership_plans.php';
} else {
    $test_link = 'product-sel-cat.php';
}

?>

<a href="<?php echo $test_link; ?>">أضف منتجات</a>
                 

                <a href="product-list.php" title="Manage Products">إدارة المنتجات المعروضة</a>
                <span>طلبات الشراء الجاهزة</span>
                <a href="manage-purchased-buyleads.php" title="Purchased Buy Requests">بيانات طلبات الشراء المشتراه</a>
                <a href="manage-buylead-alert.php" title="Manage Buy Requests Alerts">إدارة إشعارات طلبات الشراء</a>
                <span>عروض البيع</span>
                <a href="post-sell-offer.php" title="Post a Sale Offer">أنشر عرض بيع خاص</a>
                <a href="manage-sell-offer.php" title="Manage Sale Offer">إدارة عروض البيع المسجلة</a>
                <span>منتجات بيع الشركة</span>
                <a href="myproduct-sell.php" title="Products We Sell">منتجات بيعى المعتادة</a>
            </div>
        </li>
        
        <li rel="dropmenu5" style="padding-right:4px; position:static; cursor:pointer;" title="Buyer Tools">
            أدوات المشترى <span>&nbsp;</span>
            <div style="position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 710px; top: 253px;" id="dropmenu5" class="ddm">
                <a href="post-buy-req.php" title="Post a Buy Requirement">أنشر طلبات شراء</a>
                <a href="manage-buy-requirement.php" title="Manage Buy Requirements">إدارة طلبات الشراء المسجلة</a>
                <a href="manage-selloffer-alert.php" title="Subscribe Sell Offers Alerts">تلقى إشعارات عروض بيع</a>
                <a href="search_adv.php" title="Search Products & Suppliers">إبحث عن المنتجات والخدمات</a>
                <a href="myproduct-buy.php" title="Products We Buy">منتجات شرائنا المعتادة</a>
            </div>
        </li>
        
        <li rel="dropmenu7" style="padding-right:2px; position:static; cursor:pointer;" title="Tenders / Auctions">
            مناقصات ومزايدات <span>&nbsp;</span>
            <div style="position:absolute; height: auto; overflow: hidden; display: none; visibility: visible; left: 825px; top: 253px;" id="dropmenu7" class="ddm">
                <a href="post-tender.php" title="Post Tender FREE">إنشر مناقصات مجانا</a>
                <a href="manage-tenders.php" title="Manage Tenders">إدارة عروض المناقصات</a>
                <a href="manage-purchased-tenders.php" title="Purchased Tenders">بيانات المناقصات المشتراة</a>
                <a href="manage-tender-alert.php" title="Manage Tender Alert">إدارة إشعارات المناقصات</a>
                <span>المزايدات</span>
                <a href="post-auction.php" title="Post Auction FREE">إنشر مزايدة مجانا</a>
                <a href="manage-auctions.php" title="Manage Auctions">إدارة المزايدات</a>
                <a href="manage-purchased-auctions.php" title="Purchased Auctions">بيانات المزايدات المشتراة</a>
                <a href="manage-auction-alert.php" title="Manage Auction Alert">إدارة إشعارات المزايدات</a>
            </div>
        </li>
    </ul>
</div>