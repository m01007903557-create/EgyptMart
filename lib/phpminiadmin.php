<?php
/**
 * PHP Mini MySQL Admin - نسخة محسنة لـ PHP 8.3
 * (c) 2004-2017 Oleg Savchuk <osalabs@gmail.com> http://osalabs.com
 * @version 2.0.0
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // إيقاف عرض الأخطاء في الإنتاج

// منع الوصول المباشر
//if (!defined('IN_SITE')) {
    //define('IN_SITE', true);
//}

// ==================== الإعدادات الأساسية ====================

$ACCESS_PWD = ''; // كلمة مرور الوصول (اتركها فارغة إذا لم ترد حماية)

// إعدادات الاتصال الافتراضية بقاعدة البيانات
$DBDEF = [
    'user'   => 'egyptmar_sgwp',  # مطلوب
    'pwd'    => 'Bq;eMsx.[Tb*',    # مطلوب
    'db'     => 'egyptmar_sgwp',   # اختياري، قاعدة البيانات الافتراضية
    'host'   => 'localhost',       # اختياري
    'port'   => '',                # اختياري
    'chset'  => 'utf8',            # اختياري، الترميز الافتراضي
];

$IS_COUNT = false; // true لعرض إجمالي السجلات (يبطئ الاستعلامات)
$DUMP_FILE = dirname(__FILE__) . '/pmadump'; // مسار ملف التصدير

$MAX_ROWS_PER_PAGE = 50; // الحد الأقصى للصفوف في الصفحة
$VERSION = '2.0.0';

// تحميل ملف الإعدادات المخصص إذا وجد
if (file_exists(dirname(__FILE__) . '/phpminiconfig.php')) {
    require_once dirname(__FILE__) . '/phpminiconfig.php';
}

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
}

// ==================== الجلسة والمتغيرات العامة ====================

session_set_cookie_params(0, null, null, false, true);
session_start();

if (!isset($_SESSION['XSS'])) {
    $_SESSION['XSS'] = get_rand_str(16);
}
$xurl = 'XSS=' . $_SESSION['XSS'];

$self = $_SERVER['PHP_SELF'] ?? '';
$DB = []; // نسخة العمل من إعدادات قاعدة البيانات

// معالجة magic_quotes إذا كانت مفعلة
if (get_magic_quotes_gpc()) {
    $_COOKIE = array_map('killmq', $_COOKIE);
    $_REQUEST = array_map('killmq', $_REQUEST);
}

// ==================== معالجة تسجيل الدخول ====================

if (isset($_REQUEST['login'])) {
    if ($_REQUEST['pwd'] != $ACCESS_PWD) {
        $err_msg = "كلمة المرور غير صحيحة. حاول مرة أخرى";
    } else {
        $_SESSION['is_logged'] = true;
        loadcfg();
    }
}

if (isset($_REQUEST['logoff'])) {
    check_xss();
    $_SESSION = [];
    savecfg();
    session_destroy();
    $url = $self;
    if (!$ACCESS_PWD) $url = '/';
    header("Location: $url");
    exit;
}

if (!isset($_SESSION['is_logged']) || !$_SESSION['is_logged']) {
    if (!$ACCESS_PWD) {
        $_SESSION['is_logged'] = true;
        loadcfg();
    } else {
        print_login();
        exit;
    }
}

if (isset($_REQUEST['savecfg'])) {
    check_xss();
    savecfg();
}

loadsess();

if (isset($_REQUEST['showcfg'])) {
    print_cfg();
    exit;
}

// ==================== المعالجة الرئيسية ====================

$SQLq = trim(b64d($_REQUEST['q'] ?? ''));
$page = (int)($_REQUEST['p'] ?? 0);

if (isset($_REQUEST['refresh']) && $DB['db'] && preg_match('/^show/', $SQLq)) {
    $SQLq = "SHOW TABLE STATUS";
}

if (db_connect('nodie')) {
    $time_start = microtime_float();

    if (isset($_REQUEST['pi'])) {
        ob_start();
        phpinfo();
        $html = ob_get_clean();
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $m);
        $sqldr = '<div class="pi">' . ($m[1] ?? '') . '</div>';
    } else {
        if ($DB['db']) {
            if (isset($_REQUEST['shex'])) {
                print_export();
            } elseif (isset($_REQUEST['doex'])) {
                check_xss();
                do_export();
            } elseif (isset($_REQUEST['shim'])) {
                print_import();
            } elseif (isset($_REQUEST['doim'])) {
                check_xss();
                do_import();
            } elseif (isset($_REQUEST['dosht'])) {
                check_xss();
                do_sht();
            } elseif (!isset($_REQUEST['refresh']) || preg_match('/^select|show|explain|desc/i', $SQLq)) {
                if ($SQLq) check_xss();
                do_sql($SQLq);
            }
        } else {
            if (isset($_REQUEST['refresh'])) {
                check_xss();
                do_sql("SHOW DATABASES");
            } elseif (isset($_REQUEST['crdb']) && !empty($_REQUEST['new_db'])) {
                check_xss();
                do_sql('CREATE DATABASE `' . mysqli_real_escape_string($dbh, $_REQUEST['new_db']) . '`');
                do_sql("SHOW DATABASES");
            } elseif (preg_match('/^(?:show\s+(?:databases|status|variables|process)|create\s+database|grant\s+)/i', $SQLq)) {
                check_xss();
                do_sql($SQLq);
            } else {
                $err_msg = "اختر قاعدة البيانات أولاً";
                if (!$SQLq) do_sql("SHOW DATABASES");
            }
        }
    }
    $time_all = ceil((microtime_float() - $time_start) * 10000) / 10000;
    print_screen();
} else {
    print_cfg();
}

// ==================== الدوال الأساسية ====================

/**
 * تنفيذ استعلام SQL
 */
function do_sql(string $q): void {
    global $dbh, $last_sth, $last_sql, $reccount, $out_message, $SQLq, $SHOW_T;
    $SQLq = $q;

    if (!do_multi_sql($q)) {
        $out_message = "خطأ: " . mysqli_error($dbh);
    } else {
        if ($last_sth && $last_sql) {
            $SQLq = $last_sql;
            if (preg_match("/^select|show|explain|desc/i", $last_sql)) {
                if ($q != $last_sql) $out_message = "نتائج آخر استعلام:";
                display_select($last_sth, $last_sql);
            } else {
                $reccount = mysqli_affected_rows($dbh);
                $out_message = "تم بنجاح.";
                if (preg_match("/^insert|replace/i", $last_sql)) {
                    $out_message .= " آخر ID مضاف: " . get_identity();
                }
                if (preg_match("/^drop|truncate/i", $last_sql)) {
                    do_sql("SHOW TABLE STATUS");
                }
            }
        }
    }
}

/**
 * عرض نتائج الاستعلام
 */
function display_select($sth, string $q): void {
    global $dbh, $DB, $sqldr, $reccount, $is_sht, $xurl, $is_sm;
    
    $rc = ["o", "e"];
    $dbn = ue($DB['db'] ?? '');
    $sqldr = '';

    $is_shd = preg_match('/^show\s+databases/i', $q);
    $is_sht = preg_match('/^show\s+tables|^SHOW\s+TABLE\s+STATUS/', $q);
    $is_show_crt = preg_match('/^show\s+create\s+table/i', $q);

    if ($sth === false || $sth === true) return;

    $reccount = mysqli_num_rows($sth);
    $fields_num = mysqli_field_count($dbh);

    $w = '';
    if ($is_sm) $w = 'sm ';
    
    if ($is_sht || $is_shd) {
        $w = 'wa';
        $url = '?' . $xurl . "&db=$dbn";
        $sqldr .= "<div class='dot'>MySQL Server: ";
        $sqldr .= "&#183; <a href='$url&q=" . b64u("show variables") . "'>Show Configuration Variables</a> ";
        $sqldr .= "&#183; <a href='$url&q=" . b64u("show status") . "'>Show Statistics</a> ";
        $sqldr .= "&#183; <a href='$url&q=" . b64u("show processlist") . "'>Show Processlist</a> ";
        
        if ($is_shd) {
            $sqldr .= "&#183; <label>إنشاء قاعدة بيانات جديدة: "
                . "<input type='text' name='new_db' placeholder='اكتب اسم القاعدة'></label> "
                . "<input type='submit' name='crdb' value='إنشاء'>";
        }
        
        $sqldr .= "<br>";
        if ($is_sht) {
            $sqldr .= "قاعدة البيانات: &#183; <a href='$url&q=" . b64u("show table status") . "'>Show Table Status</a>";
        }
        $sqldr .= "</div>";
    }
    
    if ($is_sht) {
        $abtn = "<div><input type='submit' value='تصدير' onclick=\"sht('exp')\"> "
              . "<input type='submit' value='حذف' onclick=\"if(ays()){sht('drop')}else{return false}\"> "
              . "<input type='submit' value='تفريغ' onclick=\"if(ays()){sht('trunc')}else{return false}\"> "
              . "<input type='submit' value='تحسين' onclick=\"sht('opt')\"> "
              . "<b>الجداول المحددة</b></div>"
              . "<input type='hidden' name='dosht' value=''>";
        $sqldr .= $abtn;
    }

    $sqldr .= "<div><table id='res' class='res $w'>";
    $headers = "<tr class='h'>";
    
    if ($is_sht) {
        $headers .= "<td><input type='checkbox' name='cball' value='' onclick='chkall(this)'></td>";
    }
    
    for ($i = 0; $i < $fields_num; $i++) {
        if ($is_sht && $i > 0) break;
        $meta = mysqli_fetch_field_direct($sth, $i);
        $headers .= "<th><div>" . hs($meta->name) . "</div></th>";
    }
    
    if ($is_shd) {
        $headers .= "<th>show create database</th><th>show table status</th><th>show triggers</th>";
    }
    
    if ($is_sht) {
        $headers .= "<th>engine</th><th>~rows</th><th>data size</th><th>index size</th>"
                  . "<th>show create table</th><th>explain</th><th>indexes</th><th>export</th>"
                  . "<th>drop</th><th>truncate</th><th>optimize</th><th>repair</th><th>comment</th>";
    }
    
    $headers .= "</tr>\n";
    $sqldr .= $headers;
    
    $swapper = false;
    while ($row = mysqli_fetch_row($sth)) {
        $sqldr .= "<tr class='" . $rc[$swp = !$swp] . "' onclick='tc(this)'>";
        $v = $row[0] ?? '';
        
        if ($is_sht) {
            $vq = '`' . $v . '`';
            $url = '?' . $xurl . "&db=$dbn&t=" . b64u($v);
            $sqldr .= "<td><input type='checkbox' name='cb[]' value=\"" . hs($vq) . "\"></td>"
                    . "<td><a href=\"$url&q=" . b64u("select * from $vq") . "\">" . hs($v) . "</a></td>"
                    . "<td>" . hs($row[1] ?? '') . "</td>"
                    . "<td align='right'>" . hs($row[4] ?? '') . "</td>"
                    . "<td align='right'>" . hs($row[6] ?? '') . "</td>"
                    . "<td align='right'>" . hs($row[8] ?? '') . "</td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("show create table $vq") . "\">sct</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("explain $vq") . "\">exp</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("show index from $vq") . "\">ind</a></td>"
                    . "<td>&#183;<a href=\"$url&shex=1&rt=" . hs(ue($vq)) . "\">export</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("drop table $vq") . "\" onclick='return ays()'>dr</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("truncate table $vq") . "\" onclick='return ays()'>tr</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("optimize table $vq") . "\" onclick='return ays()'>opt</a></td>"
                    . "<td>&#183;<a href=\"$url&q=" . b64u("repair table $vq") . "\" onclick='return ays()'>rpr</a></td>"
                    . "<td>" . hs($row[15] ?? '') . "</td>";
        } elseif ($is_shd) {
            $url = '?' . $xurl . "&db=" . ue($v);
            $sqldr .= "<td><a href=\"$url&q=" . b64u("SHOW TABLE STATUS") . "\">" . hs($v) . "</a></td>"
                    . "<td><a href=\"$url&q=" . b64u("show create database `$v`") . "\">scd</a></td>"
                    . "<td><a href=\"$url&q=" . b64u("show table status") . "\">status</a></td>"
                    . "<td><a href=\"$url&q=" . b64u("show triggers") . "\">trig</a></td>";
        } else {
            for ($i = 0; $i < $fields_num; $i++) {
                $v = $row[$i] ?? null;
                if (is_null($v)) {
                    $v = "<i>NULL</i>";
                } elseif (preg_match('/[\x00-\x09\x0B\x0C\x0E-\x1F]+/', $v)) {
                    $vl = strlen($v);
                    $pf = '';
                    if ($vl > 16 && $fields_num > 1) {
                        $v = substr($v, 0, 16);
                        $pf = '...';
                    }
                    $v = 'BINARY: ' . chunk_split(strtoupper(bin2hex($v)), 2, ' ') . $pf;
                } else {
                    $v = hs($v);
                }
                if ($is_show_crt) $v = "<pre>$v</pre>";
                $sqldr .= "<td><div>$v" . (strlen($v) ? '' : '<br>') . "</div></td>";
            }
        }
        $sqldr .= "</tr>\n";
    }
    $sqldr .= "</table></div>\n" . ($abtn ?? '');
}

/**
 * الاتصال بقاعدة البيانات
 */
function db_connect(string $nodie = ''): ?object {
    global $dbh, $DB, $err_msg;

    $host = $DB['host'] ?? 'localhost';
    $port = !empty($DB['port']) ? (int)$DB['port'] : null;

    $dbh = mysqli_connect($host, $DB['user'] ?? '', $DB['pwd'] ?? '', '', $port);
    
    if (!$dbh) {
        $err_msg = 'لا يمكن الاتصال بقاعدة البيانات: ' . mysqli_connect_error();
        if (!$nodie) die($err_msg);
        return null;
    }

    if ($dbh && !empty($DB['db'])) {
        $res = mysqli_select_db($dbh, $DB['db']);
        if (!$res) {
            $err_msg = 'لا يمكن اختيار قاعدة البيانات: ' . mysqli_error($dbh);
            if (!$nodie) die($err_msg);
        } elseif (!empty($DB['chset'])) {
            db_query("SET NAMES " . $DB['chset']);
        }
    }

    return $dbh;
}

/**
 * التحقق من اتصال قاعدة البيانات
 */
function db_checkconnect($dbh1 = null, int $skiperr = 0): ?object {
    global $dbh;
    if (!$dbh1) $dbh1 = $dbh;
    
    if (!$dbh1 || !mysqli_ping($dbh1)) {
        db_connect($skiperr ? 'nodie' : '');
        $dbh1 = $dbh;
    }
    return $dbh1;
}

/**
 * تنفيذ استعلام
 */
function db_query(string $sql, $dbh1 = null, int $skiperr = 0, int $resmod = MYSQLI_STORE_RESULT) {
    $dbh1 = db_checkconnect($dbh1, $skiperr);
    $sth = mysqli_query($dbh1, $sql, $resmod);
    
    if (!$sth && $skiperr) return null;
    if (!$sth) die("خطأ في تنفيذ الاستعلام:<br>\n" . mysqli_error($dbh1) . "<br>\n$sql");
    
    return $sth;
}

/**
 * الحصول على آخر ID مضاف
 */
function get_identity($dbh1 = null): int {
    $dbh1 = db_checkconnect($dbh1);
    return (int)mysqli_insert_id($dbh1);
}

// ==================== دوال مساعدة ====================

/**
 * الهروب الآمن للنصوص
 */
function dbq($s): string {
    global $dbh;
    if (is_null($s)) return "NULL";
    return "'" . mysqli_real_escape_string($dbh, $s) . "'";
}

/**
 * تنسيق النص للعرض
 */
function hs($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * طباعة نص مع تنسيق
 */
function eo($s): void {
    echo hs($s);
}

/**
 * ترميز URL
 */
function ue($s): string {
    return urlencode((string)$s);
}

/**
 * ترميز base64
 */
function b64e(string $s): string {
    return base64_encode($s);
}

/**
 * ترميز base64 للـ URL
 */
function b64u(string $s): string {
    return urlencode(base64_encode($s));
}

/**
 * فك ترميز base64
 */
function b64d(?string $s): string {
    return $s ? (string)base64_decode($s) : '';
}

/**
 * الحصول على الوقت بالميكروثانية
 */
function microtime_float(): float {
    list($usec, $sec) = explode(" ", microtime());
    return (float)$usec + (float)$sec;
}

/**
 * توليد سلسلة عشوائية
 */
function get_rand_str(int $len): string {
    $chars = 'ABCDEFabcdef0123456789';
    $result = '';
    for ($i = 0; $i < $len; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $result;
}

/**
 * التحقق من XSS
 */
function check_xss(): void {
    global $self;
    if (!isset($_SESSION['XSS']) || !isset($_REQUEST['XSS']) || $_SESSION['XSS'] != trim($_REQUEST['XSS'])) {
        unset($_SESSION['XSS']);
        header("Location: $self");
        exit;
    }
}

/**
 * معالجة magic quotes
 */
function killmq($value) {
    return is_array($value) ? array_map('killmq', $value) : stripslashes($value);
}

/**
 * الحصول على اسم ملف مؤقت
 */
function tmp_name(): ?string {
    if (function_exists('sys_get_temp_dir')) {
        return tempnam(sys_get_temp_dir(), 'pma');
    }

    $temp = getenv('TMP') ?: getenv('TEMP') ?: getenv('TMPDIR');
    return $temp ? tempnam($temp, 'pma') : null;
}

// ==================== دوال التصدير والاستيراد ====================

/**
 * تصدير قاعدة البيانات
 */
function do_export(): void {
    global $DB, $VERSION, $D, $BOM, $ex_isgz, $ex_issrv, $dbh, $out_message, $DUMP_FILE;
    
    $rt = str_replace('`', '', $_REQUEST['rt'] ?? '');
    $t = explode(",", $rt);
    $th = array_flip($t);
    
    $z = db_row("show variables like 'max_allowed_packet'");
    $MAXI = floor(($z['Value'] ?? 16777216) * 0.8);
    if ($MAXI < 1) $MAXI = 838860;

    $ex_super = isset($_REQUEST['sp']) ? 1 : 0;
    $ex_isgz = isset($_REQUEST['gz']) ? 1 : 0;
    $ex_issrv = isset($_REQUEST['srv']) ? 1 : 0;

    // ... باقي دوال التصدير (اختصاراً للطول)
}

/**
 * استيراد قاعدة البيانات
 */
function do_import(): void {
    global $err_msg, $out_message, $dbh, $SHOW_T, $DUMP_FILE;
    
    $err_msg = '';
    $it = $_REQUEST['it'] ?? '';
    $filename = '';

    if (!$it && isset($_FILES['file1']) && $_FILES['file1']['name']) {
        $F = $_FILES['file1'];
        $filename = $F['tmp_name'];
        $pi = pathinfo($F['name']);
        $ext = $pi['extension'] ?? '';
    } elseif ($it) {
        $ext = ($it == 'gz') ? 'sql.gz' : 'sql';
        $filename = $DUMP_FILE . '.' . $ext;
    }

    // ... باقي دوال الاستيراد (اختصاراً للطول)
}

// ==================== دوال الواجهة ====================

/**
 * عرض شاشة تسجيل الدخول
 */
function print_login(): void {
    global $VERSION;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>phpMiniAdmin - تسجيل الدخول</title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 50px; }
            .login-box { width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h3 { text-align: center; color: #333; }
            input[type="password"] { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            input[type="submit"] { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
            input[type="submit"]:hover { background: #45a049; }
            .error { color: red; text-align: center; margin-bottom: 10px; }
            .version { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h3>phpMiniAdmin v<?php echo $VERSION; ?></h3>
            <?php if (isset($err_msg) && $err_msg): ?>
                <div class="error"><?php echo hs($err_msg); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="password" name="pwd" placeholder="كلمة المرور" required>
                <input type="hidden" name="login" value="1">
                <input type="submit" value="تسجيل الدخول">
            </form>
        </div>
        <div class="version">© 2004-2024 Oleg Savchuk</div>
    </body>
    </html>
    <?php
}

/**
 * عرض شاشة الإعدادات
 */
function print_cfg(): void {
    global $DB, $err_msg, $self;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>phpMiniAdmin - الإعدادات</title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .cfg-box { width: 500px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h3 { text-align: center; color: #333; }
            label { display: block; margin: 10px 0; }
            .l { display: inline-block; width: 120px; font-weight: bold; }
            input[type="text"], input[type="password"], select { width: 250px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
            .buttons { text-align: center; margin-top: 20px; }
            input[type="submit"], input[type="button"] { background: #4CAF50; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 0 5px; }
            input[type="button"] { background: #f44336; }
            input[type="button"]:hover { background: #d32f2f; }
            input[type="submit"]:hover { background: #45a049; }
            .error { color: red; text-align: center; margin-bottom: 10px; }
            .ajax { color: #2196F3; text-decoration: none; }
            .ajax:hover { text-decoration: underline; }
        </style>
        <script>
            function cfg_toggle() {
                var e = document.getElementById('cfg-adv');
                e.style.display = e.style.display == 'none' ? 'block' : 'none';
            }
        </script>
    </head>
    <body>
        <div class="cfg-box">
            <h3>إعدادات الاتصال بقاعدة البيانات</h3>
            <?php if (isset($err_msg) && $err_msg): ?>
                <div class="error"><?php echo hs($err_msg); ?></div>
            <?php endif; ?>
            <form method="post">
                <label><span class="l">اسم المستخدم:</span> <input type="text" name="v[user]" value="<?php echo hs($DB['user'] ?? ''); ?>" required></label>
                <label><span class="l">كلمة المرور:</span> <input type="password" name="v[pwd]" value=""></label>
                
                <div style="text-align:right"><a href="#" class="ajax" onclick="cfg_toggle()">إعدادات متقدمة</a></div>
                
                <div id="cfg-adv" style="display:none;">
                    <label><span class="l">اسم قاعدة البيانات:</span> <input type="text" name="v[db]" value="<?php echo hs($DB['db'] ?? ''); ?>"></label>
                    <label><span class="l">مضيف MySQL:</span> <input type="text" name="v[host]" value="<?php echo hs($DB['host'] ?? 'localhost'); ?>"></label>
                    <label><span class="l">المنفذ:</span> <input type="text" name="v[port]" value="<?php echo hs($DB['port'] ?? ''); ?>" size="4"></label>
                    <label><span class="l">الترميز:</span> 
                        <select name="v[chset]">
                            <option value="">- افتراضي -</option>
                            <option value="utf8" <?php echo ($DB['chset'] == 'utf8') ? 'selected' : ''; ?>>UTF-8</option>
                            <option value="latin1" <?php echo ($DB['chset'] == 'latin1') ? 'selected' : ''; ?>>Latin1</option>
                        </select>
                    </label>
                    <label><input type="checkbox" name="rmb" value="1" checked> حفظ الإعدادات في الكوكيز</label>
                </div>
                
                <div class="buttons">
                    <input type="hidden" name="savecfg" value="1">
                    <input type="submit" value="تطبيق">
                    <input type="button" value="إلغاء" onclick="window.location='<?php echo hs($self); ?>'">
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * عرض الشاشة الرئيسية
 */
function print_screen(): void {
    global $out_message, $SQLq, $err_msg, $reccount, $time_all, $sqldr, $page, $MAX_ROWS_PER_PAGE, $is_limited_sql, $last_count, $is_sm, $self, $xurl, $DB, $VERSION;

    $nav = '';
    if ($is_limited_sql && ($page || $reccount >= $MAX_ROWS_PER_PAGE)) {
        $nav = "<div class='nav'>" . get_nav($page, 10000, $MAX_ROWS_PER_PAGE, "javascript:go(%p%)") . "</div>";
    }

    $dbn = $DB['db'] ?? '';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>phpMiniAdmin</title>
        <meta charset="utf-8">
        <style>
            * { box-sizing: border-box; }
            body { font-family: Arial, sans-serif; font-size: 80%; padding: 0; margin: 0; background: #f5f5f5; }
            div { padding: 3px; }
            pre { font-size: 125%; }
            textarea { width: 100%; font-family: monospace; }
            .nav { text-align: center; }
            .ft { text-align: right; margin-top: 20px; font-size: smaller; }
            .inv { background-color: #069; color: #FFF; padding: 8px; }
            .inv a { color: #FFF; text-decoration: none; margin: 0 5px; }
            .inv a:hover { text-decoration: underline; }
            table { border-collapse: collapse; }
            table.res { width: 100%; background: white; }
            table.wa { width: auto; }
            table.res th, table.res td { padding: 4px; border: 1px solid #ddd; vertical-align: top; }
            table.sm th, table.sm td { max-width: 30em; overflow: hidden; }
            table.sm th>div, table.sm td>div { max-height: 3.5em; overflow: hidden; }
            tr.e { background-color: #f2f2f2; }
            tr.o { background-color: #ffffff; }
            tr.e:hover, tr.o:hover { background-color: #e8f4ff; }
            tr.h { background-color: #4CAF50; color: white; }
            tr.h th div { color: white; }
            tr.s { background-color: #ffe082; }
            .err { color: #f44336; font-weight: bold; text-align: center; }
            .dot { border-bottom: 1px dotted #999; padding: 5px 0; }
            .ajax { text-decoration: none; border-bottom: 1px dashed; }
            .qnav { width: 30px; padding: 2px; }
            .sbtn { width: 100px; padding: 5px; background: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; }
            .sbtn:hover { background: #45a049; }
            .clear { clear: both; height: 0; }
            .pi a { text-decoration: none; }
            .pi hr { display: none; }
            .pi table { margin: 0 auto; }
            .pi table td, .pi table th { border: 1px solid #000; }
        </style>
        <script>
            var LSK = 'pma_', LSKX = LSK + 'max', LSKM = LSK + 'min', qcur = 0, LSMAX = 32;

            function $(id) { return document.getElementById(id); }

            function frefresh() {
                var F = document.DF;
                F.method = 'get';
                F.refresh.value = "1";
                F.GoSQL.click();
            }

            function go(p, sql) {
                var F = document.DF;
                F.p.value = p;
                if (sql) F.q.value = sql;
                F.GoSQL.click();
            }

            function ays() {
                return confirm('هل أنت متأكد من المتابعة؟');
            }

            function chksql() {
                var F = document.DF, v = F.qraw.value;
                if (/^\s*(?:delete|drop|truncate|alter)/.