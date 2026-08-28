/**
 * EgyptMart WhatsApp Handler - يفتح التطبيق بدون مصادقة
 * الموقع: /js/whatsapp_handler.js
 */

(function() {
    'use strict';

    // تنظيف رقم الهاتف
    function cleanPhone(phone) {
        let cleaned = phone.toString().replace(/\D/g, '');
        if (cleaned.startsWith('0')) {
            cleaned = '20' + cleaned.substring(1);
        }
        if (!cleaned.startsWith('20') && cleaned.length >= 10) {
            cleaned = '20' + cleaned;
        }
        return cleaned;
    }

    // كشف نوع الجهاز
    function isMobile() {
        return /Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i.test(navigator.userAgent);
    }

    // الوظيفة الرئيسية لفتح واتساب
    window.openWhatsApp = function(phone, message) {
        let cleanPhoneNumber = cleanPhone(phone);
        let encodedMessage = encodeURIComponent(message || 'مرحبا من EgyptMart');
        
        let waLinkApp = 'whatsapp://send?phone=' + cleanPhoneNumber + '&text=' + encodedMessage;
        let waLinkWeb = 'https://wa.me/' + cleanPhoneNumber + '?text=' + encodedMessage;
        
        if (isMobile()) {
            window.open(waLinkWeb, '_blank');
        } else {
            window.location.href = waLinkApp;
            setTimeout(function() {
                if (!document.hidden) {
                    let useBrowser = confirm(
                        '⚠️ لم يتم فتح تطبيق واتساب.\n\n' +
                        'تأكد من تثبيت تطبيق WhatsApp على جهازك.\n\n' +
                        'هل تريد فتح الرابط في المتصفح؟'
                    );
                    if (useBrowser) {
                        window.open(waLinkWeb, '_blank');
                    }
                }
            }, 1500);
        }
    };

    // ربط تلقائي للأزرار
    function bindWhatsAppButtons() {
        document.querySelectorAll('[data-whatsapp]').forEach(function(element) {
            if (element._whatsappBound) return;
            element._whatsappBound = true;
            
            element.addEventListener('click', function(e) {
                e.preventDefault();
                let phone = this.getAttribute('data-whatsapp');
                let message = this.getAttribute('data-message') || 'مرحبا من EgyptMart';
                openWhatsApp(phone, message);
            });
        });
    }

    // تنفيذ الربط
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindWhatsAppButtons);
    } else {
        bindWhatsAppButtons();
    }

    // مراقبة الإضافات الجديدة
    if (window.MutationObserver) {
        let observer = new MutationObserver(function() {
            bindWhatsAppButtons();
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    console.log('✅ WhatsApp Handler جاهز');

})();