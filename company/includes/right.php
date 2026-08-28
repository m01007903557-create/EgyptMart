<?php
// ============================================
// التأكد من وجود اتصال قاعدة البيانات $con
// ============================================
if (!isset($con) || $con === null) {
    die("خطأ: اتصال قاعدة البيانات غير موجود");
}
?>

<style>
    .view-more-item.is-hidden {
    display: none;
}

.view-more-btn {
    display: none;
    cursor: pointer;
    padding: 8px 15px;
    font-size: 16px;
    background: #008000 !important;
    color: #fff !important;
    border: 1px solid #267abf !important;
}

.view-more-btn.is-visible {
    display: inline-block;
}
</style>

<li id="thinColumn">
        <section class="box2" title=" Contact Details ">
        <div class="h3"><h3>الاتصـال بالشركـة </h3></div>
        <nav>
        <b>
        <?php 
        if(!empty($row->bnsprof_ceoprefix) && !empty($row->bnsprof_ceofname) && !empty($row->bnsprof_ceolname)) { 
            echo htmlspecialchars($row->bnsprof_ceoprefix . " " . $row->bnsprof_ceofname . " " . $row->bnsprof_ceolname); 
        ?>
(CEO)              
<?php } else { 
    echo htmlspecialchars($row->name_prefix . " " . $row->fname . " " . $row->lname);
} ?> </b>
                    <p class="mb5px mt5px"><BR>
<?php echo htmlspecialchars($row->bnsprof_address1 . ", " . $row->bnsprof_address2); ?>
<BR>
<?php if($row->bnsprof_city != '0'){ echo htmlspecialchars(get_city_name($row->bnsprof_city) . ", "); } ?>
<?php if($row->bnsprof_state != '0'){ echo htmlspecialchars(get_state_name($row->bnsprof_state) . ", "); } ?>
<?php echo htmlspecialchars(get_country_name($row->country)); ?>
<?php
if(!empty($row->bnsprof_ph1)) { ?>
<br>Phone: <?php echo htmlspecialchars($row->country_ph_code); if(!empty($row->bnsprof_phcode1)){ ?>-<?php echo htmlspecialchars($row->bnsprof_phcode1); } ?>-<?php echo htmlspecialchars($row->bnsprof_ph1); ?>
<?php } ?>
<?php if(!empty($row->bnsprof_ph2)) { ?>
<?php if(!empty($row->bnsprof_ph1)){ echo ", "; } echo htmlspecialchars($row->country_ph_code); if(!empty($row->bnsprof_phcode2)){ ?>-<?php echo htmlspecialchars($row->bnsprof_phcode2); } ?>-<?php echo htmlspecialchars($row->bnsprof_ph2); ?>
<?php } ?>
<?php if(!empty($row->bnsprof_ph3)) { ?>
<?php if(!empty($row->bnsprof_ph1) || !empty($row->bnsprof_ph2)){ echo ", "; } echo htmlspecialchars($row->country_ph_code); if(!empty($row->bnsprof_phcode3)){ ?>-<?php echo htmlspecialchars($row->bnsprof_phcode3); } ?>-<?php echo htmlspecialchars($row->bnsprof_ph3); ?>
<?php } ?>
<?php if(!empty($row->bnsprof_ph4)) { ?>
<?php if(!empty($row->bnsprof_ph1) || !empty($row->bnsprof_ph2) || !empty($row->bnsprof_ph3)){ echo ", "; } echo htmlspecialchars($row->country_ph_code); if(!empty($row->bnsprof_phcode4)){ ?>-<?php echo htmlspecialchars($row->bnsprof_phcode4); } ?>-<?php echo htmlspecialchars($row->bnsprof_ph4); ?>
<?php } ?>
<?php if(!empty($row->bnsprof_fax1)) { ?>
<br>Fax: <?php echo htmlspecialchars($row->country_ph_code); ?>-<?php echo htmlspecialchars($row->bnsprof_fax1); ?>
<?php } ?></p>
<p class="read"><a href="enquiry.php?c=<?php echo urlencode($c); ?>"><span class="rA" style="color:blue;" title="More detail">>>  تـفـاصيـل أكثــر</span> </a></p>
        </nav>
        </section><br>
                <section class="box2" title="Hot Products">
        <div class="h3"><h3>المنتجات الهامـة للشركـة </h3></div>
        <div class="pro">
        <ul>            
<?php
// ============================================
// تحديث الاستعلامات لاستخدام mysqli بدلاً من mysql
// مع الحفاظ على نفس متغير الاتصال $con
// ============================================

// المنتجات الهامة (pd_hot = '1')
$sql_pd_right = "SELECT * FROM products WHERE pd_uid = ? AND pd_status = '1' AND pd_hot = '1'";
$stmt_pd_right = mysqli_prepare($con, $sql_pd_right);
if ($stmt_pd_right) {
    mysqli_stmt_bind_param($stmt_pd_right, "i", $row->usr_id);
    mysqli_stmt_execute($stmt_pd_right);
    $res_pd_right = mysqli_stmt_get_result($stmt_pd_right);
    
    if(mysqli_num_rows($res_pd_right) > 0) {    
        while($row_pd_right = mysqli_fetch_object($res_pd_right)) {
            $token = rand(1000,9999) . md5($row_pd_right->pd_id);
            ?>
                    <li><a href="product-details.php?token=<?php echo urlencode($token); ?>&c=<?php echo urlencode($c); ?>" title="<?php echo htmlspecialchars($row_pd_right->pd_title); ?>"><span class="rA" style="color: darkblue;">>></span> <?php echo htmlspecialchars($row_pd_right->pd_title); ?></a></li>
<?php 
        }
    }
    mysqli_stmt_close($stmt_pd_right);
}
?>
    </ul>
        
        </div></section><br>
                <section class="box2" title="Other Products" >
        <div class="h3"><h3>منتجـات الشركــة</h3></div>
        <div class="pro">
        <ul id="normalProductsList">

<?php
// المنتجات العادية (pd_hot = '0')
$sql_pd_right = "SELECT * FROM products WHERE pd_uid = ? AND pd_status = '1' AND pd_hot = '0'";
$stmt_pd_right = mysqli_prepare($con, $sql_pd_right);

if ($stmt_pd_right) {

    mysqli_stmt_bind_param($stmt_pd_right, "i", $row->usr_id);
    mysqli_stmt_execute($stmt_pd_right);
    $res_pd_right = mysqli_stmt_get_result($stmt_pd_right);

    if (mysqli_num_rows($res_pd_right) > 0) {

        while ($row_pd_right = mysqli_fetch_object($res_pd_right)) {

            $token = rand(1000,9999) . md5($row_pd_right->pd_id);
            ?>

            <li class="view-more-item">
                <a href="product-details.php?token=<?php echo urlencode($token); ?>&c=<?php echo urlencode($c); ?>"
                   title="<?php echo htmlspecialchars($row_pd_right->pd_title); ?>">
                    <span class="rA" style="color: darkblue;">>></span>
                    <?php echo htmlspecialchars($row_pd_right->pd_title); ?>
                </a>
            </li>

            <?php
        }
    }

    mysqli_stmt_close($stmt_pd_right);
}
?>

</ul>

<button type="button"
        class="view-more-btn "
        data-list="#normalProductsList"
        data-visible="6">
    + More
</button>
        
        </div></section><br>
                </li>