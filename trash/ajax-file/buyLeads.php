<?php
ob_start();
session_start();
include "../common.php";


if($_POST['page'])
{
$page = $_POST['page'];
$pc_id=$_POST['id'];

$cur_page = $page;
$page -= 1;
$per_page = 20; // Per page records
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
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
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

/*$sql_bl="select * from buy_requirement,product_category,user,business_profile where br_pc_id=pc_id and br_u_id=usr_id and usr_id=bnsprof_uid and br_approval_status='1' ".$sql_br_ck." and br_status='1' and br_display_status='1' and pc_parent_id in(select distinct pc_id from product_category where pc_parent_id='".$pc_id."') order by br_updated_date desc LIMIT ".$start.", ".$per_page;*/
$sql_bl= "select * from buy_requirement,product_category,user where br_u_id=usr_id and br_pc_id=pc_id and br_approval_status='1' and br_status='1' and br_display_status='1' and pc_parent_id in(select distinct pc_id from product_category where pc_parent_id='".$pc_id."') " . $sql_br_ck . " order by br_updated_date desc LIMIT ".$start.", ".$per_page;

$recObj=mysql_query($sql_bl) or die('MySql Error' . mysql_error());

/* -----Total count--- */
/*$query_pag_num = "SELECT count(*) AS count from buy_requirement,product_category,user,business_profile where br_pc_id=pc_id and br_u_id=usr_id and usr_id=bnsprof_uid and br_approval_status='1' ".$sql_br_ck." and br_status='1' and br_display_status='1' and pc_parent_id in(select distinct pc_id from product_category where pc_parent_id='".$pc_id."')"; // Total records*/
$query_pag_num = "select count(*) AS count from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand()";


$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_array($result_pag_num);
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
<div class="xx fl on" id="aaa">
<?php
	$row_pc=mysql_fetch_object(mysql_query("select * from product_category where pc_id='".$pc_id."'"));
	
?>
	<p class="cnb" style="font-size:15px;font-weight:700"><b class="cnr" style="font-size:15px;"><?php echo $row_pc->pc_name; ?></b></p>
    
    <div class="sl1">
    	<ul class="lst">
        <?php

		while($row_br=mysql_fetch_object($recObj)){
		?>
        	<li><a style="font-size:15px;font-weight:700" href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($row_br->br_id); ?>"><?php echo ucwords($row_br->br_pd_name); ?></a> <span class="vlogo g10 bo1 d2" onMouseOver="show('tp0');" onMouseOut="hide('tp0');">Verified &amp; Updated</span><span id="tp0" class="off"></span>
	<p class="p1 lnh lsdc"><?php echo $row_br->br_requirement; ?></p>
    
	<!--<p class="p1"><span class="ltu flr"> Updated: <?php /*echo date("d M,Y",strtotime($row_br->br_updated_date));*/ ?></span>
    <span class="c7">Location:</span> -->
    <?php if($row_br->br_preferred_supplier_location != ''){
    	
		if($row_br->br_preferred_supplier_location=='any')
		{
			?>
            <p class="p1"><span class="c7">Location:</span> <?php	echo get_country_name($row_br->country);	?>
            &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
            </p>
            <p class="p1">
            <span class="ltu flr"> Updated: <?php echo date("d M,Y",strtotime($row_br->br_updated_date)); ?></span>
            <span class="c7">Trade Scope:</span> (Foreign & Domestic)</p>
            <?php
		}
	    else if($row_br->br_preferred_supplier_location=='abroad')
		{
			?>
            <p class="p1"><span class="c7">Location:</span> <?php	echo get_country_name($row_br->country);	?>
            &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
            </p>
            <p class="p1">
            <span class="ltu flr"> Updated: <?php echo date("d M,Y",strtotime($row_br->br_updated_date)); ?></span>
            <span class="c7">Trade Scope:</span> (Foreign Only)</p>
            <?php
		}
		else if($row_br->br_preferred_supplier_location=='domestic')
		{	
			?>
            <p class="p1">
		    <span class="c7">Location:</span> <?php	echo get_country_name($row_br->country);	?>
            
			&nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
            
            </p>
            <p class="p1">
            <span class="ltu flr"> Updated: <?php echo date("d M,Y",strtotime($row_br->br_updated_date)); ?></span>
            <span class="c7">Trade Scope:</span> (Domestic Only)</p>
	<?php	}
		else if($row_br->br_preferred_supplier_location=='my_city' && $row_br->bnsprof_city!='0')
		{
			?>
            <p class="p1">
		    <span class="c7">Location:</span> <?php		echo get_city_name($row_br->bnsprof_city);		?>
            
            
            </p>
            <p class="p1">
            <span class="ltu flr"> Updated: <?php echo date("d M,Y",strtotime($row_br->br_updated_date)); ?></span>
            <span class="c7">Trade Scope:</span> (250 KM)</p>
			<?php
		}
		
    }else{	
		?>
            <p class="p1"><span class="ltu flr"> Updated: <?php echo date("d M,Y",strtotime($row_br->br_updated_date)); ?></span>
		    <span class="c7">Location:</span> 
            <?php
    	echo get_country_name($row_br->country);
		?>
        &nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row_br->country); ?>" alt="" class="w4" align="top" height="15" width="23">
          </p>
        <?php
     } ?>
    <!--</p>-->
			<p class="c3"></p>
			</li>
		<?php	}	?>
            
            </ul>
	<?php if($count>0){ ?>
    <p class="cl"><br></p>
	<p align="center" style="margin-bottom:10px;">
        <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showLead('1','<?php echo $pc_id; ?>')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showLead('<?php echo $pre; ?>','<?php echo $pc_id; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showLead('<?php echo $nex; ?>','<?php echo $pc_id; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showLead('<?php echo $no_of_paginations; ?>','<?php echo $pc_id; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
	</p>
	<?php } else { ?>
		<p class="cl" style="text-align: center;"><img src="/images/search_icon_man.png" width="100px" height="100px"><br></p>
		<p align="center" style="margin-bottom:10px;font-size: 20px;font-weight: 600;">
        No Leads under this category.
    	</p>
    <?php } ?>
			</div>
	</div>
<?php } ?>