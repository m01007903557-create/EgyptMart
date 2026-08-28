<?php 
ini_set('max_execution_time', 500000);
error_reporting(E_ALL);
ini_set('display_errors', 0);
?>
<style>
	.tbl li {
		height: 90px;
		width: 90px;
		background-position: 0 -392px;
		position: relative;
		text-align: center;
		line-height: 14px;
		overflow: hidden;
	}

	.bpt {
		width: 780px !important;
		height: 265px;
		padding-left: 11px;
	}

	.adi_bro {
		margin-right: 10px;
	}

		@media(max-width:640px) {
		.post-product-btn {
			margin-left: -53px !important;
		}
		.wd585 {
			font-size: 14px !important;
		}
		.lft1.lfl.fl.col-md-3.col-sm-3.col-xs-12 {
			width: 100% !important;
		}
		.bg.bp1.fl.d1.a6.bo.cate_allign.col-md-3.col-sm-3.col-xs-12 {
			width: 100% !important;
		}
		.q_bt1.w1.fl.w3.sub_menu_bar.col-md-9.col-sm-9.col-xs-12 {
			width: 100% !important;
			}
		}
		.search-show-box-buyleads .buy-leads-main-display {
			display: flex;
			flex-direction: column;
		}
		.search-show-box-buyleads .buy-leads-main-display .sub_menu_bar {
			order: 1;
		}
		.search-show-box-buyleads .buy-leads-main-display #m4t1 {
			order: 2;
		}
		.search-show-box-buyleads .mid-btm-wrapper {
			order: 3;
		}
		.search-show-box-buyleads .buy-leads-promo-block {
			order: 4;
		}
		@media(min-width:769px) {
			.search-show-box-buyleads .mid-btm-wrapper {
				align-items: flex-start;
				display: flex;
				flex-direction: row;
				width: 100%;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl {
				flex: 0 0 145px;
			}
			.search-show-box-buyleads #res {
				box-sizing: border-box;
				flex: 1 1 auto;
				float: left !important;
				width: auto !important;
			}
		}
		@media(max-width:768px) {
			.search-show-box-buyleads .q_hm1 {
				overflow-x: hidden;
			}
			.search-show-box-buyleads .lft1,
			.search-show-box-buyleads .main-content,
			.search-show-box-buyleads .mid-btm-wrapper,
			.search-show-box-buyleads #res {
				box-sizing: border-box;
				float: none !important;
				margin-left: 0 !important;
				margin-right: 0 !important;
				max-width: 100% !important;
				width: 100% !important;
			}
			.search-show-box-buyleads .buy-leads-main-display .sub_menu_bar {
				font-size: 12px;
				line-height: 1.8;
				padding: 8px 10px;
				text-align: center;
				white-space: normal;
			}
			.search-show-box-buyleads .mid-btm-wrapper {
				background: #fff;
				border: 1px solid #dbe3ea;
				border-radius: 8px;
				margin: 10px 0 14px !important;
				order: 2;
				padding: 10px !important;
			}
			.search-show-box-buyleads .mid-btm-wrapper .lbl {
				display: block !important;
				font-size: 16px !important;
				margin: 0 0 10px !important;
				text-align: right;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl {
				display: block !important;
				float: none !important;
				margin: 0 0 12px !important;
				overflow-x: auto;
				width: 100% !important;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl ul {
				display: flex;
				gap: 8px;
				margin: 0;
				padding: 0 0 8px;
				width: max-content;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl li {
				border: 1px solid #dbe3ea;
				border-radius: 8px;
				flex: 0 0 96px;
				height: auto !important;
				min-height: 92px;
				overflow: hidden;
				padding: 6px;
			}
			.search-show-box-buyleads .buy-leads-promo-block {
				height: auto !important;
				margin: 12px 0 !important;
				max-width: 100% !important;
				order: 3;
				overflow: hidden;
				padding: 10px !important;
				width: 100% !important;
			}
			.search-show-box-buyleads .buy-leads-promo-block .bnp1,
			.search-show-box-buyleads .buy-leads-promo-block .bpx {
				box-sizing: border-box;
				background-image: none !important;
				float: none !important;
				display: block !important;
				margin: 8px 0 !important;
				max-width: none !important;
				min-height: 118px;
				padding: 14px 12px !important;
				width: 100% !important;
			}
		}
	</style>
<?php
include "common.php";
include_once "includes/buylead_request_form.php";

if (isset($_COOKIE['loc_id'])) {
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
	<title><?php echo getSiteTitle(); ?></title>
	<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
	<meta name="title" content="<?php echo getSiteTitle(); ?>">
	<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
	<meta name="description" content="<?php echo get_page_settings(3); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="css/eto-index-buy-1.css" rel="STYLESHEET" type="text/css">
	<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
</head>

<body class="search-show-box-buyleads main-warpp">
	<div class="q_hm1">
		<?php include "includes/header_new.php"; ?>
		<style>
			.search-show-box-buyleads .q_hm1 {
				max-width: 1180px;
				margin: 0 auto;
				overflow-x: hidden;
			}
			.search-show-box-buyleads .lft1 {
				box-sizing: border-box;
				width: 22% !important;
			}
			.search-show-box-buyleads .buy-leads-main-display {
				box-sizing: border-box;
				width: 76% !important;
				min-height: 0 !important;
				margin: 0 !important;
				padding: 0 0 0 12px !important;
			}
			.search-show-box-buyleads .mid-btm-wrapper {
				align-items: flex-start;
				display: block !important;
				margin-top: 8px !important;
				padding-top: 0 !important;
				width: 100% !important;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl {
				display: block !important;
				float: none !important;
				margin: 0 0 12px !important;
				overflow-x: auto;
				width: 100% !important;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl ul {
				display: flex !important;
				gap: 8px;
				margin: 0;
				padding: 0 0 8px;
				width: max-content;
			}
			.search-show-box-buyleads .mid-btm-wrapper .tbl li {
				background-color: #fff;
				border: 1px solid #dbe3ea;
				border-radius: 6px;
				box-sizing: border-box;
				flex: 0 0 105px;
				height: 105px !important;
				padding: 6px;
			}
			.search-show-box-buyleads #res {
				box-sizing: border-box;
				float: none !important;
				min-width: 0;
				width: 100% !important;
			}
			.search-show-box-buyleads #res .xx,
			.search-show-box-buyleads #res .sl1,
			.search-show-box-buyleads #res .lst {
				box-sizing: border-box;
				width: 100% !important;
			}
			.search-show-box-buyleads .buy-leads-promo-block {
				width: 100% !important;
				max-width: 100% !important;
				margin: 0 0 8px !important;
			}
			@media (max-width: 768px) {
				.search-show-box-buyleads .q_hm1 {
					max-width: 430px;
					padding: 0 8px;
				}
				.search-show-box-buyleads .lft1,
				.search-show-box-buyleads .buy-leads-main-display,
				.search-show-box-buyleads .mid-btm-wrapper,
				.search-show-box-buyleads #res {
					float: none !important;
					margin-left: 0 !important;
					margin-right: 0 !important;
					max-width: 100% !important;
					padding-left: 0 !important;
					padding-right: 0 !important;
					width: 100% !important;
				}
				.search-show-box-buyleads .buy-leads-main-display {
					display: flex !important;
					flex-direction: column;
				}
				.search-show-box-buyleads .mid-btm-wrapper {
					background: #fff;
					border: 1px solid #dbe3ea;
					border-radius: 6px;
					display: block !important;
					margin-top: 8px !important;
					order: 2;
					padding: 8px !important;
				}
				.search-show-box-buyleads .mid-btm-wrapper .tbl {
					display: block !important;
					margin-bottom: 10px !important;
					overflow-x: auto;
					width: 100% !important;
				}
				.search-show-box-buyleads .mid-btm-wrapper .tbl ul {
					display: flex;
					gap: 8px;
					width: max-content;
				}
				.search-show-box-buyleads .mid-btm-wrapper .tbl li { flex: 0 0 96px; }
				.search-show-box-buyleads .buy-leads-promo-block {
					order: 3;
					background-image: none !important;
					background: #fff !important;
					border: 1px solid #dbe3ea;
					border-radius: 6px;
					box-sizing: border-box;
					height: auto !important;
					margin: 8px 0 !important;
					max-width: 100% !important;
					overflow: visible !important;
					padding: 8px !important;
					width: 100% !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bnp1 {
					align-items: center;
					background: #fff !important;
					background-image: none !important;
					border: 1px solid #ead9c2;
					border-radius: 6px;
					box-sizing: border-box;
					display: flex;
					float: none !important;
					gap: 10px;
					height: auto !important;
					margin: 0 0 10px !important;
					min-height: 132px;
					padding: 10px !important;
					width: 100% !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bnp1 .bpm {
					background-size: auto !important;
					flex: 0 0 72px;
					float: none !important;
					height: 90px !important;
					margin: 0 !important;
					width: 72px !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bnp1 .wd585 {
					box-sizing: border-box;
					float: none !important;
					font-size: 13px !important;
					line-height: 1.45;
					margin: 0 !important;
					padding: 0 !important;
					width: auto !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bnp1 .wd585,
				.search-show-box-buyleads .buy-leads-promo-block .bnp1 .wd585 * {
					max-width: 100% !important;
					white-space: normal !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bpx {
					background: #fff4e3 !important;
					background-image: none !important;
					border: 1px solid #f0b45e;
					border-radius: 6px;
					box-sizing: border-box;
					float: none !important;
					height: auto !important;
					margin: 8px 0 !important;
					min-height: 92px;
					padding: 12px 10px !important;
					text-align: center;
					width: 100% !important;
				}
				.search-show-box-buyleads .buy-leads-promo-block .bpx p,
				.search-show-box-buyleads .buy-leads-promo-block .bpx span {
					box-sizing: border-box;
					display: block;
					line-height: 1.5;
					margin-left: auto;
					margin-right: auto;
					max-width: 100%;
					white-space: normal;
				}
			}
		</style>
		<p class="q_c3"></p>
		<p class="c3"><img alt="" src="images/zero.gif" height="1" width="1"></p>
		
		<div class="lft1 lfl fl col-md-3 col-sm-3 col-xs-12">
			<p class="bg bp1 fl d1 a6 bo cate_allign col-md-3 col-sm-3 col-xs-12"><img class="my-market" alt="" src="css/img/my-market.png"></p>
			<a class="hmbrgr-menu" href="#">
				<span></span>
				<span></span>
				<span></span>
			</a>
			<?php
			if (get_page_settings('25') == 'manual') {
				$sql_order = " ORDER BY pc_order, pc_name";
			} else {
				$sql_order = " ORDER BY pc_name";
			}
			?>
			<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
			<div id='cssmenu' style="width:100% !important;margin:0 !important;padding:0 !important;">
				<div id="showsideleft"></div>
			</div>
		</div>
		
			<div class="main-content mid fl col-md-7 col-sm-7 col-xs-12 buy-leads-main-display">
				<div class="q_bt1 w1 fl w3 sub_menu_bar col-md-9 col-sm-9 col-xs-12">
				<a class="cb2" href="manage-sell-offer.php" rel="nofollow">طلبات شرائى وطلبات بيعى</a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="cb2" href="manage-buy-requirement.php" rel="nofollow">طلبات شرائى</a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="cb2" href="manage-buylead-alert.php" rel="nofollow">تلقى إشعارات طلبات شراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="q_r" href="post-buy-req.php" rel="nofollow"><img src="images/zero.gif" alt="">أنشر طلبات شراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="q_r" href="post-auction.php" rel="nofollow"><img src="images/zero.gif" alt="">أنشر مزايدة</a>
			</div>
			<center><div id="m4t1"></div></center>
			
				<div class="bnbg bpt buy-leads-promo-block">
				<div class="bnp1"><img src="images/zero.gif" class="bnbg bpm fl" alt="Buy Leads" border="1" height="98" width="90">
					<p class="bo esb wd585"> شـراء بأ فـضـل الأســعـار <br>
						<span class="f2 g5 lnh1"></span><span class="j1"> ► ► ► <a href="post-buy-req.php" class="g9 bo f1">► ► ► &lt;&lt; أنشر متطلبات شرائك وتلقى العديد من الأسعار من موردين مختلفين إبـدأ الآن</a></span>
					</p>
				</div>
				<p class="c3"></p>
				<div class="bnbg bpx fl tc">
					<p class="f4 c2">تتلقى عدة تسعييرات</p><br>
					<span class="bnp g9 lnh1">تتلقى تسعيرات معده خصيصا لك<br><strong>من موردين حقيقيين</strong><br>على محمولك وفى بريدك</span>
				</div>
				<div class="bnbg bpx fl tc bnm">
					<p class="f4 c2">نوفر لك كل الموردين</p><br>
					<span class="bnp g9 lnh1"> نوفر لك <strong> أحسن موردين حقيقيين </strong> <br> <strong> بجميع الأسواق </strong> لتوريد <br> متطلبات شرائك</span>
				</div>
				<div class="bnbg bpx fl tc bnm">
					<p class="f4 c2">أكتب لنا ماذا تريد أن تشترى</p><br>
					<span class="bnp g9 lnh1">أكتب <strong>بيانات بسيطه </strong> <br>وإجعلنا نعرف <br>متطلبات شرائك</span>
				</div>
			</div>
			<style>
				@media (max-width: 768px) {
					.search-show-box-buyleads .buy-leads-promo-block {
						box-sizing: border-box !important;
						display: block !important;
						float: none !important;
						width: 100% !important;
					}
					.search-show-box-buyleads .buy-leads-promo-block .bpx,
					.search-show-box-buyleads .buy-leads-promo-block .bnp1 {
						box-sizing: border-box !important;
						display: block !important;
						float: none !important;
						margin-left: 0 !important;
						margin-right: 0 !important;
						max-width: 100% !important;
						width: 100% !important;
					}
				}
			</style>
			<p><br></p>
			
			<script type="text/javascript">
				var buyLeadSearchKeyword = <?php echo json_encode(isset($_GET['keywords']) ? trim(str_replace(array('+', '%20'), ' ', $_GET['keywords'])) : ''); ?>;

				function showLead(page, id) {
					$('ul#sidebarTabs li').removeClass('ho');
					$('#tabbb' + id).addClass('ho');
					$(".xx").removeClass("on").addClass("off");
					$("#aaa" + id).addClass("on").removeClass("off");
					$('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
						$.post("ajax-file/buyLeads.php", { page: page, id: id, keywords: buyLeadSearchKeyword }, function(data, status) {
							$('#res').html(data);
						});
				}

				function showLeadMain(page, id) {
					$('ul#sidebarTabs li').removeClass('ho');
					$('#tabbb' + id).addClass('ho');
					$(".xx").removeClass("on").addClass("off");
					$("#aaa" + id).addClass("on").removeClass("off");
					$('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
						$.post("ajax-file/buyLeads.php", { page: page, id: id, keywords: buyLeadSearchKeyword }, function(data, status) {
							setTimeout(function() { showsidecate(); }, 500);
							$('#res').html(data);
						});
				}
				
				if ($(window).width() < 992) {
					$('#cssmenu').hide();
					$('#cssmenu li.has-sub > a').append('<span class="toggle-icon"><span class="plus">+</span><span class="minus">-</span></span>');
				}
				
				$('.hmbrgr-menu').click(function(ev) {
					ev.preventDefault();
					$('#cssmenu').slideToggle();
				});

				$('#cssmenu li a .toggle-icon').click(function(ev) {
					ev.preventDefault();
					$(this).parent().toggleClass('show-ch');
				});
			</script>
			
			<div class="bx mid-btm-wrapper fl col-md-12">
				<p class="c4 f4 g5 lbl web-nores">Latest Buy Requests</p>
				
				<div class="tbl fl adi_bro col-md-3 col-lg-2 web-nores">
					<ul id="sidebarTabs">
						<?php
						$pc = 0;
						$sql_check1 = "SELECT pc_parent_id FROM buy_requirement, product_category, user 
									   WHERE br_u_id = usr_id 
									   AND br_pc_id = pc_id 
									   AND br_approval_status = '1' 
									   AND br_display_status = '1' 
									   AND product_category.pc_status = '1' 
									   AND br_status = '1' 
									   $sql_br_ck";
						
						$res_check1 = mysqli_query($con, $sql_check1) or die('MySql Error: ' . mysqli_error($con));
						$pc_parent_id_arr = array();
						
						while ($data = mysqli_fetch_array($res_check1)) {
							$pc_parent_id_arr[] = $data['pc_parent_id'];
						}

						if (!empty($pc_parent_id_arr)) {
							$ids = "'" . join("','", $pc_parent_id_arr) . "'";
							$sql_cat = "SELECT * FROM product_category 
									   WHERE pc_id IN (SELECT DISTINCT pc_parent_id FROM product_category WHERE pc_id IN ($ids)) 
									   AND product_category.pc_status = '1'";
							
							$res_cat = mysqli_query($con, $sql_cat) or die('MySql Error: ' . mysqli_error($con));

							$i = 1;
							while ($row_cat = mysqli_fetch_object($res_cat)) {
								if ($i == 1) { $pc = $row_cat->pc_id; }
						?>
								<li onClick="showLead('1','<?php echo $row_cat->pc_id; ?>');" <?php if ($i == 1) { echo 'class="ho"'; } ?> id="tabbb<?php echo $i; ?>">
									<a class="bgf cm1 cp" id="kk1" style="background: url('upload/category/<?php echo $row_cat->pc_image; ?>') no-repeat scroll 0% 0% transparent;"></a><?php echo $row_cat->pc_name; ?>
								</li>
						<?php
								$i++;
							}
						}
						?>
					</ul>
				</div>
				
				<?php
				// إعادة تعريف $sql_br_ck للمنطقة الثانية (للتأكد)
				if (isset($_COOKIE['loc_id'])) {
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
				
				$query_pag_num = "SELECT COUNT(*) AS count FROM buy_requirement, user 
								 WHERE br_u_id = usr_id 
								 AND br_approval_status = '1' 
								 AND br_status = '1' 
								 AND br_display_status = '1' 
								 $sql_br_ck";
				
				$result_pag_num = mysqli_query($con, $query_pag_num);
				$row = mysqli_fetch_array($result_pag_num);
				$count = $row['count'];
				
				if ($count == 0) {
				?>
					<style type="text/css">
						.web-nores { display: none; }
					</style>
				<?php } ?>
				
				<script type="text/javascript">
					$(document).ready(function() {
						showLeadMain(1, <?php echo $pc; ?>);
					});
				</script>
				<div id="res" class="col-md-9 col-lg-10"></div>
			</div>
			<p class="q_c3"><br></p>
		</div>
		
		<div class="mid right-content fl col-md-2 col-sm-2 col-xs-12 hide">
			<div class="c3">
				<?php
				$sql_adv = "SELECT * FROM advertisement WHERE adv_imagewidth='239' AND adv_imageheight='186' AND adv_status='1' ORDER BY RAND() LIMIT 1";
				$res_adv = mysqli_query($con, $sql_adv);
				if (mysqli_num_rows($res_adv) > 0) {
					$row_adv = mysqli_fetch_object($res_adv);
				?>
					<a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="239" height="186" /></a>
				<?php } else { ?>
					<img src="upload/advertisement/239-186-advertisement.png" width="239" height="186" />
				<?php } ?>
			</div>
			<div class="ryt fl col-md-12">
				<?php
				$sql_testi = "SELECT * FROM testimonials WHERE testi_type='buyer' AND testi_status='1' ORDER BY testi_updated_date DESC";
				$res_testi = mysqli_query($con, $sql_testi);
				if (mysqli_num_rows($res_testi) > 0) {
				?>
					<div class="mb1 c3">
						<p class="bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
						<div class="bbx cln">
							<p class="bxh f2 bo">Buyers Speak</p>
							<div style="display: block;" id="d2">
								<?php
								$n = 1;
								while ($row_testi = mysqli_fetch_object($res_testi)) {
									$len = strlen($row_testi->testi_details);
									if ($n > 1) { echo '<br class="c3">'; }
								?>
									<p class="lnh1">
										<img alt="" class="fl pt1 pr1" src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>" height="76" width="76">
										<b class="cor lnh1"><?php echo $row_testi->testi_name; ?>,<br>
											<span class="cb2"><?php echo get_country_name($row_testi->testi_cn_id); ?></span></b><br><?php echo substr($row_testi->testi_details, 0, 120); ?>
									</p>
									<?php if ($len > 120) { ?>
										<p class="c3 pa1 rm tr"><a href="testimonial.php" target="_blank">Read More...</a></p>
									<?php } ?>
								<?php
									$n++;
								} ?>
							</div>
							<p class="c3"></p>
						</div>
						<p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php renderBuyleadRequestForm('buyleads-page-rfq-form'); ?>
		<p class="q_c3"><br><br></p>
	</div>
	<div id="bl_overlay_layer" class="layer" style="display:none">
		<div class="bl_overlay"></div>
	</div>
	
	<?php include 'includes/footer.php'; ?>
	
	<script>
		function showsidecate() {
			$('#showsideleft').html('<img src="http://egyptmart.online/images/horizontal_loading.gif">');
			$.post("showidecate.php", {}, function(data) {
				$('#showsideleft').html(data);
			});
		}
	</script>
</body>
</html>
