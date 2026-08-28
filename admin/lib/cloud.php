<?php
/**
 * File: cloud.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: فئة توليد سحابة الكلمات (Tag Cloud) للنصوص والكلمات المفتاحية
 * Tag Cloud Generator Class for text and keyword visualization
 * 
 * Original Author: Unknown
 * Last Updated: 2025-03-15
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * @package TextProcessing
 * @subpackage TagCloud
 * @license GPL-2.0-or-later
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('STDIN')) {
    //exit('Direct access not allowed');
//}

/**
 * Helper function for random sorting of array elements
 * 
 * @param mixed $a First element
 * @param mixed $b Second element
 * @return int Random -1, 0, or 1
 */
function randomSort($a, $b): int {
    return random_int(-1, 1);
}

/**
 * Class PTagCloud
 * 
 * Generates visual tag clouds from text or keyword arrays
 */
class PTagCloud {
    
    /** @var array<string, int> Array of tags with their frequencies */
    private array $tags = [];
    
    /** @var int Maximum frequency count */
    private int $maxCount = 0;
    
    /** @var int Number of elements to display in cloud */
    private int $displayedElementsCount;
    
    /** @var string Search URL template for tag links */
    private string $searchURL;
    
    /** @var string|null Background image path */
    private ?string $backgroundImage = null;
    
    /** @var string Background color */
    private string $backgroundColor = '#000';
    
    /** @var int|null Width of cloud container */
    private ?int $width = null;
    
    /** @var array<string> Array of text colors for different grades */
    private array $colors = [];
    
    /** @var bool Whether to use UTF-8 encoding */
    private bool $useUTF8 = false;
    
    /** @var array<string> Common words to filter out */
    private array $commonWords = [];
    
    /**
     * Constructor
     * 
     * @param int $displayedElementCount Number of tags to display
     * @param array<string>|false $arSeedWords Initial seed words
     */
    public function __construct(int $displayedElementCount, array|false $arSeedWords = false) {
        $this->displayedElementsCount = $displayedElementCount;
        $this->searchURL = "cloudSearch.php?hl=en&q=";
        $this->initializeDefaultColors();
        $this->initializeCommonWords();
        
        if ($arSeedWords !== false && is_array($arSeedWords)) {
            foreach ($arSeedWords as $value) {
                $this->addTag($value);
            }
        }
    }
    
    /**
     * PHP 4 compatible constructor
     * 
     * @deprecated Use __construct instead
     */
    public function PTagCloud(int $displayedElementCount, array|false $arSeedWords = false): void {
        $this->__construct($displayedElementCount, $arSeedWords);
    }
    
    /**
     * Initialize default color palette
     */
    private function initializeDefaultColors(): void {
        $this->colors = [
            "#5122CC", "#229926", "#330099", "#819922", "#22CCC3",
            "#99008D", "#943131", "#B23B3B", "#229938", "#419922"
        ];
    }
    
    /**
     * Initialize common words list
     */
    private function initializeCommonWords(): void {
        $commonWordsStr = "'tis,'twas,a,able,about,across,after,ain't,all,almost,also,am,among,an,and,any,are,aren't," .
            "as,at,be,because,been,but,by,can,can't,cannot,could,could've,couldn't,dear,did,didn't,do,does,doesn't," .
            "don't,either,else,ever,every,for,from,get,got,had,has,hasn't,have,he,he'd,he'll,he's,her,hers,him,his," .
            "how,how'd,how'll,how's,however,i,i'd,i'll,i'm,i've,if,in,into,is,isn't,it,it's,its,just,least,let,like," .
            "likely,may,me,might,might've,mightn't,most,must,must've,mustn't,my,neither,no,nor,not,o'clock,of,off," .
            "often,on,only,or,other,our,own,rather,said,say,says,shan't,she,she'd,she'll,she's,should,should've," .
            "shouldn't,since,so,some,than,that,that'll,that's,the,their,them,then,there,there's,these,they,they'd," .
            "they'll,they're,they've,this,tis,to,too,twas,us,wants,was,wasn't,we,we'd,we'll,we're,were,weren't,what," .
            "what'd,what's,when,when,when'd,when'll,when's,where,where'd,where'll,where's,which,while,who,who'd," .
            "who'll,who's,whom,why,why'd,why'll,why's,will,with,won't,would,would've,wouldn't,yet,you,you'd,you'll," .
            "you're,you've,your";
        
        $this->commonWords = array_map('strtolower', explode(",", $commonWordsStr));
    }
    
    /**
     * Set search URL for tag links
     * 
     * @param string $searchURL The search URL template
     */
    public function setSearchURL(string $searchURL): void {
        $this->searchURL = $searchURL;
    }
    
    /**
     * Set UTF-8 encoding flag
     * 
     * @param bool $useUTF8 Whether to use UTF-8
     */
    public function setUTF8(bool $useUTF8): void {
        $this->useUTF8 = $useUTF8;
    }
    
    /**
     * Set cloud container width
     * 
     * @param int $width Width in pixels
     */
    public function setWidth(int $width): void {
        $this->width = $width;
    }
    
    /**
     * Set background image
     * 
     * @param string $backgroundImage Path to background image
     */
    public function setBackgroundImage(string $backgroundImage): void {
        $this->backgroundImage = $backgroundImage;
    }
    
    /**
     * Set background color
     * 
     * @param string $backgroundColor CSS color value
     */
    public function setBackgroundColor(string $backgroundColor): void {
        $this->backgroundColor = $backgroundColor;
    }
    
    /**
     * Set text colors for different grades
     * 
     * @param array<string> $colors Array of CSS color values
     */
    public function setTextColors(array $colors): void {
        $this->colors = $colors;
    }
    
    /**
     * Replace whole word in string
     * 
     * @param string $needle Word to replace
     * @param string $replacement Replacement text
     * @param string $haystack Input string
     * @return string Modified string
     */
    private function strReplaceWord(string $needle, string $replacement, string $haystack): string {
        $pattern = "/\b" . preg_quote($needle, '/') . "\b/i";
        return preg_replace($pattern, $replacement, $haystack) ?? $haystack;
    }
    
    /**
     * Extract keywords from text
     * 
     * @param string $text Input text
     * @return array<string> Extracted keywords
     */
    public function keywordsExtract(string $text): array {
        $text = strtolower($text);
        $text = strip_tags($text);
        
        // Remove common words
        foreach ($this->commonWords as $commonWord) {
            $text = $this->strReplaceWord($commonWord, "", $text);
        }
        
        // Remove punctuation and newlines
        if ($this->useUTF8) {
            $text = preg_replace('/[^\p{L}0-9\s]|\n|\r/u', ' ', $text) ?? '';
        } else {
            $text = preg_replace('/[^a-zA-Z0-9\s]|\n|\r/', ' ', $text) ?? '';
        }
        
        // Remove extra spaces
        $text = preg_replace('/ +/', ' ', $text) ?? '';
        $text = trim($text);
        
        if (empty($text)) {
            return [];
        }
        
        $words = explode(" ", $text);
        $keywords = [];
        
        foreach ($words as $value) {
            $temp = trim($value);
            if ($temp !== '' && !is_numeric($temp)) {
                $keywords[] = $temp;
            }
        }
        
        return $keywords;
    }
    
    /**
     * Add tags from text
     * 
     * @param string $seedText Source text
     */
    public function addTagsFromText(string $seedText): void {
        $words = $this->keywordsExtract($seedText);
        foreach ($words as $value) {
            $this->addTag($value);
        }
    }
    
    /**
     * Add single tag
     * 
     * @param string $tag Tag name
     * @param int $useCount Frequency count
     */
    public function addTag(string $tag, int $useCount = 1): void {
        $tag = strtolower(trim($tag));
        if ($tag === '') {
            return;
        }
        
        if (array_key_exists($tag, $this->tags)) {
            $this->tags[$tag] += $useCount;
        } else {
            $this->tags[$tag] = $useCount;
        }
    }
    
    /**
     * Calculate grade based on frequency percentage
     * 
     * @param float $frequency Percentage of max frequency
     * @return int Grade from 0-9
     */
    private function gradeFrequency(float $frequency): int {
        return match(true) {
            $frequency >= 90 => 9,
            $frequency >= 70 => 8,
            $frequency >= 60 => 7,
            $frequency >= 50 => 6,
            $frequency >= 40 => 5,
            $frequency >= 30 => 4,
            $frequency >= 20 => 3,
            $frequency >= 10 => 2,
            $frequency >= 5  => 1,
            default           => 0
        };
    }
    
    /**
     * Emit tag cloud HTML or array
     * 
     * @param bool $returnHTML Whether to return HTML (true) or array (false)
     * @return string|array<string, int> HTML string or grade array
     */
    public function emitCloud(bool $returnHTML = true): string|array {
        if (empty($this->tags)) {
            return $returnHTML ? '' : [];
        }
        
        // Sort by frequency and take top elements
        arsort($this->tags);
        $topTags = array_slice($this->tags, 0, $this->displayedElementsCount, true);
        
        // Randomize order for display
        uasort($topTags, 'randomSort');
        
        $this->maxCount = max($this->tags);
        
        if ($returnHTML) {
            return $this->buildHTMLCloud($topTags);
        }
        
        return $this->buildGradeArray($topTags);
    }
    
    /**
     * Build HTML tag cloud
     * 
     * @param array<string, int> $tags Tags to display
     * @return string HTML output
     */
    private function buildHTMLCloud(array $tags): string {
        $style = isset($this->width) ? "width:{$this->width}px;" : "";
        $style .= "line-height:normal;";
        
        $bgStyle = isset($this->backgroundImage) 
            ? "background:url('{$this->backgroundImage}');" 
            : "";
        $bgStyle .= "border-color:#888;margin-top:20px;margin-bottom:10px;";
        $bgStyle .= "padding:5px 5px 20px 5px;background-color:{$this->backgroundColor};";
        
        $result = '<div id="id_tag_cloud" style="' . $style . '">';
        $result .= '<div style="' . $bgStyle . '">';
        
        foreach ($tags as $tag => $useCount) {
            $percentage = ($useCount * 100) / $this->maxCount;
            $grade = $this->gradeFrequency($percentage);
            $color = $this->colors[$grade] ?? $this->colors[0];
            $fontSize = 0.6 + (0.1 * $grade);
            $encodedTag = urlencode($tag);
            
            $result .= sprintf(
                '<a href="%s%s" title="More info on %s" style="color:%s;text-decoration:none">' .
                '<span style="color:%s; letter-spacing:3px; padding:4px; font-family:Tahoma; ' .
                'font-weight:900; font-size:%fem">%s</span></a> ',
                htmlspecialchars($this->searchURL, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($encodedTag, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($color, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($color, ENT_QUOTES, 'UTF-8'),
                $fontSize,
                htmlspecialchars($tag, ENT_QUOTES, 'UTF-8')
            );
        }
        
        $result .= '</div>';
        $result .= '<div style="position:relative;top:-25px">';
        $result .= '<div style="float:right;padding-right:5px;height:15px;font-size:10px"></div>';
        $result .= '</div></div><br />';
        
        return $result;
    }
    
    /**
     * Build grade array for non-HTML output
     * 
     * @param array<string, int> $tags Tags to process
     * @return array<string, int> Tag => grade mapping
     */
    private function buildGradeArray(array $tags): array {
        $result = [];
        foreach ($tags as $tag => $useCount) {
            $percentage = ($useCount * 100) / $this->maxCount;
            $result[$tag] = $this->gradeFrequency($percentage);
        }
        return $result;
    }
    
    /**
     * Get all tags with their frequencies
     * 
     * @return array<string, int>
     */
    public function getAllTags(): array {
        return $this->tags;
    }
    
    /**
     * Clear all tags
     */
    public function clearTags(): void {
        $this->tags = [];
        $this->maxCount = 0;
    }
    
    /**
     * Remove a specific tag
     * 
     * @param string $tag Tag to remove
     * @return bool True if tag existed and was removed
     */
    public function removeTag(string $tag): bool {
        $tag = strtolower(trim($tag));
        if (isset($this->tags[$tag])) {
            unset($this->tags[$tag]);
            $this->maxCount = empty($this->tags) ? 0 : max($this->tags);
            return true;
        }
        return false;
    }
    
    /**
     * Get tag frequency
     * 
     * @param string $tag Tag name
     * @return int Frequency count
     */
    public function getTagFrequency(string $tag): int {
        $tag = strtolower(trim($tag));
        return $this->tags[$tag] ?? 0;
    }
}
?>