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
    
    // ✅ استخدام المعامل c من الرابط (أولوية أولى)
    $c = isset($_GET['c']) ? mysqli_real_escape_string($con, $_GET['c']) : '';
    
    if (!empty($c)) {
        // البحث عن الشركة باستخدام MD5(bnsprof_id)
        $query = "SELECT b.*, u.* 
                  FROM business_profile b 
                  LEFT JOIN user u ON b.bnsprof_uid = u.usr_id 
                  WHERE MD5(b.bnsprof_id) = '$c' AND b.bnsprof_status = '1'";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $dbRow = mysqli_fetch_object($result);
            foreach ($dbRow as $key => $value) {
                $companyData->$key = $value;
            }
            return $companyData;
        }
    }
    
    // ✅ إذا لم يتم العثور على الشركة باستخدام c، جرب باستخدام الجلسة (للمستخدم المسجل كشركة)
    $bnsprof_uid = $_SESSION['uid_indm'] ?? 0;
    if ($bnsprof_uid > 0) {
        $query = "SELECT b.*, u.* 
                  FROM business_profile b 
                  LEFT JOIN user u ON b.bnsprof_uid = u.usr_id 
                  WHERE b.bnsprof_uid = '$bnsprof_uid' AND b.bnsprof_status = '1'";
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
?>