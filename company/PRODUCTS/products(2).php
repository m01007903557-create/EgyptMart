<?php
require_once "../common.php"; // هذا المسار صحيح لأن products.php في company
require_once "includes/get_company_data.php";
$row = getCompanyData($con);

// ثم باقي الكود...
include "includes/header.php";

// ============================================================
// 1. جلب معاملات التصنيف من الرابط
// ============================================================
$c = $_GET['c'] ?? '';           // التصنيف الرئيسي (قد لا يستخدم مباشرة)
$sc = $_GET['sc'] ?? '';         // التصنيف الفرعي (pd_subcat_id)

// التحقق من صحة sc (رقم صحيح)
$subcat_id = 0;
if (!empty($sc) && is_numeric($sc)) {
    $subcat_id = intval($sc);
}

// ============================================================
// 2. متغيرات الصفحة الأساسية
// ============================================================
$flag = $_GET['flag'] ?? '';
if ($flag == 'whsuccess') { ?>
    <div style="text-align: center; color: green; padding: 10px;">تم إرسال الطلب بنجاح</div>
<?php }

$class = "grids_list";
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 51;
$start = (($page - 1) * $limit);

// ============================================================
// 3. استعلامات المنتجات (تعتمد على pd_subcat_id)
// ============================================================

// للمنتجات الساخنة (pd_hot = 1)
$sql_pd_h = "SELECT * FROM products WHERE pd_subcat_id = '$subcat_id' AND pd_status = '1' AND pd_hot = '1' LIMIT $limit OFFSET $start";
$res_pd_h = mysqli_query($con, $sql_pd_h);
$count_pd_h = mysqli_num_rows($res_pd_h);

// للمنتجات العادية (pd_hot = 0 أو فارغة)
$sql_pd = "SELECT * FROM products WHERE pd_subcat_id = '$subcat_id' AND pd_status = '1' AND (pd_hot = '0' OR pd_hot = ' ') LIMIT $limit OFFSET $start";
$res_pd = mysqli_query($con, $sql_pd);
$count_pd = mysqli_num_rows($res_pd);

// إجمالي عدد المنتجات (للباجينيشن)
$total_sql_h = "SELECT COUNT(*) as totle FROM products WHERE pd_subcat_id = '$subcat_id' AND pd_status = '1' AND pd_hot = '1'";
$total_res_h = mysqli_query($con, $total_sql_h);
$total_row_h = mysqli_fetch_object($total_res_h);
$total_hot = $total_row_h->totle ?? 0;

$total_sql_n = "SELECT COUNT(*) as totle FROM products WHERE pd_subcat_id = '$subcat_id' AND pd_status = '1' AND (pd_hot = '0' OR pd_hot = ' ')";
$total_res_n = mysqli_query($con, $total_sql_n);
$total_row_n = mysqli_fetch_object($total_res_n);
$total_normal = $total_row_n->totle ?? 0;

$totalitems = ceil($total_normal / $limit);
$totalitem_hot = ceil($total_hot / $limit);

$prev = ($page > 1) ? $page - 1 : 1;
$next = ($page < $totalitems) ? $page + 1 : 1;

// ============================================================
// 4. كائن $row افتراضي لتجنب الأخطاء في right.php أو القالب
// ============================================================
$row = new stdClass();
$row->usr_id = 0;
$row->bnsprof_comp_url = '';
$row->name_prefix = '';
$row->fname = '';
$row->lname = '';
$row->bnsprof_address1 = '';
$row->bnsprof_address2 = '';
$row->bnsprof_city = 0;
$row->country = 0;
$row->country_ph_code = '';
$row->bnsprof_phcode1 = '';
$row->bnsprof_ph1 = '';
$row->bnsprof_state = 0;
$row->bnsprof_compname = '';
$row->bnsprof_id = 0;

// ============================================================
// 5. باقي الكود الأصلي (HTML، التبويبات، إلخ) - مع تعديل الاستعلامات المكررة
// ============================================================
?>
<!DOCTYPE html>
<html>
<head>
    <!-- باقي محتوى head كما هو في ملفك الأصلي -->
</head>
<body>

<div id="body" class='containerBlock'>
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
                    <li style="float: right; display: inline-block;">
                        <div class="view_list_design" style="">
                            <span style="font-size: 13px;" title="View As">العرض شبكة أو قائمة</span>&nbsp;&nbsp;
                            <a href="<?php echo BASE_URL . '/company/products.php?c=' . $c . '&view=product_list' . '&page=' . $page; ?>"><i class="fa fa-list"></i></a>&nbsp;&nbsp;&nbsp;
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
                                    <div class="top_text_first" style="font-weight:100 !important;" title="click to select products and contact the supplier or send wholesale inquiry."> إختـار المنتجـات التى تهتم بها وتواصل مباشـرة مع الشركـة الموردة لها الآن
                                        <span><i class="fa fa-plus"></i></span>  	
                                    </div>
                                </div>
                                <!-- باقي محتوى السلايدر (WebcastFix) كما هو في ملفك الأصلي -->
                            </li>
                        </ul>
                    </div>

                    <!-- ========================================= -->
                    <!-- تبويب المنتجات الهامة (HOT) -->
                    <!-- ========================================= -->
                    <div id="view1" style="position: relative;">
                        <div class="hotproduct">
                            <div class="top_page_list_first" style="position:absolute; top:-175px; right: 0px;font-weight: normal;">
                                <a class="but left"  uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/left.png" style="width:10%" /></a>
                                <a class="but right"  uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/right.png" style="width:10%" /></a>
                                <?php echo $page . " الى " . $totalitem_hot; ?> الصفحـات
                            </div>

                            <ul class="hot-product">
                                <li class="ac-bdrb lc-bbw0 <?php echo $class; ?>">
                                    <?php if ($count_pd_h > 0) {
                                        while ($row_pd_h = mysqli_fetch_object($res_pd_h)) { ?>
                                            <section class="itemr omParentClass">
                                                <div class="shadow items omItems">
                                                    <div class="item">
                                                        <div class="product_image omImage">
                                                            <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;">
                                                                <img src="../upload/myproduct/<?php
                                                                if ($row_pd_h->pd_image != '') {
                                                                    $imgarr = explode(',',$row_pd_h->pd_image);
                                                                    echo $imgarr[0];
                                                                } else {
                                                                    echo "noimage.jpg";
                                                                } ?>" alt="<?php echo $row_pd_h->pd_title; ?>" class="cu omImg">
                                                                <?php if ($row_pd_h->pd_imagelogo != '') { 
                                                                    $logoarr = explode(',',$row_pd_h->pd_imagelogo); ?>
                                                                    <div class="zk"><img src="../upload/myproduct/<?php echo $logoarr[0]; ?>" /></div> 
                                                                <?php } ?>
                                                            </a>
                                                            <li class="wtmp wtmpie omListWrap">
                                                                <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>" class="ajax1" style="cursor:pointer;"><img src="images/zoom.png" style="height: 30px; width:30px; float: right; position: absolute; left:190px; top: 150px;"/></a>
                                                            </li>
                                                        </div>
                                                        <div class="product_right_sec">             
                                                            <div class="product_title product_title_2">
                                                                <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;"><?php echo $row_pd_h->pd_title; ?></a>		
                                                            </div>
                                                            <div class="product_title product_title_1">
                                                                <p> <?php echo substr($row_pd_h->pd_desc, 0, 65); ?>
                                                                <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:15px;">المزيــد</a></p>
                                                            </div>
                                                            <button class="add-to-cart omcart om_cart" onclick="addtosupplier(<?php echo $row_pd_h->pd_id; ?>, '<?php echo $row->bnsprof_comp_url ?? ''; ?>', '<?php echo $row_pd_h->pd_image ?? 'noimage.jpg'; ?>');" style="float:right;"><a href="javaScript:void(0);"><i class="fa fa-plus"></i></a></button>

                                                            <div class="product_detail">
                                                                <div class="price_div">
                                                                    <span><?php echo $row_pd_h->pd_fob_price; ?></span><?php echo get_product_detail($row_pd_h->pd_id, 'pd_currency'); ?>
                                                                    <div class="unit_div"><span><?php echo $row_pd_h->pd_min_order_qty; ?> </span> <?php echo get_measurement_unit($row_pd_h->pd_unit); ?><span style="font-size:11px; color: #B5BABE;"> (أقــل كـمـية)</span></div>
                                                                </div>
                                                            </div>

                                                            <!-- زر "أطلب عرض سعر" المنتج الساخن -->
                                                            <div class="product_number">
                                                                <a href="javascript:void(0);" onclick="openQuantityPopup('<?php echo addslashes($row_pd_h->pd_title); ?>', '<?php echo addslashes($user_name); ?>', '<?php echo $user_phone; ?>', '<?php echo addslashes($user_city); ?>', '<?php echo addslashes($user_email); ?>');" style="cursor:pointer;">
                                                                    <span><img src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span> 
                                                                    أطلب عرض سعر
                                                                </a>
                                                            </div>

                                                            <div class="link pt10px">				
                                                                <span>
                                                                    <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id); ?>&pid=<?php echo $row_pd_h->pd_id; ?>&c=<?php echo $c; ?>&vform=1" id="btn_ajax<?php echo $row_pd_h->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but" title="Send Inquiry">تواصل مع الشـركة </a>
                                                                </span>
                                                                <span><img src="<?php echo BASE_URL ?>/company/images/chat_icon.png" width="20"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>         
                                            </section>            
                                        <?php }
                                    } else {
                                        echo '<div class="alert alert-info">لا توجد منتجات ساخنة في هذا التصنيف.</div>';
                                    } ?>
                                </li>
                            </ul>
                            <div class="top_page_list_first" style="display: block; position:inherit; float:left;width:100%; padding-top: 30px; text-align: center !important;">
                                <a class="but left" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">السابق</a>
                                <?php for ($i = 1; $i <= $totalitem_hot; $i++) : ?>
                                    <a class="but" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $i; ?>" uri-page="<?php echo $i;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 17px; font-family: serif;"><span style="margin: 2px 5px 4px 5px; font-weight: 200 !important;"><?php echo $i; ?></span></a>
                                <?php endfor; ?>
                                <a class="but right" uri-id="<?php echo 'test.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="border-style: solid; border-width: 1px; border-color: black; color:#060; font-size: 12px;">التالى</a>
                            </div> 
                        </div>
                    </div>

                    <!-- ========================================= -->
                    <!-- تبويب المنتجات العادية -->
                    <!-- ========================================= -->
                    <div id="view2" style="position: relative;">
                        <div class="otherproduct">
                            <div class="top_page_list_first" style="position:absolute; top:-175px;right: 0px;font-weight: normal; margin-top: 160px;">
                                <a class="buts left" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $prev; ?>" uri-page="<?php echo $prev;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/left.png" style="width:10%" /></a>
                                <a class="buts right" uri-id="<?php echo 'tests.php?c=' . $c . '&view=' . $class . '&page=' . $next; ?>" uri-page="<?php echo $next;?>" href="javascript:void(0)" style="vertical-align: sub;"><img src="images/right.png" style="width:10%" /></a>
                                <?php echo $page . " الى " . $totalitems; ?> الصفحات
                            </div>

                            <ul class="hot-product">
                                <li class="ac-bdrb lc-bbw0 <?php echo $class; ?>">
                                    <?php if ($count_pd > 0) {
                                        while ($row_pd = mysqli_fetch_object($res_pd)) { ?>
                                            <section class="itemr omParentClass">
                                                <div class="shadow items omItems">
                                                    <div class="item omItems">
                                                        <div class="product_image omImage">
                                                            <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;">
                                                                <img src="../upload/myproduct/<?php
                                                                if ($row_pd->pd_image != '') {
                                                                    $imgarr = explode(',',$row_pd->pd_image);
                                                                    echo $imgarr[0];
                                                                } else {
                                                                    echo "noimage.jpg";
                                                                } ?>" alt="<?php echo $row_pd->pd_title; ?>" class="cu omImg">
                                                                <?php if ($row_pd->pd_imagelogo != '') { 
                                                                    $logoarr = explode(',',$row_pd->pd_imagelogo); ?>
                                                                    <div class="zk"><img src="../upload/myproduct/<?php echo $logoarr[0]; ?>" /></div> 
                                                                <?php } ?>
                                                            </a>
                                                            <li class="wtmp wtmpie omListWrap">
                                                                <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" class="ajax1" style="cursor:pointer;"><img src="images/zoom.png" style="height: 30px; width: 30px; float: right; position: absolute; left: 183px; top: 100px;"/></a>
                                                            </li>
                                                        </div>

                                                        <div class="product_title product_title_2">
                                                            <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:17px;"><?php echo $row_pd->pd_title; ?></a>		
                                                        </div>
                                                        <div class="product_title product_title_2">
                                                            <p> <?php echo substr($row_pd->pd_desc, 0, 65); ?>
                                                            <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5($row_pd->pd_id); ?>&c=<?php echo $c; ?>" style="font-size:15px;"><small>المـزيــد</small></a></p>
                                                        </div>
                                                        <button class="add-to-cart omcart om_cart" onclick="addtosupplier(<?php echo $row_pd->pd_id; ?>, '<?php echo $row->bnsprof_comp_url ?? ''; ?>', '<?php
                                                            if ($row_pd->pd_image != '') {
                                                                $imgarr = explode(',',$row_pd->pd_image);
                                                                echo $imgarr[0];
                                                            } else {
                                                                echo "noimage.jpg";
                                                            } ?>');" style="float:right;"><a href="javaScript:void(0);"><i class="fa fa-plus"></i></a></button>

                                                        <div class="product_detail">
                                                            <div class="price_div">
                                                                <span><?php echo $row_pd->pd_fob_price; ?></span><?php echo get_product_detail($row_pd->pd_id, 'pd_currency'); ?>
                                                                <div class="unit_div"><span><?php echo $row_pd->pd_min_order_qty; ?> </span> <?php echo get_measurement_unit($row_pd->pd_unit); ?><span style="font-size:11px; color: #B5BABE;"> (أقــل كـمـية)</span></div>
                                                            </div>
                                                        </div>

                                                        <!-- زر "أطلب عرض سعر" المنتج العادي -->
                                                        <div class="product_number">
                                                            <a href="javascript:void(0);" onclick="openQuantityPopup('<?php echo addslashes($row_pd->pd_title); ?>', '<?php echo addslashes($user_name); ?>', '<?php echo $user_phone; ?>', '<?php echo addslashes($user_city); ?>', '<?php echo addslashes($user_email); ?>');" style="cursor:pointer;">
                                                                <span><img src="<?php echo BASE_URL ?>/company/images/mobile_icon.png"></span> 
                                                                أطلب عرض سعر
                                                            </a>
                                                        </div>

                                                        <div class="link pt10px">				
                                                            <span>
                                                                <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id); ?>&pid=<?php echo $row_pd->pd_id; ?>&c=<?php echo $c; ?>&vform=1" id="btn_ajax<?php echo $row_pd->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but" title="Send Inquiry">تواصـل مع الشـركة</a>
                                                            </span>
                                                            <span><img src="<?php echo BASE_URL ?>/company/images/chat_icon.png" width="20"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>            
                                        <?php }
                                    } else {
                                        echo '<div class="alert alert-info">لا توجد منتجات عادية في هذا التصنيف.</div>';
                                    } ?>
                                </li>
                            </ul>
                            <div class="top_page_list_first" style="display: block; position: inherit; float:left; width:100%; padding-top: 30px; text-align: center !important;">
                                <!-- هنا يمكن إضافة عناصر التصفح (pagination) الخاصة بالمنتجات العادية إذا أردت -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include "includes/right.php"; ?>			
        </li>
    </ul>
</div>

<?php include "includes/footer.php"; ?>

<!-- ========================================= -->
<!-- JavaScript اللازمة للنافذة المنبثقة (تضاف مرة واحدة في أسفل الصفحة) -->
<!-- ========================================= -->
<script>
// بيانات المستخدم من PHP (تم تعريفها في الأعلى)
var userData = <?php echo json_encode([
    'name' => $user_name ?? '',
    'phone' => $user_phone ?? '',
    'email' => $user_email ?? '',
    'city' => $user_city ?? ''
]); ?>;
var platformPhone = "201030029097";

function openQuantityPopup(productName, userName, userPhone, userCity, userEmail) {
    var popupHtml = `
    <div id="whatsappPopup" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:10000; display:flex; justify-content:center; align-items:center;">
        <div style="background:#fff; width:90%; max-width:400px; border-radius:10px; padding:20px; direction:rtl; font-family:inherit;">
            <h3 style="margin:0 0 15px 0; text-align:center;">طلب عرض سعر</h3>
            <div style="background:#f5f5f5; padding:10px; border-radius:5px; margin-bottom:15px; font-size:13px;">
                <div>🛒 المنتج: ` + productName + `</div>
                <div>👤 الاسم: ` + (userName || 'زائر') + `</div>
                <div>📍 المحافظة: ` + (userCity || 'غير مسجلة') + `</div>
                <div>📞 الجوال: ` + (userPhone || 'غير مسجل') + `</div>
            </div>
            <div style="margin-bottom:15px;">
                <label>الكمية (الحد الأدنى):</label>
                <input type="number" id="popup_qty_from" style="width:100%; padding:8px; margin-top:5px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="margin-bottom:20px;">
                <label>الكمية (الحد الأقصى):</label>
                <input type="number" id="popup_qty_to" style="width:100%; padding:8px; margin-top:5px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="sendWhatsAppRequest('` + productName + `', '` + userName + `', '` + userPhone + `', '` + userCity + `', '` + userEmail + `')" style="flex:1; background:#25D366; color:#fff; border:none; padding:10px; border-radius:5px; cursor:pointer;">إرسال</button>
                <button onclick="closeQuantityPopup()" style="flex:1; background:#ccc; border:none; padding:10px; border-radius:5px; cursor:pointer;">إلغاء</button>
            </div>
        </div>
    </div>
    `;
    if (document.getElementById('whatsappPopup')) document.getElementById('whatsappPopup').remove();
    document.body.insertAdjacentHTML('beforeend', popupHtml);
}

function closeQuantityPopup() {
    var popup = document.getElementById('whatsappPopup');
    if (popup) popup.remove();
}

function sendWhatsAppRequest(productName, userName, userPhone, userCity, userEmail) {
    var qtyFrom = document.getElementById('popup_qty_from').value.trim();
    var qtyTo = document.getElementById('popup_qty_to').value.trim();
    
    if (qtyFrom === '' || qtyTo === '') {
        alert('الرجاء إدخال الكمية (من وإلى)');
        return;
    }
    if (parseInt(qtyFrom) > parseInt(qtyTo)) {
        alert('الحد الأدنى يجب أن يكون أقل من أو يساوي الحد الأقصى');
        return;
    }
    
    var message = "📦 طلب عرض سعر للمنتج: " + productName + "\n";
    message += "📊 الكمية المطلوبة: من " + qtyFrom + " إلى " + qtyTo + "\n";
    message += "━━━━━━━━━━━━━━━━━━━━━\n";
    message += "👤 بيانات المشتري:\n";
    message += "   • الاسم: " + (userName || 'زائر') + "\n";
    message += "   • المحافظة: " + (userCity || 'غير مسجلة') + "\n";
    if (userPhone) message += "   • الجوال: " + userPhone + "\n";
    message += "━━━━━━━━━━━━━━━━━━━━━\n";
    message += "🔄 الرجاء التواصل مع المشتري لتقديم عرض السعر";
    
    closeQuantityPopup();
    window.open('https://api.whatsapp.com/send?phone=' + platformPhone + '&text=' + encodeURIComponent(message), '_blank');
}
</script>

<!-- باقي السكربتات الأصلية (مثل التبويبات، waitMe، إلخ) موجودة في ملفك الأصلي، يمكنك الاحتفاظ بها -->
</body>
</html>