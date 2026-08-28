<div id='cssmenu'>    
    <?php
if (!function_exists('staticAdsBanner')) {
    function staticAdsBanner($position = "") {
        global $con;
        if($position != ''){
            $positionCond = " AND adv_position ='".$position."'";
        } else {
            $positionCond = "";
        }
        
        $sqlban = "SELECT * FROM advertisement WHERE adv_status = '1'".$positionCond." ORDER BY adv_id DESC";
        $rsquery = mysqli_query($con, $sqlban);
        if(mysqli_num_rows($rsquery) > 0) {
            $row = mysqli_fetch_object($rsquery);
            $adv_img = $row->adv_img;
            $logopath = "http://egyptmart.shop/upload/advertisement/".$adv_img;
            $adv_link = $row->adv_link;
            if($position == 'left'){
                $data = getimagesize($logopath);
                $height = 'height="'.$data[1].'"';
            } else {
                $height = '';
            }
            $banner2ret = '<a href="'.$adv_link.'"><img src="'.$logopath.'" width="100%" '.$height.' style="display:block !important;"></a>';
        } else {
            $banner2ret = "";
        }
        return $banner2ret;
    }
}
?>

<style>
    div#saleoffer_slider { height: auto !Important; }
    div#product_slider { height: auto !Important; }
    .lnh1 { padding-left: 10px; border-bottom: #0000001f 1px solid; margin-top: 10px; }
    .lnh1 .cor a { text-decoration: none; color: E84000; margin-left: -13px; text-transform: capitalize; }
    .beellling { margin-left: -36px; }
    .beellling-get { margin-top: -5px; }
    .beellling-key { text-align: left; }
</style>

<script type="text/javascript">
function scatAddDel(id) {
    if($('#scat_'+id).attr('checked')) {
        $.post("ajax-file/addTempSellofferAlertCat.php",{id:id}, function(data){ showList(); });
    } else {
        $.post("ajax-file/delTempSellofferAlertCat.php",{id:id}, function(data){ showList(); });
    }
}
function addAlertCategory() {
    $.post("ajax-file/addSellofferAlertCat.php",{}, function(data){ window.location.reload(); });
}
function delAlertCat(id) {
    if(confirm("Are you sure to delete this Category?")) {
        $.post("ajax-file/delSellofferAlertCat.php",{id:id}, function(data){ window.location.reload(); });
    }
}
</script>

<!------------COUNTRIES & Catagories -------------------->
<?php
// استدعاء ملف الدول والمحافظات (تمت ترقيته مسبقاً)
if (file_exists('index_ls_countries.php')) {
    include_once 'index_ls_countries.php';
}

global $con, $userArrayRow_Type, $userArrayRow_Result, $sql_pd_ck, $sql_br_ck, $sql_tnd_ck, $location_geo_country;

// ============================================
// التصنيفات الفرعية (Subcategories)
// ============================================
if (isset($_GET['rctyp']) && $_GET['rctyp'] != 'Suppliers' && isset($_GET['keywords']) && $_GET['keywords'] != '') {
    if ($_GET['rctyp'] == "Products") {
        ?>
        <div class="col-lg-12 ar-box side-cat-menu">
        <?php
        $keywordsss = isset($_POST['keywords']) ? mysqli_real_escape_string($con, $_POST['keywords']) : '';
        $sqls = "SELECT * FROM `product_category` WHERE `pc_name` LIKE '%" . mysqli_real_escape_string($con, $_GET['keywords']) . "%' and pc_status='1' ORDER BY `pc_id` DESC";
        $ress = mysqli_query($con, $sqls);
        $rows = mysqli_fetch_object($ress);
        
        $catParentId = $rows->pc_parent_id ?? 0;
        $tkeyword = trim($_GET['keywords']);
        
        $iProductCategoryId = mysqli_query($con, "SELECT pd_subcat_id FROM `products` JOIN `business_profile` ON business_profile.bnsprof_uid = products.pd_uid WHERE (`pd_title` LIKE '%" . mysqli_real_escape_string($con, $tkeyword) . "%' OR `bnsprof_compname` LIKE '%" . mysqli_real_escape_string($con, $tkeyword) . "%') and pd_status='1'");
        $iCategoryDetail = mysqli_fetch_object($iProductCategoryId);
        
        $queryNewAlpha = "SELECT * FROM product_category WHERE pc_id='" . ($iCategoryDetail->pd_subcat_id ?? 0) . "' and pc_status='1' ORDER BY `pc_id` DESC";
        $queryResultNewAlpha = mysqli_query($con, $queryNewAlpha);
        $ResultsNewAlpha = mysqli_fetch_object($queryResultNewAlpha);
        ?>
        <section class="ar-flags">
            <form>
            <?php
            $sql_check1 = "SELECT * FROM product_category WHERE pc_parent_id='" . ($ResultsNewAlpha->pc_parent_id ?? 0) . "'";
            $res_check1 = mysqli_query($con, $sql_check1);
            $counter = 0;
            $iiii = 0;
            
         
            while ($data = mysqli_fetch_assoc($res_check1)):
               

                $iiii++;
                $sql_prd = "SELECT * FROM products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
                            WHERE mu_id=pd_unit AND pd_currency=cn_id " . $sql_pd_ck . " 
                            AND business_profile.bnsprof_uid = products.pd_uid 
                            AND plan_member_id.b_id = business_profile.bnsprof_id 
                            AND pd_status='1' 
                            AND smembership_plan.mp_id = plan_member_id.p_id 
                            AND pd_image!='' 
                            AND plan_member_id.expiry_date > " . time() . " 
                            AND pd_subcat_id = '" . $data['pc_id'] . "' 
                            ORDER BY FIELD(p_id,'5','4','3','15')";
                $recObj = mysqli_query($con, $sql_prd);
                $iThisValu = mysqli_num_rows($recObj);
                
                if ($iiii == '1') { echo '<header><span class="h4" style="font-weight:bold">الأصناف الفـرعيــة</span></header>'; }
                $counter++;
                if ($counter == 6) { echo '<div class="collapse" id="categories">'; }
                
                if ($iThisValu > 0):
                ?>
                <div class="checkbox" style="text-align:left;">
                    <span><a href="search.php?keyword_type=&keywords=<?php echo urlencode($_GET['keywords']); ?>&rctyp=Products&idd=<?php echo $data['pc_id']; ?>"><?php echo htmlspecialchars($data['pc_name']); ?></a></span>
                </div>
                <?php
                endif;
            endwhile;
            if ($counter >= 5):
                ?>
                <div class="text-right">
                    <button class="btn btn-link collapsed" style="color: #1736e4;" type="button" data-toggle="collapse" data-target="#categories" aria-expanded="false">+ المزيد</button>
                </div>
                <?php
            endif;
            ?>
            </form>
        </section>
        </div>
        <?php
    } // end Products
} // end if

// ============================================
// إشعارات البيع (Business Alert)
// ============================================
if (isset($_GET['keywords']) && $_GET['keywords'] != '' && isset($_GET['rctyp']) && $_GET['rctyp'] != 'Suppliers') {
    $key = $_GET['keywords'];
    ?>
    <div class="col-lg-12 ar-box text-justify webcast-alert-fix hidden-xs" style="padding-right:23px;" id="business-alert">
        <header><span class="h5 beellling" style="font-size:16px;"><img src="images/bell.png" width="20"/> <b class="txt-orange">إشعـارات بيـع ؟</b></span></header>
        <b class="h5 txt-purple txt-bold beellling-get" style="display:block;text-align=right;">تلقى إشعارات<br>: فى بريدك لــ</b>
        
        <?php
        $busss_alert_cat_name = '';
        $sub_cat_id_get = 0;
        
        if ($_GET['rctyp'] == 'buy_lead') {
            $sql_key = "SELECT pc.* FROM buy_requirement br 
                        JOIN product_category pc ON pc.pc_id = br.br_pc_id 
                        WHERE (br.br_pd_name LIKE '%" . mysqli_real_escape_string($con, str_replace("+", " ", $key)) . "%' 
                        OR pc.pc_name LIKE '%" . mysqli_real_escape_string($con, str_replace("+", " ", $key)) . "%') 
                        AND pc.pc_status='1' AND pc.pc_parent_id!='0'";
            $query_key = mysqli_query($con, $sql_key);
            $row_key = mysqli_fetch_object($query_key);
            
            if (mysqli_num_rows($query_key) > 0) {
                $busss_alert_cat_name = $row_key->pc_name;

                

                $sub_cat_id_get = $row_key->pc_id;
            } else {
                $sql_second_query = mysqli_query($con, "SELECT pc.* FROM product_category pc 
                    LEFT OUTER JOIN product_category spc ON pc.pc_id = spc.pc_parent_id 
                    WHERE pc.pc_name LIKE '%" . mysqli_real_escape_string($con, str_replace(array("+", "%20"), array(" ", " "), $key)) . "%' 
                    AND pc.pc_parent_id!='0' and pc.pc_status='1'");
                $fetch_records = mysqli_fetch_object($sql_second_query);
                if (mysqli_num_rows($sql_second_query) > 0) {
                    $sub_cat_id_get = $fetch_records->pc_id;
                    $sql_second_query1 = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id='" . $sub_cat_id_get . "' and pc_status='1'");
                    $fetch_records1 = mysqli_fetch_object($sql_second_query1);
                    if (mysqli_num_rows($sql_second_query1) > 0) {
                        $busss_alert_cat_name = $fetch_records1->pc_name;
                        $sub_cat_id_get = $fetch_records1->pc_id;
                    } else {
                        $busss_alert_cat_name = $fetch_records->pc_name;
                    }
                }
            }
        }
        elseif ($_GET['rctyp'] == 'Products') {
            $sql_key = "SELECT pc.* FROM products p 
                        JOIN product_category pc ON pc.pc_id = p.pd_subcat_id 
                        WHERE p.pd_title LIKE '" . mysqli_real_escape_string($con, $key) . "' AND pc.pc_status='1'";
            $query_key = mysqli_query($con, $sql_key);
            $row_key = mysqli_fetch_object($query_key);
            
            if (mysqli_num_rows($query_key) > 0) {
                $busss_alert_cat_name = $row_key->pc_name;
                $sub_cat_id_get = isset($row_key->pd_subcat_id) ? $row_key->pd_subcat_id : 0;
            } else {
                $sql_second_query = mysqli_query($con, "SELECT pc.* FROM product_category pc 
                    LEFT OUTER JOIN product_category spc ON pc.pc_id = spc.pc_parent_id 
                    WHERE pc.pc_name LIKE '%" . mysqli_real_escape_string($con, str_replace(array("+", "%20"), array(" ", " "), $key)) . "%' 
                    AND pc.pc_parent_id!='0' and pc.pc_status='1'");
                $fetch_records = mysqli_fetch_object($sql_second_query);
                if (mysqli_num_rows($sql_second_query) > 0) {
                    $sub_cat_id_get = $fetch_records->pc_id;
                    $sql_second_query1 = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id='" . $sub_cat_id_get . "' and pc_status='1'");
                    $fetch_records1 = mysqli_fetch_object($sql_second_query1);
                    if (mysqli_num_rows($sql_second_query1) > 0) {
                        $busss_alert_cat_name = $fetch_records1->pc_name;
                        $sub_cat_id_get = $fetch_records1->pc_id;
                    } else {
                        $busss_alert_cat_name = $fetch_records->pc_name;
                    }
                }
            }
        }
        
        if (!empty($busss_alert_cat_name)) {
            ?>
            <p class="h4 txt-orange text-center margin-top-10 beellling-key max">"<?php echo htmlspecialchars($busss_alert_cat_name); ?>"</p>
            <div class="text-center">
                <a href="manage-selloffer-alert.php?val=true&keywords=<?php echo urlencode($key); ?>&rctyp=<?php echo urlencode($_GET['rctyp']); ?>&sub_cat_id=<?php echo $sub_cat_id_get; ?>">
                    <button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">تأكـيد</button>
                </a>
            </div>
            <?php
        }
        ?>
        <?php
        $staticLeftBanner = staticAdsBanner('left');
        echo '<div class="row text-center advertise-divs" style="margin-top:20px; margin-bottom:20px;">';
        echo $staticLeftBanner . '</div>';
        ?>
    </div>
    <?php
}

// ============================================
// منتجات المقارنة (Compare List)
// ============================================
if (isset($_GET['rctyp']) && $_GET['rctyp'] != 'Suppliers' && $_GET['rctyp'] != 'tender' && $_GET['rctyp'] != 'buy_lead') {
    ?>
    <div class="col-lg-12 side_compare_list webcast-alert-fix1" style="padding-right: 0; padding-left: 0; display: none;">
        <h4>منتجــات المقـــارنة</h4>
        <div class="comp-list"></div>
        <a href="compare.php" class="text-center">
            <button type="button" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">قارن</button>
        </a>
    </div>
    <?php
}
if (isset($_GET['page']) && isset($_GET['grid']) && $_GET['grid'] === 'active') {
    
}else{
  echo "</div>";  
}
?>


<?php // if($_GET['grid']=='active' || $_GET['list']=='active' || $_GET['rctyp']=='') { echo '</div>'; } ?>
<?php echo "<!--3 نهاية index_leftsidebar.php -->"; ?>
