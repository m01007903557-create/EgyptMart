<?php
ob_start();
session_start();
include "../common.php";


if($_POST['page'])
{
$page = $_POST['page'];

$cur_page = $page;
$page -= 1;
$per_page = 10; // Per page records
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

if(isset($_COOKIE['loc_id']))
{
	$sql_br_ck=" and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	
		or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."')))
	
	
	)";
	/*
	
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."')))
	
	
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_br_ck=" and (
	
	(br_preferred_supplier_location='any')
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}

$sql_bl="select * from buy_requirement,user,business_profile where br_u_id=usr_id and usr_id=bnsprof_uid and br_approval_status='1' ".$sql_br_ck."  and br_status='1' order by br_updated_date desc LIMIT ".$start.", ".$per_page;

$recObj=mysqli_query($con, $sql_bl) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' ".$sql_br_ck." and br_status='1'"; // Total records

$result_pag_num = mysqli_query($con, $query_pag_num);
$row = mysqli_fetch_array( $result_pag_num);
$count = $row['count'];
$no_of_paginations = ceil($count / $per_page);
$pagi_string="Page ".($page+1)." of ".$no_of_paginations;
/* ---------------Calculating the starting and endign values for the loop----------------------------------- */
if($cur_page >= 7)
{
    $start_loop = $cur_page - 3;
    if($no_of_paginations > $cur_page + 3)
        $end_loop = $cur_page + 3;
    else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6)
    {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
    else
    {
        $end_loop = $no_of_paginations;
    }
}
else
{
    $start_loop = 1;
    if($no_of_paginations > 7)
        $end_loop = 7;
    else
        $end_loop = $no_of_paginations;
}
?>
<div class="bx flr">

<div class="bc1 f3 m12" style="padding-left:5px;"><p class="flr fz2 ttc">Total Buy Leads - <span class="c4 bo"><?php echo $count; ?></span></p>Latest Buy Requests</div>
<p class="flr p1 p2 p3 bg bpr"><!--<a href="#" class="tdn" target="_BLANK">FAQ - Buy Leads</a>--></p>
<p class="c3"></p>
<div class="brl">
	<ul class="lst">
    <?php
	while($row_br=mysqli_fetch_object($recObj)){
	?>
		<li>
        	<a href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($row_br->br_id); ?>" style="font-size:15px;font-weight:700;"><?php echo ucwords($row_br->br_pd_name); ?></a>
            <span class="vlogo g9 bo d1" onMouseOver="show1('tp0');" onMouseOut="hide('tp0');">Verified</span>
            <span id="tp0" class="off"></span>
			<p class="p1 lnh"><?php echo $row_br->br_requirement; ?></p>
			<p class="p1">
<?php if($row_br->br_preferred_supplier_location != ''){
if($row_br->br_preferred_supplier_location=='any')
						{
							?>
<span class="c7">Location:</span> <?php echo get_country_name($row_br->country); ?>
                            &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
			                <span style="float:right"><span class="c7">Trade Scope:</span> (Foreign & Domestic)</span>
                            <?php
						}
				    	else if($row_br->br_preferred_supplier_location=='abroad')
						{
							?>
			                <span class="c7">Location:</span> <?php echo get_country_name($row_br->country); ?>
                            &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
<span style="float:right"><span class="c7">Trade Scope:</span> (Foreign Only)</span>
<?php
						}
else if($row_br->br_preferred_supplier_location=='domestic')
						{	
						?>
<span class="c7">Location:</span> <?php echo get_country_name($row_br->country); ?>
							&nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
                            
                            <span style="float:right"><span class="c7">Trade Scope:</span> (Domestic Only)</span>
				<?php	}
						else if($row_br->br_preferred_supplier_location=='my_city' && $row_br->bnsprof_city!='0')
						{
							?>
                           <span class="c7">Location:</span> <?php echo get_city_name($row_br->bnsprof_city);	?>
                            <span style="float:right"><span class="c7">Trade Scope:</span> (250 KM)</span>
							<?php
						}
				    }else{	
				    	echo get_country_name($row_br->country);
						?>
                        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23"/>
                        <?php
					} ?>
                
            </p>
			<p class="c3"></p>
		</li>
	<?php	}	?>
   </ul>
   <?php if($count>$per_page){ ?>
            <span class="pagenavigation" style="text-align:center">
            
            <div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start -->&nbsp;&nbsp;
            
            <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showBuyleads('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showBuyleads('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showBuyleads('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showBuyleads('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            &nbsp;
            <!-- My PageNavigation end -->
            </div>
            
            </span>
            <?php	}	?>
</div></div>
<?php } ?>