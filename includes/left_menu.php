<div class="f1 w61n tb lh ml br" id="lnav">
    <ul class="nln1" style="margin:0px; padding:0px">
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Cairo', sans-serif;
            }
        </style>
        <link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>    
        
        <li>
            <h3 style="font-size: 19px; font-weight: bold; color:#f50000; text-align:center; margin:0; padding: 18px 5px 18px 5px; background-color: ;" title="Company Profile">
                تفاصيـــل الشركــة
            </h3>
        </li>
    </ul>
    
    <ul id="ulid" class="nln1" style="margin:0px; padding:0px">
        
        <?php if(isset($file) && $file != "business-details"){ ?>
        <li>
            <center>
                <div>
                    <div class="file_button" style="margin:0px; height:10px;" id="addbut">
                        <div id="queue">
                            <div align="left" id="list_photo" class="line clearfix"></div>
                        </div>
                    </div>
                </div>
            </center>
        </li>
        <?php } ?>
        
        <li class="np npnew">
            <a <?php if(isset($file) && $file == "my-contactdetails"){ ?>class="leftindi txtcol"<?php } ?> href="my-contactdetails.php" title="Contact Details">
                &raquo;&nbsp; معلومات الإتصال بالشركة
            </a>
        </li>
        
        <li class="np npnew">
            <a <?php if(isset($file) && ($file == "business-details" || $file == "statutory-details" || $file == "myproduct-buy")){ ?>class="leftindi txtcol"<?php } ?> href="business-details.php" title="Business Profile">
                &raquo;&nbsp;معلومات التجارة والأعمال
            </a>
        </li>

        <li style="border-bottom:0052ce" title="Website Pages">
            <h3>صفحات تجارة شركتى</h3>
        </li>
        
        <li class="np npnew">
            <a <?php if(isset($file) && $file == "my-homepage"){ ?>class="leftindi txtcol"<?php } ?> href="my-homepage.php" title="My Home Page">
                &raquo;&nbsp; ماذا عن الشركة
            </a>
        </li>
        
        <li class="np npnew">
            <a <?php if(isset($file) && $file == "myprofile"){ ?>class="leftindi txtcol"<?php } ?> href="myprofile.php" title="Profile & News">
                &raquo; بروفايل وأخبار الشركة
            </a>
        </li>

        <li class="np npnew">
            <a <?php if(isset($file) && $file == "company-video"){ ?>class="leftindi txtcol"<?php } ?> href="company-video.php" title="Company Video">
                &raquo;&nbsp;فيديوهات الشركة
            </a>
        </li>
        
        <li class="np npnew">
            <a <?php if(isset($file) && $file == "company-banner"){ ?>class="leftindi txtcol"<?php } ?> href="company-banner.php" title="Company Images">
                &raquo;&nbsp;صور الشركة والموظفين
            </a>
        </li>

        <li class="np npnew">
            <a href="product-list.php" title="Products / Service Pages">
                &raquo;&nbsp;صفحات منتجات وخدمات الشركة
            </a>
        </li>
        
        <li style="border-bottom:none" title="Account Details">
            <h3>بيانات الحساب</h3>
        </li>
        
        <?php if(isset($uid) && user_info($uid, 'usr_oauth_reg') == '0'){ ?>
        <li class="np npnew">
            <a <?php if(isset($file) && $file == "change-password"){ ?>class="leftindi txtcol"<?php } ?> href="change-password.php" title="Change Password">
                &raquo;&nbsp;تغيير كلمـة السـر
            </a>
        </li>
        <?php } ?>
        
        <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''){ ?>
        <li class="np npnew">
            <a href="logout.php" title="Sign Out">
                &raquo;&nbsp;تسجيل خروج
            </a>
        </li>
        <?php } ?>
        
        <li style="border-bottom:none; margin-top:40px font-size: 12px">
            <h2>روابط هامة لأعمالك</h2>
        </li>
        
        <li class="np npnew">
            <a href="product-add.php">أضف منتجات جديدة الى صفحتك</a>
        </li>
        <li class="np npnew">
            <a href="manage-buylead-alert.php">تلقى إشعارات طلبات شراء</a>
        </li>
        <li class="np npnew">
            <a href="manage-selloffer-alert.php">تلقى إشعارات عروض بيع</a>
        </li>
        <li class="np npnew">
            <a href="manage-tender-alert.php">تلقى إشعارات مناقصــــات</a>
        </li>
        <li class="np npnew">
            <a href="manage-auction-alert.php">تلقى إشعارات مــزايـــدات</a>
        </li>
    </ul>
</div>