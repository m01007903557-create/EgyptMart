<?php
// الاتصال بقاعدة البيانات (عدل المسار حسب الحاجة)
require_once __DIR__ . '/../../lib/connect.php';

$uid_indm = $_SESSION['uid_indm'] ?? 0;
$sql_usr = "select * from user, business_profile where usr_id= '$uid_indm' and bnsprof_uid='$uid_indm'";
$res_usr = mysqli_query($con, $sql_usr);
if ($res_usr && mysqli_num_rows($res_usr) > 0) {
    $row_usr = mysqli_fetch_object($res_usr);
} else {
    $row_usr = null;
}

// باقي متغيرات الصفحة
$business_type = $business_type ?? [];
$prod = $prod ?? [];
$banner = $banner ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Company Profile</title>
    <!-- باقي روابط CSS و JS -->
</head>
<body>
<header>
    <div class="headertop-custom-box">
        <div class="headertop-custom-box-left">
            <img alt="map" src="/images/page-header-col1_mapbg.jpg" class="globeimg1">
        </div>
        <!-- باقي محتوى الهيدر -->
    </div>
</header>

<!-- قسم عرض النصوص والمنتجات -->
<div class="company-info">
    <?php
    // عرض أنواع الأعمال
    $size = sizeof($business_type);
    foreach($business_type as $index=>$btp) {
        if($index < $size - 1) {
            echo '<span>' . htmlspecialchars($btp) . ' - </span>';
        } else {
            echo htmlspecialchars($btp);
        }
    }
    echo '</p>';
    
    // عرض قائمة المنتجات
    $size = sizeof($prod);
    $pi = 1;
    foreach($prod as $index=>$pro) {
        if($pi == 25) {
            echo '</p><p style="display:none;padding-left:60px; padding-bottom:5px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;" id="id1">';
        }
        if($index < $size - 1) {
            echo '<span>' . htmlspecialchars($pro) . ' , </span>';
        } else {
            echo htmlspecialchars($pro);
        }
        $pi++;
    }
    echo '</p>';
    
    if($pi > 25) {
        echo '<p style="padding-left:60px; padding-bottom:5px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;">
            <span onclick="showMore(this)" style="padding:3px; cursor:pointer; font-size:12px">
                <i class="fa fa-plus"></i>&nbsp;view more..
            </span>
        </p>';
    }
    ?>
</div>
</body>
</html>