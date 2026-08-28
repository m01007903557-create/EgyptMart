<?php
/**
 * File: name-var.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: ملف الثوابت العامة للنظام - جداول قاعدة البيانات وأسماء الصفحات
 * System constants file - Database table names and page names
 * 
 * Features:
 * - تعريفات جداول قاعدة البيانات
 * - أسماء الصفحات الرئيسية
 * - مصفوفات الصفحات والمربعات الجانبية
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('IN_ADMIN_PANEL')) {
    //exit('Direct access not allowed');
//}

// ============================================
// Database Table Name Constants
// ============================================

/** @var string Admin users table */
define('TB_ADMIN_USER', 'admin_user');

/** @var string Admin login logs table */
define('TB_ADMIN_LOG', 'admin_login_details');

/** @var string CMS pages table */
define('TB_CMS', 'cms');

/** @var string Contact us messages table */
define('TB_CONTACT_US', 'contact_us');

/** @var string Career applications table */
define('TB_CAREER', 'career');

/** @var string Main types table */
define('TB_TYPE', 'main_type');

/** @var string Technology table */
define('TB_TECH', 'technology');

/** @var string Categories table */
define('TB_CAT', 'category');

/** @var string Category types table */
define('TB_CAT_TYPE', 'category_type');

/** @var string Testimonials table */
define('TB_TESTIMONIAL', 'testimonial');

/** @var string News table */
define('TB_NEWS', 'news');

/** @var string Portfolio items table */
define('TB_PORTFOLIO', 'portfolio');

/** @var string Portfolio categories table */
define('TB_PF_CAT', 'portfolio_category');

/** @var string Portfolio technologies table */
define('TB_PF_TECH', 'portfolio_technology');

/** @var string Portfolio types table */
define('TB_PF_TYPE', 'portfolio_type');

/** @var string Right box pages table */
define('TB_RGTBOX_PAGE', 'rightbox_page');

/** @var string Right box includes table */
define('TB_RGTBOX_INCLUDE', 'rightbox_include');

/** @var string Job openings table */
define('TB_OPENINGS', 'openings');

/** @var string SEO settings table */
define('TB_SEO', 'seo');

// ============================================
// Page Name Constants
// ============================================

/** @var string Portfolio page */
define('PG_PORTFOLIO', 'portfolio.php');

/** @var string Contact us page */
define('PG_CONTACT_US', 'contact-us.php');

// ============================================
// Page Box Array
// ============================================

/**
 * Pages for right box display
 * @var array<string> List of page names
 */
$page_box = [
    'Home',
    'About Us',
    'Our Mission',
    'Our Vision',
    'Relation',
    'History',
    'Who we are',
    'Portfolio',
    'Services',
    'Services Child',
    'Inquiry',
    'Schedule An Appointment',
    'Contact Details',
    'Office Location',
    'Testimonial',
    'News',
    'Openings',
    'Disclaimer & Privacy Policy',
    'Terms & Conditions',
    'Career',
    'Referrel',
    'Flash Animation',
    'Dedicated Designer',
    'Dedicated Programmer',
    'Gallery'
];

// ============================================
// Right Box Array
// ============================================

/**
 * Right box content types
 * @var array<string> List of right box types
 */
$right_box = [
    'Service Flash',
    'Service Menu',
    'Hire Us',
    'Gallery',
    'Project Profile',
    'Forward Profile',
    'Contact',
    'Career',
    'Testimonial',
    'Video'
];

/**
 * Get all page box names
 * 
 * @return array<string> Page box array
 */
function getPageBoxNames(): array {
    global $page_box;
    return $page_box;
}

/**
 * Get all right box types
 * 
 * @return array<string> Right box array
 */
function getRightBoxTypes(): array {
    global $right_box;
    return $right_box;
}

/**
 * Check if a page exists in page box
 * 
 * @param string $pageName Page name to check
 * @return bool True if exists
 */
function isPageInBox(string $pageName): bool {
    global $page_box;
    return in_array($pageName, $page_box, true);
}

/**
 * Check if a type exists in right box
 * 
 * @param string $type Type to check
 * @return bool True if exists
 */
function isRightBoxType(string $type): bool {
    global $right_box;
    return in_array($type, $right_box, true);
}

/**
 * Get table name with prefix if needed
 * 
 * @param string $constant Table constant name
 * @return string Table name
 */
function getTableName(string $constant): string {
    return defined($constant) ? constant($constant) : '';
}

/**
 * Get page URL by constant
 * 
 * @param string $constant Page constant name
 * @return string Page URL
 */
function getPageUrl(string $constant): string {
    return defined($constant) ? constant($constant) : '';
}
?>