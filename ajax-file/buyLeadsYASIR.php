<?php
ob_start();
session_start();
include "../common.php";

if(isset($_POST['page'])) {
    $page = (int)$_POST['page'];
    $pc_id = (int)$_POST['id'];
    $keywords = isset($_POST['keywords']) ? trim(str_replace(array('+', '%20'), ' ', (string)$_POST['keywords'])) : '';
    $keyword_filter = '';
    if ($keywords !== '') {
        $keyword_parts = preg_split('/\s+/', $keywords);
        $keyword_clauses = array();
        foreach ($keyword_parts as $keyword_part) {
            $keyword_part = trim($keyword_part);
            if ($keyword_part === '') {
                continue;
            }
            $safe_keyword = mysqli_real_escape_string($con, $keyword_part);
            $keyword_clauses[] = "(br_pd_name LIKE '%".$safe_keyword."%' OR br_requirement LIKE '%".$safe_keyword."%' OR pc_name LIKE '%".$safe_keyword."%')";
        }
        if (!empty($keyword_clauses)) {
            $keyword_filter = " AND (".implode(' OR ', $keyword_clauses).")";
        }
    }
    $category_filter = $keywords === '' ? "AND pc_parent_id IN (SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = '$pc_id')" : "";
    
    $cur_page = $page;
    $page -= 1;
    $per_page = 20;
    $previous_btn = true;
    $next_btn = true;
    $first_btn = true;
    $last_btn = true;
    $start = $page * $per_page;
    
    if(isset($_COOKIE['loc_id'])) {
        $loc_id = (int)$_COOKIE['loc_id'];
        $sql_br_ck = " AND ((br_preferred_supplier_location='domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country='$loc_id')) 
        OR 
        (br_preferred_supplier_location='any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country='$loc_id'))
        OR
        (br_preferred_supplier_location='my_city' AND br_u_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='$loc_id'))))";
    } else {
        $geo_country = isset($location_geo_country) ? $location_geo_country : 'EG';
        $sql_br_ck = " AND (
        (br_preferred_supplier_location='any')
        OR
        (br_preferred_supplier_location='abroad' AND br_u_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country IN (SELECT cn_id FROM country WHERE cn_code='$geo_country')))
        )";
    }
    
    // استعلام جلب طلبات الشراء
    $sql_bl = "SELECT * FROM buy_requirement, product_category, user 
               WHERE br_u_id = usr_id 
               AND br_pc_id = pc_id 
               AND br_approval_status = '1' 
               AND br_status = '1' 
               AND br_display_status = '1' 
               $category_filter
               $keyword_filter
               $sql_br_ck 
               ORDER BY br_updated_date DESC 
               LIMIT $start, $per_page";
    
    $recObj = mysqli_query($con, $sql_bl) or die('MySql Error: ' . mysqli_error($con));
    
    // حساب العدد الإجمالي
    $query_pag_num = "SELECT COUNT(*) AS count FROM buy_requirement, product_category, user 
                      WHERE br_u_id = usr_id 
                      AND br_pc_id = pc_id
                      AND br_approval_status = '1' 
                      AND br_status = '1' 
                      AND br_display_status = '1' 
                      $category_filter
                      $keyword_filter
                      $sql_br_ck";
    
    $result_pag_num = mysqli_query($con, $query_pag_num);
    $row = mysqli_fetch_array($result_pag_num);
    $count = $row['count'] ?? 0;
    $no_of_paginations = ($per_page > 0) ? ceil($count / $per_page) : 0;
    $pagi_string = "Page " . ($cur_page) . " of " . $no_of_paginations;
    
    // حساب أرقام الصفحات للـ pagination
    if($cur_page >= 7) {
        $start_loop = $cur_page - 3;
        if($no_of_paginations > $cur_page + 3) {
            $end_loop = $cur_page + 3;
        } else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
            $start_loop = max(1, $no_of_paginations - 6);
            $end_loop = $no_of_paginations;
        } else {
            $end_loop = $no_of_paginations;
        }
    } else {
        $start_loop = 1;
        if($no_of_paginations > 7) {
            $end_loop = 7;
        } else {
            $end_loop = $no_of_paginations;
        }
    }
?>
<div class="xx fl on" id="aaa">
<?php
    $cat_query = mysqli_query($con, "SELECT * FROM product_category WHERE pc_id='$pc_id'");
    $row_pc = mysqli_fetch_object($cat_query);
?>
    <p class="cnb" style="font-size:15px;font-weight:700"><b class="cnr" style="font-size:15px;"><?php echo $keywords !== '' ? 'نتائج طلبات شراء: ' . htmlspecialchars($keywords) : htmlspecialchars($row_pc->pc_name ?? ''); ?></b></p>
    
    <div class="sl1">
        <ul class="lst">
        <?php
        if (mysqli_num_rows($recObj) > 0) {
            while($row_br = mysqli_fetch_object($recObj)) {
        ?>
            <li>
                <a style="font-size:15px;font-weight:700" href="buyleads-details.php?id=<?php echo rand(1000,9999) . md5($row_br->br_id); ?>"><?php echo htmlspecialchars(ucwords($row_br->br_pd_name ?? '')); ?></a> 
                <span class="vlogo g10 bo1 d2" onMouseOver="show('tp0');" onMouseOut="hide('tp0');">مشترى حقيقى</span>
                <span id="tp0" class="off"></span>
                <?php $cleanRequirement = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $row_br->br_requirement ?? ''); ?>
                <p class="p1 lnh lsdc"><?php echo nl2br(htmlspecialchars(strip_tags($cleanRequirement))); ?></p>
                
                <?php if(!empty($row_br->br_preferred_supplier_location)) {
                    if($row_br->br_preferred_supplier_location == 'any') { ?>
                        <p class="p1"><span class="c7"></span> <?php echo get_country_name($row_br->country); ?>
                        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23"> : الـبـلــد
                        </p>
                        <p class="p1">
                        <span class="ltu flr"> Updated: <?php echo date("d M,Y", strtotime($row_br->br_updated_date)); ?></span>
                        <span class="c7"></span> (محـلى وتصـديـر) : مكــان التجـارة
                        </p>
                    <?php } 
                    else if($row_br->br_preferred_supplier_location == 'abroad') { ?>
                        <p class="p1"><span class="c7">: الـبـلــد</span> <?php echo get_country_name($row_br->country); ?>
                        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23"> : الـبـلــد
                        </p>
                        <p class="p1">
                        <span class="ltu flr"> Updated: <?php echo date("d M,Y", strtotime($row_br->br_updated_date)); ?></span>
                        <span class="c7"></span> (تصـدير فـقـط) : مكـان التجـارة
                        </p>
                    <?php }
                    else if($row_br->br_preferred_supplier_location == 'domestic') { ?>
                        <p class="p1">
                        <span class="c7">Location:</span> <?php echo get_country_name($row_br->country); ?>
                        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23"> : الـبـلــد
                        </p>
                        <p class="p1">
                        <span class="ltu flr"> Updated: <?php echo date("d M,Y", strtotime($row_br->br_updated_date)); ?></span>
                        <span class="c7"></span> (مـحـلـى فـقـط) : مكـان التجـارة
                        </p>
                    <?php }
                    else if($row_br->br_preferred_supplier_location == 'my_city' && !empty($row_br->bnsprof_city) && $row_br->bnsprof_city != '0') { ?>
                        <p class="p1">
                        <span class="c7">Location:</span> <?php echo get_city_name($row_br->bnsprof_city); ?>
                        </p>
                        <p class="p1">
                        <span class="ltu flr"> Updated: <?php echo date("d M,Y", strtotime($row_br->br_updated_date)); ?></span>
                        <span class="c7"></span> (كيلومتر 250) : مكـان التجـارة
                        </p>
                    <?php }
                } else { ?>
                    <p class="p1">
                        <span class="ltu flr"> Updated: <?php echo date("d M,Y", strtotime($row_br->br_updated_date)); ?></span>
                        <span class="c7">Location:</span> <?php echo get_country_name($row_br->country); ?>
                        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
                    </p>
                <?php } ?>
                <p class="c3"></p>
            </li>
        <?php 
            }
        }
        ?>
        </ul>
        
        <?php if($count > 0) { ?>
        <p class="cl"><br></p>
        <p align="center" style="margin-bottom:10px;">
            <?php
            // زر FIRST
            if ($first_btn && $cur_page > 1) {
                echo '<a href="javascript:showLead(\'1\',\'' . $pc_id . '\')"><img id="firstmail" src="images/firsten.gif" alt="First"></a>';
            } else if ($first_btn) {
                echo '<img id="firstmail" src="images/first.gif" alt="First">';
            }
            echo '&nbsp;';
            
            // زر PREVIOUS
            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                echo '<a href="javascript:showLead(\'' . $pre . '\',\'' . $pc_id . '\')"><img id="prevmail" src="images/prven.gif" alt="Previous"></a>';
            } else if($previous_btn) {
                echo '<img id="prevmail" src="images/prevmail.gif" alt="Previous">';
            }
            echo '&nbsp;';
            
            // زر NEXT
            if($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                echo '<a href="javascript:showLead(\'' . $nex . '\',\'' . $pc_id . '\')"><img id="nextmail" src="images/nxten.gif" alt="Next"></a>';
            } else if ($next_btn) {
                echo '<img id="nextmail" src="images/nextmail.gif" alt="Next">';
            }
            echo '&nbsp;';
            
            // زر LAST
            if ($last_btn && $cur_page < $no_of_paginations) {
                echo '<a href="javascript:showLead(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')"><img id="lastmail" src="images/lastenv.gif" alt="Last"></a>';
            } else if ($last_btn) {
                echo '<img id="lastmail" src="images/last.gif" alt="Last">';
            }
            ?>
        </p>
        <?php } else { ?>
            <p class="cl" style="text-align: center;"><img src="/images/search_icon_man.png" width="100px" height="100px"><br></p>
            <p align="center" style="margin-bottom:10px;font-size:20px;font-weight:600;">
                No Leads under this category.
            </p>
        <?php } ?>
    </div>
</div>
<?php } ?>
