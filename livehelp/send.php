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
include('include/class.push.php');
include('include/config.php');
include('include/functions.php');

if (!isset($_REQUEST['JSON'])){ $_REQUEST['JSON'] = ''; }

ignore_user_abort(true);

if (!isset($_REQUEST['STAFF'])){ $_REQUEST['STAFF'] = ''; }
if (!isset($_REQUEST['MESSAGE'])){ $_REQUEST['MESSAGE'] = ''; }
if (!isset($_REQUEST['RESPONSE'])){ $_REQUEST['RESPONSE'] = ''; }
if (!isset($_REQUEST['COMMAND'])){ $_REQUEST['COMMAND'] = ''; }
if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }

$id = intval($_REQUEST['ID']);
$staff = $_REQUEST['STAFF'];
$message = trim($_REQUEST['MESSAGE']);
$response = trim($_REQUEST['RESPONSE']);
$command = intval(trim($_REQUEST['COMMAND']));

// Check if the message contains any content else return headers
if (empty($message) && empty($response) && empty($command)) { exit(); }

if (isset($_COOKIE['LiveHelpOperator']) && !empty($id) && $id > 0) {
	
	$cookie = new Cookie();
	$session = $cookie->decode($_COOKIE['LiveHelpOperator']);
	
	$operator = intval($session['OPERATORID']);
	$authentication = $session['AUTHENTICATION'];
	$language = $session['LANGUAGE'];
	
	if (!empty($operator) && $operator > 0 && !empty($authentication)) {
	
		$query = sprintf("SELECT `username` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = '%d' AND `password` = '%s' LIMIT 1", $SQL->escape($operator), $SQL->escape($authentication));
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$username = $row['username'];
			
			if (!empty($message)) {
				// Send messages from POSTed data
				if ($staff) {
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "administration (`user`, `username`, `datetime`, `message`, `align`, `status`) VALUES('%d', '%s', NOW(), '%s', '1', '1')", $SQL->escape($id), $SQL->escape($username), $SQL->escape($message));
					$SQL->insertquery($query);
				}
				else {
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`, `status`) VALUES('%d', '%s', NOW(), '%s', '1', '1')", $SQL->escape($id), $SQL->escape($username), $SQL->escape($message));
					$SQL->insertquery($query);
				}
			}
		
			// Format the message string
			$response = trim($response);
		
			if (!empty($response) && $response > 0) {
				// Send messages from POSTed response data
				$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`, `status`) VALUES ('%d', '%s', NOW(), '%d', '1', '1')", $SQL->escape($id), $SQL->escape($username), $response);
				$SQL->insertquery($query);
			}
			if (!empty($command) && $command > 0) {
				$query = sprintf("SELECT `type`, `name`, `content` FROM " . $_SETTINGS['TABLEPREFIX'] . "responses WHERE `id` = '%d' AND `type` > 1 LIMIT 1", $SQL->escape($command));
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					$type = $row['type'];
					$name = $row['name'];
					$content = addslashes($row['content']);
								
					switch ($type) {
						case '2':
							$status = 2;
							$command = addslashes($name . " \r\n " . $content); 
							$alert = '';
							break;
						case '3':
							$status = 3;
							$command = addslashes($name . " \r\n " . $content);
							$alert = '';
							break;
						case '4':
							$status = 4;
							$command = addslashes($content);
							$alert = addslashes('The ' . $name . ' has been PUSHed to the visitor.');
							break;
						case '5':
							$status = 5;
							$command = addslashes($content);
							$alert = addslashes('The ' . $name . ' has been sent to the visitor.');
							break;
					}
					
					if (!empty($command)) {
						$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`, `status`) VALUES ('%d', '', NOW(), '%s', '2', '%s')", $SQL->escape($id), $SQL->escape($command), $SQL->escape($status));
						if (!empty($alert)) {
							$query .= sprintf(", ('%d', '', NOW(), '%s', '2', '-1')", $SQL->escape($id), $SQL->escape($alert));
						}
						$id = $SQL->insertquery($query);
					}
					
				}
			}
		}
	}

} else {
	
	$message = trim($_REQUEST['MESSAGE']);
	$message = str_replace('<', '&lt;', $message);
	$message = str_replace('>', '&gt;', $message);
	$message = trim($message);
	
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
	
	// Guest Chat Session
	if ($chat > 0) {
		$query = sprintf("SELECT `username`, `active` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` WHERE `id` = '%d' LIMIT 1", $chat);
		$row = $SQL->selectquery($query);

		// Blocked Chat
		if ($row['active'] == -3) {
			header('HTTP/1.1 403 Access Forbidden');
			header('Content-Type: text/plain');  
			exit();
		}

	} else {
		header('HTTP/1.1 403 Access Forbidden');
		header('Content-Type: text/plain');  
		exit();
	}
	
	if (!empty($message) && is_array($row)) {

		// Device ID
		$total = 0;
		$username = $row['username'];
		$active = intval($row['active']);

		// Send Guest Message
		$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`) VALUES ('%d', '%s', NOW(), '%s', '1')", $chat, $SQL->escape($username), $SQL->escape($message));
		$id = $SQL->insertquery($query);

		// iPhone / Android PUSH Alerts
		$query = sprintf("SELECT COUNT(`id`) AS total FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = '%d' AND `status` = '7' LIMIT 1", $chat);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$total = $row['total'];
		}
		
		if ($active > 0) {

			// Send Message Device Notification
			$hooks->run('SendMessage', array('chat' => $chat, 'username' => $username, 'message' => $message, 'active' => $active));

		}
	}

	$json = (isset($_REQUEST['JSON'])) ? true : false;

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

		$json = array();
		$json['id'] = $id;
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
	}
}
?>