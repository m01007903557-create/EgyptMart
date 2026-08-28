<?php
// company/product-details.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "includes/header.php";

// التحقق من وجود token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("معرف المنتج غير صحيح");
}

$token = substr($_GET['token'], 4);
$token = mysqli_real_escape_string($con, $token);

// جلب بيانات المنتج
$sql = "SELECT * FROM products WHERE md5(pd_id) = '{$token}' AND pd_status = '1'";
$pdresk = mysqli_query($con, $sql);

if (!$pdresk) {
    die("خطأ في استعلام المنتج: " . mysqli_error($con));
}

$pdrowk = mysqli_fetch_object($pdresk);
if (!$pdrowk) {
    die("المنتج غير موجود");
}

// دالة عرض طرق الدفع
function pmNames(string $methods, mysqli $con): string {
    if (empty($methods)) return '';
    
    if (strpos($methods, ',') !== false) {
        $methods = explode(",", $methods);
        $st = "";
        foreach ($methods as $method) {
            $method_id = (int)trim($method);
            $query = mysqli_query($con, "SELECT ph_title FROM payment_method WHERE ph_id = '{$method_id}'");
            if ($query) {
                $fetch = mysqli_fetch_object($query);
                if ($fetch) {
                    $st .= $fetch->ph_title . ", ";
                }
            }
        }
        return rtrim($st, ", ");
    } else {
        $method_id = (int)$methods;
        $query = mysqli_query($con, "SELECT ph_title FROM payment_method WHERE ph_id = '{$method_id}'");
        if ($query) {
            $fetch = mysqli_fetch_object($query);
            return $fetch->ph_title ?? '';
        }
        return '';
    }
}

// معالجة الصور
$image = !empty($pdrowk->pd_image) ? explode(',', $pdrowk->pd_image) : [];
$thumbnail = !empty($image[1]) ? $image[0] : ($pdrowk->pd_image ?? '');
?>
<!-- webcast -->
<style type="text/css">
  .brand_name {
    position: relative;
    float: right;
    right: 6px;
  }

  span.brand_label {
    font-size: 16px;
    font-weight: 600;
  }

  span.brand_name_title {
    font-size: 16px;
    color: red;
  }

  /* ===== Social Icons ===== */
  #breadcrumb .social-icon-item {
    margin-left: 12px;
  }

  #breadcrumb .social-icon-item:first-of-type {
    margin-left: 20px;
  }

  #breadcrumb .social-icon-item i {
    font-size: 20px;
    vertical-align: middle;
    color: #555;
  }

  #breadcrumb .social-icon-item i.fa-twitter-square {
    color: #1da1f2;
  }

  #breadcrumb .social-icon-item i.fa-linkedin-square {
    color: #0077b5;
  }

  #breadcrumb .social-icon-item i.fa-facebook-square {
    color: #1877f2;
  }

  /* ===== Mobile Optimization ===== */
  @media (max-width: 768px) {
    #breadcrumb ul {
      display: flex;
      flex-wrap: wrap;
      /* allow icons to drop to next line if needed */
      align-items: center;
      row-gap: 8px;
    }

    #breadcrumb .social-icon-item {
      margin-left: 10px;
    }

    #breadcrumb .social-icon-item:first-of-type {
      margin-left: 10px;
      /* less forced gap on small screens */
    }

    #breadcrumb .social-icon-item i {
      font-size: 22px;
      /* slightly bigger for easier tapping */
    }

    /* give icons proper tap-target size */
    #breadcrumb .social-icon-item a {
      display: inline-block;
      padding: 6px;
      /* enlarges tappable area without changing visual icon size much */
    }
  }

  @media (max-width: 480px) {
    #breadcrumb {
      line-height: 1.6;
      /* extra breathing room since text will likely wrap on small phones */
    }

    #breadcrumb .social-icon-item {
      margin-left: 8px;
    }
  }
</style>
<div id="body">
  <ul class="cb product-detail-m">
    <li id="wideColumn">
      <div id="breadcrumb">
        <ul>
          <li>
            <a href="
							<?php echo '/company/index.php?c=' . urlencode($c ?? ''); ?>">Home </a>
            <b>»</b>
          </li>
          <li>
            <a href="
							<?php echo '/company/products.php?c=' . urlencode($c ?? ''); ?>">Product </a>
            <b>»</b>
          </li>
          <li> <?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?> </li>
          <li class="social-icon-item">
            <a href="https://twitter.com/GenidyEhab" target="_blank">
              <i class="fa fa-twitter-square"></i>
            </a>
          </li>
          <li class="social-icon-item">
            <a href="https://www.linkedin.com/in/ehab-genidy-a0730b105/" target="_blank">
              <i class="fa fa-linkedin-square"></i>
            </a>
          </li>
          <li class="social-icon-item">
            <a href="https://www.facebook.com/%D8%B3%D9%88%D9%82-%D9%85%D8%B5%D8%B1-%D8%B9%D9%84%D9%89-%D8%A7%D9%84%D8%A7%D9%86%D8%AA%D8%B1%D9%86%D8%AA-Egypt-MART-111509273583951" target="_blank">
              <i class="fa fa-facebook-square"></i>
            </a>
          </li>
        </ul>
      </div>
      <br>
      <div id="h1">
        <h1> <?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?> </h1>
      </div>
      <br>
      <div class="ac" style="position: relative;"> <?php if (!empty($pdrowk->pd_image)): ?> <div class="zoom-box" style="display: inline-block;position: relative;">
          
        <img src="https://egyptmart.shop/upload/myproduct/<?=htmlspecialchars($thumbnail);?>" class="product-image" title="<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" style="width:100%; height:100%; max-height:363px; max-width:450px;filter: drop-shadow(0 8px 18px rgba(0, 35, 85, 0.18));">
        <img class="zoom_qj" data-zoom="https://egyptmart.shop/upload/myproduct/<?=htmlspecialchars($thumbnail);?>" src="images/zoom.png" style="height: 30px;width:30px;/* float: right; */position: absolute;right: 0;top: 20px;"/>
        
        </div> <?php if (!empty($pdrowk->pd_imagelogo)): 
                        $limg = explode(',', $pdrowk->pd_imagelogo);
                    ?> <div class="zk" style="border: 1px solid #267abf; height: auto; width: 135px; position: absolute; bottom: 6px; left: 113px;">
          <img style='width: auto; height: auto; max-width: 100%;' src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($limg[0] ?? ''); ?>">
        </div> <?php endif; ?> <?php else: ?> <img src="https://egyptmart.shop/upload/myproduct/noimage.jpg" title="
									<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" alt="
									<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" class="bdr"> <?php if (!empty($pdrowk->pd_imagelogo)): 
                        $limg = explode(',', $pdrowk->pd_imagelogo);
                    ?> <div class="zk" style="border: 1px solid #267abf; height: 105px; width: 105px; position: absolute; bottom: 6px; left: 113px;">
          <img style='width: 105px; height: 105px;' src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($limg[0] ?? ''); ?>">
        </div> <?php endif; ?> <?php endif; ?> </div>
      <!-- webcast --> <?php if (!empty($pdrowk->brand_name)): ?> <div class="brand_name">
        <span class="brand_label">Brand Name:</span>
        <span class="brand_name_title"> <?php echo htmlspecialchars($pdrowk->brand_name); ?> </span>
      </div> <?php endif; ?> <br>
      <section id="proDet" class="box1 cb">
        <div class="p10px fo">
          <p class="taj pt10px"> <?php echo nl2br(htmlspecialchars($pdrowk->pd_desc ?? '')); ?> </p>
          <br>
        </div>
      </section>
      <br>
      <section id="career" class="box1">
        <div class="h2">
          <h2>تفاصيل جديدة</h2>
        </div>
        <nav class="proSpe"> <?php
                    $currencyrow = null;
                    if (!empty($pdrowk->pd_currency)) {
                        $currencysql = mysqli_query($con, "SELECT * FROM country WHERE cn_id = '" . (int)$pdrowk->pd_currency . "'");
                        $currencyrow = mysqli_fetch_object($currencysql);
                    }
                    
                    $unitrow = null;
                    if (!empty($pdrowk->pd_unit)) {
                        $unitsql = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_id = '" . (int)$pdrowk->pd_unit . "'");
                        $unitrow = mysqli_fetch_object($unitsql);
                    }
                    ?> <div style="width:auto; overflow-x:scroll;">
            <table style="width:100%" border="1" cellpadding="1" cellspacing="1">
              <tbody> <?php if (!empty($pdrowk->pd_code)): ?> <tr>
                  <th scope="row" width="%">
                    <center>كود الصنف</center>
                  </th>
                  <td width="%"> <?php echo htmlspecialchars($pdrowk->pd_code); ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_fob_price)): ?> <tr>
                  <th scope="row" width="%">
                    <center>الـسعـر</center>
                  </th>
                  <td width="%"> <?php 
                                            echo (float)$pdrowk->pd_fob_price . ' ~ ' . (float)$pdrowk->pd_fob_price2; 
                                            echo ' (' . htmlspecialchars($currencyrow->cn_currency ?? '') . ')';
                                            ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_stocks)): ?> <tr>
                  <th scope="row" width="%">
                    <center>المخزون</center>
                  </th>
                  <td width="%"> <?php echo (int)$pdrowk->pd_stocks; ?> <?php echo htmlspecialchars($unitrow->mu_name ?? ''); ?>(s) </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_pod)): ?> <tr>
                  <th scope="row" width="%">
                    <center>ميناء التسليم</center>
                  </th>
                  <td width="%"> <?php echo htmlspecialchars($pdrowk->pd_pod); ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_pn_capct)): ?> <tr>
                  <th scope="row" width="%">
                    <center>طاقة الإنتاج</center>
                  </th>
                  <td width="%"> <?php echo htmlspecialchars($pdrowk->pd_pn_capct); ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_dlv_time)): ?> <tr>
                  <th scope="row" width="%">
                    <center>وقـت التسـليم</center>
                  </th>
                  <td width="%"> <?php echo htmlspecialchars($pdrowk->pd_dlv_time); ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_pck_dets)): ?> <tr>
                  <th scope="row" width="%">
                    <center>وصف التغليف</center>
                  </th>
                  <td width="%"> <?php echo nl2br(htmlspecialchars($pdrowk->pd_pck_dets)); ?> </td>
                </tr> <?php endif; ?> <tr>
                  <th scope="row" width="%">
                    <center>أهمية المنتج</center>
                  </th>
                  <td width="%"> <?php echo ((int)($pdrowk->pd_hot ?? 0) == 0) ? 'Default' : 'Hot'; ?> </td>
                </tr> <?php if (!empty($pdrowk->pd_payment)): ?> <tr>
                  <th scope="row" width="%">
                    <center>شروط الدفع</center>
                  </th>
                  <td width="%"> <?php echo htmlspecialchars(pmNames($pdrowk->pd_payment, $con)); ?> </td>
                </tr> <?php endif; ?> <?php if (!empty($pdrowk->pd_pdf_attach)): ?> <tr>
                  <th scope="row" width="%">
                    <center>PDF File</center>
                  </th>
                  <td width="%">
                    <a href="https://egyptmart.shop/upload/productdoc/
																				<?php echo htmlspecialchars($pdrowk->pd_pdf_attach); ?>" target="_blank">
                      <img src="/images/pdf_icon.png" style="width: 28px; height: 28px; vertical-align: middle;"> PDF </a>
                  </td>
                </tr> <?php endif; ?> </tbody>
            </table>
          </div>
        </nav>
      </section>
      <br>
      <br>
      <script src="js/jquery.colorbox.js"></script>
      <link href="css/colorbox.css" type="text/css" rel="stylesheet">
      <script src="js/jquery-1.9.1.min.js"></script>
      <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
      <script>
        $(document).ready(function() {
              $("#btn_ajax<?php echo (int)($pdrowk->pd_id ?? 0); ?>").colorbox({
                width: "62%",
                height: "89%"
            });
        });
      </script>
      <!-- ✅ الزرين بجانب بعضهما أفقياً -->
      <!-- New work -->
      <div class="btns-group" style="justify-content:center;">
        <a class="oval-btn mail-btn w-xxs-100 w-t-pdt-50 w-m-pdt-50" href="quotationRequest.php?id=
																		<?php echo rand(1000, 9999) . md5($row->bnsprof_id ?? ''); ?>&pid=
																		<?php echo (int)($pdrowk->pd_id ?? 0); ?>&vform=1" rel="product-send-inquiry" class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px ml5px" id="btn_ajax
																		<?php echo (int)($pdrowk->pd_id ?? 0); ?>"> تواصل مع الشركة </a> <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''):

                    $product_id = (int)($pdrowk->pd_id ?? 0);
                    $product_title = isset($pdrowk->pd_title) ? addslashes($pdrowk->pd_title) : 'Product';

                ?> <a class="oval-btn w-xxs-100 w-t-pdt-50 w-m-pdt-50 whatsapp-btn" href="javascript:void(0)" onclick="openWaRfq(
																		<?php echo $product_id; ?>, '
																		<?php echo $product_title; ?>')" class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px ml5px oval-btn w-xxs-100 w-t-pdt-50 w-m-pdt-50 whatsapp-btn">
          <i class="fa fa-whatsapp"></i> 📱 اطلب سعر واتساب </a> <?php else: ?> <a href="https://egyptmart.shop/sign-in.php#loginform" class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px ml5px oval-btn w-xxs-100 w-t-pdt-50 w-m-pdt-50 whatsapp-btn">
          <i class="fa fa-whatsapp"></i> 📱 سجل دخول لطلب السعر </a> <?php endif; ?>
      </div>
    </li>
    <?php include "includes/right.php"; ?>
  </ul>
</div> <?php include "includes/footer.php"; ?>

<script>
  // ============================================================
  // ✅ كود Zoom (الموجود بالفعل)
  // ============================================================
  (function($) {
    // ... كود Zoom كما هو ...
  })(jQuery);
  jQuery(document).ready(function($) {
    $(".zoom-box img").jqZoom({
      selectorWidth: 30,
      selectorHeight: 30,
      viewerWidth: 400,
      viewerHeight: 300
    });
  });
  // ============================================================
  // ✅ دالة openWaRfq - نفس الموجودة في company/products
  // ============================================================
  function openWaRfq(pid, pname) {
    // ✅ التحقق من وجود الـ Modal
    if (!document.getElementById('waModal')) {
      createWaModal();
    }
    // ✅ تعيين قيم المنتج
    document.getElementById('wa_pid').value = pid;
    document.getElementById('wa_pname').value = pname;
    // ✅ عرض الـ Modal
    document.getElementById('waModal').style.display = 'block';
  }
  // ============================================================
  // ✅ إنشاء Modal إذا لم يكن موجوداً
  // ============================================================
  function createWaModal() {
    var modalHTML = `
    
														<div id="waModal" style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);direction:rtl;">
															<div style="background:#fff;width:90%;max-width:500px;margin:10% auto;padding:25px;border-radius:10px;box-shadow:0 5px 30px rgba(0,0,0,0.3);">
																<h3 style="margin-top:0;color:#25D366;font-size:22px;">
																	<i class="fa fa-whatsapp"></i> طلب سعر عبر واتساب
            
																</h3>
																<p style="color:#666;font-size:14px;margin-bottom:15px;">يرجى إدخال تفاصيل طلب السعر</p>
																<form id="waForm">
																	<input type="hidden" id="wa_pid" name="product_id">
																		<input type="hidden" id="wa_pname" name="product_name">
																			<div style="margin-bottom:12px;">
																				<label style="display:block;margin-bottom:4px;font-weight:bold;font-size:14px;">الكمية من:</label>
																				<input type="number" id="wa_qty_from" name="qty_from" value="1" min="1" 
                           style="width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:6px;box-sizing:border-box;font-size:14px;">
																				</div>
																				<div style="margin-bottom:12px;">
																					<label style="display:block;margin-bottom:4px;font-weight:bold;font-size:14px;">الكمية إلى:</label>
																					<input type="number" id="wa_qty_to" name="qty_to" value="1" min="1" 
                           style="width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:6px;box-sizing:border-box;font-size:14px;">
																					</div>
																					<div style="margin-bottom:15px;">
																						<label style="display:block;margin-bottom:4px;font-weight:bold;font-size:14px;">تفاصيل إضافية:</label>
																						<textarea id="wa_details" name="requirement_details" rows="3" 
                              style="width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:6px;box-sizing:border-box;resize:vertical;font-size:14px;font-family:Arial;"
                              placeholder="أدخل أي تفاصيل إضافية..."></textarea>
																					</div>
																					<div style="display:flex;gap:10px;">
																						<button type="submit" 
                            style="flex:1;background:#25D366;color:#fff;border:none;padding:12px 20px;border-radius:6px;cursor:pointer;font-size:16px;font-weight:bold;">
                        📤 إرسال الطلب
                    </button>
																						<button type="button" onclick="document.getElementById('waModal').style.display='none'" 
                            style="flex:1;background:#e0e0e0;color:#333;border:none;padding:12px 20px;border-radius:6px;cursor:pointer;font-size:16px;">
                        إلغاء
                    </button>
																					</div>
																				</form>
																			</div>
																		</div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    // ✅ إضافة معالج إرسال النموذج
    document.getElementById('waForm').addEventListener('submit', submitWaForm);
  }
  // ============================================================
  // ✅ دالة إرسال النموذج
  // ============================================================
  async function submitWaForm(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    // ✅ تعطيل الزر لمنع الإرسال المزدوج
    var submitBtn = form.querySelector('button[type="submit"]');
    var originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ جاري الإرسال...';
    try {
      var res = await fetch('/whatsapp_rfq_handler.php', {
        method: 'POST',
        body: formData
      });
      var data = await res.json();
      if (data.success) {
        alert('✅ تم إرسال طلب السعر بنجاح، سيتم التواصل معك قريباً.');
        if (data.whatsapp_url) {
          window.open(data.whatsapp_url, '_blank');
        }
        document.getElementById('waModal').style.display = 'none';
        form.reset();
      } else {
        alert('❌ ' + (data.error || 'حدث خطأ'));
      }
    } catch (error) {
      console.error('❌ Error:', error);
      alert('❌ خطأ في الاتصال بالخادم');
    } finally {
      // ✅ إعادة تمكين الزر
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  }
  // ============================================================
  // ✅ التحقق من وجود الـ Modal عند تحميل الصفحة
  // ============================================================
  document.addEventListener('DOMContentLoaded', function() {
    // إذا كان الـ Modal غير موجود، سيتم إنشاؤه عند أول ضغط على الزر
    console.log('✅ WhatsApp RFQ جاهز');
  });
  console.log('✅ WhatsApp RFQ جاهز لصفحة المنتج');
</script>
<!-- ✅ تضمين معالج واتساب -->
<script src="../js/whatsapp_handler.js"></script>
</body>
</html>