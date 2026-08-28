<?php
// ملف: company/includes/get_company_data.php
// هذا الملف يجلب بيانات الشركة من جدول business_profile

function getCompanyData($con) {
    $company_data = new stdClass();
    $company_data->usr_id = 0;
    $company_data->comp_name = '';
    $company_data->address1 = '';
    $company_data->address2 = '';
    $company_data->city = 0;
    $company_data->state = 0;
    $company_data->ceo_prefix = '';
    $company_data->ceo_fname = '';
    $company_data->ceo_lname = '';
    $company_data->comp_url = '';
    
    // محاولة جلب بيانات الشركة من الجلسة أو URL
    $bnsprof_uid = 0;
    
    if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
        $bnsprof_uid = $_SESSION['uid_indm'];
    } elseif (isset($_GET['uid']) && !empty($_GET['uid'])) {
        $bnsprof_uid = intval($_GET['uid']);
    } elseif (isset($row) && isset($row->usr_id)) {
        $bnsprof_uid = $row->usr_id;
    }
    
    if ($bnsprof_uid > 0) {
        $query = mysqli_query($con, "SELECT * FROM business_profile WHERE bnsprof_uid = '$bnsprof_uid' AND bnsprof_status = '1'");
        $result = mysqli_fetch_object($query);
        
        if ($result) {
            $company_data->usr_id = $bnsprof_uid;
            $company_data->comp_name = $result->bnsprof_compname ?? '';
            $company_data->address1 = $result->bnsprof_address1 ?? '';
            $company_data->address2 = $result->bnsprof_address2 ?? '';
            $company_data->city = $result->bnsprof_city ?? 0;
            $company_data->state = $result->bnsprof_state ?? 0;
            $company_data->ceo_prefix = $result->bnsprof_ceoprefix ?? '';
            $company_data->ceo_fname = $result->bnsprof_ceofname ?? '';
            $company_data->ceo_lname = $result->bnsprof_ceolname ?? '';
            $company_data->comp_url = $result->bnsprof_comp_url ?? '';
        }
    }
    
    return $company_data;
}
?>