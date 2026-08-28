<?php
/**
 * File: simpleimage.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: فئة بسيطة لمعالجة الصور - تغيير الحجم، الحفظ، والعرض
 * Simple image manipulation class - resize, save, and output
 * 
 * Features:
 * - تحميل الصور (JPEG, GIF, PNG)
 * - تغيير الحجم بنسب مختلفة
 * - حفظ الصور بتنسيقات متعددة
 * - دعم الشفافية في PNG و GIF
 * - معالجة ذكية للصور
 * 
 * @package ImageProcessing
 * @subpackage SimpleImage
 * @license GPL
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('STDIN')) {
    //exit('Direct access not allowed');
//}

/**
 * Class SimpleImage
 * 
 * Simple image manipulation class
 */
class SimpleImage {
   
   /** @var resource GD image resource */
   private $image;
   
   /** @var int Image type constant (IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG) */
   private int $imageType;
   
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
            throw new RuntimeException("Unsupported image type: {$this->imageType}");
      }
      
      if ($this->image === false) {
         throw new RuntimeException("Failed to create image from: {$filename}");
      }
   }
   
   /**
    * Save image to file
    * 
    * @param string $filename Output filename
    * @param int $imageType Image type constant (default: original type)
    * @param int $compression JPEG compression quality (0-100)
    * @param int|null $permissions File permissions (octal)
    */
   public function save(
      string $filename, 
      int $imageType = IMAGETYPE_JPEG, 
      int $compression = 75, 
      ?int $permissions = null
   ): void {
      // Use original image type if not specified
      $outputType = $imageType;
      
      switch ($this->imageType) {
         case IMAGETYPE_JPEG:
            imagejpeg($this->image, $filename, $compression);
            break;
         case IMAGETYPE_GIF:
            imagegif($this->image, $filename);
            break;
         case IMAGETYPE_PNG:
            // Preserve transparency for PNG
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
            imagepng($this->image, $filename);
            break;
         default:
            throw new RuntimeException("Unsupported image type for saving");
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
    */
   public function output(int $imageType = IMAGETYPE_JPEG): void {
      switch ($imageType) {
         case IMAGETYPE_JPEG:
            header('Content-Type: image/jpeg');
            imagejpeg($this->image);
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
    * Resize image to specific height (maintain aspect ratio)
    * 
    * @param int $height Target height in pixels
    */
   public function resizeToHeight(int $height): void {
      $ratio = $height / $this->getHeight();
      $width = (int)($this->getWidth() * $ratio);
      $this->resize($width, $height);
   }
   
   /**
    * Resize image to specific width (maintain aspect ratio)
    * 
    * @param int $width Target width in pixels
    */
   public function resizeToWidth(int $width): void {
      $ratio = $width / $this->getWidth();
      $height = (int)($this->getHeight() * $ratio);
      $this->resize($width, $height);
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
    * @param bool $forceSize Force resize even if image is smaller (optional)
    */
   public function resize(int $width, int $height, bool $forceSize = false): void {
      
      // Optional: Skip if image is smaller than target and not forced
      if (!$forceSize) {
         if ($width > $this->getWidth() && $height > $this->getHeight()) {
            $width = $this->getWidth();
            $height = $this->getHeight();
         }
      }
      
      // Create new image
      $newImage = imagecreatetruecolor($width, $height);
      if ($newImage === false) {
         throw new RuntimeException("Failed to create new image resource");
      }
      
      // Preserve transparency for GIF and PNG
      if ($this->imageType === IMAGETYPE_GIF || $this->imageType === IMAGETYPE_PNG) {
         imagealphablending($newImage, false);
         imagesavealpha($newImage, true);
         $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
         if ($transparent !== false) {
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
         }
      }
      
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
    * Crop image to specific dimensions
    * 
    * @param int $x X coordinate of crop start
    * @param int $y Y coordinate of crop start
    * @param int $width Crop width
    * @param int $height Crop height
    */
   public function crop(int $x, int $y, int $width, int $height): void {
      $newImage = imagecreatetruecolor($width, $height);
      if ($newImage === false) {
         throw new RuntimeException("Failed to create crop image");
      }
      
      // Preserve transparency
      if ($this->imageType === IMAGETYPE_GIF || $this->imageType === IMAGETYPE_PNG) {
         imagealphablending($newImage, false);
         imagesavealpha($newImage, true);
      }
      
      imagecopy($newImage, $this->image, 0, 0, $x, $y, $width, $height);
      
      imagedestroy($this->image);
      $this->image = $newImage;
   }
   
   /**
    * Get image type as string
    * 
    * @return string Image type (JPEG, GIF, PNG)
    */
   public function getImageTypeString(): string {
      switch ($this->imageType) {
         case IMAGETYPE_JPEG:
            return 'JPEG';
         case IMAGETYPE_GIF:
            return 'GIF';
         case IMAGETYPE_PNG:
            return 'PNG';
         default:
            return 'UNKNOWN';
      }
   }
   
   /**
    * Get image MIME type
    * 
    * @return string MIME type
    */
   public function getMimeType(): string {
      switch ($this->imageType) {
         case IMAGETYPE_JPEG:
            return 'image/jpeg';
         case IMAGETYPE_GIF:
            return 'image/gif';
         case IMAGETYPE_PNG:
            return 'image/png';
         default:
            return 'application/octet-stream';
      }
   }
   
   /**
    * Destroy image resource (cleanup)
    */
   public function destroy(): void {
      if ($this->image !== null) {
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