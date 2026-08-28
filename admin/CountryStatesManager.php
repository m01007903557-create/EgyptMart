<?php
/**
 * File: CountryStatesManager.php
 * Version: 1.5.0
 * Description: إدارة الولايات (الدوال الأساسية)
 * 
 * يعتمد على الاتصال من lib/connect.php
 * جداول قاعدة البيانات: country (للدول) - states (للولايات)
 * 
 * أسماء الأعمدة في جدول country:
 * - cn_id (معرف الدولة)
 * - cn_status (حالة التفعيل)
 * - cn_name (اسم الدولة)
 */

class CountryStatesManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor - يستقبل اتصال قاعدة البيانات
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * جلب الولايات حسب معرف الدولة
     * 
     * @param int $countryId معرف الدولة
     * @return array مصفوفة تحتوي على الولايات
     */
    public function getStatesByCountryId(int $countryId): array {
        $states = [];
        
        $sql = "SELECT state_id, state_name FROM states WHERE state_cn_id = ? AND state_status = 1 ORDER BY state_name";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::getStatesByCountryId - Prepare failed: " . mysqli_error($this->db));
            return $states;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $countryId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $states[] = $row;
            }
            mysqli_free_result($result);
        }
        
        mysqli_stmt_close($stmt);
        
        return $states;
    }
    
    /**
     * جلب عدد الولايات حسب معرف الدولة
     * 
     * @param int $countryId معرف الدولة
     * @return int عدد الولايات
     */
    public function countStatesByCountryId(int $countryId): int {
        $sql = "SELECT COUNT(*) as total FROM states WHERE state_cn_id = ? AND state_status = 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::countStatesByCountryId - Prepare failed: " . mysqli_error($this->db));
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $countryId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        mysqli_stmt_close($stmt);
        
        return $row ? (int)$row['total'] : 0;
    }
    
    /**
     * التحقق من وجود دولة نشطة
     * 
     * @param int $countryId معرف الدولة
     * @return bool هل الدولة موجودة ونشطة؟
     */
    public function countryExists(int $countryId): bool {
        // ✅ استخدام cn_id و cn_status الصحيحين
        $sql = "SELECT COUNT(*) as total FROM country WHERE cn_id = ? AND cn_status = 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::countryExists - Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $countryId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        mysqli_stmt_close($stmt);
        
        return $row && (int)$row['total'] > 0;
    }
    
    /**
     * جلب اسم الدولة حسب معرفها
     * 
     * @param int $countryId معرف الدولة
     * @return string|null اسم الدولة أو null إذا لم توجد
     */
    public function getCountryName(int $countryId): ?string {
        $sql = "SELECT cn_name FROM country WHERE cn_id = ? AND cn_status = 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::getCountryName - Prepare failed: " . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $countryId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        mysqli_stmt_close($stmt);
        
        return $row ? $row['cn_name'] : null;
    }
    
    /**
     * جلب جميع الدول النشطة
     * 
     * @return array مصفوفة تحتوي على الدول
     */
    public function getAllCountries(): array {
        $countries = [];
        
        $sql = "SELECT cn_id, cn_name, cn_code FROM country WHERE cn_status = 1 ORDER BY cn_name";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $countries[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $countries;
    }
    
    /**
     * إضافة ولاية جديدة
     * 
     * @param int $countryId معرف الدولة
     * @param string $stateName اسم الولاية
     * @return bool نجاح العملية أم لا
     */
    public function addState(int $countryId, string $stateName): bool {
        $sql = "INSERT INTO states (state_cn_id, state_name, state_status) VALUES (?, ?, 1)";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::addState - Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, 'is', $countryId, $stateName);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * تحديث ولاية
     * 
     * @param int $stateId معرف الولاية
     * @param string $stateName اسم الولاية الجديد
     * @return bool نجاح العملية أم لا
     */
    public function updateState(int $stateId, string $stateName): bool {
        $sql = "UPDATE states SET state_name = ? WHERE state_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::updateState - Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, 'si', $stateName, $stateId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * حذف ولاية (تغيير الحالة إلى غير مفعل)
     * 
     * @param int $stateId معرف الولاية
     * @return bool نجاح العملية أم لا
     */
    public function deleteState(int $stateId): bool {
        $sql = "UPDATE states SET state_status = 0 WHERE state_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt === false) {
            error_log("CountryStatesManager::deleteState - Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $stateId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
}
?>