<?php
/**
 * File: SimpleImage.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: فئة مبسطة لمعالجة الصور - تغيير الحجم، القص، الحفظ، والعرض
 * Simple image manipulation class - resize, crop, save, and output
 * 
 * Original Author: Simon Jarvis
 * Modified by: Miguel Fermín
 * Last Updated: 2025-03-16
 * 
 * Based on: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 2 
 * of the License, or (at your option) any later version.
 * 
 * Features:
 * - تحميل الصور (JPEG, GIF, PNG)
 * - تغيير الحجم بنسب مختلفة
 * - قص الصور من المنتصف أو من إحداثيات محددة
 * - حفظ الصور بتنسيقات متعددة
 * - دعم الشفافية في PNG و GIF
 * - تغيير الحجم مع الحفاظ على النسبة
 * - إنشاء صور مربعة
 * - ملء المساحات الفارغة بلون محدد
 * 
 * @package ImageProcessing
 * @subpackage SimpleImage
 * @license GPL-2.0-or-later
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !defined('STDIN') && basename($_SERVER['PHP_SELF'] ?? '') === basename(__FILE__)) {
    exit('Direct access not allowed');
}

/**
 * Class SimpleImage
 * 
 * Simple image manipulation class
 */
class SimpleImage {
   
    /** @var resource|\GdImage GD image resource */
    private $image;
   
    /** @var int Image type constant (IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG) */
    private int $imageType;
   
    /**
     * Constructor
     * 
     * @param string|null $filename Optional filename to load
     * @throws RuntimeException If file cannot be loaded
     */
    public function __construct(?string $filename = null) {
        if ($filename !== null && $filename !== '') {
            $this->load($filename);
        }
    }
   
    /**
     * Load image from file
     * 
     * @param string $filename Path to image file
     * @throws RuntimeException If file cannot be loaded or format not supported
     */
    public function load(string $filename): void {
        if (!file_exists($filename)) {
            throw new RuntimeException("Image file not found: {$filename}");
        }
        
        if (!is_readable($filename)) {
            throw new RuntimeException("Image file not readable: {$filename}");
        }
        
        $imageInfo = getimagesize($filename);
        if ($imageInfo === false) {
            throw new RuntimeException("Invalid image file: {$filename}");
        }
        
        $this->imageType = $imageInfo[2];
        
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filename);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filename);
                break;
            default:
                throw new RuntimeException("Unsupported image type. Only JPEG, GIF, and PNG are supported.");
        }
        
        if ($this->image === false) {
            throw new RuntimeException("Failed to create image from: {$filename}");
        }
        
        // Preserve transparency for GIF and PNG
        if ($this->imageType === IMAGETYPE_GIF || $this->imageType === IMAGETYPE_PNG) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }
    }
   
    /**
     * Save image to file
     * 
     * @param string $filename Output filename
     * @param int $imageType Image type constant (default: JPEG)
     * @param int $compression JPEG compression quality (0-100)
     * @param int|null $permissions File permissions (octal)
     * @throws RuntimeException If save fails
     */
    public function save(string $filename, int $imageType = IMAGETYPE_JPEG, int $compression = 75, ?int $permissions = null): void {
        
        // Validate compression
        $compression = max(0, min(100, $compression));
        
        $saved = false;
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $saved = imagejpeg($this->image, $filename, $compression);
                break;
            case IMAGETYPE_GIF:
                $saved = imagegif($this->image, $filename);
                break;
            case IMAGETYPE_PNG:
                // Preserve transparency for PNG
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                $saved = imagepng($this->image, $filename, (int)($compression / 10)); // PNG compression 0-9
                break;
            default:
                throw new RuntimeException("Unsupported output image type");
        }
        
        if (!$saved) {
            throw new RuntimeException("Failed to save image to: {$filename}");
        }
        
        // Set file permissions if specified
        if ($permissions !== null) {
            chmod($filename, $permissions);
        }
    }
   
    /**
     * Output image directly to browser
     * 
     * @param int $imageType Image type constant (default: JPEG)
     * @param int $quality JPEG quality (0-100)
     * @throws RuntimeException If output fails
     */
    public function output(int $imageType = IMAGETYPE_JPEG, int $quality = 80): void {
        
        // Validate quality
        $quality = max(0, min(100, $quality));
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                header('Content-Type: image/jpeg');
                imagejpeg($this->image, null, $quality);
                break;
            case IMAGETYPE_GIF:
                header('Content-Type: image/gif');
                imagegif($this->image);
                break;
            case IMAGETYPE_PNG:
                header('Content-Type: image/png');
                imagepng($this->image);
                break;
            default:
                throw new RuntimeException("Unsupported output image type");
        }
    }
   
    /**
     * Get image width
     * 
     * @return int Width in pixels
     */
    public function getWidth(): int {
        return imagesx($this->image);
    }
   
    /**
     * Get image height
     * 
     * @return int Height in pixels
     */
    public function getHeight(): int {
        return imagesy($this->image);
    }
   
    /**
     * Get image dimensions as array
     * 
     * @return array{width: int, height: int} Image dimensions
     */
    public function getDimensions(): array {
        return [
            'width' => $this->getWidth(),
            'height' => $this->getHeight()
        ];
    }
   
    /**
     * Resize image to specific height (maintain aspect ratio)
     * 
     * @param int $height Target height in pixels
     */
    public function resizeToHeight(int $height): void {
        $ratio = $height / $this->getHeight();
        $width = (int)round($this->getWidth() * $ratio);
        $this->resize($width, $height);
    }
   
    /**
     * Resize image to specific width (maintain aspect ratio)
     * 
     * @param int $width Target width in pixels
     */
    public function resizeToWidth(int $width): void {
        $ratio = $width / $this->getWidth();
        $height = (int)round($this->getHeight() * $ratio);
        $this->resize($width, $height);
    }
   
    /**
     * Create a square image by cropping from center
     * 
     * @param int $size Target square size in pixels
     */
    public function square(int $size): void {
        $newImage = imagecreatetruecolor($size, $size);
        
        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        if ($this->getWidth() > $this->getHeight()) {
            // Landscape orientation
            $this->resizeToHeight($size);
            $xOffset = (int)(($this->getWidth() - $size) / 2);
            $yOffset = 0;
        } else {
            // Portrait or square orientation
            $this->resizeToWidth($size);
            $xOffset = 0;
            $yOffset = (int)(($this->getHeight() - $size) / 2);
        }
        
        imagecopy(
            $newImage, $this->image,
            0, 0,
            $xOffset, $yOffset,
            $size, $size
        );
        
        imagedestroy($this->image);
        $this->image = $newImage;
    }
   
    /**
     * Scale image by percentage
     * 
     * @param int $scale Scale percentage (100 = original size)
     */
    public function scale(int $scale): void {
        $width = (int)($this->getWidth() * $scale / 100);
        $height = (int)($this->getHeight() * $scale / 100);
        $this->resize($width, $height);
    }
   
    /**
     * Resize image to exact dimensions
     * 
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @throws RuntimeException If resize fails
     */
    public function resize(int $width, int $height): void {
        
        // Validate dimensions
        $width = max(1, $width);
        $height = max(1, $height);
        
        $newImage = imagecreatetruecolor($width, $height);
        if ($newImage === false) {
            throw new RuntimeException("Failed to create new image resource");
        }
        
        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Resize image
        $resampled = imagecopyresampled(
            $newImage, $this->image,
            0, 0, 0, 0,
            $width, $height,
            $this->getWidth(), $this->getHeight()
        );
        
        if (!$resampled) {
            imagedestroy($newImage);
            throw new RuntimeException("Failed to resize image");
        }
        
        // Replace old image with new one
        imagedestroy($this->image);
        $this->image = $newImage;
    }
   
    /**
     * Cut/crop a portion of the image
     * 
     * @param int $x X coordinate of crop start
     * @param int $y Y coordinate of crop start
     * @param int $width Crop width
     * @param int $height Crop height
     * @throws RuntimeException If crop fails
     */
    public function cut(int $x, int $y, int $width, int $height): void {
        
        // Validate coordinates
        $x = max(0, min($x, $this->getWidth() - 1));
        $y = max(0, min($y, $this->getHeight() - 1));
        $width = min($width, $this->getWidth() - $x);
        $height = min($height, $this->getHeight() - $y);
        
        $newImage = imagecreatetruecolor($width, $height);
        if ($newImage === false) {
            throw new RuntimeException("Failed to create crop image");
        }
        
        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Copy cropped portion
        $copied = imagecopy($newImage, $this->image, 0, 0, $x, $y, $width, $height);
        
        if (!$copied) {
            imagedestroy($newImage);
            throw new RuntimeException("Failed to crop image");
        }
        
        imagedestroy($this->image);
        $this->image = $newImage;
    }
   
    /**
     * Ensure image doesn't exceed maximum dimensions
     * 
     * @param int $width Maximum width
     * @param int|null $height Maximum height (if null, same as width)
     */
    public function maxArea(int $width, ?int $height = null): void {
        $height = $height ?? $width;
        
        if ($this->getWidth() > $width) {
            $this->resizeToWidth($width);
        }
        if ($this->getHeight() > $height) {
            $this->resizeToHeight($height);
        }
    }
   
    /**
     * Ensure image meets minimum dimensions
     * 
     * @param int $width Minimum width
     * @param int|null $height Minimum height (if null, same as width)
     */
    public function minArea(int $width, ?int $height = null): void {
        $height = $height ?? $width;
        
        if ($this->getWidth() < $width) {
            $this->resizeToWidth($width);
        }
        if ($this->getHeight() < $height) {
            $this->resizeToHeight($height);
        }
    }
   
    /**
     * Crop image from center to specified dimensions
     * 
     * @param int $width Target width
     * @param int $height Target height
     */
    public function cutFromCenter(int $width, int $height): void {
        
        // First resize to ensure one dimension is at least the target size
        if ($width < $this->getWidth() && $width > $height) {
            $this->resizeToWidth($width);
        } elseif ($height < $this->getHeight()) {
            $this->resizeToHeight($height);
        }
        
        $x = (int)(($this->getWidth() / 2) - ($width / 2));
        $y = (int)(($this->getHeight() / 2) - ($height / 2));
        
        $this->cut($x, $y, $width, $height);
    }
   
    /**
     * Resize to fit within max dimensions and fill empty space with color
     * 
     * @param int $width Target width
     * @param int $height Target height
     * @param int $red Red component (0-255)
     * @param int $green Green component (0-255)
     * @param int $blue Blue component (0-255)
     * @throws RuntimeException If operation fails
     */
    public function maxAreaFill(int $width, int $height, int $red = 0, int $green = 0, int $blue = 0): void {
        
        // Validate color values
        $red = max(0, min(255, $red));
        $green = max(0, min(255, $green));
        $blue = max(0, min(255, $blue));
        
        $this->maxArea($width, $height);
        
        $newImage = imagecreatetruecolor($width, $height);
        if ($newImage === false) {
            throw new RuntimeException("Failed to create fill image");
        }
        
        // Fill background with specified color
        $colorFill = imagecolorallocate($newImage, $red, $green, $blue);
        imagefill($newImage, 0, 0, $colorFill);
        
        // Calculate position to center the original image
        $destX = (int)(($width - $this->getWidth()) / 2);
        $destY = (int)(($height - $this->getHeight()) / 2);
        
        // Copy resized image onto background
        $copied = imagecopyresampled(
            $newImage, $this->image,
            $destX, $destY, 0, 0,
            $this->getWidth(), $this->getHeight(),
            $this->getWidth(), $this->getHeight()
        );
        
        if (!$copied) {
            imagedestroy($newImage);
            throw new RuntimeException("Failed to create filled image");
        }
        
        imagedestroy($this->image);
        $this->image = $newImage;
    }
   
    /**
     * Get image MIME type
     * 
     * @return string MIME type
     */
    public function getMimeType(): string {
        return match ($this->imageType) {
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_PNG => 'image/png',
            default => 'application/octet-stream'
        };
    }
   
    /**
     * Get image type as string
     * 
     * @return string Image type (JPEG, GIF, PNG)
     */
    public function getImageTypeString(): string {
        return match ($this->imageType) {
            IMAGETYPE_JPEG => 'JPEG',
            IMAGETYPE_GIF => 'GIF',
            IMAGETYPE_PNG => 'PNG',
            default => 'UNKNOWN'
        };
    }
   
    /**
     * Rotate image
     * 
     * @param float $angle Rotation angle in degrees
     * @param int $bgdColor Background color index
     */
    public function rotate(float $angle, int $bgdColor = 0): void {
        $this->image = imagerotate($this->image, $angle, $bgdColor);
    }
   
    /**
     * Flip image horizontally
     */
    public function flipHorizontal(): void {
        imageflip($this->image, IMG_FLIP_HORIZONTAL);
    }
   
    /**
     * Flip image vertically
     */
    public function flipVertical(): void {
        imageflip($this->image, IMG_FLIP_VERTICAL);
    }
   
    /**
     * Flip image both horizontally and vertically
     */
    public function flipBoth(): void {
        imageflip($this->image, IMG_FLIP_BOTH);
    }
   
    /**
     * Destroy image resource (cleanup)
     */
    public function destroy(): void {
        if (isset($this->image) && is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }
   
    /**
     * Destructor - ensure image resource is freed
     */
    public function __destruct() {
        $this->destroy();
    }
}
?>