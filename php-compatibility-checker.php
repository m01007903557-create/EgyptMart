<?php
/**
 * أداة فحص التوافق مع PHP 8.3
 */

// زيادة الحد الأقصى للذاكرة ووقت التنفيذ
ini_set('memory_limit', '512M');
set_time_limit(300);

// استثناء المجلدات التي لا نريد فحصها (مثل admin, vendor, libs)
$exclude_dirs = ['admin', 'vendor', 'libs', 'includes', 'assets', 'css', 'js', 'images', 'fonts', 'uploads'];

// الدوال والميزات التي تم إهمالها أو تغييرها في PHP 8.x
$deprecated_features = [
    'mysql_' => 'ممنوع: دوال mysql_* غير موجودة في PHP 8.3 - يجب استبدالها بـ mysqli_*',
    'ereg' => 'محذوف: دوال ereg_* غير موجودة في PHP 8.3',
    'split' => 'محذوف: دوال split غير موجودة في PHP 8.3',
    'create_function' => 'محذوف: create_function غير موجود في PHP 8.3 - استخدم anonymous functions',
    'each' => 'محذوف: each() غير موجودة في PHP 8.3',
    '$$' => 'تحذير: استخدام المتغيرات المتغيرة (variable variables) قد يسبب مشاكل',
    'eval(' => 'تحذير: استخدام eval() غير آمن ويصعب تصحيحه',
    'call_user_method' => 'محذوف: call_user_method() غير موجودة في PHP 8.3',
    'call_user_method_array' => 'محذوف: call_user_method_array() غير موجودة في PHP 8.3',
];

// دوال mysqli القديمة التي يجب تحديثها
$old_mysqli_functions = [
    'mysqli_query(' => 'استخدم: mysqli_query مع prepared statements',
    'mysqli_fetch_array(' => 'تحذير: تأكد من استخدامها بشكل صحيح',
    'mysqli_fetch_assoc(' => 'تحذير: تأكد من استخدامها بشكل صحيح',
    'mysqli_num_rows(' => 'تحذير: تأكد من استخدامها بشكل صحيح',
];

// دوال قديمة أخرى
$other_issues = [
    '$_SERVER[' => 'تحذير: تأكد من فحص المتغيرات قبل استخدامها',
    '$_POST[' => 'تحذير: تأكد من فحص المتغيرات قبل استخدامها',
    '$_GET[' => 'تحذير: تأكد من فحص المتغيرات قبل استخدامها',
    'extract(' => 'تحذير: extract() قد يسبب مشاكل أمنية',
    'parse_str(' => 'تحذير: parse_str() قد يسبب مشاكل أمنية',
    'header(' => 'تحذير: header() يجب أن تستدعى قبل أي خرج',
];

// الدوال الجديدة التي ننصح باستخدامها
$recommended_functions = [
    'str_contains(' => 'يوجد: PHP 8.0+ - استخدم بدلاً من strpos',
    'str_starts_with(' => 'يوجد: PHP 8.0+ - استخدم بدلاً من strpos',
    'str_ends_with(' => 'يوجد: PHP 8.0+ - استخدم بدلاً من strpos',
    'get_debug_type(' => 'يوجد: PHP 8.0+',
    'array_is_list(' => 'يوجد: PHP 8.1+',
];

function scanDirectory($dir, $exclude_dirs, &$results) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // التحقق من عدم استثناء المجلد
            $skip = false;
            foreach ($exclude_dirs as $exclude) {
                if (strpos($path, '/' . $exclude) !== false || strpos($path, '\\' . $exclude) !== false) {
                    $skip = true;
                    break;
                }
            }
            
            if (!$skip) {
                scanDirectory($path, $exclude_dirs, $results);
            }
        } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            checkPHPFile($path, $results);
        }
    }
}

function checkPHPFile($file, &$results) {
    $content = file_get_contents($file);
    $lines = file($file);
    
    // التحقق من استخدام دوال mysql_
    if (preg_match('/mysql_[a-z_]+\(/', $content)) {
        $results['mysql_usage'][$file] = true;
    }
    
    // فحص سطر بسطر للبحث عن مشاكل PHP 8
    foreach ($lines as $line_number => $line) {
        $line_number++; // السطر يبدأ من 1
        
        // دوال محذوفة
        if (preg_match('/(ereg|split|create_function|each)\s*\(/', $line)) {
            $results['deprecated_functions'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'type' => 'محذوف'
            ];
        }
        
        // دوال mysql_
        if (preg_match('/(mysql_[a-z_]+)\s*\(/', $line, $matches)) {
            $results['mysql_functions'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'function' => $matches[1]
            ];
        }
        
        // mysqli_query بدون prepared statement
        if (preg_match('/mysqli_query\s*\(\s*\$[^,]+,\s*["\'].*\$.*["\']/', $line)) {
            $results['sql_injection_risk'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'type' => 'خطر SQL Injection'
            ];
        }
        
        // استخدام extract
        if (preg_match('/extract\s*\(/', $line)) {
            $results['security_issues'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'type' => 'استخدام extract() - غير آمن'
            ];
        }
        
        // استخدام eval
        if (preg_match('/eval\s*\(/', $line)) {
            $results['security_issues'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'type' => 'استخدام eval() - خطر أمني'
            ];
        }
        
        // استخدام $$ (variable variables)
        if (preg_match('/\$\$[a-zA-Z_]/', $line)) {
            $results['variable_variables'][$file][] = [
                'line' => $line_number,
                'code' => trim($line)
            ];
        }
        
        // استخدام call_user_method
        if (preg_match('/call_user_method(_array)?\s*\(/', $line)) {
            $results['deprecated_functions'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'type' => 'محذوف - استخدم call_user_func'
            ];
        }
        
        // فحص استخدام دوال PHP 8 الجديدة (إيجابي)
        if (preg_match('/(str_contains|str_starts_with|str_ends_with|get_debug_type|array_is_list)\s*\(/', $line, $matches)) {
            $results['php8_usage'][$file][] = [
                'line' => $line_number,
                'code' => trim($line),
                'function' => $matches[1]
            ];
        }
    }
}

// بدء الفحص
$root_dir = __DIR__;
$results = [
    'mysql_usage' => [],
    'mysql_functions' => [],
    'deprecated_functions' => [],
    'sql_injection_risk' => [],
    'security_issues' => [],
    'variable_variables' => [],
    'php8_usage' => [],
];

echo "<!DOCTYPE html>
<html dir='ltr' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>فحص التوافق مع PHP 8.3</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 20px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #2c3e50; color: white; padding: 10px; text-align: center; }
        td { padding: 10px; border: 1px solid #ddd; text-align: right; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-success { background: #28a745; color: white; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 فحص التوافق مع PHP 8.3</h1>
            <p>جاري فحص جميع ملفات PHP في الموقع...</p>
        </div>
        <div class='content'>";

// عرض معلومات PHP الحالية
echo "<div class='info'>
        <h3>ℹ️ معلومات النظام الحالي:</h3>
        <ul>
            <li><strong>إصدار PHP:</strong> " . phpversion() . "</li>
            <li><strong>المسار الحالي:</strong> " . __DIR__ . "</li>
            <li><strong>الحد الأقصى للذاكرة:</strong> " . ini_get('memory_limit') . "</li>
            <li><strong>الحد الأقصى للوقت:</strong> " . ini_get('max_execution_time') . " ثانية</li>
        </ul>
    </div>";

echo "<p><strong>🔎 جاري فحص الملفات...</strong></p>";
scanDirectory($root_dir, $exclude_dirs, $results);
echo "<p>✅ تم الفحص بنجاح!</p>";

// عرض النتائج
if (empty($results['mysql_functions']) && empty($results['deprecated_functions']) && 
    empty($results['security_issues']) && empty($results['variable_variables'])) {
    echo "<div class='success'>
            <h3>✅ ممتاز! لا توجد مشاكل توافق مع PHP 8.3</h3>
            <p>الموقع متوافق تماماً مع PHP 8.3</p>
          </div>";
} else {
    echo "<div class='warning'>
            <h3>⚠️ تم العثور على بعض المشاكل المحتملة</h3>
            <p>يرجى مراجعة النتائج أدناه وإصلاح المشاكل قبل الترقية الكاملة</p>
          </div>";
}

// عرض استخدام دوال mysql_ (خطير جداً)
if (!empty($results['mysql_functions'])) {
    echo "<h3 class='danger'>❌ استخدام دوال mysql_ القديمة (غير موجودة في PHP 8.3)</h3>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
                <th>الدالة المستخدمة</th>
            </tr>";
    foreach ($results['mysql_functions'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                    <td><span class='badge badge-danger'>{$issue['function']}</span></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// عرض الدوال المحذوفة
if (!empty($results['deprecated_functions'])) {
    echo "<h3 class='danger'>❌ دوال محذوفة في PHP 8.3</h3>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
                <th>نوع المشكلة</th>
            </tr>";
    foreach ($results['deprecated_functions'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                    <td><span class='badge badge-danger'>{$issue['type']}</span></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// عرض مخاطر SQL Injection
if (!empty($results['sql_injection_risk'])) {
    echo "<h3 class='danger'>⚠️ مخاطر SQL Injection محتملة</h3>";
    echo "<p>يوصى بشدة باستخدام Prepared Statements بدلاً من دمج المتغيرات مباشرة في الاستعلامات</p>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
            </tr>";
    foreach ($results['sql_injection_risk'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// عرض المشاكل الأمنية
if (!empty($results['security_issues'])) {
    echo "<h3 class='warning'>🔒 مشاكل أمنية محتملة</h3>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
                <th>النوع</th>
            </tr>";
    foreach ($results['security_issues'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                    <td><span class='badge badge-warning'>{$issue['type']}</span></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// عرض المتغيرات المتغيرة
if (!empty($results['variable_variables'])) {
    echo "<h3 class='warning'>⚠️ استخدام المتغيرات المتغيرة (Variable Variables)</h3>";
    echo "<p>قد تسبب سلوكاً غير متوقع في PHP 8.3</p>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
            </tr>";
    foreach ($results['variable_variables'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// عرض استخدام دوال PHP 8 (إيجابي)
if (!empty($results['php8_usage'])) {
    echo "<h3 class='success'>✅ استخدام دوال PHP 8 الجديدة</h3>";
    echo "<table>
            <tr>
                <th>الملف</th>
                <th>السطر</th>
                <th>الكود</th>
                <th>الدالة</th>
            </tr>";
    foreach ($results['php8_usage'] as $file => $issues) {
        foreach ($issues as $issue) {
            echo "<tr>
                    <td>" . htmlspecialchars($file) . "</td>
                    <td>{$issue['line']}</td>
                    <td><code>" . htmlspecialchars($issue['code']) . "</code></td>
                    <td><span class='badge badge-success'>{$issue['function']}</span></td>
                  </tr>";
        }
    }
    echo "</table>";
}

// إحصائيات
echo "<div class='info'>
        <h3>📊 إحصائيات الفحص:</h3>
        <ul>
            <li><strong>ملفات mysql_ القديمة:</strong> " . count($results['mysql_functions']) . "</li>
            <li><strong>دوال محذوفة:</strong> " . count($results['deprecated_functions']) . "</li>
            <li><strong>مخاطر SQL Injection:</strong> " . count($results['sql_injection_risk']) . "</li>
            <li><strong>مشاكل أمنية:</strong> " . count($results['security_issues']) . "</li>
            <li><strong>متغيرات متغيرة:</strong> " . count($results['variable_variables']) . "</li>
            <li><strong>استخدام دوال PHP 8:</strong> " . count($results['php8_usage']) . "</li>
        </ul>
    </div>";

echo "<p><a href='javascript:window.location.reload();' class='badge badge-info'>🔄 إعادة الفحص</a></p>";
echo "</div></div></body></html>";
?>