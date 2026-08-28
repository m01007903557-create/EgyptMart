<?php
// company/EnquiryRequest.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
session_start();
if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location: ../sign-in.php");
    exit;
}
ob_end_flush(); // اختياري

include "includes/header.php";

// التحقق من تسجيل الدخول
$user = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
if ($user == 0) {
    header("Location: ../sign-in.php");
    exit;
}

// جلب معاملات URL
$c = isset($_GET['c']) ? mysqli_real_escape_string($con, $_GET['c']) : '';
$cid = isset($_GET['c']) ? substr($_GET['c'], 4) : '';

// جلب بيانات المستخدم الحالي
$sql_own = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$user}' AND bnsprof_uid = usr_id LIMIT 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

// جلب معرفات المنتجات من الجلسة
$image_vars = [];
$id_vars = [];

for ($i = 1; $i <= 20; $i++) {
    $image_key = "image{$i}";
    $id_key = "id{$i}";
    
    $image_vars[$i] = isset($_SESSION[$image_key]) ? $_SESSION[$image_key] : '';
    $id_vars[$i] = isset($_SESSION[$id_key]) ? (int)$_SESSION[$id_key] : 0;
}

// استخراج المتغيرات
extract($image_vars, EXTR_PREFIX_ALL, '');
extract($id_vars, EXTR_PREFIX_ALL, '');

// التأكد من أن جميع المعرفات أعداد صحيحة
for ($i = 1; $i <= 20; $i++) {
    $id = "id{$i}";
    $$id = (int)($$id ?? 0);
}

// بناء قائمة معرفات المنتجات
$product_ids = [];
for ($i = 1; $i <= 20; $i++) {
    $id = ${"id{$i}"};
    if ($id > 0) {
        $product_ids[] = $id;
    }
}

// جلب المنتجات المحددة
$sel_product = [];
$sel_product_image = [];

if (!empty($product_ids)) {
    $ids_string = implode(',', $product_ids);
    $sel_pro = "SELECT * FROM products 
                WHERE pd_uid = '" . (int)($row->usr_id ?? 0) . "' 
                AND pd_id IN ({$ids_string})";
    $s_prod = mysqli_query($con, $sel_pro);
    
    while ($select_product = mysqli_fetch_object($s_prod)) {
        $sel_product[] = $select_product;
        $sel_product_image[] = $select_product->pd_image;
    }
}

$_SESSION['selimage'] = $sel_product_image;
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إستفسار مجمع</title>
    <style>
    .contact-image li {
        display: inline-block;
    }
    #btn-sub {
        background: #017601;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#017601', EndColorStr='#059d05');
        background: -webkit-gradient(linear, 0 0, 0 bottom, from(#017601), to(#059d05));
        background: -webkit-linear-gradient(#017601, #059d05);
        background: -moz-linear-gradient(#017601, #059d05);
        background: -ms-linear-gradient(#017601, #059d05);
        background: -o-linear-gradient(#017601, #059d05);
        background: linear-gradient(#017601, #059d05);
        margin-top: 20px;
        margin-left: 250px;
        background: #c30 !important;
        box-shadow: 0pt 1px 5px rgb(170, 170, 170);
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        color: #fff;
        border: 1px solid rgb(24, 143, 205);
        border-radius: 6px;
        padding: 5px 20px;
        cursor: pointer;
    }
    </style>
    
    <script>
    function sendEnquiry() {
        var quentity = [];
        var i = 0;
        
        $(".ws_quentity").each(function() {
            quentity[i] = $(this).val();
            i++;
        });

        var msg_from = $('#msg_from').val();
        var msg_to = $('#msg_to').val();
        var msg_subject = $('#msg_subject').val();
        var sel_product_ser = $('#sel_product_ser').val();
        var msg_message = $('#msg_message').val();

        $("#btn-sub").css("display", "none");
        $("#loading").css("display", "block");

        $.post("../company/sendimagerequestmail.php", {
            msg_from: msg_from,
            msg_image: sel_product_ser,
            msg_to: msg_to,
            msg_subject: msg_subject,
            msg_message: msg_message,
            quentity: quentity
        }, function(data) {
            if (data == 1) {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#succ_result").css("display", "block");
                }, 500);
            } else {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#err_result").css("display", "block");
                }, 500);
            }
        });
    }
    </script>
</head>

<body>
    <div id="body">
        <ul class="cb">
            <li id="wideColumn">
                <div id="h1">
                    <h1 style="text-transform: capitalize;" title="Wholesale Contact">إستـفسـار مجمـع</h1>
                </div>

                <div id="breadcrumb">
                    <ul>
                        <li>
                            <a href="http://egyptmart.shop/company/index.php?c=<?php echo urlencode($c); ?>" id="myDiv">
                                الرئيسية
                            </a>
                            <b>»</b>
                        </li>
                        <li>إستـفسـار مجمع</li>
                    </ul>
                </div>
                <br><br>

                <form method="post" action="sendimagerequestmail.php">
                    <ul class="contact-image">
                        <?php
                        $sel_product_ser = serialize($sel_product);
                        
                        foreach ($sel_product as $selpro):
                            $unit_name = get_measurement_unit((int)($selpro->pd_unit ?? 0));
                        ?>
                            <li style="height:50px; width:33%;">
                                <img style="width:170px; margin-left: 26px;" 
                                     src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($selpro->pd_image ?? ''); ?>">
                                <p style="text-align: center;">
                                    <?php echo htmlspecialchars($selpro->pd_title ?? ''); ?>
                                    <br>
                                    <b>أقل كمية :</b>
                                    <input type="text" name="quantity" class="ws_quentity" 
                                           value="<?php echo (int)($selpro->pd_min_order_qty ?? 0); ?>" 
                                           style="width: 22px; display: -webkit-inline-box;"/>
                                    <br>
                                    <b>الوحدة:</b> <?php echo htmlspecialchars($unit_name); ?>
                                </p>
                            </li> 
                        <?php endforeach; ?>
                    </ul>

                    <input type="hidden" id="sel_product_ser" name="sel_product_ser" 
                           value="<?php echo htmlspecialchars($sel_product_ser, ENT_QUOTES); ?>" />
                    <input type="hidden" id="c" name="c" value="<?php echo htmlspecialchars($c); ?>" />
                    <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $user; ?>" />
                    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)($row->usr_id ?? 0); ?>" />
                    <input type="hidden" id="msg_subject" name="msg_subject" value="Wholesale Business Enquiry" />
                    <input type='hidden' name='email' value='<?php echo htmlspecialchars($row_own->email ?? ''); ?>'>

                    <br><br><br>

                    <div style="width: 100%;">
                        <div style="width: 30%; float: left">
                            <fieldset style="height: 108px; border: 1px solid rgb(134, 182, 217); margin-top: 29px; width: 190px;">
                                <legend style="font-size: 13px; color:#017BBC; text-align: center;" 
                                        title="Describe your requirements">
                                    <strong>إوصف تفاصيل إستفسارك</strong>
                                </legend>
                                <div class="f1-nw" style="color:#055985;">
                                    <ul style="margin-left: 10px;">
                                        <li class="li-1">تفاصيل المنتجات أو الخدمات</li>
                                        <li class="li-1">المواصفــات المطلــوبة</li>
                                        <li class="li-1">الـتعبئــة والتغــليف</li>
                                        <li class="li-1">مكـان وموعـد التســليم</li>
                                    </ul>
                                </div>
                            </fieldset>
                        </div>
                        
                        <div style="width: 70%; float: left">
                            <label style="font-size:large;"><b>الطلب والإستفسار :</b></label><br />
                            <div style="width: 555px; height: auto;">
                                <textarea id="msg_message" name="msg_message" 
                                          style="width: 81%; height: 104px; overflow: auto; padding: 10px; box-sizing: border-box;"></textarea>
                            </div>
                        </div>
                    </div>

                    <input class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px" 
                           id="btn-sub" value="طلب أصناف مجمعة" type="button" onclick="sendEnquiry()">
                </form>

                <div id="loading" style="display:none; padding-left:192px; color:#1045B0; padding-top:16px;" 
                     class="g9 bo off">
                    <img class="loading" src="../images/loading-small.gif" alt="loading" height="16" width="16">
                    <b>... إنتظر من فضلك</b>
                </div>
                
                <div id="succ_result" style="display:none; padding-left:192px; color:#009700; padding-top:16px;" 
                     class="g9 bo off">
                    <b>... تم إرسال طلبك بنجاح</b>
                </div>
            </li>

            <?php include "includes/right.php"; ?>
        </ul>
    </div>
    
    <br><br>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>