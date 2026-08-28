<?php
session_start();

/*
* File: CaptchaSecurityImages.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 03/08/06
* Updated: 07/02/07
* Requirements: PHP 4/5 with GD and FreeType libraries
* Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/

class CaptchaSecurityImages {

	//var $font = 'config/monofont.ttf';
        var $font = '../css/monofont.ttf';

	function generateCode($characters) {
		/* list all possible characters, similar looking characters and vowels have been removed */
		$possible = '123456789ABHTFRWPLZMGIS';
		$code = '';
		$i = 0;
		while ($i < $characters) { 
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		return $code;
	}

	function CaptchaSecurityImages($width='140',$height='40',$characters='6') {
		$code = $this->generateCode($characters);
		/* font size will be 95% of the image height */
		$font_size = $height * 0.95;
		###### Start add expiry header for captcha image ######
		$expiresOffset = 24* 3600 * 30;
		header("Content-type: image/gif");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expiresOffset) . " GMT");
		###### End add expiry header for captcha image ######
		$image = @imagecreate($width, $height);
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		//$background_color = imagecolorallocate($image, 254, 243, 243);
		//$text_color = imagecolorallocate($image, 20, 40, 100);
		$text_color = imagecolorallocate($image, 189, 23, 34);
		$noise_color = imagecolorallocate($image, 212, 220, 212);
		//$noise_color = imagecolorallocate($image, 160, 14, 14);
		/* generate random dots in background */
		for( $i=0; $i<($width*$height)/3; $i++ ) {
			imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
		}
		/* generate random lines in background */
		//for( $i=0; $i<($width*$height)/150; $i++ ) {
		//	imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
		//}
		/* create textbox and add text */
	//	$textbox = imagettfbbox($font_size, 0, $this->font, $code) or die('Error in imagettfbbox function');
		$x = ($width - $textbox[4])/2;
		$y = ($height - $textbox[5])/2;
		//imagestring($image, $font_size, 0, $x, $y, $text_color, $this->font , $code);
		imagestring($image, $font_size, 22, 5,  $code, $text_color);
		/* output captcha image to browser */
		header('Content-Type: image/jpeg');
		imagejpeg($image);
		imagedestroy($image);
		$_SESSION['admin_login_security_code'] = $code;
	}
}

$width = isset($_GET['width']) ? $_GET['width'] : '100';
$height = isset($_GET['height']) ? $_GET['height'] : '25';
$characters = isset($_GET['characters']) && $_GET['characters'] > 1 ? $_GET['characters'] : '6';

$captcha = new CaptchaSecurityImages($width,$height,$characters);
 
?>