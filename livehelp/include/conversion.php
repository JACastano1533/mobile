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

if (!isset($_REQUEST['txid'])){ $_REQUEST['txid'] = ''; }
if (!isset($_REQUEST['affil'])){ $_REQUEST['affil'] = ''; }
if (!isset($_REQUEST['total'])){ $_REQUEST['total'] = ''; }
if (!isset($_REQUEST['tax'])){ $_REQUEST['tax'] = ''; }
if (!isset($_REQUEST['ship'])){ $_REQUEST['ship'] = ''; }
if (!isset($_REQUEST['city'])){ $_REQUEST['city'] = ''; }
if (!isset($_REQUEST['country'])){ $_REQUEST['country'] = ''; }
if (!isset($_REQUEST['im'])){ $_REQUEST['im'] = ''; }

include('./database.php');
include('./class.mysql.php');
include('./config.php');
include('./functions.php');

function validatePrice($double) {
	if ((preg_match('/^[0-9]*([\.]{1}[0-9]{1,2})?$/', $double) == true) && ($double > 0)) {
		return $double;
	}
	return 0;
}

if ($_REQUEST['txid'] != '' && isset($_SETTINGS['ANALYTICS'])) {

	$_REQUEST['total'] = validatePrice($_REQUEST['total']);
	$_REQUEST['tax'] = validatePrice($_REQUEST['tax']);
	$_REQUEST['ship'] = validatePrice($_REQUEST['ship']);

	$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "orders(`order`, `request`, `datetime`, `affiliation`, `total`, `tax`, `shipping`, `city`, `state`, `country`) VALUES('%s', '%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $SQL->escape($_REQUEST['txid']), $SQL->escape($request), $SQL->escape($_REQUEST['affil']), $SQL->escape($_REQUEST['total']), $SQL->escape($_REQUEST['tax']), $SQL->escape($_REQUEST['ship']), $SQL->escape($_REQUEST['city']), $SQL->escape($_REQUEST['state']), $SQL->escape($_REQUEST['country']));
	$order = $SQL->insertquery($query);
	
	if ($order != false) {
	
		$items = ''; $count = 0;
		foreach ($_REQUEST['im'] as $key => $item) {
	
			$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "products WHERE `code` = '%s' LIMIT 1", $SQL->escape($item['sku']));
			$product = $SQL->selectquery($query);
			if ($product == false) {
				$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "products(`code`, `name`) VALUES ('%s', '%s')", $SQL->escape($item['sku']), $SQL->escape($item['name']));
				$product = $SQL->insertquery($query);
			}
			
			$item['price'] = validatePrice($item['price']);
			if ($count == 0) {
				$items = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "orderproducts(`order`, `product`, `category`, `price`, `quantity`) VALUES ('%s', '%s', '%s', '%s', '%s')", $SQL->escape($order), $SQL->escape($product), $SQL->escape($item['category']), $SQL->escape($item['price']), $SQL->escape($item['qty'])); 
			} elseif ($count > 0) {
				$items .= sprintf(", ('%s', '%s', '%s', '%s', '%s')", $SQL->escape($order), $SQL->escape($product), $SQL->escape($item['category']), $SQL->escape($item['price']), $SQL->escape($item['qty']));
			}
			$count++;
		}
		$SQL->insertquery($items);
	}
}

header('Content-type: image/gif');
$fp = @fopen('./Offline.gif', 'rb');
if ($fp == false) {
	header('Location: ' . $_SETTINGS['URL'] . '/livehelp/include/Offline.gif');
} else {
	$contents = fread($fp, filesize('./Offline.gif'));
	echo($contents);
}
fclose($fp);
exit();

?>