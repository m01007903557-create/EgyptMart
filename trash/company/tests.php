<?php
include "../common.php";
$c = $_GET['c'];
$id = substr($_GET['c'], 4);
$sql = "select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and md5(bnsprof_id)='" . $id . "'";
$res = mysql_query($sql);
$row = mysql_fetch_object($res);



$class = "grids_list";
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 51;//9;
$start = (($page - 1) * $limit);

$sq1s_totle = "select count(*) as totle from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='1'";
$ress_totle = mysql_query($sq1s_totle);
$rows_totle = mysql_fetch_object($ress_totle);
$totalitem = ceil($rows_totle->totle / $limit);


$sq1_totle = "select count(*) as totle from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='0'";
$res_totle = mysql_query($sq1_totle);
$row_totle = mysql_fetch_object($res_totle);
$totalitems = ceil($row_totle->totle / $limit);


$prev = ($page > 1) ? $page - 1 : 1;
$next = ($page < $totalitems) ? $page + 1 : 1;

if (isset($_GET['view']) && $_GET['view'] != "") {
    $class = $_GET['view'];
}
?>
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>

<script type="text/javascript" src="js/jssor.slider.mini.js"></script>
<script src="js/index.js"></script>
<script src="../company/loader/waitMe.js"></script>


<div class="top_page_list_first" style="position:absolute; top: -166px;right: 0px;">
                            <a class="buts left" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)"><img src="images/left.png" style="width:10%" /></a>
                            <a class="buts right" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)"><img src="images/right.png" style="width:10%" /></a><?php echo $page . " of " . $totalitems; ?> pages
                        </div>

                        <ul class="hot-product">
                            <li class="ac-bdrb lc-bbw0 <?php echo $class; ?>">

                                <script src="js/jquery.colorbox.js"></script>
                                <link href="css/colorbox.css" type="text/css" rel="stylesheet">
                                <?php
                                $sql_pd = "select * from products where pd_uid='" . $row->usr_id . "' and pd_status='1' and pd_hot='0' LIMIT " . $limit . " OFFSET " . $start . "";
                                $res_pd = mysql_query($sql_pd);
                                if (mysql_num_rows($res_pd) > 0) {
                                    $j = 1;
                                    while ($row_pd = mysql_fetch_object($res_pd)) {
                                        ?>
                                        <section class="itemr">
                                            <div class="shadow items">
                                                <!-- single item -->
                                                <div class="item">
                                                    <div class="product_image">
                                                    <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;">
                                                        <img src="../upload/myproduct/<?php
                                                        if ($row_pd->pd_image != '') {
                                                            echo $row_pd->pd_image;
                                                        } else {
                                                            echo "noimage.jpg";
                                                        }
                                                        ?>" alt="<?php echo $row_pd->pd_title; ?>" class="cu" style="height:94%;"></a>
                                                        <li class="wtmp wtmpie">
                                                            <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>" class="ajax1" style="cursor:pointer;"><img src="images/zoom.png" style="height: 30px; width: 30px; float: right; position: absolute; left: 153px; top: 125px;"/>
                                                                <div class="f2 zoom2 mrgzoom"></div>
                                                            </a>
                                                        </li>

                                                    </div>
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

                                                    <div class="product_title">
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;"><?php echo $row_pd->pd_title; ?></a>		
                                                    </div>
                                                    <div class="product_title">
                                                       <p> <?php echo substr($row_pd->pd_desc, 0, 65) ?>
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:11px;">more</a>		
                                                    </p></div>
                                                    <button class="add-to-cart" onclick="addtosupplier(<?php echo $row_pd->pd_id; ?>, '<?php
                                                    if ($row->bnsprof_comp_url != '') {
                                                        echo $row->bnsprof_comp_url;
                                                    } else {
                                                        echo "";
                                                    }
                                                    ?>', '<?php
                                                    if ($row_pd->pd_image != '') {
                                                        echo $row_pd->pd_image;
                                                    } else {
                                                        echo "noimage.jpg";
                                                    }
                                                    ?>');" style="float:right;"><a href="javaScript:void(0);"><i class="fa fa-plus"></i></a></button>

                                                    <div class="product_detail">
                                                        <div class="product_left"></div>

                                                        <div class="price_div">
                                                            <span><?php echo $row_pd->pd_fob_price; ?></span><?php echo get_product_detail($row_pd->pd_id, 'pd_currency'); ?>
                                                            <div class="unit_div"><span><?php echo $row_pd->pd_min_order_qty; ?> </span> <?php echo get_measurement_unit($row_pd->pd_unit); ?><span style="font-size:11px; color: #B5BABE;"> (Min Order)</span></div>
                                                        </div>



                                                    </div>

                                                    <div class="product_number">
                                                        <span><img src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span>+20-123654789
                                                    </div>

                                                    <div class="link pt10px">				
                                                        <script>
                                                        $(document).ready(function() {
                                                            $("#btn_ajax" +<?php echo $row_pd->pd_id; ?>).colorbox({width: "62%", height: "89%"});
                                                        });
                                                        </script>
                                                        <span>
                                                            <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id); ?>&pid=<?php echo $row_pd->pd_id; ?>" id="btn_ajax<?php echo $row_pd->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but">Send Inquiry</a></span>
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
                            <!--a class="buts left" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 20px;">Prev</a>
                            <?php for ($i = 1; $i <= $totalitems; $i++) : ?>
                                <a class="buts" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $i; ?>" uri-page="<?php echo $i;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 20px; font-family: serif;"><span style="margin: 2px 5px 4px 5px; font-weight: 200 !important;"><?php print_r($i); ?></span></a>
                            <?php endfor; ?>
                             
                            <a class="buts right" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 20px;">Next</a-->
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
<li class="page-item"><span style="color:black; background:none;border:0;">Go to page <input id="goToPageNum" type="text" value="" onkeyup="goToPage()" style="width: 50px"/><button id="goToPageGo" class="btn btn-xs btn-default border-radius-0 buts" onclick="" style="padding:0 5px 0 5px">Go</button></span></li>
</ul>
</div>
</nav>                  
                       

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
