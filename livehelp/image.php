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
	
include('include/database.php');
include('include/class.mysql.php');
include('include/class.aes.php');
include('include/class.cookie.php');
include('include/config.php');
include('include/functions.php');
include('include/version.php');

if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = 0; }
if (!isset($_REQUEST['SIZE'])){ $_REQUEST['SIZE'] = -1; }
if (!isset($_REQUEST['DEFAULT'])){ $_REQUEST['DEFAULT'] = ''; }
if (!isset($_REQUEST['DEPARTMENT'])){ $_REQUEST['DEPARTMENT'] = ''; }

$row = '';
$id = $_REQUEST['ID'];
$size = $_REQUEST['SIZE'];
$round = (isset($_REQUEST['ROUND'])) ? true : false;
$default = $_REQUEST['DEFAULT'];
$updated = '';
$department = trim($_REQUEST['DEPARTMENT']);

if ($id > 0) {
	$query = sprintf("SELECT `image`, `updated` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d", $id, $department);
	$row = $SQL->selectquery($query);
} else {

	// Operators
	$id = 0;
	$ids = array();

	// Online Operators
	if ((float)$_SETTINGS['SERVERVERSION'] >= 4.1) { // Multiple Device PUSH Supported
		$query = sprintf("SELECT `users`.`id` AS `id`, `department` FROM " . $_SETTINGS['TABLEPREFIX'] . "users AS `users` LEFT JOIN " . $_SETTINGS['TABLEPREFIX'] . "devices AS `devices` ON `users`.`id` = `devices`.`user` WHERE (`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1') OR (`token` <> '' AND `status` = '1')", $_SETTINGS['CONNECTIONTIMEOUT']);
	} elseif ((float)$_SETTINGS['SERVERVERSION'] >= 3.80) { // iPhone PUSH Supported
		$query = sprintf("SELECT `id`, `department` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE (`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1') OR (`device` <> '' AND `status` = '1')", $_SETTINGS['CONNECTIONTIMEOUT']);
	} else {
		$query = sprintf("SELECT `id`, `department` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1'", $_SETTINGS['CONNECTIONTIMEOUT']);
	}
	$rows = $SQL->selectall($query);
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {

				if (!empty($department)) {
					$departments = explode(';', $row['id']);
					if (is_array($departments)) {
						foreach ($departments as $key => $value) {
							if ($department == trim($value)) {
								$ids[] = $row['id'];
							}
						}
					}
				} else {
					$ids[] = $row['id'];
				}
			}
		}
	}
	if (count($ids) > 0) {
		$id = $ids[array_rand($ids)];

		$query = sprintf("SELECT `image`, `updated` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d", $id);
		$row = $SQL->selectquery($query);
	}
}

$im = false;
if (!empty($row) && is_array($row) && !empty($row['image'])) {
	$base64 = $row['image'];
	$updated = $row['updated'];
}

// Cache Image
$updated = strtotime($updated);
header('Cache-Control: public');
header('Expires: ' . date(DATE_RFC822, strtotime('+2 day')));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $updated) . ' GMT', true, 200);

// Last Modified
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $updated)) {
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $updated) . ' GMT', true, 304);
	exit();
}

if (!empty($base64)) {
	$im = imagecreatefromstring(base64_decode($base64));
} else {
	if ($default == '404') {
		header('HTTP/1.0 404 Not Found');
		exit();
	}
	$im = @imagecreatefrompng('./images/User.png');
}

if ($im == false) {
	if ($default == '404') {
		header('HTTP/1.0 404 Not Found');
		exit();
	}
	$im = @imagecreatefrompng('./images/User.png');
}

if ($im != false) {
	if ($size > 0) {
		$width = imagesx($im);
		$height = imagesy($im);
		$aspect_ratio = $height / $width;

		if ($width <= $size) {
			$neww = $width;
			$newh = $height;
		} else {
			$neww = $size;
			$newh = abs($neww * $aspect_ratio);
		}

		# Round Image
		if ($round == true) {
			do {
				$r = rand(0, 255);
				$g = rand(0, 255);
				$b = rand(0, 255);
			}
			while (imagecolorexact($src, $r, $g, $b) < 0);

			$mask = imagecreatetruecolor($width, $height);
			$alphamaskcolor = imagecolorallocate($mask, $r, $g, $b);
			imagecolortransparent($mask, $alphamaskcolor);
			imagefilledellipse($mask, $width / 2, $height / 2, $width, $height, $alphamaskcolor);
			imagecopymerge($im, $mask, 0, 0, 0, 0, $width, $height, 100);

			$alphacolor = imagecolorallocatealpha($im, $r, $g, $b, 127);
			imagefill($im, 0, 0, $alphacolor);
			imagefill($im, $width - 1, 0, $alphacolor);
			imagefill($im, 0, $height - 1, $alphacolor);
			imagefill($im, $width - 1, $height - 1, $alphacolor);
			imagecolortransparent($im, $alphacolor);
		}

		$image = imagecreatetruecolor($neww, $newh); 
		
		imagealphablending($image, false);
		imagesavealpha($image, true);
		
		// Preserve Transparency
		//imagecolortransparent($image, imagecolorallocate($image, 0, 0, 0));

		# Resize
		imagecopyresampled($image, $im, 0, 0, 0, 0, $neww, $newh, $width, $height);

		# Content Type Header
		header('Content-Type: image/png');

		# Output the image
		imagepng($image);

		# Free Memory
		imagedestroy($mask);
		imagedestroy($im);
		imagedestroy($image);

		
	} else {

		# Content Type Header
		header('Content-Type: image/png');

		imagealphablending($im, false);
		imagesavealpha($im, true);

		# Output the image
		imagepng($im);

		# Free Memory
		imagedestroy($im);
	
	}
} else {
	if ($default == '404') {
		header('HTTP/1.0 404 Not Found');
		exit();
	}
	header('Location: ./images/User.png');
	exit();
}

?>