<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>EgyptMART - B2B Marketplace</title>
    <!-- روابط الخطوط وأساسيات CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== RESET & BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        body {
            background: #f8f9fa;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* ===== HEADER WRAPPER ===== */
        .new-header {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* ===== TOP BAR (height ~1cm) ===== */
        .top-bar {
            background: #1a1a1a; /* أسود 90% */
            color: #e6e6e6;
            padding: 4px 16px;
            height: 40px; /* ~1cm */
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow-x: auto;
            white-space: nowrap;
            gap: 8px;
        }
        .top-bar .tabs {
            display: flex;
            gap: 4px;
            align-items: center;
            height: 100%;
        }
        .top-bar .tab-btn {
            background: transparent;
            border: none;
            color: #e6e6e6;
            padding: 4px 12px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 32px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            background: rgba(255,255,255,0.05);
        }
        .top-bar .tab-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .top-bar .tab-btn.active {
            background: #000000; /* أسود 100% */
            color: #ffffff;
            font-weight: 700;
        }
        .top-bar .tab-btn i {
            margin-left: 6px;
            font-size: 13px;
        }
        /* أيقونة الهمبرجر للموبايل */
        .top-bar .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        /* ===== MIDDLE BAR (height ~2cm) ===== */
        .middle-bar {
            background: #ffffff;
            padding: 6px 16px;
            height: auto;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
        }
        /* اللوجو */
        .logo-area {
            flex-shrink: 0;
            height: 48px;
            display: flex;
            align-items: center;
        }
        .logo-area img {
            height: 100%;
            width: auto;
            max-width: 140px;
            object-fit: contain;
        }

        /* ===== حاوية البحث الكاملة ===== */
        .search-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 750px;
            margin: 0 auto;
            width: 100%;
            min-width: 200px;
        }

        /* تبويبات البحث (Products, Suppliers, Worldwide) */
        .search-tabs {
            display: flex;
            gap: 2px;
            padding: 0 2px;
        }
        .search-tab {
            background: transparent;
            border: none;
            padding: 4px 14px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }
        .search-tab:hover {
            color: #000;
            background: #f1f3f5;
        }
        .search-tab.active {
            color: #000;
            font-weight: 700;
            border-bottom-color: #1a1a1a;
            background: #f8f9fa;
        }

        /* حقل البحث مع العلم */
        .search-row {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        /* زر تغيير البلد (يظهر علم واسم الدولة) */
        .country-btn {
            background: #f1f3f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 13px;
            flex-shrink: 0;
            height: 40px;
            transition: 0.2s;
            min-width: 70px;
        }
        .country-btn:hover {
            background: #e9ecef;
        }
        .country-btn img {
            width: 28px;
            height: 20px;
            border-radius: 3px;
            object-fit: cover;
        }
        .country-btn .country-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.2;
        }
        .country-btn .country-name {
            font-weight: 600;
            color: #222;
            font-size: 12px;
        }
        .country-btn .country-code {
            font-size: 9px;
            color: #777;
        }
        .country-btn i {
            font-size: 10px;
            color: #666;
            margin-left: auto;
        }

        /* حقل البحث */
        .search-box {
            flex: 1;
            display: flex;
            align-items: center;
            background: #f1f3f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            height: 40px;
            padding: 0 12px;
            transition: 0.2s;
            min-width: 120px;
        }
        .search-box:focus-within {
            border-color: #1a1a1a;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
        }
        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 0;
            font-size: 14px;
            outline: none;
            color: #333;
            min-width: 50px;
        }
        .search-box input::placeholder {
            color: #999;
            font-size: 13px;
        }
        .search-box button {
            background: transparent;
            border: none;
            color: #1a1a1a;
            font-size: 18px;
            cursor: pointer;
            padding: 0 4px;
        }

        /* زر Post Requirement */
        .post-btn {
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0 20px;
            height: 40px;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .post-btn:hover {
            background: #000000;
            transform: scale(1.02);
        }
        .post-btn i {
            font-size: 16px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .middle-bar {
                gap: 8px;
            }
            .search-wrapper {
                max-width: 100%;
                order: 3;
                flex-basis: 100%;
                margin-top: 4px;
            }
            .logo-area {
                height: 40px;
            }
            .post-btn {
                height: 36px;
                font-size: 12px;
                padding: 0 14px;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                height: 38px;
                padding: 0 10px;
                gap: 4px;
            }
            .top-bar .tab-btn {
                font-size: 12px;
                padding: 4px 8px;
                height: 28px;
            }
            .top-bar .tab-btn i {
                font-size: 11px;
                margin-left: 4px;
            }
            .top-bar .menu-toggle {
                display: block;
            }
            .top-bar .tabs .tab-btn:nth-child(n+4) {
                display: none;
            }

            .middle-bar {
                padding: 4px 10px;
                gap: 6px;
            }
            .logo-area {
                height: 34px;
            }
            .logo-area img {
                max-width: 90px;
            }
            .country-btn {
                height: 34px;
                padding: 4px 8px;
                font-size: 11px;
                min-width: 50px;
            }
            .country-btn img {
                width: 22px;
                height: 16px;
            }
            .country-btn .country-name {
                font-size: 10px;
            }
            .country-btn .country-code {
                display: none;
            }
            .search-box {
                height: 34px;
                padding: 0 8px;
            }
            .search-box input {
                font-size: 12px;
            }
            .search-box input::placeholder {
                font-size: 11px;
            }
            .search-box button {
                font-size: 15px;
            }
            .post-btn {
                height: 34px;
                padding: 0 12px;
                font-size: 11px;
            }
            .post-btn i {
                font-size: 13px;
            }
            .search-tab {
                font-size: 11px;
                padding: 2px 10px;
            }
            .search-wrapper {
                gap: 2px;
            }
            .search-row {
                gap: 4px;
            }
        }

        @media (max-width: 480px) {
            .top-bar .tab-btn {
                font-size: 10px;
                padding: 2px 6px;
                height: 24px;
            }
            .top-bar .tab-btn i {
                font-size: 9px;
            }
            .middle-bar {
                padding: 2px 6px;
            }
            .logo-area img {
                max-width: 60px;
            }
            .country-btn {
                height: 30px;
                padding: 2px 6px;
                min-width: 40px;
            }
            .country-btn img {
                width: 18px;
                height: 13px;
            }
            .country-btn .country-name {
                font-size: 9px;
            }
            .country-btn i {
                font-size: 8px;
            }
            .search-box {
                height: 30px;
                padding: 0 6px;
            }
            .search-box input {
                font-size: 11px;
                min-width: 30px;
            }
            .search-box input::placeholder {
                font-size: 10px;
            }
            .search-box button {
                font-size: 13px;
            }
            .post-btn {
                height: 30px;
                padding: 0 8px;
                font-size: 10px;
            }
            .post-btn i {
                font-size: 11px;
            }
            .search-tab {
                font-size: 10px;
                padding: 2px 6px;
            }
        }
    </style>
</head>
<body>
<!-- بداية الهيدر الجديد -->
<div class="new-header">

    <!-- الشريط العلوي (1 سم) -->
    <div class="top-bar">
        <div class="tabs">
            <button class="tab-btn active" id="tabB2B">
                <i class="fas fa-store"></i> B2B Mart
            </button>
            <button class="tab-btn" id="tabBuyLeads" onclick="window.location.href='https://egyptmart.shop/buyleads.php';">
                <i class="fas fa-shopping-cart"></i> Buy Leads
            </button>
            <button class="tab-btn" id="tabSaleOffers" onclick="window.location.href='https://egyptmart.shop/sale-offers.php';">
                <i class="fas fa-tags"></i> Sale Offers
            </button>
            <button class="tab-btn" id="tabCategories" onclick="window.location.href='https://egyptmart.shop/dir.php';">
                <i class="fas fa-th-list"></i> Categories
            </button>
            <button class="tab-btn" id="tabSocial" onclick="window.location.href='https://egyptmart.shop/advertise-with-us.php';">
                <i class="fas fa-share-alt"></i> Social Publish
            </button>
        </div>
        <button class="menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- الشريط الأوسط (2 سم) -->
    <div class="middle-bar">
        <!-- اللوجو -->
        <div class="logo-area">
            <a href="https://egyptmart.shop" title="EgyptMART">
                <img src="https://egyptmart.shop/sitelogo/logo6744egyptmart%20logo%20SHOP%20copy.png" alt="EgyptMART Logo">
            </a>
        </div>

        <!-- حاوية البحث الكاملة -->
        <div class="search-wrapper">
            <!-- تبويبات البحث: Products | Suppliers | Worldwide -->
            <div class="search-tabs" id="searchTabs">
                <button class="search-tab active" data-tab="products">Products</button>
                <button class="search-tab" data-tab="suppliers">Suppliers</button>
                <button class="search-tab" data-tab="worldwide">Worldwide</button>
            </div>

            <!-- صف البحث: العلم + حقل البحث -->
            <div class="search-row">
                <!-- زر الدولة (يظهر علم واسم الدولة حسب IP) -->
                <button class="country-btn" id="countrySelector" title="تغيير البلد">
                    <img id="countryFlag" src="https://egyptmart.shop/images/country_flag/Global$download.png" alt="Country">
                    <span class="country-info">
                        <span class="country-name" id="countryName">جاري التحميل...</span>
                        <span class="country-code" id="countryCode">Global</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </button>

                <!-- حقل البحث -->
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="ابحث عن منتجات، موردين...">
                    <button id="searchBtn"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>

        <!-- زر Post Requirement -->
        <button class="post-btn" id="postReqBtn" onclick="window.location.href='https://egyptmart.shop/product-sel-cat.php';">
            <i class="fas fa-plus-circle"></i> Post Requirement
        </button>
    </div>
</div>
<!-- نهاية الهيدر الجديد -->

<!-- ===== كود JavaScript لتشغيل كل الوظائف ===== -->
<script>
    (function() {
        'use strict';

        // ======== 1. التحكم في تبويبات الهيدر العلوي ========
        const topTabs = document.querySelectorAll('.top-bar .tab-btn');
        topTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                if (this.id === 'tabB2B') {
                    e.preventDefault();
                    topTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    return;
                }
                topTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
        // تفعيل B2B Mart افتراضياً
        const defaultTopTab = document.getElementById('tabB2B');
        if (defaultTopTab) defaultTopTab.classList.add('active');

        // ======== 2. التحكم في تبويبات البحث (Products, Suppliers, Worldwide) ========
        const searchTabs = document.querySelectorAll('.search-tab');
        const searchInput = document.getElementById('searchInput');
        let currentTab = 'products'; // الافتراضي

        // تعيين placeholder حسب التبويب
        function updatePlaceholder(tab) {
            const placeholders = {
                'products': 'ابحث عن منتجات...',
                'suppliers': 'ابحث عن موردين...',
                'worldwide': 'بحث عالمي...'
            };
            if (searchInput) {
                searchInput.placeholder = placeholders[tab] || 'ابحث...';
            }
            currentTab = tab;
        }

        searchTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                searchTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const tabValue = this.getAttribute('data-tab');
                updatePlaceholder(tabValue);
            });
        });

        // تفعيل التبويب الأول افتراضياً
        const defaultSearchTab = document.querySelector('.search-tab[data-tab="products"]');
        if (defaultSearchTab) {
            defaultSearchTab.classList.add('active');
            updatePlaceholder('products');
        }

        // ======== 3. وظيفة البحث ========
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn && searchInput) {
            function performSearch() {
                const query = searchInput.value.trim();
                if (query.length === 0) {
                    alert('الرجاء إدخال كلمة بحث');
                    return;
                }
                // تحديد نوع البحث
                let searchType = '';
                const activeTab = document.querySelector('.search-tab.active');
                if (activeTab) {
                    searchType = activeTab.getAttribute('data-tab');
                }
                // بناء الرابط مع parameters
                let url = 'https://egyptmart.shop/search.php?keywords=' + encodeURIComponent(query);
                if (searchType === 'suppliers') {
                    url += '&type=suppliers';
                } else if (searchType === 'worldwide') {
                    url += '&scope=global';
                }
                // إضافة معامل البلد إذا كان محدداً (للبحث المحلي)
                const countryCode = document.getElementById('countryCode')?.innerText || '';
                if (searchType !== 'worldwide' && countryCode && countryCode !== 'Global') {
                    url += '&country=' + encodeURIComponent(countryCode);
                }
                window.location.href = url;
            }

            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                performSearch();
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
        }

        // ======== 4. وظيفة تغيير البلد (تعتمد على IP) ========
        const countryFlag = document.getElementById('countryFlag');
        const countryName = document.getElementById('countryName');
        const countryCode = document.getElementById('countryCode');

        // دالة لجلب بيانات الدولة من IP
        function getUserCountry() {
            // محاولة استخدام Geolocation API أو خدمة خارجية
            if (navigator.geolocation) {
                // استخدام موقع المتصفح (قد يطلب إذن)
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        // استخدام خدمة عكسية للجغرافيا
                        fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=ar`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.countryCode) {
                                    updateCountryDisplay(data.countryCode, data.countryName, data.principalSubdivision);
                                } else {
                                    fallbackIPGeolocation();
                                }
                            })
                            .catch(() => fallbackIPGeolocation());
                    },
                    function() {
                        // إذا رفض المستخدم أو فشل الـ Geolocation، نستخدم الـ IP
                        fallbackIPGeolocation();
                    }
                );
            } else {
                fallbackIPGeolocation();
            }
        }

        // دالة احتياطية تعتمد على IP
        function fallbackIPGeolocation() {
            fetch('https://ipapi.co/json/')
                .then(response => response.json())
                .then(data => {
                    if (data && data.country_code) {
                        updateCountryDisplay(data.country_code, data.country_name, data.region);
                    } else {
                        // في حال فشل كل شيء، نستخدم الـ IP من خلال خدمة أخرى
                        fetch('https://ipinfo.io/json')
                            .then(res => res.json())
                            .then(info => {
                                if (info && info.country) {
                                    updateCountryDisplay(info.country, info.country_name || info.country, info.region);
                                } else {
                                    setDefaultCountry();
                                }
                            })
                            .catch(() => setDefaultCountry());
                    }
                })
                .catch(() => setDefaultCountry());
        }

        // دالة لتحديث واجهة العرض بالدولة
        function updateCountryDisplay(code, name, region) {
            if (!code) { setDefaultCountry(); return; }
            const countryCodeLower = code.toLowerCase();
            // محاولة جلب العلم من ملفات الموقع
            const flagUrl = `https://egyptmart.shop/images/country_flag/${code}.png`;
            // التحقق من وجود العلم، وإلا استخدم default
            fetch(flagUrl, { method: 'HEAD' })
                .then(res => {
                    if (res.ok) {
                        countryFlag.src = flagUrl;
                    } else {
                        countryFlag.src = 'https://egyptmart.shop/images/country_flag/Global$download.png';
                    }
                })
                .catch(() => {
                    countryFlag.src = 'https://egyptmart.shop/images/country_flag/Global$download.png';
                });

            countryName.innerText = name || code;
            countryCode.innerText = code.toUpperCase();
        }

        // تعيين الدولة الافتراضية (إذا فشل كل شيء)
        function setDefaultCountry() {
            countryFlag.src = 'https://egyptmart.shop/images/country_flag/Global$download.png';
            countryName.innerText = 'Global';
            countryCode.innerText = 'Global';
        }

        // استدعاء دالة جلب الدولة عند تحميل الصفحة
        getUserCountry();

        // عند النقر على زر الدولة، يمكن فتح نافذة لاختيار دولة أو تحديث
        const countryBtn = document.getElementById('countrySelector');
        if (countryBtn) {
            countryBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // يمكن فتح نافذة منبثقة لاختيار الدولة يدوياً
                // أو إعادة تحميل الموقع لتحديث الـ IP
                window.location.href = 'https://egyptmart.shop/change-country.php';
            });
        }

        // ======== 5. زر القائمة للموبايل ========
        const menuToggle = document.getElementById('mobileMenuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                const tabsContainer = document.querySelector('.top-bar .tabs');
                if (tabsContainer) {
                    const hiddenTabs = tabsContainer.querySelectorAll('.tab-btn:nth-child(n+4)');
                    hiddenTabs.forEach(tab => {
                        if (tab.style.display === 'none' || tab.style.display === '') {
                            tab.style.display = 'inline-flex';
                        } else {
                            tab.style.display = 'none';
                        }
                    });
                }
            });
        }

        // ======== 6. معالجة الموبايل ========
        function handleMobileTabs() {
            const width = window.innerWidth;
            const tabsContainer = document.querySelector('.top-bar .tabs');
            if (!tabsContainer) return;
            const hiddenTabs = tabsContainer.querySelectorAll('.tab-btn:nth-child(n+4)');
            if (width <= 768) {
                hiddenTabs.forEach(tab => {
                    tab.style.display = 'none';
                });
            } else {
                hiddenTabs.forEach(tab => {
                    tab.style.display = 'inline-flex';
                });
            }
        }
        window.addEventListener('load', handleMobileTabs);
        window.addEventListener('resize', handleMobileTabs);

    })();
</script>

<!-- استمرار بقية محتوى الصفحة -->