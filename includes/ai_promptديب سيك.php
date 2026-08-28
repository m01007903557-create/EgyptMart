<?php
/**
 * ai_prompt.php - ملف الذكاء الاصطناعي للرد على عملاء واتساب
 * هذا الملف يتم استدعاؤه من webhook.php لمعالجة رسائل العملاء
 */

// التحقق من وجود المتغيرات المطلوبة
$required_vars = ['supplier_name', 'contact_name', 'products_list', 'customer_message'];
foreach ($required_vars as $var) {
    if (!isset($$var)) {
        die(json_encode(['error' => "المتغير $var غير معرف قبل استدعاء ai_prompt.php"]));
    }
}

// إعدادات OpenAI API
$openai_api_key = 'YOUR_OPENAI_API_KEY'; // ضع مفتاح API الخاص بك هنا
$openai_model = 'gpt-4o-mini';
$openai_url = 'https://api.openai.com/v1/chat/completions';

/**
 * بناء الـ System Prompt الخاص بالذكاء الاصطناعي
 * هذا النص سيوجه الـ AI كيف يتصرف مع العملاء
 */
function buildSystemPrompt($supplier_name, $contact_name, $products_list) {
    return "أنت سكرتير مبيعات اسمه سارة، شغال في شركة {$supplier_name}. لهجتك مصرية ودودة وبسيطة. ممنوع الكلام الرسمي.

قائمة منتجاتك وأسعارك الحالية:
{$products_list}

قواعد الرد اللي لازم تلتزم بيها 100%:
1. لو العميل سأل عن سعر أو كمية أو وصف، جاوب من قائمة المنتجات بس. لو مش موجود قول 'للأسف المنتج ده مش متاح حالياً'.
2. لو العميل طلب أوردر، اسأله بالترتيب: اسم حضرتك؟ رقم للتواصل؟ العنوان بالتفصيل؟ عايز كام قطعة؟
3. أول ما العميل يأكد الطلب ويقول كل البيانات، اطبع في آخر سطر من ردك كود JSON واحد بس بدون أي كلام زيادة بالشكل ده بالظبط: ORDER_DATA:{\"name\": \"اسم العميل\", \"phone\": \"رقمه\", \"product\": \"اسم المنتج\", \"qty\": \"الكمية\", \"address\": \"العنوان\"}
4. لو العميل سأل سؤال إجابته مش موجودة في قائمة المنتجات، رد الجملة دي بالظبط: 'حاضر هخلي {$contact_name} مسؤول المبيعات يتواصل مع حضرتك مباشر عشان يفيدك أكتر'.
5. لو العميل خرج عن منتجات {$supplier_name} قوله: 'أنا مختص بمنتجات {$supplier_name} فقط، تحب تعرف سعر إيه منهم؟'
6. خليك مختصر. الرد ميزيدش عن 3 سطور إلا لو العميل بيطلب أوردر.

الآن رد على رسالة العميل التالية:";
}

/**
 * إرسال طلب إلى OpenAI API باستخدام cURL
 */
function callOpenAI($system_prompt, $user_message) {
    global $openai_api_key, $openai_model, $openai_url;
    
    // التحقق من وجود مفتاح API
    if ($openai_api_key === 'YOUR_OPENAI_API_KEY') {
        return "⚠️ عذراً، لم يتم تكوين مفتاح OpenAI API بعد. يرجى التواصل مع الدعم الفني.";
    }
    
    // تجهيز البيانات المرسلة إلى OpenAI
    $post_data = [
        'model' => $openai_model,
        'messages' => [
            [
                'role' => 'system',
                'content' => $system_prompt
            ],
            [
                'role' => 'user',
                'content' => $user_message
            ]
        ],
        'temperature' => 0.7,     // إبداع متوسط
        'max_tokens' => 500,       // ردود مختصرة
        'top_p' => 0.9
    ];
    
    // تهيئة cURL
    $ch = curl_init($openai_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // مهلة 30 ثانية
    
    // تنفيذ الطلب
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // التحقق من وجود أخطاء في cURL
    if ($curl_error) {
        error_log("cURL Error in ai_prompt.php: " . $curl_error);
        return "عذراً، حدث خطأ في الاتصال. سيتم التواصل معك قريباً.";
    }
    
    // التحقق من استجابة HTTP
    if ($http_code !== 200) {
        error_log("OpenAI API Error (HTTP {$http_code}): " . $response);
        return "عذراً، حدث خطأ تقني. يرجى المحاولة مرة أخرى لاحقاً.";
    }
    
    // فك الـ JSON من OpenAI
    $response_data = json_decode($response, true);
    
    // استخراج نص الرد من الاستجابة
    if (isset($response_data['choices'][0]['message']['content'])) {
        return trim($response_data['choices'][0]['message']['content']);
    } else {
        error_log("OpenAI API Unexpected Response: " . print_r($response_data, true));
        return "عذراً، لم أستطع معالجة طلبك حالياً. الرجاء المحاولة مرة أخرى.";
    }
}

// بناء الـ System Prompt باستخدام المتغيرات المستقبلة
$system_prompt = buildSystemPrompt($supplier_name, $contact_name, $products_list);

// إضافة رسالة العميل إلى نهاية الـ System Prompt (كسياق)
$full_prompt = $system_prompt . "\n\nرسالة العميل: {$customer_message}";

// استدعاء OpenAI والحصول على الرد
$ai_reply = callOpenAI($full_prompt, $customer_message);

// إرجاع الرد إلى webhook.php
echo $ai_reply;
?>