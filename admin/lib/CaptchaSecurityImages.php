<?php
/**
 * File: CaptchaSecurityImages.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: توليد صور الكابتشا للأمان - منع البوتات والهجمات الآلية
 * Captcha Security Images - Prevent bots and automated attacks
 * 
 * Original Author: Simon Jarvis
 * Original Date: 2006-03-08
 * Last Updated: 2025-03-15
 * 
 * Requirements: PHP 8.3 with GD and FreeType libraries
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 2 
 * of the License, or (at your option) any later version.
 * 
 * @package Security
 * @subpackage Captcha
 * @license GPL-2.0-or-later
 * @link http://www.white-hat-web-design.co.uk/articles/php-captcha.php
 */

declare(strict_types=1);

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/captcha_errors.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class CaptchaSecurityImages
 * 
 * Generates secure CAPTCHA images for form protection
 */
class CaptchaSecurityImages {
    
    /** @var string Path to TrueType font file */
    private string $fontPath;
    
    /** @var string Session key for storing captcha code */
    private string $sessionKey = 'admin_login_security_code';
    
    /** @var array Allowed characters (similar looking and vowels removed) */
    private string $allowedChars = '23456789ABHTFRWPLZMGIS';
    
    /**
     * Constructor
     * 
     * @param string $fontPath Path to font file
     * @throws RuntimeException If font file not found
     */
    public function __construct(string $fontPath = '../css/monofont.ttf') {
        $this->fontPath = $fontPath;
        
        if (!file_exists($this->fontPath)) {
            throw new RuntimeException('Font file not found: ' . $this->fontPath);
        }
    }
    
    /**
     * Generate random captcha code
     * 
     * @param int $length Number of characters
     * @return string Generated code
     */
    private function generateCode(int $length): string {
        $code = '';
        $maxIndex = strlen($this->allowedChars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $this->allowedChars[random_int(0, $maxIndex)];
        }
        
        return $code;
    }
    
    /**
     * Create captcha image
     * 
     * @param int $width Image width
     * @param int $height Image height
     * @param int $charCount Number of characters
     * @return void
     */
    public function createImage(int $width, int $height, int $charCount): void {
        
        // Generate captcha code
        $code = $this->generateCode($charCount);
        
        // Store in session for validation
        $_SESSION[$this->sessionKey] = $code;
        
        // Set cache headers (30 days expiry)
        $this->setCacheHeaders();
        
        // Create image
        $image = imagecreate($width, $height);
        if (!$image) {
            throw new RuntimeException('Failed to create image');
        }
        
        try {
            // Set colors
            $backgroundColor = imagecolorallocate($image, 255, 255, 255); // White
            $textColor = imagecolorallocate($image, 189, 23, 34); // Red
            $noiseColor = imagecolorallocate($image, 212, 220, 212); // Light gray
            
            // Generate random dots in background
            $dotCount = (int)(($width * $height) / 3);
            for ($i = 0; $i < $dotCount; $i++) {
                imagefilledellipse(
                    $image,
                    random_int(0, $width),
                    random_int(0, $height),
                    1, 1,
                    $noiseColor
                );
            }
            
            // Add text to image
            $this->addTextToImage($image, $code, $width, $height);
            
            // Output image
            $this->outputImage($image);
            
        } finally {
            // Clean up
            imagedestroy($image);
        }
    }
    
    /**
     * Add text to captcha image
     * 
     * @param resource $image GD image resource
     * @param string $code Captcha code
     * @param int $width Image width
     * @param int $height Image height
     */
    private function addTextToImage($image, string $code, int $width, int $height): void {
        // Font size (95% of image height)
        $fontSize = (int)($height * 0.95);
        
        // Text color
        $textColor = imagecolorallocate($image, 189, 23, 34);
        
        // For simple text without TrueType
        imagestring($image, 5, 22, 5, $code, $textColor);
        
        // Alternative with TrueType (uncomment if needed)
        /*
        $textColor = imagecolorallocate($image, 189, 23, 34);
        $textbox = imagettfbbox($fontSize, 0, $this->fontPath, $code);
        
        if ($textbox === false) {
            throw new RuntimeException('Failed to create text bounding box');
        }
        
        $x = (int)(($width - $textbox[4]) / 2);
        $y = (int)(($height - $textbox[5]) / 2);
        
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $this->fontPath, $code);
        */
    }
    
    /**
     * Output image to browser
     * 
     * @param resource $image GD image resource
     */
    private function outputImage($image): void {
        // Set content type
        header('Content-Type: image/jpeg');
        
        // Output JPEG
        imagejpeg($image, null, 90);
    }
    
    /**
     * Set cache headers
     */
    private function setCacheHeaders(): void {
        $expiresOffset = 30 * 24 * 60 * 60; // 30 days
        
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=' . $expiresOffset);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiresOffset) . ' GMT');
        header('Pragma: cache');
    }
    
    /**
     * Validate captcha code
     * 
     * @param string $userInput User's captcha input
     * @return bool True if valid
     */
    public static function validate(string $userInput): bool {
        if (!isset($_SESSION['admin_login_security_code'])) {
            return false;
        }
        
        $storedCode = $_SESSION['admin_login_security_code'];
        $isValid = strtoupper(trim($userInput)) === $storedCode;
        
        // Clear session after validation (one-time use)
        unset($_SESSION['admin_login_security_code']);
        
        return $isValid;
    }
}

// Main execution
try {
    // Get parameters
    $width = isset($_GET['width']) ? (int)$_GET['width'] : 100;
    $height = isset($_GET['height']) ? (int)$_GET['height'] : 25;
    $characters = isset($_GET['characters']) && (int)$_GET['characters'] > 1 
        ? (int)$_GET['characters'] 
        : 6;
    
    // Validate parameters
    if ($width < 50 || $width > 500) {
        $width = 100;
    }
    
    if ($height < 20 || $height > 200) {
        $height = 25;
    }
    
    if ($characters < 4 || $characters > 10) {
        $characters = 6;
    }
    
    // Create captcha instance
    $fontPath = __DIR__ . '/../css/monofont.ttf';
    $captcha = new CaptchaSecurityImages($fontPath);
    
    // Generate image
    $captcha->createImage($width, $height, $characters);
    
} catch (Exception $e) {
    // Log error
    error_log("Captcha generation error: " . $e->getMessage());
    
    // Output error image
    header('Content-Type: image/png');
    
    $image = imagecreate(200, 50);
    $bg = imagecolorallocate($image, 255, 255, 255);
    $textColor = imagecolorallocate($image, 255, 0, 0);
    
    imagestring($image, 5, 10, 15, 'Error', $textColor);
    imagepng($image);
    imagedestroy($image);
}
?>