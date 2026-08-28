<?php
/**
 * File: addNewTender.php

 * Version: PHP 8.3
 * Description: إضافة مناقصة جديدة مع التحقق من الكلمات المحظورة وإدخال البيانات في قاعدة البيانات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تشغيل عرض الأخطاء (للتصحيح فقط - قم بإيقافه في الإنتاج)
error_reporting(1);

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

// تهيئة المتغيرات
$valid = true;
$data = ['0', ''];

// التحقق من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    $data[0] = "0";
    $data[1] = 'يجب تسجيل الدخول أولاً';
    echo implode("|", $data);
    exit();
}

// جلب المتغيرات من GET وتنظيفها
$tnd_usr_id = isset($_GET['tnd_usr_id']) ? (int)$_GET['tnd_usr_id'] : 0;
$main_cat = isset($_GET['main_cat']) ? trim($_GET['main_cat']) : '';
$pc_id = isset($_GET['pc_id']) ? trim($_GET['pc_id']) : '';
$tnd_pc_id = isset($_GET['tnd_pc_id']) ? trim($_GET['tnd_pc_id']) : '';
$tnd_heading = isset($_GET['tnd_heading']) ? trim($_GET['tnd_heading']) : '';
$tnd_value = isset($_GET['tnd_value']) ? trim($_GET['tnd_value']) : '';
$tnd_currency = isset($_GET['tnd_currency']) ? trim($_GET['tnd_currency']) : '';

$tnd_notice_type = isset($_GET['tnd_notice_type']) ? trim($_GET['tnd_notice_type']) : '';
$tnd_qty = isset($_GET['tnd_qty']) ? trim($_GET['tnd_qty']) : '';
$tnd_qty_mu_id = isset($_GET['tnd_qty_mu_id']) ? (int)$_GET['tnd_qty_mu_id'] : 0;
$tnd_emd = isset($_GET['tnd_emd']) ? trim($_GET['tnd_emd']) : '';
$tnd_document_fees = isset($_GET['tnd_document_fees']) ? trim($_GET['tnd_document_fees']) : '';
$tnd_document_fees_currency = isset($_GET['tnd_document_fees_currency']) ? trim($_GET['tnd_document_fees_currency']) : '';
$tnd_project_period = isset($_GET['tnd_project_period']) ? trim($_GET['tnd_project_period']) : '';
$tnd_products = isset($_GET['tnd_products']) ? trim($_GET['tnd_products']) : '';

$tnd_publish_date = isset($_GET['tnd_publish_date']) ? trim($_GET['tnd_publish_date']) : '';
$tnd_docSaleStart_date = isset($_GET['tnd_docSaleStart_date']) ? trim($_GET['tnd_docSaleStart_date']) : '';
$tnd_docSaleEnd_date = isset($_GET['tnd_docSaleEnd_date']) ? trim($_GET['tnd_docSaleEnd_date']) : '';
$tnd_docSubmitBefore_date = isset($_GET['tnd_docSubmitBefore_date']) ? trim($_GET['tnd_docSubmitBefore_date']) : '';
$tnd_due_date = isset($_GET['tnd_due_date']) ? trim($_GET['tnd_due_date']) : '';

$tnd_prequalification_criteria = isset($_GET['tnd_prequalification_criteria']) ? trim($_GET['tnd_prequalification_criteria']) : '';
$tnd_details = isset($_GET['tnd_details']) ? trim($_GET['tnd_details']) : '';
$tnd_preferred_location = isset($_GET['tnd_preferred_location']) ? trim($_GET['tnd_preferred_location']) : '';

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
        echo implode("|", $data);
        exit();
    } else if ($pc_id == "") {
        $data[0] = "0";
        $data[1] = 'Kindly select Category.';
        $valid = false;
        echo implode("|", $data);
        exit();
    } else if ($tnd_pc_id == "") {
        $data[0] = "0";
        $data[1] = 'Kindly select Sub-Category.';
        $valid = false;
        echo implode("|", $data);
        exit();
    }
} elseif ($typeofselection) {
    if ($keywordsFilter == "") {
        $data[0] = "0";
        $data[1] = 'Kindly enter Keyword.';
        $valid = false;
        echo implode("|", $data);
        exit();
    }
    
    $searchedproducts = isset($_SESSION['searchedproducts']) ? $_SESSION['searchedproducts'] : [];
    
    if (empty($searchedproducts) || !array_key_exists($keywordsFilter, $searchedproducts)) {
        $data[0] = "0";
        $data[1] = 'No category found with given keywords';
        $valid = false;
        echo implode("|", $data);
        exit();
    }
    
    $keywordsFilter = explode(">>", $keywordsFilter);
    $keywordsFilter1 = end($keywordsFilter);
    $tnd_pc_id = isset($searchedproducts[$keywordsFilter1]) ? $searchedproducts[$keywordsFilter1] : '';
    $pc_id = isset($keywordsFilter[1]) && isset($searchedproducts[$keywordsFilter[1]]) ? $searchedproducts[$keywordsFilter[1]] : '';
    $main_cat = isset($keywordsFilter[0]) && isset($searchedproducts[$keywordsFilter[0]]) ? $searchedproducts[$keywordsFilter[0]] : '';
    
    if (!$tnd_pc_id) {
        $data[0] = "0";
        $data[1] = 'No category found with given keywords';
        $valid = false;
        echo implode("|", $data);
        exit();
    }
} else if ($tnd_heading == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Tender Heading.';
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_heading != "" && checkBadWord(strtoupper($tnd_heading)) == false) {
    $data[0] = "0";
    $data[1] = "You can't post this Tender Heading. It contains some Bad words.";
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_notice_type == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Notice Type.';
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_document_fees == "") {
    $data[0] = "0";
    $data[1] = 'Kindly enter Document Fees.';
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_document_fees_currency == "") {
    $data[0] = "0";
    $data[1] = 'Kindly select currency for Document Fees.';
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_prequalification_criteria == "") {
    $data[0] = "0";
    $data[1] = 'Kindly describe Pre-qualification Criteria.';
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_prequalification_criteria != "" && checkBadWord(strtoupper($tnd_prequalification_criteria)) == false) {
    $data[0] = "0";
    $data[1] = "You can't post this Pre-qualification Criteria. It contains some Bad words.";
    $valid = false;
    echo implode("|", $data);
    exit();
} else if ($tnd_details == "") {
    $data[0] = "0";
    $data[1] = 'Kindly describe Tender details.';
    $valid = false;
    echo implode("|", $data);
    exit();
}

if ($valid) {
    global $con;
    
    // إضافة إلى temp_tender_alert_cat
    $sql1 = "INSERT INTO temp_tender_alert_cat
             SET
                 ttac_usr_id = " . (int)$_SESSION['uid_indm'] . ",
                 ttac_pc_id = '" . mysqli_real_escape_string($con, $tnd_pc_id) . "',
                 ttac_updated_date = NOW()";
    mysqli_query($con, $sql1);
    
    // نقل البيانات إلى tender_alert_category
    $sql2 = "SELECT * FROM temp_tender_alert_cat WHERE ttac_usr_id = " . (int)$_SESSION['uid_indm'];
    $res2 = mysqli_query($con, $sql2);
    
    if ($res2) {
        while ($row = mysqli_fetch_object($res2)) {
            $sql_exist = "SELECT * FROM tender_alert_category 
                         WHERE tac_usr_id = " . (int)$_SESSION['uid_indm'] . " 
                           AND tac_pc_id = '" . mysqli_real_escape_string($con, $row->ttac_pc_id) . "'";
            $res12 = mysqli_query($con, $sql_exist);
            
            if ($res12 && mysqli_num_rows($res12) == 0) {
                $sql_ins = "INSERT INTO tender_alert_category
                           SET
                               tac_usr_id = " . (int)$_SESSION['uid_indm'] . ",
                               tac_pc_id = '" . mysqli_real_escape_string($con, $row->ttac_pc_id) . "',
                               tac_updated_date = NOW()";
                mysqli_query($con, $sql_ins);
            }
        }
    }
    
    // حذف البيانات المؤقتة
    mysqli_query($con, "DELETE FROM temp_tender_alert_cat WHERE ttac_usr_id = " . (int)$_SESSION['uid_indm']);
    
    // التحقق من وجود الفئة في تنبيهات المناقصات
    $sql_exist = "SELECT * FROM tender_alert_category 
                 WHERE tac_usr_id = " . (int)$_SESSION['uid_indm'] . " 
                   AND tac_pc_id = '" . mysqli_real_escape_string($con, $tnd_pc_id) . "'";
    $res12 = mysqli_query($con, $sql_exist);
    
    if ($res12 && mysqli_num_rows($res12) == 0) {
        $sql_ins = "INSERT INTO tender_alert_category
                   SET
                       tac_usr_id = " . (int)$_SESSION['uid_indm'] . ",
                       tac_pc_id = '" . mysqli_real_escape_string($con, $tnd_pc_id) . "',
                       tac_updated_date = NOW()";
        mysqli_query($con, $sql_ins);
    }
    
    // تحويل التواريخ من تنسيق dd/mm/yyyy إلى yyyy-mm-dd
    $publish_date = str_replace('/', '-', $tnd_publish_date);
    $docSaleStart_date = str_replace('/', '-', $tnd_docSaleStart_date);
    $docSaleEnd_date = str_replace('/', '-', $tnd_docSaleEnd_date);
    $docSubmitBefore_date = str_replace('/', '-', $tnd_docSubmitBefore_date);
    $due_date = str_replace('/', '-', $tnd_due_date);
    
    // إدخال بيانات المناقصة
    $sql = "INSERT INTO tender
            SET
                tnd_usr_id = " . (int)$tnd_usr_id . ",
                tnd_pc_id = '" . mysqli_real_escape_string($con, $tnd_pc_id) . "',
                tnd_heading = '" . mysqli_real_escape_string($con, $tnd_heading) . "',
                tnd_value = '" . mysqli_real_escape_string($con, $tnd_value) . "',
                tnd_currency = '" . mysqli_real_escape_string($con, $tnd_currency) . "',
                tnd_notice_type = '" . mysqli_real_escape_string($con, $tnd_notice_type) . "',
                tnd_qty = '" . mysqli_real_escape_string($con, $tnd_qty) . "',
                tnd_qty_mu_id = " . (int)$tnd_qty_mu_id . ",
                tnd_emd = '" . mysqli_real_escape_string($con, $tnd_emd) . "',
                tnd_document_fees = '" . mysqli_real_escape_string($con, $tnd_document_fees) . "',
                tnd_document_fees_currency = '" . mysqli_real_escape_string($con, $tnd_document_fees_currency) . "',
                tnd_project_period = '" . mysqli_real_escape_string($con, $tnd_project_period) . "',
                tnd_products = '" . mysqli_real_escape_string($con, $tnd_products) . "',
                tnd_prequalification_criteria = '" . mysqli_real_escape_string($con, $tnd_prequalification_criteria) . "',
                tnd_details = '" . mysqli_real_escape_string($con, $tnd_details) . "',
                tnd_preferred_location = '" . mysqli_real_escape_string($con, $tnd_preferred_location) . "',
                tnd_publish_date = '" . date('Y-m-d', strtotime($publish_date)) . "',
                tnd_docSaleStart_date = '" . date('Y-m-d', strtotime($docSaleStart_date)) . "',
                tnd_docSaleEnd_date = '" . date('Y-m-d', strtotime($docSaleEnd_date)) . "',
                tnd_docSubmitBefore_date = '" . date('Y-m-d', strtotime($docSubmitBefore_date)) . "',
                tnd_due_date = '" . date('Y-m-d', strtotime($due_date)) . "',
                tnd_updated_date = NOW()";
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        $data[0] = "0";
        $data[1] = 'Error posting tender: ' . mysqli_error($con);
        echo implode("|", $data);
        exit();
    }
    
    $tnd = mysqli_insert_id($con);
    
    // إضافة المعلومات الإضافية إذا وجدت
    if ($af_id != '' && $afv_val != '') {
        $tav_af_id = explode("|", $af_id);
        $tav_value = explode("|", $afv_val);
        
        for ($i = 0; $i < count($tav_af_id); $i++) {
            $val = explode("-", $tav_value[$i] ?? '');
            for ($c = 0; $c < count($val); $c++) {
                if (!empty($val[$c])) {
                    $sql_sav = "INSERT INTO tender_additional_value
                               SET
                                   tav_tnd_id = " . (int)$tnd . ",
                                   tav_af_id = '" . mysqli_real_escape_string($con, $tav_af_id[$i] ?? '') . "',
                                   tav_value = '" . mysqli_real_escape_string($con, $val[$c]) . "',
                                   tav_status = '1'";
                    mysqli_query($con, $sql_sav);
                }
            }
        }
    }
    
    $data[0] = "1";
    $data[1] = 'Tender posted successfully.';
}

echo implode("|", $data);

// إنهاء المخزن المؤقت
ob_end_flush();
?>