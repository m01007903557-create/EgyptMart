<?php
/**
 * File Name: php-admin.php
 * PHP Version: 8.3
 * Description: أداة إدارة قواعد البيانات Adminer - نسخة مطورة ومتوافقة مع PHP 8.3
 * 
 * Adminer - Compact database management
 * @link https://www.adminer.org/
 * @author Jakub Vrana, https://www.vrana.cz/
 * @copyright 2007 Jakub Vrana
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 * @version 4.6.3
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

$tc = !preg_match('~^(unsafe_raw)?$~', ini_get("filter.default"));
if ($tc || ini_get("filter.default_flags")) {
    foreach (['_GET', '_POST', '_COOKIE', '_SERVER'] as $X) {
        $Yg = filter_input_array(constant("INPUT$X"), FILTER_UNSAFE_RAW);
        if ($Yg) {
            $$X = $Yg;
        }
    }
}

if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding("8bit");
}

function connection() {
    global $g;
    return $g;
}

function adminer() {
    global $c;
    return $c;
}

function version() {
    global $fa;
    return $fa;
}

function idf_unescape(string $w): string {
    $rd = substr($w, -1);
    return str_replace($rd . $rd, $rd, substr($w, 1, -1));
}

function escape_string(string $X): string {
    return substr(q($X), 1, -1);
}

function number(string $X): string {
    return preg_replace('~[^0-9]+~', '', $X);
}

function number_type(): string {
    return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';
}

function remove_slashes(array &$ef, bool $tc = false): void {
    if (get_magic_quotes_gpc()) {
        while (list($_, $X) = each($ef)) {
            foreach ($X as $jd => $W) {
                unset($ef[$_][$jd]);
                if (is_array($W)) {
                    $ef[$_][stripslashes((string)$jd)] = $W;
                    $ef[] = &$ef[$_][stripslashes((string)$jd)];
                } else {
                    $ef[$_][stripslashes((string)$jd)] = ($tc ? $W : stripslashes((string)$W));
                }
            }
        }
    }
}

function bracket_escape(string $w, bool $_a = false): string {
    static $Lg = [':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4'];
    return strtr($w, ($_a ? array_flip($Lg) : $Lg));
}

function min_version(string $mh, string $Dd = "", $h = null): bool {
    global $g;
    if (!$h) {
        $h = $g;
    }
    $Mf = $h->server_info;
    if ($Dd && preg_match('~([\d.]+)-MariaDB~', $Mf, $D)) {
        $Mf = $D[1];
        $mh = $Dd;
    }
    return (version_compare($Mf, $mh) >= 0);
}

function charset($g): string {
    return (min_version("5.5.3", "10.0", $g) ? "utf8mb4" : "utf8");
}

function script(string $Uf, string $Kg = "\n"): string {
    return "<script" . nonce() . ">$Uf</script>$Kg";
}

function script_src(string $dh): string {
    return "<script src='" . h($dh) . "'" . nonce() . "></script>\n";
}

function nonce(): string {
    return ' nonce="' . get_nonce() . '"';
}

function target_blank(): string {
    return ' target="_blank" rel="noreferrer noopener"';
}

function h(string $eg): string {
    return str_replace("\0", "&#0;", htmlspecialchars($eg, ENT_QUOTES | ENT_HTML5, 'utf-8'));
}

function nl_br(string $eg): string {
    return str_replace("\n", "<br>", $eg);
}

function checkbox(string $F, $Y, bool $Na, string $nd = "", string $ne = "", string $Ra = "", string $od = ""): string {
    $K = "<input type='checkbox' name='" . h($F) . "' value='" . h((string)$Y) . "'" . ($Na ? " checked" : "") . ($od ? " aria-labelledby='$od'" : "") . ">" . ($ne ? script("qsl('input').onclick = function () { $ne };", "") : "");
    return ($nd !== "" || $Ra !== "" ? "<label" . ($Ra ? " class='$Ra'" : "") . ">$K" . h($nd) . "</label>" : $K);
}

function optionlist(array $re, $Hf = null, bool $gh = false): string {
    $K = "";
    foreach ($re as $jd => $W) {
        $se = [$jd => $W];
        if (is_array($W)) {
            $K .= '<optgroup label="' . h((string)$jd) . '">';
            $se = $W;
        }
        foreach ($se as $_ => $X) {
            $K .= '<option' . ($gh || is_string($_) ? ' value="' . h((string)$_) . '"' : '') . 
                   (($gh || is_string($_) ? (string)$_ : (string)$X) === (string)$Hf ? ' selected' : '') . '>' . 
                   h((string)$X);
        }
        if (is_array($W)) {
            $K .= '</optgroup>';
        }
    }
    return $K;
}

function html_select(string $F, array $re, $Y = "", $me = true, string $od = ""): string {
    if ($me) {
        return "<select name='" . h($F) . "'" . ($od ? " aria-labelledby='$od'" : "") . ">" . 
               optionlist($re, $Y) . "</select>" . 
               (is_string($me) ? script("qsl('select').onchange = function () { $me };", "") : "");
    }
    $K = "";
    foreach ($re as $_ => $X) {
        $K .= "<label><input type='radio' name='" . h($F) . "' value='" . h((string)$_) . "'" . 
               ((string)$_ === (string)$Y ? " checked" : "") . ">" . h((string)$X) . "</label>";
    }
    return $K;
}

function select_input(string $wa, array $re, $Y = "", string $me = "", string $Re = ""): string {
    $tg = ($re ? "select" : "input");
    return "<$tg$wa" . ($re ? "><option value=''>$Re" . optionlist($re, $Y, true) . "</select>" : " size='10' value='" . h((string)$Y) . "' placeholder='$Re'>") . 
           ($me ? script("qsl('$tg').onchange = $me;", "") : "");
}

function confirm(string $E = "", string $If = "qsl('input')"): string {
    return script("$If.onclick = function () { return confirm('" . ($E ? js_escape($E) : lang(0)) . "'); };", "");
}

function print_fieldset(string $v, string $wd, bool $ph = false): void {
    echo "<fieldset><legend>",
         "<a href='#fieldset-$v'>$wd</a>",
         script("qsl('a').onclick = partial(toggle, 'fieldset-$v');", ""),
         "</legend>",
         "<div id='fieldset-$v'" . ($ph ? "" : " class='hidden'") . ">\n";
}

function bold(bool $Ga, string $Ra = ""): string {
    return ($Ga ? " class='active $Ra'" : ($Ra ? " class='$Ra'" : ""));
}

function odd(string $K = ' class="odd"'): string {
    static $u = 0;
    if (!$K) {
        $u = -1;
    }
    return ($u++ % 2 ? $K : '');
}

function js_escape(string $eg): string {
    return addcslashes($eg, "\r\n'\\/");
}

function json_row(string $_, $X = null): void {
    static $uc = true;
    if ($uc) {
        echo "{";
    }
    if ($_ !== "") {
        echo ($uc ? "" : ",") . "\n\t\"" . addcslashes($_, "\r\n\t\"\\/") . '": ' . 
             ($X !== null ? '"' . addcslashes((string)$X, "\r\n\"\\/") . '"' : 'null');
        $uc = false;
    } else {
        echo "\n}\n";
        $uc = true;
    }
}

function ini_bool(string $Xc): bool {
    $X = ini_get($Xc);
    return (preg_match('~^(on|true|yes)$~i', $X) || (int)$X);
}

function sid(): bool {
    static $K;
    if ($K === null) {
        $K = (SID && !($_COOKIE && ini_bool("session.use_cookies")));
    }
    return $K;
}

function set_password(string $lh, string $O, string $V, $Ne): void {
    $_SESSION["pwds"][$lh][$O][$V] = ($_COOKIE["adminer_key"] && is_string($Ne) ? 
                                      [encrypt_string($Ne, $_COOKIE["adminer_key"])] : $Ne);
}

function get_password() {
    $K = get_session("pwds");
    if (is_array($K)) {
        $K = ($_COOKIE["adminer_key"] ? decrypt_string($K[0], $_COOKIE["adminer_key"]) : false);
    }
    return $K;
}

function q(string $eg): string {
    global $g;
    return $g->quote($eg);
}

function get_vals(string $I, int $d = 0): array {
    global $g;
    $K = [];
    $J = $g->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            $K[] = $L[$d];
        }
    }
    return $K;
}

function get_key_vals(string $I, $h = null, bool $Pf = true): array {
    global $g;
    if (!is_object($h)) {
        $h = $g;
    }
    $K = [];
    $J = $h->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            if ($Pf) {
                $K[$L[0]] = $L[1] ?? '';
            } else {
                $K[] = $L[0];
            }
        }
    }
    return $K;
}

function get_rows(string $I, $h = null, string $m = "<p class='error'>"): array {
    global $g;
    $eb = (is_object($h) ? $h : $g);
    $K = [];
    $J = $eb->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_assoc()) {
            $K[] = $L;
        }
    } elseif (!$J && !is_object($h) && $m && defined("PAGE_HEADER")) {
        echo $m . error() . "\n";
    }
    return $K;
}

function unique_array(array $L, array $y): ?array {
    foreach ($y as $x) {
        if (preg_match("~PRIMARY|UNIQUE~", $x["type"] ?? '')) {
            $K = [];
            foreach ($x["columns"] as $_) {
                if (!isset($L[$_])) {
                    continue 2;
                }
                $K[$_] = $L[$_];
            }
            return $K;
        }
    }
    return null;
}

function escape_key(string $_): string {
    if (preg_match('(^([\w(]+)(' . str_replace("_", ".*", preg_quote(idf_escape("_"))) . ')([ \w)]+)$)', $_, $D)) {
        return $D[1] . idf_escape(idf_unescape($D[2])) . $D[3];
    }
    return idf_escape($_);
}

function where(array $Z, array $o = []): string {
    global $g, $z;
    $K = [];
    foreach ((array)($Z["where"] ?? []) as $_ => $X) {
        $_ = bracket_escape((string)$_, 1);
        $d = escape_key($_);
        $K[] = $d . ($z == "sql" && preg_match('~^[0-9]*\.[0-9]*$~', (string)$X) ? 
               " LIKE " . q(addcslashes((string)$X, "%_\\")) : 
               ($z == "mssql" ? " LIKE " . q(preg_replace('~[_%[]~', '[\0]', (string)$X)) : 
               " = " . unconvert_field($o[$_], q((string)$X))));
        if ($z == "sql" && preg_match('~char|text~', $o[$_]["type"] ?? '') && preg_match("~[^ -@]~", (string)$X)) {
            $K[] = "$d = " . q((string)$X) . " COLLATE " . charset($g) . "_bin";
        }
    }
    foreach ((array)($Z["null"] ?? []) as $_ => $X) {
        $K[] = escape_key((string)$X) . " IS NULL";
    }
    return implode(" AND ", $K);
}

function where_check(string $X, array $o = []): string {
    parse_str($X, $Ma);
    remove_slashes([&$Ma]);
    return where($Ma, $o);
}

function where_link(string $u, string $d, $Y, string $oe = "="): string {
    return "&where%5B$u%5D%5Bcol%5D=" . urlencode($d) . 
           "&where%5B$u%5D%5Bop%5D=" . urlencode(($Y !== null ? $oe : "IS NULL")) . 
           "&where%5B$u%5D%5Bval%5D=" . urlencode((string)$Y);
}

function convert_fields(array $e, array $o, array $N = []): string {
    $K = "";
    foreach ($e as $_ => $X) {
        if ($N && !in_array(idf_escape((string)$_), $N)) {
            continue;
        }
        $ua = convert_field($o[(string)$_] ?? null);
        if ($ua) {
            $K .= ", $ua AS " . idf_escape((string)$_);
        }
    }
    return $K;
}

function cookie(string $F, string $Y, int $zd = 2592000): bool {
    global $ba;
    return header("Set-Cookie: $F=" . urlencode($Y) . ($zd ? "; expires=" . gmdate("D, d M Y H:i:s", time() + $zd) . " GMT" : "") . 
                 "; path=" . preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"] ?? '') . 
                 ($ba ? "; secure" : "") . "; HttpOnly; SameSite=lax", false);
}

function restart_session(): void {
    if (!ini_bool("session.use_cookies")) {
        session_start();
    }
}

function stop_session(bool $wc = false): void {
    if (!ini_bool("session.use_cookies") || ($wc && @ini_set("session.use_cookies", "0") !== false)) {
        session_write_close();
    }
}

function &get_session(string $_): array {
    $driver = DRIVER ?? 'server';
    $server = SERVER ?? '';
    $username = $_GET["username"] ?? '';
    if (!isset($_SESSION[$_][$driver][$server][$username])) {
        $_SESSION[$_][$driver][$server][$username] = [];
    }
    return $_SESSION[$_][$driver][$server][$username];
}

function set_session(string $_, $X): void {
    $driver = DRIVER ?? 'server';
    $server = SERVER ?? '';
    $username = $_GET["username"] ?? '';
    $_SESSION[$_][$driver][$server][$username] = $X;
}

function auth_url(string $lh, string $O, string $V, ?string $k = null): string {
    global $Ib;
    $params = implode("|", array_keys($Ib ?? []));
    $params .= "|username" . ($k !== null ? "|db|" : "") . "|" . session_name();
    preg_match('~([^?]*)\??(.*)~', remove_from_uri($params), $D);
    return $D[1] . "?" . (sid() ? SID . "&" : "") . 
           ($lh !== "server" || $O !== "" ? urlencode($lh) . "=" . urlencode($O) . "&" : "") . 
           "username=" . urlencode($V) . ($k !== "" ? "&db=" . urlencode($k) : "") . 
           (!empty($D[2]) ? "&$D[2]" : "");
}

function is_ajax(): bool {
    return ($_SERVER["HTTP_X_REQUESTED_WITH"] ?? '') == "XMLHttpRequest";
}

function redirect(?string $C, ?string $E = null): void {
    if ($E !== null) {
        restart_session();
        $key = preg_replace('~^[^?]*~', '', ($C !== null ? $C : $_SERVER["REQUEST_URI"] ?? ''));
        $_SESSION["messages"][$key][] = $E;
    }
    if ($C !== null) {
        if ($C == "") {
            $C = ".";
        }
        header("Location: $C");
        exit;
    }
}

function query_redirect(string $I, string $C, string $E, bool $mf = true, bool $gc = true, bool $nc = false, string $_g = ""): bool {
    global $g, $m, $c;
    if ($gc) {
        $ag = microtime(true);
        $nc = !$g->query($I);
        $_g = format_time($ag);
    }
    $Wf = "";
    if ($I) {
        $Wf = $c->messageQuery($I, $_g, $nc);
    }
    if ($nc) {
        $m = error() . $Wf . script("messagesPrint();");
        return false;
    }
    if ($mf) {
        redirect($C, $E . $Wf);
    }
    return true;
}

function queries($I): bool {
    global $g;
    static $hf = [];
    static $ag;
    if (!$ag) {
        $ag = microtime(true);
    }
    if ($I === null) {
        return false;
    }
    $hf[] = (preg_match('~;$~', $I) ? "DELIMITER ;;\n$I;\nDELIMITER " : $I) . ";";
    return $g->query($I);
}

function apply_queries(string $I, array $S, callable $cc = null): bool {
    if ($cc === null) {
        $cc = 'table';
    }
    foreach ($S as $Q) {
        if (!queries("$I " . $cc($Q))) {
            return false;
        }
    }
    return true;
}

function queries_redirect(string $C, string $E, bool $mf): bool {
    list($hf, $_g) = queries(null);
    return query_redirect($hf, $C, $E, $mf, false, !$mf, $_g);
}

function format_time(float $ag): string {
    return lang(1, max(0, microtime(true) - $ag));
}

function remove_from_uri(string $Fe = ""): string {
    $uri = $_SERVER["REQUEST_URI"] ?? '';
    $params = $Fe . (SID ? "" : "|" . session_name());
    return substr(preg_replace("~(?<=[?&])($params)=[^&]*&~", '', $uri . "&"), 0, -1);
}

function pagination(int $G, int $pb): string {
    return " " . ($G == $pb ? $G + 1 : 
           '<a href="' . h(remove_from_uri("page") . ($G ? "&page=$G" . (isset($_GET["next"]) ? "&next=" . urlencode((string)$_GET["next"]) : "") : "")) . '">' . 
           ($G + 1) . "</a>");
}

function get_file(string $_, bool $xb = false): ?string {
    $rc = $_FILES[$_] ?? null;
    if (!$rc) {
        return null;
    }
    foreach ($rc as $_ => $X) {
        $rc[$_] = (array)$X;
    }
    $K = '';
    foreach ($rc["error"] as $_ => $m) {
        if ($m) {
            return (string)$m;
        }
        $F = $rc["name"][$_];
        $Hg = $rc["tmp_name"][$_];
        $fb = file_get_contents($xb && preg_match('~\.gz$~', $F) ? "compress.zlib://$Hg" : $Hg);
        if ($xb) {
            $ag = substr($fb, 0, 3);
            if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $ag, $sf)) {
                $fb = iconv("utf-16", "utf-8", $fb);
            } elseif ($ag == "\xEF\xBB\xBF") {
                $fb = substr($fb, 3);
            }
            $K .= $fb . "\n\n";
        } else {
            $K .= $fb;
        }
    }
    return $K;
}

function upload_error(int $m): string {
    $Jd = ($m == UPLOAD_ERR_INI_SIZE ? ini_get("upload_max_filesize") : 0);
    return ($m ? lang(2) . ($Jd ? " " . lang(3, $Jd) : "") : lang(4));
}

function repeat_pattern(string $Pe, int $xd): string {
    return str_repeat("$Pe{0,65535}", (int)($xd / 65535)) . "$Pe{0," . ($xd % 65535) . "}";
}

function is_utf8(string $X): bool {
    return (preg_match('~~u', $X) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $X));
}

function shorten_utf8(string $eg, int $xd = 80, string $ig = ""): string {
    $pattern = "(^(" . repeat_pattern("[\t\r\n -\x{10FFFF}]", $xd) . ")($)?)u";
    if (!preg_match($pattern, $eg, $D)) {
        preg_match("(^(" . repeat_pattern("[\t\r\n -~]", $xd) . ")($)?)", $eg, $D);
    }
    return h($D[1] ?? '') . $ig . (isset($D[2]) ? "" : "<i>...</i>");
}

function format_number(float $X): string {
    return strtr(number_format($X, 0, ".", lang(5)), 
                 preg_split('~~u', lang(6), -1, PREG_SPLIT_NO_EMPTY));
}

function friendly_url(string $X): string {
    return preg_replace('~[^a-z0-9_]~i', '-', $X);
}

function hidden_fields(array $ef, array $Uc = []): bool {
    $K = false;
    while (list($_, $X) = each($ef)) {
        if (!in_array((string)$_, $Uc)) {
            if (is_array($X)) {
                foreach ($X as $jd => $W) {
                    $ef[(string)$_ . "[$jd]"] = $W;
                }
            } else {
                $K = true;
                echo '<input type="hidden" name="' . h((string)$_) . '" value="' . h((string)$X) . '">';
            }
        }
    }
    return $K;
}

function hidden_fields_get(): void {
    echo (sid() ? '<input type="hidden" name="' . h(session_name()) . '" value="' . h(session_id()) . '">' : ''),
         (defined('SERVER') && SERVER !== null ? '<input type="hidden" name="' . h(DRIVER) . '" value="' . h(SERVER) . '">' : ""),
         '<input type="hidden" name="username" value="' . h($_GET["username"] ?? '') . '">';
}

function table_status1(string $Q, bool $oc = false): array {
    $K = table_status($Q, $oc);
    return ($K ? $K : ["Name" => $Q]);
}

function column_foreign_keys(string $Q): array {
    global $c;
    $K = [];
    foreach ($c->foreignKeys($Q) as $p) {
        foreach ($p["source"] as $X) {
            $K[$X][] = $p;
        }
    }
    return $K;
}

function enum_input(string $U, string $wa, array $n, $Y, ?string $Wb = null): string {
    global $c;
    preg_match_all("~'((?:[^']|'')*)'~", $n["length"] ?? '', $Ed);
    $K = ($Wb !== null ? "<label><input type='$U'$wa value='" . h($Wb) . "'" . 
          ((is_array($Y) ? in_array($Wb, $Y) : $Y === 0) ? " checked" : "") . "><i>" . lang(7) . "</i></label>" : "");
    foreach ($Ed[1] as $u => $X) {
        $X = stripcslashes(str_replace("''", "'", $X));
        $Na = (is_int($Y) ? $Y == $u + 1 : (is_array($Y) ? in_array($u + 1, $Y) : $Y === $X));
        $K .= " <label><input type='$U'$wa value='" . ($u + 1) . "'" . ($Na ? ' checked' : '') . '>' . 
              h($c->editVal($X, $n)) . '</label>';
    }
    return $K;
}

function input(array $n, $Y, ?string $s): void {
    global $Tg, $c, $z;
    $F = h(bracket_escape($n["field"] ?? ''));
    echo "<td class='function'>";
    
    if (is_array($Y) && !$s) {
        $ta = [$Y];
        if (version_compare(PHP_VERSION, '5.4') >= 0) {
            $ta[] = JSON_PRETTY_PRINT;
        }
        $Y = call_user_func_array('json_encode', $ta);
        $s = "json";
    }
    
    $uf = ($z == "mssql" && !empty($n["auto_increment"]));
    if ($uf && !isset($_POST["save"])) {
        $s = null;
    }
    
    $Cc = (isset($_GET["select"]) || $uf ? ["orig" => lang(8)] : []) + $c->editFunctions($n);
    $wa = " name='fields[$F]'";
    
    if (($n["type"] ?? '') == "enum") {
        echo h($Cc[""] ?? '') . "<td>" . $c->editInput($_GET["edit"] ?? '', $n, $wa, $Y);
    } else {
        $Lc = (in_array($s, $Cc) || isset($Cc[$s]));
        echo (count($Cc) > 1 ? 
              "<select name='function[$F]'>" . optionlist($Cc, $s === null || $Lc ? $s : "") . "</select>" . 
              on_help("getTarget(event).value.replace(/^SQL\$/, '')", 1) . script("qsl('select').onchange = functionChange;", "") : 
              h(reset($Cc))) . '<td>';
              
        $Zc = $c->editInput($_GET["edit"] ?? '', $n, $wa, $Y);
        if ($Zc !== "") {
            echo $Zc;
        } elseif (preg_match('~bool~', $n["type"] ?? '')) {
            echo "<input type='hidden'$wa value='0'>" .
                 "<input type='checkbox'" . (preg_match('~^(1|t|true|y|yes|on)$~i', (string)$Y) ? " checked='checked'" : "") . "$wa value='1'>";
        } elseif (($n["type"] ?? '') == "set") {
            preg_match_all("~'((?:[^']|'')*)'~", $n["length"] ?? '', $Ed);
            foreach ($Ed[1] as $u => $X) {
                $X = stripcslashes(str_replace("''", "'", $X));
                $Na = (is_int($Y) ? ($Y >> $u) & 1 : in_array($X, explode(",", (string)$Y), true));
                echo " <label><input type='checkbox' name='fields[$F][$u]' value='" . (1 << $u) . "'" . 
                     ($Na ? ' checked' : '') . ">" . h($c->editVal($X, $n)) . '</label>';
            }
        } elseif (preg_match('~blob|bytea|raw|file~', $n["type"] ?? '') && ini_bool("file_uploads")) {
            echo "<input type='file' name='fields-$F'>";
        } elseif (($yg = preg_match('~text|lob~', $n["type"] ?? '')) || preg_match("~\n~", (string)$Y)) {
            if ($yg && $z != "sqlite") {
                $wa .= " cols='50' rows='12'";
            } else {
                $M = min(12, substr_count((string)$Y, "\n") + 1);
                $wa .= " cols='30' rows='$M'" . ($M == 1 ? " style='height: 1.2em;'" : "");
            }
            echo "<textarea$wa>" . h((string)$Y) . '</textarea>';
        } elseif ($s == "json" || preg_match('~^jsonb?$~', $n["type"] ?? '')) {
            echo "<textarea$wa cols='50' rows='12' class='jush-js'>" . h((string)$Y) . '</textarea>';
        } else {
            $Ld = (!preg_match('~int~', $n["type"] ?? '') && preg_match('~^(\d+)(,(\d+))?$~', $n["length"] ?? '', $D) ? 
                  ((preg_match("~binary~", $n["type"] ?? '') ? 2 : 1) * (int)$D[1] + (isset($D[3]) ? 1 : 0) + (isset($D[2]) && empty($n["unsigned"]) ? 1 : 0)) : 
                  (isset($Tg[$n["type"] ?? '']) ? $Tg[$n["type"] ?? ''] + (empty($n["unsigned"]) ? 0 : 1) : 0));
            if ($z == 'sql' && min_version("5.6") && preg_match('~time~', $n["type"] ?? '')) {
                $Ld += 7;
            }
            echo "<input" . ((!$Lc || $s === "") && preg_match('~(?<!o)int(?!er)~', $n["type"] ?? '') && 
                 !preg_match('~\[\]~', $n["full_type"] ?? '') ? " type='number'" : "") . 
                 " value='" . h((string)$Y) . "'" . ($Ld ? " data-maxlength='$Ld'" : "") . 
                 (preg_match('~char|binary~', $n["type"] ?? '') && $Ld > 20 ? " size='40'" : "") . "$wa>";
        }
        echo $c->editHint($_GET["edit"] ?? '', $n, $Y);
        $uc = 0;
        foreach ($Cc as $_ => $X) {
            if ($_ === "" || !$X) {
                break;
            }
            $uc++;
        }
        if ($uc) {
            echo script("mixin(qsl('td'), {onchange: partial(skipOriginal, $uc), oninput: function () { this.onchange(); }});");
        }
    }
    echo "\n";
}

function process_input(array $n) {
    global $c, $l;
    $w = bracket_escape($n["field"] ?? '');
    $s = $_POST["function"][$w] ?? '';
    $Y = $_POST["fields"][$w] ?? '';
    
    if (($n["type"] ?? '') == "enum") {
        if ($Y == -1) {
            return false;
        }
        if ($Y == "") {
            return "NULL";
        }
        return +$Y;
    }
    
    if (!empty($n["auto_increment"]) && $Y == "") {
        return null;
    }
    
    if ($s == "orig") {
        return (!empty($n["on_update"]) && $n["on_update"] == "CURRENT_TIMESTAMP" ? idf_escape($n["field"] ?? '') : false);
    }
    
    if ($s == "NULL") {
        return "NULL";
    }
    
    if (($n["type"] ?? '') == "set") {
        return array_sum((array)$Y);
    }
    
    if ($s == "json") {
        $s = "";
        $Y = json_decode((string)$Y, true);
        if (!is_array($Y)) {
            return false;
        }
        return $Y;
    }
    
    if (preg_match('~blob|bytea|raw|file~', $n["type"] ?? '') && ini_bool("file_uploads")) {
        $rc = get_file("fields-$w");
        if (!is_string($rc)) {
            return false;
        }
        return $l->quoteBinary($rc);
    }
    
    return $c->processInput($n, $Y, $s);
}

function fields_from_edit(): array {
    global $l;
    $K = [];
    foreach ((array)($_POST["field_keys"] ?? []) as $_ => $X) {
        if ($X != "") {
            $X = bracket_escape((string)$X);
            $_POST["function"][$X] = $_POST["field_funs"][$_] ?? '';
            $_POST["fields"][$X] = $_POST["field_vals"][$_] ?? '';
        }
    }
    foreach ((array)($_POST["fields"] ?? []) as $_ => $X) {
        $F = bracket_escape((string)$_, 1);
        $K[$F] = [
            "field" => $F,
            "privileges" => ["insert" => 1, "update" => 1],
            "null" => 1,
            "auto_increment" => ($_ == $l->primary)
        ];
    }
    return $K;
}

function search_tables(): void {
    global $c, $g;
    $_GET["where"][0]["val"] = $_POST["query"] ?? '';
    $Kf = "<ul>\n";
    foreach (table_status('', true) as $Q => $R) {
        $F = $c->tableName($R);
        if (isset($R["Engine"]) && $F != "" && (!isset($_POST["tables"]) || in_array($Q, (array)$_POST["tables"]))) {
            $J = $g->query("SELECT" . limit("1 FROM " . table($Q), " WHERE " . implode(" AND ", $c->selectSearchProcess(fields($Q), [])), 1));
            if (!$J || $J->fetch_row()) {
                $af = "<a href='" . h(ME . "select=" . urlencode($Q) . 
                      "&where[0][op]=" . urlencode($_GET["where"][0]["op"] ?? '') . 
                      "&where[0][val]=" . urlencode($_GET["where"][0]["val"] ?? '')) . "'>$F</a>";
                echo "$Kf<li>" . ($J ? $af : "<p class='error'>$af: " . error()) . "\n";
                $Kf = "";
            }
        }
    }
    echo ($Kf ? "<p class='message'>" . lang(9) : "</ul>") . "\n";
}

function dump_headers($Tc, bool $Sd = false): string {
    global $c;
    $K = $c->dumpHeaders($Tc, $Sd);
    $Ce = $_POST["output"] ?? '';
    if ($Ce != "text") {
        header("Content-Disposition: attachment; filename=" . $c->dumpFilename($Tc) . ".$K" . 
               ($Ce != "file" && !preg_match('~[^0-9a-z]~', $Ce) ? ".$Ce" : ""));
    }
    session_write_close();
    ob_flush();
    flush();
    return $K;
}

function dump_csv(array $L): void {
    foreach ($L as $_ => $X) {
        if (preg_match("~[\"\n,;\t]~", (string)$X) || (string)$X === "") {
            $L[$_] = '"' . str_replace('"', '""', (string)$X) . '"';
        }
    }
    echo implode(($_POST["format"] == "csv" ? "," : ($_POST["format"] == "tsv" ? "\t" : ";")), $L) . "\r\n";
}

function apply_sql_function(?string $s, string $d): string {
    return ($s ? ($s == "unixepoch" ? "DATETIME($d, '$s')" : 
           ($s == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$s("))) . "$d)" : $d);
}

function get_temp_dir(): ?string {
    $K = ini_get("upload_tmp_dir");
    if (!$K) {
        if (function_exists('sys_get_temp_dir')) {
            $K = sys_get_temp_dir();
        } else {
            $sc = @tempnam("", "");
            if (!$sc) {
                return null;
            }
            $K = dirname($sc);
            unlink($sc);
        }
    }
    return $K;
}

function file_open_lock(string $sc) {
    $r = @fopen($sc, "r+");
    if (!$r) {
        $r = @fopen($sc, "w");
        if (!$r) {
            return null;
        }
        chmod($sc, 0660);
    }
    flock($r, LOCK_EX);
    return $r;
}

function file_write_unlock($r, string $rb): void {
    rewind($r);
    fwrite($r, $rb);
    ftruncate($r, strlen($rb));
    flock($r, LOCK_UN);
    fclose($r);
}

function password_file(bool $i): ?string {
    $sc = get_temp_dir() . "/adminer.key";
    $K = @file_get_contents($sc);
    if ($K || !$i) {
        return $K ?: null;
    }
    $r = @fopen($sc, "w");
    if ($r) {
        chmod($sc, 0660);
        $K = rand_string();
        fwrite($r, $K);
        fclose($r);
    }
    return $K;
}

function rand_string(): string {
    return md5(uniqid((string)mt_rand(), true));
}

function select_value($X, $B, array $n, string $zg): string {
    global $c;
    if (is_array($X)) {
        $K = "";
        foreach ($X as $jd => $W) {
            $K .= "<tr>" . ($X != array_values($X) ? "<th>" . h((string)$jd) : "") . 
                  "<td>" . select_value($W, $B, $n, $zg);
        }
        return "<table cellspacing='0'>$K</table>";
    }
    if (!$B) {
        $B = $c->selectLink($X, $n);
    }
    if ($B === null) {
        if (is_mail((string)$X)) {
            $B = "mailto:$X";
        }
        if (is_url((string)$X)) {
            $B = (string)$X;
        }
    }
    $K = $c->editVal($X, $n);
    if ($K !== null) {
        if (!is_utf8($K)) {
            $K = "\0";
        } elseif ($zg != "" && is_shortable($n)) {
            $K = shorten_utf8($K, max(0, (int)$zg));
        } else {
            $K = h($K);
        }
    }
    return $c->selectVal($K, $B, $n, $X);
}

function is_mail(string $Tb): bool {
    $va = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    $Pe = "$va+(\\.$va+)*@($Hb?\\.)+$Hb";
    return preg_match("(^$Pe(,\\s*$Pe)*\$)i", $Tb);
}

function is_url(string $eg): bool {
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    return preg_match("~^(https?)://($Hb?\\.)+$Hb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $eg);
}

function is_shortable(array $n): bool {
    return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $n["type"] ?? '');
}

function count_rows(string $Q, array $Z, bool $fd, array $t): string {
    global $z;
    $I = " FROM " . table($Q) . ($Z ? " WHERE " . implode(" AND ", $Z) : "");
    return ($fd && ($z == "sql" || count($t) == 1) ? 
           "SELECT COUNT(DISTINCT " . implode(", ", $t) . ")$I" : 
           "SELECT COUNT(*)" . ($fd ? " FROM (SELECT 1$I GROUP BY " . implode(", ", $t) . ") x" : $I));
}

function slow_query(string $I) {
    global $c, $T, $l;
    $k = $c->database();
    $Ag = $c->queryTimeout();
    $Sf = $l->slowQuery($I, $Ag);
    if (!$Sf && support("kill") && is_object($h = connect()) && ($k == "" || $h->select_db($k))) {
        $ld = $h->result(connection_id());
        echo '<script', nonce(), '>
var timeout = setTimeout(function () {
	ajax(\'', js_escape(ME), 'script=kill\', function () {
	}, \'kill=',$ld,'&token=',$T,'\');
}, ', 1000 * $Ag, ');
</script>
';
    } else {
        $h = null;
    }
    ob_flush();
    flush();
    $K = @get_key_vals(($Sf ? $Sf : $I), $h, false);
    if ($h) {
        echo script("clearTimeout(timeout);");
        ob_flush();
        flush();
    }
    return $K;
}

function get_token(): string {
    $kf = rand(1, 1000000);
    return ($kf ^ $_SESSION["token"]) . ":$kf";
}

function verify_token(): bool {
    $token = explode(":", $_POST["token"] ?? '');
    $T = $token[0] ?? '';
    $kf = (int)($token[1] ?? 0);
    return ($kf ^ $_SESSION["token"]) == $T;
}

function lzw_decompress(string $Da): string {
    $Db = 256;
    $Ea = 8;
    $Ta = [];
    $vf = 0;
    $wf = 0;
    for ($u = 0; $u < strlen($Da); $u++) {
        $vf = ($vf << 8) + ord($Da[$u]);
        $wf += 8;
        if ($wf >= $Ea) {
            $wf -= $Ea;
            $Ta[] = $vf >> $wf;
            $vf &= (1 << $wf) - 1;
            $Db++;
            if ($Db >> $Ea) {
                $Ea++;
            }
        }
    }
    $Cb = range("\0", "\xFF");
    $K = "";
    foreach ($Ta as $u => $Sa) {
        $Sb = $Cb[$Sa] ?? null;
        if (!isset($Sb)) {
            $Sb = $vh . $vh[0];
        }
        $K .= $Sb;
        if ($u) {
            $Cb[] = $vh . $Sb[0];
        }
        $vh = $Sb;
    }
    return $K;
}

function on_help(string $Za, int $Qf = 0): string {
    return script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $Za, $Qf) }, onmouseout: helpMouseout});", "");
}

function edit_form(string $b, array $o, $L, bool $bh): void {
    global $c, $z, $T, $m;
    $ng = $c->tableName(table_status1($b, true));
    page_header(($bh ? lang(10) : lang(11)), $m, ["select" => [$b, $ng]], $ng);
    if ($L === false) {
        echo "<p class='error'>" . lang(12) . "\n";
    }
    echo '<form action="" method="post" enctype="multipart/form-data" id="form">
';
    if (!$o) {
        echo "<p class='error'>" . lang(13) . "\n";
    } else {
        echo "<table cellspacing='0'>" . script("qsl('table').onkeydown = editingKeydown;");
        foreach ($o as $F => $n) {
            echo "<tr><th>" . $c->fieldName($n);
            $yb = $_GET["set"][bracket_escape($F)] ?? null;
            if ($yb === null) {
                $yb = $n["default"] ?? '';
                if (($n["type"] ?? '') == "bit" && preg_match("~^b'([01]*)'\$~", (string)$yb, $sf)) {
                    $yb = $sf[1];
                }
            }
            $Y = ($L !== null ? ($L[$F] !== "" && $z == "sql" && preg_match("~enum|set~", $n["type"] ?? '') ? 
                  (is_array($L[$F]) ? array_sum($L[$F]) : +$L[$F]) : $L[$F]) : 
                  (!$bh && !empty($n["auto_increment"]) ? "" : (isset($_GET["select"]) ? false : $yb)));
            if (!isset($_POST["save"]) && is_string($Y)) {
                $Y = $c->editVal($Y, $n);
            }
            $s = (isset($_POST["save"]) ? (string)($_POST["function"][$F] ?? '') : 
                  ($bh && !empty($n["on_update"]) && $n["on_update"] == "CURRENT_TIMESTAMP" ? "now" : 
                  ($Y === false ? null : ($Y !== null ? '' : 'NULL'))));
            if (preg_match("~time~", $n["type"] ?? '') && $Y == "CURRENT_TIMESTAMP") {
                $Y = "";
                $s = "now";
            }
            input($n, $Y, $s);
            echo "\n";
        }
        if (!support("table")) {
            echo "<tr>" .
                 "<th><input name='field_keys[]'>" . script("qsl('input').oninput = fieldChange;") .
                 "<td class='function'>" . html_select("field_funs[]", $c->editFunctions(["null" => isset($_GET["select"])])) .
                 "<td><input name='field_vals[]'>" .
                 "\n";
        }
        echo "</table>\n";
    }
    echo "<p>\n";
    if ($o) {
        echo "<input type='submit' value='" . lang(14) . "'>\n";
        if (!isset($_GET["select"])) {
            echo "<input type='submit' name='insert' value='" . ($bh ? lang(15) : lang(16)) . "' title='Ctrl+Shift+Enter'>\n" .
                 ($bh ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '" . lang(17) . "...', this); };") : "");
        }
    }
    echo ($bh ? "<input type='submit' name='delete' value='" . lang(18) . "'>" . confirm() . "\n" : 
          ($_POST || !$o ? "" : script("focus(qsa('td', qs('#form'))[1].firstChild);")));
    
    if (isset($_GET["select"])) {
        hidden_fields([
            "check" => (array)($_POST["check"] ?? []),
            "clone" => $_POST["clone"] ?? '',
            "all" => $_POST["all"] ?? ''
        ]);
    }
    
    echo '<input type="hidden" name="referer" value="' . h(isset($_POST["referer"]) ? $_POST["referer"] : ($_SERVER["HTTP_REFERER"] ?? '')) . '">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="' . h($T) . '">
</form>
';
}

if (isset($_GET["file"])) {
    if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + 365 * 24 * 60 * 60) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: immutable");
    
    if ($_GET["file"] == "favicon.ico") {
        header("Content-Type: image/x-icon");
        echo lzw_decompress("\0\0\0` \0�\0\n @\0�C��\"\0`E�Q����?�tvM'�Jd�d\\�b0\0�\"��fӈ��s5����A�XPaJ�0���8�#R�T��z`�#.��c�X��Ȁ?�-\0�Im?�.�M��\0ȯ(̉��/(%�\0");
    } elseif ($_GET["file"] == "default.css") {
        header("Content-Type: text/css; charset=utf-8");
        echo lzw_decompress("\n1̇�ٌ�l7��B1�4vb0��fs���n2B�ѱ٘�n:�#(�b.\rDc)��a7E����l�ñ��i1̎s���-4��f�	��i7�������Fé�vt2���!�r0����t~�U�'3M��W�B�'c�P�:6T\rc�A�zr_�WK�\r-�VNFS%~�c���&�\\^�r����u�ŎÞ�ً4'7k�����Q��h�'g\rFB\ryT7SS�P�1=ǤcI��:�d��m>�S8L�J��t.M���	ϋ`'C����889�� �Q����2�#8А����6m����j��h�<�����9/���:�J�)ʂ�\0d>!\0Z��v��n����o(����k�7��s��>���!�R\"*nS�\0@P\"���(�#[���@g�o���zn�9k�8�n���1�I*��=�n������0�c(�;�à��!���*c��>Ύ�E7D�LJ��1����`�8(��3M��\"�39�?E�e=Ҭ�~������Ӹ7;�C����E\rd!)�a*�5ajo\0�#`�38�\0��]�e����2�	mk��e]���AZs�StZ�Z!)BR�G+�#Jv2(����c�4<�#sB�0���6YL\r�=���[�73��<�:��bx��J=	m_ ���f�l��t��I��H�3�x*���6`t6��%�U�L�eق�<�\0�AQ<P<:�#u/�:T\\>��-�xJ�͍QH\nj�L+j�z���7���`����\nk��'�N�vX>�C-T˩�����4*L�%Cj>7ߨ�ި���`���;y���q�r�3#��} :#n�\r��^�=C�Aܸ�Ǝ�s&8��K&��*0��t�S���=�[��:�\\]�E݌�/O�>^]�ø�<����gZ�V��q����� ��x\\�������޺��\"J�\\î��##���D��x6���5x�������\rH�l ����b��r�7��6���j|����ۖ*�FAquvyO��WeM����D.F��:R�\$-����T!�DS`�8D�~��A`(�em�����T@O1@��X���\nLp���P�����m�yf��)	���GSEI���xC(s(a�?\$`tE�n����,�� \$a��U>,�В\$Z�kDm,G\0��\\��i��%ʹ� n��������g���b	y`��Ԇ�W�� 䗗�_C��T\ni��H%�da��i�7�At�,��J�X4n����0o͹�9g\nzm�M%`�'I���О-����7:p�3p��Q�rED������b2]�PF����>e���3j\n�߰t!�?4f�tK;��\rΞи�!�o�u�?���Ph���0uIC}'~��2�v�Q���8)���7�DI�=��y&��ea�s*hɕjlA�(��\"�\\��m^i��M)��^�	|~�l��#!Y�f81RS����!���62P�C��l&���xd!�|��9�`�_OY�=��G�[E�-eL�CvT� )�@�j-5���pSg�.�G=���ZE��\$\0�цKj�U��\$���G'I�P��~�ځ� ;��hNێG%*�Rj��X[�XPf^��|��T!�*N��І�\rU��^q1V!��Uz,�I|7�7�r,���7���ľB���;�+���ߕ�A�p����^���~ؼW!3P�I8]��v�J��f�q�|,���9W�f`\0�q�Z�p}[Jdhy��N��Y|��Cy,�<s A�{e�Q���hd���Ǉ �B4;ks&�������a�������;˹}�S��J���)�=d��|���Nd��I�*8���dl�ѓ�E6~Ϩ�F����X`�M\rʞ/�%B/V�I�N&;���0�UC cT&.E+����������@�0`;���G�5���ަj'������Ɛ�Y�+��QZ-i���yv��I�5��,O|�P�]Fۏ�����\0���2�49͢���n/χ]س&��I^�=�l��qfI��= �]x1GR�&�e�7��)��'��:B�B�>a�z�-���2.����bz���#������Uᓍ�L7-�w�t�3ɵ���e���D��\$�#���j�@�G�8� �7p���R�YC��~��:�@��EU�J��;67v]�J'���q1ϳ�El�QІi������/��{k<��֡M�po�}��r��q�؞�c�ä�_m�w���^�u������������ln���	��_�~�G�n����{kܞ�w���\rj~�K�\0�����-����B�;����b`}�CC,���-��L��8\r,��kl�ǌ�n}-5����3u�gm��Ÿ�*�/������׏�`�`�#x�+B?#�ۏN;OR\r����\$�����k��ϙ\01\0k�\0�8��a��/t���#(&�l&���p��삅���i�M�{�zp*�-g���v��6�k�	����d�؋����A`6�lX)+d ���7 �\r�� �ځcj6��\rp�\r��\r\"oP�7�\r��\0�\0�y��P���\rQ7���Z��4Q���ڍp/�y\r��##D�;����<�g�\0fi2�)f�\\	m�Gh\r�#�n����@[ �G�\"Sqm��\r���#��(Aj��qѣ%���̑3qE��\0r�����0��я�����.��Q7шW���u����� �@��H��q'vs�0�\n�+0����SG�p�O`�\r)c�#�����R=\$�ƐR\r�Gы\$R?%2C�[\0؍�~�!�\\��p�#@���O(rg%�?ra\$��)r](��&�?&�#&R�',\rqV3�\"H�m+���l�Q\"\0�4��\$r�,��=����&2;.�H@`����a����\$�_*RIS&��q��_�1�1+1������3)2�V7��2l�ڄ!1g-�2f`���,Q�7��0qg�]!q��m6����_�M7 ���7�o6Q����kp�3�g9��s� 3�6�\r�:S�9ӏ;� �\r9�-\0�Yӧ0Q�<b#<Ӂ�w/�G��>r�\r��=3��^&Q;ѣ?q�0\"�0HЙ�|���ʖS��i��@*�T�2�T#�� �\0�C��07]?��&���E��D�;:/�3�E�5��EQ�e��T\"�m����5�E;���#=4�8��*�����LS�5Hr�JE TO\rԅJ��J��J���eG)8B�8�,&��G�����	���+M���ɲ��^*���G��14�6�\$.\"拢�I4w!\$L �8b�A2�L�'M?MF�\$�,����Nr��/4�BJ�¨");
    } elseif ($_GET["file"] == "functions.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo lzw_decompress("f:��gCI��\n8��3)��7���81��x:\nOg#)��r7\n\"���`�|2�gSi�H)N�S���\r��\"0��@�)�`(\$s6O!���V/=��' T4�=��iS��6IO��er�x�9�*ź��n3�\rщv�C��`���2G%�Y�����1��f���Ȃl��1�\ny�*pC\r\$�n�T��3=\\�r9O\"�	��l<�\r�\\��I,�s\nA��eh+M��!�q0��f�`(�N{c��+w���Y��p٧3�3��+I��j�����k��n�q���zi#^r�����3����[���o;��(��6�#�Ґ��\":cz>ߣC2v�CX�<�P��c*5\n����/�P97�|F��c0�����!����!���!��\nZ%�ć#CH�!��r8�\$����,�Rܔ2���^0��@�2��(�88P/���݄�\\�\$La\\�;c�H��HX���\nʃt���8A<�sZ�*�;I��3��@�2<���!A8G<�j�-K�({*\r��a1���N4Tc\"\\�!=1^���M9O�:�;j��\r�X��L#H�7�#Tݪ/-���p�;�B \n�2!���t]apΎ��\0R�C�v�M�I,\r���\0Hv��?kT�4����uٱ�;&���+&�����\r�X���bu4ݡi88�2B�/⃖4���N8A�A)52������2��s�8��5���p�WC@�:�t�㾴�e��h\"#8_��cp^���I]OH��:zd�3g�(���Ök���\\6����2�ږ��i��7���]\r�xO�n�p�<��p�Q�U�n��|@���#G3��8bA��6�2�67%#�\\8\r��2�c\r�ݟk��.(�	��-�J;���� ��L�� ���W��㧓ѥɤ�����n��ҧ���M��9ZНs]�z����y^[��4-�U\0ta��62^��.`���.C�j�[ᄠ% Q\0`d�M8�����\$O0`4���\n\0a\rA�<�@����\r!�:�BA�9�?h>�Ǻ��~̌�6Ȉh�=�-�A7X��և\\�\r��Q<蚧q�'!XΓ2�T �!�D\r��,K�\"�%�H�qR\r�̠��C =��������<c�\n#<�5�M� �E��y�������o\"�cJKL2�&��eR��W�AΐTw�ё;�J���\\`)5��ޜB�qhT3��R	�'\r+\":�8��tV�A�+]��S72���Y�F��Z85�c,���J��/+S�nBpoW�d��\"�Q��a�ZKp�ާy\$�����4�I�@L'@�xC�df�~}Q*�ҺA��Q�\"B�*2\0�.��kF�\"\r��� �o�\\�Ԣ���VijY��M��O�\$��2�ThH����0XH�5~kL���T*:~P��2�t���B\0�Y������j�vD�s.�9�s��̤�P�*x���b�o����P�\$�W/�*��z';��\$�*����d�m�Ã�'b\r�n%��47W�-�������K���@<�g�èbB��[7�\\�|�VdR��6leQ�`(Ԣ,�d���8\r�]S:?�1�`��Y�`�A�ғ%��ZkQ�sM�*���{`�J*�w��ӊ>�վ�D���>�eӾ�\"�t+po������W\$�����Q��@��3t`����-k7g���]��l��E��^dW>nv�t�lzPH��FvW�V\n�h;��B�D�س/�:J��\\�+ %������]��ъ��wa�ݫ���=��X����N�/��w�J�_[�t)5���QR2l�-:�Y9�&l R;�u#S	� ht�k�E!l���>SH��X<,��O�YyЃ%L�]\0�	��^�dw�3�,Sc�Qt�e=�M:4���2]��P�T�s��n:��u>�/�d�� ���a�'%�����qҨ&@֐���H�G�@w8p����΁�Z\n��{�[�t2���a��>	�w�J�^+u~�o��µXkզBZk˱�X=��0>�t��lŃ)Wb�ܦ��'�A�,��m�Y�,�A����e��#V��+�n1I����E�+[����[��-R�mK9��~����L�-3O���`_0s���L;�����]�6��|��h�V�T:��ޞerM��a�\$~e�9�>�����Д�\r��\\���J1Ú���%�=0{�	����|ޗtڼ�=����Q�|\0?��[g@u?ɝ|��4�*��c-7�4\ri'^����n;�������(����{K�h�nf���Zϝ}l�����]\r���pJ>�,gp{�;�\0��u)��s�N�'����H��C9M5��*��`�k�㬎����AhY��*����jJ�ǅPN+^� D�*��À���D��P���LQ`O&��\0�}�\$���6�Zn>��0� �e��\n��	�trp!�hV�'Py�^�*|r%|\nr\r#���@w����T.Rv�8�j�\nmB���p�� �Y0�Ϣ�m\0�@P\r8�Y\rG��d�	�QG�P%E�/@]\r���{\0�Q����bR M\rF��|��%0SDr�����f/����\":�mo�ރ�%�@�3H�x\0�l\0���	��W����\n�8\r\0}�@�D��`#�t��.�jEoDrǢlb����t�f4�0���%�0���k�z2\r�� �W@�%\r\n~1��X�����D2!��O�*���{0<E��k*m�0ı���|\r\n�^i��� ��!.�r � ��������f��Ĭ��+:���ŋJ�B5\$L���P���LĂ��� Z@����`^P�L%5%jp�H�W��on��kA#&���8��<K6�/����̏������XWe+&�%���c&rj��'%�x�����nK�2�2ֶ�l��*�.�r��΢���*�\r+jp�Bg�{ ���0�%1(���Z�`Q#�Ԏ�n*h���v�B����\\F\n�W�r f\$�93�G4%d�b�:JZ!�,��_��f%2��6s*F���Һ�EQ�q~��`ts�Ҁ���(�`�\r���#�R����R�r���X��:R�)�A*3�\$l�*ν:\"Xl��tbK�-��O>R�-�d��=��\$S�\$�2��}7Sf��[�}\"@�]�[6S|SE_>�q-�@z`�;�0��ƻ��C�*��[���{D��jC\nf�s�P�6'���ȕ QE���N\\%r�o�7o�G+dW4A*��#TqE�f��%�D�Z�3��2.���Rk��z@��@�E�D�`C�V!C��ŕ\0���I�)38��M3�@�3L��ZB�1F@L�h~G�1M���6���4�Xє�}ƞf�ˢIN��34��X�Btd�8\nbtN��Qb;�ܑD��L�\0��\"\n����V��6��]U�cVf���D`�M�6�O4�4sJ��55�5�\\x	�<5[F�ߵy7m�)@SV��Ğ#�x��8 ոы��`�\\`�-�v2���p���+v���U��L�xY.����\0005(�@���ⰵ[U@#�VJuX4�u_�\"JO(Dt�_	5s�^���������5�^�^V��I��\rg&]��\r\"ZCI�6��#��\r��ܓ��]7���q�0��6}o���`u��ab(�X�D�f�M�N)�V�UUF�о��=jSWi�\"\\B1Ğ�E0� �amP��&<�O_�L����.c�1Z*��R\$�h���mv�[v>ݭ�p����(��0�����cP�om\0R��p�&�w+KQ�s6�}5[s�J���2��/���O �V*)�R�.Du33�F\r�;��v4���H�	_!��2��k����+��%�:�_,�eo��F��AJ�O�\"%�\n�k5`z %|�%�Ϋg|��}l�v2n7�~\0�	�YRH��@��r��xN-Jp\0�����f#��@ˀmv�x��\r���2WMO/�\nD��7�}2����VW�W��wɀ7����H�k���]�\$�Mz\\�e�.f�RZ�a�B����Qd�KZ��vt���w4�\0�Z@�	��Bc;�b��>�B�	3m�n\n�o��J3��k�(܍���\"�yG\$:\r�ņ�ݎ��G6�ɲJ��y��Q�\\Q��if�����(�m)/r�\$�J�/�H�]*�����g�ZOD�Ѭ��]1�g22�������f�=HT��]N�&���M\0�[8x�ȮE���8&L�Vm�v����j�ט�F��\\��	���&s�@Q� \\\"�b��	��\rBs�Iw�	�Yɜ�N �7�C/&٫`�\n\n��[k���*A���T�V*UZtz{�.��y�S���#�3�ipzW@yC\nKT��1@|�z#���_CJz(B�,V�(K�_��dO���P�@X��t�Ѕ��c;�WZzW�_٠�\0ފ�CF�xR �	��\n������P�A��&�������,�pfV|@N�\"�\$�[�i����������Z�\0Zd\\\"�|�W`��]��tz�o\$�\0[����u�e����ə�bhU-��,�r �Lk8��֫�V&�al����d���2;	�'-��Jyu��a���\0����a��{s�[9V\0��F��R �VB0S;D�>L4�&�ZHO1�\0�wg��S�tK��R�z���i��+�3�w��z�X�]�(G\$����D+�tչ�(#����oc�:	��Y6�\0��&��	@�	���)��!����w���# t�x�ND�����)��C��FZ�p��a��*F�b�	��ͼ����ģ�����Si/S�!��z�UH*�4�����0�K�-�/���-k`�n�Li�J�~�w�Jn��\"�`�=��V�3Oį8t�>��vo��E.��Rz`��p�P���E\\��ɧ�3L�l�ѥs]T���oV���\n��	*�\r�@7)��D�m�0W�5Ӏ��ǰ�w��b���|	��JV����\"�ur\r�&N0N�B�d��d�8�D���_ͫ�^T��H#]�d�+�v�~�U,�PR%�������x���fA��C��m����͸����c��yŜD)���uH���p�p�^u\0������}�{ѡ�\rg�s�QM�Y�2j�\r�|0\0X��@q���I`��5F�6�N��V@ӔsE�p���#\r�P�T��DeW�ؼ񛭁��z!û��:�DMV(��~X���9�\0��@���40N�ܽ~�Q�[T���e�qSv\"�\"h�\0R-�hZ�d�����F5�P��`�9�D&xs9W֗5Er@o�wkb�1��PO-O�OxlH�D6/ֿ�m�ޠ��3�7T��K�~54�	�p#�I�>YIN\\5���NӃ����M��pr&�G�xM�sq����.F���8�Cs�� h�e5��������*�b�)Sڪ��̭�e�0�-X� {�5|�i�֢a��ȕ6z�޽��/Y���ێM� ƃ� �\nR*8r o� @7�8Bf�z�K�r���A\$˰	p�\0?���d�k�|45}�A����ɶ�W��J�2k Gi\0\"����d���8�\0�>m���� `8�w�7�o4�cGh��Q�(퀨�8@\$<\0p��0���L�eX+�Ja�{��B���h��8�Cy���P2��Ӯ�*�EH�2���DqS�ۘ�p�0�I���k�`��S�\n�:��B�7����{-����`�����6�A�W�ܖ\r�p�W#���?���{\0������cD��[<����f�--�pԌ�*B�]�nW��^��R70\r�+N�GN�\$(\0�#+y�@�@iD(8@\r�h��H�He����zz�{1����h��W1F�Who&aɜ�d6���jw�������`h�{v`RE�\nj���`�ܷ����*���ʸ}�Y��	\rY�H�6�#\0��廆��a�� Q�HEl4�d���p��#�������o�br+_)\r`��!�|dQ�>��=Qʡ��ζ�EOB'�>�P���Ӷ� A\rnK�i�� 	�����	�%<	�o;�S�@�!	�x��:���A�+\\1d\$�jO��7�%�	�/����gu�z*�G�H�5\"8��,�]raq���/�h��#����\$ /tn��8y��-�O���H�b���<�Z�!���1��`�.(uo����|`GːS��BaM	ڂ9ƞ�D@���1�B�tD��ʡ@?o�(H��qC��8E�TcncR��6�N%�rHj��2G\0�a��q �r��z9b>(P���x��<��)�x#�8�誹t���h�2v��Wo2U���t��+=�l#���j�D�	0����&R�c�\$�*̑-Z`��\r��;�|A�p�=1�	1����ƈ�bEv(^�X�P2=\0}�W���G�<���G�����R�#P�Hܮr9	��Y��!�LB���4�NC�Z��IC���MLm��,�f@eY�x�BS(�+��<4Y�)-�\r�z?\$���\"\"�� 6�E�\r)z���@ȑ��r����*���J�윋��%\$�e�J���\0A�\$ڰ/5��B0S���x��I�Q)��<��4YS�&�{��b�+IG=>�\r�PY`Z�D�`��U����F1���4d8X(����C%�`�㜭0�I\$�7W�pǁ,��Ac���&Ԍ�p\$�:�>]�.�VY��\$p� ��]��`�;��e�\0�0�\n��K+�@DL�S��r(on�M\0@9��%�\"�WS�\"���� 䥙�ٍ�ػj�_J-��rʜ���5�\\�2�5>Ze\"0��%9y��^�WMax&a)D�L���2Q����t?�=,�/o�f�3I�J�\$\r;���7�}�\r�W�@�Ұ�M|\r�Y���]5���\\*s:��FV!���kن�R���L3L�	��52�M�sb�\$����7�\0l�y���&� 9�|m!��0J��4��TSd���G���nK�V:l�D'/��:Zs��\n��y�%��i����,@ҲL��j1<��3Ĩ�D2/;��'Pݻ����`����qKȰ�f�I�L� Dݬ�4�3 ��OH�J�	q�&�����X��!��r)F�Xx���^QwOP��h��՞-_�>�a����(	��x%��K�b�<�E�j7�������hHt�`�.r�P���x��\"{\0006CVQE�&��>�ޅ�w����e'?B�9x�>:\"�73���xT\0e�����j	��[t�Ҝ\"�(\\K�e�z�r����e> ���\0002�hʇ��X�a<�JtU�z`�達?��#�����2-��4hFY|C��\"M�yƔKd ���E�7���+(U�ʖX�� /D���)�\"����بމjoh�Fz4�t���D׌�G��RZ�ć�ȿ\0�FV4Q�6v�b�i=G�;Ϭ�k�d+\n>�E��\0�2f{����!J��Q��J�ؘ9��(2�#\\Z��,��Qܥ�3?8`�	bwR6��\n*�㋀�ƒ�(t��L*�S�d�\0x�)�(�*�wH]7O�N�v(Гdg�q	\nLp��L�N��H@�1����M �		n��z���e4!!	��'槝-t����AQP���L,����7��\\�i����^�\$�,�|�Z��(S9����\n* +��T�D�z?(T�>��L��æ��R����\$�zдi̼W�ͨ�Ds�{)�@�����	v�P��g�qIVҨ����\n )�!�8|\$pZ�*�!7A����N��j�NW����U���Q���)�eF�UA�S�x\0[N���2���X :S�T�~�S*T4	�3��]9�F���]:�KUg;��*Ay�a��1j|8Ϋ����I�MR��Vh7uU����r,�h�%<q�R@N9�ާ�k�	�B|�����8��r������DР@\"�ɋ�z\r������O�_���Q�\0\0���|�]�f�\nz�����UeH�Ą/k+�TF?��*03�!�\0��I���t	f\0(S��U��ZA�F��1\0��k�]��WZN�Q��܂���%��x1���'��!-,�Ƕvzg��#�Gh�;f�PH�9Bj�u�\n�A�VR����1K+�MN!��Sμ��Y��vdZ\\,���g٨�����\"}W��Yɵ�t�P���g�,�����	\0b�-�hB/@�̎�/�M���J���Y\0����)\n��I�?v�	��Ȕ1��\$�(�w\r+�n ���s�s�QfQ�O�P�.D���bV\0-�J<�i;[���=#���n,j?)�\"���lYL.����A::������BxOF7����`���d��}�}=�i)@к��\$ q˷(y%��huzb2�3Ƨ��.�-h�oO����\0`���VZ��&y�t9C���鋭Z��ґ�Z!�X�U����.k���V#8�G�}�Q���u8cΫt�bE>�v��{@{QP]<�ary��j\\��\$j�x�nc6k�;qs�T���K�����jJ���n\\C��{���`g���6�5���Rk�t������s�|@�_0΅5:B�3����rѡ�&�㴸�\0����&�׈�����ԡ���SXʕ�G�m�ʶWr,j�q\0\$޺sW�P�.A\n4�9(u��.���l�V�Ju�Ԍ�+�A�uC�>hl6��2���G�e���N���n�=�'���~��Þ����PҀ�%0z�u��r�\0��9uE�s\"���\\�ט����^���(3ՑS%<+�9��Ծ����\0���~'̞�֓<+�,i�:��@��N���\$�o������� �]�������Z�!��]�n,��x��>_�f��W\0006��%�}I�\nh߀w�����ǃ -��H@_�Vi�����{���R��^�۔}5�b,!5���H��p/��k<��<�jh|i��k��hLv݄\n�`�[���WC6��z\n�g��r��u=��!zCţ����e#��nj��\0`^;=E�*@�y�% ��LQe����2�A�1,��C�ix�t����G�]q�O(����\n�V9dr�D'5@x\$�r6��;\"ǣ���7�\0M0ņH_#�c�pn>��<aa�q@g�2��lm-��������8��?8��7p����>��ji���N�\$#E/�0��s\n��B\r�*��z���oyn[Ι�� 6�a����g8�qC��⼜�I��rNF�ȫ�1��70�����/i(�B�0����Z��(��+S�J�,���91/Y+jxӱF���A��k�f�Jee\r�Cͳrz��m���h@9��O�� ؝���GK�Ad���OH���=���<&`��K�PA�!WO;-�X�L��m��Kz�7-e[u��p�q���o/�`�C����KX�f�i���Y7=�M�/�F�R�۔T�d��Y\"=`�1�k�1Տh�\r����f@N��z�(@������	h�\0�����I�}PJKr���pR`x������fo���(A��[��19�(&jo<��I@p	@��������,y�	nIs�^Ўѫ:Y��vc���؏9q.C��8�bW��V?��҅�9�\$u�@5#S(4Y���K���6�!��N6<��|v1���3ʊ:��!����`��M��l����f`�Z��J=��GX�Y)_l�А�T�)P��`�%��:�!Z\"lYS�Uؤ(��Y1Z�니rv)F`�K~=Y>���S���c����!l���D����BrF\$��RA:�\\�P�4�V�R6<�O�S�_BCS+����'V��2T#Lc�F�NBD%�G�W�nR�S����I��\n'k�0���O��Ў����8rݯAS�?��xm��yv���a�b��Ͱ�,��ЅA������]pJ\\\\�Xi����Eu��B)�����Z@Ώ \"��gg0{��n��'APR��٨v�~�0R�w쀱\"�������H�J���Ζ�\\�\r}i?��Ғ:��2���g��{I�3)��B��͙Z�s��`.�#2�vt��X�IGU>`)�%���(|�f<Κ_�ޯ���_G�<��_ ˟������[:�6G8��l�#J(��JC���`���wF�w\"b�!,��!�r�@�K(���\n@AsV��S�ֹ�4�_\ns٠eڋj��)&�3�{��k���Q���G�c��X^�L{�C\n�m����A��D��1O?(��(�����2\"UL��+#o��@���X�\0�٭���^n_p�eQ˙X}%��*��e�m�{�GN��Xl�q�]R\\Z�v!�) ���xd΀,�cK��鮇�m���I~�����K�{+��Gݥ�=@Q��,1!aEOc��#6<u��rB�\n�Ȳ��dH�t����	�{C�<x3���H��1��K�wB�\0��u����'ӆQ�^���򕥂�i�rRv�Vɷ�lS�.O)����[��xS�t���c)���k�B��+��v���B��w�.�wC���2���2d�.H��p+a\\H��[�\$}nNN7��H�.�S\r�ȒT���w�	*H�g\\��\$�,�:KBOx��>����5�����Ӷ����u2��n���`��Yq�D���xwMB�n�2>���G�ڄ����YaK�w(2`����w����1m�-:�&LD8�U��8l��\\<���	��z�a����:,��K'�%7:����M����U[���*;K���j�;/wG���\n���^�eV'��,��;��B6�G�1��OKW����(i�X\np��Cکc6�^��㷀=�^ûcQ��Rp`\$	�D(\0D�>{��ET�c��I\r{����\$o�R	�ZZ�4*��??�+j���n��Q`����X�3�	\$���M�\n׉w�\"d�W���~@�'�I�᭫�0+-��w�����y�6�vȽ'�Ԇ:Y)Y0\0�*)?'��Ǟv����fI�\n���z�9�.�b��!�c�E�[��F麙ks�}��Bv�g�5�V���,)J\$��j�Z�J�\$�Y��ח9�\0�\n����.^J��ڋ�b��mI0:g��������˗ATP�I�]~!��;D�����	�z��<P�Q>�m���`��?%Y��T\n\0D\0�\0'���H@0`�<׭�10�(�m�-��ɞ7A\0�~�~ꁡĤ?t�hє.w�%)0	#c����\"�c����jfW��\0\0p��C���kC��8��85+i:��[�8�b��l�[\"����5S�y\0�����*�Q��6V�s�9��7!�;\"��c�)�O�Q,��Ա��\r�7�,*�0�aQ�u?�_C|�������R(o(��<j(��Tv��\r|_\"�3��m��S7D�!׸�h�|���(�&�@:��	\"-ގ��&Mu;�,�bк=p�>A6ɭ���7���- WW9�O,�o'�v2�<�3\0���h��@`� 3TX�Ϛ|�\"FC_��~x����`��'f�Q-4�����/�`'���=A�\$>��`P��_G(���E���&/J�I�v�'�m餧zpޞFo�	�/[��i�؋�G*���y�(���<���7q�Y�.�眪��B���\r�l�r\nUnƧ��T>�������	�Q���_�|����K��8�ډ�e��_��xz�x�L���p14��d����U#4t�K���\$�!����p�w�����Zx���_�����i5T?}��C�{�����h/Gzj\$.B�Ҩ�=#�Ϗ|���*����I���w/��a�x`*��*���]����>a?'}FJS���ԖA0��'������ʟ�0:63���л��n'��U/�r�|=slb0��\0W��rB�ʤ���@T��~\$����H�����	��D\\���-���(��ᩖB��M���z+�%�(��i��㹃�I���5/�.y/���\$�{Q}p�ܻdI�\\�Վ�B�\0V0�B�9�{T\$n�8\$Z�e�Pĳ���%9�&���V��b�x}g\"%h���*ٸvOw�˾�/�o�L,���=��V��5Bg� ϶�3��>�~�`\nxi�\"��v@������nף�ϳyac�G�'%[��4`n��47!5�ހr����ɉ��>z�(Y�t��0���V���P�ZXT`2�~Cl���[o�n�t8jB\0d�\0000��V��g�����@V!�h\0006d<���=[�W�����f�@pb��a��ټ�s;���G<�~a��?�N�L����\"(���?�%�x#�7�|S��O�Ɠ)�B4��+��*�!��)6#�+?'���(X�����JO\0��");
    } else {
        header("Content-Type: image/gif");
        switch ($_GET["file"]) {
            case "plus.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0!�����M��*)�o��) q��e���#��L�\0;";
                break;
            case "cross.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0#�����#\na�Fo~y�.�_wa��1��J�G�L�6]\0\0;";
                break;
            case "up.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����MQN\n�}��a8�y�aŶ�\0��\0;";
                break;
            case "down.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����M��*)�[W�\\��L&ٜƶ�\0��\0;";
                break;
            case "arrow.gif":
                echo "GIF89a\0\n\0�\0\0������!�\0\0\0,\0\0\0\0\0\n\0\0�i������Ӳ޻\0\0;";
                break;
        }
    }
    exit;
}

if (isset($_GET["script"]) && $_GET["script"] == "version") {
    $r = file_open_lock(get_temp_dir() . "/adminer.version");
    if ($r) {
        file_write_unlock($r, serialize([
            "signature" => $_POST["signature"] ?? '',
            "version" => $_POST["version"] ?? ''
        ]));
    }
    exit;
}

global $c, $g, $l, $Ib, $Pb, $Zb, $m, $Cc, $Hc, $ba, $Yc, $z, $a, $qd, $le, $Qe, $fg, $Mc, $T, $Ng, $Tg, $ah, $fa;

if (!isset($_SERVER["REQUEST_URI"])) {
    $_SERVER["REQUEST_URI"] = $_SERVER["ORIG_PATH_INFO"] ?? '';
}
if (!strpos($_SERVER["REQUEST_URI"] ?? '', '?') && ($_SERVER["QUERY_STRING"] ?? '') !== "") {
    $_SERVER["REQUEST_URI"] .= "?$_SERVER[QUERY_STRING]";
}
if (isset($_SERVER["HTTP_X_FORWARDED_PREFIX"])) {
    $_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_FORWARDED_PREFIX"] . $_SERVER["REQUEST_URI"];
}

$ba = (isset($_SERVER["HTTPS"]) && strcasecmp($_SERVER["HTTPS"], "off")) || ini_bool("session.cookie_secure");

@ini_set("session.use_trans_sid", "0");

if (!defined("SID")) {
    session_cache_limiter("");
    session_name("adminer_sid");
    $Ge = [0, preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"] ?? ''), "", $ba];
    if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
        $Ge[] = true;
    }
    call_user_func_array('session_set_cookie_params', $Ge);
    session_start();
}

remove_slashes([&$_GET, &$_POST, &$_COOKIE], $tc);

if (get_magic_quotes_runtime()) {
    set_magic_quotes_runtime(0);
}

@set_time_limit(0);
@ini_set("zend.ze1_compatibility_mode", "0");
@ini_set("precision", "15");

$qd = [
    'en' => 'English',
    'ar' => 'العربية',
    'bg' => 'Български',
    'bn' => 'বাংলা',
    'bs' => 'Bosanski',
    'ca' => 'Català',
    'cs' => 'Čeština',
    'da' => 'Dansk',
    'de' => 'Deutsch',
    'el' => 'Ελληνικά',
    'es' => 'Español',
    'et' => 'Eesti',
    'fa' => 'فارسی',
    'fi' => 'Suomi',
    'fr' => 'Français',
    'gl' => 'Galego',
    'he' => 'עברית',
    'hu' => 'Magyar',
    'id' => 'Bahasa Indonesia',
    'it' => 'Italiano',
    'ja' => '日本語',
    'ko' => '한국어',
    'lt' => 'Lietuvių',
    'ms' => 'Bahasa Melayu',
    'nl' => 'Nederlands',
    'no' => 'Norsk',
    'pl' => 'Polski',
    'pt' => 'Português',
    'pt-br' => 'Português (Brazil)',
    'ro' => 'Limba Română',
    'ru' => 'Русский',
    'sk' => 'Slovenčina',
    'sl' => 'Slovenski',
    'sr' => 'Српски',
    'ta' => 'த‌மிழ்',
    'th' => 'ภาษาไทย',
    'tr' => 'Türkçe',
    'uk' => 'Українська',
    'vi' => 'Tiếng Việt',
    'zh' => '简体中文',
    'zh-tw' => '繁體中文',
];

function get_lang() {
    global $a;
    return $a;
}

function lang($w, $ce = null) {
    if (is_string($w)) {
        $Te = array_search($w, get_translations("en"));
        if ($Te !== false) {
            $w = $Te;
        }
    }
    global $a, $Ng;
    $Mg = ($Ng[$w] ?? $w);
    if (is_array($Mg)) {
        $Te = ($ce == 1 ? 0 : 
               ($a == 'cs' || $a == 'sk' ? ($ce && $ce < 5 ? 1 : 2) : 
               ($a == 'fr' ? (!$ce ? 0 : 1) : 
               ($a == 'pl' ? ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2) : 
               ($a == 'sl' ? ($ce % 100 == 1 ? 0 : ($ce % 100 == 2 ? 1 : ($ce % 100 == 3 || $ce % 100 == 4 ? 2 : 3))) : 
               ($a == 'lt' ? ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : ($ce % 10 > 1 && $ce / 10 % 10 != 1 ? 1 : 2)) : 
               ($a == 'bs' || $a == 'ru' || $a == 'sr' || $a == 'uk' ? 
                   ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : 
                    ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2)) : 1)))))));
        $Mg = $Mg[$Te];
    }
    $ta = func_get_args();
    array_shift($ta);
    $zc = str_replace("%d", "%s", $Mg);
    if ($zc != $Mg) {
        $ta[0] = format_number((float)$ce);
    }
    return vsprintf($zc, $ta);
}

function switch_lang(): void {
    global $a, $qd;
    echo "<form action='' method='post'>\n<div id='lang'>",
         lang(19) . ": " . html_select("lang", $qd, $a, "this.form.submit();"),
         " <input type='submit' value='" . lang(20) . "' class='hidden'>\n",
         "<input type='hidden' name='token' value='" . get_token() . "'>\n";
    echo "</div>\n</form>\n";
}

if (isset($_POST["lang"]) && verify_token()) {
    cookie("adminer_lang", $_POST["lang"]);
    $_SESSION["lang"] = $_POST["lang"];
    $_SESSION["translations"] = [];
    redirect(remove_from_uri());
}

$a = "en";
if (isset($qd[$_COOKIE["adminer_lang"] ?? ''])) {
    cookie("adminer_lang", (string)$_COOKIE["adminer_lang"]);
    $a = $_COOKIE["adminer_lang"];
} elseif (isset($qd[$_SESSION["lang"] ?? ''])) {
    $a = $_SESSION["lang"];
} else {
    $ka = [];
    preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~', str_replace("_", "-", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? '')), $Ed, PREG_SET_ORDER);
    foreach ($Ed as $D) {
        $ka[$D[1]] = (float)($D[3] ?? 1);
    }
    arsort($ka);
    foreach ($ka as $_ => $H) {
        if (isset($qd[$_])) {
            $a = $_;
            break;
        }
        $_ = preg_replace('~-.*~', '', $_);
        if (!isset($ka[$_]) && isset($qd[$_])) {
            $a = $_;
            break;
        }
    }
}

$Ng = $_SESSION["translations"] ?? [];
if (($_SESSION["translations_version"] ?? 0) != 2138479313) {
    $Ng = [];
    $_SESSION["translations_version"] = 2138479313;
}

function get_translations(string $pd): string {
    switch ($pd) {
        case "en":
            return "A9D�y�@s:�G��(�ff�����	��:�S���a2\"1�..L'�I��m�#�s,�K��OP#I�@%9��i4�o2ύ�����,9�%�P�b2��a��r\n2�NC�(�r4��1C`(�:Eb�9A�i:�&㙔�y��F���Y��\r�\n� 8Z�S=\$A����`�=�܌���0�\n��dF�	��n:Zΰ)��Q���mw����O��mfpQ�΂��q��a�į�#q��w7S�X3���=�O��ztR-�<����i���gKG4�n����r&r�\$-��Ӊ�����KX�9,�8�7�o��)�*���/�h��/Ȥ\n�9��8�Ⳉ�E\r�P�/�k��)��\\# ڵ����)jj8:�0�c�9�i}�QX@;�B#�I�\0x����C@�:�t����\$�~���8^�ㄵ�C ^(�ڳ��p̳�M�^�|�8�(Ʀ�k�Q+�;�:�hKN ����2c(�T1�����0@�B��78o�J��C�:��rξ��6%�x�<�\r=�6�m�p:��ƀ٫ˌ3#�CR6#N)�4�#�u&�/���3�#;9tCX�4N`�;���#C\"�%5����£�\"�h�z7;_q�CcB�����\n\"`@�Y��d���MTTR}W����y�#!�/�+|�QFN��yl@�2�J��_�(�\"��~b��h��(e �/���P�lB\r�Cx�3\r��P&E��*\r��d7(��NIQ�makw.�Iܵ���{9Z\r�l׶ԄI2^߉Fۛ/n���om���/c��4�\"�)̸�5��pAp5����Qjׯ�6��p��P*1n�}C�c�������K�s�Tr�1L�\0D(��b�єu!�\nv�4�#\$�������pܔ%P�G=Ds�B���k�x��1̳<�5ͳ|�N����i�5��@���E֫�ǈ!��\\�U�5d�&΍�L5��\"\$h��i�<��2;7N�Q�J��_(*�!!���F��;qE�M*�bȐ{(�4��``E��ߞ� ���h�t��\0����E���\nI\$NX�\"P��q�\\����dȹ�\$1L��LI��/A��5�[̈́E\r���\\Lq�G�j<BjII������j��\0P\"�H��7��\n�\"�rC���af���fTI!�&��C����8�d�=Wf�U�i�!<)�FL]R&�p�dRz��f2�Ӟ��INpr]�l���cICVcg0̑�@���\nlfH�&H�0T�W�z^%� �d�5ZNˠB�(�)���xNT(@�(\n� �\"P�h�/Xk�>�f�G�y�����c]��s�JCy�#���s��R�1��>����K��i����T~�r>0i=.��*�h�9��p�`��@�(+ ��yϹ�3����5��af:�p�&b�Y=�(��[�j��(3���-��Vt���po�b��S�R�I�-\\��J�´�T�P�G����d��ϳ��AC�J���2��D_��d&��3��YB�h]g�ؠteP�PF(��U��D���/!D�p�����4��Rў�HwV�@��@ ���H���o�d�q�\"����/!����`�\"�\$&��l�aORM������U��\$MmIE@\\o�~m81�@A��E�0��)8�`X�g�ny�`3:J���v�\n�L.�~)�������,�h���}�!8#������#��ݑxJ����Hhp��������%˖�A���㸥�&d\r�N�#�1g,�M���>�m	&t��}R��9�=���mI#�WBg�ӟ�I�7e�XF)�4J\r�3'��/�4��TQ�O�0jL�?�t��B�sQ��~kem��_c���!t��|Yu�x�h��׫����>����Sq�UK�r������h��\r�d0�NIn�Ɯ��]��F��k''�TT�5=I 4�v�Ϥbُ[��-��6�zy���y���&D�߅3�6\r}�\$)]���{D�\r\\�p�O���\n�7N�ӓ��[^Mm�EI�K��[Kl�xƪҶ����rhmaM����aQ�i�sˬ����b�Є��-�3\$>�W�Q��y�*b_K�G�RG�޲偗��+W�J�v�F������ʮ��iU�5�wV�݄����sT�{0�J��\0���<*�~:����r���}d�^��/���УLñ|q���J�K{r��)3������9Hd�q���n/�9��%��yu��\$\\�9��\"�PdSލ�6��`�S2��G�̠�BN�d����#��������=;����d���O�l>�H���\r�BZ4�4/ �7���l�\0o��\0��Q�M��`	D_\$N��b��Yɔ�>4�z�d�� �\$�.���2\n�Ic,�\r��4��<7��o2.\"�	pM���. �PPW�`\r����S�B���\r�V�\0�`��\"F��0l��.��m\"�(b���`�F�\n���Z�5��9�P��\"%��.;��0�H��\"c�3����Uf�	��\r0������6�g�Т3җe�[�X���N̮�@`DB: �DB�\n��'\n�[B�԰Z��RSe�QQM��MH���R�i\"Պ����0vqi�2�d���\nfXf�^��.�'��*�����+��h��@	�L�\r�ڱ�aE�\0� #N�����*%�S�vG�1�e�\nOG�l`��c����=H�Tn�߱��@�BfX�F�\"�-K�2������d\$�\"�\"�2#\"i��\"�\\";
        case "ar":
            return "�C�P���l*�\r�,&\n�A����(J.��0Se\\�\r��b�@�0�,\nQ,l)���µ���A��j_1�C�M��e��S�\ng@�Og����X�DM�)��0��cA��n8�e*y#au4�� �Ir*;rS�U�dJ	}���*z�U�@��X;ai1l(n������[�y�d�u'c(��oF����e3�Nb���p2N�S��ӳ:LZ�z�P�\\b��u�.�[�Q`u	!��Jy��&2��(gT��SњM�x�5g5�K�K�¦�����0ʀ(�7\rm8�7(�9\r��f\"7�^��pL\n7A�*�BP��<7cp�4����Y�+dHB&���O��̤��\\�<i���H��2�lk4�����ﲠƗ\ns W���HBƯ��(�z �>����%�t�\$(�R�\n�v�-��������R���0ӣ��et�@2�� ��k� ��4�x荶��I�#��C�X@0ѭӄ0�m(�4���0�ԃ����`@T@�2���D4���9�Ax^;؁p�D�pT3��(��m^9�xD��lҽC46�Q\0��|��%��[F��ڏ����t�wk��j�P���Ӭ� ��m~�s���Pi�����n�E���9\r�PΎ�\$ؠ#�����r��8#��:�Yc���(r�\"W�6Rc��6�+�)/w�I(J����'	j?��ɩ�U�H��E*��߂]Z\r�~�F�d�i�	�[�r�(�}���B6n66��61�#s�-��p@)�\"bԇ����d��l�1\\��]�������1K���ű�\"�J\\�n�����S_7k����!��ٖN;�^��qj��Z��1̃Ň��W4O=7x�\" ��&��B9�`�4�J7��0�E��µɺ��ț�B���\\p����MS�6n\r�x��u��9}c�OP �,d(��M�(`���r,�\0C\naH#B��#\rO�9E�N\nS�-�����L��il]I��B���F0��9��\0�Q�Y��Ɨ��)�@�o'اC8 Q+ ƈP�dQ��Ыur�Ø\"�9\nF,�1Ow��C��PRH��\\C:5�K�/Ee��'Xn�\n�&a5R���C��V<\0ҭ�\$���\\+�x���X��c,�܁�r�Y�=\r��>�V��	!�8�ڳ��`�B�URƍ�!�n)0���L�]ԈRR~ܚLoGP�(HBI�T\r�L��B8ln���p�e;!�?)G� _�p~��*szm&Y2�9*K;e<����[|*���æ@�'�,E�(8�����0d5�Ar�?J�YMD�!�.����&���`XҖz�ߩ14�xw�=㺕�LӡĔ�Bl*I�0e��XB��ie���2�Qϑ�Gt�ϲ�QΡ'���0�F�ɟQ���u&�Ct�7��[�zԘfA�%IeoVt�~��tRU�li�\rd���`�T!dE����Ljj;K\r4���PQ!6i.R�е��㈆����\$�B��ס���5\$�Н�@��@\$;V��6��c��@�LIo�0j<\rj��P()��\0�\"��U����iQ�8�Ī���NC�II�{�6��p \n�@\"�@U�\"���yۼ4O���+�bˉ�0��(6JbM�^G([R���K.̎��|\\�U{\$tCY��˚\$/��[*(4���\ngi���v,�_f�4st����D���ĳ���`7v�]�9�cLJ���:����QC���n��ŧ'��K�A�JI���XJ�9rĝ��ܣ@�� ���5��a�w(4���LNsk�b\r@�5��M@��L4��}Ee\$g�L2��1H\"%\0*>G����35��;�����	Id4R;�R�X6����}T�����P@����02ac��]b�vqJ\r}��=pN�9؉L�;U�-�\",P�)�X�\n)>�\\�>6��R�*y�A��*2��sG�-䤕8T\n�!��AY�0iRZ�)@�H�܉#�8K�B(��O��\"Tx \"�k�7t^�{#%'�XBg��K�VP�O����~c�� �S�&^Y|:e��B�y8�U���\\6G���ᙃ���H�HD>&��{izo:����L�@e���\0��TR�A܎#������ĩ1�I���01�PBW��'�?����ڗ;��%����(q�\r�*]��Y��Z�U(.�敱VA�9B��\niQ	��%\rϐ�T���p'U�Gx��!�-'��ư�c�=f���0�{��Dݬ;�>����#��|�13�{���G��������{��`�,�x�����\\;^��-� \n�\"���t�W��ǔ�écO����k��4H�O��O�_�Z�B��mL�,*+\"V�MX�\"���@\0P:�� @RJ�I���O��J]�H^LV��D\$)�qd�c�>��H�0DuJ�̀�PAxy	�\0f\n�'�d��Ķ�'|����D�\"���VNJ]�P��>L��b��N�ݭ|�C�װ�+K�#�q\0�t'���0�����	p���\rc\r�R��o��	��-d1>#��0���_#���8�fl�HVB�q\n!c��H�\n�#�����侌�Imf����pd��T����(a�q�+�ڢ@x��Bj����O�l�T?/:֍l;����qp\$�Bc����\"��y��`P�w�������60���Ѥ�\"&p��\\.M����O��d�O����s����E�\"aG�:m��E�;FH�ڌq�2/��������·'f ���r	�{�!\0���q�#��������#�I\"�Dɰ�dpf@��0��\\�\$��i�!�]�p+���&���5'f�(q�\$�'�Ŀ\$nr�i��(Km�!�u\0���Ļ\"��L�+��.GlR:��Nj�fT������f�w�P��M&l~e,\r��G��&�(i&Hs��\r<\"nL�c ��VJ�Z)�c*(`�`�{`�\rd0@�@gFx7��\r��\r �}eP&`�����ж��@��\n���pBh�4����b����mb:c�\$o>���aϠ��P	�I4��aNq\$��nJ�@�2\r�\0E!L<��AK/ġ�f	�޶��V�B8.�=�t=��%*n�+N-��!�/�Xj��&Brxg-��\$��&l\"-0C	�J/�AT#����B �B�f4CI6\n�\r�����)�L2��Cg��]\"���&�.�+E�D!�l��ϼ\$&�hAT)�/\0�74v{�~qq܅s�8 \n�2��\r���2�y�xE�&�j9C��\$�@����(̅-���P�b�tK�'0\n ��d4pOdp&\r�d �	\0t	��@�\n`";
        default:
            return "";
            
            
            
            
   <?php
/**
 * File Name: adminer.php
 * PHP Version: 8.3
 * Description: أداة إدارة قواعد البيانات Adminer - نسخة مطورة ومتوافقة مع PHP 8.3
 * /

declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

$tc = !preg_match('~^(unsafe_raw)?$~', ini_get("filter.default"));
if ($tc || ini_get("filter.default_flags")) {
    foreach (['_GET', '_POST', '_COOKIE', '_SERVER'] as $X) {
        $Yg = filter_input_array(constant("INPUT$X"), FILTER_UNSAFE_RAW);
        if ($Yg) {
            $$X = $Yg;
        }
    }
}

if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding("8bit");
}

function connection() {
    global $g;
    return $g;
}

function adminer() {
    global $c;
    return $c;
}

function version() {
    global $fa;
    return $fa;
}

function idf_unescape(string $w): string {
    $rd = substr($w, -1);
    return str_replace($rd . $rd, $rd, substr($w, 1, -1));
}

function escape_string(string $X): string {
    return substr(q($X), 1, -1);
}

function number(string $X): string {
    return preg_replace('~[^0-9]+~', '', $X);
}

function number_type(): string {
    return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';
}

function remove_slashes(array &$ef, bool $tc = false): void {
    if (get_magic_quotes_gpc()) {
        while (list($_, $X) = each($ef)) {
            foreach ($X as $jd => $W) {
                unset($ef[$_][$jd]);
                if (is_array($W)) {
                    $ef[$_][stripslashes((string)$jd)] = $W;
                    $ef[] = &$ef[$_][stripslashes((string)$jd)];
                } else {
                    $ef[$_][stripslashes((string)$jd)] = ($tc ? $W : stripslashes((string)$W));
                }
            }
        }
    }
}

function bracket_escape(string $w, bool $_a = false): string {
    static $Lg = [':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4'];
    return strtr($w, ($_a ? array_flip($Lg) : $Lg));
}

function min_version(string $mh, string $Dd = "", $h = null): bool {
    global $g;
    if (!$h) {
        $h = $g;
    }
    $Mf = $h->server_info;
    if ($Dd && preg_match('~([\d.]+)-MariaDB~', $Mf, $D)) {
        $Mf = $D[1];
        $mh = $Dd;
    }
    return (version_compare($Mf, $mh) >= 0);
}

function charset($g): string {
    return (min_version("5.5.3", "10.0", $g) ? "utf8mb4" : "utf8");
}

function script(string $Uf, string $Kg = "\n"): string {
    return "<script" . nonce() . ">$Uf</script>$Kg";
}

function script_src(string $dh): string {
    return "<script src='" . h($dh) . "'" . nonce() . "></script>\n";
}

function nonce(): string {
    return ' nonce="' . get_nonce() . '"';
}

function target_blank(): string {
    return ' target="_blank" rel="noreferrer noopener"';
}

function h(string $eg): string {
    return str_replace("\0", "&#0;", htmlspecialchars($eg, ENT_QUOTES | ENT_HTML5, 'utf-8'));
}

function nl_br(string $eg): string {
    return str_replace("\n", "<br>", $eg);
}

function checkbox(string $F, $Y, bool $Na, string $nd = "", string $ne = "", string $Ra = "", string $od = ""): string {
    $K = "<input type='checkbox' name='" . h($F) . "' value='" . h((string)$Y) . "'" . ($Na ? " checked" : "") . ($od ? " aria-labelledby='$od'" : "") . ">" . ($ne ? script("qsl('input').onclick = function () { $ne };", "") : "");
    return ($nd !== "" || $Ra !== "" ? "<label" . ($Ra ? " class='$Ra'" : "") . ">$K" . h($nd) . "</label>" : $K);
}

function optionlist(array $re, $Hf = null, bool $gh = false): string {
    $K = "";
    foreach ($re as $jd => $W) {
        $se = [$jd => $W];
        if (is_array($W)) {
            $K .= '<optgroup label="' . h((string)$jd) . '">';
            $se = $W;
        }
        foreach ($se as $_ => $X) {
            $K .= '<option' . ($gh || is_string($_) ? ' value="' . h((string)$_) . '"' : '') . 
                   (($gh || is_string($_) ? (string)$_ : (string)$X) === (string)$Hf ? ' selected' : '') . '>' . 
                   h((string)$X);
        }
        if (is_array($W)) {
            $K .= '</optgroup>';
        }
    }
    return $K;
}

function html_select(string $F, array $re, $Y = "", $me = true, string $od = ""): string {
    if ($me) {
        return "<select name='" . h($F) . "'" . ($od ? " aria-labelledby='$od'" : "") . ">" . 
               optionlist($re, $Y) . "</select>" . 
               (is_string($me) ? script("qsl('select').onchange = function () { $me };", "") : "");
    }
    $K = "";
    foreach ($re as $_ => $X) {
        $K .= "<label><input type='radio' name='" . h($F) . "' value='" . h((string)$_) . "'" . 
               ((string)$_ === (string)$Y ? " checked" : "") . ">" . h((string)$X) . "</label>";
    }
    return $K;
}

function select_input(string $wa, array $re, $Y = "", string $me = "", string $Re = ""): string {
    $tg = ($re ? "select" : "input");
    return "<$tg$wa" . ($re ? "><option value=''>$Re" . optionlist($re, $Y, true) . "</select>" : " size='10' value='" . h((string)$Y) . "' placeholder='$Re'>") . 
           ($me ? script("qsl('$tg').onchange = $me;", "") : "");
}

function confirm(string $E = "", string $If = "qsl('input')"): string {
    return script("$If.onclick = function () { return confirm('" . ($E ? js_escape($E) : lang(0)) . "'); };", "");
}

function print_fieldset(string $v, string $wd, bool $ph = false): void {
    echo "<fieldset><legend>",
         "<a href='#fieldset-$v'>$wd</a>",
         script("qsl('a').onclick = partial(toggle, 'fieldset-$v');", ""),
         "</legend>",
         "<div id='fieldset-$v'" . ($ph ? "" : " class='hidden'") . ">\n";
}

function bold(bool $Ga, string $Ra = ""): string {
    return ($Ga ? " class='active $Ra'" : ($Ra ? " class='$Ra'" : ""));
}

function odd(string $K = ' class="odd"'): string {
    static $u = 0;
    if (!$K) {
        $u = -1;
    }
    return ($u++ % 2 ? $K : '');
}

function js_escape(string $eg): string {
    return addcslashes($eg, "\r\n'\\/");
}

function json_row(string $_, $X = null): void {
    static $uc = true;
    if ($uc) {
        echo "{";
    }
    if ($_ !== "") {
        echo ($uc ? "" : ",") . "\n\t\"" . addcslashes($_, "\r\n\t\"\\/") . '": ' . 
             ($X !== null ? '"' . addcslashes((string)$X, "\r\n\"\\/") . '"' : 'null');
        $uc = false;
    } else {
        echo "\n}\n";
        $uc = true;
    }
}

function ini_bool(string $Xc): bool {
    $X = ini_get($Xc);
    return (preg_match('~^(on|true|yes)$~i', $X) || (int)$X);
}

function sid(): bool {
    static $K;
    if ($K === null) {
        $K = (SID && !($_COOKIE && ini_bool("session.use_cookies")));
    }
    return $K;
}

function set_password(string $lh, string $O, string $V, $Ne): void {
    $_SESSION["pwds"][$lh][$O][$V] = ($_COOKIE["adminer_key"] && is_string($Ne) ? 
                                      [encrypt_string($Ne, $_COOKIE["adminer_key"])] : $Ne);
}

function get_password() {
    $K = get_session("pwds");
    if (is_array($K)) {
        $K = ($_COOKIE["adminer_key"] ? decrypt_string($K[0], $_COOKIE["adminer_key"]) : false);
    }
    return $K;
}

function q(string $eg): string {
    global $g;
    return $g->quote($eg);
}

function get_vals(string $I, int $d = 0): array {
    global $g;
    $K = [];
    $J = $g->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            $K[] = $L[$d];
        }
    }
    return $K;
}

function get_key_vals(string $I, $h = null, bool $Pf = true): array {
    global $g;
    if (!is_object($h)) {
        $h = $g;
    }
    $K = [];
    $J = $h->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            if ($Pf) {
                $K[$L[0]] = $L[1] ?? '';
            } else {
                $K[] = $L[0];
            }
        }
    }
    return $K;
}

function get_rows(string $I, $h = null, string $m = "<p class='error'>"): array {
    global $g;
    $eb = (is_object($h) ? $h : $g);
    $K = [];
    $J = $eb->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_assoc()) {
            $K[] = $L;
        }
    } elseif (!$J && !is_object($h) && $m && defined("PAGE_HEADER")) {
        echo $m . error() . "\n";
    }
    return $K;
}

function unique_array(array $L, array $y): ?array {
    foreach ($y as $x) {
        if (preg_match("~PRIMARY|UNIQUE~", $x["type"] ?? '')) {
            $K = [];
            foreach ($x["columns"] as $_) {
                if (!isset($L[$_])) {
                    continue 2;
                }
                $K[$_] = $L[$_];
            }
            return $K;
        }
    }
    return null;
}

function escape_key(string $_): string {
    if (preg_match('(^([\w(]+)(' . str_replace("_", ".*", preg_quote(idf_escape("_"))) . ')([ \w)]+)$)', $_, $D)) {
        return $D[1] . idf_escape(idf_unescape($D[2])) . $D[3];
    }
    return idf_escape($_);
}

function where(array $Z, array $o = []): string {
    global $g, $z;
    $K = [];
    foreach ((array)($Z["where"] ?? []) as $_ => $X) {
        $_ = bracket_escape((string)$_, 1);
        $d = escape_key($_);
        $K[] = $d . ($z == "sql" && preg_match('~^[0-9]*\.[0-9]*$~', (string)$X) ? 
               " LIKE " . q(addcslashes((string)$X, "%_\\")) : 
               ($z == "mssql" ? " LIKE " . q(preg_replace('~[_%[]~', '[\0]', (string)$X)) : 
               " = " . unconvert_field($o[$_], q((string)$X))));
        if ($z == "sql" && preg_match('~char|text~', $o[$_]["type"] ?? '') && preg_match("~[^ -@]~", (string)$X)) {
            $K[] = "$d = " . q((string)$X) . " COLLATE " . charset($g) . "_bin";
        }
    }
    foreach ((array)($Z["null"] ?? []) as $_ => $X) {
        $K[] = escape_key((string)$X) . " IS NULL";
    }
    return implode(" AND ", $K);
}

function where_check(string $X, array $o = []): string {
    parse_str($X, $Ma);
    remove_slashes([&$Ma]);
    return where($Ma, $o);
}

function where_link(string $u, string $d, $Y, string $oe = "="): string {
    return "&where%5B$u%5D%5Bcol%5D=" . urlencode($d) . 
           "&where%5B$u%5D%5Bop%5D=" . urlencode(($Y !== null ? $oe : "IS NULL")) . 
           "&where%5B$u%5D%5Bval%5D=" . urlencode((string)$Y);
}

function convert_fields(array $e, array $o, array $N = []): string {
    $K = "";
    foreach ($e as $_ => $X) {
        if ($N && !in_array(idf_escape((string)$_), $N)) {
            continue;
        }
        $ua = convert_field($o[(string)$_] ?? null);
        if ($ua) {
            $K .= ", $ua AS " . idf_escape((string)$_);
        }
    }
    return $K;
}

function cookie(string $F, string $Y, int $zd = 2592000): bool {
    global $ba;
    return header("Set-Cookie: $F=" . urlencode($Y) . ($zd ? "; expires=" . gmdate("D, d M Y H:i:s", time() + $zd) . " GMT" : "") . 
                 "; path=" . preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"] ?? '') . 
                 ($ba ? "; secure" : "") . "; HttpOnly; SameSite=lax", false);
}

function restart_session(): void {
    if (!ini_bool("session.use_cookies")) {
        session_start();
    }
}

function stop_session(bool $wc = false): void {
    if (!ini_bool("session.use_cookies") || ($wc && @ini_set("session.use_cookies", "0") !== false)) {
        session_write_close();
    }
}

function &get_session(string $_): array {
    $driver = DRIVER ?? 'server';
    $server = SERVER ?? '';
    $username = $_GET["username"] ?? '';
    if (!isset($_SESSION[$_][$driver][$server][$username])) {
        $_SESSION[$_][$driver][$server][$username] = [];
    }
    return $_SESSION[$_][$driver][$server][$username];
}

function set_session(string $_, $X): void {
    $driver = DRIVER ?? 'server';
    $server = SERVER ?? '';
    $username = $_GET["username"] ?? '';
    $_SESSION[$_][$driver][$server][$username] = $X;
}

function auth_url(string $lh, string $O, string $V, ?string $k = null): string {
    global $Ib;
    $params = implode("|", array_keys($Ib ?? []));
    $params .= "|username" . ($k !== null ? "|db|" : "") . "|" . session_name();
    preg_match('~([^?]*)\??(.*)~', remove_from_uri($params), $D);
    return $D[1] . "?" . (sid() ? SID . "&" : "") . 
           ($lh !== "server" || $O !== "" ? urlencode($lh) . "=" . urlencode($O) . "&" : "") . 
           "username=" . urlencode($V) . ($k !== "" ? "&db=" . urlencode($k) : "") . 
           (!empty($D[2]) ? "&$D[2]" : "");
}

function is_ajax(): bool {
    return ($_SERVER["HTTP_X_REQUESTED_WITH"] ?? '') == "XMLHttpRequest";
}

function redirect(?string $C, ?string $E = null): void {
    if ($E !== null) {
        restart_session();
        $key = preg_replace('~^[^?]*~', '', ($C !== null ? $C : $_SERVER["REQUEST_URI"] ?? ''));
        $_SESSION["messages"][$key][] = $E;
    }
    if ($C !== null) {
        if ($C == "") {
            $C = ".";
        }
        header("Location: $C");
        exit;
    }
}

function query_redirect(string $I, string $C, string $E, bool $mf = true, bool $gc = true, bool $nc = false, string $_g = ""): bool {
    global $g, $m, $c;
    if ($gc) {
        $ag = microtime(true);
        $nc = !$g->query($I);
        $_g = format_time($ag);
    }
    $Wf = "";
    if ($I) {
        $Wf = $c->messageQuery($I, $_g, $nc);
    }
    if ($nc) {
        $m = error() . $Wf . script("messagesPrint();");
        return false;
    }
    if ($mf) {
        redirect($C, $E . $Wf);
    }
    return true;
}

function queries($I): bool {
    global $g;
    static $hf = [];
    static $ag;
    if (!$ag) {
        $ag = microtime(true);
    }
    if ($I === null) {
        return false;
    }
    $hf[] = (preg_match('~;$~', $I) ? "DELIMITER ;;\n$I;\nDELIMITER " : $I) . ";";
    return $g->query($I);
}

function apply_queries(string $I, array $S, callable $cc = null): bool {
    if ($cc === null) {
        $cc = 'table';
    }
    foreach ($S as $Q) {
        if (!queries("$I " . $cc($Q))) {
            return false;
        }
    }
    return true;
}

function queries_redirect(string $C, string $E, bool $mf): bool {
    list($hf, $_g) = queries(null);
    return query_redirect($hf, $C, $E, $mf, false, !$mf, $_g);
}

function format_time(float $ag): string {
    return lang(1, max(0, microtime(true) - $ag));
}

function remove_from_uri(string $Fe = ""): string {
    $uri = $_SERVER["REQUEST_URI"] ?? '';
    $params = $Fe . (SID ? "" : "|" . session_name());
    return substr(preg_replace("~(?<=[?&])($params)=[^&]*&~", '', $uri . "&"), 0, -1);
}

function pagination(int $G, int $pb): string {
    return " " . ($G == $pb ? $G + 1 : 
           '<a href="' . h(remove_from_uri("page") . ($G ? "&page=$G" . (isset($_GET["next"]) ? "&next=" . urlencode((string)$_GET["next"]) : "") : "")) . '">' . 
           ($G + 1) . "</a>");
}

function get_file(string $_, bool $xb = false): ?string {
    $rc = $_FILES[$_] ?? null;
    if (!$rc) {
        return null;
    }
    foreach ($rc as $_ => $X) {
        $rc[$_] = (array)$X;
    }
    $K = '';
    foreach ($rc["error"] as $_ => $m) {
        if ($m) {
            return (string)$m;
        }
        $F = $rc["name"][$_];
        $Hg = $rc["tmp_name"][$_];
        $fb = file_get_contents($xb && preg_match('~\.gz$~', $F) ? "compress.zlib://$Hg" : $Hg);
        if ($xb) {
            $ag = substr($fb, 0, 3);
            if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $ag, $sf)) {
                $fb = iconv("utf-16", "utf-8", $fb);
            } elseif ($ag == "\xEF\xBB\xBF") {
                $fb = substr($fb, 3);
            }
            $K .= $fb . "\n\n";
        } else {
            $K .= $fb;
        }
    }
    return $K;
}

function upload_error(int $m): string {
    $Jd = ($m == UPLOAD_ERR_INI_SIZE ? ini_get("upload_max_filesize") : 0);
    return ($m ? lang(2) . ($Jd ? " " . lang(3, $Jd) : "") : lang(4));
}

function repeat_pattern(string $Pe, int $xd): string {
    return str_repeat("$Pe{0,65535}", (int)($xd / 65535)) . "$Pe{0," . ($xd % 65535) . "}";
}

function is_utf8(string $X): bool {
    return (preg_match('~~u', $X) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $X));
}

function shorten_utf8(string $eg, int $xd = 80, string $ig = ""): string {
    $pattern = "(^(" . repeat_pattern("[\t\r\n -\x{10FFFF}]", $xd) . ")($)?)u";
    if (!preg_match($pattern, $eg, $D)) {
        preg_match("(^(" . repeat_pattern("[\t\r\n -~]", $xd) . ")($)?)", $eg, $D);
    }
    return h($D[1] ?? '') . $ig . (isset($D[2]) ? "" : "<i>...</i>");
}

function format_number(float $X): string {
    return strtr(number_format($X, 0, ".", lang(5)), 
                 preg_split('~~u', lang(6), -1, PREG_SPLIT_NO_EMPTY));
}

function friendly_url(string $X): string {
    return preg_replace('~[^a-z0-9_]~i', '-', $X);
}

function hidden_fields(array $ef, array $Uc = []): bool {
    $K = false;
    while (list($_, $X) = each($ef)) {
        if (!in_array((string)$_, $Uc)) {
            if (is_array($X)) {
                foreach ($X as $jd => $W) {
                    $ef[(string)$_ . "[$jd]"] = $W;
                }
            } else {
                $K = true;
                echo '<input type="hidden" name="' . h((string)$_) . '" value="' . h((string)$X) . '">';
            }
        }
    }
    return $K;
}

function hidden_fields_get(): void {
    echo (sid() ? '<input type="hidden" name="' . h(session_name()) . '" value="' . h(session_id()) . '">' : ''),
         (defined('SERVER') && SERVER !== null ? '<input type="hidden" name="' . h(DRIVER) . '" value="' . h(SERVER) . '">' : ""),
         '<input type="hidden" name="username" value="' . h($_GET["username"] ?? '') . '">';
}

function table_status1(string $Q, bool $oc = false): array {
    $K = table_status($Q, $oc);
    return ($K ? $K : ["Name" => $Q]);
}

function column_foreign_keys(string $Q): array {
    global $c;
    $K = [];
    foreach ($c->foreignKeys($Q) as $p) {
        foreach ($p["source"] as $X) {
            $K[$X][] = $p;
        }
    }
    return $K;
}

function enum_input(string $U, string $wa, array $n, $Y, ?string $Wb = null): string {
    global $c;
    preg_match_all("~'((?:[^']|'')*)'~", $n["length"] ?? '', $Ed);
    $K = ($Wb !== null ? "<label><input type='$U'$wa value='" . h($Wb) . "'" . 
          ((is_array($Y) ? in_array($Wb, $Y) : $Y === 0) ? " checked" : "") . "><i>" . lang(7) . "</i></label>" : "");
    foreach ($Ed[1] as $u => $X) {
        $X = stripcslashes(str_replace("''", "'", $X));
        $Na = (is_int($Y) ? $Y == $u + 1 : (is_array($Y) ? in_array($u + 1, $Y) : $Y === $X));
        $K .= " <label><input type='$U'$wa value='" . ($u + 1) . "'" . ($Na ? ' checked' : '') . '>' . 
              h($c->editVal($X, $n)) . '</label>';
    }
    return $K;
}

function input(array $n, $Y, ?string $s): void {
    global $Tg, $c, $z;
    $F = h(bracket_escape($n["field"] ?? ''));
    echo "<td class='function'>";
    
    if (is_array($Y) && !$s) {
        $ta = [$Y];
        if (version_compare(PHP_VERSION, '5.4') >= 0) {
            $ta[] = JSON_PRETTY_PRINT;
        }
        $Y = call_user_func_array('json_encode', $ta);
        $s = "json";
    }
    
    $uf = ($z == "mssql" && !empty($n["auto_increment"]));
    if ($uf && !isset($_POST["save"])) {
        $s = null;
    }
    
    $Cc = (isset($_GET["select"]) || $uf ? ["orig" => lang(8)] : []) + $c->editFunctions($n);
    $wa = " name='fields[$F]'";
    
    if (($n["type"] ?? '') == "enum") {
        echo h($Cc[""] ?? '') . "<td>" . $c->editInput($_GET["edit"] ?? '', $n, $wa, $Y);
    } else {
        $Lc = (in_array($s, $Cc) || isset($Cc[$s]));
        echo (count($Cc) > 1 ? 
              "<select name='function[$F]'>" . optionlist($Cc, $s === null || $Lc ? $s : "") . "</select>" . 
              on_help("getTarget(event).value.replace(/^SQL\$/, '')", 1) . script("qsl('select').onchange = functionChange;", "") : 
              h(reset($Cc))) . '<td>';
              
        $Zc = $c->editInput($_GET["edit"] ?? '', $n, $wa, $Y);
        if ($Zc !== "") {
            echo $Zc;
        } elseif (preg_match('~bool~', $n["type"] ?? '')) {
            echo "<input type='hidden'$wa value='0'>" .
                 "<input type='checkbox'" . (preg_match('~^(1|t|true|y|yes|on)$~i', (string)$Y) ? " checked='checked'" : "") . "$wa value='1'>";
        } elseif (($n["type"] ?? '') == "set") {
            preg_match_all("~'((?:[^']|'')*)'~", $n["length"] ?? '', $Ed);
            foreach ($Ed[1] as $u => $X) {
                $X = stripcslashes(str_replace("''", "'", $X));
                $Na = (is_int($Y) ? ($Y >> $u) & 1 : in_array($X, explode(",", (string)$Y), true));
                echo " <label><input type='checkbox' name='fields[$F][$u]' value='" . (1 << $u) . "'" . 
                     ($Na ? ' checked' : '') . ">" . h($c->editVal($X, $n)) . '</label>';
            }
        } elseif (preg_match('~blob|bytea|raw|file~', $n["type"] ?? '') && ini_bool("file_uploads")) {
            echo "<input type='file' name='fields-$F'>";
        } elseif (($yg = preg_match('~text|lob~', $n["type"] ?? '')) || preg_match("~\n~", (string)$Y)) {
            if ($yg && $z != "sqlite") {
                $wa .= " cols='50' rows='12'";
            } else {
                $M = min(12, substr_count((string)$Y, "\n") + 1);
                $wa .= " cols='30' rows='$M'" . ($M == 1 ? " style='height: 1.2em;'" : "");
            }
            echo "<textarea$wa>" . h((string)$Y) . '</textarea>';
        } elseif ($s == "json" || preg_match('~^jsonb?$~', $n["type"] ?? '')) {
            echo "<textarea$wa cols='50' rows='12' class='jush-js'>" . h((string)$Y) . '</textarea>';
        } else {
            $Ld = (!preg_match('~int~', $n["type"] ?? '') && preg_match('~^(\d+)(,(\d+))?$~', $n["length"] ?? '', $D) ? 
                  ((preg_match("~binary~", $n["type"] ?? '') ? 2 : 1) * (int)$D[1] + (isset($D[3]) ? 1 : 0) + (isset($D[2]) && empty($n["unsigned"]) ? 1 : 0)) : 
                  (isset($Tg[$n["type"] ?? '']) ? $Tg[$n["type"] ?? ''] + (empty($n["unsigned"]) ? 0 : 1) : 0));
            if ($z == 'sql' && min_version("5.6") && preg_match('~time~', $n["type"] ?? '')) {
                $Ld += 7;
            }
            echo "<input" . ((!$Lc || $s === "") && preg_match('~(?<!o)int(?!er)~', $n["type"] ?? '') && 
                 !preg_match('~\[\]~', $n["full_type"] ?? '') ? " type='number'" : "") . 
                 " value='" . h((string)$Y) . "'" . ($Ld ? " data-maxlength='$Ld'" : "") . 
                 (preg_match('~char|binary~', $n["type"] ?? '') && $Ld > 20 ? " size='40'" : "") . "$wa>";
        }
        echo $c->editHint($_GET["edit"] ?? '', $n, $Y);
        $uc = 0;
        foreach ($Cc as $_ => $X) {
            if ($_ === "" || !$X) {
                break;
            }
            $uc++;
        }
        if ($uc) {
            echo script("mixin(qsl('td'), {onchange: partial(skipOriginal, $uc), oninput: function () { this.onchange(); }});");
        }
    }
    echo "\n";
}

function process_input(array $n) {
    global $c, $l;
    $w = bracket_escape($n["field"] ?? '');
    $s = $_POST["function"][$w] ?? '';
    $Y = $_POST["fields"][$w] ?? '';
    
    if (($n["type"] ?? '') == "enum") {
        if ($Y == -1) {
            return false;
        }
        if ($Y == "") {
            return "NULL";
        }
        return +$Y;
    }
    
    if (!empty($n["auto_increment"]) && $Y == "") {
        return null;
    }
    
    if ($s == "orig") {
        return (!empty($n["on_update"]) && $n["on_update"] == "CURRENT_TIMESTAMP" ? idf_escape($n["field"] ?? '') : false);
    }
    
    if ($s == "NULL") {
        return "NULL";
    }
    
    if (($n["type"] ?? '') == "set") {
        return array_sum((array)$Y);
    }
    
    if ($s == "json") {
        $s = "";
        $Y = json_decode((string)$Y, true);
        if (!is_array($Y)) {
            return false;
        }
        return $Y;
    }
    
    if (preg_match('~blob|bytea|raw|file~', $n["type"] ?? '') && ini_bool("file_uploads")) {
        $rc = get_file("fields-$w");
        if (!is_string($rc)) {
            return false;
        }
        return $l->quoteBinary($rc);
    }
    
    return $c->processInput($n, $Y, $s);
}

function fields_from_edit(): array {
    global $l;
    $K = [];
    foreach ((array)($_POST["field_keys"] ?? []) as $_ => $X) {
        if ($X != "") {
            $X = bracket_escape((string)$X);
            $_POST["function"][$X] = $_POST["field_funs"][$_] ?? '';
            $_POST["fields"][$X] = $_POST["field_vals"][$_] ?? '';
        }
    }
    foreach ((array)($_POST["fields"] ?? []) as $_ => $X) {
        $F = bracket_escape((string)$_, 1);
        $K[$F] = [
            "field" => $F,
            "privileges" => ["insert" => 1, "update" => 1],
            "null" => 1,
            "auto_increment" => ($_ == $l->primary)
        ];
    }
    return $K;
}

function search_tables(): void {
    global $c, $g;
    $_GET["where"][0]["val"] = $_POST["query"] ?? '';
    $Kf = "<ul>\n";
    foreach (table_status('', true) as $Q => $R) {
        $F = $c->tableName($R);
        if (isset($R["Engine"]) && $F != "" && (!isset($_POST["tables"]) || in_array($Q, (array)$_POST["tables"]))) {
            $J = $g->query("SELECT" . limit("1 FROM " . table($Q), " WHERE " . implode(" AND ", $c->selectSearchProcess(fields($Q), [])), 1));
            if (!$J || $J->fetch_row()) {
                $af = "<a href='" . h(ME . "select=" . urlencode($Q) . 
                      "&where[0][op]=" . urlencode($_GET["where"][0]["op"] ?? '') . 
                      "&where[0][val]=" . urlencode($_GET["where"][0]["val"] ?? '')) . "'>$F</a>";
                echo "$Kf<li>" . ($J ? $af : "<p class='error'>$af: " . error()) . "\n";
                $Kf = "";
            }
        }
    }
    echo ($Kf ? "<p class='message'>" . lang(9) : "</ul>") . "\n";
}

function dump_headers($Tc, bool $Sd = false): string {
    global $c;
    $K = $c->dumpHeaders($Tc, $Sd);
    $Ce = $_POST["output"] ?? '';
    if ($Ce != "text") {
        header("Content-Disposition: attachment; filename=" . $c->dumpFilename($Tc) . ".$K" . 
               ($Ce != "file" && !preg_match('~[^0-9a-z]~', $Ce) ? ".$Ce" : ""));
    }
    session_write_close();
    ob_flush();
    flush();
    return $K;
}

function dump_csv(array $L): void {
    foreach ($L as $_ => $X) {
        if (preg_match("~[\"\n,;\t]~", (string)$X) || (string)$X === "") {
            $L[$_] = '"' . str_replace('"', '""', (string)$X) . '"';
        }
    }
    echo implode(($_POST["format"] == "csv" ? "," : ($_POST["format"] == "tsv" ? "\t" : ";")), $L) . "\r\n";
}

function apply_sql_function(?string $s, string $d): string {
    return ($s ? ($s == "unixepoch" ? "DATETIME($d, '$s')" : 
           ($s == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$s("))) . "$d)" : $d);
}

function get_temp_dir(): ?string {
    $K = ini_get("upload_tmp_dir");
    if (!$K) {
        if (function_exists('sys_get_temp_dir')) {
            $K = sys_get_temp_dir();
        } else {
            $sc = @tempnam("", "");
            if (!$sc) {
                return null;
            }
            $K = dirname($sc);
            unlink($sc);
        }
    }
    return $K;
}

function file_open_lock(string $sc) {
    $r = @fopen($sc, "r+");
    if (!$r) {
        $r = @fopen($sc, "w");
        if (!$r) {
            return null;
        }
        chmod($sc, 0660);
    }
    flock($r, LOCK_EX);
    return $r;
}

function file_write_unlock($r, string $rb): void {
    rewind($r);
    fwrite($r, $rb);
    ftruncate($r, strlen($rb));
    flock($r, LOCK_UN);
    fclose($r);
}

function password_file(bool $i): ?string {
    $sc = get_temp_dir() . "/adminer.key";
    $K = @file_get_contents($sc);
    if ($K || !$i) {
        return $K ?: null;
    }
    $r = @fopen($sc, "w");
    if ($r) {
        chmod($sc, 0660);
        $K = rand_string();
        fwrite($r, $K);
        fclose($r);
    }
    return $K;
}

function rand_string(): string {
    return md5(uniqid((string)mt_rand(), true));
}

function select_value($X, $B, array $n, string $zg): string {
    global $c;
    if (is_array($X)) {
        $K = "";
        foreach ($X as $jd => $W) {
            $K .= "<tr>" . ($X != array_values($X) ? "<th>" . h((string)$jd) : "") . 
                  "<td>" . select_value($W, $B, $n, $zg);
        }
        return "<table cellspacing='0'>$K</table>";
    }
    if (!$B) {
        $B = $c->selectLink($X, $n);
    }
    if ($B === null) {
        if (is_mail((string)$X)) {
            $B = "mailto:$X";
        }
        if (is_url((string)$X)) {
            $B = (string)$X;
        }
    }
    $K = $c->editVal($X, $n);
    if ($K !== null) {
        if (!is_utf8($K)) {
            $K = "\0";
        } elseif ($zg != "" && is_shortable($n)) {
            $K = shorten_utf8($K, max(0, (int)$zg));
        } else {
            $K = h($K);
        }
    }
    return $c->selectVal($K, $B, $n, $X);
}

function is_mail(string $Tb): bool {
    $va = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    $Pe = "$va+(\\.$va+)*@($Hb?\\.)+$Hb";
    return preg_match("(^$Pe(,\\s*$Pe)*\$)i", $Tb);
}

function is_url(string $eg): bool {
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    return preg_match("~^(https?)://($Hb?\\.)+$Hb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $eg);
}

function is_shortable(array $n): bool {
    return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $n["type"] ?? '');
}

function count_rows(string $Q, array $Z, bool $fd, array $t): string {
    global $z;
    $I = " FROM " . table($Q) . ($Z ? " WHERE " . implode(" AND ", $Z) : "");
    return ($fd && ($z == "sql" || count($t) == 1) ? 
           "SELECT COUNT(DISTINCT " . implode(", ", $t) . ")$I" : 
           "SELECT COUNT(*)" . ($fd ? " FROM (SELECT 1$I GROUP BY " . implode(", ", $t) . ") x" : $I));
}

function slow_query(string $I) {
    global $c, $T, $l;
    $k = $c->database();
    $Ag = $c->queryTimeout();
    $Sf = $l->slowQuery($I, $Ag);
    if (!$Sf && support("kill") && is_object($h = connect()) && ($k == "" || $h->select_db($k))) {
        $ld = $h->result(connection_id());
        echo '<script', nonce(), '>
var timeout = setTimeout(function () {
	ajax(\'', js_escape(ME), 'script=kill\', function () {
	}, \'kill=',$ld,'&token=',$T,'\');
}, ', 1000 * $Ag, ');
</script>
';
    } else {
        $h = null;
    }
    ob_flush();
    flush();
    $K = @get_key_vals(($Sf ? $Sf : $I), $h, false);
    if ($h) {
        echo script("clearTimeout(timeout);");
        ob_flush();
        flush();
    }
    return $K;
}

function get_token(): string {
    $kf = rand(1, 1000000);
    return ($kf ^ $_SESSION["token"]) . ":$kf";
}

function verify_token(): bool {
    $token = explode(":", $_POST["token"] ?? '');
    $T = $token[0] ?? '';
    $kf = (int)($token[1] ?? 0);
    return ($kf ^ $_SESSION["token"]) == $T;
}

function lzw_decompress(string $Da): string {
    $Db = 256;
    $Ea = 8;
    $Ta = [];
    $vf = 0;
    $wf = 0;
    for ($u = 0; $u < strlen($Da); $u++) {
        $vf = ($vf << 8) + ord($Da[$u]);
        $wf += 8;
        if ($wf >= $Ea) {
            $wf -= $Ea;
            $Ta[] = $vf >> $wf;
            $vf &= (1 << $wf) - 1;
            $Db++;
            if ($Db >> $Ea) {
                $Ea++;
            }
        }
    }
    $Cb = range("\0", "\xFF");
    $K = "";
    foreach ($Ta as $u => $Sa) {
        $Sb = $Cb[$Sa] ?? null;
        if (!isset($Sb)) {
            $Sb = $vh . $vh[0];
        }
        $K .= $Sb;
        if ($u) {
            $Cb[] = $vh . $Sb[0];
        }
        $vh = $Sb;
    }
    return $K;
}

function on_help(string $Za, int $Qf = 0): string {
    return script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $Za, $Qf) }, onmouseout: helpMouseout});", "");
}

function edit_form(string $b, array $o, $L, bool $bh): void {
    global $c, $z, $T, $m;
    $ng = $c->tableName(table_status1($b, true));
    page_header(($bh ? lang(10) : lang(11)), $m, ["select" => [$b, $ng]], $ng);
    if ($L === false) {
        echo "<p class='error'>" . lang(12) . "\n";
    }
    echo '<form action="" method="post" enctype="multipart/form-data" id="form">
';
    if (!$o) {
        echo "<p class='error'>" . lang(13) . "\n";
    } else {
        echo "<table cellspacing='0'>" . script("qsl('table').onkeydown = editingKeydown;");
        foreach ($o as $F => $n) {
            echo "<tr><th>" . $c->fieldName($n);
            $yb = $_GET["set"][bracket_escape($F)] ?? null;
            if ($yb === null) {
                $yb = $n["default"] ?? '';
                if (($n["type"] ?? '') == "bit" && preg_match("~^b'([01]*)'\$~", (string)$yb, $sf)) {
                    $yb = $sf[1];
                }
            }
            $Y = ($L !== null ? ($L[$F] !== "" && $z == "sql" && preg_match("~enum|set~", $n["type"] ?? '') ? 
                  (is_array($L[$F]) ? array_sum($L[$F]) : +$L[$F]) : $L[$F]) : 
                  (!$bh && !empty($n["auto_increment"]) ? "" : (isset($_GET["select"]) ? false : $yb)));
            if (!isset($_POST["save"]) && is_string($Y)) {
                $Y = $c->editVal($Y, $n);
            }
            $s = (isset($_POST["save"]) ? (string)($_POST["function"][$F] ?? '') : 
                  ($bh && !empty($n["on_update"]) && $n["on_update"] == "CURRENT_TIMESTAMP" ? "now" : 
                  ($Y === false ? null : ($Y !== null ? '' : 'NULL'))));
            if (preg_match("~time~", $n["type"] ?? '') && $Y == "CURRENT_TIMESTAMP") {
                $Y = "";
                $s = "now";
            }
            input($n, $Y, $s);
            echo "\n";
        }
        if (!support("table")) {
            echo "<tr>" .
                 "<th><input name='field_keys[]'>" . script("qsl('input').oninput = fieldChange;") .
                 "<td class='function'>" . html_select("field_funs[]", $c->editFunctions(["null" => isset($_GET["select"])])) .
                 "<td><input name='field_vals[]'>" .
                 "\n";
        }
        echo "</table>\n";
    }
    echo "<p>\n";
    if ($o) {
        echo "<input type='submit' value='" . lang(14) . "'>\n";
        if (!isset($_GET["select"])) {
            echo "<input type='submit' name='insert' value='" . ($bh ? lang(15) : lang(16)) . "' title='Ctrl+Shift+Enter'>\n" .
                 ($bh ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '" . lang(17) . "...', this); };") : "");
        }
    }
    echo ($bh ? "<input type='submit' name='delete' value='" . lang(18) . "'>" . confirm() . "\n" : 
          ($_POST || !$o ? "" : script("focus(qsa('td', qs('#form'))[1].firstChild);")));
    
    if (isset($_GET["select"])) {
        hidden_fields([
            "check" => (array)($_POST["check"] ?? []),
            "clone" => $_POST["clone"] ?? '',
            "all" => $_POST["all"] ?? ''
        ]);
    }
    
    echo '<input type="hidden" name="referer" value="' . h(isset($_POST["referer"]) ? $_POST["referer"] : ($_SERVER["HTTP_REFERER"] ?? '')) . '">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="' . h($T) . '">
</form>
';
}

if (isset($_GET["file"])) {
    if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + 365 * 24 * 60 * 60) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: immutable");
    
    if ($_GET["file"] == "favicon.ico") {
        header("Content-Type: image/x-icon");
        echo lzw_decompress("\0\0\0` \0�\0\n @\0�C��\"\0`E�Q����?�tvM'�Jd�d\\�b0\0�\"��fӈ��s5����A�XPaJ�0���8�#R�T��z`�#.��c�X��Ȁ?�-\0�Im?�.�M��\0ȯ(̉��/(%�\0");
    } elseif ($_GET["file"] == "default.css") {
        header("Content-Type: text/css; charset=utf-8");
        echo lzw_decompress("\n1̇�ٌ�l7��B1�4vb0��fs���n2B�ѱ٘�n:�#(�b.\rDc)��a7E����l�ñ��i1̎s���-4��f�	��i7�������Fé�vt2���!�r0����t~�U�'3M��W�B�'c�P�:6T\rc�A�zr_�WK�\r-�VNFS%~�c���&�\\^�r����u�ŎÞ�ً4'7k�����Q��h�'g\rFB\ryT7SS�P�1=ǤcI��:�d��m>�S8L�J��t.M���	ϋ`'C����889�� �Q����2�#8А����6m����j��h�<�����9/���:�J�)ʂ�\0d>!\0Z��v��n����o(����k�7��s��>���!�R\"*nS�\0@P\"���(�#[���@g�o���zn�9k�8�n���1�I*��=�n������0�c(�;�à��!���*c��>Ύ�E7D�LJ��1����`�8(��3M��\"�39�?E�e=Ҭ�~������Ӹ7;�C����E\rd!)�a*�5ajo\0�#`�38�\0��]�e����2�	mk��e]���AZs�StZ�Z!)BR�G+�#Jv2(����c�4<�#sB�0���6YL\r�=���[�73��<�:��bx��J=	m_ ���f�l��t��I��H�3�x*���6`t6��%�U�L�eق�<�\0�AQ<P<:�#u/�:T\\>��-�xJ�͍QH\nj�L+j�z���7���`����\nk��'�N�vX>�C-T˩�����4*L�%Cj>7ߨ�ި���`���;y���q�r�3#��} :#n�\r��^�=C�Aܸ�Ǝ�s&8��K&��*0��t�S���=�[��:�\\]�E݌�/O�>^]�ø�<����gZ�V��q����� ��x\\�������޺��\"J�\\î��##���D��x6���5x�������\rH�l ����b��r�7��6���j|����ۖ*�FAquvyO��WeM����D.F��:R�\$-����T!�DS`�8D�~��A`(�em�����T@O1@��X���\nLp���P�����m�yf��)	���GSEI���xC(s(a�?\$`tE�n����,�� \$a��U>,�В\$Z�kDm,G\0��\\��i��%ʹ� n��������g���b	y`��Ԇ�W�� 䗗�_C��T\ni��H%�da��i�7�At�,��J�X4n����0o͹�9g\nzm�M%`�'I���О-����7:p�3p��Q�rED������b2]�PF����>e���3j\n�߰t!�?4f�tK;��\rΞи�!�o�u�?���Ph���0uIC}'~��2�v�Q���8)���7�DI�=��y&��ea�s*hɕjlA�(��\"�\\��m^i��M)��^�	|~�l��#!Y�f81RS����!���62P�C��l&���xd!�|��9�`�_OY�=��G�[E�-eL�CvT� )�@�j-5���pSg�.�G=���ZE��\$\0�цKj�U��\$���G'I�P��~�ځ� ;��hNێG%*�Rj��X[�XPf^��|��T!�*N��І�\rU��^q1V!��Uz,�I|7�7�r,���7���ľB���;�+���ߕ�A�p����^���~ؼW!3P�I8]��v�J��f�q�|,���9W�f`\0�q�Z�p}[Jdhy��N��Y|��Cy,�<s A�{e�Q���hd���Ǉ �B4;ks&�������a�������;˹}�S��J���)�=d��|���Nd��I�*8���dl�ѓ�E6~Ϩ�F����X`�M\rʞ/�%B/V�I�N&;���0�UC cT&.E+����������@�0`;���G�5���ަj'������Ɛ�Y�+��QZ-i���yv��I�5��,O|�P�]Fۏ�����\0���2�49͢���n/χ]س&��I^�=�l��qfI��= �]x1GR�&�e�7��)��'��:B�B�>a�z�-���2.����bz���#������Uᓍ�L7-�w�t�3ɵ���e���D��\$�#���j�@�G�8� �7p���R�YC��~��:�@��EU�J��;67v]�J'���q1ϳ�El�QІi������/��{k<��֡M�po�}��r��q�؞�c�ä�_m�w���^�u������������ln���	��_�~�G�n����{kܞ�w���\rj~�K�\0�����-����B�;����b`}�CC,���-��L��8\r,��kl�ǌ�n}-5����3u�gm��Ÿ�*�/������׏�`�`�#x�+B?#�ۏN;OR\r����\$�����k��ϙ\01\0k�\0�8��a��/t���#(&�l&���p��삅���i�M�{�zp*�-g���v��6�k�	����d�؋����A`6�lX)+d ���7 �\r�� �ځcj6��\rp�\r��\r\"oP�7�\r��\0�\0�y��P���\rQ7���Z��4Q���ڍp/�y\r��##D�;����<�g�\0fi2�)f�\\	m�Gh\r�#�n����@[ �G�\"Sqm��\r���#��(Aj��qѣ%���̑3qE��\0r�����0��я�����.��Q7шW���u����� �@��H��q'vs�0�\n�+0����SG�p�O`�\r)c�#�����R=\$�ƐR\r�Gы\$R?%2C�[\0؍�~�!�\\��p�#@���O(rg%�?ra\$��)r](��&�?&�#&R�',\rqV3�\"H�m+���l�Q\"\0�4��\$r�,��=����&2;.�H@`����a����\$�_*RIS&��q��_�1�1+1������3)2�V7��2l�ڄ!1g-�2f`���,Q�7��0qg�]!q��m6����_�M7 ���7�o6Q����kp�3�g9��s� 3�6�\r�:S�9ӏ;� �\r9�-\0�Yӧ0Q�<b#<Ӂ�w/�G��>r�\r��=3��^&Q;ѣ?q�0\"�0HЙ�|���ʖS��i��@*�T�2�T#�� �\0�C��07]?��&���E��D�;:/�3�E�5��EQ�e��T\"�m����5�E;���#=4�8��*�����LS�5Hr�JE TO\rԅJ��J��J���eG)8B�8�,&��G�����	���+M���ɲ��^*���G��14�6�\$.\"拢�I4w!\$L �8b�A2�L�'M?MF�\$�,����Nr��/4�BJ�¨");
    } elseif ($_GET["file"] == "functions.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo lzw_decompress("f:��gCI��\n8��3)��7���81��x:\nOg#)��r7\n\"���`�|2�gSi�H)N�S���\r��\"0��@�)�`(\$s6O!���V/=��' T4�=��iS��6IO��er�x�9�*ź��n3�\rщv�C��`���2G%�Y�����1��f���Ȃl��1�\ny�*pC\r\$�n�T��3=\\�r9O\"�	��l<�\r�\\��I,�s\nA��eh+M��!�q0��f�`(�N{c��+w���Y��p٧3�3��+I��j�����k��n�q���zi#^r�����3����[���o;��(��6�#�Ґ��\":cz>ߣC2v�CX�<�P��c*5\n����/�P97�|F��c0�����!����!���!��\nZ%�ć#CH�!��r8�\$����,�Rܔ2���^0��@�2��(�88P/���݄�\\�\$La\\�;c�H��HX���\nʃt���8A<�sZ�*�;I��3��@�2<���!A8G<�j�-K�({*\r��a1���N4Tc\"\\�!=1^���M9O�:�;j��\r�X��L#H�7�#Tݪ/-���p�;�B \n�2!���t]apΎ��\0R�C�v�M�I,\r���\0Hv��?kT�4����uٱ�;&���+&�����\r�X���bu4ݡi88�2B�/⃖4���N8A�A)52������2��s�8��5���p�WC@�:�t�㾴�e��h\"#8_��cp^���I]OH��:zd�3g�(���Ök���\\6����2�ږ��i��7���]\r�xO�n�p�<��p�Q�U�n��|@���#G3��8bA��6�2�67%#�\\8\r��2�c\r�ݟk��.(�	��-�J;���� ��L�� ���W��㧓ѥɤ�����n��ҧ���M��9ZНs]�z����y^[��4-�U\0ta��62^��.`���.C�j�[ᄠ% Q\0`d�M8�����\$O0`4���\n\0a\rA�<�@����\r!�:�BA�9�?h>�Ǻ��~̌�6Ȉh�=�-�A7X��և\\�\r��Q<蚧q�'!XΓ2�T �!�D\r��,K�\"�%�H�qR\r�̠��C =��������<c�\n#<�5�M� �E��y�������o\"�cJKL2�&��eR��W�AΐTw�ё;�J���\\`)5��ޜB�qhT3��R	�'\r+\":�8��tV�A�+]��S72���Y�F��Z85�c,���J��/+S�nBpoW�d��\"�Q��a�ZKp�ާy\$�����4�I�@L'@�xC�df�~}Q*�ҺA��Q�\"B�*2\0�.��kF�\"\r��� �o�\\�Ԣ���VijY��M��O�\$��2�ThH����0XH�5~kL���T*:~P��2�t���B\0�Y������j�vD�s.�9�s��̤�P�*x���b�o����P�\$�W/�*��z';��\$�*����d�m�Ã�'b\r�n%��47W�-�������K���@<�g�èbB��[7�\\�|�VdR��6leQ�`(Ԣ,�d���8\r�]S:?�1�`��Y�`�A�ғ%��ZkQ�sM�*���{`�J*�w��ӊ>�վ�D���>�eӾ�\"�t+po������W\$�����Q��@��3t`����-k7g���]��l��E��^dW>nv�t�lzPH��FvW�V\n�h;��B�D�س/�:J��\\�+ %������]��ъ��wa�ݫ���=��X����N�/��w�J�_[�t)5���QR2l�-:�Y9�&l R;�u#S	� ht�k�E!l���>SH��X<,��O�YyЃ%L�]\0�	��^�dw�3�,Sc�Qt�e=�M:4���2]��P�T�s��n:��u>�/�d�� ���a�'%�����qҨ&@֐���H�G�@w8p����΁�Z\n��{�[�t2���a��>	�w�J�^+u~�o��µXkզBZk˱�X=��0>�t��lŃ)Wb�ܦ��'�A�,��m�Y�,�A����e��#V��+�n1I����E�+[����[��-R�mK9��~����L�-3O���`_0s���L;�����]�6��|��h�V�T:��ޞerM��a�\$~e�9�>�����Д�\r��\\���J1Ú���%�=0{�	����|ޗtڼ�=����Q�|\0?��[g@u?ɝ|��4�*��c-7�4\ri'^����n;�������(����{K�h�nf���Zϝ}l�����]\r���pJ>�,gp{�;�\0��u)��s�N�'����H��C9M5��*��`�k�㬎����AhY��*����jJ�ǅPN+^� D�*��À���D��P���LQ`O&��\0�}�\$���6�Zn>��0� �e��\n��	�trp!�hV�'Py�^�*|r%|\nr\r#���@w����T.Rv�8�j�\nmB���p�� �Y0�Ϣ�m\0�@P\r8�Y\rG��d�	�QG�P%E�/@]\r���{\0�Q����bR M\rF��|��%0SDr�����f/����\":�mo�ރ�%�@�3H�x\0�l\0���	��W����\n�8\r\0}�@�D��`#�t��.�jEoDrǢlb����t�f4�0���%�0���k�z2\r�� �W@�%\r\n~1��X�����D2!��O�*���{0<E��k*m�0ı���|\r\n�^i��� ��!.�r � ��������f��Ĭ��+:���ŋJ�B5\$L���P���LĂ��� Z@����`^P�L%5%jp�H�W��on��kA#&���8��<K6�/����̏������XWe+&�%���c&rj��'%�x�����nK�2�2ֶ�l��*�.�r��΢���*�\r+jp�Bg�{ ���0�%1(���Z�`Q#�Ԏ�n*h���v�B����\\F\n�W�r f\$�93�G4%d�b�:JZ!�,��_��f%2��6s*F���Һ�EQ�q~��`ts�Ҁ���(�`�\r���#�R����R�r���X��:R�)�A*3�\$l�*ν:\"Xl��tbK�-��O>R�-�d��=��\$S�\$�2��}7Sf��[�}\"@�]�[6S|SE_>�q-�@z`�;�0��ƻ��C�*��[���{D��jC\nf�s�P�6'���ȕ QE���N\\%r�o�7o�G+dW4A*��#TqE�f��%�D�Z�3��2.���Rk��z@��@�E�D�`C�V!C��ŕ\0���I�)38��M3�@�3L��ZB�1F@L�h~G�1M���6���4�Xє�}ƞf�ˢIN��34��X�Btd�8\nbtN��Qb;�ܑD��L�\0��\"\n����V��6��]U�cVf���D`�M�6�O4�4sJ��55�5�\\x	�<5[F�ߵy7m�)@SV��Ğ#�x��8 ոы��`�\\`�-�v2���p���+v���U��L�xY.����\0005(�@���ⰵ[U@#�VJuX4�u_�\"JO(Dt�_	5s�^���������5�^�^V��I��\rg&]��\r\"ZCI�6��#��\r��ܓ��]7���q�0��6}o���`u��ab(�X�D�f�M�N)�V�UUF�о��=jSWi�\"\\B1Ğ�E0� �amP��&<�O_�L����.c�1Z*��R\$�h���mv�[v>ݭ�p����(��0�����cP�om\0R��p�&�w+KQ�s6�}5[s�J���2��/���O �V*)�R�.Du33�F\r�;��v4���H�	_!��2��k����+��%�:�_,�eo��F��AJ�O�\"%�\n�k5`z %|�%�Ϋg|��}l�v2n7�~\0�	�YRH��@��r��xN-Jp\0�����f#��@ˀmv�x��\r���2WMO/�\nD��7�}2����VW�W��wɀ7����H�k���]�\$�Mz\\�e�.f�RZ�a�B����Qd�KZ��vt���w4�\0�Z@�	��Bc;�b��>�B�	3m�n\n�o��J3��k�(܍���\"�yG\$:\r�ņ�ݎ��G6�ɲJ��y��Q�\\Q��if�����(�m)/r�\$�J�/�H�]*�����g�ZOD�Ѭ��]1�g22�������f�=HT��]N�&���M\0�[8x�ȮE���8&L�Vm�v����j�ט�F��\\��	���&s�@Q� \\\"�b��	��\rBs�Iw�	�Yɜ�N �7�C/&٫`�\n\n��[k���*A���T�V*UZtz{�.��y�S���#�3�ipzW@yC\nKT��1@|�z#���_CJz(B�,V�(K�_��dO���P�@X��t�Ѕ��c;�WZzW�_٠�\0ފ�CF�xR �	��\n������P�A��&�������,�pfV|@N�\"�\$�[�i����������Z�\0Zd\\\"�|�W`��]��tz�o\$�\0[����u�e����ə�bhU-��,�r �Lk8��֫�V&�al����d���2;	�'-��Jyu��a���\0����a��{s�[9V\0��F��R �VB0S;D�>L4�&�ZHO1�\0�wg��S�tK��R�z���i��+�3�w��z�X�]�(G\$����D+�tչ�(#����oc�:	��Y6�\0��&��	@�	���)��!����w���# t�x�ND�����)��C��FZ�p��a��*F�b�	��ͼ����ģ�����Si/S�!��z�UH*�4�����0�K�-�/���-k`�n�Li�J�~�w�Jn��\"�`�=��V�3Oį8t�>��vo��E.��Rz`��p�P���E\\��ɧ�3L�l�ѥs]T���oV���\n��	*�\r�@7)��D�m�0W�5Ӏ��ǰ�w��b���|	��JV����\"�ur\r�&N0N�B�d��d�8�D���_ͫ�^T��H#]�d�+�v�~�U,�PR%�������x���fA��C��m����͸����c��yŜD)���uH���p�p�^u\0������}�{ѡ�\rg�s�QM�Y�2j�\r�|0\0X��@q���I`��5F�6�N��V@ӔsE�p���#\r�P�T��DeW�ؼ񛭁��z!û��:�DMV(��~X���9�\0��@���40N�ܽ~�Q�[T���e�qSv\"�\"h�\0R-�hZ�d�����F5�P��`�9�D&xs9W֗5Er@o�wkb�1��PO-O�OxlH�D6/ֿ�m�ޠ��3�7T��K�~54�	�p#�I�>YIN\\5���NӃ����M��pr&�G�xM�sq����.F���8�Cs�� h�e5��������*�b�)Sڪ��̭�e�0�-X� {�5|�i�֢a��ȕ6z�޽��/Y���ێM� ƃ� �\nR*8r o� @7�8Bf�z�K�r���A\$˰	p�\0?���d�k�|45}�A����ɶ�W��J�2k Gi\0\"����d���8�\0�>m���� `8�w�7�o4�cGh��Q�(퀨�8@\$<\0p��0���L�eX+�Ja�{��B���h��8�Cy���P2��Ӯ�*�EH�2���DqS�ۘ�p�0�I���k�`��S�\n�:��B�7����{-����`�����6�A�W�ܖ\r�p�W#���?���{\0������cD��[<����f�--�pԌ�*B�]�nW��^��R70\r�+N�GN�\$(\0�#+y�@�@iD(8@\r�h��H�He����zz�{1����h��W1F�Who&aɜ�d6���jw�������`h�{v`RE�\nj���`�ܷ����*���ʸ}�Y��	\rY�H�6�#\0��廆��a�� Q�HEl4�d���p��#�������o�br+_)\r`��!�|dQ�>��=Qʡ��ζ�EOB'�>�P���Ӷ� A\rnK�i�� 	�����	�%<	�o;�S�@�!	�x��:���A�+\\1d\$�jO��7�%�	�/����gu�z*�G�H�5\"8��,�]raq���/�h��#����\$ /tn��8y��-�O���H�b���<�Z�!���1��`�.(uo����|`GːS��BaM	ڂ9ƞ�D@���1�B�tD��ʡ@?o�(H��qC��8E�TcncR��6�N%�rHj��2G\0�a��q �r��z9b>(P���x��<��)�x#�8�誹t���h�2v��Wo2U���t��+=�l#���j�D�	0����&R�c�\$�*̑-Z`��\r��;�|A�p�=1�	1����ƈ�bEv(^�X�P2=\0}�W���G�<���G�����R�#P�Hܮr9	��Y��!�LB���4�NC�Z��IC���MLm��,�f@eY�x�BS(�+��<4Y�)-�\r�z?\$���\"\"�� 6�E�\r)z���@ȑ��r����*���J�윋��%\$�e�J���\0A�\$ڰ/5��B0S���x��I�Q)��<��4YS�&�{��b�+IG=>�\r�PY`Z�D�`��U����F1���4d8X(����C%�`�㜭0�I\$�7W�pǁ,��Ac���&Ԍ�p\$�:�>]�.�VY��\$p� ��]��`�;��e�\0�0�\n��K+�@DL�S��r(on�M\0@9��%�\"�WS�\"���� 䥙�ٍ�ػj�_J-��rʜ���5�\\�2�5>Ze\"0��%9y��^�WMax&a)D�L���2Q����t?�=,�/o�f�3I�J�\$\r;���7�}�\r�W�@�Ұ�M|\r�Y���]5���\\*s:��FV!���kن�R���L3L�	��52�M�sb�\$����7�\0l�y���&� 9�|m!��0J��4��TSd���G���nK�V:l�D'/��:Zs��\n��y�%��i����,@ҲL��j1<��3Ĩ�D2/;��'Pݻ����`����qKȰ�f�I�L� Dݬ�4�3 ��OH�J�	q�&�����X��!��r)F�Xx���^QwOP��h��՞-_�>�a����(	��x%��K�b�<�E�j7�������hHt�`�.r�P���x��\"{\0006CVQE�&��>�ޅ�w����e'?B�9x�>:\"�73���xT\0e�����j	��[t�Ҝ\"�(\\K�e�z�r����e> ���\0002�hʇ��X�a<�JtU�z`�達?��#�����2-��4hFY|C��\"M�yƔKd ���E�7���+(U�ʖX�� /D���)�\"����بމjoh�Fz4�t���D׌�G��RZ�ć�ȿ\0�FV4Q�6v�b�i=G�;Ϭ�k�d+\n>�E��\0�2f{����!J��Q��J�ؘ9��(2�#\\Z��,��Qܥ�3?8`�	bwR6��\n*�㋀�ƒ�(t��L*�S�d�\0x�)�(�*�wH]7O�N�v(Гdg�q	\nLp��L�N��H@�1����M �		n��z���e4!!	��'槝-t����AQP���L,����7��\\�i����^�\$�,�|�Z��(S9����\n* +��T�D�z?(T�>��L��æ��R����\$�zдi̼W�ͨ�Ds�{)�@�����	v�P��g�qIVҨ����\n )�!�8|\$pZ�*�!7A����N��j�NW����U���Q���)�eF�UA�S�x\0[N���2���X :S�T�~�S*T4	�3��]9�F���]:�KUg;��*Ay�a��1j|8Ϋ����I�MR��Vh7uU����r,�h�%<q�R@N9�ާ�k�	�B|�����8��r������DР@\"�ɋ�z\r������O�_���Q�\0\0���|�]�f�\nz�����UeH�Ą/k+�TF?��*03�!�\0��I���t	f\0(S��U��ZA�F��1\0��k�]��WZN�Q��܂���%��x1���'��!-,�Ƕvzg��#�Gh�;f�PH�9Bj�u�\n�A�VR����1K+�MN!��Sμ��Y��vdZ\\,���g٨�����\"}W��Yɵ�t�P���g�,�����	\0b�-�hB/@�̎�/�M���J���Y\0����)\n��I�?v�	��Ȕ1��\$�(�w\r+�n ���s�s�QfQ�O�P�.D���bV\0-�J<�i;[���=#���n,j?)�\"���lYL.����A::������BxOF7����`���d��}�}=�i)@к��\$ q˷(y%��huzb2�3Ƨ��.�-h�oO����\0`���VZ��&y�t9C���鋭Z��ґ�Z!�X�U����.k���V#8�G�}�Q���u8cΫt�bE>�v��{@{QP]<�ary��j\\��\$j�x�nc6k�;qs�T���K�����jJ���n\\C��{���`g���6�5���Rk�t������s�|@�_0΅5:B�3����rѡ�&�㴸�\0����&�׈�����ԡ���SXʕ�G�m�ʶWr,j�q\0\$޺sW�P�.A\n4�9(u��.���l�V�Ju�Ԍ�+�A�uC�>hl6��2���G�e���N���n�=�'���~��Þ����PҀ�%0z�u��r�\0��9uE�s\"���\\�ט����^���(3ՑS%<+�9��Ծ����\0���~'̞�֓<+�,i�:��@��N���\$�o������� �]�������Z�!��]�n,��x��>_�f��W\0006��%�}I�\nh߀w�����ǃ -��H@_�Vi�����{���R��^�۔}5�b,!5���H��p/��k<��<�jh|i��k��hLv݄\n�`�[���WC6��z\n�g��r��u=��!zCţ����e#��nj��\0`^;=E�*@�y�% ��LQe����2�A�1,��C�ix�t����G�]q�O(����\n�V9dr�D'5@x\$�r6��;\"ǣ���7�\0M0ņH_#�c�pn>��<aa�q@g�2��lm-��������8��?8��7p����>��ji���N�\$#E/�0��s\n��B\r�*��z���oyn[Ι�� 6�a����g8�qC��⼜�I��rNF�ȫ�1��70�����/i(�B�0����Z��(��+S�J�,���91/Y+jxӱF���A��k�f�Jee\r�Cͳrz��m���h@9��O�� ؝���GK�Ad���OH���=���<&`��K�PA�!WO;-�X�L��m��Kz�7-e[u��p�q���o/�`�C����KX�f�i���Y7=�M�/�F�R�۔T�d��Y\"=`�1�k�1Տh�\r����f@N��z�(@������	h�\0�����I�}PJKr���pR`x������fo���(A��[��19�(&jo<��I@p	@��������,y�	nIs�^Ўѫ:Y��vc���؏9q.C��8�bW��V?��҅�9�\$u�@5#S(4Y���K���6�!��N6<��|v1���3ʊ:��!����`��M��l����f`�Z��J=��GX�Y)_l�А�T�)P��`�%��:�!Z\"lYS�Uؤ(��Y1Z�니rv)F`�K~=Y>���S���c����!l���D����BrF\$��RA:�\\�P�4�V�R6<�O�S�_BCS+����'V��2T#Lc�F�NBD%�G�W�nR�S����I��\n'k�0���O��Ў����8rݯAS�?��xm��yv���a�b��Ͱ�,��ЅA������]pJ\\\\�Xi����Eu��B)�����Z@Ώ \"��gg0{��n��'APR��٨v�~�0R�w쀱\"�������H�J���Ζ�\\�\r}i?��Ғ:��2���g��{I�3)��B��͙Z�s��`.�#2�vt��X�IGU>`)�%���(|�f<Κ_�ޯ���_G�<��_ ˟������[:�6G8��l�#J(��JC���`���wF�w\"b�!,��!�r�@�K(���\n@AsV��S�ֹ�4�_\ns٠eڋj��)&�3�{��k���Q���G�c��X^�L{�C\n�m����A��D��1O?(��(�����2\"UL��+#o��@���X�\0�٭���^n_p�eQ˙X}%��*��e�m�{�GN��Xl�q�]R\\Z�v!�) ���xd΀,�cK��鮇�m���I~�����K�{+��Gݥ�=@Q��,1!aEOc��#6<u��rB�\n�Ȳ��dH�t����	�{C�<x3���H��1��K�wB�\0��u����'ӆQ�^���򕥂�i�rRv�Vɷ�lS�.O)����[��xS�t���c)���k�B��+��v���B��w�.�wC���2���2d�.H��p+a\\H��[�\$}nNN7��H�.�S\r�ȒT���w�	*H�g\\��\$�,�:KBOx��>����5�����Ӷ����u2��n���`��Yq�D���xwMB�n�2>���G�ڄ����YaK�w(2`����w����1m�-:�&LD8�U��8l��\\<���	��z�a����:,��K'�%7:����M����U[���*;K���j�;/wG���\n���^�eV'��,��;��B6�G�1��OKW����(i�X\np��Cکc6�^��㷀=�^ûcQ��Rp`\$	�D(\0D�>{��ET�c��I\r{����\$o�R	�ZZ�4*��??�+j���n��Q`����X�3�	\$���M�\n׉w�\"d�W���~@�'�I�᭫�0+-��w�����y�6�vȽ'�Ԇ:Y)Y0\0�*)?'��Ǟv����fI�\n���z�9�.�b��!�c�E�[��F麙ks�}��Bv�g�5�V���,)J\$��j�Z�J�\$�Y��ח9�\0�\n����.^J��ڋ�b��mI0:g��������˗ATP�I�]~!��;D�����	�z��<P�Q>�m���`��?%Y��T\n\0D\0�\0'���H@0`�<׭�10�(�m�-��ɞ7A\0�~�~ꁡĤ?t�hє.w�%)0	#c����\"�c����jfW��\0\0p��C���kC��8��85+i:��[�8�b��l�[\"����5S�y\0�����*�Q��6V�s�9��7!�;\"��c�)�O�Q,��Ա��\r�7�,*�0�aQ�u?�_C|�������R(o(��<j(��Tv��\r|_\"�3��m��S7D�!׸�h�|���(�&�@:��	\"-ގ��&Mu;�,�bк=p�>A6ɭ���7���- WW9�O,�o'�v2�<�3\0���h��@`� 3TX�Ϛ|�\"FC_��~x����`��'f�Q-4�����/�`'���=A�\$>��`P��_G(���E���&/J�I�v�'�m餧zpޞFo�	�/[��i�؋�G*���y�(���<���7q�Y�.�眪��B���\r�l�r\nUnƧ��T>�������	�Q���_�|����K��8�ډ�e��_��xz�x�L���p14��d����U#4t�K���\$�!����p�w�����Zx���_�����i5T?}��C�{�����h/Gzj\$.B�Ҩ�=#�Ϗ|���*����I���w/��a�x`*��*���]����>a?'}FJS���ԖA0��'������ʟ�0:63���л��n'��U/�r�|=slb0��\0W��rB�ʤ���@T��~\$����H�����	��D\\���-���(��ᩖB��M���z+�%�(��i��㹃�I���5/�.y/���\$�{Q}p�ܻdI�\\�Վ�B�\0V0�B�9�{T\$n�8\$Z�e�Pĳ���%9�&���V��b�x}g\"%h���*ٸvOw�˾�/�o�L,���=��V��5Bg� ϶�3��>�~�`\nxi�\"��v@������nף�ϳyac�G�'%[��4`n��47!5�ހr����ɉ��>z�(Y�t��0���V���P�ZXT`2�~Cl���[o�n�t8jB\0d�\0000��V��g�����@V!�h\0006d<���=[�W�����f�@pb��a��ټ�s;���G<�~a��?�N�L����\"(���?�%�x#�7�|S��O�Ɠ)�B4��+��*�!��)6#�+?'���(X�����JO\0��");
    } else {
        header("Content-Type: image/gif");
        switch ($_GET["file"]) {
            case "plus.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0!�����M��*)�o��) q��e���#��L�\0;";
                break;
            case "cross.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0#�����#\na�Fo~y�.�_wa��1��J�G�L�6]\0\0;";
                break;
            case "up.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����MQN\n�}��a8�y�aŶ�\0��\0;";
                break;
            case "down.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����M��*)�[W�\\��L&ٜƶ�\0��\0;";
                break;
            case "arrow.gif":
                echo "GIF89a\0\n\0�\0\0������!�\0\0\0,\0\0\0\0\0\n\0\0�i������Ӳ޻\0\0;";
                break;
        }
    }
    exit;
}

if (isset($_GET["script"]) && $_GET["script"] == "version") {
    $r = file_open_lock(get_temp_dir() . "/adminer.version");
    if ($r) {
        file_write_unlock($r, serialize([
            "signature" => $_POST["signature"] ?? '',
            "version" => $_POST["version"] ?? ''
        ]));
    }
    exit;
}

global $c, $g, $l, $Ib, $Pb, $Zb, $m, $Cc, $Hc, $ba, $Yc, $z, $a, $qd, $le, $Qe, $fg, $Mc, $T, $Ng, $Tg, $ah, $fa;

if (!isset($_SERVER["REQUEST_URI"])) {
    $_SERVER["REQUEST_URI"] = $_SERVER["ORIG_PATH_INFO"] ?? '';
}
if (!strpos($_SERVER["REQUEST_URI"] ?? '', '?') && ($_SERVER["QUERY_STRING"] ?? '') !== "") {
    $_SERVER["REQUEST_URI"] .= "?$_SERVER[QUERY_STRING]";
}
if (isset($_SERVER["HTTP_X_FORWARDED_PREFIX"])) {
    $_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_FORWARDED_PREFIX"] . $_SERVER["REQUEST_URI"];
}

$ba = (isset($_SERVER["HTTPS"]) && strcasecmp($_SERVER["HTTPS"], "off")) || ini_bool("session.cookie_secure");

@ini_set("session.use_trans_sid", "0");

if (!defined("SID")) {
    session_cache_limiter("");
    session_name("adminer_sid");
    $Ge = [0, preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"] ?? ''), "", $ba];
    if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
        $Ge[] = true;
    }
    call_user_func_array('session_set_cookie_params', $Ge);
    session_start();
}

remove_slashes([&$_GET, &$_POST, &$_COOKIE], $tc);

if (get_magic_quotes_runtime()) {
    set_magic_quotes_runtime(0);
}

@set_time_limit(0);
@ini_set("zend.ze1_compatibility_mode", "0");
@ini_set("precision", "15");

$qd = [
    'en' => 'English',
    'ar' => 'العربية',
    'bg' => 'Български',
    'bn' => 'বাংলা',
    'bs' => 'Bosanski',
    'ca' => 'Català',
    'cs' => 'Čeština',
    'da' => 'Dansk',
    'de' => 'Deutsch',
    'el' => 'Ελληνικά',
    'es' => 'Español',
    'et' => 'Eesti',
    'fa' => 'فارسی',
    'fi' => 'Suomi',
    'fr' => 'Français',
    'gl' => 'Galego',
    'he' => 'עברית',
    'hu' => 'Magyar',
    'id' => 'Bahasa Indonesia',
    'it' => 'Italiano',
    'ja' => '日本語',
    'ko' => '한국어',
    'lt' => 'Lietuvių',
    'ms' => 'Bahasa Melayu',
    'nl' => 'Nederlands',
    'no' => 'Norsk',
    'pl' => 'Polski',
    'pt' => 'Português',
    'pt-br' => 'Português (Brazil)',
    'ro' => 'Limba Română',
    'ru' => 'Русский',
    'sk' => 'Slovenčina',
    'sl' => 'Slovenski',
    'sr' => 'Српски',
    'ta' => 'த‌மிழ்',
    'th' => 'ภาษาไทย',
    'tr' => 'Türkçe',
    'uk' => 'Українська',
    'vi' => 'Tiếng Việt',
    'zh' => '简体中文',
    'zh-tw' => '繁體中文',
];

function get_lang() {
    global $a;
    return $a;
}

function lang($w, $ce = null) {
    if (is_string($w)) {
        $Te = array_search($w, get_translations("en"));
        if ($Te !== false) {
            $w = $Te;
        }
    }
    global $a, $Ng;
    $Mg = ($Ng[$w] ?? $w);
    if (is_array($Mg)) {
        $Te = ($ce == 1 ? 0 : 
               ($a == 'cs' || $a == 'sk' ? ($ce && $ce < 5 ? 1 : 2) : 
               ($a == 'fr' ? (!$ce ? 0 : 1) : 
               ($a == 'pl' ? ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2) : 
               ($a == 'sl' ? ($ce % 100 == 1 ? 0 : ($ce % 100 == 2 ? 1 : ($ce % 100 == 3 || $ce % 100 == 4 ? 2 : 3))) : 
               ($a == 'lt' ? ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : ($ce % 10 > 1 && $ce / 10 % 10 != 1 ? 1 : 2)) : 
               ($a == 'bs' || $a == 'ru' || $a == 'sr' || $a == 'uk' ? 
                   ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : 
                    ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2)) : 1)))))));
        $Mg = $Mg[$Te];
    }
    $ta = func_get_args();
    array_shift($ta);
    $zc = str_replace("%d", "%s", $Mg);
    if ($zc != $Mg) {
        $ta[0] = format_number((float)$ce);
    }
    return vsprintf($zc, $ta);
}

function switch_lang(): void {
    global $a, $qd;
    echo "<form action='' method='post'>\n<div id='lang'>",
         lang(19) . ": " . html_select("lang", $qd, $a, "this.form.submit();"),
         " <input type='submit' value='" . lang(20) . "' class='hidden'>\n",
         "<input type='hidden' name='token' value='" . get_token() . "'>\n";
    echo "</div>\n</form>\n";
}

if (isset($_POST["lang"]) && verify_token()) {
    cookie("adminer_lang", $_POST["lang"]);
    $_SESSION["lang"] = $_POST["lang"];
    $_SESSION["translations"] = [];
    redirect(remove_from_uri());
}

$a = "en";
if (isset($qd[$_COOKIE["adminer_lang"] ?? ''])) {
    cookie("adminer_lang", (string)$_COOKIE["adminer_lang"]);
    $a = $_COOKIE["adminer_lang"];
} elseif (isset($qd[$_SESSION["lang"] ?? ''])) {
    $a = $_SESSION["lang"];
} else {
    $ka = [];
    preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~', str_replace("_", "-", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? '')), $Ed, PREG_SET_ORDER);
    foreach ($Ed as $D) {
        $ka[$D[1]] = (float)($D[3] ?? 1);
    }
    arsort($ka);
    foreach ($ka as $_ => $H) {
        if (isset($qd[$_])) {
            $a = $_;
            break;
        }
        $_ = preg_replace('~-.*~', '', $_);
        if (!isset($ka[$_]) && isset($qd[$_])) {
            $a = $_;
            break;
        }
    }
}

$Ng = $_SESSION["translations"] ?? [];
if (($_SESSION["translations_version"] ?? 0) != 2138479313) {
    $Ng = [];
    $_SESSION["translations_version"] = 2138479313;
}

function get_translations(string $pd): array {
    $f = '';
    switch ($pd) {
        case "en":
            $f = "A9D�y�@s:�G��(�ff�����	��:�S���a2\"1�..L'�I��m�#�s,�K��OP#I�@%9��i4�o2ύ�����,9�%�P�b2��a��r\n2�NC�(�r4��1C`(�:Eb�9A�i:�&㙔�y��F���Y��\r�\n� 8Z�S=\$A����`�=�܌���0�\n��dF�	��n:Zΰ)��Q���mw����O��mfpQ�΂��q��a�į�#q��w7S�X3���=�O��ztR-�<����i���gKG4�n����r&r�\$-��Ӊ�����KX�9,�8�7�o��)�*���/�h��/Ȥ\n�9��8�Ⳉ�E\r�P�/�k��)��\\# ڵ����)jj8:�0�c�9�i}�QX@;�B#�I�\0x����C@�:�t����\$�~���8^�ㄵ�C ^(�ڳ��p̳�M�^�|�8�(Ʀ�k�Q+�;�:�hKN ����2c(�T1�����0@�B��78o�J��C�:��rξ��6%�x�<�\r=�6�m�p:��ƀ٫ˌ3#�CR6#N)�4�#�u&�/���3�#;9tCX�4N`�;���#C\"�%5����£�\"�h�z7;_q�CcB�����\n\"`@�Y��d���MTTR}W����y�#!�/�+|�QFN��yl@�2�J��_�(�\"��~b��h��(e �/���P�lB\r�Cx�3\r��P&E��*\r��d7(��NIQ�makw.�Iܵ���{9Z\r�l׶ԄI2^߉Fۛ/n���om���/c��4�\"�)̸�5��pAp5����Qjׯ�6��p��P*1n�}C�c�������K�s�Tr�1L�\0D(��b�єu!�\nv�4�#\$�������pܔ%P�G=Ds�B���k�x��1̳<�5ͳ|�N����i�5��@���E֫�ǈ!��\\�U�5d�&΍�L5��\"\$h��i�<��2;7N�Q�J��_(*�!!���F��;qE�M*�bȐ{(�4��``E��ߞ� ���h�t��\0����E���\nI\$NX�\"P��q�\\����dȹ�\$1L��LI��/A��5�[̈́E\r���\\Lq�G�j<BjII������j��\0P\"�H��7��\n�\"�rC���af���fTI!�&��C����8�d�=Wf�U�i�!<)�FL]R&�p�dRz��f2�Ӟ��INpr]�l���cICVcg0̑�@���\nlfH�&H�0T�W�z^%� �d�5ZNˠB�(�)���xNT(@�(\n� �\"P�h�/Xk�>�f�G�y�����c]��s�JCy�#���s��R�1��>����K��i����T~�r>0i=.��*�h�9��p�`��@�(+ ��yϹ�3����5��af:�p�&b�Y=�(��[�j��(3���-��Vt���po�b��S�R�I�-\\��J�´�T�P�G����d��ϳ��AC�J���2��D_��d&��3��YB�h]g�ؠteP�PF(��U��D���/!D�p�����4��Rў�HwV�@��@ ���H���o�d�q�\"����/!����`�\"�\$&��l�aORM������U��\$MmIE@\\o�~m81�@A��E�0��)8�`X�g�ny�`3:J���v�\n�L.�~)�������,�h���}�!8#������#��ݑxJ����Hhp��������%˖�A���㸥�&d\r�N�#�1g,�M���>�m	&t��}R��9�=���mI#�WBg�ӟ�I�7e�XF)�4J\r�3'��/�4��TQ�O�0jL�?�t��B�sQ��~kem��_c���!t��|Yu�x�h��׫����>����Sq�UK�r������h��\r�d0�NIn�Ɯ��]��F��k''�TT�5=I 4�v�Ϥbُ[��-��6�zy���y���&D�߅3�6\r}�\$)]���{D�\r\\�p�O���\n�7N�ӓ��[^Mm�EI�K��[Kl�xƪҶ����rhmaM����aQ�i�sˬ����b�Є��-�3\$>�W�Q��y�*b_K�G�RG�޲偗��+W�J�v�F������ʮ��iU�5�wV�݄����sT�{0�J��\0���<*�~:����r���}d�^��/���УLñ|q���J�K{r��)3������9Hd�q���n/�9��%��yu��\$\\�9��\"�PdSލ�6��`�S2��G�̠�BN�d����#��������=;����d���O�l>�H���\r�BZ4�4/ �7���l�\0o��\0��Q�M��`	D_\$N��b��Yɔ�>4�z�d�� �\$�.���2\n�Ic,�\r��4��<7��o2.\"�	pM���. �PPW�`\r����S�B���\r�V�\0�`��\"F��0l��.��m\"�(b���`�F�\n���Z�5��9�P��\"%��.;��0�H��\"c�3����Uf�	��\r0������6�g�Т3җe�[�X���N̮�@`DB: �DB�\n��'\n�[B�԰Z��RSe�QQM��MH���R�i\"Պ����0vqi�2�d���\nfXf�^��.�'��*�����+��h��@	�L�\r�ڱ�aE�\0� #N�����*%�S�vG�1�e�\nOG�l`��c����=H�Tn�߱��@�BfX�F�\"�-K�2������d\$�\"�\"�2#\"i��\"�\\";
            break;
        case "ar":
            $f = "�C�P���l*�\r�,&\n�A����(J.��0Se\\�\r��b�@�0�,\nQ,l)���µ���A��j_1�C�M��e��S�\ng@�Og����X�DM�)��0��cA��n8�e*y#au4�� �Ir*;rS�U�dJ	}���*z�U�@��X;ai1l(n������[�y�d�u'c(��oF����e3�Nb���p2N�S��ӳ:LZ�z�P�\\b��u�.�[�Q`u	!��Jy��&2��(gT��SњM�x�5g5�K�K�¦�����0ʀ(�7\rm8�7(�9\r��f\"7�^��pL\n7A�*�BP��<7cp�4����Y�+dHB&���O��̤��\\�<i���H��2�lk4�����ﲠƗ\ns W���HBƯ��(�z �>����%�t�\$(�R�\n�v�-��������R���0ӣ��et�@2�� ��k� ��4�x荶��I�#��C�X@0ѭӄ0�m(�4���0�ԃ����`@T@�2���D4���9�Ax^;؁p�D�pT3��(��m^9�xD��lҽC46�Q\0��|��%��[F��ڏ����t�wk��j�P���Ӭ� ��m~�s���Pi�����n�E���9\r�PΎ�\$ؠ#�����r��8#��:�Yc���(r�\"W�6Rc��6�+�)/w�I(J����'	j?��ɩ�U�H��E*��߂]Z\r�~�F�d�i�	�[�r�(�}���B6n66��61�#s�-��p@)�\"bԇ����d��l�1\\��]�������1K���ű�\"�J\\�n�����S_7k����!��ٖN;�^��qj��Z��1̃Ň��W4O=7x�\" ��&��B9�`�4�J7��0�E��µɺ��ț�B���\\p����MS�6n\r�x��u��9}c�OP �,d(��M�(`���r,�\0C\naH#B��#\rO�9E�N\nS�-�����L��il]I��B���F0��9��\0�Q�Y��Ɨ��)�@�o'اC8 Q+ ƈP�dQ��Ыur�Ø\"�9\nF,�1Ow��C��PRH��\\C:5�K�/Ee��'Xn�\n�&a5R���C��V<\0ҭ�\$���\\+�x���X��c,�܁�r�Y�=\r��>�V��	!�8�ڳ��`�B�URƍ�!�n)0���L�]ԈRR~ܚLoGP�(HBI�T\r�L��B8ln���p�e;!�?)G� _�p~��*szm&Y2�9*K;e<����[|*���æ@�'�,E�(8�����0d5�Ar�?J�YMD�!�.����&���`XҖz�ߩ14�xw�=㺕�LӡĔ�Bl*I�0e��XB��ie���2�Qϑ�Gt�ϲ�QΡ'���0�F�ɟQ���u&�Ct�7��[�zԘfA�%IeoVt�~��tRU�li�\rd���`�T!dE����Ljj;K\r4���PQ!6i.R�е��㈆����\$�B��ס���5\$�Н�@��@\$;V��6��c��@�LIo�0j<\rj��P()��\0�\"��U����iQ�8�Ī���NC�II�{�6��p \n�@\"�@U�\"���yۼ4O���+�bˉ�0��(6JbM�^G([R���K.̎��|\\�U{\$tCY��˚\$/��[*(4���\ngi���v,�_f�4st����D���ĳ���`7v�]�9�cLJ���:����QC���n��ŧ'��K�A�JI���XJ�9rĝ��ܣ@�� ���5��a�w(4���LNsk�b\r@�5��M@��L4��}Ee\$g�L2��1H\"%\0*>G����35��;�����	Id4R;�R�X6����}T�����P@����02ac��]b�vqJ\r}��=pN�9؉L�;U�-�\",P�)�X�\n)>�\\�>6��R�*y�A��*2��sG�-䤕8T\n�!��AY�0iRZ�)@�H�܉#�8K�B(��O��\"Tx \"�k�7t^�{#%'�XBg��K�VP�O����~c�� �S�&^Y|:e��B�y8�U���\\6G���ᙃ���H�HD>&��{izo:����L�@e���\0��TR�A܎#������ĩ1�I���01�PBW��'�?����ڗ;��%����(q�\r�*]��Y��Z�U(.�敱VA�9B��\niQ	��%\rϐ�T���p'U�Gx��!�-'��ư�c�=f���0�{��Dݬ;�>����#��|�13�{���G��������{��`�,�x�����\\;^��-� \n�\"���t�W��ǔ�écO����k��4H�O��O�_�Z�B��mL�,*+\"V�MX�\"���@\0P:�� @RJ�I���O��J]�H^LV��D\$)�qd�c�>��H�0DuJ�̀�PAxy	�\0f\n�'�d��Ķ�'|����D�\"���VNJ]�P��>L��b��N�ݭ|�C�װ�+K�#�q\0�t'���0�����	p���\rc\r�R��o��	��-d1>#��0���_#���8�fl�HVB�q\n!c��H�\n�#�����侌�Imf����pd��T����(a�q�+�ڢ@x��Bj����O�l�T?/:֍l;����qp\$�Bc����\"��y��`P�w�������60���Ѥ�\"&p��\\.M����O��d�O����s����E�\"aG�:m��E�;FH�ڌq�2/��������·'f ���r	�{�!\0���q�#��������#�I\"�Dɰ�dpf@��0��\\�\$��i�!�]�p+���&���5'f�(q�\$�'�Ŀ\$nr�i��(Km�!�u\0���Ļ\"��L�+��.GlR:��Nj�fT������f�w�P��M&l~e,\r��G��&�(i&Hs��\r<\"nL�c ��VJ�Z)�c*(`�`�{`�\rd0@�@gFx7��\r��\r �}eP&`�����ж��@��\n���pBh�4����b����mb:c�\$o>���aϠ��P	�I4��aNq\$��nJ�@�2\r�\0E!L<��AK/ġ�f	�޶��V�B8.�=�t=��%*n�+N-��!�/�Xj��&Brxg-��\$��&l\"-0C	�J/�AT#����B �B�f4CI6\n�\r�����)�L2��Cg��]\"���&�.�+E�D!�l��ϼ\$&�hAT)�/\0�74v{�~qq܅s�8 \n�2��\r���2�y�xE�&�j9C��\$�@����(̅-���P�b�tK�'0\n ��d4pOdp&\r�d �	\0t	��@�\n`";
            break;
        default:
            $f = "";
    }
    
    $Ng = [];
    foreach (explode("\n", lzw_decompress($f)) as $X) {
        $Ng[] = (strpos($X, "\t") ? explode("\t", $X) : $X);
    }
    return $Ng;
}

if (!$Ng) {
    $Ng = get_translations($a);
    $_SESSION["translations"] = $Ng;
}         
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
    }
}