<?php
/**
 * File: addNewAuction.php

 * Version: PHP 8.3
 * Description: إضافة مزاد جديد مع التحقق من الكلمات المحظورة وإدخال البيانات في قاعدة البيانات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

/**
 * التحقق من وجود كلمات محظورة في النص
 * @param string $param النص المراد فحصه
 * @return bool true إذا كان النص سليماً، false إذا وجدت كلمات محظورة
 */
function checkBadWord($param): bool
{
    global $con;
    $valid = true;
    
    // جلب الكلمات المحظورة من قاعدة البيانات
    $sqlrpl = "SELECT bd_word FROM bad_word";
    $resrpl = mysqli_query($con, $sqlrpl);
    
    if (!$resrpl) {
        error_log("خطأ في جلب الكلمات المحظورة: " . mysqli_error($con));
        return $valid;
    }
    
    $letters = [];
    while ($rowrpl = mysqli_fetch_object($resrpl)) {
        if (!empty($rowrpl->bd_word)) {
            $letters[] = strtoupper(trim($rowrpl->bd_word));
        }
    }
    
    $param_upper = strtoupper($param);
    foreach ($letters as $val) {
        if (!empty($val) && strpos($param_upper, $val) !== false) {
            $valid = false;
            break;
        }
    }
    
    return $valid;
}

// التحقق من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    $data[0] = "0";
    $data[1] = 'يجب تسجيل الدخول أولاً';
    echo implode("|", $data);
    exit();
}

// تهيئة المتغيرات
$valid = true;
$data = ['0', ''];

// جلب المتغيرات من GET وتنظيفها
$auc_usr_id = isset($_GET['auc_usr_id']) ? (int)$_GET['auc_usr_id'] : 0;
$main_cat = isset($_GET['main_cat']) ? trim($_GET['main_cat']) : '';
$pc_id = isset($_GET['pc_id']) ? trim($_GET['pc_id']) : '';
$auc_pc_id = isset($_GET['auc_pc_id']) ? trim($_GET['auc_pc_id']) : '';
$auc_heading = isset($_GET['auc_heading']) ? trim($_GET['auc_heading']) : '';
$auc_value = isset($_GET['auc_value']) ? trim($_GET['auc_value']) : '';
$auc_currency = isset($_GET['auc_currency']) ? trim($_GET['auc_currency']) : '';

$auc_notice_type = isset($_GET['auc_notice_type']) ? trim($_GET['auc_notice_type']) : '';
$auc_qty = isset($_GET['auc_qty']) ? trim($_GET['auc_qty']) : '';
$auc_qty_mu_id = isset($_GET['auc_qty_mu_id']) ? (int)$_GET['auc_qty_mu_id'] : 0;
$auc_emd = isset($_GET['auc_emd']) ? trim($_GET['auc_emd']) : '';
$auc_document_fees = isset($_GET['auc_document_fees']) ? trim($_GET['auc_document_fees']) : '';
$auc_document_fees_currency = isset($_GET['auc_document_fees_currency']) ? trim($_GET['auc_document_fees_currency']) : '';
$auc_project_period = isset($_GET['auc_project_period']) ? trim($_GET['auc_project_period']) : '';
$auc_products = isset($_GET['auc_products']) ? trim($_GET['auc_products']) : '';

$auc_publish_date = isset($_GET['auc_publish_date']) ? trim($_GET['auc_publish_date']) : '';
$auc_docSaleStart_date = isset($_GET['auc_docSaleStart_date']) ? trim($_GET['auc_docSaleStart_date']) : '';
$auc_docSaleEnd_date = isset($_GET['auc_docSaleEnd_date']) ? trim($_GET['auc_docSaleEnd_date']) : '';
$auc_docSubmitBefore_date = isset($_GET['auc_docSubmitBefore_date']) ? trim($_GET['auc_docSubmitBefore_date']) : '';
$auc_due_date = isset($_GET['auc_due_date']) ? trim($_GET['auc_due_date']) : '';

$auc_prequalification_criteria = isset($_GET['auc_prequalification_criteria']) ? trim($_GET['auc_prequalification_criteria']) : '';
$auc_details = isset($_GET['auc_details']) ? trim($_GET['auc_details']) : '';
$auc_preferred_location = isset($_GET['auc_preferred_location']) ? trim($_GET['auc_preferred_location']) : '';

$af_id = isset($_GET['af_id']) ? trim($_GET['af_id']) : '';
$afv_val = isset($_GET['afv_val']) ? trim($_GET['afv_val']) : '';

$typeofselection = isset($_GET['typeofselection']) ? trim($_GET['typeofselection']) : '';
$keywordsFilter = isset($_GET['keywordsFilter']) ? trim($_GET['keywordsFilter']) : '';

// التحقق من صحة البيانات حسب نوع الاختيار
if (!$typeofselection) {
    if ($main_cat == "") {
        $data[0] = "0";
        $data[1] = 'Kindly select Main Category.';
        $valid = false;
    } else if ($pc_id == "") {
        $data[0] = "0";
        $data[1] = 'Kindly select Category.';
        $valid = false;
    } else if ($auc_pc_id == "") {
        $data[0] = "0";
        $data[1] = 'Kindly select Sub-Category.';
        $valid = false;
    }
} elseif ($typeofselection) {
    if ($keywordsFilter == "") {
        $data[0] = "0";
        $data[1] = 'Kindly enter Keyword.';
        $valid = false;
    }
    
    $searchedproducts = isset($_SESSION['searchedproducts']) ? $_SESSION['searchedproducts'] : [];
    
    if (empty($searchedproducts) || !array_key_exists($keywordsFilter, $searchedproducts)) {
        $data[0] = "0";
        $data[1] = 'No category found with given keywords';
        $valid = false;
    }
    
    $keywordsFilter = explode(">>", $keywordsFilter);
    $keywordsFilter1 = end($keywordsFilter);
    $auc_pc_id = isset($searchedproducts[$keywordsFilter1]) ? $searchedproducts[$keywordsFilter1] : '';
    $pc_id = isset($keywordsFilter[1]) && isset($searchedproducts[$keywordsFilter[1]]) ? $searchedproducts[$keywordsFilter[1]] : '';
    $main_cat = isset($keywordsFilter[0]) && isset($searchedproducts[$keywordsFilter[0]]) ? $searchedproducts[$keywordsFilter[0]] : '';
    
    if (!$auc_pc_id) {
        $data[0] = "0";
        $data[1] = 'No category found with given keywords';
        $valid = false;
    }
} else if ($auc_heading == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Auction Heading.';
    $valid = false;
} else if ($auc_heading != "" && checkBadWord(strtoupper($auc_heading)) == false) {
    $data[0] = "0";
    $data[1] = "You can't post this Auction Heading. It contains some Bad words.";
    $valid = false;
} else if ($auc_notice_type == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Notice Type.';
    $valid = false;
} else if ($auc_document_fees == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Document Fees.';
    $valid = false;
} else if ($auc_document_fees_currency == "") {
    $data[0] = "0";
    $data[1] = 'Kindly select currency for Document Fees.';
    $valid = false;
} else if ($auc_prequalification_criteria == "") {
    $data[0] = "0";
    $data[1] = 'Kindly describe Pre-qualification Criteria.';
    $valid = false;
} else if ($auc_prequalification_criteria != "" && checkBadWord(strtoupper($auc_prequalification_criteria)) == false) {
    $data[0] = "0";
    $data[1] = "You can't post this Pre-qualification Criteria. It contains some Bad words.";
    $valid = false;
} else if ($auc_details == "") {
    $data[0] = "0";
    $data[1] = 'Kindly describe Auction details.';
    $valid = false;
}

if ($valid) {
    global $con;
    
    // تحويل التواريخ من تنسيق dd/mm/yyyy إلى yyyy-mm-dd
    $publish_date = str_replace('/', '-', $auc_publish_date);
    $docSaleStart_date = str_replace('/', '-', $auc_docSaleStart_date);
    $docSaleEnd_date = str_replace('/', '-', $auc_docSaleEnd_date);
    $docSubmitBefore_date = str_replace('/', '-', $auc_docSubmitBefore_date);
    $due_date = str_replace('/', '-', $auc_due_date);
    
    // إدخال بيانات المزاد
    $sql = "INSERT INTO auction
            SET
                auc_usr_id = " . (int)$auc_usr_id . ",
                auc_pc_id = '" . mysqli_real_escape_string($con, $auc_pc_id) . "',
                auc_heading = '" . mysqli_real_escape_string($con, $auc_heading) . "',
                auc_value = '" . mysqli_real_escape_string($con, $auc_value) . "',
                auc_currency = '" . mysqli_real_escape_string($con, $auc_currency) . "',
                auc_notice_type = '" . mysqli_real_escape_string($con, $auc_notice_type) . "',
                auc_qty = '" . mysqli_real_escape_string($con, $auc_qty) . "',
                auc_qty_mu_id = " . (int)$auc_qty_mu_id . ",
                auc_emd = '" . mysqli_real_escape_string($con, $auc_emd) . "',
                auc_document_fees = '" . mysqli_real_escape_string($con, $auc_document_fees) . "',
                auc_document_fees_currency = '" . mysqli_real_escape_string($con, $auc_document_fees_currency) . "',
                auc_project_period = '" . mysqli_real_escape_string($con, $auc_project_period) . "',
                auc_products = '" . mysqli_real_escape_string($con, $auc_products) . "',
                auc_prequalification_criteria = '" . mysqli_real_escape_string($con, $auc_prequalification_criteria) . "',
                auc_details = '" . mysqli_real_escape_string($con, $auc_details) . "',
                auc_preferred_location = '" . mysqli_real_escape_string($con, $auc_preferred_location) . "',
                auc_publish_date = '" . date('Y-m-d', strtotime($publish_date)) . "',
                auc_docSaleStart_date = '" . date('Y-m-d', strtotime($docSaleStart_date)) . "',
                auc_docSaleEnd_date = '" . date('Y-m-d', strtotime($docSaleEnd_date)) . "',
                auc_docSubmitBefore_date = '" . date('Y-m-d', strtotime($docSubmitBefore_date)) . "',
                auc_due_date = '" . date('Y-m-d', strtotime($due_date)) . "',
                auc_updated_date = NOW()";
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        $data[0] = "0";
        $data[1] = 'Error posting auction: ' . mysqli_error($con);
        echo implode("|", $data);
        exit();
    }
    
    $auc_id = mysqli_insert_id($con);
    
    // إضافة المعلومات الإضافية إذا وجدت
    if ($af_id != '' && $afv_val != '') {
        $tav_af_id = explode("|", $af_id);
        $aav_value = explode("|", $afv_val);
        
        for ($i = 0; $i < count($tav_af_id); $i++) {
            $val = explode("-", $aav_value[$i] ?? '');
            for ($c = 0; $c < count($val); $c++) {
                if (!empty($val[$c])) {
                    $sql_sav = "INSERT INTO auction_additional_value
                               SET
                                   aav_auc_id = " . (int)$auc_id . ",
                                   aav_af_id = '" . mysqli_real_escape_string($con, $tav_af_id[$i] ?? '') . "',
                                   aav_value = '" . mysqli_real_escape_string($con, $val[$c]) . "',
                                   aav_status = '1'";
                    mysqli_query($con, $sql_sav);
                }
            }
        }
    }
    
    $data[0] = "1";
    $data[1] = 'Auction posted successfully.';
}

echo implode("|", $data);

// إنهاء المخزن المؤقت
ob_end_flush();
?>