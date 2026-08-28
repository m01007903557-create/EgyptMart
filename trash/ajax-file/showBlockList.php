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


$sql="select distinct * from user,blocked_user where 
		(case
		 	when bu_blockBy='".$_SESSION['uid_indm']."' then bu_blocked=usr_id
			when bu_blocked='".$_SESSION['uid_indm']."' then bu_blockBy=usr_id
		 end) order by bu_updated_date desc LIMIT ".$start.", ".$per_page;
// $sql_prev_msg = "select * from message,user where if(msg_to_usr_id='".$_SESSION['uid_fb']."' , msg_from_usr_id=usr_id , msg_to_usr_id=usr_id) and msg_id in(select max(msg_id) from message where (msg_to_usr_id='".$_SESSION['uid_fb']."' or msg_from_usr_id='".$_SESSION['uid_fb']."') ".$excl_ur." group by if(msg_to_usr_id='".$_SESSION['uid_fb']."',msg_from_usr_id,msg_to_usr_id)) order by msg_updated_date desc";

//$sql="select * from user where usr_id in(select distinct usr_id from message,user where if(msg_to='".$_SESSION['uid_indm']."' , msg_from=usr_id , msg_to=usr_id) and msg_id in(select distinct msg_id from message where (msg_to='".$_SESSION['uid_indm']."' or msg_from='".$_SESSION['uid_indm']."')))";

$recObj=mysqli_query($con, $sql) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from user,blocked_user where 
		(case
		 	when bu_blockBy='".$_SESSION['uid_indm']."' then bu_blocked=usr_id
			when bu_blocked='".$_SESSION['uid_indm']."' then bu_blockBy=usr_id
		 end) group by usr_id"; // Total records
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

<div class="f1 p2b p14 add_b" style="width:80%">
	<!--div class="bc f11">Enquiries &raquo; </div-->
    <!--h1>My Contacts</h1-->
    <!-- address book listing:start-->
	<div id="dymesg" class="load_contacts" align="center">
    	<div style="display: none; width: 15%;" class="c2_m2 bo_m2 lh_m2" id="loading"><img class="loading_m2" src="images/my2-loading.gif">&nbsp;Loading...&nbsp;</div>
    </div>

	<span style="display: block;" class="pagenav" id="pagenav">
       	<span class="pagenav" id="pagenav">
			<span class="f1"><h1>My Block List</h1></span>
			<div id="PageNavMaster">
             <?php if($count>0){ ?>
			<div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><?php echo $pagi_string; ?>
            
			 <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showBlockList('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showBlockList('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showBlockList('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showBlockList('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            
            <!-- My PageNavigation end -->
            </div>
            <?php	}	?>
			</div>
			<div class="c3"></div>	
			<div class="mt5 ab1" id="addBook">
            
            <span class="addressbook">
        
		<div class="ab2">
        	<table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
            <tr>
            	<td class="ab3" width="410"><span>Contact Name</span></td>
                <td class="ab3" width="100"><span>Country</span></td>
                <td class="ab3" width="120"><span>Blocked On</span></td>
                <td class="ab3" width="80"><span>&nbsp;</span></td>
			</tr>
            </tbody></table></div>
            <?php	if($count>0){	?>
            <?php 	while($row=mysqli_fetch_object($recObj)){	?>
            <div class="ab4">
            	<table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                	<tr>
                    	<td width="410"><strong><?php echo $row->name_prefix." ".$row->fname." ".$row->lname; ?></strong> (<?php echo $row->email; ?>)<br><span><?php echo $row->bnsprof_compname; ?> &nbsp;</span></td>
                        <td width="100">
							<?php echo get_country_name($row->country); ?><!--<img src="images/gh_flag_s.png" title="Ghana" class="abm5" border="0" height="13" width="20">-->
                        </td>
                        <td width="120"><?php echo date("d M Y",strtotime($row->bu_updated_date)); ?></td>
                    <td width="80"><input class="adr f11 fw" style="cursor:pointer;" type="button" title="UnBlock" value="UnBlock" onclick="unBlockUser('<?php echo $_SESSION['uid_indm']; ?>','<?php echo $row->usr_id; ?>')"/></td>
                    </tr>
                </tbody>
                </table>
			</div>
            <?php	}	?>
            <?php	}else{	?>
            <div class="ab4">
            	<table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                	<tr>
                    <td align="center"><font color="#FF0000">No Blocked Contacts listed.</font></td>
                    </tr>
                </tbody>
                </table>
            </div>
            <?php } ?>
			
        
		</span>
    </div>
    <div class="mt12 ab1" id="addprof" style="display:none"></div>
    </span>
    </span>
</div>
<?php } ?>