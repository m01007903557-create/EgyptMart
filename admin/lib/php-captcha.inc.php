<?php
/**
 * File: php-captcha.inc.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: مكتبة توليد الكابتشا المرئية والصوتية
 * Visual and audio CAPTCHA generation library
 * 
 * Original Author: Edward Eliot
 * Copyright: 2005-2006 Edward Eliot
 * License: BSD License
 * 
 * This software is provided under the BSD License.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions must retain the above copyright notice
 * - Redistributions in binary form must reproduce the above copyright notice
 * - Neither the name of Edward Eliot nor the names of its contributors 
 *   may be used to endorse or promote products derived from this software 
 *   without specific prior written permission.
 * 
 * @package Security
 * @subpackage Captcha
 * @license BSD
 * @link http://www.ejeliot.com/pages/2
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('STDIN')) {
    //exit('Direct access not allowed');
//}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// Default Configuration Constants
// ============================================

/** @var string Session ID for storing captcha code */
define('CAPTCHA_SESSION_ID', 'php_captcha');

/** @var int Default image width (max 500) */
define('CAPTCHA_WIDTH', 200);

/** @var int Default image height (max 200) */
define('CAPTCHA_HEIGHT', 50);

/** @var int Default number of characters */
define('CAPTCHA_NUM_CHARS', 5);

/** @var int Default number of lines */
define('CAPTCHA_NUM_LINES', 70);

/** @var bool Default shadow setting */
define('CAPTCHA_CHAR_SHADOW', false);

/** @var string Default owner text */
define('CAPTCHA_OWNER_TEXT', '');

/** @var string Default character set (A-Z) */
define('CAPTCHA_CHAR_SET', '');

/** @var bool Default case insensitivity */
define('CAPTCHA_CASE_INSENSITIVE', true);

/** @var string Default background images path */
define('CAPTCHA_BACKGROUND_IMAGES', '');

/** @var int Default minimum font size */
define('CAPTCHA_MIN_FONT_SIZE', 16);

/** @var int Default maximum font size */
define('CAPTCHA_MAX_FONT_SIZE', 25);

/** @var bool Default use color */
define('CAPTCHA_USE_COLOUR', false);

/** @var string Default file type (jpeg, png, gif) */
define('CAPTCHA_FILE_TYPE', 'jpeg');

/** @var string Path to flite binary for audio CAPTCHA */
define('CAPTCHA_FLITE_PATH', '/usr/bin/flite');

/** @var string Path for temporary audio files (must be writable) */
define('CAPTCHA_AUDIO_PATH', '/tmp/');

/**
 * Class PhpCaptcha - Visual CAPTCHA Generator
 */
class PhpCaptcha {
    
    /** @var resource GD image resource */
    private $oImage;
    
    /** @var array<string> Array of TrueType fonts */
    private array $aFonts;
    
    /** @var int Image width */
    private int $iWidth;
    
    /** @var int Image height */
    private int $iHeight;
    
    /** @var int Number of characters */
    private int $iNumChars;
    
    /** @var int Number of lines */
    private int $iNumLines;
    
    /** @var int Character spacing */
    private int $iSpacing;
    
    /** @var bool Display character shadow */
    private bool $bCharShadow;
    
    /** @var string Owner text */
    private string $sOwnerText;
    
    /** @var array<string> Character set */
    private array $aCharSet = [];
    
    /** @var bool Case insensitive comparison */
    private bool $bCaseInsensitive;
    
    /** @var string|array<string> Background images */
    private $vBackgroundImages;
    
    /** @var int Minimum font size */
    private int $iMinFontSize;
    
    /** @var int Maximum font size */
    private int $iMaxFontSize;
    
    /** @var bool Use color */
    private bool $bUseColour;
    
    /** @var string File type (jpeg, png, gif) */
    private string $sFileType;
    
    /** @var string Generated code */
    private string $sCode = '';
    
    /**
     * Constructor
     * 
     * @param array<string> $aFonts Array of TrueType fonts (full paths)
     * @param int $iWidth Image width
     * @param int $iHeight Image height
     */
    public function __construct(array $aFonts, int $iWidth = CAPTCHA_WIDTH, int $iHeight = CAPTCHA_HEIGHT) {
        $this->aFonts = $aFonts;
        $this->setNumChars(CAPTCHA_NUM_CHARS);
        $this->setNumLines(CAPTCHA_NUM_LINES);
        $this->displayShadow(CAPTCHA_CHAR_SHADOW);
        $this->setOwnerText(CAPTCHA_OWNER_TEXT);
        $this->setCharSet(CAPTCHA_CHAR_SET);
        $this->caseInsensitive(CAPTCHA_CASE_INSENSITIVE);
        $this->setBackgroundImages(CAPTCHA_BACKGROUND_IMAGES);
        $this->setMinFontSize(CAPTCHA_MIN_FONT_SIZE);
        $this->setMaxFontSize(CAPTCHA_MAX_FONT_SIZE);
        $this->useColour(CAPTCHA_USE_COLOUR);
        $this->setFileType(CAPTCHA_FILE_TYPE);
        $this->setWidth($iWidth);
        $this->setHeight($iHeight);
    }
    
    /**
     * PHP 4 compatible constructor
     * 
     * @deprecated Use __construct instead
     */
    public function PhpCaptcha(array $aFonts, int $iWidth = CAPTCHA_WIDTH, int $iHeight = CAPTCHA_HEIGHT): void {
        $this->__construct($aFonts, $iWidth, $iHeight);
    }
    
    /**
     * Calculate character spacing
     */
    private function calculateSpacing(): void {
        $this->iSpacing = (int)($this->iWidth / $this->iNumChars);
    }
    
    /**
     * Set image width
     * 
     * @param int $iWidth Width in pixels
     */
    public function setWidth(int $iWidth): void {
        $this->iWidth = min($iWidth, 500); // Prevent performance impact
        $this->calculateSpacing();
    }
    
    /**
     * Set image height
     * 
     * @param int $iHeight Height in pixels
     */
    public function setHeight(int $iHeight): void {
        $this->iHeight = min($iHeight, 200); // Prevent performance impact
    }
    
    /**
     * Set number of characters
     * 
     * @param int $iNumChars Number of characters
     */
    public function setNumChars(int $iNumChars): void {
        $this->iNumChars = $iNumChars;
        $this->calculateSpacing();
    }
    
    /**
     * Set number of lines
     * 
     * @param int $iNumLines Number of lines
     */
    public function setNumLines(int $iNumLines): void {
        $this->iNumLines = $iNumLines;
    }
    
    /**
     * Display character shadow
     * 
     * @param bool $bCharShadow Whether to display shadow
     */
    public function displayShadow(bool $bCharShadow): void {
        $this->bCharShadow = $bCharShadow;
    }
    
    /**
     * Set owner text
     * 
     * @param string $sOwnerText Owner text
     */
    public function setOwnerText(string $sOwnerText): void {
        $this->sOwnerText = $sOwnerText;
    }
    
    /**
     * Set character set
     * 
     * @param string|array<string> $vCharSet Character set string or array
     */
    public function setCharSet($vCharSet): void {
        if (is_array($vCharSet)) {
            $this->aCharSet = $vCharSet;
            return;
        }
        
        if ($vCharSet === '') {
            return;
        }
        
        $this->aCharSet = [];
        $aCharSet = explode(',', $vCharSet);
        
        foreach ($aCharSet as $sCurrentItem) {
            // Check for range (e.g., A-Z)
            if (strlen($sCurrentItem) === 3 && strpos($sCurrentItem, '-') === 1) {
                $aRange = explode('-', $sCurrentItem);
                
                if (count($aRange) === 2 && $aRange[0] < $aRange[1]) {
                    $aRange = range($aRange[0], $aRange[1]);
                    $this->aCharSet = array_merge($this->aCharSet, $aRange);
                }
            } else {
                $this->aCharSet[] = $sCurrentItem;
            }
        }
    }
    
    /**
     * Set case insensitivity
     * 
     * @param bool $bCaseInsensitive Whether to use case insensitive comparison
     */
    public function caseInsensitive(bool $bCaseInsensitive): void {
        $this->bCaseInsensitive = $bCaseInsensitive;
    }
    
    /**
     * Set background images
     * 
     * @param string|array<string> $vBackgroundImages Background images
     */
    public function setBackgroundImages($vBackgroundImages): void {
        $this->vBackgroundImages = $vBackgroundImages;
    }
    
    /**
     * Set minimum font size
     * 
     * @param int $iMinFontSize Minimum font size
     */
    public function setMinFontSize(int $iMinFontSize): void {
        $this->iMinFontSize = $iMinFontSize;
    }
    
    /**
     * Set maximum font size
     * 
     * @param int $iMaxFontSize Maximum font size
     */
    public function setMaxFontSize(int $iMaxFontSize): void {
        $this->iMaxFontSize = $iMaxFontSize;
    }
    
    /**
     * Set use colour
     * 
     * @param bool $bUseColour Whether to use colour
     */
    public function useColour(bool $bUseColour): void {
        $this->bUseColour = $bUseColour;
    }
    
    /**
     * Set file type
     * 
     * @param string $sFileType File type (gif, png, jpeg)
     */
    public function setFileType(string $sFileType): void {
        $this->sFileType = in_array($sFileType, ['gif', 'png', 'jpeg'], true) ? $sFileType : 'jpeg';
    }
    
    /**
     * Draw random lines on image
     */
    private function drawLines(): void {
        for ($i = 0; $i < $this->iNumLines; $i++) {
            if ($this->bUseColour) {
                $iLineColour = imagecolorallocate($this->oImage, random_int(100, 250), random_int(100, 250), random_int(100, 250));
            } else {
                $iRandColour = random_int(100, 250);
                $iLineColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
            }
            
            imageline(
                $this->oImage,
                random_int(0, $this->iWidth),
                random_int(0, $this->iHeight),
                random_int(0, $this->iWidth),
                random_int(0, $this->iHeight),
                $iLineColour
            );
        }
    }
    
    /**
     * Draw owner text
     */
    private function drawOwnerText(): void {
        $iBlack = imagecolorallocate($this->oImage, 0, 0, 0);
        $iOwnerTextHeight = imagefontheight(2);
        $iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;
        
        // Draw separator line
        imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);
        
        // Write owner text
        imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);
        
        // Reduce available height
        $this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
    }
    
    /**
     * Generate random code
     */
    private function generateCode(): void {
        $this->sCode = '';
        
        for ($i = 0; $i < $this->iNumChars; $i++) {
            if (!empty($this->aCharSet)) {
                $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
            } else {
                $this->sCode .= chr(random_int(65, 90)); // A-Z
            }
        }
        
        // Store in session
        $_SESSION[CAPTCHA_SESSION_ID] = $this->bCaseInsensitive 
            ? strtoupper($this->sCode) 
            : $this->sCode;
    }
    
    /**
     * Draw characters on image
     */
    private function drawCharacters(): void {
        for ($i = 0; $i < strlen($this->sCode); $i++) {
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];
            
            if ($this->bUseColour) {
                $iTextColour = imagecolorallocate($this->oImage, random_int(0, 100), random_int(0, 100), random_int(0, 100));
                
                if ($this->bCharShadow) {
                    $iShadowColour = imagecolorallocate($this->oImage, random_int(0, 100), random_int(0, 100), random_int(0, 100));
                }
            } else {
                $iRandColour = random_int(0, 100);
                $iTextColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
                
                if ($this->bCharShadow) {
                    $iRandColour = random_int(0, 100);
                    $iShadowColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
                }
            }
            
            $iFontSize = random_int($this->iMinFontSize, $this->iMaxFontSize);
            $iAngle = random_int(-30, 30);
            
            $aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $this->sCode[$i]);
            
            $iX = (int)($this->iSpacing / 4 + $i * $this->iSpacing);
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = (int)($this->iHeight / 2 + $iCharHeight / 4);
            
            imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColour, $sCurrentFont, $this->sCode[$i]);
            
            if ($this->bCharShadow) {
                $iOffsetAngle = random_int(-30, 30);
                $iRandOffsetX = random_int(-5, 5);
                $iRandOffsetY = random_int(-5, 5);
                
                imagefttext(
                    $this->oImage, 
                    $iFontSize, 
                    $iOffsetAngle, 
                    $iX + $iRandOffsetX, 
                    $iY + $iRandOffsetY, 
                    $iShadowColour, 
                    $sCurrentFont, 
                    $this->sCode[$i]
                );
            }
        }
    }
    
    /**
     * Write image to file or browser
     * 
     * @param string $sFilename Filename (empty for browser output)
     */
    private function writeFile(string $sFilename = ''): void {
        if ($sFilename === '') {
            header("Content-type: image/{$this->sFileType}");
        }
        
        switch ($this->sFileType) {
            case 'gif':
                $sFilename !== '' ? imagegif($this->oImage, $sFilename) : imagegif($this->oImage);
                break;
            case 'png':
                $sFilename !== '' ? imagepng($this->oImage, $sFilename) : imagepng($this->oImage);
                break;
            default: // jpeg
                $sFilename !== '' ? imagejpeg($this->oImage, $sFilename, 90) : imagejpeg($this->oImage);
        }
    }
    
    /**
     * Create CAPTCHA image
     * 
     * @param string $sFilename Filename (empty for browser output)
     * @return bool Success status
     */
    public function create(string $sFilename = ''): bool {
        // Check required functions
        if (!function_exists('imagecreate') || 
            !function_exists("image{$this->sFileType}") || 
            ($this->vBackgroundImages !== '' && !function_exists('imagecreatetruecolor'))) {
            return false;
        }
        
        // Create image with background if specified
        if (!empty($this->vBackgroundImages)) {
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
            
            if (is_array($this->vBackgroundImages)) {
                $iRandImage = array_rand($this->vBackgroundImages);
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages[$iRandImage]);
            } else {
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages);
            }
            
            imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);
            imagedestroy($oBackgroundImage);
        } else {
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
            imagecolorallocate($this->oImage, 255, 255, 255); // White background
        }
        
        // Draw owner text if specified
        if ($this->sOwnerText !== '') {
            $this->drawOwnerText();
        }
        
        // Draw lines if no background image
        if (empty($this->vBackgroundImages)) {
            $this->drawLines();
        }
        
        $this->generateCode();
        $this->drawCharacters();
        
        // Output image
        $this->writeFile($sFilename);
        
        // Free memory
        imagedestroy($this->oImage);
        
        return true;
    }
    
    /**
     * Validate user input against stored code
     * 
     * @param string $sUserCode User input
     * @param bool $bCaseInsensitive Whether to use case insensitive comparison
     * @return bool True if valid
     */
    public static function validate(string $sUserCode, bool $bCaseInsensitive = true): bool {
        if ($bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
        }
        
        if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $sUserCode === $_SESSION[CAPTCHA_SESSION_ID]) {
            unset($_SESSION[CAPTCHA_SESSION_ID]); // Prevent reuse
            return true;
        }
        
        return false;
    }
}

/**
 * Class AudioPhpCaptcha - Audio CAPTCHA Generator
 * 
 * Note: This class requires the visual CAPTCHA to be created first
 */
class AudioPhpCaptcha {
    
    /** @var string Path to flite binary */
    private string $sFlitePath;
    
    /** @var string Path for temporary audio files */
    private string $sAudioPath;
    
    /** @var string CAPTCHA code */
    private string $sCode;
    
    /**
     * Constructor
     * 
     * @param string $sFlitePath Path to flite binary
     * @param string $sAudioPath Path for temporary audio files
     */
    public function __construct(
        string $sFlitePath = CAPTCHA_FLITE_PATH,
        string $sAudioPath = CAPTCHA_AUDIO_PATH
    ) {
        $this->setFlitePath($sFlitePath);
        $this->setAudioPath($sAudioPath);
        
        // Retrieve code from session if available
        $this->sCode = $_SESSION[CAPTCHA_SESSION_ID] ?? '';
    }
    
    /**
     * Set flite path
     * 
     * @param string $sFlitePath Path to flite binary
     */
    public function setFlitePath(string $sFlitePath): void {
        $this->sFlitePath = $sFlitePath;
    }
    
    /**
     * Set audio path
     * 
     * @param string $sAudioPath Path for temporary audio files
     */
    public function setAudioPath(string $sAudioPath): void {
        $this->sAudioPath = rtrim($sAudioPath, '/') . '/';
    }
    
    /**
     * Mask text for audio output
     * 
     * @param string $sText Text to mask
     * @return string Masked text
     */
    private function mask(string $sText): string {
        $iLength = strlen($sText);
        
        // Format text with commas and "and"
        $sFormattedText = '';
        for ($i = 0; $i < $iLength; $i++) {
            if ($i > 0 && $i < $iLength - 1) {
                $sFormattedText .= ', ';
            } elseif ($i == $iLength - 1) {
                $sFormattedText .= ' and ';
            }
            $sFormattedText .= $sText[$i];
        }
        
        $aPhrases = [
            "The %1\$s characters are as follows: %2\$s",
            "%2\$s, are the %1\$s letters",
            "Here are the %1\$s characters: %2\$s",
            "%1\$s characters are: %2\$s",
            "%1\$s letters: %2\$s"
        ];
        
        $iPhrase = array_rand($aPhrases);
        
        return sprintf($aPhrases[$iPhrase], $iLength, $sFormattedText);
    }
    
    /**
     * Create audio CAPTCHA
     */
    public function create(): void {
        if (empty($this->sCode)) {
            return;
        }
        
        $sText = $this->mask($this->sCode);
        $sFile = md5($this->sCode . time());
        
        // Generate audio file with flite
        $command = escapeshellcmd("{$this->sFlitePath} -t " . escapeshellarg($sText) . " -o {$this->sAudioPath}{$sFile}.wav");
        shell_exec($command);
        
        // Set headers
        header('Content-type: audio/x-wav');
        header("Content-Disposition: attachment;filename={$sFile}.wav");
        
        // Output file
        $audioFile = "{$this->sAudioPath}{$sFile}.wav";
        if (file_exists($audioFile)) {
            readfile($audioFile);
            @unlink($audioFile);
        }
    }
}

/**
 * Class PhpCaptchaColour - Colour CAPTCHA subclass
 */
class PhpCaptchaColour extends PhpCaptcha {
    
    /**
     * Constructor
     * 
     * @param array<string> $aFonts Array of TrueType fonts
     * @param int $iWidth Image width
     * @param int $iHeight Image height
     */
    public function __construct(array $aFonts, int $iWidth = CAPTCHA_WIDTH, int $iHeight = CAPTCHA_HEIGHT) {
        parent::__construct($aFonts, $iWidth, $iHeight);
        $this->useColour(true);
    }
    
    /**
     * PHP 4 compatible constructor
     * 
     * @deprecated Use __construct instead
     */
    public function PhpCaptchaColour(array $aFonts, int $iWidth = CAPTCHA_WIDTH, int $iHeight = CAPTCHA_HEIGHT): void {
        $this->__construct($aFonts, $iWidth, $iHeight);
    }
}
?>