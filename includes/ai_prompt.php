<?php
// includes/ai_prompts.php
// Prompts متقدمة لـ AI Agents - PHP 8.3

class AIPrompts {

    public static function getProductCaption($platform = 'instagram') {
        $prompt = "أنت خبير تسويقي محترف لمنصة B2B2C.\n\n";
        $prompt .= "اكتب كابشن جذاب واحترافي للمنشور التالي:\n\n";
        $prompt .= "اسم المنتج: {pd_title}\n";
        $prompt .= "السعر: {pd_fob_price} {pd_currency}\n";
        $prompt .= "الوصف: {pd_desc}\n";
        $prompt .= "الحد الأدنى للطلب: {pd_min_order_qty} {pd_unit}\n";
        $prompt .= "الماركة: {brand_name}\n\n";

        if ($platform === 'instagram') {
            $prompt .= "اكتب كابشن إنستغرام قصير، جذاب، مع إيموجي مناسب، هاشتاجات قوية (5-8 هاشتاج)، ودعوة للعمل (Call to Action).";
        } elseif ($platform === 'facebook') {
            $prompt .= "اكتب بوست فيسبوك احترافي أطول قليلاً، يناسب الموردين والمشترين B2B، مع هاشتاجات.";
        } elseif ($platform === 'telegram') {
            $prompt .= "اكتب رسالة تليجرام واضحة واحترافية مع إيموجي.";
        } elseif ($platform === 'whatsapp') {
            $prompt .= "اكتب وصف قصير وجذاب جداً لكتالوج واتساب.";
        }

        $prompt .= "\n\nالرد يجب أن يكون بالعربية فقط، بدون أي شرح إضافي.";

        return $prompt;
    }

    public static function getLeadResponsePrompt() {
        return "أنت مساعد مبيعات ذكي في منصة B2B.\n"
             . "العميل أرسل الرسالة التالية:\n\n"
             . "{message}\n\n"
             . "حلل الطلب وأعطِ رد احترافي ودود مناسب لإرساله على واتساب.";
    }

    // يمكنك إضافة prompts أخرى لاحقاً
}