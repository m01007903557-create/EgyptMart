<?php
ob_start();
session_start();
include "../common.php";

$usr_id=$_POST['uid'];
$msg_id=$_POST['mid'];
$page=$_POST['pg'];

$sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$usr_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_m="select * from message where msg_id='".$msg_id."'";
$res_m=mysqli_query($con, $sql_m);
$row_m=mysqli_fetch_object($res_m);
?>

<div class="f1 p2b p14 add_b"><!--div class="bc f11">Enquiries &raquo; </div-->
        	<!--h1>My Contacts</h1-->
        	<!-- address book listing:start-->
            <div id="dymesg" class="load_contacts" align="center"><div style="display: none; width: 15%;" class="c2_m2 bo_m2 lh_m2" id="loading">
		<img class="loading_m2" src="images/my2-loading.gif">&nbsp;Loading...&nbsp;
		</div></div>
			<span class="pagenav" id="pagenav">
			<span class="f1"><h1>My Contacts</h1></span>
			
			<div class="c3"></div>	
		<div class="mt12 ab1" id="addprof" style="display: block;"><!--member profile:start-->
                <div class="ab3w f1">

                <div class="ab2 ab3 ab7" style="border-right: 1px solid #B0D4EE;"><a href="javascript:back_to_list(<?php echo $page; ?>)" class="ab0 ab6 bnr f1">Back</a></div>
                <div class="ab8" style="border-right: 1px solid #B0D4EE;">
                    <span>Member Profile</span>
                    <h2><strong></strong> <?php echo $row->name_prefix." ".$row->fname." ".$row->lname; ?></h2>
                    <p>
		    <?php echo $row->bnsprof_compname; ?>,
		    <?php echo get_country_name($row->country); ?></p><br>
                    <p><span>(Member since:  <?php echo date("M d Y",strtotime($row->bnsprof_creation_date)); ?>)</span></p><br>
                    <div class="ab0 abem bnr"><?php echo $row->email; ?></div>
                    <div class="ab0 mobile bnr mt12"><?php echo $row->country_ph_code; ?>-<?php echo $row->mobile1; ?></div>
                    <div style="padding-top:5px;"></div>
                </div>
                <div class="c3"></div>
                </div>

		<div class="lc1 f1" style="border-left:none;">
                <div class="lc2">Last Contacted : <span><?php echo date("d M Y",strtotime($row_m->msg_date)); ?></span></div>
                <div class="ab8">
                
                
		<table border="0" cellpadding="3" cellspacing="0" width="100%"><tbody><tr><td width="29%">&nbsp;</td>
        <td width="71%"><input class="adr f11 fw" id="rev_submit" value="UnBlock" name="rev_submit" onclick="unBlockUser('<?php echo $_SESSION['uid_indm']; ?>','<?php echo $row->usr_id; ?>')" type="button"/></td>
        </tr></tbody></table>
                </div>
                
                <!--<div class="mhis ab0 mt5"><a class="mhi ab0 f1 bnr">Message History</a></div>
                <div class="ab0 bnr obox lh"><a href="/cgi/my-showmessage.mp?frm=2&amp;mail=Vzo4NTA4MTU3NzoAlX8AAA==&amp;act=contacts" onclick="return trackLink(this,'contactDetail','Message','9061497');">safsfsf</a><br><span class="f11">Feb 01 2014</span></div>-->
                
                </div><div class="c3"></div></div></span></div>