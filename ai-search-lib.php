<?php
if (!function_exists('ai_search_strtolower')) {
    function ai_search_strtolower($text) {
        return function_exists('mb_strtolower') ? mb_strtolower((string)$text, 'UTF-8') : strtolower((string)$text);
    }
}

if (!function_exists('ai_search_clean')) {
    function ai_search_clean($text) {
        $text = preg_replace('/[“”"\'`]+/u', ' ', (string)$text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }
}

if (!function_exists('ai_search_words')) {
    function ai_search_words($text) {
        $text = preg_replace('/[^a-z0-9\x{0600}-\x{06FF} ]+/iu', ' ', (string)$text);
        $parts = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        return is_array($parts) ? array_values($parts) : array();
    }
}

if (!function_exists('ai_search_country_map')) {
    function ai_search_country_map() {
        return array(
            'egypt' => 'Egypt', 'مصر' => 'Egypt',
            'saudi' => 'Saudi Arabia', 'saudi arabia' => 'Saudi Arabia', 'ksa' => 'Saudi Arabia', 'السعودية' => 'Saudi Arabia',
            'uae' => 'United Arab Emirates', 'emirates' => 'United Arab Emirates', 'dubai' => 'United Arab Emirates', 'دبي' => 'United Arab Emirates', 'الامارات' => 'United Arab Emirates', 'الإمارات' => 'United Arab Emirates',
            'kuwait' => 'Kuwait', 'الكويت' => 'Kuwait',
            'china' => 'China', 'الصين' => 'China',
            'india' => 'India', 'الهند' => 'India',
            'turkey' => 'Turkey', 'تركيا' => 'Turkey',
            'vietnam' => 'Vietnam', 'فيتنام' => 'Vietnam',
            'thailand' => 'Thailand', 'تايلاند' => 'Thailand',
            'malaysia' => 'Malaysia', 'ماليزيا' => 'Malaysia'
        );
    }
}

if (!function_exists('ai_search_supplier_type_map')) {
    function ai_search_supplier_type_map() {
        return array(
            'manufacturer' => 'Manufacturer', 'factory' => 'Manufacturer', 'مصنع' => 'Manufacturer', 'مصانع' => 'Manufacturer',
            'supplier' => 'Supplier', 'suppliers' => 'Supplier', 'مورد' => 'Supplier', 'موردين' => 'Supplier',
            'exporter' => 'Exporter', 'export' => 'Exporter', 'مصدر' => 'Exporter', 'تصدير' => 'Exporter',
            'wholesaler' => 'Wholesaler', 'wholesale' => 'Wholesaler', 'جملة' => 'Wholesaler',
            'distributor' => 'Distributor', 'موزع' => 'Distributor'
        );
    }
}

if (!function_exists('ai_search_pick_map_value')) {
    function ai_search_pick_map_value($lower, $map) {
        foreach ($map as $needle => $value) {
            if (preg_match('/(^|[^a-z0-9\x{0600}-\x{06FF}])' . preg_quote($needle, '/') . '([^a-z0-9\x{0600}-\x{06FF}]|$)/iu', $lower)) {
                return $value;
            }
        }
        return '';
    }
}

if (!function_exists('ai_search_stopwords')) {
    function ai_search_stopwords() {
        return array(
            'i','me','my','we','us','can','could','may','please','need','want','looking','look','search','find','ask','have','get','give','show','best','kind','good','price','prices','quote','quotation','buy','buying','purchase','order','large','small','big','bulk','quantity','amount','fast','delivery','deliver','here','near','anywhere','from','with','for','in','at','by','of','and','or','to','a','an','the','company','companies','supplier','suppliers','manufacturer','factory','exporter','products','product','service','services','egypt','egyptian','saudi','arabia','malaysia',
            'اريد','أريد','احتاج','أحتاج','ابحث','إبحث','بحث','اسال','أسأل','هل','اجد','أجد','هنا','عن','من','في','الى','إلى','على','مع','ال','افضل','أفضل','سعر','اسعار','أسعار','شراء','كمية','كبير','كبيرة','صغيرة','توصيل','سريع','شحن','فورى','فوري','شركة','شركات','مورد','موردين','مصنع','مصانع','منتج','منتجات','خدمة','خدمات','مصرى','مصري','مصرية','مصر','السعودية','ماليزيا'
        );
    }
}

if (!function_exists('ai_search_extract_between')) {
    function ai_search_extract_between($text, $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                return ai_search_clean($match[1]);
            }
        }
        return '';
    }
}

if (!function_exists('ai_search_trim_entity')) {
    function ai_search_trim_entity($value) {
        $value = ai_search_clean($value);
        if (preg_match('/\bfrom\s+(.+)$/iu', $value, $match)) {
            $value = $match[1];
        }
        $value = preg_replace('/\b(?:show me|find|search for|search|looking for|look for|get|give me|can i have|can|ask|inquire|inquiry|to inquire about)\b/iu', ' ', $value);
        $value = preg_replace('/\b(?:best|kind|price|prices|quote|quotation|buy|purchase|order|need|want|large|small|bulk|quantity|anywhere|egypt|egyptian|saudi|arabia|malaysia)\b/iu', ' ', $value);
        $value = preg_replace('/\b(company|co|supplier|manufacturer|factory|products|product|service|services)\b/iu', ' ', $value);
        $value = preg_replace('/\b(ابحث|بحث|اريد|أريد|احتاج|أحتاج|شركة|شركات|مورد|مصنع|منتجات|منتج|خدمات|خدمة)\b/u', ' ', $value);
        return ai_search_clean($value);
    }
}

if (!function_exists('ai_search_extract_company')) {
    function ai_search_extract_company($text) {
        $company = ai_search_extract_between($text, array(
            '/\b(?:show me|find|search for|look for|looking for|get|give me)\s+([a-z0-9][a-z0-9 .&-]{1,50}?)\s+(?:supplier|company|profile)\b/iu',
            '/\b([a-z0-9][a-z0-9 .&-]{1,50}?)\s+(?:company|co)\b/iu',
            '/\b(?:show me|find|search for|look for|looking for|get|give me)?\s*([a-z0-9][a-z0-9 .&-]{1,50}?)\s+(?:products?|catalog)\b/iu',
            '/\b(?:from|by)\s+([a-z0-9][a-z0-9 .&-]{1,60}?)\s+(?:company|co)\b/iu',
            '/(?:شركة|شركات|منتجات شركة)\s+([\x{0600}-\x{06FF}a-z0-9 .&-]{2,60}?)(?:\s+(?:في|من|مع|ل|ب)|$)/iu'
        ));
        if (preg_match('/\b(best|good|verified|real|trusted|manufacturer|factory|supplier|exporter|country|city)\b/iu', $company)) {
            return '';
        }
        $countryMap = ai_search_country_map();
        if (isset($countryMap[ai_search_strtolower($company)])) {
            return '';
        }
        return ai_search_trim_entity($company);
    }
}

if (!function_exists('ai_search_extract_product')) {
    function ai_search_extract_product($text, $company) {
        $text = preg_replace('/\b(?:to\s+inquire\s+about|inquire\s+about|inquiry\s+about)\b/iu', ' ', $text);
        if ($company !== '' && preg_match('/\b' . preg_quote($company, '/') . '\s+(?:company|co)?\s*(?:products?|catalog)\b/iu', $text)) {
            return '';
        }
        if ($company !== '' && preg_match('/(?:منتجات\s+شركة\s+' . preg_quote($company, '/') . '|شركة\s+' . preg_quote($company, '/') . '.*منتجات)/iu', $text)) {
            return '';
        }
        if ($company !== '' && preg_match('/(?:سماد|اسمدة|أسمدة)\s+(?:شركة|شركات)/u', $text)) {
            return 'سماد';
        }
        $candidate = ai_search_extract_between($text, array(
            '/\b(?:buy|purchase|order|need|want|looking for|search for|look for|find|get|have|ask quotation for|ask for|ask|show me)\s+(.+?)(?:\s+from\b|\s+by\b|\s+with\b|\s+in\b|\s+near\b|$)/iu',
            '/(?:شراء|أحتاج|احتاج|اريد|أريد|ابحث عن|بحث عن|اسال عن|أسأل عن|هل اجد|هل أجد)\s+(.+?)(?:\s+من\s+|\s+في\s+|\s+مع\s+|$)/iu'
        ));
        if ($candidate === '') {
            $candidate = $text;
        }
        if ($company !== '') {
            $candidate = preg_replace('/\bfrom\s+' . preg_quote($company, '/') . '\b.*$/iu', ' ', $candidate);
            $candidate = preg_replace('/\b' . preg_quote($company, '/') . '\s+(?:company|co)\b/iu', ' ', $candidate);
        }
        $candidate = preg_replace('/\b(?:in|near|at)\s+(cairo|alexandria|giza|riyadh|jeddah|dubai|abu dhabi|kuwait city)\b.*$/iu', ' ', $candidate);
        $candidate = preg_replace('/(?:في|قرب|داخل)\s+(القاهرة|الاسكندرية|الإسكندرية|الجيزة|الرياض|جدة|دبي|أبو ظبي).*$/u', ' ', $candidate);
        $candidate = preg_replace('/\b(?:large|small|big|bulk|quantity|amount|best|kind|price|prices|quote|quotation|fast|delivery|deliver|shipping|urgent|supplier|suppliers|manufacturer|factory|company|companies|products?|services?|anywhere|egypt|egyptian|saudi|arabia|malaysia)\b/iu', ' ', $candidate);
        $candidate = preg_replace('/(?:كمية|كبير|كبيرة|صغيرة|افضل|أفضل|سعر|اسعار|أسعار|توصيل|سريع|شحن|فورى|فوري|موردين|مورد|مصنع|شركة|شركات|منتجات|منتج|خدمات|خدمة|هنا|مصرى|مصري|مصرية)/u', ' ', $candidate);
        $candidate = preg_replace('/\s+ال\s*$/u', ' ', $candidate);
        $words = ai_search_words($candidate);
        $stop = array_flip(ai_search_stopwords());
        $picked = array();
        foreach ($words as $word) {
            $key = ai_search_strtolower($word);
            if (!isset($stop[$key])) {
                $picked[] = $word;
            }
        }
        $pickedText = ai_search_clean(implode(' ', array_slice($picked, 0, 5)));
        if ($pickedText === '' && $company === '') {
            return ai_search_clean($text);
        }
        return $pickedText;
    }
}

if (!function_exists('ai_search_extract_quantity')) {
    function ai_search_extract_quantity($text) {
        if (preg_match('/(\d+(?:[,.]\d+)?)\s*(tons?|tonnes?|kg|kilograms?|cartons?|pieces?|pcs|boxes|طن|كيلو|كرتونة|قطعة)/iu', $text, $match)) {
            return ai_search_clean($match[0]);
        }
        if (preg_match('/\b(large|small|bulk|big)\s+quantity\b/iu', $text, $match)) {
            return ai_search_clean($match[0]);
        }
        if (preg_match('/(?:كمية\s+كبيرة|كمية\s+صغيرة|بالجملة|جملة)/u', $text, $match)) {
            return ai_search_clean($match[0]);
        }
        return '';
    }
}

if (!function_exists('ai_search_result_keywords')) {
    function ai_search_result_keywords($product, $company, $text) {
        $source = ai_search_clean($product !== '' ? $product : ($company !== '' ? $company : $text));
        $lower = ai_search_strtolower($source);
        $rawLower = ai_search_strtolower($text);
        if (preg_match('/\bcompound\s+fertilizer\b/iu', $lower)) {
            return 'Compound Fertilizer';
        }
        if (preg_match('/\b(?:sol|sop|flora)\b.*\bfertilizer\b|\bfertilizer\b.*\b(?:sol|sop|flora)\b/iu', $lower)) {
            return 'Fertilizer';
        }
        if (preg_match('/(?:سماد|اسمدة|أسمدة)/u', $source)) {
            return 'سماد';
        }
        if (preg_match('/(?:مانجو)/u', $source)) {
            return 'مانجو';
        }
        if (preg_match('/\bmango\b/iu', $lower)) {
            return 'mango';
        }
        if ($company !== '' && preg_match('/\b(products?|catalog)\b|(?:منتجات|كتالوج)/iu', $rawLower)) {
            return $company;
        }
        return $source;
    }
}

if (!function_exists('ai_search_extract_city')) {
    function ai_search_extract_city($text, $country) {
        $cities = array('cairo', 'القاهرة', 'alexandria', 'الاسكندرية', 'الإسكندرية', 'giza', 'الجيزة', 'riyadh', 'الرياض', 'jeddah', 'جدة', 'dubai', 'دبي', 'abu dhabi', 'أبو ظبي', 'kuwait city');
        $lower = ai_search_strtolower($text);
        foreach ($cities as $city) {
            if (strpos($lower, ai_search_strtolower($city)) !== false) {
                return $city;
            }
        }
        return '';
    }
}

if (!function_exists('ai_search_extract_intent')) {
    function ai_search_extract_intent($text, $requestedType = 'Products') {
        $text = ai_search_clean($text);
        $lower = ai_search_strtolower($text);
        $company = ai_search_extract_company($text);
        $product = ai_search_extract_product($text, $company);
        $country = ai_search_pick_map_value($lower, ai_search_country_map());
        if (preg_match('/\b(egypt|saudi|saudi arabia|ksa)\b.*\bor\b.*\b(egypt|saudi|saudi arabia|ksa)\b/iu', $lower)) {
            $country = '';
        }
        $city = ai_search_extract_city($text, $country);
        $supplierType = ai_search_pick_map_value($lower, ai_search_supplier_type_map());
        $quantity = ai_search_extract_quantity($text);
        $delivery = preg_match('/\b(fast delivery|urgent delivery|same day|delivery)\b|(?:توصيل سريع|توصيل|شحن سريع)/iu', $lower, $m) ? ai_search_clean($m[0]) : '';
        $brand = ai_search_extract_between($text, array('/\bbrand\s+([a-z0-9 .&-]{2,40})/iu', '/(?:ماركة|براند)\s+([\x{0600}-\x{06FF}a-z0-9 .&-]{2,40})/iu'));
        $category = ai_search_extract_between($text, array('/\bcategory\s+([a-z0-9 .&-]{2,40})/iu', '/(?:قسم|تصنيف|فئة)\s+([\x{0600}-\x{06FF}a-z0-9 .&-]{2,40})/iu'));

        $intentType = 'product_search';
        $rctyp = in_array($requestedType, array('Products', 'Suppliers', 'buy_lead', 'tender'), true) ? $requestedType : 'Products';

        $asksForSupplier = preg_match('/\b(supplier|suppliers|manufacturer|manufacturers|factory|factories|exporter|exporters|distributor|distributors|wholesaler|wholesalers)\b|(?:مورد|موردين|مصنع|مصانع|مصدر|مصدرين|موزع|موزعين|تاجر|تجار|جملة)/iu', $lower);
        $asksForCompany = preg_match('/\b(company|companies|business|profile)\b|(?:شركة|شركات|صفحة شركة|ملف شركة)/iu', $lower);
        $asksForProducts = preg_match('/\b(products?|items?|catalog|catalogue|list)\b|(?:منتجات|كتالوج|قائمة منتجات)/iu', $lower);
        $asksToBuy = preg_match('/\b(buy|purchase|order|need|want|ask quotation|quotation|quote|price)\b|(?:شراء|اشترى|أشتري|اشتري|اسال|أسأل|عرض سعر|تسعير|سعر)/iu', $lower);

        if ($asksForSupplier) {
            if ($company !== '' && ($product === '' || ai_search_strtolower($product) === ai_search_strtolower($company))) {
                $intentType = 'supplier_search';
                $rctyp = 'Suppliers';
                $product = '';
            } elseif ($product !== '') {
                $intentType = 'supplier_products';
                $rctyp = 'Products';
            } else {
                $intentType = 'supplier_search';
                $rctyp = 'Suppliers';
            }
        }
        if ($asksForCompany && !$asksForProducts && $company !== '' && ($product === '' || !$asksToBuy)) {
            $intentType = 'supplier_search';
            $rctyp = 'Suppliers';
            $product = '';
        }
        if ($company !== '' && $asksForProducts) {
            $intentType = 'supplier_products';
            $rctyp = 'Products';
        }
        if ($company !== '' && $product !== '' && $asksToBuy) {
            $intentType = 'supplier_products';
            $rctyp = 'Products';
        }
        if ($company !== '' && $product !== '' && !$asksForSupplier && !$asksForCompany) {
            $intentType = 'supplier_products';
            $rctyp = 'Products';
        }
        if ($requestedType === 'Suppliers' && $intentType === 'product_search') {
            $intentType = 'supplier_search';
            $rctyp = 'Suppliers';
        }
        if (preg_match('/\b(service|services)\b|(?:خدمة|خدمات)/iu', $lower) && $product !== '') {
            $intentType = 'service_search';
            $rctyp = 'Products';
        }
        if (preg_match('/\b(buy lead|buyer request|rfq)\b|(?:طلب شراء|طلبات شراء)/iu', $lower)) {
            $intentType = 'buy_lead';
            $rctyp = 'buy_lead';
        }
        if (preg_match('/\b(tender|auction)\b|(?:مناقصة|مناقصات|مزايدة|مزادات)/iu', $lower)) {
            $intentType = 'tender';
            $rctyp = 'tender';
        }

        $keywords = ai_search_clean(ai_search_result_keywords($product, $company, $text));
        if ($intentType === 'supplier_search' && preg_match('/\b([a-z0-9][a-z0-9 .&-]{1,50}?\s+company)\b/iu', $text, $companyKeywordMatch)) {
            $keywords = ai_search_trim_entity($companyKeywordMatch[1]) . ' Company';
            $keywords = ai_search_clean($keywords);
        }
        if ($keywords === '') {
            $keywords = $text;
        }

        return array(
            'raw_text' => $text,
            'language' => preg_match('/[\x{0600}-\x{06FF}]/u', $text) ? 'ar' : 'en',
            'intent_type' => $intentType,
            'entity_type' => $rctyp,
            'rctyp' => $rctyp,
            'keywords' => $keywords,
            'product' => $product,
            'category' => $category,
            'supplier_company' => $company,
            'brand' => ai_search_trim_entity($brand),
            'country' => $country,
            'city_region' => $city,
            'supplier_type' => $supplierType,
            'quantity' => $quantity,
            'delivery_requirements' => $delivery
        );
    }
}

if (!function_exists('ai_search_merge_payload')) {
    function ai_search_merge_payload($intent, $payload) {
        if (!is_array($payload)) {
            return $intent;
        }
        $map = array(
            'keywords' => array('keywords', 'keyword', 'query', 'search'),
            'product' => array('product', 'product_name'),
            'category' => array('category', 'category_name'),
            'supplier_company' => array('supplier_company', 'company', 'company_name', 'supplier', 'supplier_name'),
            'brand' => array('brand'),
            'country' => array('country'),
            'city_region' => array('city', 'city_region', 'region'),
            'supplier_type' => array('supplier_type', 'business_type'),
            'quantity' => array('quantity'),
            'delivery_requirements' => array('delivery', 'delivery_requirements'),
            'intent_type' => array('intent', 'intent_type'),
            'rctyp' => array('rctyp', 'type', 'entity_type')
        );
        foreach ($map as $target => $keys) {
            foreach ($keys as $key) {
                if (isset($payload[$key]) && is_scalar($payload[$key]) && trim((string)$payload[$key]) !== '') {
                    $intent[$target] = ai_search_clean((string)$payload[$key]);
                    break;
                }
            }
        }
        if (!in_array($intent['rctyp'], array('Products', 'Suppliers', 'buy_lead', 'tender'), true)) {
            $intent['rctyp'] = 'Products';
        }
        if ($intent['keywords'] === '') {
            $intent['keywords'] = $intent['product'] !== '' ? $intent['product'] : ($intent['supplier_company'] !== '' ? $intent['supplier_company'] : $intent['raw_text']);
        }
        return $intent;
    }
}

if (!function_exists('ai_search_build_url')) {
    function ai_search_build_url($intent) {
        $params = array(
            'keywords' => $intent['keywords'],
            'rctyp' => $intent['rctyp'],
            'search_mode' => 'scenario',
            'ai_params' => base64_encode(json_encode($intent, JSON_UNESCAPED_UNICODE)),
            'ai_product' => $intent['product'],
            'ai_company' => $intent['supplier_company'],
            'ai_country' => $intent['country'],
            'ai_city' => $intent['city_region'],
            'ai_supplier_type' => $intent['supplier_type'],
            'ai_brand' => $intent['brand'],
            'ai_category' => $intent['category']
        );
        foreach ($params as $key => $value) {
            if ($value === '') {
                unset($params[$key]);
            }
        }
        return 'search.php?' . http_build_query($params);
    }
}
?>
