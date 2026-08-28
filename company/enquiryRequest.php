<?php
ob_start();
session_start();
include "includes/header.php";

$user = $_SESSION['uid_indm'] ?? 0;
if ($user == 0) {
    header("Location: ../sign-in.php");
    exit;
}

$c = $_GET['c'] ?? '';
$cid = substr($c, 4);

// جلب بيانات المورد الحالي
$sql = "select * from business_profile,user where bnsprof_uid=usr_id and md5(bnsprof_id)='$cid'";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

// جلب بيانات المستخدم المسجل
$uid_indm = $_SESSION['uid_indm'] ?? 0;
$sql_own = "select * from user, business_profile where usr_id='$uid_indm' and bnsprof_uid=usr_id limit 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

// ✅ إفراغ الجلسة إذا تغير المورد
$current_mfr = $cid;
$last_mfr = $_SESSION['last_mfr_for_products'] ?? '';
if ($last_mfr != '' && $last_mfr != $current_mfr) {
    for ($i = 1; $i <= 30; $i++) {
        unset($_SESSION["id$i"]);
        unset($_SESSION["image$i"]);
    }
}
$_SESSION['last_mfr_for_products'] = $current_mfr;

// استرجاع المعرفات من الجلسة
$ids = [];
for ($i = 1; $i <= 30; $i++) {
    if (isset($_SESSION["id$i"]) && !empty($_SESSION["id$i"]) && $_SESSION["id$i"] != 0) {
        $ids[$i] = (int)$_SESSION["id$i"];
    }
}

$id_list = implode(',', array_filter($ids));
if (empty($id_list)) {
    $id_list = '0';
}

// جلب المنتجات المختارة
$sel_product = [];
if ($id_list != '0') {
    $sel_pro = "select * from products where pd_id IN ($id_list)";
    $s_prod = mysqli_query($con, $sel_pro);
    if ($s_prod) {
        while ($select_product = mysqli_fetch_object($s_prod)) {
            $sel_product[] = $select_product;
        }
    }
}

// عرض المنتجات
if (empty($sel_product)) {
    
echo '<p style="color: red; text-align: center; padding: 20px; font-size: 18px; font-weight: bold;  "> ⚠️  لا يمكنك تجميع منتجات أكثر من مورد في نفس الوقت. يرجى العودة إلى صفحة المنتجات السابقة وإكمال طلبك، أو إغلاق الصفحة الحالية والبدء من جديد </p>';
   
} else {
    // ✅ كود عرض المنتجات (كما هو موجود)
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>استفسار مجمع</title>
    <style>
        /* إصلاح التلاصق الرأسي */
        .contact-image {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }
        .contact-image li {
            width: calc(33.333% - 14px);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            box-sizing: border-box;
        }
        .contact-image li img {
            width: 100%;
            height: 130px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .contact-image li p {
            font-size: 13px;
            margin: 5px 0;
        }
        .ws_quentity {
            width: 55px;
            text-align: center;
            margin: 0 5px;
        }
        @media (max-width: 768px) {
            .contact-image li {
                width: calc(50% - 10px);
            }
        }
        @media (max-width: 480px) {
            .contact-image li {
                width: 100%;
            }
        }
        /* إصلاح النموذج السفلي */
        #body form {
            clear: both;
            margin-top: 30px;
        }
        #msg_message {
            width: 100%;
            max-width: 555px;
            height: 120px;
        }
    </style>
</head>
<body>

<div id="body">
    <ul class="cb">
        <li id="wideColumn">
            <div id="h1"><h1 style="text-transform: capitalize;">إستـفسـار مجمـع</h1></div>
            <div id="breadcrumb">
                <ul>
                    <li><a href="http://egyptmart.shop/company/index.php?c=<?php echo htmlspecialchars($c); ?>">الرئيسية</a><b>»</b></li>
                    <li>إستـفسـار مجمع</li>
                </ul>
            </div>

            <?php if (count($sel_product) > 0) { ?>
                <form method="post" action="sendimagerequestmail.php">
                    <ul class="contact-image">
                        <?php
                        $sel_product_ser = serialize($sel_product);
                        foreach ($sel_product as $selpro) {
                        ?>
                            <li>
                                <img src="../upload/myproduct/<?php echo htmlspecialchars($selpro->pd_image); ?>">
                                <p>
                                    <?php echo htmlspecialchars($selpro->pd_title); ?>
                                    <br><b>أقل كمية :</b>
                                    <input type="text" name="quantity[]" class="ws_quentity" value="<?php echo htmlspecialchars($selpro->pd_min_order_qty); ?>">
                                    <br><b>الوحدة:</b> <?php echo htmlspecialchars(get_measurement_unit($selpro->pd_unit)); ?>
                                </p>
                            </li>
                        <?php } ?>
                    </ul>

                    <input type="hidden" name="sel_product_ser" value="<?php echo htmlentities($sel_product_ser); ?>">
                    <input type="hidden" name="c" value="<?php echo htmlspecialchars($c); ?>">
                    <input type="hidden" name="msg_from" value="<?php echo $_SESSION['uid_indm'] ?? 0; ?>">
                    <input type="hidden" name="msg_to" value="<?php echo $row->usr_id ?? 0; ?>">
                    <input type="hidden" name="msg_subject" value="Wholesale Business Enquiry">
                    <input type='hidden' name='email' value='<?php echo htmlspecialchars($row_own->email ?? ''); ?>'>

                    <div style="width: 100%;">
                        <div style="width: 30%; float: left">
                            <fieldset style="height: 108px; border: 1px solid rgb(134, 182, 217); margin-top: 29px; width: 190px;">
                                <legend style="font-size: 13px;color:#017BBC; text-align: center;"><strong>إوصف تفاصيل إستفسارك</strong></legend>
                                <div style="color:#055985;">
                                    <ul style="margin-left: 10px;">
                                        <li>تفاصيل المنتجات أو الخدمات</li>
                                        <li>المواصفــات المطلــوبة</li>
                                        <li>الـتعبئــة والتغــليف</li>
                                        <li>مكـان وموعـد التســليم</li>
                                    </ul>
                                </div>
                            </fieldset>
                        </div>
                        <div style="width: 70%; float: left">
                            <label style="font-size:large;"><b>: الطلب والإستفسار </b></label><br />
                            <div style="width: 555px; height: auto;">
                                <textarea id="msg_message" name="msg_message" style="width: 81%; height: 104px; overflow: auto; padding: 10px; box-sizing: border-box;"></textarea>
                            </div>
                        </div>
                    </div>

                    <input class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px" id="btn-sub" value="طلب أصناف مجمعة" style="margin-top: 20px;margin-left: 250px; background: #c30 !important;box-shadow: 0pt 1px 5px rgb(170, 170, 170); font-family: Arial,Helvetica,sans-serif; font-size: 16px; font-weight: bold; text-align: center; color: rgb(255, 255, 255); border: 1px solid rgb(24, 143, 205);border-radius:6px; padding:5px 20px; cursor:pointer;" type="button" onclick='sendEnquiry()'>
                </form>
            <?php } else { ?>
                <p style="color: red; text-align: center;">لم يتم اختيار أي منتجات. الرجاء العودة إلى صفحة المنتجات واختيار المنتجات المطلوبة.</p>
            <?php } ?>

            <div id="loading" style="display:none;padding-left:192px;color:#1045B0;padding-top:16px;">
                <img class="loading" src="../images/loading-small.gif" alt="loading" height="16" width="16"><b>... إنتظر من فضلك</b>
            </div>
            <div id="succ_result" style="display:none;padding-left:192px;color:#009700;padding-top:16px;">
                <b>... تم إرسال طلبك بنجاح</b>
            </div>
        </li>
    </ul>
</div>

<script>
function sendEnquiry() {
    $("#loading").css("display", "block");
    $.post("sendimagerequestmail.php", $("form").serialize(), function(data) {
        $("#loading").css("display", "none");
        if (data == 1) {
            $("#succ_result").css("display", "block");
        } else {
            alert("حدث خطأ، يرجى المحاولة مرة أخرى");
        }
    });
}
</script>
</body>
</html>