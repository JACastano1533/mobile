<?php
/*
stardevelop.com Live Help
International Copyright stardevelop.com

You may not distribute this program in any manner,
modified or otherwise, without the express, written
consent from stardevelop.com

You may make modifications, but only for your own 
use and within the confines of the License Agreement.
All rights reserved.

Selling the code for this program without prior 
written consent is expressly forbidden. Obtain 
permission before redistributing this program over 
the Internet or in any other medium.  In all cases 
copyright and header must remain intact.  
*/
include('../include/database.php');
include('../include/class.mysql.php');
include('../include/config.php');
include('../include/version.php');
include('../include/functions.php');

// TODO Add Security Token Setting

if ((function_exists('imagepng') || function_exists('imagejpeg')) && function_exists('imagettftext')) {

	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){ 
		$cut = imagecreatetruecolor($src_w, $src_h); 
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
	} 

	function imagettftextsp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0) {        
		if ($spacing == 0) {
			imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
		} else {
			$temp_x = $x;
			for ($i = 0; $i < strlen($text); $i++) {
				$bbox = imagettftext($image, $size, $angle, $temp_x, $y, $color, $font, $text[$i]);
				$temp_x += $spacing + ($bbox[2] - $bbox[0]);
			}
		}
	}

	function hex2rgb($hex) {
		$color = str_replace('#','',$hex);
		$rgb = array(hexdec(substr($color,0,2)), hexdec(substr($color,2,2)), hexdec(substr($color,4,2)));
		return $rgb;
	}
	
	// Total Visitors
	if ($_SETTINGS['SERVERVERSION'] >= 4.10) {
		if (file_exists('../plugins/cloud/admin.js')) {
			$where = sprintf("((`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0') OR `status` = '2')", $_SETTINGS['VISITORTIMEOUT']);
		} else {
			$where = sprintf("`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0'", $_SETTINGS['VISITORTIMEOUT']);
		}
		$query = "SELECT COUNT(*) AS `total` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests AS `requests` LEFT JOIN " . $_SETTINGS['TABLEPREFIX'] . "geolocation AS `geo` ON `requests`.`id` = `geo`.`request` WHERE $where ORDER BY `requests`.`id` ASC";
	} else {
		$query = sprintf("SELECT COUNT(*) AS `total` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0' ORDER BY `id` ASC", $_SETTINGS['VISITORTIMEOUT']);
	}
	$row = $SQL->selectquery($query);
	$visitors = 0;
	if (is_array($row)) {
		$visitors = $row['total'];
	}

	// Chatting Visitors
	$query = "SELECT COUNT(*) AS `total` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats AS chats, " . $_SETTINGS['TABLEPREFIX'] . "users AS users WHERE chats.active = users.id AND chats.refresh > DATE_SUB(NOW(), INTERVAL " . $_SETTINGS['CONNECTIONTIMEOUT'] . " SECOND) AND chats.active > '0' ORDER BY chats.username";
	$row = $SQL->selectquery($query);
	$chats = 0;
	if (is_array($row)) {
		$chats = $row['total'];
	}

	$rgb = hex2rgb('#E2E2E2');
	$image = imagecreatetruecolor(310, 150);
	$bg = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
	imagefilledrectangle($image, 0, 0, 310, 150, $bg);

	imagealphablending($image, true);
	imagesavealpha($image, true);

	// Transparent Background
	//imagecolortransparent($image, $bg);

	$insert = imagecreatefrompng('images/Win8TileWide.png'); 
	$x = imagesx($insert);
	$y = imagesy($insert);
	imagecopymerge_alpha($image, $insert, 0, 0, 0, 0, $x, $y, 100);
	imagedestroy($insert);

	$insert = imagecreatefrompng('images/VisitorsTotal.png');
	$x = imagesx($insert);
	$y = imagesy($insert);
	imagecopymerge_alpha($image, $insert, 130, 90, 0, 0, $x, $y, 100);
	imagedestroy($insert);

	$insert = imagecreatefrompng('images/ChatsTotal.png');
	$x = imagesx($insert);
	$y = imagesy($insert);
	imagecopymerge_alpha($image, $insert, 220, 90, 0, 0, $x, $y, 100);
	imagedestroy($insert);

	// Create random angle
	$size = 32;
	$color = imagecolorallocate($image, 170, 170, 170);
	$path = dirname(__FILE__);
	if (substr($path, 0, 2) == '\\\\') { $path = '//' . substr($path, 2); }
	
	if (substr($path, -1) == '/') {
		$font = $path . '../styles/fonts/OpenSans-Light-webfont.ttf';
	} else {
		$font = $path . '/../styles/fonts/OpenSans-Light-webfont.ttf';
	}
	
	// Visitor Total
	imagettftextsp($image, $size, 0, 165, 120, $color, $font, $visitors, 4);

	// Chats Total
	imagettftextsp($image, $size, 0, 260, 120, $color, $font, $chats, 4);
	
	if (function_exists('imagepng')) {
		// Output GIF Image
		header('Content-Type: image/png');
		imagepng($image);
	}
	elseif (function_exists('imagejpeg')) {
		// Output JPEG Image
		header('Content-Type: image/jpeg');
		imagejpeg($image, '', 100);
	}
	
	// Destroy the image to free memory
	imagedestroy($image);
	exit();

}
else {

	if (strpos(php_sapi_name(), 'cgi') === false ) { header('HTTP/1.0 404 Not Found'); } else { header('Status: 404 Not Found'); }
	exit;
	
}

?>