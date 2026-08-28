<?php
ob_start();
session_start();
include 'common.php';
set_time_limit(600);
$uid=$_SESSION['uid_indm'];

$pc_id=$_GET['c'];



if(isset($_COOKIE['loc_id']))

{

	$sql_pd_ck=" and (

	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 

	or 

	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))

	or

	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";

	/*

	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))

	or

	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))

	*/

}

else

{

	$sql_pd_ck=" and (

	

	(pd_preferred_buyer_location='any')

	or

	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))

	)";

	/*(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))

	or

	or

	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))

	*/

}

$sql_pd_ck="";

//echo print_r(getLocationInfoByIp());

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title><?php echo getSiteTitle(); ?></title>

<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">

<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">

<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="keywords" content="<?php echo get_page_settings(2); ?>">

<meta name="description" content="<?php echo get_page_settings(3); ?>">

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!--added by webcast on 14-05-2019 -->
<style type="text/css">
    @media (width: 1280px) {
        .footer .footer-searchsec {
            max-width: 860px !important;
        }
        .footer .footer-searchsec-right {
            margin-left: 8px !important;
        }
    }
</style>
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript">

var is_sub = false;
function loadProductByCategory(page,id, flag = 0)

{
	//if (flag ) {
		is_sub = flag;
	//}

    $("#header_load").show();
    $("#current_cat").val(id);
    var value='';
    var elements = $("input[name=mst_type]");
    elements.each(function (index,one) {
        if($(this).prop('checked')){
            value += $(this).val()+','
        }
    });
    var min_order = $("#min_order").val();
    var members = value.slice(0,-1);

	$.post("ajax-file/loadProductByCategory.php",{page:page,id:id,mst_type:members,min_order:min_order,is_sub:flag},    function(data){    $('#res_row').html(data);$("#header_load").hide(); });

	//alert ("Category");

}

function loadProductBySubCategory(page,id)

{
    $("#header_load").show();
    $("#current_cat").val(id);
    var value='';
    var elements = $("input[name=mst_type]");
    elements.each(function (index,one) {
        if($(this).prop('checked')){
            value += $(this).val()+','
        }
    });
    var min_order = $("#min_order").val();
    var members = value.slice(0,-1);
	$.post("ajax-file/loadProductBySubCategory.php",{page:page,id:id,mst_type:members,min_order:min_order},    function(data){    $('#res_row').html(data);$("#header_load").hide();  });

	//alert ("SubCategory");

}
//var is_sub = false; //moved higher
var main_cat = '<?php echo addslashes($_GET['c']); ?>';
function refineProductBySubCategory(page,id,flag=false)

{
	if (flag === false) {
		flag = is_sub;
	} else if (flag == -1) {
		is_sub = false;
		flag = false;
	} else {
		is_sub = flag;
	}
    $("#header_load_sub").show();
    $("#current_cat").val(id);
    var value='';
    var elements = $("input[name=mst_type]");
    elements.each(function (index,one) {
        if($(this).prop('checked')){
            value += $(this).val()+','
        }
    });
    var members = value.slice(0,-1);
    
	value='';
    var elements = $("input[name=country_sel]");
    elements.each(function (index,one) {
        if($(this).prop('checked')){
            value += $(this).val()+','
        }
    });
    var countries = value.slice(0,-1);

	value='';
    var elements = $("input[name=state_sel]");
    elements.each(function (index,one) {
        if($(this).prop('checked')){
            value += $(this).val()+','
        }
    });
    var states = value.slice(0,-1);

    var min_order = $("#min_order").val();
	var city = $("input[name=scity]").val();
   // $.LoadingOverlay("show");
    $("#product_slider").html("");
	$.post("ajax-file/refineProductBySubCategory_new.php",{page:page,id:id,mst_type:members,country:countries,state:states,city:city,min_order:min_order,is_sub:flag},    function(data){    
		$('#product_slider').html(data);//$("#header_load_sub").hide();  

		$.post("ajax-file/loadLeftCats.php",{page:page,id:id,mst_type:members,country:countries,state:states,city:city,min_order:min_order,is_sub:flag},    function(data){    
			$('#leftCats').html(data);
			$("#header_load_sub").hide();  

		});
	});

}

</script>



<script type="text/javascript">

$(document).ready(function()

{

	<?php	if(isset($_GET['c'])){	 ?>

		loadProductByCategory(1,'<?php  echo $_GET['c']; ?>');

	<?php	}	?>

	<?php	if(isset($_GET['sc'])){  	?>

		loadProductBySubCategory(1,'<?php  echo $_GET['sc']; ?>');

	<?php	}	?>


});



</script>

<script type="text/javascript" src="js/jquery.als-1.6.js"></script>

<link href="css/im-style-v1.css" rel="stylesheet" type="text/css">

<style>

/*************************************

 * generic styling for ALS elements

 ************************************/


.als-container {position: relative;	width: 100%;	margin: 0px auto;	z-index: 0;	}



.als-viewport {	position: relative;	overflow: hidden;	margin: 0px auto;	}



.als-wrapper {	position: relative;	list-style: none;	}



.als-item {	position: relative;	display: block;	text-align: center;	cursor: pointer;transition: transform .2s; /* Animation */	float: left; width:18.5%;}
/*.als-item >a>img {
    transition: transform .2s; !* Animation *!
}*/
.als-item:hover {
    box-shadow: 0 0 10px;
    transform: scale(1.05);
}
/*.als-item:hover >a>img {
    transform: scale(1.2);
}*/


.als-prev, .als-next {	position: absolute;	cursor: pointer;	clear: both;	}

.als-item a div > span{font-size:15px !important;}

.utext:hover{text-decoration:underline;color: #d81921 !important;}

p.cnt_supplier {

    padding: 5px 0;

    background: #f26a22;

    color: #fff;

    border-radius: 3px;

}

p.cnt-phone {

    padding: 7px 5px;

    background: #eee;

    color: #000;

    /* margin-bottom: 10px; */

    border-radius: 3px;

}
span.cnt-phone-inner img {
	width: 22px !important;
	float: left;
	margin-top: -2px !important;
}

/*************************************



 * specific styling for #demo3



 ************************************/







#product_slider {		margin: 2px auto;	}



#product_slider .als-item {	/*margin: 0px 5px*/;	padding: 4px 0px;	min-height: 150px;	/*min-width: 120px;*/	text-align: justify;/*center;*/	}



#product_slider .als-item img {	/*display: block*/;	width:100%;margin: 0 auto;	vertical-align: middle; /* block disabled and next by webxtor */	max-height:95%; max-width:95%; width: auto; }



#product_slider .als-prev, #product_slider .als-next {	top: 60px;	}



#product_slider .als-prev {	left: 20px;}



#product_slider .als-next {	right: 20px;}


#saleoffer_slider {		margin: 2px auto;	}

#saleoffer_slider .als-item {	margin: 0px 5px;	padding: 4px 0px;	min-height: 140px;	min-width: 120px;	text-align: center;	}

#saleoffer_slider .als-item img {	display: block;	margin: 0 auto;	vertical-align: middle;	}

#saleoffer_slider .als-prev, #saleoffer_slider .als-next {	top: 60px;	}

#saleoffer_slider .als-prev {	left: 20px;}

#saleoffer_slider .als-next {	right: 20px;}
#res_row>#cssmenu.hidden{
    display: none!important;
}
.left-side-bar-sale-offer h4 {
    font-size: 15px;
    font-weight: 600;
}
.post-product-btn {
    font-size: 13px !important;
}
.page-header-col2-intro-texts .post-product-btn small {
    font-size: 8px !important;
}
div#res a { color:#237abf; }
.search-show-box-buyleads.products-categories-listing #res {width: calc(100% - 210px)!important;padding-left: 0;}
div.countries {
    margin-top: 2px;
    background-color: #fff;
    padding: 10px;
}
#showcnt {
    cursor: pointer;
    box-shadow: 0 0 1px;
    padding: 2px;
}
.cnt-phone a {
	font-size:14px;
	color: black;
	font-weight: bold;
	display: inline-block;
}
p.cnt-phone {
	padding-right: 2px;
}
.cnt_supplier {
	text-align: center;
}
.cnt_supplier span {
	color: white;
	font-size: 13px;
}
#img-div {
	text-align: center;
}
.utext {
	height:30px;
}
.togle_style:hover{
	color:#FF751A !important;
}
#getcitydata {
    float: right;
    border: 1px solid #ddd;
    padding: 0px !important;
    width: 100% !important;
    text-align: right;
    margin-right: -43px;
    margin-top: 6px;
}
	#scity {
    width: 87%;
    float: left;
    border: none;
    height: 19px;
    padding-top: 14px;
    padding-bottom: 9px;
}
	.scity_btn {
    font-size: 13px;
    margin-top: 2px;
    margin-right: 3px;
    padding: 1px;
}
.main-warpp #topbar ul {
    min-width: 160px !important;
}	
</style>

<style>
.checkbox-inline+.checkbox-inline {
    margin-top: 0;
    margin-left: 58px !important;
}
.min_quan{
margin-top:8px !important; float:left!important; width: auto!important;margin-top:8px!important;float:left!important;width: auto!important;margin-left: -25px!important;padding-left: 0!important;
}
.cnt-phone a{font-weight:800; font-size:12px;}

.span_red{color:red;font-size:14px !important;font-weight:bold;}

.utext{color:#2b2b2b;font-size: 14px !important;padding: 0px 0px 0px 0px;text-align: center;height: auto;margin-bottom: 6px;
}
.als-item{border:1px solid #ccc;margin-top:1%;margin-left:0%;margin-right:1%;padding:4px !important;margin-bottom:1%;border-radius:4px; float:left; height:auto; background-color:rgb(255, 255, 255);height: 350px;
}

@media only screen and (min-width: 750px) and (max-width: 1024px) {
.search-show-box-buyleads, #final_result {
   width: 100% !important;;
    padding-left: 0;
    padding-right: 0;
}
.checkbox-inline{ margin-left:30px !important;}
}
@media only screen and (max-width: 768px) {
	.search-show-box-buyleads, #final_result {
   width: 100% !important;;
    padding-left: 0;
    padding-right: 0;
}
	.als-item{border:1px solid #ccc;margin-top:1%;margin-left:0%;margin-right:1%;padding:4px !important;margin-bottom:1%;border-radius:4px; float:left; height:auto; background-color:rgba(251, 251, 251, 0.96);height: 350px;
}
	.cnt_supplier_inner{   
	 font-size: 13px !important;
    padding: 0px !important;}
	.utext{color:#2b2b2b;font-size: 13px !important;padding: 0px 0px 0px 0px;text-align: center;height: auto;margin-bottom: 6px;
}
	.span_red{color:red !important;font-size:13px !important;font-weight:bold;}

   .min_quan{
margin-top:8px !important; float:left!important; width: auto!important;margin-top:8px!important;float:left!important;width: auto!important;margin-left: 25px!important;padding-left: 0!important;
}
#getcitydata {
    float: left;
    border: 1px solid #ddd;
    padding: 5px;
    width: 159px !important;
    text-align: right;
}
.scity_btn {
    font-size: 16px!important;
    margin-top: 2px !important;
    margin-right: 3px !important;
    padding: 6px !important;
}
.fa_search {
    font-size: 12px !important;
    padding: 1px !important;
    position: absolute !important;
    right: -22px !important;
    top: 10px !important;
}
   .checkbox-inline{
	margin-left: 22px !important;
	padding-bottom: 8px !important;
	}
	.checkbox-inline+.checkbox-inline {
    margin-top: 0;
    margin-left: 22px !important;
}
.mycol{margin-left: -20px !important;}
span.cnt-phone-inner img {
    width: 16px !important;
    float: left;
    margin-top: 0px !important;
}
.cnt-phone a{font-weight:800; font-size:10px;}
.als-viewport {
    position: relative;
    overflow: hidden;
       /*margin-left: 11px !important;*/
}
.min_order{margin-left: -10px !important;}
.min_order1{margin-left: 0px !important;}
.min_btn{margin-top: -7px !important}
.togle_style{ font-size:18px !important}
}

#product_slider .als-item{ margin:4px !important}
</style>
</head>





<body class="search-show-box-buyleads products-categories-listing">
 

<input type="hidden" id="current_cat" value="">
<div id="fb-root"></div>

<script>(function(d, s, id) {

  var js, fjs = d.getElementsByTagName(s)[0];

  if (d.getElementById(id)) return;

  js = d.createElement(s); js.id = id;

  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";

  fjs.parentNode.insertBefore(js, fjs);

}(document, 'script', 'facebook-jssdk'));</script>

      

<?php	include "includes/header_new.php";	?>

<?php

if(get_page_settings('25')=='manual')

{

	$sql_order=" order by pc_order,pc_name";

}

else

{

	$sql_order=" order by pc_name";

}

?>

<!--<p class="bt cb"><img src="images/z.gif" alt="<?php /*echo getWebSiteName(); */?>" width="1" height="1"></p>-->
<style>
.prc-right-side, #final_result {
	padding-left: 0px !important;
}
</style>

<!--Menu-->
<div id="header_load" style="text-align: center;display: none" ><img src="http://arabyos.com/ripple.gif" style="height: 250px"/></div>
   <div id="res_row" class="maincontainertop clearfix custom_quick_fix">

   </div>


<p class="cb"><br></p>

<?php include 'includes/footer.php'; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="application/javascript">

    $(document).ready(function(){

        $(document).on('click', "#minorder", function () {



            $('div#product_slider').css("opacity","0.5");

            $('#header_load').css({"display": "block", "margin-top": "100px"});

            var minorder=$('#min_order').val();

            var id='<?php  echo $_GET['c']; ?>';



            $.ajax({

                url: "ajax-file/minporder.php",

                method: "POST",

                datatype:"html",

                data: {minorder:minorder,id:id},

                success: function(mr_result)

                {

                    $("#res").html(mr_result);

                    $('#header_load').css("display","none");

                }



            });



        });

        $(document).on('click', "#showcnt", function () {

            $('.countries').toggle();
            $("#showcnt>i").toggleClass('fa-sort-desc fa-sort-asc');
        });

        $(document).on('click', ".cnt_state", function () {



            var id=$(this).attr('id');
			var cid=$("#current_cat").val();//'<?php  echo $_GET['c']; ?>';



            $('.state_section').css({"display": "block"});

            $('.countries_inner').css({"display": "none"});



            $.ajax({

                url: "ajax-file/slectedstate.php",

                method: "POST",

                datatype:"html",

                data: {id:id, cid:cid, is_sub:is_sub},

                success: function(mr_result)

                {

                    $(".state_section").html(mr_result);


					$("input[name=country_sel][value="+id+"]").prop('checked', ''); //unselect because appended state selection block also has country // webxtor
                }



            });

        });

        $(document).on('click', ".close_state", function () {

            $('.state_section').css({"display": "none"});

            $('.countries_inner').css({"display": "block"});

        });

    });

    function toggle_menu() {
		if($('#cssmenu').hasClass('menu-active'))
		{
			$("#downarrow").css('display','inline');
			$("#uparrow").css('display','none');
			} else {
			$("#uparrow").css('display','inline');
			$("#downarrow").css('display','none');
			}
        $("#cssmenu").toggleClass("menu-active");
    }
    function filter_member() {
        refineProductBySubCategory(1,$("#current_cat").val());
    }

</script>
