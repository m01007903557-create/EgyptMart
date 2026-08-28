<?php
/**
 * File: includes/style.php
 * Version: PHP 8.3 Compatible
 * Description: أنماط CSS لتحسين استجابة رأس الموقع على مختلف أحجام الشاشات
 * 
 * هذا الملف يحتوي على أنماط مخصصة لتحسين ظهور رأس الموقع
 * على الأجهزة المختلفة والشاشات ذات الأحجام المتنوعة
 */
?>
<style>
/* الأنماط الأساسية لتحسين الاستجابة */
@media (max-width: 1300px) and (min-width: 1200px) {
    /* تحسين زر طلب الشراء */
    .post-buy-req-btn {
        height: auto;
        padding: 4px 0;
    }
    
    /* تحسين مربع البحث */
    .srchBx {
        margin-left: 0px !important;
    }
    
    /* تحسين النص المتحرك */
    .cd-words-wrapper b {
        text-align: left !important;
        padding: 0px !important;
        margin: 0px !important;
    }
    
    /* تحسين القائمة الجانبية */
    #block_navigation .navigation .ptag:hover {
        width: 230px;
    }
    
    /* تحسين مربع البحث عند التفاعل */
    #search-box1:hover,
    #search-box1:focus {
        box-shadow: 0 3px 8px 0 rgba(0,0,0,0.2), 0 0 0 1px rgba(0,0,0,0.08);
    }
    
    /* تحسين نتائج البحث */
    .page-header-col1-row2-col2-form #suggesstionBoxs #country-list {
        margin-top: 4px;
        border-bottom: 1px solid #006bb1;
        border-left: 1px solid #006bb1;
        border-right: 1px solid #006bb1;
    }
    
    /* تحسين عرض موقع المستخدم */
    .page-header-col1-row1-col1_row2_pic#cnlocation span {
        line-height: 1;
    }
    
    /* تحسين اسم المستخدم */
    .user-name-topbar {
        color: #FFF;
    }
    
    /* تحسين موقع أيقونة البلد */
    .page-header-col1-row1-col1_row2 {
        margin-left: 52px !important;
        padding: 65px 0 0 !important;
        margin-top:80px;
    }
    
    /* تحسين القائمة العلوية */
    #topbar .top-lft ul li {
        float: left;
    }
    
    #topbar .top-lft ul {
        line-height: 1;
        padding-top: 15px;
    }
}
</style>