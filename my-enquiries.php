<?php
/**
 * File: my-enquiries.php
 * Description: إدارة الاستفسارات والرسائل - عرض صندوق الوارد والصادر
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);
echo "<!-- Debug: my-enquiries.php loaded -->";
ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "my-enquiries.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];
$combinedNotificationCount = function_exists('getCombinedNotificationCount') ? getCombinedNotificationCount($uid) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- CSS -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/my2.css" type="text/css" rel="stylesheet">
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <script type="text/javascript">
    $(document).ready(function() {
        $('body').on('click', '.ajax', function(event) {
            $.colorbox({
                href: $(this).attr('href'),
                open: true,
                width: '750px'
            });
            return false;
        });
        
        showInbox(1);
        
        <?php if (isset($_GET['ii']) && !empty($_GET['ii']) && isset($_GET['tp']) && !empty($_GET['tp'])): ?>
        openmail(<?php echo (int)$_GET['ii']; ?>, 'inbox ');
        <?php endif; ?>
    });
    
    function showInbox(page) {
        $('#mail-details').html('');
        $('#mail-details').css("display", "none");
        $('#mail').css("display", "block");
        
        $(".inbox").addClass("txtcol");
        $(".sent").removeClass("txtcol");
        
        $.post("ajax-file/inbox.php", {page: page}, function(data) {
            $('#mail').html(data);
        });
    }
    
    function showSent(page) {
        $('#mail-details').html('');
        $('#mail-details').css("display", "none");
        $('#mail').css("display", "block");
        
        $(".sent").addClass("txtcol");
        $(".inbox").removeClass("txtcol");
        
        $.post("ajax-file/sentbox.php", {page: page}, function(data) {
            $('#mail').html(data);
        });
    }
    
 function openmail(id, type) {
    if (!id || !type) {
        console.log('بيانات غير مكتملة:', {id, type});
        return;
    }
    
    console.log('فتح الرسالة:', id, type);
    $("#loading").css("display", "block");
    
    setTimeout(function() {
        $.post("/ajax-file/enq-details.php", {id: id, type: type}, function(data) {
            console.log('تم تحميل التفاصيل');
            $("#loading").css("display", "none");
            $('#mail').css("display", "none");
            $("#mail-details").css("display", "block");
            $('#mail-details').html(data);
        }).fail(function(xhr) {
            console.log('خطأ في التحميل:', xhr.status, xhr.statusText);
            $("#loading").css("display", "none");
            $('#mail-details').html('<div class="alert alert-danger">خطأ في تحميل تفاصيل الرسالة: ' + xhr.status + '</div>');
            $("#mail-details").css("display", "block");
        });
    }, 800);
}

function closeDetail() {
    $("#loading_det").css("display", "block");
    
    setTimeout(function() {
        $("#loading_det").css("display", "none");
        $('#mail-details').html('');
        $('#mail-details').css("display", "none");
        $('#mail').css("display", "block");
    }, 1000);
}
    
    function delMessage(id) {
        if (confirm("Are you sure to delete this enquiry?")) {
            $.post("ajax-file/delMessage.php", {id: id}, function(data) {
                if (data == 1) {
                    closeDetail();
                    showInbox(1);
                } else {
                    alert("Enquiry not deleted. Please try after sometime.");
                }
            });
        }
    }
    
    function deleteInbox() {
        if (confirm("متأكد إنك تريد حذف الرسالة ؟")) {
            var msg = $("input[name=cbI]:checked").map(function() {
                return this.value;
            }).get().join(",");
            
            $.post("ajax-file/deleteInbox.php", {msg: msg}, function(data) {
                showInbox(1);
            });
        }
    }
    
    function deleteSentbox() {
        if (confirm("Are you sure to delete these enquiry?")) {
            var msg = $("input[name=cbS]:checked").map(function() {
                return this.value;
            }).get().join(",");
            
            $.post("ajax-file/deleteSent.php", {msg: msg}, function(data) {
                showSent(1);
            });
        }
    }
    
    var checked = false;
    
    function checkedAll_inbox() {
        var aa = document.getElementById('m_inbox');
        checked = !checked;
        
        for (var i = 0; i < aa.elements.length; i++) {
            if (aa.elements[i].type == 'checkbox') {
                aa.elements[i].checked = checked;
            }
        }
    }
    
    function checkedAll_sent() {
        var aa = document.getElementById('m_sent');
        checked = !checked;
        
        for (var i = 0; i < aa.elements.length; i++) {
            if (aa.elements[i].type == 'checkbox') {
                aa.elements[i].checked = checked;
            }
        }
    }
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="enqulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; text-align:center; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#f2f2f2;">
                        مراسلات تجارتى
                    </h3>
                </li>
                
                <li class="np">
                    <a class="txtcol me inbox bnr" onclick="showInbox(1);" style="cursor:pointer;">
                        البريد المرسل الى شركتى
                        <span class="label label-yellow" style="float:left; margin-left:6px;"><?php echo (int)$combinedNotificationCount; ?></span>
                    </a>
                </li>
                
                <li class="np">
                    <a class="me sent bnr" onclick="showSent(1);" style="cursor:pointer;" title="Sent Box">
                        صندوق البريد المرسل
                    </a>
                </li>
                
                <li style="border-bottom: medium none;">
                    <h3 style="height:18px;">
                        <a href="javascript:showfolders();" id="folimg" class="mf_h me bnr f1">My Folders</a>
                        <a href="javascript:newfol();" id="m2_w2nf" class=""></a>
                    </h3>
                </li>
                
                <span id="m2_nf" style="display:none;">
                    <li style="border-bottom:0;">
                        <table border="0" cellpadding="0" cellspacing="3" width="100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <input class="mu11" style="width:128px; font-size:10px;" id="m2_nfn" name="m2_nfn" type="text">
                                    </td>
                                    <td width="45">
                                        <input value="Add" onclick="addfolder();" class="fadb me bnr" type="button">
                                    </td>
                                    <td width="10">
                                        <input value="" onclick="newfol();" class="me ffc bnr" type="button">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                </span>
                
                <span id="allfol" style="display:block;"></span>
            </ul>
            
            <ul id="m2_sep">&nbsp;</ul>
            
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li style="border-bottom: medium none;" title="Address Book">
                    <h3>دفتر عناوين مراسلاتى البريدية</h3>
                </li>
                <li class="np npnew"><a href="my-addressbook.php" title="Contacts List">»&nbsp;قائمة عملائى المتصلين</a></li>
                <li class="np npnew"><a href="my-blocklist.php">»&nbsp;قائمة البلوك</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;بيانات طلبات شرائى</a></li>
                
                <li style="border-bottom: medium none; margin-top:40px;"><h2>روابط هامة</h2></li>
                <li class="np npnew"><a href="buyleads.php">شاهد طلبات شراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">إدارة بيانات طلبات شرائى</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php">إدارة إشعارات طلبات شراء</a></li>
            </ul>
        </div>
        <!-- نهاية القائمة الجانبية -->
        
        <!-- منطقة عرض البريد -->
        <div id="mail" class="w56 f1 p2b blr b1_m2"></div>
        <div id="mail-details" class="w56 f1 p2b blr b1_m2"></div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
// دالة تعديل عرض السعر للمورد - معرفة عامة في الصفحة الرئيسية
// دالة تعديل عرض السعر للمورد
function openOfferPopup(rfqId, action, offerId) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات (اختياري):', '');

    let confirmMsg = `البيانات المرسلة:\nالسعر: ${price} USD\nمدة التوصيل: ${delivery} يوم\n`;
    confirmMsg += notes ? `ملاحظات: ${notes}\n` : '';
    confirmMsg += `\nالعرض: ${action === 'send' ? 'جديد' : 'تعديل'}\nهل أنت متأكد من الإرسال؟`;
    if (!confirm(confirmMsg)) return;

    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `offer_id=${offerId}&price=${price}&delivery_days=${delivery}&notes=${encodeURIComponent(notes)}&rfq_id=${rfqId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // عرض رسالة التحذير إن وجدت
            if (data.warning) alert('⚠️ ' + data.warning);
            alert('✅ ' + data.message);
            
            // تقديم خيارات أفضل للواتساب
            let userChoice = confirm(
                'تم تحديث العرض بنجاح. هل تريد إرسال إشعار للمشتري عبر واتساب؟\n\n' +
                'اختر "موافق" لفتح محادثة واتساب (قد تحتاج لمسح رمز QR).\n' +
                'اختر "إلغاء" لنسخ الرابط السحري وإرساله بنفسك.'
            );
            
            if (userChoice) {
                // فتح رابط واتساب (قد يطلب رمز QR)
                window.open(data.whatsapp_url, '_blank');
                alert('📱 سيتم فتح واتساب. إذا طلب منك رمز QR، قم بمسحه من هاتفك لربط الجلسة.\n\nبعد ربط الجهاز، ستظهر نافذة الدردشة. قم بلصق الرسالة وإرسالها.');
            } else {
                // نسخ الرابط السحري للحافظة
                navigator.clipboard.writeText(data.magic_link);
                alert('🔗 تم نسخ الرابط السحري. يمكنك الآن إرساله للمشتري عبر أي طريقة أخرى.');
            }
            location.reload();
        } else {
            alert('❌ فشل التحديث: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('❌ خطأ في الاتصال بالخادم: ' + error.message);
    });
}

// دالة قبول العرض للمشتري
function acceptOffer(offerId, rfqId) {
    if (!confirm('هل أنت متأكد من قبول هذا العرض؟ سيتم كشف بيانات المورد بعد القبول.')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=accept'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            if (data.supplier_data) {
                let info = '📋 بيانات المورد:\n\n';
                info += '🏢 الشركة: ' + data.supplier_data.company_name + '\n';
                info += '📞 الهاتف: ' + data.supplier_data.phone + '\n';
                info += '📧 البريد: ' + data.supplier_data.email + '\n';
                info += '🌐 الموقع: ' + data.supplier_data.website;
                alert(info);
            }
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('❌ خطأ: ' + error.message));
}

function rejectOffer(offerId, rfqId) {
    if (!confirm('هل أنت متأكد من رفض هذا العرض؟')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=reject'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم رفض العرض');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('❌ خطأ: ' + error.message));
}
</script>
</body>
</html>
