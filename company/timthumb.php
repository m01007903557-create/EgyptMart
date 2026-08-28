<?php
/**
 * TimThumb - PHP Image Resizing & Cropping
 * نسخة محدثة ومحسنة لـ PHP 8.3
 * 
 * Original by Ben Gillbanks and Mark Maunder
 * Updated for PHP 8.3 compatibility and security
 * 
 * @version 3.0.0
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // إيقاف عرض الأخطاء في الإنتاج

// ==================== التهيئة ====================
define('TIMTHUMB_VERSION', '3.0.0');

// تحميل ملف الإعدادات المخصص إذا وجد
if (file_exists(dirname(__FILE__) . '/timthumb-config.php')) {
    require_once 'timthumb-config.php';
}

// ==================== الإعدادات الأساسية ====================
defined('DEBUG_ON') or define('DEBUG_ON', false);
defined('DEBUG_LEVEL') or define('DEBUG_LEVEL', 1);
defined('MEMORY_LIMIT') or define('MEMORY_LIMIT', '128M');
defined('BLOCK_EXTERNAL_LEECHERS') or define('BLOCK_EXTERNAL_LEECHERS', false);
defined('DISPLAY_ERROR_MESSAGES') or define('DISPLAY_ERROR_MESSAGES', false);

// إعدادات جلب الصور والتخزين المؤقت
defined('ALLOW_EXTERNAL') or define('ALLOW_EXTERNAL', false);
defined('ALLOW_ALL_EXTERNAL_SITES') or define('ALLOW_ALL_EXTERNAL_SITES', false);
defined('FILE_CACHE_ENABLED') or define('FILE_CACHE_ENABLED', true);
defined('FILE_CACHE_TIME_BETWEEN_CLEANS') or define('FILE_CACHE_TIME_BETWEEN_CLEANS', 86400);
defined('FILE_CACHE_MAX_FILE_AGE') or define('FILE_CACHE_MAX_FILE_AGE', 86400);
defined('FILE_CACHE_SUFFIX') or define('FILE_CACHE_SUFFIX', '.timthumb.txt');
defined('FILE_CACHE_PREFIX') or define('FILE_CACHE_PREFIX', 'timthumb_');
defined('FILE_CACHE_DIRECTORY') or define('FILE_CACHE_DIRECTORY', './cache/');
defined('MAX_FILE_SIZE') or define('MAX_FILE_SIZE', 10485760); // 10MB
defined('CURL_TIMEOUT') or define('CURL_TIMEOUT', 20);
defined('WAIT_BETWEEN_FETCH_ERRORS') or define('WAIT_BETWEEN_FETCH_ERRORS', 3600);

// إعدادات التخزين المؤقت في المتصفح
defined('BROWSER_CACHE_MAX_AGE') or define('BROWSER_CACHE_MAX_AGE', 864000);
defined('BROWSER_CACHE_DISABLE') or define('BROWSER_CACHE_DISABLE', false);

// أبعاد الصورة الافتراضية
defined('MAX_WIDTH') or define('MAX_WIDTH', 2500);
defined('MAX_HEIGHT') or define('MAX_HEIGHT', 2500);
defined('NOT_FOUND_IMAGE') or define('NOT_FOUND_IMAGE', '');
defined('ERROR_IMAGE') or define('ERROR_IMAGE', '');
defined('PNG_IS_TRANSPARENT') or define('PNG_IS_TRANSPARENT', false);
defined('DEFAULT_Q') or define('DEFAULT_Q', 85);
defined('DEFAULT_ZC') or define('DEFAULT_ZC', 1);
defined('DEFAULT_F') or define('DEFAULT_F', '');
defined('DEFAULT_S') or define('DEFAULT_S', 0);
defined('DEFAULT_CC') or define('DEFAULT_CC', 'ffffff');
defined('DEFAULT_WIDTH') or define('DEFAULT_WIDTH', 100);
defined('DEFAULT_HEIGHT') or define('DEFAULT_HEIGHT', 100);

// ضغط PNG (معطل افتراضياً)
defined('OPTIPNG_ENABLED') or define('OPTIPNG_ENABLED', false);
defined('OPTIPNG_PATH') or define('OPTIPNG_PATH', '/usr/bin/optipng');
defined('PNGCRUSH_ENABLED') or define('PNGCRUSH_ENABLED', false);
defined('PNGCRUSH_PATH') or define('PNGCRUSH_PATH', '/usr/bin/pngcrush');

// المواقع المسموح بها للصور الخارجية
$ALLOWED_SITES = $ALLOWED_SITES ?? [
    'flickr.com',
    'staticflickr.com',
    'picasa.com',
    'img.youtube.com',
    'upload.wikimedia.org',
    'photobucket.com',
    'imgur.com',
    'imageshack.us',
    'tinypic.com',
    'egyptmart.shop',
    'arab-mart.com'
];

// ==================== بدء التشغيل ====================
try {
    $timthumb = new TimThumbImproved();
    $timthumb->handleRequest();
} catch (Exception $e) {
    TimThumbImproved::displayError('خطأ في النظام: ' . $e->getMessage());
}

/**
 * كلاس TimThumb المحسن
 */
class TimThumbImproved {
    protected string $src = '';
    protected bool $is404 = false;
    protected string $docRoot = '';
    protected ?string $lastURLError = null;
    protected string $localImage = '';
    protected int $localImageMTime = 0;
    protected ?array $url = null;
    protected string $myHost = '';
    protected bool $isURL = false;
    protected string $cacheFile = '';
    protected array $errors = [];
    protected array $toDeletes = [];
    protected string $cacheDirectory = '';
    protected float $startTime = 0;
    protected float $lastBenchTime = 0;
    protected bool $cropTop = false;
    protected string $salt = '';
    protected int $fileCacheVersion = 2;
    protected string $filePrependSecurityBlock = "<?php die('Access denied'); //";
    protected static int $curlDataWritten = 0;
    protected static $curlFH = null;
    protected array $imageFilters = [];

    public function __construct() {
        $this->startTime = microtime(true);
        date_default_timezone_set('UTC');
        $this->debug(1, "بدء طلب جديد من " . $this->getIP());
        
        $this->calcDocRoot();
        $this->salt = md5((string)@filemtime(__FILE__) . $this->fileCacheVersion);
        
        $this->initCacheDirectory();
        $this->cleanCache();
        
        $this->myHost = preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'] ?? '');
        $this->src = $this->param('src', '');
        $this->url = parse_url($this->src);
        
        if (strlen($this->src) <= 3) {
            throw new Exception('لم يتم تحديد صورة');
        }
        
        $this->checkHotlinking();
        $this->determineImageType();
        $this->generateCacheFilename();
        
        $this->initImageFilters();
    }

    protected function initImageFilters(): void {
        if (function_exists('imagefilter') && defined('IMG_FILTER_NEGATE')) {
            $this->imageFilters = [
                1 => [IMG_FILTER_NEGATE, 0],
                2 => [IMG_FILTER_GRAYSCALE, 0],
                3 => [IMG_FILTER_BRIGHTNESS, 1],
                4 => [IMG_FILTER_CONTRAST, 1],
                5 => [IMG_FILTER_COLORIZE, 4],
                6 => [IMG_FILTER_EDGEDETECT, 0],
                7 => [IMG_FILTER_EMBOSS, 0],
                8 => [IMG_FILTER_GAUSSIAN_BLUR, 0],
                9 => [IMG_FILTER_SELECTIVE_BLUR, 0],
                10 => [IMG_FILTER_MEAN_REMOVAL, 0],
                11 => [IMG_FILTER_SMOOTH, 0],
            ];
        }
    }

    public function handleRequest(): void {
        $this->checkErrors();
        
        if ($this->tryBrowserCache()) {
            exit(0);
        }
        
        $this->checkErrors();
        
        if (FILE_CACHE_ENABLED && $this->tryServerCache()) {
            exit(0);
        }
        
        $this->checkErrors();
        $this->processImage();
        $this->checkErrors();
        exit(0);
    }

    protected function checkHotlinking(): void {
        if (!BLOCK_EXTERNAL_LEECHERS) {
            return;
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referer)) {
            return;
        }
        
        if (!preg_match('/^https?:\/\/(?:www\.)?' . preg_quote($this->myHost, '/') . '(?:$|\/)/i', $referer)) {
            $this->serveHotlinkBlockImage();
            exit(0);
        }
    }

    protected function serveHotlinkBlockImage(): void {
        $imgData = base64_decode("R0lGODlhUAAMAIAAAP8AAP///yH5BAAHAP8ALAAAAABQAAwAAAJpjI+py+0Po5y0OgAMjjv01YUZOGplhWXfNa6JCLnWkXplrcBmW+spbwvaVr/cDyg7IoFC2KbYVC2NQ5MQ4ZNao9Ynzjl9ScNYpnebDULB3RP6JuPuaGfuuV4fumf8PuvqFyhYtjdoeFgAADs=");
        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen($imgData));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header("Pragma: no-cache");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time()));
        echo $imgData;
    }

    protected function determineImageType(): void {
        if (preg_match('/^https?:\/\//i', $this->src)) {
            $this->debug(2, "طلب صورة خارجية: " . $this->src);
            $this->isURL = true;
            
            if (!ALLOW_EXTERNAL) {
                throw new Exception('غير مسموح بجلب الصور من مواقع خارجية');
            }
            
            if (!ALLOW_ALL_EXTERNAL_SITES) {
                $this->checkAllowedSites();
            }
        } else {
            $this->debug(2, "طلب صورة داخلية: " . $this->src);
            $this->localImage = $this->getLocalImagePath($this->src);
            
            if (!$this->localImage) {
                $this->debug(1, "لم يتم العثور على الصورة المحلية: {$this->src}");
                $this->set404();
                throw new Exception('لم يتم العثور على الصورة');
            }
            
            $this->debug(1, "مسار الصورة المحلية: {$this->localImage}");
            $this->localImageMTime = @filemtime($this->localImage);
        }
    }

    protected function checkAllowedSites(): void {
        global $ALLOWED_SITES;
        $allowed = false;
        
        foreach ($ALLOWED_SITES as $site) {
            $host = $this->url['host'] ?? '';
            $siteDot = '.' . ltrim($site, '.');
            
            if (strtolower($host) === strtolower($site) || 
                strtolower(substr($host, -strlen($siteDot))) === strtolower($siteDot)) {
                $this->debug(3, "المضيف {$host} متطابق مع {$site}");
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            throw new Exception('غير مسموح بجلب الصور من هذا الموقع');
        }
    }

    protected function generateCacheFilename(): void {
        $cachePrefix = ($this->isURL ? '_ext_' : '_int_');
        
        if ($this->isURL) {
            $arr = $_GET;
            unset($arr['src']);
            ksort($arr);
            $hash = md5($this->salt . serialize($arr) . $this->fileCacheVersion);
        } else {
            $hash = md5($this->salt . $this->localImageMTime . serialize($_GET) . $this->fileCacheVersion);
        }
        
        $this->cacheFile = $this->cacheDirectory . '/' . FILE_CACHE_PREFIX . $cachePrefix . $hash . FILE_CACHE_SUFFIX;
        $this->debug(2, "ملف الكاش: " . $this->cacheFile);
    }

    protected function initCacheDirectory(): void {
        if (FILE_CACHE_DIRECTORY) {
            $this->cacheDirectory = rtrim(FILE_CACHE_DIRECTORY, '/');
            
            if (!is_dir($this->cacheDirectory)) {
                if (!@mkdir($this->cacheDirectory, 0755, true)) {
                    throw new Exception('لا يمكن إنشاء مجلد الكاش');
                }
            }
            
            $indexFile = $this->cacheDirectory . '/index.html';
            if (!is_file($indexFile)) {
                @file_put_contents($indexFile, '');
            }
        } else {
            $this->cacheDirectory = sys_get_temp_dir();
        }
        
        $this->debug(2, "مجلد الكاش: " . $this->cacheDirectory);
    }

    protected function calcDocRoot(): void {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        if (defined('LOCAL_FILE_BASE_DIRECTORY')) {
            $docRoot = LOCAL_FILE_BASE_DIRECTORY;
        }
        
        if (empty($docRoot) && isset($_SERVER['SCRIPT_FILENAME'])) {
            $docRoot = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
        }
        
        $this->docRoot = rtrim($docRoot, '/');
        $this->debug(3, "المجلد الجذر: " . $this->docRoot);
    }

    protected function getLocalImagePath(string $src): string {
        $src = ltrim($src, '/');
        
        if (empty($this->docRoot)) {
            $file = basename($src);
            if (is_file($file)) {
                return $this->realpath($file);
            }
            return '';
        }
        
        // البحث في المجلد الجذر
        $path = $this->docRoot . '/' . $src;
        if (is_file($path)) {
            $real = $this->realpath($path);
            if (str_starts_with($real, $this->docRoot)) {
                return $real;
            }
        }
        
        // البحث في المسار المطلق
        $absolute = $this->realpath('/' . $src);
        if ($absolute && is_file($absolute) && str_starts_with($absolute, $this->docRoot)) {
            return $absolute;
        }
        
        // البحث في المسارات الفرعية
        $base = $this->docRoot;
        $scriptPath = str_replace($this->docRoot, '', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $directories = explode('/', ltrim(dirname($scriptPath), '/'));
        
        foreach ($directories as $dir) {
            $base .= '/' . $dir;
            $testPath = $base . '/' . $src;
            if (is_file($testPath)) {
                $real = $this->realpath($testPath);
                if (str_starts_with($real, $this->docRoot)) {
                    return $real;
                }
            }
        }
        
        return '';
    }

    protected function realpath(string $path): string {
        // إزالة المسارات النسبية
        $path = str_replace(['\\', '//'], '/', $path);
        $parts = explode('/', $path);
        $result = [];
        
        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($result);
            } elseif ($part !== '.' && $part !== '') {
                $result[] = $part;
            }
        }
        
        return '/' . implode('/', $result);
    }

    protected function tryBrowserCache(): bool {
        if (BROWSER_CACHE_DISABLE) {
            $this->debug(3, "تخزين المتصفح معطل");
            return false;
        }
        
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if (empty($ifModifiedSince)) {
            return false;
        }
        
        if (!is_file($this->cacheFile)) {
            return false;
        }
        
        $mtime = $this->localImageMTime ?: @filemtime($this->cacheFile);
        if (!$mtime) {
            return false;
        }
        
        $iftime = strtotime($ifModifiedSince);
        if ($iftime < 1) {
            return false;
        }
        
        if ($iftime >= $mtime) {
            $this->debug(3, "إرسال 304 Not Modified");
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            return true;
        }
        
        return false;
    }

    protected function tryServerCache(): bool {
        $this->debug(3, "فحص الكاش");
        
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        if ($this->isURL && filesize($this->cacheFile) < 1) {
            $fileAge = time() - @filemtime($this->cacheFile);
            if ($fileAge > WAIT_BETWEEN_FETCH_ERRORS) {
                $this->debug(3, "ملف الكاش الفارغ قديم، حذفه");
                @unlink($this->cacheFile);
                return false;
            } else {
                $this->set404();
                throw new Exception('حدث خطأ في جلب الصورة');
            }
        }
        
        if ($this->serveCacheFile()) {
            $this->debug(3, "تم تقديم الملف من الكاش");
            return true;
        }
        
        $this->debug(3, "فشل تقديم ملف الكاش، حذفه");
        @unlink($this->cacheFile);
        return false;
    }

    protected function processImage(): void {
        if ($this->isURL) {
            if ($this->param('webshot') && defined('WEBSHOT_ENABLED') && WEBSHOT_ENABLED) {
                $this->serveWebshot();
            } else {
                $this->serveExternalImage();
            }
        } else {
            $this->serveInternalImage();
        }
    }

    protected function serveInternalImage(): void {
        $this->debug(3, "معالجة صورة داخلية");
        
        if (empty($this->localImage)) {
            throw new Exception('لم يتم تحديد صورة محلية');
        }
        
        $fileSize = filesize($this->localImage);
        if ($fileSize > MAX_FILE_SIZE) {
            throw new Exception('حجم الملف أكبر من الحد المسموح');
        }
        
        if ($fileSize <= 0) {
            throw new Exception('حجم الملف غير صالح');
        }
        
        if ($this->processImageAndWriteToCache($this->localImage)) {
            $this->serveCacheFile();
        }
    }

    protected function serveExternalImage(): void {
        if (!preg_match('/^https?:\/\//i', $this->src)) {
            throw new Exception('رابط غير صالح');
        }
        
        $tempFile = tempnam($this->cacheDirectory, 'timthumb_');
        $this->toDeletes[] = $tempFile;
        $this->debug(3, "جلب صورة خارجية إلى $tempFile");
        
        if (!$this->fetchURL($this->src, $tempFile)) {
            @unlink($this->cacheFile);
            touch($this->cacheFile);
            $error = $this->lastURLError ?? 'خطأ غير معروف';
            throw new Exception("خطأ في جلب الصورة: $error");
        }
        
        $mimeType = $this->getMimeType($tempFile);
        if (!preg_match("/^image\/(?:jpe?g|png|gif|webp)$/i", $mimeType)) {
            $this->debug(3, "نوع الملف غير صالح: $mimeType");
            @unlink($this->cacheFile);
            touch($this->cacheFile);
            throw new Exception('الملف البعيد ليس صورة صالحة');
        }
        
        if ($this->processImageAndWriteToCache($tempFile)) {
            $this->debug(3, "تمت معالجة الصورة بنجاح");
            $this->serveCacheFile();
        }
    }

    protected function fetchURL(string $url, string $tempFile): bool {
        $this->lastURLError = null;
        $url = str_replace(' ', '%20', $url);
        
        if (function_exists('curl_init')) {
            return $this->fetchWithCurl($url, $tempFile);
        } else {
            return $this->fetchWithFileGetContents($url, $tempFile);
        }
    }

    protected function fetchWithCurl(string $url, string $tempFile): bool {
        $fp = fopen($tempFile, 'w');
        if (!$fp) {
            return false;
        }
        
        self::$curlFH = $fp;
        self::$curlDataWritten = 0;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => CURL_TIMEOUT,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; TimThumb/3.0)',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_WRITEFUNCTION => [$this, 'curlWrite']
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode == 404) {
            $this->set404();
        }
        
        fclose($fp);
        curl_close($ch);
        
        return $result !== false && self::$curlDataWritten > 0;
    }

    public static function curlWrite($ch, string $data): int {
        if (self::$curlFH) {
            $written = fwrite(self::$curlFH, $data);
            self::$curlDataWritten += $written;
            
            if (self::$curlDataWritten > MAX_FILE_SIZE) {
                return 0;
            }
            return $written;
        }
        return 0;
    }

    protected function fetchWithFileGetContents(string $url, string $tempFile): bool {
        $context = stream_context_create([
            'http' => [
                'timeout' => CURL_TIMEOUT,
                'user_agent' => 'Mozilla/5.0 (compatible; TimThumb/3.0)'
            ]
        ]);
        
        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            $error = error_get_last();
            $this->lastURLError = $error['message'] ?? 'خطأ غير معروف';
            
            if (str_contains($this->lastURLError, '404')) {
                $this->set404();
            }
            return false;
        }
        
        if (file_put_contents($tempFile, $data) === false) {
            return false;
        }
        
        return true;
    }

    protected function serveCacheFile(): bool {
        $this->debug(3, "تقديم ملف الكاش: {$this->cacheFile}");
        
        if (!is_file($this->cacheFile)) {
            throw new Exception('ملف الكاش غير موجود');
        }
        
        $fp = fopen($this->cacheFile, 'rb');
        if (!$fp) {
            throw new Exception('لا يمكن فتح ملف الكاش');
        }
        
        // تخطي كتلة الأمان
        fseek($fp, strlen($this->filePrependSecurityBlock), SEEK_SET);
        $imgType = fread($fp, 3);
        fseek($fp, 3, SEEK_CUR);
        
        if (ftell($fp) != strlen($this->filePrependSecurityBlock) + 6) {
            fclose($fp);
            @unlink($this->cacheFile);
            throw new Exception('ملف الكاش تالف');
        }
        
        $dataSize = filesize($this->cacheFile) - (strlen($this->filePrependSecurityBlock) + 6);
        $this->sendImageHeaders($imgType, $dataSize);
        
        fpassthru($fp);
        fclose($fp);
        return true;
    }

    protected function sendImageHeaders(string $mimeType, int $dataSize): void {
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $mime = $mimeMap[strtolower($mimeType)] ?? 'image/' . $mimeType;
        
        header('Content-Type: ' . $mime);
        header('Accept-Ranges: none');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Length: ' . $dataSize);
        
        if (BROWSER_CACHE_DISABLE) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()));
        } else {
            $expires = time() + BROWSER_CACHE_MAX_AGE;
            header('Cache-Control: max-age=' . BROWSER_CACHE_MAX_AGE . ', must-revalidate');
            header('Expires: ' . gmdate('D, d M Y H:i:s', $expires) . ' GMT');
        }
    }

    protected function processImageAndWriteToCache(string $imagePath): bool {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            throw new Exception('لا يمكن قراءة معلومات الصورة');
        }
        
        [$origWidth, $origHeight, $origType] = $imageInfo;
        $mimeType = $imageInfo['mime'];
        
        $this->debug(3, "نوع الصورة: $mimeType");
        
        if (!preg_match('/^image\/(?:gif|jpe?g|png|webp)$/i', $mimeType)) {
            throw new Exception('نوع الصورة غير مدعوم');
        }
        
        if (!function_exists('imagecreatetruecolor')) {
            throw new Exception('مكتبة GD غير مثبتة');
        }
        
        // معاملات الإدخال
        $newWidth = max(1, (int)abs($this->param('w', 0)));
        $newHeight = max(1, (int)abs($this->param('h', 0)));
        $zoomCrop = (int)$this->param('zc', DEFAULT_ZC);
        $quality = min(100, max(1, (int)abs($this->param('q', DEFAULT_Q))));
        $align = $this->cropTop ? 't' : $this->param('a', 'c');
        $filters = $this->param('f', DEFAULT_F);
        $sharpen = (bool)$this->param('s', DEFAULT_S);
        $canvasColor = $this->param('cc', DEFAULT_CC);
        $canvasTrans = (bool)$this->param('ct', '1');
        
        // أبعاد افتراضية
        if ($newWidth == 0 && $newHeight == 0) {
            $newWidth = DEFAULT_WIDTH;
            $newHeight = DEFAULT_HEIGHT;
        }
        
        // تحديد الأبعاد القصوى
        $newWidth = min($newWidth, MAX_WIDTH);
        $newHeight = min($newHeight, MAX_HEIGHT);
        
        $this->setMemoryLimit();
        
        // فتح الصورة
        $image = $this->openImage($mimeType, $imagePath);
        if (!$image) {
            throw new Exception('لا يمكن فتح الصورة');
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        $srcX = 0;
        $srcY = 0;
        $srcW = $width;
        $srcH = $height;
        
        // حساب الأبعاد الجديدة
        if ($newWidth && !$newHeight) {
            $newHeight = (int)floor($height * ($newWidth / $width));
        } elseif ($newHeight && !$newWidth) {
            $newWidth = (int)floor($width * ($newHeight / $height));
        }
        
        // إنشاء الصورة الجديدة
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        
        // لون الخلفية
        $canvasColor = $this->normalizeColor($canvasColor, DEFAULT_CC);
        $bgColor = $this->hexToRgb($canvasColor);
        
        if (preg_match('/^image\/png$/i', $mimeType) && !PNG_IS_TRANSPARENT && $canvasTrans) {
            $color = imagecolorallocatealpha($canvas, $bgColor['r'], $bgColor['g'], $bgColor['b'], 127);
        } else {
            $color = imagecolorallocate($canvas, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        }
        
        imagefill($canvas, 0, 0, $color);
        
        // معالجة القص
        if ($zoomCrop == 2) {
            $finalHeight = $height * ($newWidth / $width);
            if ($finalHeight > $newHeight) {
                $srcW = (int)($width * ($newHeight / $height));
                $srcX = (int)(($width - $srcW) / 2);
            } else {
                $srcH = (int)($height * ($newWidth / $width));
                $srcY = (int)(($height - $srcH) / 2);
            }
        } elseif ($zoomCrop == 3) {
            $finalHeight = $height * ($newWidth / $width);
            if ($finalHeight > $newHeight) {
                $newWidth = (int)($width * ($newHeight / $height));
            } else {
                $newHeight = (int)$finalHeight;
            }
        }
        
        // تطبيق القص بناءً على المحاذاة
        if ($zoomCrop == 1) {
            $ratioX = $width / $newWidth;
            $ratioY = $height / $newHeight;
            
            if ($ratioX > $ratioY) {
                $srcW = (int)($width / $ratioX * $ratioY);
                $srcX = (int)(($width - $srcW) / 2);
            } else {
                $srcH = (int)($height / $ratioY * $ratioX);
                $srcY = (int)(($height - $srcH) / 2);
            }
            
            // تطبيق المحاذاة
            if (str_contains($align, 'l')) $srcX = 0;
            if (str_contains($align, 'r')) $srcX = $width - $srcW;
            if (str_contains($align, 't')) $srcY = 0;
            if (str_contains($align, 'b')) $srcY = $height - $srcH;
        }
        
        imagesavealpha($canvas, true);
        
        // نسخ الصورة
        imagecopyresampled($canvas, $image, 0, 0, $srcX, $srcY, $newWidth, $newHeight, $srcW, $srcH);
        
        // تطبيق الفلاتر
        $this->applyFilters($canvas, $filters);
        
        // تطبيق الشاربنينغ
        if ($sharpen && function_exists('imageconvolution')) {
            $sharpenMatrix = [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1]
            ];
            imageconvolution($canvas, $sharpenMatrix, 8, 0);
        }
        
        // تحسين ألوان PNG/GIF
        if (($origType == IMAGETYPE_PNG || $origType == IMAGETYPE_GIF) && 
            function_exists('imageistruecolor') && !imageistruecolor($image) && 
            imagecolortransparent($image) > 0) {
            imagetruecolortopalette($canvas, false, imagecolorstotal($image));
        }
        
        // حفظ الصورة
        $tempFile = tempnam($this->cacheDirectory, 'timthumb_tmp_');
        
        switch ($origType) {
            case IMAGETYPE_JPEG:
                imagejpeg($canvas, $tempFile, $quality);
                $imgType = 'jpg';
                break;
            case IMAGETYPE_PNG:
                imagepng($canvas, $tempFile, (int)($quality * 0.09));
                $imgType = 'png';
                break;
            case IMAGETYPE_GIF:
                imagegif($canvas, $tempFile);
                $imgType = 'gif';
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    imagewebp($canvas, $tempFile, $quality);
                    $imgType = 'webp';
                } else {
                    imagejpeg($canvas, $tempFile, $quality);
                    $imgType = 'jpg';
                }
                break;
            default:
                imagedestroy($canvas);
                imagedestroy($image);
                throw new Exception('نوع الصورة غير مدعوم');
        }
        
        // تحسين PNG
        $this->optimizePNG($tempFile, $imgType);
        
        // إضافة كتلة الأمان
        $this->addSecurityBlock($tempFile, $imgType);
        
        // نقل الملف النهائي
        $lockFile = $this->cacheFile . '.lock';
        $lockFp = fopen($lockFile, 'w');
        
        if (!$lockFp) {
            @unlink($tempFile);
            throw new Exception('لا يمكن إنشاء ملف القفل');
        }
        
        if (flock($lockFp, LOCK_EX)) {
            @unlink($this->cacheFile);
            rename($tempFile, $this->cacheFile);
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            @unlink($lockFile);
        } else {
            fclose($lockFp);
            @unlink($lockFile);
            @unlink($tempFile);
        }
        
        imagedestroy($canvas);
        imagedestroy($image);
        
        return true;
    }

    protected function applyFilters($canvas, string $filters): void {
        if (empty($filters) || empty($this->imageFilters)) {
            return;
        }
        
        $filterList = explode('|', $filters);
        foreach ($filterList as $filter) {
            $settings = explode(',', $filter);
            $filterId = (int)($settings[0] ?? 0);
            
            if (!isset($this->imageFilters[$filterId])) {
                continue;
            }
            
            [$filterType, $paramCount] = $this->imageFilters[$filterId];
            
            switch ($paramCount) {
                case 1:
                    $param = (int)($settings[1] ?? 0);
                    imagefilter($canvas, $filterType, $param);
                    break;
                case 2:
                    $param1 = (int)($settings[1] ?? 0);
                    $param2 = (int)($settings[2] ?? 0);
                    imagefilter($canvas, $filterType, $param1, $param2);
                    break;
                case 4:
                    $param1 = (int)($settings[1] ?? 0);
                    $param2 = (int)($settings[2] ?? 0);
                    $param3 = (int)($settings[3] ?? 0);
                    $param4 = (int)($settings[4] ?? 0);
                    imagefilter($canvas, $filterType, $param1, $param2, $param3, $param4);
                    break;
                default:
                    imagefilter($canvas, $filterType);
            }
        }
    }

    protected function optimizePNG(string $file, string $imgType): void {
        if ($imgType != 'png') {
            return;
        }
        
        if (OPTIPNG_ENABLED && OPTIPNG_PATH && is_file(OPTIPNG_PATH)) {
            $command = escapeshellcmd(OPTIPNG_PATH) . ' -o1 ' . escapeshellarg($file);
            $output = shell_exec($command);
            $this->debug(3, "optipng output: " . ($output ?? ''));
        } elseif (PNGCRUSH_ENABLED && PNGCRUSH_PATH && is_file(PNGCRUSH_PATH)) {
            $tempFile2 = tempnam($this->cacheDirectory, 'timthumb_crush_');
            $command = escapeshellcmd(PNGCRUSH_PATH) . ' ' . escapeshellarg($file) . ' ' . escapeshellarg($tempFile2);
            $output = shell_exec($command);
            
            if (is_file($tempFile2)) {
                $sizeDrop = filesize($file) - filesize($tempFile2);
                if ($sizeDrop > 0) {
                    unlink($file);
                    rename($tempFile2, $file);
                } else {
                    unlink($tempFile2);
                }
            }
        }
    }

    protected function addSecurityBlock(string $file, string $imgType): void {
        $tempFile = tempnam($this->cacheDirectory, 'timthumb_sec_');
        $content = file_get_contents($file);
        file_put_contents($tempFile, $this->filePrependSecurityBlock . $imgType . ' ?>' . $content);
        unlink($file);
        rename($tempFile, $file);
    }

    protected function normalizeColor(string $color, string $default): string {
        $color = preg_replace('/[^0-9A-Fa-f]/', '', $color);
        
        if (strlen($color) == 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        
        if (strlen($color) != 6) {
            $color = $default;
        }
        
        return $color;
    }

    protected function hexToRgb(string $hex): array {
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    protected function openImage(string $mimeType, string $path) {
        return match($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default => null
        };
    }

    protected function setMemoryLimit(): void {
        $current = ini_get('memory_limit');
        $currentBytes = $this->returnBytes($current);
        $requiredBytes = $this->returnBytes(MEMORY_LIMIT);
        
        if ($currentBytes < $requiredBytes) {
            ini_set('memory_limit', MEMORY_LIMIT);
            $this->debug(3, "زيادة الذاكرة من $current إلى " . MEMORY_LIMIT);
        }
    }

    protected function returnBytes(string $size): int {
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $value = (int)$size;
        
        return match($last) {
            'g' => $value * 1073741824,
            'm' => $value * 1048576,
            'k' => $value * 1024,
            default => $value
        };
    }

    protected function cleanCache(): void {
        if (FILE_CACHE_TIME_BETWEEN_CLEANS < 0) {
            return;
        }
        
        $lastCleanFile = $this->cacheDirectory . '/timthumb_last_clean.txt';
        
        if (!is_file($lastCleanFile)) {
            file_put_contents($lastCleanFile, (string)time());
            return;
        }
        
        $lastClean = (int)file_get_contents($lastCleanFile);
        $now = time();
        
        if ($now - $lastClean < FILE_CACHE_TIME_BETWEEN_CLEANS) {
            return;
        }
        
        file_put_contents($lastCleanFile, (string)$now);
        
        $files = glob($this->cacheDirectory . '/*' . FILE_CACHE_SUFFIX);
        $cutoff = $now - FILE_CACHE_MAX_FILE_AGE;
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    protected function serveWebshot(): void {
        if (!defined('WEBSHOT_ENABLED') || !WEBSHOT_ENABLED) {
            throw new Exception('خدمة لقطات الشاشة غير مفعلة');
        }
        
        $required = ['WEBSHOT_CUTYCAPT', 'WEBSHOT_XVFB'];
        foreach ($required as $const) {
            if (!defined($const) || !is_file(constant($const))) {
                throw new Exception("الأداة $const غير موجودة");
            }
        }
        
        $url = $this->src;
        if (!preg_match('/^https?:\/\//i', $url)) {
            throw new Exception('رابط غير صالح');
        }
        
        // تنظيف الرابط
        $url = preg_replace('/[^A-Za-z0-9\-\.\_:\/\?\&\+\;\=]/', '', $url);
        
        $tempFile = tempnam($this->cacheDirectory, 'timthumb_webshot_');
        $this->toDeletes[] = $tempFile;
        
        $xvfb = WEBSHOT_XVFB_RUNNING ? '' : WEBSHOT_XVFB . ' --server-args="-screen 0, ' . WEBSHOT_SCREEN_X . 'x' . WEBSHOT_SCREEN_Y . 'x' . WEBSHOT_COLOR_DEPTH . '" ';
        $command = $xvfb . WEBSHOT_CUTYCAPT .
            ' --max-wait=' . (WEBSHOT_TIMEOUT * 1000) .
            ' --user-agent="' . WEBSHOT_USER_AGENT . '"' .
            ' --javascript=' . (WEBSHOT_JAVASCRIPT_ON ? 'on' : 'off') .
            ' --java=' . (WEBSHOT_JAVA_ON ? 'on' : 'off') .
            ' --plugins=' . (WEBSHOT_PLUGINS_ON ? 'on' : 'off') .
            ' --url="' . addslashes($url) . '"' .
            ' --out-format=' . WEBSHOT_IMAGE_FORMAT .
            ' --out=' . $tempFile;
        
        $this->debug(3, "تنفيذ: $command");
        $output = shell_exec($command);
        
        if (!is_file($tempFile)) {
            throw new Exception('فشل إنشاء لقطة الشاشة');
        }
        
        $this->cropTop = true;
        
        if ($this->processImageAndWriteToCache($tempFile)) {
            $this->serveCacheFile();
        }
    }

    protected function param(string $name, $default = '') {
        return $_GET[$name] ?? $default;
    }

    protected function getMimeType(string $file): string {
        $info = @getimagesize($file);
        return $info['mime'] ?? '';
    }

    protected function getIP(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $client = $_SERVER['HTTP_CLIENT_IP'] ?? '';
        
        if (!empty($forwarded) && !preg_match('/^(192\.168|172\.16|10\.|127\.)/', $ip)) {
            return $forwarded;
        }
        
        if (!empty($client) && !preg_match('/^(192\.168|172\.16|10\.|127\.)/', $ip)) {
            return $client;
        }
        
        return $ip ?: 'UNKNOWN';
    }

    protected function debug(int $level, string $message): void {
        if (!DEBUG_ON || $level > DEBUG_LEVEL) {
            return;
        }
        
        $execTime = sprintf('%.6f', microtime(true) - $this->startTime);
        $tick = sprintf('%.6f', $this->lastBenchTime ? microtime(true) - $this->lastBenchTime : 0);
        $this->lastBenchTime = microtime(true);
        
        error_log("TimThumb [{$execTime}:{$tick}]: {$message}");
    }

    protected function set404(): void {
        $this->is404 = true;
    }

    protected function checkErrors(): void {
        if (empty($this->errors)) {
            return;
        }
        
        if (NOT_FOUND_IMAGE && $this->is404) {
            if ($this->serveImage(NOT_FOUND_IMAGE)) {
                exit(0);
            }
        }
        
        if (ERROR_IMAGE) {
            if ($this->serveImage(ERROR_IMAGE)) {
                exit(0);
            }
        }
        
        $this->displayErrors();
    }

    protected function serveImage(string $path): bool {
        if (!is_file($path)) {
            return false;
        }
        
        $info = getimagesize($path);
        if (!$info) {
            return false;
        }
        
        header('Content-Type: ' . $info['mime']);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        readfile($path);
        return true;
    }

    protected function displayErrors(): void {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        
        if (!DISPLAY_ERROR_MESSAGES) {
            return;
        }
        
        $html = '<h1>TimThumb Error</h1>';
        $html .= '<p>The following error(s) occurred:</p><ul>';
        foreach ($this->errors as $err) {
            $html .= '<li>' . htmlspecialchars($err) . '</li>';
        }
        $html .= '</ul>';
        $html .= '<p>Query: ' . htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') . '</p>';
        $html .= '<p>Version: ' . TIMTHUMB_VERSION . '</p>';
        
        echo $html;
    }

    public function error(string $message): void {
        $this->debug(3, "إضافة رسالة خطأ: $message");
        $this->errors[] = $message;
    }

    public function __destruct() {
        foreach ($this->toDeletes as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}