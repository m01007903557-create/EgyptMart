<div class="container top-bar" id="topbar">
  <div class="row">
    <div class="col-sm-12 col-lg-4 top-lft">
      <ul>
        <?php $cid;
		if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
            $uid = $_SESSION['uid_indm'];
			$sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join user u on sip.mp_id = u.usr_mp_id where u.usr_id = ".$uid;
			$get_icon = mysql_query($sql_icon) or die(mysql_error());
			$sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$uid."' and status = '1'";
			$res=mysqli_query($con, $sql);
			$row=mysqli_fetch_object($res);
			$cid=rand(1000,9999).md5($row->bnsprof_id);
			?>
        <li><span class="pp1"><span
            class="tlc">Welcome: </span><?php echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname');
			if($row->bnsprof_compname !=''){
			?> <span>
          <?php if(mysql_num_rows($get_icon) > 0){
				if(get_membership_expired()!=true){
				$title = 'Junior';
				$icon = mysql_fetch_array($get_icon);
				
				if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
				$title = 'Senior';
				}
				else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
				$title = 'Sponsor';
				}
				
																	?>
          <a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;"
                                                                                              alt=""/></a>
          <?php } }
			?>
          </span>
          <?php } ?>
          </span> </li>
        <?php } else { ?>
        <li><a href="sign-in.php#loginform" target="_top" rel="nofollow">Sign in</a></li>
        |
        <li><a href="create_account.php#signupform" target="_top" rel="nofollow">Join Free &nbsp;|</a></li>
        <?php } ?>
        <li class="dropdown dropdown1"  style="z-index: 100;"> <a data-target="myARABYOS"  class="dropbtn1" href="" data-toggle="dropdown" role="button"
               aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">M<span class="s-small">Y</span> <?php echo getWebSiteName(); ?></b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> |</span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/>
          <?php
            if(isset($_SESSION['uid_indm'])){
            $query_pag_num = "SELECT count(*) AS count from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1'"; // Total records
            $result_pag_num = mysqli_query($con, $query_pag_num);
            $row = mysqli_fetch_array( $result_pag_num);
            $count = $row['count'];
            echo '<span class="label label-yellow">'.$count.'</span>';
          }
          else{
            echo '<span class="label label-yellow">0</span>';
          }
            ?>
          </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myARABYOS" style="width:101%; z-index: -1;">
            <li><a href="my-dashboard.php">My Dashboard</a></li>
            <li><a href="my-enquiries.php">My Inbox</a></li>
            <!--<li><a href="compare.php">Compare</a></li>-->
            <li><a href="favorite.php">My Favorites</a></li>
            <li><a href="image-gallery.php">Image Gallery</a></li>
            <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
            <li><a href="logout.php">Sign Out</a></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
    </div>
    <div class="col-sm-6 col-lg-4 top-mid">
      <ul>
        <?php if(getUserInfo($uid, 'usr_mp_id') < 4){ ?>
       <!-- <li style="color: orange; padding-left:3px;" > Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px; color: orange"> <b style="color: white"><?php echo (getUserInfo($uid, 'usr_credit') > 0)?getUserInfo($uid, 'usr_credit'):'0'; ?></b></a> </li>
        <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>-->
        <?php }   ?>
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
		<li><a href="company/index.php?c=<?php echo $cid; ?>" class="txt-yellow" style=" color:#fff450;font-weight:700;">My B2B Website</a></li>		
		
		<li class="contact_photooo"><a style="margin-left: 25px;" href="<?php echo BASE_URL ?>my-contactdetails.php">
			<?php if(user_info($uid,'image')!=""){ ?>
			<img  src="<?php echo 'data:image/jpg;base64,'.base64_encode( getUserInfo($uid,'profileImage'));?>"  width="30" id="profilephoto" height="30"> <?php echo getUserInfo($uid, 'fname'); ?>
			<?php } else { ?>
			<img src="https://ARABYOS.com/images/uploadd.png"  width="30" id="profilephoto" height="30">	<?php echo getUserInfo($uid, 'fname'); ?>
			<?php } ?>
			</a>
		</li>
		<?php } ?>
         
         <li class="dropdown dropdown1"> <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Buy <i class="fa fa-chevron-down"></i> </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="buy">
            <li><a href="post-buy-req.php">Post Your Buy Requirement</a></li>
            <li><a href="search_adv.php">Search Product &amp; Suppliers</a></li>
            <li><a href="manage-selloffer-alert.php">Manage Sale Notifications</a></li>
            <li><a href="post-tender.php">Post Tenders FREE</a></li>
          </ul>
        </li>
      <li class="dropdown dropdown1" id="sell"> <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown"
               role="button" aria-haspopup="true" aria-expanded="false"
               > Sell <i class="fa fa-chevron-down"></i> </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur " aria-labelledby="sell">
            <li><a href="product-add.php">Display Products / Services</a></li>
            <li><a href="create-free-website.php">Create B2B Website</a></li>
            <li><a href="buyleads.php">Latest Buy Requests </a></li>
            <li><a href="http://arabyos.com/post-sell-offer.php">Post Sale Offers</a></li>
            <li><a href="manage-buylead-alert.php">Manage Buy Notifications</a></li>
            <li><a href="post-tender.php">Post Auctions FREE </a></li>
          </ul>
        </li>
      </span>
<a class="header-language-switcher" href="https://egyptmart.online">عربى</a>
      </ul>
    </div>
    <div class="col-sm-6 col-lg-4 top-rht">
      <ul class="text-right tstleft">
      
         <!--<li style="padding-right:3px;"><a href="http://ARABYOS.com/company/products.php?c=3654fa3a3c407f82377f55c19c5d403335c7&amp;sc=179742556">Member</a></li>-->
        <li><a href="contact_us.php" style="color:orange ">WhatsApp:<b style="font-weight:900; color: white"> <span
            class="txt-yellow"></span><?php echo get_page_settings(21); ?></b></a></li>
        <li><a href="why_ARABYOS.php" class=" txt-yellow"><b class="txt-yellow"  style="font-weight:900;">Why  ARABYOS</b></a> </li>
        <li style=""><a href="help.php">How it works?</a></li>
      </ul>
    </div>
  </div>
</div>