<?php 
$banner = []; // مصفوفة فارغة (ستعرض الصور الافتراضية)
$file = "profile"; // تأكيد القيمة

include "includes/header.php"; 


// ✅ التأكد من وجود بيانات الشركة
if (!isset($row) || $row === null) {
    // محاولة جلب البيانات من الرابط c
    $c = $_GET['c'] ?? '';
    if (!empty($c)) {
        $id = substr($c, 4);
        $sql = "SELECT * FROM business_profile, user 
                WHERE bnsprof_uid = usr_id 
                AND md5(bnsprof_id) = '$id'";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($res);
    }
}

// ✅ إذا لم يتم العثور على بيانات، اعرض رسالة خطأ
if (!$row || !isset($row->bnsprof_id)) {
    die("بيانات المورد غير متوفرة");
}


// التأكد من وجود $row ومنعه من أن يكون null
if (!$row) {
    $row = new stdClass();
    $row->bnsprof_businesstype = '';
    $row->bnsprof_comemp = '';
    $row->emprange_type = '';
    $row->bnsprof_compname = '';
    $row->bnsprof_complogo = '';
    $row->country = 0;
    $row->usr_id = 0;
    $row->bnsprof_id = 0;
    $row->date = '';
}

$sql_wc = "select * from website_content where wc_usr_id='" . ($row->usr_id ?? 0) . "'";
$res_wc = mysqli_query($con, $sql_wc);
$row_wc = mysqli_fetch_object($res_wc);
?>

<div id="body">
    <ul class="cb wide_thin_col_parent">
        <!-- باقي محتوى الصفحة -->

	<li id="wideColumn">
		<?php if($row_wc->wc_homepage_key_desc != '') : ?>
		<section class="box1">
		
		<div class="h2"><h2>Company Description</h2></div>
		<nav class="comPro">
		
<p><?php echo $row_wc->wc_homepage_key_desc; ?></p>
		
		</nav>
		
</section>
<?php endif; ?>
<br>
			

		</section><br/>


<?php
$bfact = '';
$exta = $row->bnsprof_yoe;
$noe = '';
$lesta = '';
$tern = '';
$regno = $row->bnsprof_regno;
$serTax = $row->bnsprof_svtax_no;

$user_turnover_id = $user_turnover_id ?? 0;
$user_owntype_id = $user_owntype_id ?? 0;
$user_businesstype_id = $user_businesstype_id ?? 0;


$turnoversql = null;
$user_turnover_id = isset($user_turnover_id) ? (int)$user_turnover_id : 0;

if ($user_turnover_id > 0) {
    $turnoversql = mysqli_query($con, "select revturnover_title from revenue_turnover where revturnover_status='1' AND revturnover_id=" . $user_turnover_id);
}

// عند استخدام النتيجة
if ($turnoversql && mysqli_num_rows($turnoversql) > 0) {
    while($turnoverow = mysqli_fetch_object($turnoversql)) {
        // استخدم $turnoverow هنا
    }
} else {
    // لا توجد بيانات - لا تفعل شيئاً
    // $turnoverow = null;
}

$owntypesql=mysqli_query($con,"select * from ownership_type where owntyp_status='1' AND owntyp_id=".$user_owntype_id);



$owntypesql = null;
$user_owntype_id = isset($user_owntype_id) ? (int)$user_owntype_id : 0;

if ($user_owntype_id > 0) {
    $owntypesql = mysqli_query($con, "select * from ownership_type where owntyp_status='1' AND owntyp_id=" . $user_owntype_id);
}

// عند استخدام النتيجة
if ($owntypesql && mysqli_num_rows($owntypesql) > 0) {
    while($owntyperow = mysqli_fetch_object($owntypesql)) {
        // استخدم $owntyperow هنا
    }






	$lesta = $owntyperow->owntyp_title;
}
$sql="select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and bnsprof_owntype=owntyp_id and bnsprof_turnover=revturnover_id and md5(bnsprof_id)='".$id."'";
$res=mysqli_query($con,$sql);
$row=mysqli_fetch_object($res);

$bnsprof_businesstype=array();
$bstyp = ($row && $row->bnsprof_businesstype) ? explode(",", $row->bnsprof_businesstype) : [];
$business_type_ids = ($row && $row->bnsprof_businesstype) ? $row->bnsprof_businesstype : '0';
$sql_btype = "select * from business_type where bsntyp_id IN ($business_type_ids)";
$res_btype=mysqli_query($con,$sql_btype);
if(mysqli_num_rows($res_btype)>0)
{
?>
<?php while($row_btype=mysqli_fetch_object($res_btype)){	
	$arr_bfact[] = $row_btype->bsntyp_title;
}
}


$comp_emp_id = ($row && $row->bnsprof_comemp) ? $row->bnsprof_comemp : 0;
$noempsql = mysqli_query($con, "select * from employee_range where emprange_status='1' AND emprange_id='$comp_emp_id'");
$noemprow = mysqli_fetch_object($noempsql);

$noemprow=mysqli_fetch_object($noempsql);
$noe = ($noemprow && $noemprow->emprange_type) ? $noemprow->emprange_type : '';
?>
<?php
	if(!empty($arr_bfact) || $exta != '' || $noe != '' || $lesta != '' || $tern != '' || $regno != '' || $serTax != '')
	{
		?>
		<section class="box1">
		<div class="h2"><h2>Company Facts</h2></div>
		<nav class="comFact">		
		<?php 
		if(!empty($arr_bfact)) {	
			echo '<p><span>Business Type </span><span>'.implode(",",$arr_bfact).'<span></p>';
		}
		if($exta != '') {	
			echo '<p><span>Establishment </span><span>'.$exta.'<span></p>';
		}
		if($noe != '') {	
			echo '<p><span>No. of Employees </span><span>'.$noe.'<span></p>';
		}
		if($lesta != '') {	
			echo '<p><span>Legal Status </span><span>'.$lesta.'<span></p>';
		}
		if($tern != '') {	
			echo '<p><span>Turnover </span><span>'.$tern.'<span></p>';
		}
				
if(isset($row->bnsprof_designation) && $row->bnsprof_designation != ''){
    
    
if(isset($res_desig) && mysqli_num_rows($res_desig)>0){ 
$row_desig=mysqli_fetch_object($res_desig);
?>
<p><span><?php echo $row_desig->desig_title; ?></span><span><?php echo $row->name_prefix." ".$row->fname." ".$row->lname; ?></span></p>
<?php	}
}
if($regno != '') {	
			echo '<p><span>Registration No. </span><span>'.$regno.'<span></p>';
		}
		if($serTax != '') {	
			echo '<p><span>Service Tax No. </span><span>'.$serTax.'<span></p>';
		}
?>


					</nav>
	    </section>
	<?php } ?>
		
	<br>
		
<br>
<style>
    
.info_table_right img{
    border: 1px solid #bac3fc;
    }
    </style>
   
   <?php
   $abtsql=mysqli_query($con,"select * from about_us,profile_heading_arabyos where abtus_ph_id=ph_id and abtus_wc_id='".$row_wc->wc_id."' ORDER BY abtus_order"); 
   $totalabt=mysqli_num_rows($abtsql);
   if($totalabt>0)
   {	 
		while($abtrow=mysqli_fetch_object($abtsql))
		{
			?>
			
			<section class="box1">
		<div class="h2"><h2><?php echo $abtrow->ph_title; ?> </h2></div>	
                <div class="info_table">
					<div class="info_table_left">
						<?php echo $abtrow->abtus_desc; ?>
					</div>
					<div class="info_table_right" style='border:none;'>
						<?php
						  if($abtrow->abtus_image!="")
						  {
							?>
							
							<img src="/upload/myprofile/<?php echo htmlspecialchars($abtrow->abtus_image ?? ''); ?>" id="img_small_form_1671511" alt="Profile Image">
	                           <!--<img src="<?php echo BASE_URL ?>/upload/myprofile/<?php echo $abtrow->abtus_image; ?>" id="img_small_form_1671511">-->
                       <?php } else { ?>
       
                         <img src="<?php echo BASE_URL ?>/images/noimage.jpg" id="img_small_form_1671511" >
	
        <?php } ?>
					</div>
					
				</div>
		</section>
   <br>
   <?php
		
		}
	}
	
	?>
	
<?php
$sql="select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and md5(bnsprof_id)='".$id."'";
$res=mysqli_query($con,$sql);
$row=mysqli_fetch_object($res);
?>
	</li><?php include "includes/right.php"; ?>
</ul>
	</div>
	<?php include "includes/footer.php"; ?>

	<script>
$(document).on('ready', function() {
    if ($(".center.slider").length > 0) {
        $(".center.slider").slick({
            dots: true,
            infinite: true,
            centerMode: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            autoplay: true,
            arrows: true
        });
    }
});
</script>
</body></html>