<?php
include "includes/header.php";

$flag = $_GET['flag'] ?? '';

// باقي الكود...

if($flag=='whsuccess'){  ?>
<div style="text-align: center; color: green; text-align: center">تم إرسال الطلب بنجاح</div>
<?php }

$uid_indm = $_SESSION['uid_indm'] ?? 0;
$sql_own = "select * from user,business_profile where usr_id='" . $uid_indm . "' and bnsprof_uid=usr_id limit 1";

$res_own = mysqli_query($con,$sql_own);
$row_own = mysqli_fetch_object($res_own);


$class = "grids_list";
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 51;//12;
$start = (($page - 1) * $limit);

$sq1s_totle = "select count(*) as totle from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='1'";
$ress_totle = mysqli_query($con,$sq1s_totle);
$rows_totle = mysqli_fetch_object($ress_totle);
$totalitem = ceil($rows_totle->totle / $limit);


$sq1_totle = "select count(*) as totle from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and (pd_hot='0' OR pd_hot=' ')";
$res_totle = mysqli_query($con,$sq1_totle);
$row_totle = mysqli_fetch_object($res_totle);
$totalitems = ceil($row_totle->totle / $limit);


$prev = ($page > 1) ? $page - 1 : 1;
$next = ($page < $totalitems) ? $page + 1 : 1;

if (isset($_GET['view']) && $_GET['view'] != "") {
    $class = $_GET['view'];
}


if($row->bnsprof_comp_url==''){
    $company='company';
}else{
    $company=$row->bnsprof_comp_url;
}

$_SESSION['last_page'] = "company/products.php?c=" . $c;
$lastpage=$_SESSION['last_page'];


?>
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>

<script type="text/javascript" src="js/jssor.slider.mini.js"></script>
<script src="js/index.js"></script>

<!-- use jssor.slider.debug.js instead for debug -->
<script>
    jQuery(document).ready(function($) {

        var jssor_1_options = {
            $AutoPlay: false,
            $AutoPlaySteps: 1,
            $SlideWidth: 50,
            $SlideSpacing: 3,
            $Cols: 10,
            /*$ArrowNavigatorOptions: {
                $Class: $JssorArrowNavigator$,
                $Steps: 1
            },
            $BulletNavigatorOptions: {
                $Class: $JssorBulletNavigator$,
                $SpacingX: 1,
                $SpacingY: 1
            }*/
        };

        var jssor_1_slider = new $JssorSlider$("jssor_1", jssor_1_options);
        var jssor_2_slider = new $JssorSlider$("jssor_2", jssor_1_options);
		
        //responsive code begin
        //you can remove responsive code if you don't want the slider scales while window resizing
        function ScaleSlider() {
            var refSize = jssor_1_slider.$Elmt.parentNode.clientWidth;
             var refSize = jssor_2_slider.$Elmt.parentNode.clientWidth;
            if (refSize) {
                refSize = Math.min(refSize, 520);
                jssor_1_slider.$ScaleWidth(refSize);
                jssor_2_slider.$ScaleWidth(refSize);
            }
            else {
                window.setTimeout(ScaleSlider, 30);
            }
        }
        ScaleSlider();
        $(window).bind("load", ScaleSlider);
        $(window).bind("resize", ScaleSlider);
        $(window).bind("orientationchange", ScaleSlider);
        //responsive code end
    });
</script>

<style>
.product_number {
    background: #ffffff;
}
    /* jssor slider bullet navigator skin 03 css */
    /*
    .jssorb03 div           (normal)
    .jssorb03 div:hover     (normal mouseover)
    .jssorb03 .av           (active)
    .jssorb03 .av:hover     (active mouseover)
    .jssorb03 .dn           (mousedown)
    */
    .jssorb03 {
        position: absolute;
    }
    .jssorb03 div, .jssorb03 div:hover, .jssorb03 .av {
        position: absolute;
        /* size of bullet elment */
        width: 21px;
        height: 21px;
        text-align: center;
        line-height: 21px;
        color: white;
        font-size: 12px;
        background: url('img/b03.png') no-repeat;
        overflow: hidden;
        cursor: pointer;
    }
    .jssorb03 div { background-position: -5px -4px; }
    .jssorb03 div:hover, .jssorb03 .av:hover { background-position: -35px -4px; }
    .jssorb03 .av { background-position: -65px -4px; }
    .jssorb03 .dn, .jssorb03 .dn:hover { background-position: -95px -4px; }

    /* jssor slider arrow navigator skin 03 css */
    /*
    .jssora03l                  (normal)
    .jssora03r                  (normal)
    .jssora03l:hover            (normal mouseover)
    .jssora03r:hover            (normal mouseover)
    .jssora03l.jssora03ldn      (mousedown)
    .jssora03r.jssora03rdn      (mousedown)
    */
    .jssora03l, .jssora03r {
        display: block;
        position: absolute;
        /* size of arrow element */
        width: 55px;
        height: 55px;
        cursor: pointer;
        background: url('img/a03.png') no-repeat;
        overflow: hidden;
    }
    .jssora03l { background-position: -3px -33px; }
    .jssora03r { background-position: -63px -33px; }
    .jssora03l:hover { background-position: -123px -33px; }
    .jssora03r:hover { background-position: -183px -33px; }
    .jssora03l.jssora03ldn { background-position: -243px -33px; }
    .jssora03r.jssora03rdn { background-position: -303px -33px; }
	
#wideColumn.cust .hot-product .grids_list section {
	height: 400px;
}
.link.pt10px > span > img,
#profile_sub_menu .chat span img {
	display: none;
}
#wideColumn.cust .product_image {
	height: 150px;
}
#wideColumn.cust .product_image a {
	line-height: 140px;
}
.price_div {
	text-align: left;
}
@media (max-width: 768px) {
	.product_title {
		width: 100%;
	}
}
.zk {
    /*border: 1px solid #267abf;*/
    height: auto;
    width: 100px;
    position: absolute;
    left: 25px;
    top: auto !important;
    bottom: 5px;
}
.zk img {
    width: 60px;
    height: auto;
    max-width: 100%;
    margin-top: auto;
    margin-bottom: auto;
    display: table;
}
</style>
<style>
    ul.tabs li.selected a, ul.tabs li.selected a:hover
    {
        position: relative;
        top: 0px;
        font-weight:bold;
        background: white;
        border: 1px solid #B7B7B7;
        border-bottom-color: white;
    }


    ul.tabs li.selected a:hover
    {
        text-decoration: none;
    }
    .hot-product .grids_list section{
        /*border: 1px solid #ddd;*/
        border-radius: 3px;
        box-shadow: 0 0 6px #333;
        float: left;
        margin: 10px 5px;
        padding: 10px 5px;
        width: 30%;
    }
</style>
<style>
    /*.itemr:hover {      
        -webkit-transform: scale(1.05);
        -moz-transform: scale(1.05);
        -ms-transform: scale(1.05);
        -o-transform: scale(1.05);
        transform: scale(1.05);    
    }*/
</style>

<style>
    #fake{
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0px;
    background: rgba(0, 0, 0, 0.65);
    display: none;
    top: 0px;
    }
	
</style>
<style>
@media(max-width:700px){
    .product_title {
        width:calc(100% - 200px);
    }
}
  <!--Om Css -->
  
  .product_list .product_image{
	  width:150px!important;
	  height: 150px!important;
	  position: relative;
	  margin-right: 10px;
	  float: left;
	}
  .product_list .product_title
  {
	  float:none ! important;
  }
  .product_list .product_image
  {
	  margin-right:32px ! important;
  }
  
    <!--Om Css End-->
 .om_cart
 {
	position: absolute;
    right: 10px;
    top: 28px;
	float:none !important;
 }

/*webcast*/
#jssor_1 {
    height: 82px !important;
}
#jssor_2 {
    top: -32px !important;
    height: 85px !important;
}
.selected_list_div {
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
.webcast-fixed {
    position: fixed !important;
    top: 0px !important;
    z-index: 99;
    /*background-color: white;*/
    left: 262px;
    background-color: transparent;
}
</style>








<script src="js/prefixfree.min.js"></script>

<!--loaders -->
<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500">
<link type="text/css" rel="stylesheet" href="../company/loader/waitMe.css">


<div id="body" class='containerBlock'>
<!--<div id="fake"></div>-->
    <ul class="cb">

        <li id="wideColumn" class="cust">		
            

            <div id="breadcrumb">
                <ul>
                    <li><a href="<?php echo '/company/index.php?c=' . $c;?>">Home</a><b>>></b></li>
                    <li>مـنتجـات</li>
                </ul>
            </div><br>

            <div> 

                <ul class="b fo ac-fl acac-db acac-p10px15px acac-btlr5px acac-btrr5px ac-mr5px large tabs" data-persist="true">

                    <li><a href="#view1" id="hide" class="lightbg2 gbiwt bdrr bdr bdrb0 gray" style="outline:none; cursor: pointer;">المنتجات الهامة </a></li>

                    <li class="selected"><a href="#view2" id="show" class="lightbg2 gbiwb cd bdr bdrb0 mt1px"  style="outline:none;">المنتجات العادية</a></li>
                    <li style="float: right;    display: inline-block;">
                        <div class="view_list_design" style=""><span style="font-size: 13px;"title="View As">العرض شبكة أو قائمة</span>&nbsp;&nbsp;
                            <a href="<?php echo BASE_URL . '/company/products.php?c=' . $c . '&view=product_list' . '&page=' . $page; ?>">
                                <i class="fa fa-list"></i></a>&nbsp;&nbsp;&nbsp;
                            <a href="<?php echo BASE_URL . '/company/products.php?c=' . $c . '&view=grids_list' . '&page=' . $page; ?>"><i class="fa fa-th-large"></i></a>
                        </div>
                    </li>

                </ul>


                <div class="tabcontents">
                    <div>
                        <p class="bgccc pt1px h0" style="margin-top:-1px;"></p>	
                        <ul class="bdrt0 ">
                            <li class="grids_list">
                                <div class="product_top_div_first">
                                    <div class="top_text_first" style="font-weight:100 !important;"title="click to select products and contact the supplier or send wholesale inquiry."> إختـار المنتجـات التى تهتم بها وتواصل مباشـرة مع الشركـة الموردة لها الآن
                                        <span><i class="fa fa-plus"></i></span>  	
                                    </div>


                                </div>

                                <style>
                                    .select_list{
                                        top: 10px !important;
                                        right: 10px !important;
                                        /*width: 100px !important;*/
                                        width: 50px !important;
                                        height: 50px !important;
                                    }
                                </style>

                                <div id="WebcastFix" style="display: none;">
                                <div id="jssor_1" class="selected_list_div shopping-cart" style="position: relative; margin: 0 auto; top: 0px; left: 0px; width: 550px; height: 150px; overflow: hidden; visibility: hidden;border-bottom: none;">
                                    <!-- Loading Screen -->
                                    <form>
                                        <div data-u="slides" id="productdiv" style="cursor: default; position: relative; top: 0px; left: 0px; width: 550px; height: 150px; overflow: hidden;">

                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <!-- <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div> -->


                                        </div>
                                        <!-- webcast works start -->
                                        <!-- <div data-u="slides" id="productdiv" style="cursor: default; position: relative; top: 0px; left: 150px; width: 809px; height: 150px; overflow: hidden;">

                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                           

                                        </div> -->
                                        <!-- webcast works end -->
                                        <!-- <div class="last_button_div" style="padding-right: 14px; float:right !important; margin-top: 103px; position:absolute; right:0px;">
                                            <a onclick="checklogin()" href="../<?php echo 'company'; ?>/enquiryRequest.php?c=<?php echo $c; ?>" style="font-size: 23px;"><i class="fa fa-envelope"></i>إ</a>إستفسار أو طلب مجمع
                                        </div> -->
                                        <!-- Bullet Navigator -->
                                        <!-- <div data-u="navigator" class="jssorb03" style="bottom:10px;right:10px; float:left; left:10px;">
                                            
                                            <div data-u="prototype" style="width:21px;height:21px;">
                                                <div data-u="numbertemplate"></div>
                                            </div>
                                        </div> -->
                                        <!-- Arrow Navigator -->
                                        <!-- <span data-u="arrowleft" id="arrowleft" class="jssora03l" style="top:0px;left:8px;width:55px;height:55px;" data-autocenter="2"></span>
                                        <span data-u="arrowright" id="arrowright" class="jssora03r" style="top:0px;right:8px;width:55px;height:55px;" data-autocenter="2"></span> -->
                                    </form>  
                                </div>
<!-- webcast start -->
<div id="jssor_2" class="selected_list_div shopping-cart" style="position: relative; margin: 0 auto; top: 0px; left: 0px; width: 550px; height: 150px; overflow: hidden; visibility: hidden;border-top: none;">
                                    <!-- Loading Screen -->
                                    <form>
                                        <!-- webcast works start -->
                                        <div data-u="slides" id="productdiv" style="cursor: default; position: relative; top: 0px; left: 0; width: 550px; height: 150px; overflow: hidden;">

                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>
                                            <div class="select_list"></div>

                                        </div>
                                        <!-- webcast works end -->
                                        <div class="last_button_div" style="padding-right: 14px; float:right !important; margin-top: 64px; position:absolute; right:16px;">
                                            <a onclick="checklogin()" href="../<?php echo 'company'; ?>/enquiryRequest.php?c=<?php echo $c; ?>" style="font-size:16px;"><i class="fa fa-envelope" style="font-size: 16px;"></i>طلب أصناف مجمعـة</a>
                                        </div>
                                        <!-- Bullet Navigator -->
                                        <!-- <div data-u="navigator" class="jssorb03" style="bottom:10px;right:10px; float:left; left:10px;">
                                            
                                            <div data-u="prototype" style="width:21px;height:21px;">
                                                <div data-u="numbertemplate"></div>
                                            </div>
                                        </div> -->
                                        <!-- Arrow Navigator -->
                                        <!-- <span data-u="arrowleft" id="arrowleft" class="jssora03l" style="top:0px;left:8px;width:55px;height:55px;" data-autocenter="2"></span>
                                        <span data-u="arrowright" id="arrowright" class="jssora03r" style="top:0px;right:8px;width:55px;height:55px;" data-autocenter="2"></span> -->
                                    </form>  
                                </div>
                            </div>
<script>
window.onscroll = function() {myFunction()};

var header = document.getElementById("WebcastFix");
var sticky = header.offsetTop;

function myFunction() {

  if (window.pageYOffset > sticky) {
    header.classList.add("webcast-fixed");
  } else {
    header.classList.remove("webcast-fixed");
  }
}
</script>
                            </li>

                        </ul>


                    </div>

                    <div id="view1" style="position: relative;">
                        <div class="hotproduct">
                        
                        <div class="top_page_list_first" style="position:absolute; top:-175px; right: 0px;font-weight: normal;">
                            <a class="but left"  uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="vertical-align: sub;" ><img src="images/left.png" style="width:10%" /></a>
                            <a class="but right"  uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="vertical-align: sub;" ><img src="images/right.png" style="width:10%" /></a><?php echo $page . " الى " . $totalitem; ?> الصفحـات
                        </div>

                        <ul class="hot-product">
                            <li class="ac-bdrb lc-bbw0 <?php echo $class; ?>">

                                <script src="js/jquery.colorbox.js"></script>
                                <link href="css/colorbox.css" type="text/css" rel="stylesheet">
								  <script>
                                                        $(document).ready(function() {
                                                            //Examples of how to assign the ColorBox event to elements

                                                            $(".ajax1").colorbox();
                                                            $(".inline").colorbox({inline: true, width: "50%"});
                                                            //Example of preserving a JavaScript event for inline calls.
                                                            $("#click").click(function() {
                                                                $('#click').css({"background-color": "#f00", "color": "#fff", "cursor": "inherit"}).text("Open this window again and this message will 		still be here.");
                                                                return false;
                                                            });
                                                        });
                                                    </script>
                                <?php
                                $sql_pd_h = "select * from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='1' LIMIT " . $limit . " OFFSET " . $start . "";
                                $res_pd_h = mysqli_query($con,$sql_pd_h);
                                if (mysqli_num_rows($res_pd_h) > 0) {
                                    $j = 1;
                                    while ($row_pd_h = mysqli_fetch_object($res_pd_h)) {
                                        ?>
                                        <section class="itemr omParentClass">
                                            <div class="shadow items omItems">
                                                <!-- single item -->
                                                <div class="item">
                                                    <div class="product_image omImage">

                                                    <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;">
                                                        <img src="../upload/myproduct/<?php
                                                        if ($row_pd_h->pd_image != '') {

                                                             $imgarr = explode(',',$row_pd_h->pd_image);
                                                            echo $imgarr[0];
                                                        } else {
                                                            echo "noimage.jpg";
                                                        }
                                                        ?>" alt="<?php echo $row_pd_h->pd_title; ?>" class="cu omImg" style="/*height:150px!important;width:150px!important;*/">
                                                        <?php if ($row_pd_h->pd_imagelogo != '') { 
                                                          $logoarr = explode(',',$row_pd_h->pd_imagelogo); ?>
                                        <div class="zk"><img src="../upload/myproduct/<?php echo  $logoarr[0]?>" /></div> 
                                                        <?php  }  ?>
                                                        
                                                
                                                        <li class="wtmp wtmpie omListWrap">
                                                            <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>" class="ajax1" style="cursor:pointer;"><img src="images/zoom.png" style="height: 30px; width:30px; float: right; position: absolute; left:190px; top: 150px;"/>
                                                                <div class="f2 zoom2 mrgzoom"></div>
                                                            </a>
                                                        </li>

                                                    </div>
                                                   <div class="product_right_sec">             
                                                  
                                                    <div class="product_title product_title_2">
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;"><?php echo $row_pd_h->pd_title; ?></a>		
                                                    </div>
                                                     <div class="product_title product_title_1">
                                                       <p> <?php echo substr($row_pd_h->pd_desc, 0, 65) ?>
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:15px;">المزيــد</a>		
                                                    </p></div>
                                                    <button class="add-to-cart omcart om_cart" onclick="addtosupplier(<?php echo $row_pd_h->pd_id; ?>, '<?php
                                                    if ($row->bnsprof_comp_url != '') {
                                                        echo $row->bnsprof_comp_url;
                                                    } else {
                                                        echo "";
                                                    }
                                                    ?>', '<?php
                                                    if ($row_pd_h->pd_image != '') {
                                                        echo $row_pd_h->pd_image;
                                                    } else {
                                                        echo "noimage.jpg";
                                                    }
                                                    ?>');" style="float:right;"><a href="javaScript:void(0);"><i class="fa fa-plus"></i></a></button>

                                                    <div class="product_detail">
                                                        <div class="product_left"></div>

                                                        <div class="price_div">
                                                            <span><?php echo $row_pd_h->pd_fob_price; ?></span><?php echo get_product_detail($row_pd_h->pd_id, 'pd_currency'); ?>
                                                            <div class="unit_div"><span><?php echo $row_pd_h->pd_min_order_qty; ?> </span> <?php echo get_measurement_unit($row_pd_h->pd_unit); ?><span style="font-size:11px; color: #B5BABE;"> (أقــل كـمـية)</span></div>
                                                        </div>



                                                    </div>

                                                    <div class="product_number">
                                                        <span><img src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span> <?php echo $row->country_ph_code; ?>-<?php echo $row->mobile1; ?>
                                                    </div>

                                                    <div class="link pt10px">				
                                                        <script>
                                                        $(document).ready(function() {
                                                           var uid_ind='<?php echo $_SESSION['uid_indm']; ?>';
                                                           
                                                          $("#btn_ajax" +<?php echo $row_pd_h->pd_id; ?>).click(function(){ 

                            if(uid_ind==''){
                            window.location.href="https://www.egyptmart.shop/sign-in.php";
                            }else{
                                          $("#btn_ajax" +<?php echo $row_pd_h->pd_id; ?>).colorbox({width: "62%", height: "89%"}); } });
                                                        });
                                                        </script>
                                                        <span>
                                                            <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id); ?>&pid=<?php echo $row_pd_h->pd_id; ?>&c=<?php echo $c; ?>&vform=1" id="btn_ajax<?php echo $row_pd_h->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but"title="Send Inquiry">تواصل مع الشـركة </a></span>
                                                        <span><img src="<?php echo BASE_URL ?>/company/images/chat_icon.png" width="20"></span>
                                                    </div>


                                                </div>
                                            </div>
                                                    </div>         
                                        </section>            
                                        <?php
                                        $j++;
                                    }
                                }
                                ?>



                            </li>
                        </ul>
                        <div class="top_page_list_first" style="display: block; position:inherit; float:left;width:100%; padding-top: 30px; text-align: center !important;">
                            <a class="but left" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">السابق</a>
                            <?php for ($i = 1; $i <= $totalitem; $i++) : ?>
                                <a class="but" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $i; ?>" uri-page="<?php echo $i;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 17px; font-family: serif;"><span style="margin: 2px 5px 4px 5px; font-weight: 200 !important;"><?php print_r($i); ?></span></a>
                            <?php endfor; ?>
                             
                             <a class="but right" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">التالى</a>

                        </div> 
                        </div>
                    </div>

                    <div id="view2" style="position: relative;">
                        <div class="otherproduct">
                        
                        <div class="top_page_list_first" style="position:absolute; top:-175px;right: 0px;font-weight: normal; margin-top: 160px;">
                            <a class="buts left" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/left.png" style="width:10%" /></a>
                            <a class="buts right" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/right.png" style="width:10%" /></a><?php echo $page . " الى  " . $totalitems; ?> الصفحات من 
                        </div>

                        <ul class="hot-product">
                            <li class="ac-bdrb lc-bbw0 <?php echo $class; ?>">

                                <script src="js/jquery.colorbox.js"></script>
                                <link href="css/colorbox.css" type="text/css" rel="stylesheet">
                                <?php
                             //   $sql_pd = "select * from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='0'  LIMIT " . $limit . " OFFSET " . $start . "";
							 $sql_pd = "select * from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and (pd_hot='0' OR pd_hot=' ')  LIMIT " . $limit . " OFFSET " . $start . "";
								//echo $sql_pd;
                                $res_pd = mysqli_query($con,$sql_pd);
                                if (mysqli_num_rows($res_pd) > 0) {
                                    $j = 1;
                                    while ($row_pd = mysqli_fetch_object($res_pd)) {
                                        ?>
                                        <section class="itemr omParentClass">
                                            <div class="shadow items omItems">
                                                <!-- single item -->
                                                <div class="item omItems">
                                                    <div class="product_image omImage">
                                                     <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;">
                                                        <img src="../upload/myproduct/<?php
                                                        if ($row_pd->pd_image != '') {
                                                            $imgarr = explode(',',$row_pd->pd_image);
                                                            echo $imgarr[0];
                                                        } else {
                                                            echo "noimage.jpg";
                                                        }
                                                        ?>" alt="<?php echo $row_pd->pd_title; ?>" class="cu omImg" >
                                                
                                                        <?php  if ($row_pd->pd_imagelogo != '') { 
                                                          $logoarr = explode(',',$row_pd->pd_imagelogo); ?>
                                        <div class="zk"><img src="../upload/myproduct/<?php echo  $logoarr[0]?>"/></div> 
                                                        <?php  } ?>
                                                        </a>
                                                        <li class="wtmp wtmpie omListWrap">
                                                            <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" class="ajax1" style="cursor:pointer;"><img src="images/zoom.png" style="height: 30px; width: 30px; float: right; position: absolute; left: 183px; top: 100px;"/>
                                                                <div class="f2 zoom2 mrgzoom"></div> 
                                                            </a>
                                                        </li>

                                                    </div>


                                                    <div class="product_title product_title_2" >
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;"><?php echo $row_pd->pd_title; ?></a>		
                                                    </div>
                                                    <div class="product_title product_title_2">
                                                       <p> <?php echo substr($row_pd->pd_desc, 0, 65) ?>
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:15px;"><small>المـزيــد</small></a>		
                                                    </p></div>
                                                    <button class="add-to-cart omcart om_cart" onclick="addtosupplier(<?php echo $row_pd->pd_id; ?>, '<?php
                                                    if ($row->bnsprof_comp_url != '') {
                                                        echo $row->bnsprof_comp_url;
                                                    } else {
                                                        echo "";
                                                    }
                                                    ?>', '<?php
                                                    if ($row_pd->pd_image != '') {
														$imgarr = explode(',',$row_pd->pd_image);
														echo $imgarr[0];
                                                       // echo $row_pd->pd_image;
                                                    } else {
                                                        echo "noimage.jpg";
                                                    }
                                                    ?>');" style="float:right;"><a href="javaScript:void(0);"><i class="fa fa-plus"></i></a></button>

                                                    <div class="product_detail">
                                                        <div class="product_left"></div>

                                                        <div class="price_div">
                                                            <span><?php echo $row_pd->pd_fob_price; ?></span><?php echo get_product_detail($row_pd->pd_id, 'pd_currency'); ?>
                                                            <div class="unit_div"><span><?php echo $row_pd->pd_min_order_qty; ?> </span> <?php echo get_measurement_unit($row_pd->pd_unit); ?><span style="font-size:11px; color: #B5BABE;"> (أقــل كـمـية)</span></div>
                                                        </div>



                                                    </div>
<?php 
//webcast country code task
$Countryphone = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM `country` where cn_id = " . $row->country)); ?>
                                                    <div class="product_number">
                                                         <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                                                        <a href="tel:<?php echo $row->country_ph_code; ?><?php echo $row->mobile1; ?>"><span><img src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span> <?php //echo $row->country_ph_code; 
                                                        echo $Countryphone['cn_ph']; ?>-<?php echo $row->mobile1; ?></a>
                                                         <?php
                                                   }else{
                                                       ?>
                                                     <img style="width: 20px;" src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span><a href="https://egyptmart.shop/sign-in.php#loginform">إظهر الرقم
                                                           </a>
                                                        
                                                       <?php
                                                   }
                                                            ?>
                                                        
                                                    </div>

                                                    <div class="link pt10px">				
                                                        <script>
                                                        $(document).ready(function() {
                                                          var uid_ind='<?php echo $_SESSION['uid_indm']; ?>';
                                                          $("#btn_ajax" +<?php echo $row_pd->pd_id; ?>).click(function(){ 

                                                                if(uid_ind==''){
                                                                window.location.href="https://www.egyptmart.shop/sign-in.php";
                                                                }else{
                                                            $("#btn_ajax" +<?php echo $row_pd->pd_id; ?>).colorbox({width: "62%", height: "89%"}); } });
                                                        });
                                                        </script>
                                                        <span>
                                                            <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id); ?>&pid=<?php echo $row_pd->pd_id; ?>&c=<?php echo $c; ?>&vform=1" id="btn_ajax<?php echo $row_pd->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but"title="Send Inquiry">تواصـل مع الشـركة</a></span>
                                                        <span><img src="<?php echo BASE_URL ?>/company/images/chat_icon.png" width="20"></span>
                                                    </div>


                                                </div>
                                            </div>

                                        </section>            
                                        <?php
                                        $j++;
                                    }
                                }
                                ?>



                            </li>
                        </ul>
                        <div class="top_page_list_first" style="display: block; position: inherit; float:left; width:100%; padding-top: 30px; text-align: center !important;">
                            <!--a class="buts left" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">السابق</a>
                            <?php for ($i = 1; $i <= $totalitems; $i++) : ?>
                                <a class="buts" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $i; ?>" uri-page="<?php echo $i;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px; font-family: serif;"><span style="margin: 2px 5px 4px 5px; font-weight: 200 !important;"><?php print_r($i); ?></span></a>
                            <?php endfor; ?>
                             
                            <a class="buts right" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">التالى</a-->
                        </div>


<!--link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css"/-->
  <style>


	
	
	
.pagination {
    display: inline-block;
	font-size: 14px;
}

.pagination a {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
    transition: background-color .3s;
    border: 1px solid #ddd;
}

.pagination a.active {
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
}

.pagination a:hover:not(.active) {background-color: #ddd;}

  .pagination ul {
    display: inline-block;
    padding: 0;
    margin: 0;
  }
  .pagination li, .pagination input {
    display: inline;
  }
  .pagination li a, .pagination li span {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
  }
  .pagination li.active a {
    background-color: blue;
    color: white;
  }
  .pagination li a:hover:not(.active) {
    background-color: #ddd;
  }
  
.product_left:empty {
	display: none;
}
.item.omItems {
	text-align: center;
}
.grids_list .product_title {
	text-align: left;
}
.top_page_list_first {
	top: -183px !important
}
.product_top_div_first {
    padding-bottom: 0;
}
.product_number span {
	margin-right: 0px;
	vertical-align: sub;
}
.product_number span img {
	width: 20px;
	height: 20px;
}
  </style>
<script>
function goToPage() {
	$('#goToPageGo').attr('uri-id', '<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page='; ?>'+$('#goToPageNum').val());
	$('#goToPageGo').attr('uri-page', $('#goToPageNum').val());
}
</script>
<nav>
<div class="text-center" style="text-align:center">
<ul class="pagination">
<?php
// http://www.phpfreaks.com/tutorial/basic-pagination


$numrows = $row_totle->totle;

// number of rows to show per page
$rowsperpage = $limit;//$xml_atts['totalResultsReturned']
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);

if ($totalpages > 1) {

// get the current page or set a default
if (isset($page) && is_numeric($page)) {
   // cast var as int
   $currentpage = (int) $page;
} else {
   // default page num
   $currentpage = 1;
} // end if

// if current page is greater than total pages...
if ($currentpage > $totalpages) {
   // set current page to last page
   $currentpage = $totalpages;
} // end if
// if current page is less than first page...
if ($currentpage < 1) {
   // set current page to first page
   $currentpage = 1;
} // end if

// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;


/******  build the pagination links ******/
// range of num links to show
$range = 3;

$link = "tests.php?c=$c&view=$class&page={P}";
// if not on page 1, don't show back links
if ($currentpage > 1) {
   // show << link to go back to page 1
   //echo ' <li class="page-item"><a class="page-link" href="'.str_replace('{P}',1,$link).'"><<</a></li> ';
   // get previous page num
   $prevpage = $currentpage - 1;
   // show < link to go back to 1 page
   echo ' <li class="page-item"><a class="page-link buts" uri-id="'.str_replace('{P}',$prevpage,$link).'" uri-page="'.$prevpage.'" href="javascript:void(0)"><</a></li> ';

	if ($currentpage - $range >= 2) {
		echo ' <li class="page-item"> <a class="page-link buts" uri-id="'.str_replace('{P}',1,$link).'" uri-page="1" href="javascript:void(0)">1</a></li>';
		if ($currentpage - $range > 2) {
			echo '<li class="disabled"><span>...</span></li>';
		}
	}
} // end if 

// loop to show links to range of pages around current page
for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
   // if it's a valid page number...
   if (($x > 0) && ($x <= $totalpages)) {
      // if we're on current page...
      if ($x == $currentpage) {
         // 'highlight' it but don't make a link
         echo ' <li class="page-item active"><a class="page-link buts" uri-id="'.str_replace('{P}',$x,$link).'" uri-page="'.$x.'" href="javascript:void(0)">'.$x.'</a></li> ';
      // if not current page...
      } else {
         // make it a link
         echo ' <li class="page-item"><a class="page-link buts" uri-id="'.str_replace('{P}',$x,$link).'" uri-page="'.$x.'" href="javascript:void(0)">'.$x.'</a></li> ';
      } // end else
   } // end if 
} // end for

if ($x <= $totalpages) {
	if ($x < $totalpages) {
		echo ' <li class="disabled"><span>...</span></li>';
	}
	echo '<li class="page-item"><a class="page-link buts" uri-id="'.str_replace('{P}',$totalpages,$link).'" uri-page="'.$totalpages.'" href="javascript:void(0)">'.$totalpages.'</a></li> ';
}
                 
// if not on last page, show forward and last page links        
if ($currentpage != $totalpages) {
   // get next page
   $nextpage = $currentpage + 1;
    // echo forward link for next page 
   echo ' <li class="page-item"><a class="page-link buts" uri-id="'.str_replace('{P}',$nextpage,$link).'" uri-page="'.$nextpage.'" href="javascript:void(0)">></a></li> ';
   // echo forward link for lastpage
   //echo " <li class='page-item'><a class='page-link' href='$link&currentpage=$totalpages'>>></a></li> ";
} // end if
/****** end build pagination links ******/
?>
<li class="page-item"><span style="color:black; background:none;border:0;">Go to page <input id="goToPageNum" type="text" value="" onkeyup="goToPage()" style="width: 50px; height:20px ; margin-right:8px; "/><button id="goToPageGo" class="btn btn-xs btn-default border-radius-0 buts" onclick="" style="padding:0 5px 0 5px">Go</button></span></li>
<?php } //if ($totalpages ?>
</ul>
</div>
</nav>        
 
                    </div>
                    </div>
                </div>
<style>
.hot-product .grids_list section{
height:423px;
}
</style>
               
                <?php include "includes/right.php"; ?>			
    </ul>
</div>
<?php include "includes/footer.php"; ?>
            
 <script src="../company/loader/waitMe.js"></script>

            
<!-- <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>-->
<script>
    $(document).ready(function() {
      
       
        $(".but").click(function() {
        var page=$(this).attr('uri-page');
      
            //console.log($(this).attr('uri-id'));
            $.ajax({
                url: $(this).attr('uri-id'),
                type: 'GET',
                dataType: "html",
                data: {
                    page:page
                },
                beforeSend: function() {
                    var current_effect = "roundBounce";
                    run_waitMe(current_effect);
                    function run_waitMe(effect){
                    $('.containerBlock').waitMe({
			effect: effect,
			text: 'Please wait...',
			bg: 'rgba(255,255,255,0.7)',
			color: '#000',
			maxSize: '',
			source: 'img.svg',
			onClose: function() {}
                        });
                    }
                    
                },
                success: function(success) {
                    console.log(success);
                    $('.hotproduct').html(success);
                },
                error: function(error) {

                },
                complete: function(complete) {
                    $('.containerBlock').waitMe('hide');
                }
            });

        });
    });

</script>
<script>
    $(document).ready(function() {
        
        $(".buts").click(function() {
            //console.log($(this).attr('uri-id'));
            $.ajax({
                url: $(this).attr('uri-id'),
                type: 'GET',
                dataType: "html",
                data: {
                },
                beforeSend: function() {
                    var current_effect = "roundBounce";
                    run_waitMe(current_effect);
                    function run_waitMe(effect){
                    $('.containerBlock').waitMe({
			effect: effect,
			text: 'Please wait...',
			bg: 'rgba(255,255,255,0.7)',
			color: '#000',
			maxSize: '',
			source: 'img.svg',
			onClose: function() {}
                        });
                        //$('.waitMe').show();
                    }
                    
                },
                success: function(success) {
                    console.log(success);
                    $('.otherproduct').html(success);
                },
                error: function(error) {

                },
                complete: function(complete) {
                    $('.containerBlock').waitMe('hide');
                }
            });

        });
    });

</script>
<script>
    /* http://www.menucool.com/tabbed-content Free to use. v2013.7.6 */
    (function() {
        var g = function(a) {
            if (a && a.stopPropagation)
                a.stopPropagation();
            else
                window.event.cancelBubble = true;
            var b = a ? a : window.event;
            b.preventDefault && b.preventDefault()
        }, d = function(a, c, b) {
            if (a.addEventListener)
                a.addEventListener(c, b, false);
            else
                a.attachEvent && a.attachEvent("on" + c, b)
        }, a = function(c, a) {
            var b = new RegExp("(^| )" + a + "( |$)");
            return b.test(c.className) ? true : false
        }, j = function(b, c, d) {
            if (!a(b, c))
                if (b.className == "")
                    b.className = c;
                else if (d)
                    b.className = c + " " + b.className;
                else
                    b.className += " " + c
        }, h = function(a, b) {
            var c = new RegExp("(^| )" + b + "( |$)");
            a.className = a.className.replace(c, "$1");
            a.className = a.className.replace(/ $/, "")
        }, e = function() {
            var b = window.location.pathname;
            if (b.indexOf("/") != -1)
                b = b.split("/");
            var a = b[b.length - 1] || "root";
            if (a.indexOf(".") != -1)
                a = a.substring(0, a.indexOf("."));
            if (a > 20)
                a = a.substring(a.length - 19);
            return a
        }, c = "mi" + e(), b = function(b, a) {
            this.g(b, a)
        };
        b.prototype = {h: function() {
                var b = new RegExp(c + this.a + "=(\\d+)"), a = document.cookie.match(b);
                return a ? a[1] : this.i()
            }, i: function() {
                for (var b = 0, c = this.b.length; b < c; b++)
                    if (a(this.b[b].parentNode, "selected"))
                        return b;
                return 0
            }, j: function(b, d) {
                var c = document.getElementById(b.TargetId);
                if (!c)
                    return;
                this.l(c);
                for (var a = 0; a < this.b.length; a++)
                    if (this.b[a] == b) {
                        j(b.parentNode, "selected");
                        d && this.d && this.k(this.a, a)
                    } else
                        h(this.b[a].parentNode, "selected")
            }, k: function(a, b) {
                document.cookie = c + a + "=" + b + "; path=/"
            }, l: function(b) {
                for (var a = 0; a < this.c.length; a++)
                    this.c[a].style.display = this.c[a].id == b.id ? "block" : "none"
            }, m: function() {
                this.c = [];
                for (var c = this, a = 0; a < this.b.length; a++) {
                    var b = document.getElementById(this.b[a].TargetId);
                    if (b) {
                        this.c.push(b);
                        d(this.b[a], "click", function(b) {
                            var a = this;
                            if (a === window)
                                a = window.event.srcElement;
                            c.j(a, 1);
                            g(b);
                            return false
                        })
                    }
                }
            }, g: function(f, h) {
                this.a = h;
                this.b = [];
                for (var e = f.getElementsByTagName("a"), i = /#([^?]+)/, a, b, c = 0; c < e.length; c++) {
                    b = e[c];
                    a = b.getAttribute("href");
                    if (a.indexOf("#") == -1)
                        continue;
                    else {
                        var d = a.match(i);
                        if (d) {
                            a = d[1];
                            b.TargetId = a;
                            this.b.push(b)
                        } else
                            continue
                    }
                }
                var g = f.getAttribute("data-persist") || "";
                this.d = g.toLowerCase() == "true" ? 1 : 0;
                this.m();
                this.n()
            }, n: function() {
                var a = this.d ? parseInt(this.h()) : this.i();
                if (a >= this.b.length)
                    a = 0;
                this.j(this.b[a], 0)
            }};
        var k = [], i = function(e) {
            var b = false;
            function a() {
                if (b)
                    return;
                b = true;
                setTimeout(e, 4)
            }
            if (document.addEventListener)
                document.addEventListener("DOMContentLoaded", a, false);
            else if (document.attachEvent) {
                try {
                    var f = window.frameElement != null
                } catch (g) {
                }
                if (document.documentElement.doScroll && !f) {
                    function c() {
                        if (b)
                            return;
                        try {
                            document.documentElement.doScroll("left");
                            a()
                        } catch (d) {
                            setTimeout(c, 10)
                        }
                    }
                    c()
                }
                document.attachEvent("onreadystatechange", function() {
                    document.readyState === "complete" && a()
                })
            }
            d(window, "load", a)
        }, f = function() {
            for (var d = document.getElementsByTagName("ul"), c = 0, e = d.length; c < e; c++)
                a(d[c], "tabs") && k.push(new b(d[c], c))
        };
        i(f);
        return{}
    })()
</script>
<script>
    $(document).ready(function() {

        $('#v1').hide();
        $('#v2').show();
        $("#hide").click(function() {
            $('#v1').hide();
            $('#v2').show();
        });
        $("#show").click(function() {
            $('#v1').show();
            $('#v2').hide();
        });
    });
</script>

</body></html>
