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

<div class="f1 p2b p14 add_b" style="width:80%"><!--div class="bc f11">Enquiries &raquo; </div-->
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

                <div class="ab2 ab3 ab7"><a href="javascript:back_to_list(<?php echo $page; ?>)" class="ab0 ab6 bnr f1">Back</a></div>
                <div class="ab8">
                    <span>Member Profile</span>
                    <h2><strong></strong> <?php echo $row->name_prefix." ".$row->fname." ".$row->lname; ?></h2>
                    <p>
		    <?php echo $row->bnsprof_compname; ?>,
		    <?php echo get_country_name($row->country); ?></p><br>
                    <p><span>(Member since:  <?php echo date("M d Y",strtotime($row->bnsprof_creation_date)); ?>)</span></p><br>
                    <div class="ab0 abem bnr"><?php echo $row->email; ?></div>
                    <div class="ab0 mobile bnr mt12"><?php echo $row->country_ph_code; ?>-<?php echo $row->mobile1; ?></div>
                    <div style="padding-top:5px;"><input class="adr f11 fw" id="rev_submit" value="Block" name="rev_submit" onclick="blockUser('<?php echo $_SESSION['uid_indm']; ?>','<?php echo $row->usr_id; ?>')" type="button"></div>
                </div>
                <div class="c3"></div>
                </div>

		<div class="lc1 f1">
                <div class="lc2">Last Contacted : <span><?php echo date("d M Y",strtotime($row_m->msg_date)); ?></span></div>
                <div class="ab8">
                <table border="0" cellpadding="3" cellspacing="0" width="100%"><tbody>
                <tr>
             
<script type="text/javascript">
function giveRating(r)
{
	$("#rr_rating").val(r);
	for (var i=1;i<=r;i++)
	{
		$("#rating"+i).removeClass("starDactive").addClass("starActive");
	}
	for(;i<=5;i++)
	{
		$("#rating"+i).removeClass("starActive").addClass("starDactive");
	}
}
function update_review()
{

	var id=$("#rr_id").val();
	var r=$("#rr_rating").val();
	var rv=$("#rr_review").val();
	if(rv=='')
	{
		alert('Please write your remarks first');
		$("#rr_review").focus();
	}
	else
	{
		$.post("ajax-file/addReview.php",{id:id,r:r,rv:rv},    function(data){
			alert('Remarks Posted Successfully');
			$("#rev_submit").val("UPDATE");
		});
	}
}
</script>
                	<td width="29%"><span>Rank:</span></td>
                    <td width="71%">
                    <ul id="ulmy_text2" class="starRating webwidget_rating_simple">
                    <?php
						$sql_rr="select * from review_rating where rr_from_usr='".$_SESSION['uid_indm']."' and rr_to_usr='".$row->usr_id."'";
						$res_rr=mysqli_query($con, $sql_rr);
						$row_rr=mysqli_fetch_object($res_rr);
						$activeStar=$row_rr->rr_rating;
						$rr_review=$row_rr->rr_review;
					?>
                    
                    <?php for($star=1;$star<=$activeStar;$star++){ ?>
                    	<li class="starActive" id="rating<?php echo $star; ?>" onClick="giveRating('<?php echo $star; ?>')"><span><?php echo $star; ?></span></li>
                    <?php } ?>
                    <?php for(;$star<=5;$star++){ ?>
                        <li class="starDactive" id="rating<?php echo $star; ?>" onClick="giveRating('<?php echo $star; ?>')"><span><?php echo $star; ?></span></li>                    <?php } ?>
<!--                        <li class="starActive" id="rating2" onClick="giveRating('2')"><span>2</span></li>
                        <li class="starActive" id="rating3" onClick="giveRating('3')"><span>3</span></li>
                        <li class="starDactive" id="rating4" onClick="giveRating('4')"><span>4</span></li>
                        <li class="starDactive" id="rating5" onClick="giveRating('5')"><span>5</span></li>-->
                    </ul>
                    <input type="hidden" id="rr_id" name="rr_id" value="<?php echo $row_rr->rr_id; ?>"/>
                    <input type="hidden" id="rr_rating" name="rr_rating" value="<?php echo $activeStar; ?>"/>
                    <div style="clear:both;display:none"></div>
                    </td>
                </tr></tbody></table>
                <table border="0" cellpadding="3" cellspacing="0" width="100%"><tbody>
                <tr><td width="29%"><span>Remarks:</span></td><td width="71%">You can add remarks here</td></tr></tbody></table>
		<table border="0" cellpadding="3" cellspacing="0" width="100%"><tbody><tr><td width="29%">&nbsp;</td>
        <td width="71%">
        <textarea class="mu11" style="resize:none;" rows="5" cols="17" maxlength="1000" id="rr_review" name="rr_review"><?php echo $rr_review; ?></textarea>
        </td>
        </tr><tr><td width="29%">&nbsp;</td>
        <td width="71%"><input class="adr f11 fw" id="rev_submit" value="<?php if($rr_review!=''){ ?>UPDATE<?php }else{ ?>ADD<?php } ?>" name="rev_submit" onclick="update_review()" type="button"></td>
        </tr></tbody></table>
                </div>
                
                <!--<div class="mhis ab0 mt5"><a class="mhi ab0 f1 bnr">Message History</a></div>
                <div class="ab0 bnr obox lh"><a href="/cgi/my-showmessage.mp?frm=2&amp;mail=Vzo4NTA4MTU3NzoAlX8AAA==&amp;act=contacts" onclick="return trackLink(this,'contactDetail','Message','9061497');">safsfsf</a><br><span class="f11">Feb 01 2014</span></div>-->
                
                </div><div class="c3"></div></div></span></div>