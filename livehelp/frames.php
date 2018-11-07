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
// Smarty Template
require('include/smarty/Smarty.class.php');

include('include/spiders.php');
include('include/database.php');
include('include/class.mysql.php');
include('include/class.aes.php');
include('include/class.cookie.php');
include('include/class.push.php');
include('include/config.php');
include('include/functions.php');
include('include/version.php');

if (!isset($_REQUEST['NAME'])){ $_REQUEST['NAME'] = ''; }
if (!isset($_REQUEST['EMAIL'])){ $_REQUEST['EMAIL'] = ''; }
if (!isset($_REQUEST['QUESTION'])){ $_REQUEST['QUESTION'] = ''; }
if (!isset($_REQUEST['DEPARTMENT'])){ $_REQUEST['DEPARTMENT'] = ''; }
if (!isset($_REQUEST['SERVER'])){ $_REQUEST['SERVER'] = ''; }
if (!isset($_REQUEST['URL'])){ $_REQUEST['URL'] = ''; }

if (!isset($_REQUEST['OTHER']) || !empty($_REQUEST['OTHER'])) {
	header('HTTP/1.1 403 Access Forbidden');
	header('Content-Type: text/plain');  
	exit();
}

$user = trim($_REQUEST['NAME']);
$email = trim($_REQUEST['EMAIL']);
$department = trim($_REQUEST['DEPARTMENT']);
$question = trim($_REQUEST['QUESTION']);
$server = trim($_REQUEST['SERVER']);
$referer = $_REQUEST['URL'];
$ipaddress = $_SERVER['REMOTE_ADDR'];
$json = (isset($_REQUEST['JSON'])) ? true : false;
$active = 0;

if ($json) {
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
			header('Access-Control-Allow-Headers: X-Requested-With');
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 1728000');
			header('Content-Length: 0');
			header('Content-Type: text/plain');
			exit();
		} else {
			header('HTTP/1.1 403 Access Forbidden');
			header('Content-Type: text/plain');  
			exit();
		}
	} else {
		// AJAX Cross-site Headers
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Credentials: true');
		}
	}
}

// Override Session
$request = 0;
$chat = 0;
if (isset($_REQUEST['SESSION'])) {
	$cookie = rawurldecode($_REQUEST['SESSION']);

	$aes = new AES256($_SETTINGS['AUTHKEY']);

	$size = strlen($aes->iv);
	$iv = substr($cookie, 0, $size);
	$verify = substr($cookie, $size, 40);
	$ciphertext = substr($cookie, 40 + $size);

	$decrypted = $aes->decrypt($ciphertext, $iv);
	
	if (sha1($decrypted) == $verify) {
		$cookie = json_decode($decrypted, true);
		$request = $cookie['visitor'];
		$chat = $cookie['chat'];
	}
}

if (empty($user)) { $user = 'Guest'; }

// Reset Previous Chat History
if ($_SETTINGS['PREVIOUSCHATTRANSCRIPTS'] == false) { $chat = 0; }

// Existing Chat / Skip Verification
if ($chat > 0) {
	$query = sprintf("SELECT `username`, `email`, `server`, `department`, `active` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` WHERE `id` = '%d' AND `active` > 0 LIMIT 1", $chat);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
	
		// Chat Details
		$user = $row['user'];
		$email = $row['email'];
		$server = $row['server'];
		$department = $row['department'];
		$active = $row['active'];
		
	} else {
	
		// Update Chat
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `request` = '%d', `username` = '%s', `datetime` = NOW(), `email` = '%s', `server` = '%s', `department` = '%s', `refresh` = NOW(), `active` = '0' WHERE `id` = '%d'", $request, $SQL->escape($user), $SQL->escape($email), $SQL->escape($server), $SQL->escape($department), $chat);
		$SQL->updatequery($query);
	
	}

}
else {

	// Override Validation / Initiate Chat
	$override = false;
	$query = sprintf("SELECT `initiate` FROM `" . $_SETTINGS['TABLEPREFIX'] . "requests` WHERE `id` = '%d' LIMIT 1", $request);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$initiate = (int)$row['initiate'];
		if ($initiate < 0) {
			$override = true;
		}
	}

	// Verification
	if ($_SETTINGS['REQUIREGUESTDETAILS'] == true && $_SETTINGS['LOGINDETAILS'] == true && $override == false) {

		if (file_exists('locale/' . LANGUAGE . '/guest.php')) {
			include('locale/' . LANGUAGE . '/guest.php');
		}
		else {
			include('locale/en/guest.php');
		}

		if (!empty($department)) { $departmentquery = '&DEPARTMENT=' . $department; }
		if (empty($user) || (empty($email) && $_SETTINGS['LOGINEMAIL'] == true)) {
			if ($json) {
				$json = array();
				$json['error'] = $_LOCALE['invaliddetailserror'];
				$json = json_encode($json);
				if (!isset($_GET['callback'])) {
					header('Content-Type: application/json; charset=utf-8');
					exit($json);
				} else {
					if (is_valid_callback($_GET['callback'])) {
						header('Content-Type: text/javascript; charset=utf-8');
						exit($_GET['callback'] . '(' . $json . ')');
					} else {
						header('Status: 400 Bad Request');
						exit();
					}
				}
			} else {
				header('Location: index.php?ERROR=empty' . $departmentquery);
			}
			exit();
		}
		else if ($_SETTINGS['LOGINEMAIL'] == true) {
			if (!preg_match('/^[\-!#$%&\'*+\\\\.\/0-9=?A-Z\^_`a-z{|}~]+@[\-!#$%&\'*+\\\\\/0-9=?A-Z\^_`a-z{|}~]+\.[\-!#$%&\'*+\\\\.\/0-9=?A-Z\^_`a-z{|}~]+$/', $email)) {
				if ($json) {
					$json = array();
					$json['error'] = $_LOCALE['invalidemail'];
					$json = json_encode($json);
					if (!isset($_GET['callback'])) {
						header('Content-Type: application/json; charset=utf-8');
						exit($json);
					} else {
						if (is_valid_callback($_GET['callback'])) {
							header('Content-Type: text/javascript; charset=utf-8');
							exit($_GET['callback'] . '(' . $json . ')');
						} else {
							header('Status: 400 Bad Request');
							exit();
						}
					}
				} else {
					header('Location: index.php?ERROR=email' . $departmentquery);
				}
				exit();
			}
		}
	}
	
	// Add Chat Session
	$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "chats (`request`, `username`, `datetime`, `email`, `server`, `department`, `refresh`) VALUES ('%d', '%s', NOW(), '%s', '%s', '%s', NOW())", $request, $SQL->escape($user), $SQL->escape($email), $SQL->escape($server), $SQL->escape($department));
	$chat = $SQL->insertquery($query);

}

if ($request > 0) {

	// Visitor Details
	$query = sprintf("SELECT `id` FROM `" . $_SETTINGS['TABLEPREFIX'] . "requests` AS `requests` WHERE `id` = '%d' LIMIT 1", $request);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$request = $row['id'];
	}

	// Update Chat Session
	if ($active == -3 || $active == -1) {
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `request` = '%d', `username` = '%s', `datetime` = NOW(), `email` = '%s', `server` = '%s', `department` = '%s', `refresh` = NOW(), `active` = '0' WHERE `id` = '%d'", $request, $SQL->escape($user), $SQL->escape($email), $SQL->escape($server), $SQL->escape($department), $chat);
		$SQL->updatequery($query);
	}
}

// Online Operators
if ((float)$_SETTINGS['SERVERVERSION'] >= 4.1) { // Multiple Device PUSH Supported
	$query = sprintf("SELECT `users`.`id` AS `id`, `devices`.`token` AS `device`, `unique` FROM " . $_SETTINGS['TABLEPREFIX'] . "users AS `users` LEFT JOIN " . $_SETTINGS['TABLEPREFIX'] . "devices AS `devices` ON `users`.`id` = `devices`.`user` WHERE (`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1') OR (`token` <> '' AND `status` = '1')", $_SETTINGS['CONNECTIONTIMEOUT']);
} elseif ((float)$_SETTINGS['SERVERVERSION'] >= 3.80) { // iPhone PUSH Supported
	$query = sprintf("SELECT `id`, `device` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE (`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1') OR (`device` <> '' AND `status` = '1')", $_SETTINGS['CONNECTIONTIMEOUT']);
} else {
	$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '1'", $_SETTINGS['CONNECTIONTIMEOUT']);
}
if ($_SETTINGS['DEPARTMENTS'] == true && !empty($department)) { $query .= sprintf(" AND `department` LIKE '%%%s%%'", $SQL->escape($department)); }
if ((float)$_SETTINGS['SERVERVERSION'] >= 4.1) { $query .= sprintf(" GROUP BY `devices`.`id` ORDER BY `users`.`datetime` DESC"); }
$rows = $SQL->selectall($query);

$devices = array();
if (is_array($rows)) {
	// iPhone / Android Devices
	if ((float)$_SETTINGS['SERVERVERSION'] >= 4.1) {
		$unique = array();
		foreach ($rows as $key => $row) {
			if (!in_array($row['unique'], $unique)) {
				$unique[] = $row['unique'];
				$device = $row['device'];
				if (!empty($device)) {
					$devices[] = $device;
				}
			}
		}
	} else { // iPhone PUSH Supported
		foreach ($rows as $key => $row) {
			$device = $row['device'];
			if (!empty($device)) {
				$devices[] = $device;
			}
		}
	}
}
else {
	if ($json) {
		$json = array();
		$json['status'] = 'Offline';
		$json = json_encode($json);
		if (!isset($_GET['callback'])) {
			header('Content-Type: application/json; charset=utf-8');
			exit($json);
		} else {
			if (is_valid_callback($_GET['callback'])) {
				header('Content-Type: text/javascript; charset=utf-8');
				exit($_GET['callback'] . '(' . $json . ')');
			} else {
				header('Status: 400 Bad Request');
				exit();
			}
		}
	} else {
		header('Location: offline.php?SERVER=' . $server);
	}
	exit();
}

if (empty($user)) { $user = 'Guest'; }

$server = $_SETTINGS['URL'];

// Hostname
if ($request > 0) {
	$query = sprintf("SELECT `url` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `id` = '%d' LIMIT 1", $request);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$server = $row['url'];

		for ($i = 0; $i < 3; $i++) {
			$substr_pos = strpos($server, '/');
			if ($substr_pos === false) {
				break;
			}
			if ($i < 2) {
				$server = substr($server, $substr_pos + 1);
			}
			else {
				$server = substr($server, 0, $substr_pos);
			}
		
		}
		if (substr($server, 0, 4) == 'www.') { $server = substr($server, 4); }
	}
}

// Update Activity
if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
	// Insert Requested Live Help
	$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity (`user`, `username`, `datetime`, `activity`, `type`, `status`) VALUES ('%d', '%s', NOW(), 'requested Live Help with %s', 8, 0)", $chat, $SQL->escape($user), $SQL->escape($department));
	$SQL->insertquery($query);
}

// Send Guest Initial Question as chat message if different from previous
if (!empty($question)) {
	$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`) VALUES ('%d', '%s', NOW(), '%s', '1')", $chat, $SQL->escape($user), $SQL->escape($question));
	$SQL->insertquery($query);
}
	
// Cancel Initiate Chat
if ($request > 0) {
	$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "requests SET `initiate` = '-4' WHERE `id` = '%d'", $request);
	$SQL->updatequery($query);
}

// Current Server
if ($server != '') {
	$query = sprintf("SELECT `server` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = '%d' LIMIT 1", $chat);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$server = $row['server'];
	}
}

// TODO AJAX Total Pending Visitors / Average Wait
$query = sprintf("SELECT `department` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = '%d' LIMIT 1", $chat);
$row = $SQL->selectquery($query);
if (is_array($row)) {
	$department = $row['department'];
	$query = sprintf("SELECT count(`id`) FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `active` = '0' AND `department` LIKE '%%%s%%' LIMIT 1", $_SETTINGS['CONNECTIONTIMEOUT'], $SQL->escape($department));
}
else {
	$query = sprintf("SELECT count(`id`) FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `active` = '0' LIMIT 1", $_SETTINGS['CONNECTIONTIMEOUT']);
}
$row = $SQL->selectquery($query);
if (is_array($row)) {
	$online = $row['count(`id`)'];
}
else {
	$online = '1';
}

// Pending Chat Device Notification
$badge = (is_numeric($online) ? $online : 0);
$hooks->run('PendingChat', array($user, $server, $badge, $chat, $devices));


if ($_SETTINGS['LOGO'] != '') { $margin = 16; $footer = -10; $textmargin = 15; } else { $margin = 50; $footer = 30; $textmargin = 50; }

if (file_exists('locale/' . LANGUAGE . '/guest.php')) {
	include('locale/' . LANGUAGE . '/guest.php');
}
else {
	include('locale/en/guest.php');
}

// Encrypt Session
if ($chat > 0 || $request > 0) {

	$cookie = array('visitor' => (int)$request, 'chat' => (int)$chat);
	$cookie = json_encode($cookie);
	$verify = sha1($cookie);

	$aes = new AES256($_SETTINGS['AUTHKEY']);
	$session = $aes->iv . $verify . $aes->encrypt($cookie);
}

if ($json) {
	$json = array();
	$json['visitor'] = $visitor;
	$json['chat'] = $chat;
	$json['session'] = $session;
	$json['user'] = $user;
	$json = json_encode($json);
	if (!isset($_GET['callback'])) {
		header('Content-Type: application/json; charset=utf-8');
		exit($json);
	} else {
		if (is_valid_callback($_GET['callback'])) {
			header('Content-Type: text/javascript; charset=utf-8');
			exit($_GET['callback'] . '(' . $json . ')');
		} else {
			header('Status: 400 Bad Request');
			exit();
		}
	}
} else {
	header('Status: 400 Bad Request');
	exit();
}

?>