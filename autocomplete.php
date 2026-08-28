<?php
/**
 * File: autocomplete.php
 * Version: PHP 8.3
 * Description: البحث عن المنتجات وعرضها مع تصنيفاتها (للاستخدام في الإكمال التلقائي autocomplete)
 */

// تعيين رؤوس منع التخزين المؤقت
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: text/html; charset=UTF-8');

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// تهيئة المتغيرات
$type = "";
$dataarray = array();
$keywords = '';
$getD = '';

// معالجة طلب GET (للإكمال التلقائي)
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $keywords = mysqli_real_escape_string($con, trim($_GET['q']));
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    
    // التحقق من وجود referer
    $reffer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $refferr = explode("-alert", $reffer);
    $refferr1 = explode("product-sel-cat", $reffer);
    $refferr12 = explode("post-", $reffer);
    $refferr123 = explode("myproduct-", $reffer);
    
    // إذا كان الطلب من صفحات معينة، ابحث في التصنيفات
    if (count($refferr) > 1 || count($refferr1) > 1 || count($refferr12) > 1 || count($refferr123) > 1) {
        
        $sql = "SELECT s.pc_id as pd_subcat_id, s.pc_name as pd_title, s.pc_sort_name, 
                       c.pc_name as childname, c.pc_id as childid, 
                       m.pc_id as GrandparentId, m.pc_name as Grandparentname 
                FROM product_category s, product_category c, product_category m 
                WHERE s.pc_parent_id = c.pc_id 
                  AND c.pc_parent_id = m.pc_id 
                  AND m.pc_parent_id = '0' 
                  AND m.pc_status = '1' 
                  AND c.pc_status = '1' 
                  AND s.pc_status = '1' 
                  AND s.pc_name LIKE '%{$keywords}%' 
                ORDER BY s.pc_id DESC";
        
        $res = mysqli_query($con, $sql) or die(mysqli_error($con));
        
        while ($row = mysqli_fetch_object($res)) {
            echo htmlspecialchars($row->Grandparentname) . ">>" . 
                 htmlspecialchars($row->childname) . ">>" . 
                 htmlspecialchars($row->pd_title) . "\n";
            
            $_SESSION[$row->Grandparentname] = $row->GrandparentId;
            $_SESSION[$row->childname] = $row->childid;
            $dataarray[$row->pd_title] = $row->pd_subcat_id;
        }
        
        $_SESSION['searchedproducts'] = $dataarray;
        exit();
    }
}

// معالجة طلب POST (للإكمال التلقائي العادي)
if (isset($_POST['keywordsAjax']) && !empty($_POST['keywordsAjax'])) {
    $getD = mysqli_real_escape_string($con, trim($_POST['keywordsAjax']));
    
    $sql_prd = "SELECT pd_title, pd_subcat_id 
                FROM products 
                WHERE pd_title LIKE '{$getD}%' 
                  AND pd_status = '1' 
                GROUP BY pd_title 
                ORDER BY pd_title";
    
    $result_prd = mysqli_query($con, $sql_prd);
    
    if (mysqli_num_rows($result_prd) > 0 && isset($_POST['Products']) && $_POST['Products'] == 'Products') {
        echo "<ul style='list-style:none; margin:0; padding:0;'>";
        
        while ($row_prd = mysqli_fetch_object($result_prd)) {
            if (!empty($row_prd->pd_title)) {
                // جلب معلومات التصنيف
                $query = "SELECT * FROM product_category WHERE pc_id = " . (int)$row_prd->pd_subcat_id . " LIMIT 1";
                $querySub = mysqli_query($con, $query);
                $ResultSub = mysqli_fetch_object($querySub);
                
                $ResultParent = null;
                $ResultMain = null;
                
                if ($ResultSub && isset($ResultSub->pc_parent_id) && $ResultSub->pc_parent_id > 0) {
                    $queryParent = "SELECT * FROM product_category WHERE pc_id = " . (int)$ResultSub->pc_parent_id . " LIMIT 1";
                    $queryParents = mysqli_query($con, $queryParent);
                    $ResultParent = mysqli_fetch_object($queryParents);
                    
                    if ($ResultParent && isset($ResultParent->pc_parent_id) && $ResultParent->pc_parent_id > 0) {
                        $queryMain = "SELECT * FROM product_category WHERE pc_id = " . (int)$ResultParent->pc_parent_id . " LIMIT 1";
                        $queryMainResult = mysqli_query($con, $queryMain);
                        $ResultMain = mysqli_fetch_object($queryMainResult);
                    }
                }
                
                $mainName = ($ResultMain && isset($ResultMain->pc_name)) ? $ResultMain->pc_name : '';
                $parentName = ($ResultParent && isset($ResultParent->pc_name)) ? $ResultParent->pc_name : '';
                $subName = ($ResultSub && isset($ResultSub->pc_name)) ? $ResultSub->pc_name : '';
                
                echo "<li style='padding:5px; border-bottom:1px solid #eee; cursor:pointer;' 
                           onmouseover='this.style.backgroundColor=\"#FAF4FF\"' 
                           onmouseout='this.style.backgroundColor=\"transparent\"'>";
                echo "<a id='getSearchText' href='javascript:void(0);' class='" . htmlspecialchars($row_prd->pd_title) . "' style='font-size:13px; text-decoration:none; color:#333; display:block;'>";
                
                if (!empty($mainName) || !empty($parentName) || !empty($subName)) {
                    if (!empty($mainName)) {
                        echo "<span style='color:#666;'>" . htmlspecialchars($mainName) . "</span> >> ";
                    }
                    if (!empty($parentName)) {
                        echo "<span style='color:#666;'>" . htmlspecialchars($parentName) . "</span> >> ";
                    }
                    echo "<span style='color:#c00; font-weight:bold; float:" . (is_rtl() ? 'left' : 'right') . ";'>" . htmlspecialchars($subName) . "</span>";
                } else {
                    echo "<span style='color:#333;'>" . htmlspecialchars($row_prd->pd_title) . "</span>";
                }
                
                echo "</a>";
                echo "</li>";
                
                $dataarray[$row_prd->pd_title] = $row_prd->pd_subcat_id;
            }
        }
        
        echo "</ul>";
        $_SESSION['searchedproducts'] = $dataarray;
    }
}

// إغلاق اتصال قاعدة البيانات
mysqli_close($con);
?>