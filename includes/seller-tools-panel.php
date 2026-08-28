<?php
// تعريف المتغيرات المفقودة
$usr_mp_id = 0;
$file = '';

if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $uid = (int)$_SESSION['uid_indm'];
    $sql = "SELECT usr_mp_id FROM user WHERE usr_id = '$uid'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $usr_mp_id = (int)$row['usr_mp_id'];
        $_SESSION['usr_mp_id'] = $usr_mp_id;
    }
}

// تعريف $file من المسار الحالي
$currentPath = $_SERVER['SCRIPT_NAME'];
$pos = strrpos($currentPath, '/');
$file = substr($currentPath, $pos + 1);
$dotpos = strrpos($file, '.');
$file = substr($file, 0, $dotpos);
?>
<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
    <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Seller Tools</h3></li>
    
    <li style="border-bottom:none"><h3>Products/Services</h3></li>
    
    <li class="np npnew">
        <?php if($usr_mp_id == 3 || $usr_mp_id == 0){ ?>
            <a href="membership_plans.php">»&nbsp;Add New Products</a>
        <?php }else{ ?>
            <a href="product-add.php">»&nbsp;Add New Products</a>
        <?php } ?>
    </li>    
    <li class="np npnew"><a href="product-list.php" class=" ">»&nbsp;Manage Products</a></li>
    
    <li style="border-bottom: medium none;"><h3>Buy Leads</h3></li>
    <li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;Purchased Buy Leads</a></li>
    
    <li style="border-bottom: medium none;"><h3>Sell Offers</h3></li>
    <li class="np npnew"><a href="post-sell-offer.php">»&nbsp;Post a Sell Offer</a></li>
    <li class="np npnew">
        <a <?php if($file == "manage-sell-offer"){ ?>class="txtcol leftindi"<?php } ?> href="manage-sell-offer.php">»&nbsp;Manage Sell Offer</a>
    </li>
    
    <li style="border-bottom: medium none;"><h3>Image Gallery</h3></li>
    <li class="np npnew">
        <a <?php if($file == "image-gallery"){ ?>class="txtcol leftindi"<?php } ?> href="image-gallery.php">»&nbsp;Show Images</a>
    </li>
    
    <li style="border-bottom: medium none;"><h3>Subscriptions</h3></li>
    <li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
    <li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
    <li class="np npnew"><a href="post-buy-req.php">Post a New Buy Requirement</a></li>
    <li class="np npnew"><a href="my-enquiries.php">Reply Enquiries from Your Website</a></li>
    
    <li class="np npnew"><a href="my-contactdetails.php">Update Contact Details</a></li>
    <li class="np npnew"><a href="business-details.php">Update Business Information</a></li>
</ul>