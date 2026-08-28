<?php
// تأكد من وجود تعريف الدالة
if (!function_exists('getUserInfo')) {
    require_once __DIR__ . '/function.php'; // أو المسار الصحيح
}
?>

<link rel="stylesheet" href="/inner-header.css">
<div class="top-bar" id="topbar">
  <div class="top-bar-inner">
  <div class="top-lft">
      <ul>
        <?php 
        $cid = '';
        if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
            $uid = (int)$_SESSION['uid_indm'];
            
            // استعلام أيقونة العضوية - محسن
            $sql_icon = "SELECT sip.mst_icon, sip.mst_name FROM smembership_icon_plan sip 
                         JOIN user u ON sip.mp_id = u.usr_mp_id WHERE u.usr_id = ?";
            $stmt_icon = mysqli_prepare($con, $sql_icon);
            if ($stmt_icon) {
                mysqli_stmt_bind_param($stmt_icon, 'i', $uid);
                mysqli_stmt_execute($stmt_icon);
                $result_icon = mysqli_stmt_get_result($stmt_icon);
                $get_icon = $result_icon;
            } else {
                $get_icon = false;
            }
            
            // استعلام بيانات المستخدم
            $sql = "SELECT * FROM user, business_profile 
                    WHERE usr_id = bnsprof_uid AND usr_id = ? AND status = '1'";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $uid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_object($result);
                mysqli_stmt_close($stmt);
            } else {
                $row = null;
            }
            
            if ($row && isset($row->bnsprof_id)) {
                $cid = rand(1000, 9999) . md5($row->bnsprof_id);
            }
        ?>
        <li>
          <span class="pp1">
            <span class="tlc"> مرحبـا </span>
            <?php echo htmlspecialchars(getUserInfo($uid, 'name_prefix'), ENT_QUOTES, 'UTF-8') . "&nbsp;" . htmlspecialchars(getUserInfo($uid, 'fname'), ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($row && $row->bnsprof_compname != '') { ?>
            <span>
              <?php 
              if ($get_icon && mysqli_num_rows($get_icon) > 0) {
                  if (function_exists('get_membership_expired') && get_membership_expired() != true) {
                      $title = 'Junior';
                      $icon = mysqli_fetch_array($get_icon);
                      
                      if (strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                          $title = 'Senior';
                      } else if (strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                          $title = 'Sponsor';
                      }
                      
                      $icon_src = !empty($icon['mst_icon']) ? $icon['mst_icon'] : '';
              ?>
              <a href="company/index.php?c=<?php echo htmlspecialchars($cid, ENT_QUOTES, 'UTF-8'); ?>">
                <img src="admin/images/<?php echo htmlspecialchars($icon_src, ENT_QUOTES, 'UTF-8'); ?>" 
                     title="<?php echo strtoupper(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')); ?>" 
                     class="membership-icon" alt=""/>
              </a>
              <?php 
                  }
              }
              if ($stmt_icon) { mysqli_stmt_close($stmt_icon); }
              ?>
            </span>
            <?php } ?>
          </span>
        </li>
        <?php } else { ?>
        <li>
          <a class="top-auth-link top-sign-in" href="sign-in.php#loginform" target="_top" rel="nofollow" title="Sign in">سجل دخول</a>
        </li>
        |
        <li>
          <a class="top-auth-link" href="create_account.php#signupform" target="_top" rel="nofollow" title="Join Free">إنشىء حساب مجانا &nbsp;|</a>
        </li>
        <?php } ?>
        
        <li class="dropdown dropdown1 top-menu-item" title="عملى على - سوق مصر على الإنترنت">
          <a data-target="myEgyptmart" class="dropbtn1" href="" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <b class="txt-yellow top-menu-label">تجـارتى</b> <i class="fa fa-chevron-down"></i>
          </a>
          <span class="linebr"> |</span>
          <a href="my-enquiries.php">
            <img class="message-icon" src="images/envolap.png" alt=""/>
            <?php
            if (isset($_SESSION['uid_indm'])) {
                $uid_msg = (int)$_SESSION['uid_indm'];
                $query_pag_num = "SELECT COUNT(*) AS count FROM message, user 
                                  WHERE msg_to = ? AND msg_from = usr_id AND msg_to_status = '1'";
                $stmt_msg = mysqli_prepare($con, $query_pag_num);
                if ($stmt_msg) {
                    mysqli_stmt_bind_param($stmt_msg, 'i', $uid_msg);
                    mysqli_stmt_execute($stmt_msg);
                    $result_pag_num = mysqli_stmt_get_result($stmt_msg);
                    $row_msg = mysqli_fetch_array($result_pag_num);
                    $count = $row_msg ? (int)$row_msg['count'] : 0;
                    mysqli_stmt_close($stmt_msg);
                } else {
                    $count = 0;
                }
                echo '<span class="label label-yellow">' . $count . '</span>';
            } else {
                echo '<span class="label label-yellow">0</span>';
            }
            ?>
          </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 top-dropdown-menu" aria-labelledby="myEgyptmart">
            <li><a href="my-dashboard.php" class="top-dropdown-link" title="My Dashboard">منطقة تحكم المستخدم</a></li>
            <li><a href="my-enquiries.php" class="top-dropdown-link" title="My Inbox">صندوق رسائلى</a></li>
            <li><a href="favorite.php" class="top-dropdown-link" title="My Favorites">منتجـاتى المفضلـة</a></li>
           <li><a href="image-gallery.php" class="top-dropdown-link" title="Image Gallery">جاليـــــرى صــــــور منتجــاتى</a
            <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
            <li><a href="logout.php" class="top-dropdown-link" title="Sign Out">تـسجيــل خــروج</a></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
    </div>
    
    <div class="top-mid">
      <ul>
<?php if (isset($uid) && getUserInfo((int)$uid, 'usr_mp_id') < 4) { ?>
        <!-- محتوى مخفي -->
        <?php } ?>
        
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '' && !empty($cid)) { ?>
      <!---<li><a href="company/index.php?c=<?php echo htmlspecialchars($cid, ENT_QUOTES, 'UTF-8'); ?>" class="txt-yellow" title="My B2B Website">معروضاتى</a></li>--->
        <?php } ?>
      </ul>
      
      <span class="top-actions">
        <li class="dropdown dropdown1">
          <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="Buy">إشترى <i class="fa fa-chevron-down"></i></a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="buy">
            <li><a href="post-buy-req.php" class="top-dropdown-link" title="Post Your Buy Requirement">سجل طلبات شراء</a></li>
            <li><a href="search_adv.php" class="top-dropdown-link" title="Search Product & Suppliers">إبحث عن منتجات وخدمات</a></li>
            <li><a href="manage-selloffer-alert.php" class="top-dropdown-link" title="Manage Sale Notifications">سجل اشعارات فرص بيع</a></li>
            <li><a href="post-tender.php" class="top-dropdown-link" title="Post Tenders FREE">أنشر مناقصات مجانا</a></li>
          </ul>
        </li>
        
        <li class="dropdown dropdown1" id="sell">
          <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="Sell">بيـع <i class="fa fa-chevron-down"></i></a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="sell">
          
          <?php 
$uid_test = $_SESSION['uid_indm'];
$sql_test = "SELECT usr_mp_id FROM user WHERE usr_id = '$uid_test'";
$res_test = mysqli_query($con, $sql_test);
$row_test = mysqli_fetch_assoc($res_test);
$mp_id = $row_test['usr_mp_id'];

if ($mp_id == 3) {
    $add_link = 'membership_plans.php';
} else {
    $add_link = 'product-sel-cat.php';
}
?>


          <li><a href="<?php echo $add_link; ?>" class="top-dropdown-link" title="Display Products / Services">إعرض منتجات وخدمات</a></li>
          
            
            <li><a href="membership_plans.php" class="top-dropdown-link" title="Create B2B Website">إنشىء صفحات أعمالك</a></li>
            <li><a href="buyleads.php" class="top-dropdown-link" title="Latest Buy Requests">أحدث طلبات الشراء</a></li>
            <li><a href="post-sell-offer.php" class="top-dropdown-link" title="Post Sale Offers">أنشر عروض بيع خاصة</a></li>
            <li><a href="manage-buylead-alert.php" class="top-dropdown-link" title="Manage Buy Notifications">سجل منتجات إشعارات شراء</a></li>
            <li><a href="post-auction.php" class="top-dropdown-link" title="Post Auctions FREE">أنشر مزايدات مجانا</a></li>
          </ul>
        </li>
      </span>
      
      <a class="trial-link" href="">نسخة تجريبية</a>
      
      <li class="dropdown dropdown1 top-menu-item" title="عملى على - سوق مصر على الإنترنت">
        <a data-target="myEgyptmart" class="dropbtn1" href="" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
          <b class="txt-yellow top-menu-label">ARAB EXPORT</b> <i class="fa fa-chevron-down"></i>
        </a>
        <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 top-dropdown-menu" aria-labelledby="myEgyptmart">
          <li><a href="http://arab-mart.com" class="top-dropdown-link" title="My Dashboard">Arab-MART سوق العرب</a></li>
        </ul>
      </li>
    </div>
    
    <div class="top-rht">
      <ul class="text-right tstleft">
        <li>
          <a href="why_egyptmart.php" class="txt-yellow">
            <b class="txt-yellow top-menu-label">فوائـد الإشتراك</b>
          </a>
        </li>
        <li>
          <a href="help.php" class="top-nav-link" title="How It Works ?">كيف تعمل المنصة ؟</a>
        </li>
      </ul>
    </div>
  </div>
</div>