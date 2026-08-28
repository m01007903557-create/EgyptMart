<?php
function getCompanyData($con) {
    $companyData = new stdClass();
    
    // القيم الافتراضية لجميع الخصائص المستخدمة
    $companyData->usr_id = 0;
    $companyData->bnsprof_comp_url = '';
    $companyData->name_prefix = '';
    $companyData->fname = '';
    $companyData->lname = '';
    $companyData->bnsprof_address1 = '';
    $companyData->bnsprof_address2 = '';
    $companyData->bnsprof_city = 0;
    $companyData->country = 0;
    $companyData->country_ph_code = '';
    $companyData->bnsprof_phcode1 = '';
    $companyData->bnsprof_ph1 = '';
    $companyData->bnsprof_state = 0;
    $companyData->bnsprof_compname = '';
    $companyData->bnsprof_id = 0;
    $companyData->bnsprof_yoe = '';
    $companyData->bnsprof_regno = '';
    $companyData->bnsprof_svtax_no = '';
    $companyData->bnsprof_tan_no = '';
    $companyData->bnsprof_pan_no = '';
    $companyData->bnsprof_cin_no = '';
    $companyData->bnsprof_epf_no = '';
    $companyData->bnsprof_esi_no = '';
    $companyData->bnsprof_sct_no = '';
    $companyData->bnsprof_dnb_no = '';
    $companyData->bnsprof_rbi_no = '';
    $companyData->bnsprof_fssailic_no = '';
    $companyData->bnsprof_nsic_no = '';
    $companyData->bnsprof_sst_no = '';
    $companyData->bnsprof_ie_code = '';
    $companyData->bnsprof_cst_no = '';
    $companyData->bnsprof_msme_no = '';
    $companyData->bnsprof_vat_no = '';
    $companyData->bnsprof_excisereg_no = '';
    $companyData->bnsprof_turnover = '';
    $companyData->bnsprof_comemp = '';
    $companyData->bnsprof_owntype = '';
    
    // معرف الشركة من الجلسة (إذا كان المستخدم مسجلاً كشركة)
    $bnsprof_uid = $_SESSION['uid_indm'] ?? 0;
    
    if ($bnsprof_uid > 0) {
        $query = "SELECT * FROM business_profile WHERE bnsprof_uid = '$bnsprof_uid' AND bnsprof_status = '1'";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $dbRow = mysqli_fetch_object($result);
            foreach ($dbRow as $key => $value) {
                $companyData->$key = $value;
            }
        }
    }
    
    return $companyData;
}