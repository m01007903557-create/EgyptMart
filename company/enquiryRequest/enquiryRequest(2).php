<?php
ob_start(); // أضف هذا السطر في البداية

// تضمين الهيدر والتحقق من تسجيل الدخول
include "includes/header.php";

$user = $_SESSION['uid_indm'] ?? 0;
if ($user == 0) {
    header("Location: ../sign-in.php");
    exit;
}

$c = $_GET['c'] ?? '';
$cid = substr($c, 4);

// جلب بيانات المستخدم الحالي
$uid_indm = $_SESSION['uid_indm'] ?? 0;
$sql_own = "select * from user, business_profile where usr_id='$uid_indm' and bnsprof_uid=usr_id limit 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

// استرجاع الصور والمعرفات من الجلسة
$images = [];
$ids = [];
for ($i = 1; $i <= 20; $i++) {
    $images[$i] = $_SESSION["image$i"] ?? '';
    $ids[$i] = $_SESSION["id$i"] ?? 0;
}

// بناء قائمة المعرفات للاستعلام
$id_list = implode(',', array_filter($ids));
if (empty($id_list)) {
    $id_list = '0';
}

// جلب المنتجات المختارة
$sel_product = [];
$sel_pro = "select * from products where pd_uid='" . ($row->usr_id ?? 0) . "' and pd_id IN ($id_list)";
$s_prod = mysqli_query($con, $sel_pro);
while ($select_product = mysqli_fetch_object($s_prod)) {
    $sel_product[] = $select_product;
    $sel_product_image[] = $select_product->pd_image;
}
$_SESSION['selimage'] = $sel_product_image ?? [];
?>

<div id="body">
    <ul class="cb">
        <li id="wideColumn">
            <div id="h1"><h1 style="text-transform: capitalize;" title="Wholesale Contact">إستـفسـار مجمـع </h1></div>
            <div id="breadcrumb">
                <ul>
                    <li><a href="http://egyptmart.shop/company/index.php?c=<?php echo htmlspecialchars($c); ?>" id="myDiv">الرئيسية</a><b>»</b></li>
                    <li>إستـفسـار مجمع </li>
                </ul>
            </div>
            <br><br>

            <form method="post" action="sendimagerequestmail.php">
                <ul class="contact-image">
                    <?php
                    $sel_product_ser = serialize($sel_product);
                    foreach ($sel_product as $selpro) {
                        ?>
                        <li style="height:50px; width:33%;">
                            <img style="width:170px; margin-left: 26px;" src="../upload/myproduct/<?php echo htmlspecialchars($selpro->pd_image); ?>">
                            <p style="text-align: center;">
                                <?php echo htmlspecialchars($selpro->pd_title); ?>
                                <br>
                                <b> أقل كمية :</b>
                                <input type="text" name="quantity[]" class="ws_quentity" value="<?php echo htmlspecialchars($selpro->pd_min_order_qty); ?>" style="width: 55px; display: inline-block;"/>
                                <br>
                                <b>الوحدة:</b> <?php echo htmlspecialchars(get_measurement_unit($selpro->pd_unit)); ?>
                            </p>
                        </li> 
                    <?php } ?>						
                </ul>
               <style>
.contact-image {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 20px;
    list-style: none;
    padding: 0;
    margin: 0 0 30px 0;
}
.contact-image li {
    width: calc(33% - 20px);
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: auto;
    min-height: 320px;
    box-sizing: border-box;
}
.contact-image li img {
    width: 100%;
    height: 150px;
    object-fit: contain;
    border-radius: 5px;
    display: block;
    margin-bottom: 10px;
}
.contact-image li p {
    font-size: 14px;
    margin: 5px 0;
    flex-grow: 1;
}
.ws_quentity {
    width: 60px;
    text-align: center;
    margin: 0 5px;
}
/* النموذج السفلي */
#body form {
    clear: both;
    margin-top: 30px;
    display: block;
    width: 100%;
}
#msg_message {
    width: 100%;
    max-width: 555px;
    height: 120px;
}
@media (max-width: 768px) {
    .contact-image li {
        width: calc(50% - 20px);
    }
}
@media (max-width: 480px) {
    .contact-image li {
        width: 100%;
    }
}
</style>
                
                <input type="hidden" name="sel_product_ser" value="<?php echo htmlentities($sel_product_ser); ?>" />
                <input type="hidden" name="c" value="<?php echo htmlspecialchars($c); ?>" />
                <input type="hidden" name="msg_from" value="<?php echo $_SESSION['uid_indm'] ?? 0; ?>" />
                <input type="hidden" name="msg_to" value="<?php echo $row->usr_id ?? 0; ?>" />
                <input type="hidden" name="msg_subject" value="Wholesale Business Enquiry" />
                <input type='hidden' name='email' value='<?php echo htmlspecialchars($row_own->email ?? ''); ?>'>

                <div style="width: 100%;">
                    <div style="width: 30%; float: left">
                        <fieldset style="height: 108px; border: 1px solid rgb(134, 182, 217); margin-top: 29px; width: 190px;">
                            <legend style="font-size: 13px;color:#017BBC; text-align: center;" title="Describe your requirements"><strong>إوصف تفاصيل إستفسارك</strong></legend>
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
                        <label style="font-size:large;"><b>: الطلب والإستفسار </b></label><br />
                        <div style="width: 555px; height: auto;">
                            <textarea id="msg_message" name="msg_message" style="width: 81%; height: 104px; overflow: auto; padding: 10px; box-sizing: border-box;"></textarea>
                        </div>
                    </div>
                </div>

                <input class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px" id="btn-sub" value="طلب أصناف مجمعة" style="margin-top: 20px;margin-left: 250px; background: #c30 !important;box-shadow: 0pt 1px 5px rgb(170, 170, 170); font-family: Arial,Helvetica,sans-serif; font-size: 16px; font-weight: bold; text-align: center; color: rgb(255, 255, 255); border: 1px solid rgb(24, 143, 205);border-radius:6px; padding:5px 20px; cursor:pointer;" type="button" onclick='sendEnquiry()'>
            </form>

            <div id="loading" style="display:none;padding-left:192px;color:#1045B0;padding-top:16px;" class="g9 bo off">
                <img class="loading" src="../images/loading-small.gif" alt="loading" height="16" width="16"><b>... إنتظر من فضلك </b>
            </div>	
            <div id="succ_result" style="display:none;padding-left:192px;color:#009700;padding-top:16px;" class="g9 bo off">
                <b>... تم إرسال طلبك بنجاح </b>
            </div>
        </li>
   
           
           
           
           
           
           
           
           
           
        <?php include "includes/right.php"; ?>
    </ul>
</div><BR><BR>
<?php include "includes/footer.php"; ?>
</body></html>
<style>
    #btn-sub{
        background:#017601;*zoom:1;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#017601', EndColorStr='#059d05');
        background:-webkit-gradient(linear, 0 0, 0 bottom, from(#017601), to(#059d05));
        background:-webkit-linear-gradient(#017601, #059d05);
        background:   -moz-linear-gradient(#017601, #059d05);
        background:    -ms-linear-gradient(#017601, #059d05);
        background:     -o-linear-gradient(#017601, #059d05);
        background:        linear-gradient(#017601, #059d05);
    }
</style>
    
    <script>
    function sendEnquiry() {
        var quentity = [];
        var i = 0;
        $(".ws_quentity").each(function () {
            quentity[i] = $(this).val();
            i++;
        });

        var msg_from = document.getElementById('msg_from').value;
        var msg_to = document.getElementById('msg_to').value;
        var msg_subject = document.getElementById('msg_subject').value;
        var sel_product_ser = document.getElementById('sel_product_ser').value;
        var c = document.getElementById('c').value;
        var msg_message = document.getElementById('msg_message').value;



        $("#btn-sub").css("display", "none");
        $("#loading").css("display", "block");

        $.post("../company/sendimagerequestmail.php", {msg_from: msg_from, msg_image: sel_product_ser, msg_to: msg_to, msg_subject: msg_subject, msg_message: msg_message, quentity: quentity}, function (data) {
            if (data == 1)
            {
                setTimeout(function () {
                    $("#loading").css("display", "none");
                    $("#succ_result").css("display", "block");
                }, 500);
            } else
            {
                setTimeout(function () {
                    $("#loading").css("display", "none");
                    $("#err_result").css("display", "block");
                }, 500);
            }
        });

    }

</script>
</body>
</html>