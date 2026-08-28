<?php
/**
 * File: check_admin.php
 * Description: ملف مركزي للتحقق من صلاحيات المشرف
 */

require_once dirname(__DIR__) . "/common.php";

if (!check_admin_access(false)) {
    header('Location: index.php');
    exit;
}