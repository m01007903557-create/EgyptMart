<?php
require_once dirname(__DIR__) . "/common.php";
if (function_exists('GettingSite_Setting')) {
    echo "الدالة موجودة";
} else {
    echo "الدالة غير موجودة";
}