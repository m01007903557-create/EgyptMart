<?php
// ✅ الحفاظ على التعريف القديم
define('ACCESS_ALLOWED', true);

// ✅ بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ السماح بطلبات AJAX من نفس الموقع
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // هذا طلب AJAX، استمر دون التحقق الإضافي
} else {
    // طلب عادي، تأكد من الإحالة
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        die("Direct Access Not Allowed");
    }
}

// تضمين الملفات
require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

// تعريف ملف القائمة الجانبية
$file = "whatsapp_rfq";

// بدء المخزن المؤقت
ob_start();

// تضمين الملفات الأساسية
require_once "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// تضمين دوال WhatsApp RFQ
//include "includes/whatsapp_rfq_functions.php";
include "includes/wa_functions_simple.php";

// Pagination variables
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب البيانات
$total = get_whatsapp_rfq_count($search, $status);
$total_pages = ceil($total / $limit);
$results = get_whatsapp_rfq_list($start, $limit, $search, $status);

// معالجة الإجراءات
if (isset($_POST['action']) && isset($_POST['br_ids'])) {
    $br_ids = $_POST['br_ids'];
    if ($_POST['action'] == 'delete') {
        foreach ($br_ids as $id) delete_whatsapp_rfq($id);
        echo '<div class="alert alert-success">تم حذف الطلبات المحددة</div>';
    }
    if ($_POST['action'] == 'publish') {
        foreach ($br_ids as $id) publish_to_public_rfq($id);
        echo '<div class="alert alert-success">تم نشر الطلبات المحددة</div>';
    }
}
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' ,'fixed');}catch(e){}
    </script>
    
    
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed');}catch(e){}
                </script>
                
                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="whatsapp_rfq.php">WhatsApp RFQ</a>
                    </li>
                    <li class="active">جميع الطلبات</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="fa fa-whatsapp" style="color:#25D366;"></i>
                        طلبات WhatsApp RFQ
                        <small class="text-muted">نظام مستقل - غير مرتبط بالإيميل</small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <!-- شريط البحث والفلتر -->
                        <div class="well well-sm">
                            <form method="GET" class="form-inline">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" placeholder="بحث: RFQ ID, منتج, تفاصيل" value="<?php echo htmlspecialchars($search); ?>" style="width:300px;">
                                </div>
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="all">جميع الحالات</option>
                                        <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>قيد الانتظار</option>
                                        <option value="sent_to_supplier" <?php echo $status=='sent_to_supplier'?'selected':''; ?>>تم الإرسال للمورد</option>
                                        <option value="waiting_response" <?php echo $status=='waiting_response'?'selected':''; ?>>في انتظار الرد</option>
                                        <option value="closed" <?php echo $status=='closed'?'selected':''; ?>>مغلق</option>
                                        <option value="cancelled" <?php echo $status=='cancelled'?'selected':''; ?>>ملغي</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">بحث</button>
                                <a href="whatsapp_rfq.php" class="btn btn-default">إعادة تعيين</a>
                            </form>
                        </div>
                        
                        <!-- نموذج الإجراءات الجماعية -->
                        <form method="POST" id="bulkForm">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
    <tr>
        <th width="30"><input type="checkbox" id="checkAll"></th>
        <th>RFQ ID</th>
        <th>التاريخ</th>
        <th>الصورة</th>
        <th>عنوان المنتج</th>
        <th style="width: 5%;">الوصف</th>
        <th>المورد</th>
        <th>المشتري</th>
        <th>الكمية</th>
        <th>الدولة / المحافظة</th>
        <th>نوع العضوية</th>
        <th>الحالة</th>
        <th width="150">إجراءات</th>
    </tr>
</thead>
                                    <tbody>
    <?php if(mysqli_num_rows($results) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($results)): ?>
  


<!-- Debug: RFQ ID <?php echo $row['br_id']; ?> -> Mobile: <?php 
$debug_sql = mysqli_query($con, "SELECT u.mobile1 
    FROM buy_requirement br 
    LEFT JOIN products p ON br.br_pc_id = p.pd_id 
    LEFT JOIN user u ON p.pd_uid = u.usr_id 
    WHERE br.br_id = " . (int)$row['br_id']);
$debug_row = mysqli_fetch_assoc($debug_sql);
echo $debug_row['mobile1'] ?? 'غير موجود';
?> -->

<tr>
    <td class="center"><input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>"></td>
    <td class="center"><?php echo $row['br_id']; ?></td>
    <!-- باقي الأعمدة ... -->
</tr>
    
    
    <tr>
        <td><input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>"></td>
        <td><?php echo $row['br_id']; ?></td>
        <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
       <td class="center">
    <?php 
    $img = !empty($row['pd_image']) ? explode(',', $row['pd_image'])[0] : 'noimage.jpg';
    echo '<img src="../upload/myproduct/' . $img . '" width="50" height="50" style="object-fit:cover;">';
    ?>
</td>
       
       <td><?php echo htmlspecialchars($row['pd_title'] ?? $row['br_pd_name']); ?></td>
        <td><?php echo mb_substr(htmlspecialchars($row['br_requirement']), 0, 30); ?>...</td>
        
     
    
    
    
    
 <td class="center">
    <?php 
    $product_id = $row['br_pc_id'] ?? 0;
    $sup_sql = mysqli_query($con, "SELECT bp.bnsprof_compname 
                                   FROM products p 
                                   LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid 
                                   WHERE p.pd_id = $product_id");
    $sup_row = mysqli_fetch_assoc($sup_sql);
    echo htmlspecialchars($sup_row['bnsprof_compname'] ?? 'غير محدد');
    ?>
</td>
      
      
      
      
      
        
        
        <td><?php echo htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')); ?></td>
        <td><?php echo $row['br_estimate_qty'] . ' ' . $row['br_estimate_qty_unit']; ?></td>
      <td class="center">
    <?php 
    $country = $row['country_name'] ?? 'غير محدد';
    $city = $row['city_name'] ?? 'غير محدد';
    echo htmlspecialchars($country . ' - ' . $city); 
    ?>
</td>
        <td>
            <?php
            // نوع العضوية
            echo '<span class="label label-default">عضوية عادية</span>';
            ?>
        </td>
        <td>
            <select class="form-control status-select" data-id="<?php echo $row['br_id']; ?>" style="width:130px;">
                <option value="pending" <?php echo $row['wa_status']=='pending'?'selected':''; ?>>قيد الانتظار</option>
                <option value="sent_to_supplier" <?php echo $row['wa_status']=='sent_to_supplier'?'selected':''; ?>>تم الإرسال</option>
                <option value="waiting_response" <?php echo $row['wa_status']=='waiting_response'?'selected':''; ?>>انتظار رد</option>
                <option value="closed" <?php echo $row['wa_status']=='closed'?'selected':''; ?>>مغلق</option>
                <option value="cancelled" <?php echo $row['wa_status']=='cancelled'?'selected':''; ?>>ملغي</option>
            </select>
            <?php if(($row['wa_sent_count'] ?? 0) > 0): ?>
            <small class="text-muted">أرسل <?php echo $row['wa_sent_count']; ?> مرة</small>
            <?php endif; ?>
        </td>
        <td>
            
      
      
      
    <div>
    <button class="btn btn-info btn-sm view-rfq" data-id="<?php echo $row['br_id']; ?>" title="عرض"><i class="fa fa-eye"></i></button>
    
    <?php
    // التحقق من وجود عرض مقبول لهذا الطلب
    $check_accepted = mysqli_query($con, "SELECT status FROM offers WHERE rfq_id = {$row['br_id']} AND status = 'accepted' LIMIT 1");
    $is_accepted = (mysqli_num_rows($check_accepted) > 0);
    ?>
    
    <?php if (!$is_accepted): ?>
        <!-- الزر القديم (Send to Supplier) -->
        <button class="btn btn-primary btn-sm send-supplier" data-id="<?php echo $row['br_id']; ?>" title="إرسال للمورد"><i class="fa fa-paper-plane"></i></button>
        
        <!-- زر واتساب الجديد -->
       <div class="btn-group btn-group-xs">
    <!-- زر واتساب (موجود) -->
    <button class="btn btn-success btn-sm" 
        onclick="sendAndWhatsApp(<?php echo $row['br_id']; ?>)"
        title="إرسال الطلب للمورد وفتح واتساب">
    <i class="fa fa-paper-plane"></i> <i class="fa fa-whatsapp"></i> إرسال وواتساب
</button>
</div>
        
        
        
        
    <?php else: ?>
        <button class="btn btn-default btn-sm" disabled title="تم قبول العرض مسبقاً">
            <i class="fa fa-check"></i> تم القبول
        </button>
    <?php endif; ?>
    
    <button class="btn btn-warning btn-sm similar-suppliers" data-id="<?php echo $row['br_id']; ?>" title="موردين مشابهين"><i class="fa fa-users"></i></button>
    <button class="btn btn-danger btn-sm" onclick="deleteRfq(<?php echo $row['br_id']; ?>)" title="حذف"><i class="fa fa-trash"></i></button>
</div>


        </td>
    </tr>
    <?php endwhile; ?>
    <?php else: ?>
    <tr>
        <td colspan="12" class="text-center">لا توجد طلبات WhatsApp RFQ</td>
    </tr>
    <?php endif; ?>
</tbody>
                               
                               
                                </table>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="action" class="form-control" style="width:200px; display:inline-block;">
                                        <option value="">-- إجراءات جماعية --</option>
                                        <option value="delete">حذف المحدد</option>
                                        <option value="publish">نشر في الصفحة العامة</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">تنفيذ</button>
                                </div>
                                <div class="col-md-6 text-right">
                                    إجمالي الطلبات: <strong><?php echo $total; ?></strong>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Pagination -->
                        <div class="text-center">
                            <ul class="pagination">
                                <?php if($page > 1): ?>
                                <li><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">&laquo; السابق</a></li>
                                <?php endif; ?>
                                <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <li class="<?php echo $i==$page?'active':''; ?>"><a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <?php if($page < $total_pages): ?>
                                <li><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">التالي &raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                            <div class="form-inline">
                                <label>انتقل إلى صفحة:</label>
                                <input type="number" id="goToPage" min="1" max="<?php echo $total_pages; ?>" class="form-control" style="width:70px;">
                                <button class="btn btn-default" onclick="window.location='?page='+document.getElementById('goToPage').value+'&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>'">Go</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal عرض التفاصيل -->
<div class="modal fade" id="rfqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">تفاصيل طلب RFQ <span id="modalRfqId"></span></h4>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal الموردين المشابهين -->
<div class="modal fade" id="similarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">إرسال إلى موردين مشابهين</h4>
            </div>
            <div class="modal-body" id="similarBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" id="sendToSimilarBtn">إرسال للمحددين</button>
            </div>
        </div>
    </div>
</div>


<script>
// JavaScript خالص بدون jQuery
document.addEventListener('DOMContentLoaded', function() {
    // زر إرسال للمورد
    // إرسال الطلب للمورد - نسخة تعمل 100%
// إرسال الطلب للمورد - فتح الرابط في نافذة جديدة
// إرسال الطلب للمورد عبر واتساب (النسخة النهائية)
$('.send-supplier').click(function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    
    if (!id) {
        alert('خطأ: لا يوجد معرف للمنتج');
        return;
    }
    
    if (!confirm('سيتم فتح واتساب للمورد مع رسالة جاهزة. هل تريد المتابعة؟')) return;
    
    var btn = $(this);
    var originalHtml = btn.html();
    btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    
    // استخدام الرابط الذي نجح معنا
    fetch('/admin/send_wa_final_working.php?rfq_id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
                alert('✓ تم فتح واتساب. قم بإرسال الرسالة.');
                // يمكنك إعادة تحميل الصفحة أو تحديث حالة الطلب إذا أردت
                // location.reload(); 
            } else {
                alert('❌ حدث خطأ: ' + (data.error || 'لم يتم إنشاء الرابط'));
            }
            btn.html(originalHtml).prop('disabled', false);
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('❌ حدث خطأ في الاتصال بالخادم');
            btn.html(originalHtml).prop('disabled', false);
        });
});


        if(data.success && data.whatsapp_url) {
            // فتح رابط واتساب في نافذة جديدة
            window.open(data.whatsapp_url, '_blank');
            alert('✓ تم تجهيز الرسالة. سيتم فتح واتساب. أرسل الرسالة من هاتفك.');
        } else if(data.success) {
            alert('✓ تم إرسال الطلب للمورد بنجاح (ولكن لم يتم إنشاء رابط واتساب).');
        } else {
            alert('❌ فشل الإرسال: ' + (data.error || 'خطأ غير معروف'));
        }
        location.reload(); // إعادة تحميل الصفحة لتحديث الحالة
    })
    .fail(function(xhr, status, error) {
        console.error("خطأ في AJAX:", status, error);
        alert('حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
        btn.html(originalHtml).prop('disabled', false);
    });
});
</script>

<script>
// JavaScript خالص - بدون jQuery
document.addEventListener('DOMContentLoaded', function() {
    // البحث عن جميع أزرار send-supplier
    var buttons = document.querySelectorAll('.send-supplier');
    console.log('عدد الأزرار:', buttons.length);
    
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            console.log('RFQ ID:', id);
            
            if(!id) {
                alert('خطأ: لا يوجد معرف للمنتج');
                return;
            }
            
            if(!confirm('إرسال هذا الطلب للمورد؟')) return;
            
            var originalHtml = this.innerHTML;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch('/admin/send_whatsapp_rfq_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'rfq_id=' + id
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if(data.success) {
                    alert('✓ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.error);
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                }
            }.bind(this))
            .catch(function(error) {
                alert('خطأ في الاتصال: ' + error);
                this.innerHTML = originalHtml;
                this.disabled = false;
            }.bind(this));
        });
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// انتظر تحميل الصفحة
setTimeout(function() {
    // إضافة حدث النقر لجميع أزرار send-supplier
    var buttons = document.querySelectorAll('.send-supplier');
    console.log('عدد الأزرار:', buttons.length);
    
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].onclick = function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            
            if(!id) {
                alert('خطأ: لا يوجد معرف');
                return;
            }
            
            if(!confirm('إرسال الطلب للمورد؟')) return;
            
            var btn = this;
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            fetch('/admin/send_whatsapp_rfq_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'rfq_id=' + id
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if(data.success) {
                    alert('✓ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.error);
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(function(err) {
                alert('خطأ: ' + err);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        };
    }
}, 1000);
</script>
<script>
function openWhatsapp(rfqId) {
    // فتح الرابط مباشرة
    var url = '/admin/send_wa_final_working.php?rfq_id=' + rfqId;
    
    // نستخدم fetch للحصول على رابط واتساب من الملف
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.whatsapp_url) {
                // فتح واتساب في تبويب جديد
                window.open(data.whatsapp_url, '_blank');
            } else {
                alert('خطأ: ' + (data.error || 'لا يمكن إنشاء الرابط'));
            }
        })
        .catch(error => {
            console.error('خطأ:', error);
            alert('حدث خطأ في الاتصال');
        });
}
</script>
<script>

        
        
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('❌ حدث خطأ في الاتصال بالخادم');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}
</script>

<script>

<script>
function sendToSupplier(rfqId) {
    if (!rfqId) {
        alert('خطأ: لا يوجد معرف للمنتج');
        return;
    }
    window.open('/admin/wa_new.php?rfq_id=' + rfqId, '_blank');
}
</script>

<!-- Modal واتساب الذكي -->
<!-- Modal واتساب الذكي -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:450px; max-width:90%; margin:100px auto; padding:25px; border-radius:12px; direction:rtl; box-shadow:0 5px 20px rgba(0,0,0,0.3);">
        <span onclick="closeWaModal()" style="float:left; cursor:pointer; font-size:24px; color:#888;">&times;</span>
        <h3 style="color:#25D366; margin-top:0; margin-bottom:20px;">
            <i class="fa fa-whatsapp"></i> إرسال طلب للمورد
        </h3>
        
        <!-- منطقة الرسالة القابلة للنسخ -->
        <label style="font-weight:bold; display:block; margin-bottom:8px;">نص الرسالة:</label>
        <textarea id="waMessage" rows="5" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-family:monospace; font-size:13px; background:#f9f9f9; resize:vertical;" readonly></textarea>
        
        <div style="margin:15px 0;">
            <button onclick="copyWaMessage()" style="background:#2196F3; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; margin-left:10px;">
                <i class="fa fa-copy"></i> نسخ النص
            </button>
            <a id="waLinkBtn" href="#" target="_blank" style="background:#25D366; color:white; text-decoration:none; padding:8px 15px; border-radius:6px; display:inline-block;">
                <i class="fa fa-whatsapp"></i> فتح واتساب
            </a>
        </div>
        
        <!-- تنبيه للكمبيوتر -->
        <div id="waDesktopAlert" style="display:none; background:#FFF3CD; padding:10px; border-radius:8px; margin-top:15px; border-right:4px solid #FFC107;">
            <small style="color:#856404;">
                <i class="fa fa-info-circle"></i> <strong>ملاحظة:</strong> على الكمبيوتر، واتساب ويب لا يحمل الرسالة تلقائياً.<br>
                اضغط <strong>"نسخ النص"</strong>، ثم بعد فتح واتساب اضغط <strong>Ctrl+V (لصق)</strong> ثم إرسال.
            </small>
        </div>
        
        <!-- إشارة للموبايل -->
        <div id="waMobileAlert" style="display:none; background:#E8F5E9; padding:10px; border-radius:8px; margin-top:15px; border-right:4px solid #25D366;">
            <small style="color:#2E7D32;">
                <i class="fa fa-check-circle"></i> الرسالة ستظهر تلقائياً في واتساب. اضغط إرسال.
            </small>
        </div>
    </div>
</div>

<script>
let currentWaPhone = '';
let currentWaMessage = '';

function openWaModal(rfqId, productName, quantity) {
    // منع أي إغلاق تلقائي
    event.stopPropagation();
    event.preventDefault();
    
    // جلب البيانات من السيرفر
    fetch('/admin/get_wa_data.php?rfq_id=' + rfqId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentWaPhone = data.phone;
                currentWaMessage = data.message;
                
                document.getElementById('waMessage').value = currentWaMessage;
                
                // اكتشاف نوع الجهاز
                let isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                let waLink;
                
                if (isMobile) {
                    // موبايل: الرابط كامل مع الرسالة
                    waLink = 'https://wa.me/' + currentWaPhone + '?text=' + encodeURIComponent(currentWaMessage);
                    document.getElementById('waMobileAlert').style.display = 'block';
                    document.getElementById('waDesktopAlert').style.display = 'none';
                } else {
                    // كمبيوتر: رابط بدون رسالة
                    waLink = 'https://wa.me/' + currentWaPhone;
                    document.getElementById('waDesktopAlert').style.display = 'block';
                    document.getElementById('waMobileAlert').style.display = 'none';
                }
                
                document.getElementById('waLinkBtn').href = waLink;
                
                // إظهار النافذة
                document.getElementById('waModal').style.display = 'block';
            } else {
                alert('خطأ: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال');
        });
}

function copyWaMessage() {
    let textarea = document.getElementById('waMessage');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // للجوال
    document.execCommand('copy');
    alert('✓ تم نسخ الرسالة\nيمكنك لصقها في واتساب (Ctrl+V)');
}

function closeWaModal() {
    document.getElementById('waModal').style.display = 'none';
}

// إغلاق النافذة عند الضغط على ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeWaModal();
    }
});

// إغلاق النافذة عند الضغط خارجها
window.onclick = function(event) {
    let modal = document.getElementById('waModal');
    if (event.target == modal) {
        closeWaModal();
    }
}

function sendToSupplier(rfqId) {
    if (!confirm('هل أنت متأكد من إرسال هذا الطلب للمورد؟')) return;
    
    fetch('/admin/send_to_supplier_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم إرسال الطلب للمورد');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ خطأ في الاتصال');
    });
}

function sendAndWhatsApp(rfqId) {
    if (!confirm('هل أنت متأكد من إرسال هذا الطلب للمورد وفتح واتساب؟')) return;
    
    // 1. إرسال الطلب للمورد
    fetch('/admin/send_to_supplier_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم إرسال الطلب للمورد');
            
            // 2. فتح واتساب بعد نجاح الإرسال
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
            }
            
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ خطأ في الاتصال');
    });
}
</script>

<?php include "includes/footer.php"; ?>