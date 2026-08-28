<?php
/**
 * File: seo_function.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: مولد علامات SEO التلقائي بناءً على اسم الصفحة
 * Automatic SEO meta tags generator based on page name
 * 
 * Features:
 * - توليد علامات SEO ديناميكية
 * - دعم خاص لصفحات تفاصيل الأعمال (portfolio)
 * - استخدام قاعدة بيانات SEO المخصصة
 * - توافق مع معايير محركات البحث
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('IN_ADMIN_PANEL')) {
    //exit('Direct access not allowed');
//}

// Include common if not already included
if (!isset($con) && file_exists('common.php')) {
    include 'common.php';
}

/**
 * Class SEOMetaGenerator
 * 
 * Generates SEO meta tags for pages
 */
class SEOMetaGenerator {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Current page name */
    private string $pageName;
    
    /** @var array<string, string> SEO data from database */
    private array $seoData = [];
    
    /** @var array<string> Allowed meta tags */
    private array $allowedMetaTags = [
        'title', 'keyword', 'description', 'robots', 'googlebot',
        'language', 'author', 'copyright', 'contact', 'expires',
        'last_modified', 'distribution', 'rating', 'revisit_after'
    ];
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $pageName Current page name
     */
    public function __construct(mysqli $database, string $pageName) {
        $this->db = $database;
        $this->pageName = $pageName;
        $this->loadSEODate();
    }
    
    /**
     * Load SEO data from database
     */
    private function loadSEODate(): void {
        $sql = "SELECT * FROM seo WHERE page_name = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("SEO prepare failed: " . mysqli_error($this->db));
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->pageName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $key => $value) {
                $this->seoData[$key] = $value ?? '';
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get portfolio title for details page
     * 
     * @param string $titleUrl Portfolio title URL
     * @return string Formatted portfolio title
     */
    private function getPortfolioTitle(string $titleUrl): string {
        $sql = "SELECT pt_title_url FROM " . TB_PORTFOLIO . " 
                WHERE pt_status = 1 AND pt_title_url = ? LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "s", $titleUrl);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            
            $titlename = explode("-", stripslashes($row['pt_title_url']));
            
            if (count($titlename) > 1) {
                $formattedTitle = '';
                foreach ($titlename as $part) {
                    $formattedTitle .= ' ' . ucfirst($part);
                }
                return trim($formattedTitle);
            }
            
            return ucfirst($titlename[0] ?? '');
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Generate page title
     * 
     * @return string Page title
     */
    private function generateTitle(): string {
        $baseTitle = $this->seoData['title'] ?? '';
        
        // Special handling for portfolio details page
        if ($this->pageName === 'portfolio_details.php' && isset($_GET['title'])) {
            $portfolioTitle = $this->getPortfolioTitle($_GET['title']);
            if (!empty($portfolioTitle)) {
                return $baseTitle . ' ' . $portfolioTitle;
            }
        }
        
        return $baseTitle;
    }
    
    /**
     * Get meta tag value with fallback
     * 
     * @param string $tag Meta tag name
     * @return string Meta tag value
     */
    private function getMetaValue(string $tag): string {
        return htmlspecialchars($this->seoData[$tag] ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate all meta tags
     * 
     * @return string Complete meta tags HTML
     */
    public function generate(): string {
        $title = $this->generateTitle();
        
        $metaTags = [];
        
        // Title tag
        $metaTags[] = "<title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>";
        
        // Standard meta tags
        $metaMappings = [
            'description' => 'description',
            'keyword' => 'keywords',
            'robots' => 'robots',
            'googlebot' => 'googlebot',
            'language' => 'language',
            'author' => 'author',
            'copyright' => 'copyright',
            'contact' => 'contact',
            'expires' => 'expires',
            'last_modified' => 'last_modified'
        ];
        
        foreach ($metaMappings as $dbField => $metaName) {
            $value = $this->getMetaValue($dbField);
            if (!empty($value)) {
                $metaTags[] = "<meta name=\"{$metaName}\" content=\"{$value}\" />";
            }
        }
        
        // Optional meta tags (commented in original)
        $optionalTags = ['distribution', 'rating', 'revisit_after'];
        foreach ($optionalTags as $tag) {
            $value = $this->getMetaValue($tag);
            if (!empty($value)) {
                $metaTags[] = "<meta name=\"{$tag}\" content=\"{$value}\" />";
            }
        }
        
        return implode("\n", $metaTags);
    }
    
    /**
     * Output meta tags
     */
    public function output(): void {
        echo $this->generate();
    }
    
    /**
     * Get specific SEO data
     * 
     * @param string $key Data key
     * @return string Data value
     */
    public function getSEODatum(string $key): string {
        return $this->seoData[$key] ?? '';
    }
    
    /**
     * Get all SEO data
     * 
     * @return array<string, string> SEO data
     */
    public function getAllSEOData(): array {
        return $this->seoData;
    }
}

// Main execution
try {
    // Get current page name
    $referer = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    
    // Get language parameter (if any)
    $lang = $_GET['lang'] ?? 'en';
    
    // Initialize SEO generator
    $seoGenerator = new SEOMetaGenerator($con, $referer);
    
    // Output meta tags
    $seoGenerator->output();
    
} catch (Exception $e) {
    // Log error and output minimal fallback
    error_log("SEO generator error: " . $e->getMessage());
    
    $fallbackTitle = htmlspecialchars(getWebSiteTitle() ?? 'Website', ENT_QUOTES, 'UTF-8');
    echo "<title>{$fallbackTitle}</title>\n";
    echo "<meta name=\"robots\" content=\"index, follow\" />\n";
}
?>