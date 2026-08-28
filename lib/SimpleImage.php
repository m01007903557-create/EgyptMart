<?php
/**
 * SimpleImage.php - نسخة PHP 8.3
 * مكتبة بسيطة لمعالجة الصور
 */

declare(strict_types=1);

class SimpleImage {
    
    private $image = null;
    private int $imageType = 0;
    private int $width = 0;
    private int $height = 0;
    
    /**
     * تحميل صورة من ملف
     */
    public function load(string $filename): bool {
        if (!file_exists($filename)) {
            throw new Exception('الملف غير موجود: ' . $filename);
        }
        
        $imageInfo = getimagesize($filename);
        if (!$imageInfo) {
            throw new Exception('ملف صورة غير صالح');
        }
        
        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
        $this->imageType = $imageInfo[2];
        
        if ($this->image) {
            imagedestroy($this->image);
        }
        
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filename);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filename);
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;
            default:
                throw new Exception('نوع الصورة غير مدعوم');
        }
        
        return $this->image !== false;
    }
    
    /**
     * حفظ الصورة
     */
    public function save($filename, int $type = IMAGETYPE_JPEG, int $compression = 75): bool {
        if (!$this->image) {
            return false;
        }
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($this->image, $filename, $compression);
            case IMAGETYPE_GIF:
                return imagegif($this->image, $filename);
            case IMAGETYPE_PNG:
                return imagepng($this->image, $filename);
        }
        return false;
    }
    
    /**
     * تغيير حجم الصورة
     */
    public function resize(int $width, int $height): bool {
        if (!$this->image) {
            return false;
        }
        
        $newImage = imagecreatetruecolor($width, $height);
        
        // الحفاظ على الشفافية للـ PNG
        if ($this->imageType == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;
        
        return true;
    }
}
?>