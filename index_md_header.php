<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<div class="row">
        <div class="col-lg-10 col-sm-6 prdt-search-btn-grp" style="padding-left:0px;">
    <?php 
    $key1 = trim($_GET['keywords'] ?? '');
    $key2 = str_replace(" ", "+", $key1); 
    ?>
     
          
          <?php  
      
     // echo $_GET['rctyp'];
      
      if(isset($_GET['rctyp']) && ($_GET['rctyp']) == 'Suppliers'){ ?>
            
          <button type="button" onclick="location='https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/arabyos/?keyword_typesss=<?php echo urlencode($_GET['keyword_typesss'] ?? ''); ?>&keywords=<?php echo urlencode($key2); ?>&rctyp=Suppliers'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd" style="color:#FFF; background-color:#00F"><span>موردون</span></button>
          
          <?php }else{ ?>
          <?php
            $key_cat_id = '';

            if (isset($_GET['rctyp']) && ($_GET['rctyp']) == "Products") {
              if (isset($_GET['keywords']) && $_GET['keywords'] == '') {
                  $keywords = 'all';
              }
            /* added by Hetal on Date - 24 Feb 2018 */
            //echo $_GET["keywords"];
            $key = str_replace("+"," ", $_GET['keywords'] ?? '');
            $sql_key = "SELECT * FROM products 
                        JOIN product_category ON product_category.pc_id = products.pd_subcat_id 
                        JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid 
                        WHERE (pd_title LIKE '%" . mysqli_real_escape_string($con, $key) . "%' 
                        OR business_profile.bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $key) . "%') 
                        AND pc_status='1'";
            //echo $sql_key;die;
            $query_key = mysqli_query($con, $sql_key);
            $row_key = mysqli_fetch_object($query_key);
      //print_r($row_key);
              //$key_cat_id='';
              if(mysqli_num_rows($query_key) > 0){
                $key_cat_id = $row_key->pc_parent_id;
              }
              else{
                
                $sql_second_query = mysqli_query($con, "SELECT pc.* FROM product_category pc 
                    LEFT OUTER JOIN product_category spc ON pc.pc_id = spc.pc_parent_id 
                    WHERE pc.pc_name LIKE '%" . mysqli_real_escape_string($con, str_replace(array("+","%20"), array(" "," "), $_GET['keywords'] ?? '')) . "%' 
                    AND pc.pc_parent_id!='0' AND pc.pc_status='1'");
                $fetch_records = mysqli_fetch_object($sql_second_query);
                if(mysqli_num_rows($sql_second_query) > 0){
                    $sub_cat_id_get = $fetch_records->pc_id;
                    $sql_second_query1 = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id='" . $sub_cat_id_get . "'");
                    $fetch_records1 = mysqli_fetch_object($sql_second_query1);
                    if(mysqli_num_rows($sql_second_query1) > 0){
                        $key_cat_id = $fetch_records1->pc_id;
                    } else {
                        $key_cat_id = $fetch_records->pc_id;
                    }
                }
              }
            
          }

      // echo $key_cat_id;

          ?>
          <button type="button" onclick="location='https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/catcompany.php?token=<?php echo rand(1000,9999) . md5((string)$key_cat_id); ?>'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd"><span>شركات</span></button>
          <!-- <button type="button" onclick="location='https://<?php echo $_SERVER['HTTP_HOST']; ?>/search.php?rctyp=Suppliers&keywords=<?php echo $_GET['keywords']?>'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd"><span>موردون</span></button> -->
          <?php } ?>
          
            
            <?php  
      if(isset($_GET['rctyp']) && ($_GET['rctyp']) == 'buy_lead'){ ?>
            
          <button type="button" onclick="location='https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/arabyos/?keyword_typesss=<?php echo urlencode($_GET['keyword_typesss'] ?? ''); ?>&keywords=<?php echo urlencode($key2); ?>&rctyp=buy_lead'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd" style="color:#FFF; background-color:#00F"><span>طلبات شراء</span></button>
          
          <?php }else{ ?>
          <button type="button" onclick="location='/search.php?rctyp=buy_lead&keywords=<?php echo urlencode($_GET['keywords'] ?? ''); ?>'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd"><span>طلبات شراء</span></button>
          <?php }  ?>
          
          
          
          
          <?php  
      if((isset($_GET['rctyp']) && ($_GET['rctyp']) == 'tender') || (isset($_GET['rctyp']) && ($_GET['rctyp']) == 'auction')){ ?>
            
          <button type="button" onclick="location='https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/arabyos/?keyword_typesss=<?php echo urlencode($_GET['keyword_typesss'] ?? ''); ?>&keywords=<?php echo urlencode($key2); ?>&rctyp=tender'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd" style="color:#FFF; background-color:#00F"><span> مناقصات </span></button>
          
          <?php }else{ ?>
          <button type="button" onclick="location='/search.php?rctyp=tender&keywords=<?php echo urlencode($_GET['keywords'] ?? ''); ?>'"  class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd"><span>مناقصات</span></button>
          <?php }  ?>
            
            
            <button type="button" class="btn btn-sm btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize newhd">
          <a href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/post-sell-offer.php" class="txt-cherry"><img src="images/free.png" width="45px"/> أنشر عرض بيع خاص </a> / <a href="post-tender.php" >أنشـر مناقصة</a>
          </button>
        </div>
        <div class="col-lg-2 col-sm-2 text-right prdt-sup-ctrl" style="padding-top:8px;"> 
          <!--
            <b><a href="#"></a> <span><?php if(isset($_GET['rctyp']) && ($_GET['rctyp'] == 'Products' || $_GET['rctyp'] == 'Suppliers'  || isset($_GET['grid']) && $_GET['grid'] == 'active')){?>Products & Suppliers<?php }else if(isset($_GET['rctyp']) && $_GET['rctyp'] == 'tender'){?>Tenders & Auctions<?php }?></span></b>
          <button class="btn btn-default btn-xs"><i class="fa fa-caret-left" style="font-size:15px;"></i></button>
          <button class="btn btn-default btn-xs"><i class="fa fa-caret-right" style="font-size:15px;"></i></button>
          -->
        </div>
        <div class="clearfix"></div>
      </div>
 <?php
 
 if(isset($_REQUEST['rctyp']) && $_REQUEST['rctyp'] == 'buy_lead'){
   
   
 }
 elseif(isset($_REQUEST['rctyp']) && $_REQUEST['rctyp'] == 'tender'){
   
 }
 elseif(isset($_REQUEST['rctyp']) && $_REQUEST['rctyp'] == 'Suppliers'){
   
 }
else { 
 ?>

<div class="row"  style="width:100%">
        <div class="col-lg-12 col-sm-9 ar-box-1 margin-top-5 padding-bottom-0 padding-top-0 hidden-xs">
          
          <form autocomplete="off" name="searchForm" action="search.php?<?php if(isset($_GET['rctyp']) && $_GET['rctyp'] != ''){ echo "&rctyp=".urlencode($_GET['rctyp']); } ?><?php if(isset($_GET['keywords']) && $_GET['keywords'] != ''){ echo "&keywords=".urlencode(trim($_GET['keywords'])); } ?>" method="POST" id="hdr_frm">
            <table class="table">
            <tr>
              <td class="search-flex-item">
                <div class="search-flex-item-first">
                    <div class=" txt-gray hidden-sm hidden-md visible-lg">نـوع الـتجــارة  </div>
                    <div class="blank-td hidden-sm hidden-md visible-lg"><span></span></div>
                </div>
                  <div class="search-flex-item-second">
                  <?php  
                    $btqury = "select * from business_type";
                    $btresult = mysqli_query($con, $btqury);
                    $business_type = array();
                    if(isset($_SESSION['business_type'])) {
                      $business_type = $_SESSION['business_type'] ;
                    }
                    $strno = "1,2,3,5,9,";
                    while($btrow = mysqli_fetch_array($btresult)){ 
                        $btno = $btrow['bsntyp_id'].",";
                        if(strpos($strno, $btno) !== false) { 
                    ?>

                  <div>
                    <input type="checkbox" class="search_filter" <?php echo (isset($_POST['bsn_type']) && in_array($btrow['bsntyp_id'], $_POST['bsn_type'])) ? "checked" : ''; ?> name="bsn_type[]" value="<?php echo $btrow['bsntyp_id']; ?>">

                    <label>
                      <span>
                        <b class="txt-bold" style="font-size:10px"><?php echo htmlspecialchars($btrow['bsntyp_title']); ?></b>
                      </span>
                    </label>
                  </div>
            <?php } } ?>
          </div>
          <div class="search-flex-item-third">
                <div class="text-right search_confirm_wrap" style="width:110px;">
                <input type="hidden" id="srchbustype" name="srchbustype" value="srchbustype" />
                <!--<input type="submit" id="btnSearch" value="searchmem" class="page-header-col1-row2-col2-form-btn"/>-->
              <input id="btnSearch" value="تأكــيد" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit">
              <a href="javascript: window.history.go(-1)" class="cancelBtns">الغــاء </a>
                    </div>
                </div>
                </td>
            </tr>
      </form>
      <form autocomplete="off" name="membershipForm" action="search.php?<?php if(isset($_GET['rctyp']) && $_GET['rctyp'] != ''){ echo "&rctyp=".urlencode($_GET['rctyp']); } ?><?php if(isset($_GET['keywords']) && $_GET['keywords'] != ''){ echo "&keywords=".urlencode(trim($_GET['keywords'])); } ?>" method="POST" id="membershipForm">
            <tr style="display: none;">
        <td colspan="8">
        <table width="100%">
          <tr>
              <td class="txt-gray hidden-sm hidden-md visible-lg">Membership Type</td>
              <td class="blank-td hidden-sm hidden-md visible-lg"><span></span></td>
              <?php
                  $membership_type = array();
                  if(isset($_SESSION['membership_type'])) {
                      $membership_type = $_SESSION['membership_type'] ;
                  }

                  $mtqury = "SELECT sp.mp_id, sip.mst_icon, sp.mst_name FROM smembership_plan sp 
                             JOIN smembership_icon_plan sip ON sp.mp_id = sip.mp_id 
                             WHERE sp.mp_status != '0'"; 
                  $mtresult = mysqli_query($con, $mtqury);
                  $plan_array_icons = array();

                  while($mtrow = mysqli_fetch_array($mtresult)){
                      $plan_array_icons[$mtrow['mp_id']] = $mtrow['mst_icon'];
                  ?>
                  <td><input type="checkbox" class="search_filter" <?php echo (isset($_POST['mst_type']) && in_array($mtrow['mp_id'], $_POST['mst_type'])) ? "checked" : ''; ?> name="mst_type[]" value="<?php echo $mtrow['mp_id']; ?>"> 
                     <img src="admin/images/<?php echo htmlspecialchars($mtrow['mst_icon']); ?>" width="20px" height="20px;" style="margin-right:5px;"/> 
                     <span class="txt-gray" class="text-uppercase"><?php echo htmlspecialchars($mtrow['mst_name']); ?></span>
                  </td>
                <?php } ?>
                <input type="hidden" id="srchbustype" name="srchbustype" value="srchbustype" />
                <td class="text-right search_confirm_wrap" style="width:110px;">
                    <input id="memberBtnSearch" value="Confirm" name="memberBtnSearch" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit">
                    <a href="javascript: window.history.go(-1)" class="cancelBtns">الغاء</a>
                </td>
              </tr>
          </table>
         </td>
             </tr>
      </form>
             <tr>
        <td colspan="7">
          <table width="100%">
       <tr>
          
        <td class="txt-gray hidden-sm hidden-md visible-lg">أدوات أخـرى</td>
              <td class="blank-td hidden-sm hidden-md visible-lg" style="position: relative; left: -43px;"><span></span></td>
              <td class="txt-gray">  
          
          
      
              
                  
                  
        <form name="minQtyForm" id="minQtyForm" method="post" action="search.php?<?php if(isset($_GET['rctyp']) && $_GET['rctyp'] != ''){ echo "&rctyp=".urlencode($_GET['rctyp']); } ?><?php if(isset($_GET['keywords']) && $_GET['keywords'] != ''){ echo "&keywords=".urlencode($_GET['keywords']); } ?>">
          أقـل كميـة
                <input type="text" name="min_qty" value="<?php echo isset($_POST['min_qty']) ? htmlspecialchars($_POST['min_qty']) : 1; ?>" style="width:35px;" autocomplete="off" style="width:35px; margin-left:5px;" required/>
        <input type="submit" name="minqty" value="OK" class="minQtySubCls" />
        </form>  
           </td>
              <!--<td class="ar-header-box txt-gray">Currency
                <label style=" margin-left:10px;">
                  <select class="txt-nerrow">
                    <option value="USD" selected>USD</option>
                    <option value="INR">INR</option>
                    <option value="EGP">UAE</option>
                  </select>
                </label></td>
              <td  class="txt-gray">Online <img src="images/chat.png" width="15px"/></td>-->
          <td class="text-right txt-gray" style="text-align: left;"> رؤية شبكة أو قائمة

    <?php
    $base_url = 'search.php?';
    if(isset($_GET['keywords']) && $_GET['keywords'] != ''){ $base_url .= 'keywords='.urlencode($_GET['keywords']).'&'; }
    if(isset($_GET['rctyp']) && $_GET['rctyp'] != ''){ $base_url .= 'rctyp='.urlencode($_GET['rctyp']).'&'; }
    if(isset($_GET['keyword_type']) && $_GET['keyword_type'] != ''){ $base_url .= 'keyword_type='.urlencode($_GET['keyword_type']).'&'; }
    $is_grid = (isset($_GET['grid']) && $_GET['grid'] == 'active');
    ?>

    <a style="margin-left: 14px;" href="<?php echo $base_url; ?>list=active">
        <i class="fa fa-list" style="font-size:16px;padding-left:7px;margin-top:2px;
        color:<?php echo !$is_grid ? '#00F' : '#999'; ?>;
        font-weight:<?php echo !$is_grid ? 'bold' : 'normal'; ?>;"></i>
    </a>

    <a href="<?php echo $base_url; ?>grid=active">
        <i class="fa fa-th-large" style="font-size:16px;margin-top:2px;padding-left:7px;
        color:<?php echo $is_grid ? '#00F' : '#999'; ?>;
        font-weight:<?php echo $is_grid ? 'bold' : 'normal'; ?>;"></i>
    </a>
</td>
         <td style="padding-left: 10px;" colspan="2">
              <form name="getcitydata" id="getcitydata" method="post" action="search.php?<?php if(isset($_GET['rctyp']) && $_GET['rctyp'] != ''){ echo "&rctyp=".urlencode($_GET['rctyp']); } ?><?php if(isset($_GET['keywords']) && $_GET['keywords'] != ''){ echo "&keywords=".urlencode($_GET['keywords']); } ?>" style="width: 120px">
              <input type="hidden" id="srchbustype" name="srchbustype" value="srchbustype" />
              <input type="search" class="border-0" name="scity" id="scity" value="<?php echo isset($_POST['scity']) ? htmlspecialchars($_POST['scity']) : ''; ?>" placeholder="المـدينــة" style="width:70px;float: left;"/>
              <!--<a href="#"><i class="fa fa-search " style="font-size:14px;"></i></a>-->
              <button type="submit" style="border: none;background-color: transparent;"><i class="fa fa-search " style="font-size:14px;"></i></button>
              <!--<div id="CitysearchShows" style="display: none; position: fixed; width: 0px; top: 205px; left: 73.5%;">
                <img src="img/377.gif" style="width: 15px;">
              </div>-->
              <div id="city_suggesstion_box"></div>
              </form>
               </td>
             </tr>
       </table>
         </td>
             </tr>
           </table>
            
<!--            <table class="table">
            <tr>
              <td class=" txt-gray">نـوع التجــارة</td>
              <td class="blank-td"><span></span></td>
              <td><b class="txt-bold" style="font-size:18px"><a href="#"title="Manufacturer">مصنع</a></b></td>
              <td><b class="txt-bold" style="font-size:18px"><a href="#"title="Exporter">مصدر</a></b></td>
              <td><b class="txt-bold" style="font-size:18px"><a href="#"title="Wholesaler">تاجر جملة</a></b></td>
              <td><b class="txt-bold" style="font-size:18px"><a href="#"title="Retailer">تاجر تجزئة</a></b></td>
              <td class="text-right" style="width:110px;"><input type="search" class="border-0" placeholder="search city" style="width:70px;"/>
                <a href="#"><i class="fa fa-search " style="font-size:16px;"></i></a>
            </tr>
            <tr>
              <td class="txt-gray">Membership Type</td>
              <td class="blank-td"><span></span></td>
              <td><a href="#" class="txt-gray"><img src="images/4.png" width="20px" height="20px;" style="margin-right:5px;"/> <span class="text-uppercase">Sponsor</span> Supplier</a></td>
              <td><a href="#" class="txt-gray"><img src="images/5.png" width="20px" style="margin-right:5px;"/><span class="text-uppercase">Senior</span> Supplier</a></td>
              <td><a href="#" class="txt-gray"><img src="images/6.png" width="20px" style="margin-right:5px;"/>Verified <span class="text-uppercase"> Junior</span></a></td>
              <td></a></td>
              <td></td>
             </tr>
             <tr>
              <td class="txt-gray">Evaluation Tools</td>
              <td class="blank-td"><span></span></td>
              <td class="txt-gray">Min Qty
                  <input type="text" name="min_qty" value="1" style="width:50px;"/></td>
              <td class="ar-header-box txt-gray" colspan="2">Currency Converter
                <label style=" margin-left:28px;">
                  <select class="txt-nerrow">
                    <option selected>USD</option>
                    <option>INR</option>
                    <option>UAE</option>
                  </select>
                </label></td>
              <td  class="txt-gray">Online <img src="images/chat.png" width="15px"/></td>
              <td class="text-right txt-gray"> View As
                <button type="button" class="btn btn-default btn-xs"><i class="fa fa-list " style="font-size:14px; margin-top:2px;"></i></button>
                <button type="button" class="btn btn-default btn-xs"><i class="fa fa-th-large " style="font-size:14px; margin-top:2px;"></i></button></td>
             </tr>
           </table>-->
        </div>
        <div class="clearfix"></div>
      </div>
   <?php } ?>   
      <script>
    $(document).ready(function() {
      $("#scity").autocomplete("ajax-file/showProductsCity.php", {
      selectFirst: true,
      extraParams: {keywords:'<?php echo htmlspecialchars($_GET['keywords'] ?? ''); ?>', rctype: '<?php echo htmlspecialchars($_GET['rctyp'] ?? ''); ?>'} 
    });

      $('input#scity').focus(function(){
        $(this).parent().animate({width: '250px'})
    });
      $('input#scity').blur(function(){
        $(this).parent().animate({width: '120px'})
    });
    });
  </script>