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
include('../include/phpmailer/class.phpmailer.php');
include('../include/config.php');
include('../include/version.php');
include('../include/functions.php');
include('../include/class.passwordhash.php');
include('../include/class.aes.php');
include('../include/class.push.php');
include('../include/class.upgrade.php');

if (!ini_get('safe_mode')) { 
	set_time_limit(0);
}
ignore_user_abort(true);

// Database Connection
if (DB_HOST == '' || DB_NAME == '' || DB_USER == '' || DB_PASS == '') {
	// HTTP Service Unavailable
	if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 503 Service Unavailable'); } else { header('Status: 503 Service Unavailable'); }
	exit();
}



/* Cross-Origin Resource Sharing
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
}
*/

if (!isset($_REQUEST['Username'])){ $_REQUEST['Username'] = ''; }
if (!isset($_REQUEST['Password'])){ $_REQUEST['Password'] = ''; }
$_OPERATOR = array();

if (IsAuthorized() == true) {

	switch ($_SERVER['QUERY_STRING']) {
		case 'Login':
			Login();
			break;
		case 'Users':
			Users();
			break;
		case 'Visitors':
			Visitors();
			break;
		case 'Visitor':
			Visitor();
			break;
		case 'Version':
			Version();
			break;
		case 'Settings':
			Settings();
			break;
		case 'InitaliseChat':
			InitaliseChat();
			break;
		case 'Chat':
			Chat();
			break;
		case 'Chats':
			Chats();
			break;
		case 'Operators':
			Operators();
			break;
		case 'Statistics':
			Statistics();
			break;
		case 'History':
			History();
			break;
		case 'Send':
			Send();
			break;
		case 'EmailChat':
			EmailChat();
			break;
		case 'Calls':
			Calls();
			break;
		case 'Responses':
			Responses();
			break;
		case 'Activity':
			Activity();
			break;
		default:
			if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 403 Forbidden'); } else { header('Status: 403 Forbidden'); }
			break;
	}
	
} else {

	switch ($_SERVER['QUERY_STRING']) {
		case 'Version':
			Version();
			break;
		case 'ResetPassword':
			ResetPassword();
			break;
		default:
			if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 403 Forbidden'); } else { header('Status: 403 Forbidden'); }
			break;
	}
	
}

exit();


function IsAuthorized() {

	global $_OPERATOR;
	global $_PLUGINS;
	global $_SETTINGS;
	global $SQL;
	global $hooks;

	// Encrypted Operator Session
	if (isset($_REQUEST['Session'])) {
		$cookie = base64_decode($_REQUEST['Session']);
		$aes = new AES256($_SETTINGS['AUTHKEY']); // TODO Setup Seperate Operator Key

		$size = strlen($aes->iv);
		$iv = substr($cookie, 0, $size);
		$verify = substr($cookie, $size, 40);
		$ciphertext = substr($cookie, 40 + $size);

		$decrypted = $aes->decrypt($ciphertext, $iv);
		
		if (sha1($decrypted) == $verify) {
			$cookie = json_decode($decrypted, true);
			
			$id = (int)$cookie['id'];
			$_REQUEST['Username'] = $cookie['username'];
			$_REQUEST['Password'] = $cookie['password'];
		}

	}

	$username = $_REQUEST['Username'];
	$password = $_REQUEST['Password'];

	if (isset($_REQUEST['Version']) && $_REQUEST['Version'] > 3.9) { 
		$query = sprintf("SELECT `id`, `username`, `password`, `firstname`, `lastname`, `email`, `department`, `datetime`, `disabled`, `privilege`, `status` FROM `" . $_SETTINGS['TABLEPREFIX'] . "users` WHERE `username` LIKE BINARY '%s' LIMIT 1", $SQL->escape($username));
	} else {
		$query = sprintf("SELECT `id`, `username`, `password`, `firstname`, `lastname`, `email`, `department`, `datetime`, `disabled`, `privilege`, `status` FROM `" . $_SETTINGS['TABLEPREFIX'] . "users` WHERE `username` LIKE BINARY '%s' AND `disabled` = 0 LIMIT 1", $SQL->escape($username));
	}
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$id = $row['id'];
		$username = $row['username'];
		$hash = $row['password'];
		$firstname = $row['firstname'];
		$lastname = $row['lastname'];
		$email = $row['email'];
		$department = $row['department'];
		$length = strlen($row['password']);

		// v4.0 Password
		$hasher = new PasswordHash(8, true);
		$check = $hasher->CheckPassword($password, $hash);

		// Legacy Hashes
		$legacy = '';
		if (substr($hash, 0, 3) != '$P$') {
			switch ($length) {
				case 40: // SHA1
					$legacy = sha1($password);
					break;
				case 128: // SHA512
					if (function_exists('hash')) {
						if (in_array('sha512', hash_algos())) {
							$legacy = hash('sha512', $password);
						} else if (in_array('sha1', hash_algos())) {
							$legacy = hash('sha1', $password);
						}
					} else if (function_exists('mhash') && mhash_get_hash_name(MHASH_SHA512) != false) {
						$legacy = bin2hex(mhash(MHASH_SHA512, $password));
					}
					break;
				default: // MD5
					$legacy = md5($password);
					break;
			}
		}

		// Process Legacy Password
		$password = $hooks->run('LoginCustomHash', $password);

		if ((isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 4.0 && ($check || $hash == $legacy)) || $hash == $password) {

			// Upgrade Password Authentication
			if (isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 4.0) {
				if (substr($hash, 0, 3) != '$P$') {
					$hash = $hasher->HashPassword($_REQUEST['Password']);
					if (strlen($hash) >= 20) {
						// Update Password Hash
						$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `password` = '%s' WHERE `id` = %d LIMIT 1", $SQL->escape($hash), $id);
						$SQL->updatequery($query);
					}
				}
			}
		
			$_OPERATOR['DISABLED'] = $row['disabled'];
			if ($_OPERATOR['DISABLED']) {
				header('X-Disabled: *');
				return false;
			} else {
		
				$_OPERATOR['ID'] = $id;
				$_OPERATOR['DATETIME'] = $row['datetime'];
				$_OPERATOR['PRIVILEGE'] = $row['privilege'];
				$_OPERATOR['STATUS'] = $row['status'];
				$_OPERATOR['USERNAME'] = $username;
				$_OPERATOR['PASSWORD'] = $hash;
				$_OPERATOR['NAME'] = (!empty($lastname)) ? $firstname . ' ' . $lastname : $firstname;
				$_OPERATOR['DEPARMENT'] = $department;

				$_OPERATOR = $hooks->run('LoginCompleted', $_OPERATOR);
				return true;

			}
			
		} else {

			$_OPERATOR['ID'] = $id;
			$_OPERATOR['USERNAME'] = $username;
			$_OPERATOR['DATETIME'] = $row['datetime'];
			$_OPERATOR['PRIVILEGE'] = $row['privilege'];
			$_OPERATOR['STATUS'] = $row['status'];
	
			$_OPERATOR = $hooks->run('LoginFailed', array('Operator' => $_OPERATOR, 'Password' => $password));

			if (isset($_OPERATOR['PASSWORD'])) {
				return true;
			}
		
			$_OPERATOR['DISABLED'] = $row['disabled'];
			if ($_OPERATOR['DISABLED']) {
				header('X-Disabled: *');
				return false;
			}
		}
	} else {

		// Account Missing
		$_OPERATOR = $hooks->run('LoginAccountMissing', array('Username' => $username, 'Password' => $password));
		if ($_OPERATOR != false && count($_OPERATOR) > 2) {
			return true;
		} else {
			return false;
		}
	
	}

	//  Supports v4.0 Authentication
	$version = '4.0';
	header('X-Authentication: ' . $version);

	return false;
}

function Login() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;
	
	// Automatic Database Upgrade
	$upgrade = new DatabaseUpgrade();
	$version = $upgrade->upgrade();

	if (!isset($_SETTINGS['OPERATORVERSION'])){ $_SETTINGS['OPERATORVERSION'] = '3.28'; }
	if (!isset($_REQUEST['Action'])){ $_REQUEST['Action'] = ''; }
	if (!isset($_REQUEST['Device'])){ $_REQUEST['Device'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }

	// Encrypted Operator Session
	if (isset($_REQUEST['Session'])) {
		$cookie = base64_decode($_REQUEST['Session']);
		$aes = new AES256($_SETTINGS['AUTHKEY']); // TODO Setup Seperate Operator Key

		$size = strlen($aes->iv);
		$iv = substr($cookie, 0, $size);
		$verify = substr($cookie, $size, 40);
		$ciphertext = substr($cookie, 40 + $size);

		$decrypted = $aes->decrypt($ciphertext, $iv);
		
		if (sha1($decrypted) == $verify) {
			$cookie = json_decode($decrypted, true);
			
			$id = (int)$cookie['id'];
			$username = $cookie['username'];
			$password = $cookie['password'];
		}

	} else {
		$username = $_REQUEST['Username'];
		$password = $_REQUEST['Password'];
	}

	switch ($_REQUEST['Action']) {
		case 'Offline':
			$status = 0;
			break;
		case 'Hidden':
			$status = 0;
			break;
		case 'Online':
			$status = 1;
			break;
		case 'BRB':
			$status = 2;
			break;
		case 'Away':
			$status = 3;
			break;
		default:
			$status = -1;
			break;
	}
	
	if ($status != -1) {
		// Update Operator Session
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `datetime` = NOW(), `refresh` = NOW(), `status` = '%d'", $status, $SQL->escape($_OPERATOR['ID']));
		
		// iPhone APNS (PUSH Notifications)
		if ((float)$_SETTINGS['SERVERVERSION'] >= 4.10 && isset($_REQUEST['Unique']) && isset($_REQUEST['Model']) && isset($_REQUEST['OS'])) {

			$query .= " WHERE `id` = '" . $_OPERATOR['ID'] . "' LIMIT 1";
			$SQL->updatequery($query);

			$unique = sha1($_REQUEST['Unique']);

			$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "devices WHERE `user` = '%d' AND `unique` = '%s' LIMIT 1", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique));
			$row = $SQL->selectquery($query);
			if (is_array($row) && count($row) > 0) {
				$id = $row['id'];

				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "devices SET `token` = '%s', `device` = '%s' WHERE `id` = '%d'", $SQL->escape($_REQUEST['Device']), $SQL->escape($_REQUEST['Model']), $id);
				$SQL->updatequery($query);

			} else {
				$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "devices(`id`, `user`, `datetime`, `unique`, `device`, `os`, `token`) VALUES ('', '%d', NOW(), '%s', '%s', '%s', '%s')", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique), $SQL->escape($_REQUEST['Model']), $SQL->escape($_REQUEST['OS']), $SQL->escape($_REQUEST['Device']));
				$SQL->insertquery($query);
			}

		} else {
			if (!empty($_REQUEST['Device'])) {
				$query .= ", `device` = '" . $_REQUEST['Device'] . "'";
			}
			$query .= " WHERE `id` = '" . $_OPERATOR['ID'] . "' LIMIT 1";
			$SQL->updatequery($query);
		}
		
		// Update Operator Status
		$_OPERATOR['STATUS'] = $status;
		
	} else {
		// iPhone APNS (PUSH Notifications)
		if ((float)$_SETTINGS['SERVERVERSION'] >= 4.10 && isset($_REQUEST['Unique']) && isset($_REQUEST['Model']) && isset($_REQUEST['OS'])) {
			
			$unique = sha1($_REQUEST['Unique']);

			$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "devices WHERE `user` = '%d' AND `unique` = '%s' LIMIT 1", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique));
			$row = $SQL->selectquery($query);
			if (is_array($row) && count($row) > 0) {
				$id = $row['id'];

				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "devices SET `token` = '%s', `device` = '%s' WHERE `id` = '%d'", $SQL->escape($_REQUEST['Device']), $SQL->escape($_REQUEST['Model']), $id);
				$SQL->updatequery($query);

			} else {
				$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "devices(`id`, `user`, `datetime`, `unique`, `device`, `os`, `token`) VALUES ('', '%d', NOW(), '%s', '%s', '%s', '%s')", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique), $SQL->escape($_REQUEST['Model']), $SQL->escape($_REQUEST['OS']), $SQL->escape($_REQUEST['Device']));
				$SQL->insertquery($query);
			}

		} else {
			if (!empty($_REQUEST['Device'])) {
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `device` = '%s' WHERE `id` = '%d'", $SQL->escape($_REQUEST['Device']), $SQL->escape($_OPERATOR['ID']));
				$SQL->updatequery($query);
			}
		}
	}
	
	// Authentication
	$authentication = '4.0';

	// Generate Session
	$salt = '';
	$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz`~!@#$%^&*()-_=+[{]}\|;:\'",.>/?';
	for ($index = 1; $index <= 64; $index++) {
		$number = rand(1, strlen($chars));
		$salt .= substr($chars, $number - 1, 1);
	}
	$unique = uniqid($salt, true);
	$hash = sha1($unique);
	$ipaddress = ip_address();

	// Encrypt Session
	$cookie = array('id' => (int)$_OPERATOR['ID'], 'username' => $username, 'password' => $password);
	$cookie = json_encode($cookie);
	$verify = sha1($cookie);
	$aes = new AES256($_SETTINGS['AUTHKEY']);  // TODO Setup Seperate Operator Key
	$session = $aes->iv . $verify . $aes->encrypt($cookie);
	$session = base64_encode($session);
	
	if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
		// Insert Login Activity
		$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity(`user`, `username`, `datetime`, `activity`, `type`, `status`) VALUES ('%d', '%s', NOW(), 'signed into Live Help', 1, 1)", $SQL->escape($_OPERATOR['ID']), $SQL->escape($_OPERATOR['NAME']));
		$SQL->insertquery($query);
	}

	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Login xmlns="urn:LiveHelp" ID="<?php echo($_OPERATOR['ID']); ?>" Session="<?php echo($session); ?>" Version="<?php echo($_SETTINGS['OPERATORVERSION']); ?>" Database="<?php echo($version); ?>" Authentication="<?php echo($authentication) ?>" Name="<?php echo(xmlattribinvalidchars($_OPERATOR['NAME'])); ?>" Access="<?php echo($_OPERATOR['PRIVILEGE']); ?>"/>
<?php
	} else {
		header('Content-type: application/json; charset=utf-8');
		$login = array('ID' => (int)$_OPERATOR['ID'], 'Session' => $session, 'Version' => $_SETTINGS['OPERATORVERSION'], 'Database' => $version, 'Authentication' => $authentication, 'Name' => $_OPERATOR['NAME'], 'Access' => $_OPERATOR['PRIVILEGE'], 'Status' => (int)$_OPERATOR['STATUS']);
		$json = array('Login' => $login);
		$json = json_encode($json);
		echo($json);
		exit();
	}

}

function Users() {
	
	global $_OPERATOR;
	global $_SETTINGS;
	global $_PLUGINS;
	global $_LOCALE;
	global $SQL;
	global $hooks;
	
	if (!isset($_REQUEST['Action'])){ $_REQUEST['Action'] = ''; }
	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Transfer'])){ $_REQUEST['Transfer'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	
	// iPhone APNS (PUSH Notifications)
	if ((float)$_SETTINGS['SERVERVERSION'] >= 4.10 && isset($_REQUEST['Device']) && isset($_REQUEST['Unique']) && isset($_REQUEST['Model']) && isset($_REQUEST['OS'])) {
		
		$unique = sha1($_REQUEST['Unique']);

		$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "devices WHERE `user` = '%d' AND `unique` = '%s' LIMIT 1", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique));
		$row = $SQL->selectquery($query);
		if (is_array($row) && count($row) > 0) {
			$id = $row['id'];

			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "devices SET `token` = '%s', `device` = '%s' WHERE `id` = '%d'", $SQL->escape($_REQUEST['Device']), $SQL->escape($_REQUEST['Model']), $id);
			$SQL->updatequery($query);

		} else {
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "devices(`id`, `user`, `datetime`, `unique`, `device`, `os`, `token`) VALUES ('', '%d', NOW(), '%s', '%s', '%s', '%s')", $SQL->escape($_OPERATOR['ID']), $SQL->escape($unique), $SQL->escape($_REQUEST['Model']), $SQL->escape($_REQUEST['OS']), $SQL->escape($_REQUEST['Device']));
			$SQL->insertquery($query);
		}
	} else {
		if (isset($_REQUEST['Device'])) {
			// Update iPhone APNS
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `device` = '%s' WHERE `id` = %d", $SQL->escape($_REQUEST['Device']), $_OPERATOR['ID']);
			$SQL->updatequery($query);
		} else {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW() WHERE `id` = %d", $_OPERATOR['ID']);
			$SQL->updatequery($query);
		}
	}
	
	// Check for actions and process
	if ($_REQUEST['Action'] == 'Accept' && $_REQUEST['ID'] != '0') {
	
		// Check if already assigned to an operator
		$query = sprintf("SELECT `username`, UNIX_TIMESTAMP(`datetime`) AS `datetime`, `active` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d", $_REQUEST['ID']);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			if ($row['active'] == '0' || $row['active'] == '-2') {
	
				$name = ucwords(strtolower($row['username']));
				$datetime = $row['datetime'];
	
				// Update the active flag of the guest user to the ID of the operator
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `active` = %d WHERE `id` = %d", $_OPERATOR['ID'], $_REQUEST['ID']);
				$SQL->updatequery($query);
				
				if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
					// Insert Accepted Chat Activity
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity(`user`, `chat`, `username`, `datetime`, `activity`, `duration`, `type`, `status`) VALUES ('%d', '%d', '%s', NOW(), 'accepted chat with %s', UNIX_TIMESTAMP(NOW()) - %s, 7, 1)", $_OPERATOR['ID'], $_REQUEST['ID'], $SQL->escape($name), $SQL->escape($name), $SQL->escape($datetime));
					$SQL->insertquery($query);
				}

				// Accept Chat Device Notification
				$hooks->run('AcceptChat', array($name));

			}
		}
	}
	elseif ($_REQUEST['Action'] == 'Close' && $_REQUEST['ID'] != '0') {
	
		// Verify Closed Chat
		$query = sprintf("SELECT `username`, `active` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d", $_REQUEST['ID']);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$active = $row['active'];
			$username = $row['username'];

			// Determine EOL
			$server = strtoupper(substr(PHP_OS, 0, 3));
			if ($server == 'WIN') { 
				$eol = "\r\n"; 
			} elseif ($server == 'MAC') { 
				$eol = "\r"; 
			} else { 
				$eol = "\n"; 
			}
			
			if ($active > 0) {
	
				// Close Chat
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `active` = '-1' WHERE `id` = %d", $_REQUEST['ID']);
				$SQL->updatequery($query);
				
				$chat = (int)$_REQUEST['ID'];
				$hooks->run('CloseChat', array($chat, $username));

			}
		
			// Send Chat Transcript
			if (isset($_SETTINGS['AUTOEMAILTRANSCRIPT']) && $_SETTINGS['AUTOEMAILTRANSCRIPT'] != '') {

				$query = sprintf("SELECT `username`, `message`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND `status` <= '3' ORDER BY `datetime`", $_REQUEST['ID']);
				$row = $SQL->selectquery($query);
				$htmlmessages = ''; $textmessages = '';
				while ($row) {
					if (is_array($row)) {
						$username = $row['username'];
						$message = $row['message'];
						$status = $row['status'];
						
						// Operator
						if ($status) {
							$htmlmessages .= '<div style="color:#666666">' . $username . ' says:</div><div style="margin-left:15px; color:#666666;">' . $message . '</div>'; 
							$textmessages .= $username . ' says:' . $eol . '	' . $message . $eol; 
						}
						// Guest
						if (!$status) {
							$htmlmessages .= '<div>' . $username . ' says:</div><div style="margin-left: 15px;">' . $message . '</div>'; 
							$textmessages .= $username . ' says:' . $eol . '	' . $message . $eol; 
						}
				
						$row = $SQL->selectnext();
					}
				}

				$htmlmessages = preg_replace("/(\r\n|\r|\n)/", '<br/>', $htmlmessages);	
				
				$html = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<!--

div, p {
	font-family: Calibri, Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #000000;
}

//-->
</style>
</head>

<body>
<p><img src="{$_SETTINGS['CHATTRANSCRIPTHEADERIMAGE']}" alt="Chat Transcript" /></p>
<p><strong>Chat Transcript:</strong></p>
<p>$htmlmessages</p>
<p><img src="{$_SETTINGS['CHATTRANSCRIPTFOOTERIMAGE']}" alt="{$_SETTINGS['NAME']}" /></p>
</body>
</html>
END;
				if ($_SETTINGS['AUTOEMAILTRANSCRIPT'] != '') {
					$mail = new PHPMailer(true);
					try {
						$mail->CharSet = 'UTF-8';
						$mail->AddReplyTo($_SETTINGS['EMAIL'], $_SETTINGS['NAME']);
						$mail->AddAddress($_SETTINGS['AUTOEMAILTRANSCRIPT']);
						$mail->SetFrom($_SETTINGS['EMAIL']);
						$mail->Subject = $_SETTINGS['NAME'] . ' ' . $_LOCALE['chattranscript'] . ' (' . $_LOCALE['autogenerated'] . ')';
						$mail->MsgHTML($html);
						$mail->Send();
						$result = true;
					} catch (phpmailerException $e) {
						trigger_error('Email Error: ' . $e->errorMessage(), E_USER_ERROR); 
						$result = false;
					} catch (Exception $e) {
						trigger_error('Email Error: ' . $e->getMessage(), E_USER_ERROR); 
						$result = false;
					}
				}
			}
		}
		
	}
	elseif ($_REQUEST['Action'] == 'Transfer' && $_REQUEST['ID'] != '0' && $_REQUEST['Transfer'] != '0') {
	
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `datetime` = NOW(), `active`= '-2', `transfer` = %d WHERE `id` = %d", $_REQUEST['Transfer'], $_REQUEST['ID']);
		$SQL->updatequery($query);
		
	}
	elseif ($_REQUEST['Action'] == 'Block' && $_REQUEST['ID'] != '0') {
	
		// Block Chat
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `active` = '-3' WHERE `id` = %d", $_REQUEST['ID']);
		$SQL->updatequery($query);
		
	}
	elseif ($_REQUEST['Action'] == 'Unblock' && $_REQUEST['ID'] != '0') {
	
		// Unblock Chat
		$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `active` = '-1' WHERE `id` = %d", $_REQUEST['ID']);
		$SQL->updatequery($query);
		
	}
	elseif ($_REQUEST['Action'] == 'Hidden' || $_REQUEST['Action'] == 'Offline') {
	
		if ($_REQUEST['ID'] != '') {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '0' WHERE `id` = %d", $_REQUEST['ID']);
		} else {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '0' WHERE `id` = %d", $_OPERATOR['ID']);
		}
		$SQL->updatequery($query);

		$hooks->run('OperatorUpdatedStatusMode', array('status' => $_REQUEST['Action']));
		
	}
	elseif ($_REQUEST['Action'] == 'Online') {
	
		if ($_REQUEST['ID'] != '') {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '1' WHERE `id` = %d", $_REQUEST['ID']);
		} else {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '1' WHERE `id` = %d", $_OPERATOR['ID']);
		}
		$SQL->updatequery($query);

		$hooks->run('OperatorUpdatedStatusMode', array('status' => $_REQUEST['Action']));
		
	}
	elseif ($_REQUEST['Action'] == 'BRB') {
	
		if ($_REQUEST['ID'] != '') {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '2' WHERE `id` = %d", $_REQUEST['ID']);
		} else {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '2' WHERE `id` = %d", $_OPERATOR['ID']);
		}
		$SQL->updatequery($query);

		$hooks->run('OperatorUpdatedStatusMode', array('status' => $_REQUEST['Action']));

	}
	elseif ($_REQUEST['Action'] == 'Away') {
	
		if ($_REQUEST['ID'] != '') {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '3' WHERE `id` = %d", $_REQUEST['ID']);
		} else {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `refresh` = NOW(), `status` = '3' WHERE `id` = %d", $_OPERATOR['ID']);
		}
		$SQL->updatequery($query);

		$hooks->run('OperatorUpdatedStatusMode', array('status' => $_REQUEST['Action']));

	}
	
	// Update Activity
	if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
	
		if ($_REQUEST['Action'] == 'Offline') {

			// Insert Sign Out Activity
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity(`user`, `username`, `datetime`, `activity`, `type`, `status`) VALUES ('%d', '%s', NOW(), 'signed out of Live Help', 2, 1)", $_OPERATOR['ID'], $SQL->escape($_OPERATOR['NAME']));
			$SQL->insertquery($query);

		} elseif ($_REQUEST['Action'] == 'Online' || $_REQUEST['Action'] == 'BRB' || $_REQUEST['Action'] == 'Away' || $_REQUEST['Action'] == 'Hidden') {
			
			switch ($_REQUEST['Action']) {
				case 'Hidden':
					$status = 'Hidden';
					$flag = 3;
					break;
				case 'BRB':
					$status = 'Be Right Back';
					$flag = 5;
					break;
				case 'Away':
					$status = 'Away';
					$flag = 6;
					break;
				default:
					$status = 'Online';
					$flag = 4;
					break;
			}
			
			if ($_REQUEST['ID'] != '') {
				// Select Operator Name
				$query_name = "SELECT `firstname`, `lastname` FROM  " . $_SETTINGS['TABLEPREFIX'] . "users " . $_SETTINGS['TABLEPREFIX'] . "users";
				$row = $SQL->selectquery($query_name);
				if (is_array($row)) {
					$name = $row['firstname'] . ' ' . $row['lastname'];
					
					// Insert Away Status Activity
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity(`user`, `username`, `datetime`, `activity`, `type`, `status`) VALUES ('%d', '%s', NOW(), 'changed the status of %s to $s', %d, 1)", $_OPERATOR['ID'], $SQL->escape($_OPERATOR['NAME']), $SQL->escape($name), $SQL->escape($status), $flag);
					$SQL->insertquery($query);
				}
			} else {
			
				// Insert Status Activity
				$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "activity(`user`, `username`, `datetime`, `activity`, `type`, `status`) VALUES ('%d', '%s', NOW(), 'changed status to %s', %d, 1)", $_OPERATOR['ID'], $SQL->escape($_OPERATOR['NAME']), $SQL->escape($status), $flag);
				$SQL->insertquery($query);
			}
		}
	}
	
	$lastcall = '0';
	$query = "SELECT MAX(`id`) AS `max` FROM " . $_SETTINGS['TABLEPREFIX'] . "callback LIMIT 1";
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		if ($row['max'] != '') {
			$lastcall = $row['max'];
		}
	}
	
	$lastactivity = '0';
	if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
		$query = sprintf("SELECT MAX(`id`) AS `max` FROM " . $_SETTINGS['TABLEPREFIX'] . "activity WHERE `user` <> %d OR `status` = 0 LIMIT 1", $_OPERATOR['ID']);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			if ($row['max'] != '') {
				$lastactivity = $row['max'];
			}
		}
	}
	
	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
		
		if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
?>
<Users xmlns="urn:LiveHelp" LastCall="<?php echo($lastcall); ?>" LastActivity="<?php echo($lastactivity); ?>">
<?php
		} else {
?>
<Users xmlns="urn:LiveHelp" LastCall="<?php echo($lastcall); ?>">
<?php
		}
	} else {
		header('Content-type: application/json; charset=utf-8');

		$staff = array();
		$online = array();
		$pending = array();
		$transferred = array();

	}
	
	// Online Operators
	$query = sprintf("SELECT `id`, `username`, `firstname`, `department`, `privilege`, `status`, `device` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE (`refresh` > DATE_SUB(NOW(), INTERVAL %s SECOND) AND (`status` = '1' OR `status` = '2' OR `status` = '3')) OR (`device` <> '' AND `status` = '1') ORDER BY `username`", $SQL->escape($_SETTINGS['CONNECTIONTIMEOUT']));
	$rows = $SQL->selectall($query);
	
	$total_users = count($rows);
	
	if (is_array($rows)) {
		if ($_REQUEST['Format'] == 'xml') {
?>
<Staff>
<?php
		}
	
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$id = $row['id'];
				$status = $row['status'];
				$username = $row['username'];
				$firstname = $row['firstname'];
				$department = $row['department'];
				$access = $row['privilege'];
				$device = $row['device'];
				
				// Count the total NEW messages that have been sent to the current login
				$query = sprintf("SELECT max(`id`) FROM " . $_SETTINGS['TABLEPREFIX'] . "administration WHERE `username` = '$username' AND `user` = %d AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('%s')) > '0'", $_OPERATOR['ID'], $SQL->escape($_OPERATOR['DATETIME']));
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					$messages = $row['max(`id`)'];
				}
				
				if ($_REQUEST['Format'] == 'xml') {
?>
<User ID="<?php echo($id); ?>" <?php if ($messages != '') { ?>Messages="<?php echo($messages); ?>" <?php } ?>Status="<?php echo($status); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
				} else {

					// Staff User JSON
					if ($messages != '') {
						$user = array('ID' => $id, 'Name' => $username, 'Firstname' => $firstname, 'Department' => $department, 'Access' => $access, 'Messages' => $messages, 'Status' => $status);
					} else {
						$user = array('ID' => $id, 'Name' => $username, 'Firstname' => $firstname, 'Department' => $department, 'Access' => $access, 'Status' => $status);
					}
					$staff[] = $user;
				}

			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
?>
</Staff>
<?php
		}

	}
	
	// Chatting Visitors
	$query = sprintf("SELECT chats.id, chats.request, chats.active, chats.username, chats.department, chats.server, chats.email, users.firstname, users.lastname FROM " . $_SETTINGS['TABLEPREFIX'] . "chats AS chats, " . $_SETTINGS['TABLEPREFIX'] . "users AS users WHERE chats.active = users.id AND chats.refresh > DATE_SUB(NOW(), INTERVAL %s SECOND) AND chats.active > '0' ORDER BY chats.username", $SQL->escape($_SETTINGS['CONNECTIONTIMEOUT']));
	$rows = $SQL->selectall($query);
	
	$total_users = count($rows);
	
	if (is_array($rows)) {
		if ($_REQUEST['Format'] == 'xml') {
?>
<Online>
<?php
		}
		
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$id = $row['id'];
				$username = $row['username'];
				$request = $row['request'];
				$active = $row['active'];
				
				$department = '';
				if (isset($row['department'])) {
					$department = $row['department'];
				}
				
				$server = '';
				if (isset($row['server'])) {
					$server = $row['server'];
				}
				
				$email = '';
				if (isset($row['email'])) {
					$email = $row['email'];
				}
				
				$question = '';
				if (isset($row['question'])) {
					$question = $row['question'];
				}
				
				$custom = '';
				$reference = '';
				
				// Integration
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = '%d'", $request);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
				
				$operator = '';
				if (isset($row['firstname']) && isset($row['lastname'])) {
					$operator = $row['firstname'] . ' ' . $row['lastname'];
				}
				
				if ($_OPERATOR['PRIVILEGE'] <= 1 && $_OPERATOR['ID'] != $active) {
					
					if (isset($_REQUEST['Version'])) {
						if ($_REQUEST['Format'] == 'xml') {
?> 
<User ID="<?php echo($id); ?>" Active="<?php echo($active); ?>" Operator="<?php echo($operator); ?>" Visitor="<?php echo($request); ?>" Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
						} else {

							// Online User JSON
							$user = array('ID' => $id, 'Name' => $username, 'Active' => $active, 'Operator' => $operator, 'Visitor' => $request, 'Department' => $department, 'Server' => $server, 'Email' => $email, 'Question' => $question);
							$online[] = $user;
						}
					}
				}
				else if ($_OPERATOR['ID'] == $active) {
				
					// Count the Total Messages
					$query = sprintf("SELECT max(`id`) FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = '$id' AND `status` <= '3' AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('%s')) > '0'", $SQL->escape($_OPERATOR['DATETIME']));
					$row = $SQL->selectquery($query);
					if (is_array($row)) {
						$messages = $row['max(`id`)'];
					}

					if ($_REQUEST['Format'] == 'xml') {
						if (empty($request)) {
?> 
<User ID="<?php echo($id); ?>" Active="<?php echo($active); ?>" <?php if ($messages != '') { ?> Messages="<?php echo($messages); ?>"<?php } ?> Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
						} else {
?> 
<User ID="<?php echo($id); ?>" Active="<?php echo($active); ?>" Visitor="<?php echo($request); ?>"<?php if ($messages != '') { ?> Messages="<?php echo($messages); ?>"<?php } ?> Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
						}
					} else {

						// Online User JSON
						$user = array('ID' => $id, 'Name' => $username, 'Active' => $active, 'Operator' => $operator, 'Visitor' => $request, 'Messages' => $messages, 'Department' => $department, 'Server' => $server, 'Email' => $email, 'Question' => $question);
						$online[] = $user;

					}
				
				}
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
?>
</Online>
<?php
		}
	}
		
	// Pending Visitors
	if ($_SETTINGS['DEPARTMENTS'] == true) {
		$sql = departmentsSQL($_OPERATOR['DEPARMENT']);
		$query = sprintf("SELECT DISTINCT `id`, `request`, `username`, `department`, `server`, `email` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %s SECOND) AND `active` = '0' AND $sql ORDER BY `username`", $SQL->escape($_SETTINGS['CONNECTIONTIMEOUT']));
	}
	else {
		$query = sprintf("SELECT DISTINCT `id`, `request`, `username`, `server`, `email` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %s SECOND) AND `active` = '0' ORDER BY `username`", $SQL->escape($_SETTINGS['CONNECTIONTIMEOUT']));
	}
	$rows = $SQL->selectall($query);
	
	$total_users = count($rows);
	
	if (is_array($rows)) {
		if ($_REQUEST['Format'] == 'xml') {
?>
<Pending>
<?php
		}
		
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$id = $row['id'];
				$username = $row['username'];
				$request = $row['request'];
				
				$department = '';
				if (isset($row['department'])) {
					$department = $row['department'];
				}
				
				$server = '';
				if (isset($row['server'])) {
					$server = $row['server'];
				}
				
				$email = '';
				if (isset($row['email'])) {
					$email = $row['email'];
				}
				
				$question = '';
				if (isset($row['question'])) {
					$question = $row['question'];
				}
				
				$custom = '';
				$reference = '';
				
				// Integration
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = '%d'", $request);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
				
				if ($_REQUEST['Format'] == 'xml') {
					if (empty($request)) {
?>
<User ID="<?php echo($id); ?>" Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
					} else { 
?>
<User ID="<?php echo($id); ?>" Visitor="<?php echo($request); ?>" Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
					}
				} else {

					// Pending User JSON
					$user = array('ID' => $id, 'Name' => $username, 'Visitor' => $request, 'Department' => $department, 'Server' => $server, 'Email' => $email, 'Question' => $question);
					$pending[] = $user;
				}	
			}
		}
		if ($_REQUEST['Format'] == 'xml') {
?>
</Pending>
<?php
		}
	}
	
	// Transferred Visitors
	$query = sprintf("SELECT DISTINCT `id`, `request`, `username`, `department`, `server`, `email` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %s SECOND) AND `active` = '-2' AND `transfer` = %d ORDER BY `username`", $SQL->escape($_SETTINGS['CONNECTIONTIMEOUT']), $_OPERATOR['ID']);
	$rows = $SQL->selectall($query);
	
	$total_users = count($rows);
	
	if (is_array($rows)) {
		if ($_REQUEST['Format'] == 'xml') {
?>
<Transferred>
<?php
		}
		
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$id = $row['id'];
				$request = $row['request'];
				$username = $row['username'];
				
				$department = '';
				if (isset($row['department'])) {
					$department = $row['department'];
				}
				
				$server = '';
				if (isset($row['server'])) {
					$server = $row['server'];
				}
				
				$email = '';
				if (isset($row['email'])) {
					$email = $row['email'];
				}
				
				$question = '';
				if (isset($row['question'])) {
					$question = $row['question'];
				}
				
				$custom = '';
				$reference = '';
				
				// Integration
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = '%d'", $request);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
				
				if ($_REQUEST['Format'] == 'xml') {
?> 
<User ID="<?php echo($id); ?>" Visitor="<?php echo($request); ?>" Department="<?php echo(xmlattribinvalidchars($department)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>"><?php echo(xmlelementinvalidchars($username)); ?></User>
<?php
				} else {

					// Tranferrered User JSON
					$user = array('ID' => $id, 'Name' => $username, 'Visitor' => $request, 'Department' => $department, 'Server' => $server, 'Email' => $email, 'Question' => $question);
					$transferred[] = $user;
				}	
			}
		}

		if ($_REQUEST['Format'] == 'xml') {
?>
</Transferred>
<?php
		}
	}
			
	if ($_REQUEST['Format'] == 'xml') {
?>
</Users>
<?php
	} else {

		// Output JSON
		if ((float)$_SETTINGS['SERVERVERSION'] >= 3.90) {
			$users = array('LastCall' => $lastcall, 'LastActivity' => $lastactivity, 'Staff' => array('User' => $staff), 'Online' => array('User' => $online), 'Pending' => array('User' => $pending), 'Transferred' => array('User' => $transferred));
		} else {
			$users = array('LastCall' => $lastcall, 'Staff' => array('User' => $staff), 'Online' => array('User' => $online), 'Pending' => array('User' => $pending), 'Transferred' => array('User' => $transferred));
		}
		$json = array('Users' => $users);
		
		echo(json_encode($json));

	}
}

function Visitors() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $_PLUGINS;
	global $SQL;
	global $hooks;

	if (!isset($_REQUEST['Action'])){ $_REQUEST['Action'] = ''; }
	if (!isset($_REQUEST['Request'])){ $_REQUEST['Request'] = ''; }
	if (!isset($_REQUEST['Record'])){ $_REQUEST['Record'] = ''; }
	if (!isset($_REQUEST['Total'])){ $_REQUEST['Total'] = '6'; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	
	if ($_REQUEST['Action'] == 'Initiate' && $_OPERATOR['PRIVILEGE'] < 4) {
	
		if ($_REQUEST['Request'] != '') {
			// Update active field of user to the ID of the operator that initiated support
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "requests SET `initiate` = %d WHERE `id` = %d AND `initiate` = 0", $_OPERATOR['ID'], $_REQUEST['Request']);
			$SQL->updatequery($query);
		}
		else {
			// Initiate chat request with all visitors
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "requests SET `initiate` = %d AND `initiate` = 0", $_OPERATOR['ID']);
			$SQL->updatequery($query);
		}
	}
	elseif ($_REQUEST['Action'] == 'Remove' && $_OPERATOR['PRIVILEGE'] < 3) {
	
		if ($_REQUEST['Request'] != '') {
			// Hide Visitor Request
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "requests SET `status` = '1' WHERE `id` = %d", $_REQUEST['Request']);
			$SQL->updatequery($query);
		}
	}

	if ((float)$_SETTINGS['SERVERVERSION'] >= 4.10) {
		if (file_exists('../plugins/cloud/admin.js')) {
			$where = sprintf("((`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0') OR `status` = '2')", $_SETTINGS['VISITORTIMEOUT']);
		} else {
			$where = sprintf("`refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0'", $_SETTINGS['VISITORTIMEOUT']);
		}
		$query = "SELECT `requests`.*, `geo`.`latitude`, `geo`.`longitude`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`))) AS `sitetime`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`requests`.`request`))) AS `pagetime` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests AS `requests` LEFT JOIN " . $_SETTINGS['TABLEPREFIX'] . "geolocation AS `geo` ON `requests`.`id` = `geo`.`request` WHERE $where ORDER BY `requests`.`id` ASC";
	} else {
		$query = sprintf("SELECT *, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`))) AS `sitetime`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`))) AS `pagetime` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `refresh` > DATE_SUB(NOW(), INTERVAL %d SECOND) AND `status` = '0' ORDER BY `id` ASC", $_SETTINGS['VISITORTIMEOUT']);
	}
	$rows = $SQL->selectall($query);

	if (is_array($rows)) {

		// Visitors Query Completed Hook
		if (count($rows) > 0) {
			$rows = $hooks->run('VisitorsQueryCompleted', $rows);
		}

		$last = 0; $total = 0; $pageviews = 0;
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$last = $row['id'];
				$total += 1;
				$pageviews += substr_count($row['path'], '; ') + 1;
			}
		}

		if ($total > 0) {
			while ($total <= $_REQUEST['Record']) {
				$_REQUEST['Record'] = $_REQUEST['Record'] - $_REQUEST['Total'];
			}
		} else {
			$_REQUEST['Record'] = 0;
		}
		
		if ($_REQUEST['Format'] == 'xml') {
			header('Content-type: text/xml; charset=utf-8');
			echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Visitors xmlns="urn:LiveHelp" TotalVisitors="<?php echo($total); ?>" LastVisitor="<?php echo($last); ?>" PageViews="<?php echo($pageviews); ?>">
<?php
		} else {
			header('Content-type: application/json; charset=utf-8');

			if ($_REQUEST['Total'] < 0) {
				$visitors = array();
				foreach ($rows as $key => $row) {
					if (is_array($row)) {
						$visitors[] = (int)$row['id'];
					}
				}
				$data = array('Visitors' => $visitors);
				$json = json_encode($data);
				echo($json);
				exit();
			}

			$visits = array();
			$visitors = array('TotalVisitors' => $total, 'LastVisitor' => $last, 'Pageviews' => $pageviews);
		}
	
		$initiated_default_label = 'Live Help Request has not been Initiated';
		$initiated_sending_label = 'Sending the Initiate Live Help Request...';
		$initiated_waiting_label = 'Waiting on the Initiate Live Help Reply...';
		$initiated_accepted_label = 'Initiate Live Help Request was ACCEPTED';
		$initiated_declined_label = 'Initiate Live Help Request was DECLINED';
		$initiated_chatting_label = 'Currently chatting to Operator';
		$initiated_chatted_label = 'Already chatted to an Operator';
		$initiated_pending_label = 'Currently Pending for Live Help';
		
		$rating_label = 'Rating';			
		$unavailable_label = 'Unavailable';
		
		$count = count($rows);
		$total = $_REQUEST['Record'] + $_REQUEST['Total'];
		if ($count < $total) { $total_visitors = $count; } else { $total_visitors = $total; }
		
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				if ($key >= $_REQUEST['Record'] && $key < $_REQUEST['Record'] + $_REQUEST['Total']) {
					$current_request_id = $row['id'];
					$current_request_ipaddress = $row['ipaddress'];
					$current_request_user_agent = $row['useragent'];
					$current_request_resolution = $row['resolution'];
					$current_request_city = (isset($row['city'])) ? $row['city'] : '';
					$current_request_state = (isset($row['state'])) ? $row['state'] : '';
					$current_request_country = $row['country'];

					$current_request_latitude = '';
					$current_request_longitude = '';
					if (isset($row['latitude']) && isset($row['longitude'])) {
						$current_request_latitude = $row['latitude'];
						$current_request_longitude = $row['longitude'];
					}

					$current_request_current_page = $row['url'];
					$current_request_current_page_title = $row['title'];
					$current_request_referrer = $row['referrer'];
					$current_request_pagetime = $row['pagetime'];
					$current_request_page_path = $row['path'];
					$current_request_sitetime = $row['sitetime'];
					$current_request_initiate = $row['initiate'];

					$paths = explode('; ', $current_request_page_path);
					$total_pages = count($paths);
					
					// Last 20 Page Paths
					$last_paths = array_slice($paths, $total_pages - 20);
					$current_request_page_path = implode('; ', $last_paths);
					
					// Limit Page History
					if (strlen($current_request_page_path) > 500) {
						$current_request_page_path = substr($current_request_page_path, 0, 500);
					}
					
					// Operator Name
					$query = sprintf("SELECT chats.id, chats.username, `firstname`, `lastname`, chats.department, `rating`, `active` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats AS chats, " . $_SETTINGS['TABLEPREFIX'] . "users AS users WHERE `active` = users.id AND `request` = %d LIMIT 1", $current_request_id);
					$row = $SQL->selectquery($query);
					if (is_array($row)) {
					
						$current_session_id = $row['id'];
						$current_session_username = $row['username'];
						$current_session_firstname = $row['firstname'];
						$current_session_lastname = $row['lastname'];
						$current_session_department = $row['department'];
						$current_session_rating = $row['rating'];
						$current_session_active = $row['active'];
						
						if ($current_session_active == '-1' || $current_session_active == '-3') {
						
							// Display the rating of the ended chat request
							if ($current_session_rating > 0) {
								$current_request_initiate_status = $initiated_chatted_label . ' - ' . $rating_label . ' (' . $current_session_rating . '/5)';
							}
							else {
								$current_request_initiate_status = $initiated_chatted_label;
							}
							
							// Initiate Chat Status
							switch ($current_request_initiate) {
								case 0: // Not Initiated
									break;
								case -1: // Waiting
									$current_request_initiate_status = $initiated_waiting_label;
									break;
								case -2: // Accepted
									$current_request_initiate_status = $initiated_accepted_label;
									break;
								case -3: // Declined
									$current_request_initiate_status = $initiated_declined_label;
									break;
								case -4: // Chatting
									break;
								default: // Sending
									$current_request_initiate_status = $initiated_sending_label;
									break;
							}
						
						}
						else {
							if ($current_session_active > 0) {
								if ($current_session_firstname != '') {
									if ($current_session_lastname != '') {
										$current_request_initiate_status = $initiated_chatting_label . ' (' . $current_session_firstname . ' ' . $current_session_lastname . ')';
									} else {
										$current_request_initiate_status = $initiated_chatting_label . ' (' . $current_session_firstname . ')';
									}
								}
								else {
									$current_request_initiate_status = $initiated_chatting_label . ' (' . $unavailable_label . ')';
								}
							}
							else {
								if ($current_session_department != '') {
									$current_request_initiate_status = $initiated_pending_label . ' (' . $current_session_department . ')';
								}
								else {
									$current_request_initiate_status = $initiated_pending_label;
								}
							}
						}
					}
					else {
					
						$query = sprintf("SELECT `id`, `username`, `active` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `active` <> 0 AND `request` = %d LIMIT 1", $current_request_id);
						$row = $SQL->selectquery($query);
						if (is_array($row)) {
							$current_session_id = $row['id'];
							$current_session_username = $row['username'];
							$current_session_active = $row['active'];
						} else {
							$current_session_id = 0;
							$current_session_username = '';
							$current_session_active = '';
						}
						
						// Initiate Chat Status
						switch($current_request_initiate) {
							case 0: // Default Status
								$current_request_initiate_status = $initiated_default_label;
								break;
							case -1: // Waiting
								$current_request_initiate_status = $initiated_waiting_label;
								break;
							case -2: // Accepted
								$current_request_initiate_status = $initiated_accepted_label;
								break;
							case -3: // Declined
								$current_request_initiate_status = $initiated_declined_label;
								break;
							default: // Sending
								$current_request_initiate_status = $initiated_sending_label;
								break;
						}
					}
					
					if ($current_request_current_page == '') {
						$current_request_current_page = $unavailable_label;
					}
					
					// Set the referrer as approriate
					if ($current_request_referrer != '' && $current_request_referrer != 'false') {
						$current_request_referrer_result = urldecode($current_request_referrer);
					}
					elseif ($current_request_referrer == false) {
						$current_request_referrer_result = 'Direct Visit / Bookmark';
					}
					else {
						$current_request_referrer_result = $unavailable_label;
					}
					
					if ($_SETTINGS['LIMITHISTORY'] > 0) {
						$history = explode(';', $current_request_page_path);
						$path = array();
						if (count($history) > $_SETTINGS['LIMITHISTORY']) {
							for($i = 0; $i < $_SETTINGS['LIMITHISTORY']; $i++) {
									array_unshift($path, array_pop($history));
							}
							$current_request_page_path = implode('; ', $path);
						}
					}
					
					if ($_REQUEST['Format'] == 'xml') {
						
						$query = sprintf("SELECT `custom`, `name`, `reference` FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = %d LIMIT 1", $current_request_id);
						$row = $SQL->selectquery($query);
						if (is_array($row)) {
							$current_request_custom = $row['custom'];
							$current_request_username = $row['name'];
							$current_request_reference = $row['reference'];
?>
<Visitor ID="<?php echo($current_request_id); ?>" Session="<?php echo($current_session_id); ?>" Active="<?php echo($current_session_active); ?>" Username="<?php echo(xmlattribinvalidchars($current_request_username)); ?>" Custom="<?php echo(xmlattribinvalidchars($current_request_custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($current_request_reference)); ?>">
<?php
						}
						else {
?>
<Visitor ID="<?php echo($current_request_id); ?>" Session="<?php echo($current_session_id); ?>" Active="<?php echo($current_session_active); ?>" Username="<?php echo(xmlattribinvalidchars($current_session_username)); ?>">
<?php
						}
?>
<Hostname><?php echo(xmlelementinvalidchars($current_request_ipaddress)); ?></Hostname>
<Country City="<?php echo(xmlattribinvalidchars($current_request_city)); ?>" State="<?php echo(xmlattribinvalidchars($current_request_state)); ?>"><?php echo($current_request_country); ?></Country>
<UserAgent><?php echo(xmlelementinvalidchars($current_request_user_agent)); ?></UserAgent>
<Resolution><?php echo(xmlelementinvalidchars($current_request_resolution)); ?></Resolution>
<CurrentPage><?php echo(xmlelementinvalidchars($current_request_current_page)); ?></CurrentPage>
<CurrentPageTitle><?php echo(xmlelementinvalidchars($current_request_current_page_title)); ?></CurrentPageTitle>
<Referrer><?php echo(xmlelementinvalidchars($current_request_referrer_result)); ?></Referrer>
<TimeOnPage><?php echo($current_request_pagetime); ?></TimeOnPage>
<ChatStatus><?php echo(xmlelementinvalidchars($current_request_initiate_status)); ?></ChatStatus>
<PagePath Total="<?php echo($total_pages); ?>"><?php echo(xmlelementinvalidchars($current_request_page_path)); ?></PagePath>
<TimeOnSite><?php echo($current_request_sitetime); ?></TimeOnSite>
</Visitor>
<?php
					} else {
						$visit = array(
							'ID' => $current_request_id,
							'Active' => $current_session_active,
							'Session' => $current_session_id,
							'Username' => $current_session_username,
							'Hostname' => $current_request_ipaddress,
							'City' => $current_request_city,
							'State' => $current_request_state,
							'Country' => $current_request_country,
							'Latitude' => $current_request_latitude,
							'Longitude' => $current_request_longitude,
							'UserAgent' => $current_request_user_agent,
							'Resolution' => $current_request_resolution,
							'CurrentPage' => $current_request_current_page,
							'CurrentPageTitle' => $current_request_current_page_title,
							'Referrer' => $current_request_referrer_result,
							'TimeOnPage' => $current_request_pagetime,
							'ChatStatus' => $current_request_initiate_status,
							'PagePath' => $current_request_page_path,
							'TimeOnSite' => $current_request_sitetime
						);
						$visits[] = $visit;
					}
				}
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
?>
</Visitors>
<?php
		} else {
			$visitors['Visitor'] = $visits;
			$json = array('Visitors' => $visitors);
			$json = json_encode($json);
			echo($json);
		}
	}
	else {
		if ($_REQUEST['Format'] == 'xml') {
			header('Content-type: text/xml; charset=utf-8');
			echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Visitors xmlns="urn:LiveHelp"/>
<?php
		} else {
			header('Content-type: application/json; charset=utf-8');
			$visitors = array('Visitors' => null);
			$json = json_encode($visitors);
			echo($json);
		}
	}
}

function Visitor() {

	global $_OPERATOR;
	global $SQL;
	global $_PLUGINS;
	global $_SETTINGS;
	global $hooks;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	
	$query = sprintf("SELECT *, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`))) AS `sitetime`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`))) AS `pagetime` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `id` = %d LIMIT 1", $_REQUEST['ID']);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		
		$initiated_default_label = 'Live Help Request has not been Initiated';
		$initiated_sending_label = 'Sending the Initiate Live Help Request...';
		$initiated_waiting_label = 'Waiting on the Initiate Live Help Reply...';
		$initiated_accepted_label = 'Initiate Live Help Request was ACCEPTED';
		$initiated_declined_label = 'Initiate Live Help Request was DECLINED';
		$initiated_chatting_label = 'Currently chatting to Operator';
		$initiated_chatted_label = 'Already chatted to an Operator';
		$initiated_pending_label = 'Currently Pending for Live Help';
		
		$rating_label = 'Rating';			
		$unavailable_label = 'Unavailable';
		
		if (is_array($row)) {
			$id = $row['id'];
			$ipaddress = $row['ipaddress'];
			$useragent = $row['useragent'];
			$resolution = $row['resolution'];
			$country = $row['country'];
			$page = $row['url'];
			$pagetitle = $row['title'];
			$referrer = $row['referrer'];
			$pagetime = $row['pagetime'];
			$pagepath = $row['path'];
			$sitetime = $row['sitetime'];
			$initiate = $row['initiate'];
			
			// Operator Name
			$query = sprintf("SELECT `id`, `username`, `department`, `rating`, `active` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` WHERE `request` = '%d' LIMIT 1", $id);
			$row = $SQL->selectquery($query);
			if (is_array($row)) {
			
				$session = $row['id'];
				$username = $row['username'];
				$department = $row['department'];
				$rating = $row['rating'];
				$active = $row['active'];
				
				if ($active == '-1' || $active == '-3') {
				
					// Display the rating of the ended chat request
					if ($rating > 0) {
						$initiatestatus = $initiated_chatted_label . ' - ' . $rating_label . ' (' . $rating . '/5)';
					} else {
						$initiatestatus = $initiated_chatted_label;
					}
					
					// Initiate Chat Status
					switch ($initiate) {
						case 0: // Not Initiated
							break;
						case -1: // Waiting
							$initiatestatus = $initiated_waiting_label;
							break;
						case -2: // Accepted
							$initiatestatus = $initiated_accepted_label;
							break;
						case -3: // Declined
							$initiatestatus = $initiated_declined_label;
							break;
						case -4: // Chatting
							break;
						default: // Sending
							$initiatestatus = $initiated_sending_label;
							break;
					}
				
				}
				else {
				
					if ($active > 0) {
					
						$firstname = '';
						$lastname = '';
					
						$query = sprintf("SELECT `firstname`, `lastname` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = '%d' LIMIT 1", $active);
						$row = $SQL->selectquery($query);
						if (is_array($row)) {
							$firstname = $row['firstname'];
							$lastname = $row['lastname'];
						}
					
						if ($firstname != '' && $lastname != '') {
							$initiatestatus = $initiated_chatting_label . ' (' . $firstname . ' ' . $lastname . ')';
						}
						else {
							$initiatestatus = $initiated_chatting_label . ' (' . $unavailable_label . ')';
						}
					}
					else {
						if ($department != '') {
							$initiatestatus = $initiated_pending_label . ' (' . $department . ')';
						}
						else {
							$initiatestatus = $initiated_pending_label;
						}
					}
				}
			}
			else {
				$session = 0;
				$username = '';
				$active = '';
				
				// Initiate Chat Status
				switch($initiate) {
					case 0: // Default Status
						$initiatestatus = $initiated_default_label;
						break;
					case -1: // Waiting
						$initiatestatus = $initiated_waiting_label;
						break;
					case -2: // Accepted
						$initiatestatus = $initiated_accepted_label;
						break;
					case -3: // Declined
						$initiatestatus = $initiated_declined_label;
						break;
					default: // Sending
						$initiatestatus = $initiated_sending_label;
						break;
				}
			}
			
			if ($page == '') {
				$page = $unavailable_label;
			}
			
			// Set the referrer as approriate
			if ($referrer != '' && $referrer != 'false') {
				$referrer = urldecode($referrer);
			}
			elseif ($referrer == false) {
				$referrer = 'Direct Visit / Bookmark';
			}
			else {
				$referrer = $unavailable_label;
			}
			
			header('Content-type: text/xml; charset=utf-8');
			echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
			
			// Custom Visitor Details
			$custom = '';
			$reference = '';
			
			$data = $hooks->run('VisitorCustomDetails', $id);
			if ($data != false) {
				$custom = $data['Custom'];
				$username = $data['Username'];
				$reference = $data['Reference'];
			}

?>
<Visitor xmlns="urn:LiveHelp" ID="<?php echo($id); ?>" Session="<?php echo($session); ?>" Active="<?php echo($active); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>">
<Hostname><?php echo(xmlelementinvalidchars($ipaddress)); ?></Hostname>
<Country><?php echo($country); ?></Country>
<UserAgent><?php echo(xmlelementinvalidchars($useragent)); ?></UserAgent>
<Resolution><?php echo(xmlelementinvalidchars($resolution)); ?></Resolution>
<CurrentPage><?php echo(xmlelementinvalidchars($page)); ?></CurrentPage>
<CurrentPageTitle><?php echo(xmlelementinvalidchars($pagetitle)); ?></CurrentPageTitle>
<Referrer><?php echo(xmlelementinvalidchars($referrer)); ?></Referrer>
<TimeOnPage><?php echo($pagetime); ?></TimeOnPage>
<ChatStatus><?php echo(xmlelementinvalidchars($initiatestatus)); ?></ChatStatus>
<PagePath><?php echo(xmlelementinvalidchars($pagepath)); ?></PagePath>
<TimeOnSite><?php echo($sitetime); ?></TimeOnSite>
</Visitor>
<?php
		}
	}
	else {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Visitor xmlns="urn:LiveHelp"/>
<?php
	}
}

function Version() {

	global $_OPERATOR;
	global $SQL;
	global $_SETTINGS;
	global $web_application_version;
	global $windows_application_version;

	if (!isset($_REQUEST['Windows'])){ $_REQUEST['Windows'] = ''; }
	if ($_REQUEST['Windows'] == $windows_application_version) { $result = 'true'; } else { $result = 'false'; }
	
	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Version xmlns="urn:LiveHelp" Web="<?php echo($web_application_version); ?>" Windows="<?php echo($result); ?>"/>
<?php
	} else {

		if ($result && strtolower($result) !== "false") {
			$result = true;
		} else {
			$result = false;
		}
		
		header('Content-type: application/json; charset=utf-8');
		$json = array('Web' => floatval($web_application_version), 'Windows' => $result);
		echo(json_encode($json));
	}

	exit();
}

function Settings() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $_PLUGINS;
	global $SQL;
	global $hooks;
	
	if (!isset($_REQUEST['Cached'])){ $_REQUEST['Cached'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }

	// Save Settings Full Administrator / Department Administrator
	if ($_OPERATOR['PRIVILEGE'] < 2) {
	
		// Update Settings
		$updated = false;
		foreach ($_REQUEST as $key => $value) {
			// Valid Setting
			if (array_key_exists(strtoupper($key), $_SETTINGS)) { 
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "settings SET `value` = '%s' WHERE `name` = '%s'", $SQL->escape($value), $SQL->escape($key));
				$SQL->updatequery($query);
				$updated = true;
			}
		}
		
		// Last Updated
		if ($updated == true) {
			$query = "UPDATE " . $_SETTINGS['TABLEPREFIX'] . "settings SET `value` = NOW() WHERE `name` = 'LastUpdated'";
			$SQL->updatequery($query);
		}
		
		$query = "SELECT `name`, `value` FROM " . $_SETTINGS['TABLEPREFIX'] . "settings";
		$row = $SQL->selectquery($query);
		while ($row) {
			if (is_array($row)) {
				$_SETTINGS[strtoupper($row['name'])] = $row['value'];
			}
			$row = $SQL->selectnext();
		}
		
		// Default Settings
		if (!isset($_SETTINGS['CHATWINDOWWIDTH'])) { $_SETTINGS['CHATWINDOWWIDTH'] = 625; }
		if (!isset($_SETTINGS['CHATWINDOWHEIGHT'])) { $_SETTINGS['CHATWINDOWHEIGHT'] = 435; }
		if (!isset($_SETTINGS['TEMPLATE'])) { $_SETTINGS['TEMPLATE'] = 'default'; }
		if (!isset($_SETTINGS['LOCALE'])) { $_SETTINGS['LOCALE'] = 'en'; }
		
	}
	
	// Time Zone Setting
	$_SETTINGS['DEFAULTTIMEZONE'] = date('Z');
	
	// Language Packs
	$languages = file('../locale/i18n.txt');
	$available_languages = '';
	foreach ($languages as $key => $line) {
		$i18n = explode(',', $line);
		$code = trim($i18n[0]);
		$available = file_exists('../locale/' . $code . '/guest.php');
		if ($available) {
			if ($available_languages == '') {
				$available_languages .= $code;
			}
			else {
				$available_languages .=  ', ' . $code;
			}
		}
	}

	// Templates	
	$templates = array();
	$templatedir = '../templates/';

	if (is_dir($templatedir)) {
		if ($dh = opendir($templatedir)) {
			while (($file = readdir($dh)) !== false) {
				if (is_dir($templatedir . $file) && $file != '.' && $file != '..') {
					
					$name = ucwords(str_replace('-', ' ', $file));
					
					$template = array('name' => $name, 'value' => $file);
					$templates[] = $template;
				}
			}
			closedir($dh);
		}
	}

	$_SETTINGS = $hooks->run('SettingsLoaded', $_SETTINGS);

	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Settings xmlns="urn:LiveHelp">
<Domain><?php echo(xmlelementinvalidchars($_SETTINGS['DOMAIN'])); ?></Domain>
<SiteAddress><?php echo(xmlelementinvalidchars($_SETTINGS['URL'])); ?></SiteAddress>
<Email><?php echo(xmlelementinvalidchars($_SETTINGS['EMAIL'])); ?></Email>
<Name><?php echo(xmlelementinvalidchars($_SETTINGS['NAME'])); ?></Name>
<Logo><?php echo(xmlelementinvalidchars($_SETTINGS['LOGO'])); ?></Logo>
<WelcomeMessage><?php echo(xmlelementinvalidchars($_SETTINGS['INTRODUCTION'])); ?></WelcomeMessage>
<?php 
	if (isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 3.5) { 
?>
<Smilies Enabled="<?php echo($_SETTINGS['SMILIES']); ?>"/>
<?php
	} else {
		if (!isset($_SETTINGS['GUESTSMILIES'])) { $_SETTINGS['GUESTSMILIES'] = '-1'; }
		if (!isset($_SETTINGS['OPERATORSMILIES'])) { $_SETTINGS['OPERATORSMILIES'] = '-1'; }
?>
<Smilies Guest="<?php echo($_SETTINGS['GUESTSMILIES']); ?>" Operator="<?php echo($_SETTINGS['OPERATORSMILIES']); ?>"/>
<?php
	}
?>
<Font Size="<?php echo(xmlattribinvalidchars($_SETTINGS['FONTSIZE'])); ?>" Color="<?php echo(xmlattribinvalidchars($_SETTINGS['FONTCOLOR'])); ?>" LinkColor="<?php echo(xmlattribinvalidchars($_SETTINGS['LINKCOLOR'])); ?>"><?php echo(xmlattribinvalidchars($_SETTINGS['FONT'])); ?></Font>
<ChatFont Size="<?php echo(xmlattribinvalidchars($_SETTINGS['CHATFONTSIZE'])); ?>" SentColor="<?php echo(xmlattribinvalidchars($_SETTINGS['SENTFONTCOLOR'])); ?>" ReceivedColor="<?php echo(xmlattribinvalidchars($_SETTINGS['RECEIVEDFONTCOLOR'])); ?>"><?php echo(xmlelementinvalidchars($_SETTINGS['CHATFONT'])); ?></ChatFont>
<BackgroundColor><?php echo(xmlelementinvalidchars($_SETTINGS['BACKGROUNDCOLOR'])); ?></BackgroundColor>
<OnlineLogo><?php echo(xmlelementinvalidchars($_SETTINGS['ONLINELOGO'])); ?></OnlineLogo>
<OfflineLogo><?php echo(xmlelementinvalidchars($_SETTINGS['OFFLINELOGO'])); ?></OfflineLogo>
<OfflineEmailLogo><?php echo(xmlelementinvalidchars($_SETTINGS['OFFLINEEMAILLOGO'])); ?></OfflineEmailLogo>
<BeRightBackLogo><?php echo(xmlelementinvalidchars($_SETTINGS['BERIGHTBACKLOGO'])); ?></BeRightBackLogo>
<AwayLogo><?php echo(xmlelementinvalidchars($_SETTINGS['AWAYLOGO'])); ?></AwayLogo>
<LoginDetails Enabled="<?php echo($_SETTINGS['LOGINDETAILS']); ?>" Required="<?php echo(xmlattribinvalidchars($_SETTINGS['REQUIREGUESTDETAILS'])); ?>" Email="<?php echo($_SETTINGS['LOGINEMAIL']); ?>" Question="<?php echo($_SETTINGS['LOGINQUESTION']); ?>"/>
<OfflineEmail Enabled="<?php echo($_SETTINGS['OFFLINEEMAIL']); ?>" Redirect="<?php echo(xmlattribinvalidchars($_SETTINGS['OFFLINEEMAILREDIRECT'])); ?>"><?php echo(xmlelementinvalidchars($_SETTINGS['OFFLINEEMAIL'])); ?></OfflineEmail>
<SecurityCode Enabled="<?php echo($_SETTINGS['SECURITYCODE']); ?>"/>
<Departments Enabled="<?php echo($_SETTINGS['DEPARTMENTS']); ?>"/>
<VisitorTracking Enabled="<?php echo($_SETTINGS['VISITORTRACKING']); ?>"/>
<Timezone Server="<?php echo($_SETTINGS['DEFAULTTIMEZONE']); ?>"><?php echo(xmlelementinvalidchars($_SETTINGS['TIMEZONE'])); ?></Timezone>
<Language Available="<?php echo(xmlattribinvalidchars($available_languages)); ?>"><?php echo(xmlelementinvalidchars($_SETTINGS['LOCALE'])); ?></Language>
<InitiateChat Vertical="<?php echo(xmlattribinvalidchars($_SETTINGS['INITIATECHATVERTICAL'])); ?>" Horizontal="<?php echo(xmlattribinvalidchars($_SETTINGS['INITIATECHATHORIZONTAL'])); ?>" Auto="<?php echo($_SETTINGS['INITIATECHATAUTO']); ?>"/>
<ChatUsername Enabled="<?php echo($_SETTINGS['CHATUSERNAME']); ?>"/>
<Campaign Link="<?php echo(xmlattribinvalidchars($_SETTINGS['CAMPAIGNLINK'])); ?>"><?php echo(xmlelementinvalidchars($_SETTINGS['CAMPAIGNIMAGE'])); ?></Campaign>
<IP2Country Enabled="<?php echo($_SETTINGS['IP2COUNTRY']); ?>"/>
<P3P><?php echo(xmlelementinvalidchars($_SETTINGS['P3P'])); ?></P3P>
<ChatWindowSize Width="<?php echo($_SETTINGS['CHATWINDOWWIDTH']); ?>" Height="<?php echo($_SETTINGS['CHATWINDOWHEIGHT']); ?>"/>
<Code>
<Head><![CDATA[<?php echo($_SETTINGS['HTMLHEAD']); ?>]]></Head>
<Body><![CDATA[<?php echo($_SETTINGS['HTMLBODY']); ?>]]></Body>
<Image><![CDATA[<?php echo($_SETTINGS['HTMLIMAGE']); ?>]]></Image>
</Code>
<?php
	if (isset($_PLUGINS)) {
?>
<Plugins>
<?php
		$hooks->run('SettingsPlugin');
?>
</Plugins>
<?php
	}

	if (is_array($templates)) {
?>
<Templates Current="<?php echo($_SETTINGS['TEMPLATE']); ?>">
<?php
		foreach ($templates as $key => $template) {
			$name = $template['name'];
			$value = $template['value'];
?>
<Template Name="<?php echo(xmlattribinvalidchars($name)); ?>" Value="<?php echo(xmlattribinvalidchars($value)); ?>" />
<?php
		}
?>
</Templates>
<?php
	}
?>
</Settings>
<?php
	} else {
		
		if ($_REQUEST['Cached'] != '') { 
			$updated = strtotime($_SETTINGS['LASTUPDATED']);
			$cached = strtotime($_REQUEST['Cached']);
			if ($updated - $cached <= 0) {
				if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 304 Not Modified'); } else { header('Status: 304 Not Modified'); }
				exit();
			}
		}
		
		header('Content-type: application/json; charset=utf-8');
		$settings = array(
			'Domain' => $_SETTINGS['DOMAIN'],
			'SiteAddress' => $_SETTINGS['URL'],
			'Email' => $_SETTINGS['EMAIL'],
			'Name' => $_SETTINGS['NAME'],
			'Logo' => $_SETTINGS['LOGO'],
			'WelcomeMessage' => $_SETTINGS['INTRODUCTION'],
			'Smilies' => (int)$_SETTINGS['SMILIES'],
			'Font' => array('Type' => $_SETTINGS['FONT'], 'Size' => $_SETTINGS['FONTSIZE'], 'Color' => $_SETTINGS['FONTCOLOR'], 'LinkColor' => $_SETTINGS['LINKCOLOR']),
			'ChatFont' => array('Type' => $_SETTINGS['FONT'], 'Size' => $_SETTINGS['CHATFONTSIZE'], 'SentColor' => $_SETTINGS['SENTFONTCOLOR'], 'ReceivedColor' => $_SETTINGS['RECEIVEDFONTCOLOR']),
			'BackgroundColor' => $_SETTINGS['BACKGROUNDCOLOR'],
			'OnlineLogo' => $_SETTINGS['ONLINELOGO'],
			'OfflineLogo' => $_SETTINGS['OFFLINELOGO'],
			'OfflineEmailLogo' => $_SETTINGS['OFFLINEEMAILLOGO'],
			'BeRightBackLogo' => $_SETTINGS['BERIGHTBACKLOGO'],
			'AwayLogo' => $_SETTINGS['AWAYLOGO'],
			'LoginDetails' => array('Enabled' => (int)$_SETTINGS['LOGINDETAILS'], 'Required' => (int)$_SETTINGS['REQUIREGUESTDETAILS'], 'Email' => (int)$_SETTINGS['LOGINEMAIL'], 'Question' => (int)$_SETTINGS['LOGINQUESTION']),
			'OfflineEmail' => array('Enabled' => (int)$_SETTINGS['OFFLINEEMAIL'], 'Redirect' => $_SETTINGS['OFFLINEEMAILREDIRECT'], 'Email' => (int)$_SETTINGS['OFFLINEEMAIL']),
			'SecurityCode' => (int)$_SETTINGS['SECURITYCODE'],
			'Departments' => (int)$_SETTINGS['DEPARTMENTS'],
			'VisitorTracking' => (int)$_SETTINGS['VISITORTRACKING'],
			'Timezone' => array('Offset' => $_SETTINGS['DEFAULTTIMEZONE'], 'Server' => $_SETTINGS['TIMEZONE']),
			'Language' => array('Available' => $available_languages, 'Locale' => $_SETTINGS['LOCALE']),
			'InitiateChat' => array('Vertical' => $_SETTINGS['INITIATECHATVERTICAL'], 'Horizontal' => $_SETTINGS['INITIATECHATHORIZONTAL'], 'Auto' => $_SETTINGS['INITIATECHATAUTO']),
			'ChatUsername' => (int)$_SETTINGS['CHATUSERNAME'],
			'Campaign' => array('Link' => $_SETTINGS['CAMPAIGNLINK'], 'Image' => $_SETTINGS['CAMPAIGNIMAGE']),
			'P3P' => $_SETTINGS['P3P'],
			'ChatWindowSize' => array('Width' => (int)$_SETTINGS['CHATWINDOWWIDTH'], 'Height' => (int)$_SETTINGS['CHATWINDOWHEIGHT']),
			'LastUpdated' => $_SETTINGS['LASTUPDATED'],
			'Code' => array('Head' => $_SETTINGS['HTMLHEAD'], 'Body' => $_SETTINGS['HTMLBODY'], 'Image' => $_SETTINGS['HTMLIMAGE']),
			'Templates' => $templates,
			'Version' => array('Server' => (float)$_SETTINGS['SERVERVERSION'])
		);
		$json = array('Settings' => $settings);
		$json = json_encode($json);
		echo($json);
	}

}

function InitaliseChat() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Message'])){ $_REQUEST['Message'] = ''; }

	$query = sprintf("SELECT `email`, `server`, `department`, `typing`, `active` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d", $_REQUEST['ID']);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$email = $row['email'];
		$question = '';
		$server = $row['server'];
		$department = $row['department'];
		$typing = $row['typing'];
		$active = $row['active'];
	}

	$query = sprintf("SELECT `id`, `chat`, `username`, `message`, `align`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND `status` <= '3' AND `id` > %d ORDER BY `datetime`", $_REQUEST['ID'], $_REQUEST['Message']);
	$rows = $SQL->selectall($query);
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$message = $row['id']; 
			}
		}
	}
	else {
		$message = '';
	}
	
	header('Content-type: text/xml; charset=utf-8');
	echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Messages xmlns="urn:LiveHelp" ID="<?php echo($_REQUEST['ID']); ?>" Status="<?php echo($active); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Server="<?php echo(xmlattribinvalidchars($server)); ?>" Department="<?php echo(xmlattribinvalidchars($department)); ?>" Question="<?php echo(xmlattribinvalidchars($question)); ?>">
<?php
if (is_array($rows)) {
	foreach ($rows as $key => $row) {
		if (is_array($row)) {
		
			$id = $row['id'];
			$session = $row['chat']; 
			$username = $row['username'];
			$message = $row['message'];
			$align = $row['align'];
			$status = $row['status'];
			$custom = 0;
			
			// Integration
			if ($status == -4) {
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `id` = %d", $align);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
			}
			
			// Outputs sent message
			if ($status == 1) {
?>
<Message ID="<?php echo($id); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
			} else {	// Outputs received message
				if ($custom > 0) {
?>
<Message ID="<?php echo($id); ?>" Custom="<?php echo($custom); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
				} else {
?>
<Message ID="<?php echo($id); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
				}
			}
		}
	}
}
?>
</Messages>
<?php

}

function Chat() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Message'])){ $_REQUEST['Message'] = ''; }
	if (!isset($_REQUEST['Staff'])){ $_REQUEST['Staff'] = ''; }
	if (!isset($_REQUEST['Typing'])){ $_REQUEST['Typing'] = ''; }

	if (!$_REQUEST['Staff']) {
		$query = sprintf("SELECT `active`, `typing` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d", $_REQUEST['ID']);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$active = $row['active'];
			$typing = $row['typing'];
			
			if ($_REQUEST['Typing']) { // Currently Typing
				switch($typing) {
				case 0: // None
					$typingresult = 2;
					break;
				case 1: // Guest Only
					$typingresult = 3;
					break;
				case 2: // Operator Only
					$typingresult = 2;
					break;
				case 3: // Both
					$typingresult = 3;
					break;		
				}
			}
			else { // Not Currently Typing
				switch($typing) {
				case 0: // None
					$typingresult = 0;
					break;
				case 1: // Guest Only
					$typingresult = 1;
					break;
				case 2: // Operator Only
					$typingresult = 0;
					break;
				case 3: // Both
					$typingresult = 1;
					break;		
				}
			}
				
			// Update the typing status of the specified chatting visitor
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `typing` = '$typingresult' WHERE `id` = %d", $_REQUEST['ID']);
			$SQL->updatequery($query);
		}
	}
	else {
		$active = '-1';
		$typingresult = '0';
	}
	
	if ($_REQUEST['Staff']) {
		$query = sprintf("SELECT `username` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d", $_REQUEST['ID']);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$username = $row['username'];
		}
		$query = sprintf("SELECT `id`, `user`, `username`, `message`, `align`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "administration WHERE ((`user` = %d AND `username` = '%s') OR (`user` = %d AND `username` = '%s')) AND `status` <= '3' AND `id` > '%s' AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('%s')) > '0' ORDER BY `datetime`", $_REQUEST['ID'], $SQL->escape($_OPERATOR['USERNAME']), $_OPERATOR['ID'], $SQL->escape($username), $SQL->escape($_REQUEST['Message']), $SQL->escape($_OPERATOR['DATETIME']));
	}
	else {
		$query = sprintf("SELECT `id`, `chat`, `username`, `message`, `align`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND `status` <= '3' AND `id` > '%s' ORDER BY `datetime`", $_REQUEST['ID'], $SQL->escape($_REQUEST['Message']));
	}
	$rows = $SQL->selectall($query);
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$message = $row['id']; 
			}
		}
	}
	else {
		$message = '';
	}
	
	header('Content-type: text/xml; charset=utf-8');
	echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Messages xmlns="urn:LiveHelp" ID="<?php echo($_REQUEST['ID']); ?>" Typing="<?php echo($typingresult); ?>" Status="<?php echo($active); ?>" ChatType="<?php echo($_REQUEST['Staff']); ?>">
<?php
if (is_array($rows)) {
	foreach ($rows as $key => $row) {
		if (is_array($row)) {
		
			if ($_REQUEST['Staff']) {
				$session = $row['user'];
			}
			else {
				$session = $row['chat']; 
			}
			$id = $row['id'];
			$username = $row['username'];
			$message = $row['message'];
			$align = $row['align'];
			$status = $row['status'];
			
			// Integration
			if ($status == -4) {
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `id` = %d", $align);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
			}
			
			// Outputs sent message
			if ((!$_REQUEST['Staff'] && $status == 1) || ($_REQUEST['Staff'] && $session == $_REQUEST['ID'] && $row['username'] == $_OPERATOR['USERNAME'])) {
?>
<Message ID="<?php echo($id); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
			}
			// Outputs received message
			if ((!$_REQUEST['Staff'] && $status != 1) || ($_REQUEST['Staff'] && $session == $_OPERATOR['ID'] && $row['username'] != $_OPERATOR['USERNAME'])) {
				if ($custom > 0) {
?>
<Message ID="<?php echo($id); ?>" Custom="<?php echo($custom); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
				} else {
?>
<Message ID="<?php echo($id); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
				}
			}
		}
	}
}
?>
</Messages>
<?php

}

function Chats() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['Data'])){ $_REQUEST['Data'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	
	if ($_REQUEST['Data'] == '') {
?>
<MultipleMessages xmlns="urn:LiveHelp"/>
<?php
		exit();
	}
	
	$data = explode('|', $_REQUEST['Data']);
	if (is_array($data)) {
		
		if ($_REQUEST['Format'] == 'xml') {
			header('Content-type: text/xml; charset=utf-8');
			echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<MultipleMessages xmlns="urn:LiveHelp">
<?php
		}
		else {
			$chats = array();
		}
		
		foreach ($data as $chatkey => $value) {
			list($id, $typingstatus, $staff, $message) = explode(',', $value);
			
			$introduction = false;
			if ($message < 0) { $introduction = true; }
			
			$active = -1;
			$typingresult = 0;
			if (!$staff) {
				$query = sprintf("SELECT `username`, `active`, `typing` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d", $id);
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					$username = $row['username'];
					$active = $row['active'];
					$typing = $row['typing'];
					
					if ($typingstatus) { // Currently Typing
						switch($typing) {
						case 0: // None
							$typingresult = 2;
							break;
						case 1: // Guest Only
							$typingresult = 3;
							break;
						case 2: // Operator Only
							$typingresult = 2;
							break;
						case 3: // Both
							$typingresult = 3;
							break;		
						}
					}
					else { // Not Currently Typing
						switch($typing) {
						case 0: // None
							$typingresult = 0;
							break;
						case 1: // Guest Only
							$typingresult = 1;
							break;
						case 2: // Operator Only
							$typingresult = 0;
							break;
						case 3: // Both
							$typingresult = 1;
							break;		
						}
					}
						
					// Update the typing status of the specified chatting visitor
					$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "chats SET `typing` = '%d' WHERE `id` = %d", $typingresult, $id);
					$SQL->updatequery($query);
				}
			}
			
			if ($staff) {
				$query = sprintf("SELECT `username` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d", $id);
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					$operator = $row['username'];
				}
				$query = sprintf("SELECT `id`, `user`, `username`, `datetime`, `message`, `align`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "administration WHERE ((`user` = %d AND `username` = '%s') OR (`user` = %d AND `username` = '%s')) AND (`status` <= '3' OR `status` = '7') AND `id` > %d AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('%s')) > '0' ORDER BY `datetime`", $id, $SQL->escape($_OPERATOR['USERNAME']), $_OPERATOR['ID'], $SQL->escape($operator), $message, $SQL->escape($_OPERATOR['DATETIME']));
			}
			else {
				$query = sprintf("SELECT `id`, `chat`, `username`, `datetime`, `message`, `align`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND (`status` <= '3' OR `status` = '7') AND `id` > %d ORDER BY `datetime`", $id, $message);
			}
			$rows = $SQL->selectall($query);
			if (is_array($rows)) {
				foreach ($rows as $key => $row) {
					if (is_array($row)) {
						$message = $row['id']; 
					}
				}
			}
			else { $message = ''; }
			
			if ($_REQUEST['Format'] == 'xml') {
?>
<Messages xmlns="urn:LiveHelp" ID="<?php echo($id); ?>" Typing="<?php echo($typingresult); ?>" Status="<?php echo($active); ?>" ChatType="<?php echo($staff); ?>">
<?php
			}
			else {
				$chat = array('ID' => $id, 'Typing' => $typingresult, 'Status' => $active, 'ChatType' => $staff);
				$messages = array();
			}

/*
if ($introduction == true && $_SETTINGS['INTRODUCTION'] != '') {
	$_SETTINGS['INTRODUCTION'] = preg_replace("/({Username})/", $username, $_SETTINGS['INTRODUCTION']);
	if ($_REQUEST['Format'] == 'xml') {
?>
<Message ID="<?php echo($msgid); ?>" Datetime="<?php echo($datetime); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
	}
}
*/

$names = array();

if (is_array($rows)) {
	foreach ($rows as $key => $row) {
		if (is_array($row)) {
		
			if ($staff) {
				$session = $row['user'];
			}
			else {
				$session = $row['chat']; 
			}
			$msgid = $row['id'];
			$username = $row['username'];
			$datetime = $row['datetime'];
			$message = $row['message'];
			$align = $row['align'];
			$status = $row['status'];
			$custom = '';
			$reference = '';
			
			/* TODO Operator Username / Firstname
			if ($status > 0) {
				if (!array_key_exists($username, $names)) {
					$query = sprintf("SELECT `firstname` FROM `" . $_SETTINGS['TABLEPREFIX'] . "users` WHERE `username` LIKE BINARY '%s' LIMIT 1", $username);
					$name = $SQL->selectquery($query);
					if (is_array($name)) {
						$username = $name['firstname'];
						$names[$username] = $name['firstname'];
					}
				} else {
					$username = $names[$username];
				}
			}
			*/
			
			// Integration
			if ($status == -4) {
				$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `id` = %d", $align);
				$integration = $SQL->selectquery($query);
				if (is_array($integration)) {
					$custom = $integration['custom'];
					$reference = $integration['reference'];
				}
			}
			
			// Outputs sent message
			if ((!$staff && $status == 1) || ($staff && $session == $id && $username == $_OPERATOR['USERNAME'])) {
				if ($_REQUEST['Format'] == 'xml') {
?>
<Message ID="<?php echo($msgid); ?>" Datetime="<?php echo($datetime); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
				}
				else {
					$messages[] = array(
						'ID' => $msgid,
						'Content' => $message,
						'Datetime' => $datetime,
						'Align' => $align,
						'Status' => $status,
						'Username' => $username
					);
				}
			}
			// Outputs received message
			if ((!$staff && $status != 1) || ($staff && $session == $_OPERATOR['ID'] && $username == $operator)) {
				if ($_REQUEST['Format'] == 'xml') {
					if ($custom > 0 && !empty($reference)) {
?>
<Message ID="<?php echo($msgid); ?>" Datetime="<?php echo($datetime); ?>" Custom="<?php echo($custom); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
					} else {
?>
<Message ID="<?php echo($msgid); ?>" Datetime="<?php echo($datetime); ?>" Align="<?php echo($align); ?>" Status="<?php echo($status); ?>" Username="<?php echo(xmlattribinvalidchars($username)) ?>"><?php echo(xmlelementinvalidchars($message)); ?></Message>
<?php
					}
				}
				else {
					$messages[] = array(
						'ID' => $msgid,
						'Content' => $message,
						'Datetime' => $datetime,
						'Align' => $align,
						'Status' => $status,
						'Username' => $username
					);
				}
			}
		}
	}
}
			if ($_REQUEST['Format'] == 'xml') {
?>
</Messages>
<?php
			}
			else {
				$chat['Message'] = $messages;
				$chats[] = $chat;
			}
		}
		if ($_REQUEST['Format'] == 'xml') {
?>
</MultipleMessages>
<?php
		}
		else {
			$json = array('MultipleMessages' => array('Messages' => $chats));
			$json = json_encode($json);
			echo($json);
		}
	}
}

function Operators() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;
	global $operators;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['User'])){ $_REQUEST['User'] = ''; }
	if (!isset($_REQUEST['Firstname'])){ $_REQUEST['Firstname'] = ''; }
	if (!isset($_REQUEST['Lastname'])){ $_REQUEST['Lastname'] = ''; }
	if (!isset($_REQUEST['CurrentPassword'])){ $_REQUEST['CurrentPassword'] = ''; }
	if (!isset($_REQUEST['NewPassword'])){ $_REQUEST['NewPassword'] = ''; }
	if (!isset($_REQUEST['Email'])){ $_REQUEST['Email'] = ''; }
	if (!isset($_REQUEST['Department'])){ $_REQUEST['Department'] = ''; }
	if (!isset($_REQUEST['Image'])){ $_REQUEST['Image'] = ''; }
	if (!isset($_REQUEST['Privilege'])){ $_REQUEST['Privilege'] = ''; }
	if (!isset($_REQUEST['Disabled'])){ $_REQUEST['Disabled'] = ''; }
	if (!isset($_REQUEST['Status'])){ $_REQUEST['Status'] = ''; }
	if (!isset($_REQUEST['Cached'])){ $_REQUEST['Cached'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	if (!isset($_REQUEST['Version'])){ $_REQUEST['Version'] = ''; }
	
	// Password Hash
	$hash = '';
	$password = $_REQUEST['NewPassword'];

	// Hash Operator Password
	if (!empty($_REQUEST['Version']) && !empty($password)) {
		$version = $_REQUEST['Version'];
		list($major, $minor) = explode('.', $version);
		if ((int)$major >= 4) {
			if (strlen($password) > 72) { 
				$hash = '';
			} else {
				$hasher = new PasswordHash(8, true);
				$hash = $hasher->HashPassword($password);
				if (strlen($hash) < 20) {
					$hash = '';
				}
			}
		} else {
			if (function_exists('hash')) {
				if (in_array('sha512', hash_algos())) {
					$hash = hash('sha512', $password);
				}
				elseif (in_array('sha1', hash_algos())) {
					$hash = hash('sha1', $password);
				}
			} else if (function_exists('mhash') && mhash_get_hash_name(MHASH_SHA512) != false) {
				$hash = bin2hex(mhash(MHASH_SHA512, $password));
			} else if (function_exists('sha1')) {
				$hash = sha1($password);
			}
		}
	}

	if (!empty($_REQUEST['ID'])) {
	
		// Editing Own Account
		if ($_OPERATOR['ID'] == $_REQUEST['ID']) {
		
			// Can't change permission to lower value - higher administration rights
			if ($_REQUEST['Privilege'] < $_OPERATOR['PRIVILEGE']) {
				$_REQUEST['Privilege'] = $_OPERATOR['PRIVILEGE'];
			}
		}
		else {
			// Other Access Levels Excluding Full / Department Administrator
			if ($_OPERATOR['PRIVILEGE'] > 1) {
			
				if ($_REQUEST['Format'] == 'xml') {
					header('Content-type: text/xml; charset=utf-8');
					echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
			
?>
<Operators xmlns="urn:LiveHelp" />
<?php
				}
				else {
					header('Content-type: application/json; charset=utf-8');
					$operators = array('Operators' => null);
					$json = json_encode($operators);
					echo($json);	
				}
				exit();
			}
		}
	
		// Update Existing Account
		if (!empty($_REQUEST['ID']) && !empty($_REQUEST['User']) && !empty($_REQUEST['Firstname']) && !empty($_REQUEST['Email']) && !empty($_REQUEST['Department']) && $_REQUEST['Privilege'] != '' && $_REQUEST['Disabled'] != '') {
			
			// Update Username
			$query = sprintf("SELECT `username` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d LIMIT 1", $SQL->escape($_REQUEST['ID']));
			$row = $SQL->selectquery($query);
			if (is_array($row)) {
				$username = $row['username'];
				if ($_REQUEST['User'] != $username) {
					// Update Messages
					$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "messages SET `username` = '%s' WHERE `username` = '%s' AND `status` <> 0", $SQL->escape($_REQUEST['User']), $SQL->escape($username));
					$SQL->updatequery($query);

					// Update Operator Messages
					$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "administration SET `username` = '%s' WHERE `username` = '%s'", $SQL->escape($_REQUEST['User']), $SQL->escape($username));
					$SQL->updatequery($query);
				}
			}

			// Uploaded Operator Image
			$upload = isset($_FILES['files']) ? $_FILES['files'] : null;
			if ($upload && is_array($upload['tmp_name'])) {
				// Upload File
				$file = $upload['tmp_name'][0];

				// Validate Image
				list($width, $height) = @getimagesize($file);
				if ($width >= 100 && $width <= 300 && $height >= 100 && $height <= 300) {
					$content = file_get_contents($file);
					$_REQUEST['Image'] = base64_encode($content);
				}
			}

			// Full Administrator / Department Administrator
			if ($_OPERATOR['PRIVILEGE'] < 2) {
				// Update Account
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `username` = '%s', `firstname` = '%s', `lastname` = '%s', `email` = '%s', `department` = '%s', `privilege` = '%d', `disabled` = '%d'", $SQL->escape($_REQUEST['User']), $SQL->escape($_REQUEST['Firstname']), $SQL->escape($_REQUEST['Lastname']), $SQL->escape($_REQUEST['Email']), $SQL->escape($_REQUEST['Department']), $_REQUEST['Privilege'], $_REQUEST['Disabled']);
				
				// Update Password
				if (!empty($hash)) {
					$query .= sprintf(", `password` = '%s'", $SQL->escape($hash));
				}

				// Update Image
				if (!empty($_REQUEST['Image'])) {
					$query .= sprintf(", `image` = '%s', `updated` = NOW()", $SQL->escape($_REQUEST['Image']));
				}
				$query .= sprintf(" WHERE `id` = %d", $_REQUEST['ID']);
			} else {
				// Update Account / Other Access Levels
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `username` = '%s', `firstname` = '%s', `lastname` = '%s', `email` = '%s', `disabled` = '%d'", $SQL->escape($_REQUEST['User']), $SQL->escape($_REQUEST['Firstname']), $SQL->escape($_REQUEST['Lastname']), $SQL->escape($_REQUEST['Email']), $_REQUEST['Disabled']);

				// Update Image
				if (!empty($_REQUEST['Image'])) {
					$query .= sprintf("', `image` = '%s', `updated` = NOW()", $SQL->escape($_REQUEST['Image']));
				}
				$query .= sprintf(" WHERE `id` = %d", $_REQUEST['ID']);
			}
			$result = $SQL->updatequery($query);
			if ($result == false) {
			
				if ($_REQUEST['Format'] == 'xml') {
					header('Content-type: text/xml; charset=utf-8');
					echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
			
?>
<Operators xmlns="urn:LiveHelp" />
<?php
				}
				else {
					header('Content-type: application/json; charset=utf-8');
					$operators = array('Operators' => null);
					$json = json_encode($operators);
					echo($json);
				}
				exit();
			}
	
		}
		elseif (!empty($_REQUEST['NewPassword'])) {  // Change password
			
			// Other Access Levels / Confirm Current Password
			if ($_OPERATOR['PRIVILEGE'] > 0 && !empty($_REQUEST['CurrentPassword'])) {
				$query = sprintf("SELECT `id` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d AND `password` = '%s' LIMIT 1", $_REQUEST['ID'], $SQL->escape($_REQUEST['CurrentPassword']));
				$row = $SQL->selectquery($query);
			}
			// Full Admnistrator
			if ($_OPERATOR['PRIVILEGE'] <= 0 || is_array($row)) {
		
				$hash = $_REQUEST['NewPassword'];
				if (isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 4.0) {
					$hasher = new PasswordHash(8, true);
					$hash = $hasher->HashPassword($hash);
				}

				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `password` = '%s' WHERE `id` = %d", $SQL->escape($hash), $_REQUEST['ID']);
				$result = $SQL->updatequery($query);
				if ($result == false) {
				
					if ($_REQUEST['Format'] == 'xml') {
						header('Content-type: text/xml; charset=utf-8');
						echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
				
?>
<Operators xmlns="urn:LiveHelp" />
<?php
					}
					else {
						header('Content-type: application/json; charset=utf-8');
						$operators = array('Operators' => null);
						$json = json_encode($operators);
						echo($json);
					}
					exit();
				}
				
			} elseif (!is_array($row)) {
			
				// Forbidden - Incorrect Password
				if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 403 Forbidden'); } else { header('Status: 403 Forbidden'); }
				exit();
				
			} else {
			
				if ($_REQUEST['Format'] == 'xml') {
					header('Content-type: text/xml; charset=utf-8');
					echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
				
?>
<Operators xmlns="urn:LiveHelp" />
<?php
				}
				else {
					header('Content-type: application/json; charset=utf-8');
					$operators = array('Operators' => null);
					$json = json_encode($operators);
					echo($json);
				}
				exit();
			}
		}
		else {  // Delete Account
		
			if ($_OPERATOR['ID'] != $_REQUEST['ID']) {
				$query = sprintf("DELETE FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d AND `privilege` <> -1", $_REQUEST['ID']);
				$result = $SQL->deletequery($query);
				if ($result == false) {
				
					if ($_REQUEST['Format'] == 'xml') {
						header('Content-type: text/xml; charset=utf-8');
						echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Operators xmlns="urn:LiveHelp" />
<?php
					}
					else {
						header('Content-type: application/json; charset=utf-8');
						$operators = array('Operators' => null);
						$json = json_encode($operators);
						echo($json);
					}
					exit();
				}
			}
			else {
				if ($_REQUEST['Format'] == 'xml') {
					header('Content-type: text/xml; charset=utf-8');
					echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Operators xmlns="urn:LiveHelp" />
<?php
				}
				else {
					header('Content-type: application/json; charset=utf-8');
					$operators = array('Operators' => null);
					$json = json_encode($operators);
					echo($json);
				}
				exit();
			}
		
		} 
	}
	else {
	
		// Full Administrator / Department Administrator
		if ($_OPERATOR['PRIVILEGE'] < 2) {
	
			// Add Account
			if ($_REQUEST['User'] != '' && $_REQUEST['Firstname'] != '' && $_REQUEST['NewPassword'] != '' && $_REQUEST['Email'] != '' && $_REQUEST['Department'] != '' && $_REQUEST['Privilege'] != '' && $_REQUEST['Disabled'] != '') {
		
				if ($_OPERATOR['PRIVILEGE'] > 0 && $_REQUEST['Privilege'] == 0) {
					if ($_REQUEST['Format'] == 'xml') {
						header('Content-type: text/xml; charset=utf-8');
						echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Operators xmlns="urn:LiveHelp" />
<?php
					}
					else {
						header('Content-type: application/json; charset=utf-8');
						$operators = array('Operators' => null);
						$json = json_encode($operators);
						echo($json);
					}
					exit();
				}
		
				if (isset($operators)) {
					$query = "SELECT COUNT(*) FROM " . $_SETTINGS['TABLEPREFIX'] . "users";
					$row = $SQL->selectquery($query);
					if (isset($row['COUNT(*)'])) {
						$total = $row['COUNT(*)'];
						if ($total == $operators) {
						
							if ($_REQUEST['Format'] == 'xml') {
								header('Content-type: text/xml; charset=utf-8');
								echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
				
?>
<Operators xmlns="urn:LiveHelp" />
<?php
							}
							else {
								header('Content-type: application/json; charset=utf-8');
								$operators = array('Operators' => null);
								$json = json_encode($operators);
								echo($json);
							}
							exit();
						}
					}
				}

				// Uploaded Operator Image
				$upload = isset($_FILES['files']) ? $_FILES['files'] : null;
				if ($upload && is_array($upload['tmp_name'])) {
					// Upload File
					$file = $upload['tmp_name'][0];

					// Validate Image
					list($width, $height) = @getimagesize($file);
					if ($width >= 100 && $width <= 300 && $height >= 100 && $height <= 300) {
						$content = file_get_contents($file);
						$_REQUEST['Image'] = base64_encode($content);
					}
				}

				$result = false;
				if (!empty($hash)) {
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "users(`username`, `firstname`, `lastname`, `password`, `email`, `department`, `device`, `image`, `updated`, `privilege`, `disabled`) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '', '%s', NOW(), '%d', '%d')", $SQL->escape($_REQUEST['User']), $SQL->escape($_REQUEST['Firstname']), $SQL->escape($_REQUEST['Lastname']), $SQL->escape($hash), $SQL->escape($_REQUEST['Email']), $SQL->escape($_REQUEST['Department']), $SQL->escape($_REQUEST['Image']), $_REQUEST['Privilege'], $_REQUEST['Disabled']);
					$result = $SQL->insertquery($query);
				}
				if ($result == false) {
				
					if ($_REQUEST['Format'] == 'xml') {
						header('Content-type: text/xml; charset=utf-8');
						echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
				
?>
<Operators xmlns="urn:LiveHelp" />
<?php
					}
					else {
						header('Content-type: application/json; charset=utf-8');
						$operators = array('Operators' => null);
						$json = json_encode($operators);
						echo($json);
					}
					exit();
				}
			}
		}
		
	}
	
	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
	}
	else {
		header('Content-type: application/json; charset=utf-8');
	}
	
	$query = "SELECT *, NOW() AS `time` FROM " . $_SETTINGS['TABLEPREFIX'] . "users ORDER BY `username`";
	$rows = $SQL->selectall($query);
	
	if (is_array($rows)) {
		if (isset($operators)) {
			$limit = $operators;
			if ($_REQUEST['Format'] == 'xml') {
?>
<Operators xmlns="urn:LiveHelp" Limit="<?php echo($limit) ?>">
<?php
			}
			else {
				$operators = array();
			}
		} else {
			if ($_REQUEST['Format'] == 'xml') {
?>
<Operators xmlns="urn:LiveHelp">
<?php
			}
			else {
				$operators = array();
			}
		}

		$query = "SELECT messages.username, AVG(`rating`) AS `average` FROM `" . $_SETTINGS['TABLEPREFIX'] . "messages` AS messages, `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS chats WHERE messages.chat = chats.id AND `status` = 1 AND `rating` <> 0 GROUP BY messages.username";
		$ratings = $SQL->selectall($query);

		foreach ($rows as $operatorkey => $row) {
			if (is_array($row)) {
				$operator_id = $row['id'];
				$operator_username = $row['username'];
				$operator_firstname = $row['firstname'];
				$operator_lastname = $row['lastname'];
				$operator_email = $row['email'];
				$operator_password = $row['password'];
				$operator_department = $row['department'];
				$operator_device = $row['device'];
				$operator_image = $row['image'];
				$operator_datetime = $row['datetime'];
				$operator_refresh = $row['refresh'];
				$operator_updated = $row['updated'];
				$operator_privilege = $row['privilege'];
				$operator_disabled = $row['disabled'];
				$operator_status = $row['status'];
				$operator_time = $row['time'];
				
				if (substr($operator_password, 0, 3) != '$P$') {
					$length = strlen($operator_password);
					switch ($length) {
						case 40: // SHA1
							$authentication = '2.0';
							break;
						case 128: // SHA512
							$authentication = '3.0';
							break;
						default: // MD5
							$authentication = '1.0';
							break;
					}
				} else {
					$authentication = '4.0';
				}
				
				$refresh = strtotime($operator_refresh);
				$time = strtotime($operator_time);
				if ($time - $refresh > 45 && empty($operator_device)) { $operator_status = 0; }
				
				if (!empty($_REQUEST['Cached'])) { 
					$updated = strtotime($operator_updated);
					$cached = strtotime($_REQUEST['Cached']);
					if ($updated - $cached <= 0) {
						$operator_image = '';
					}
				}
				
				$operator_rating = 'Unavailable';
				if (is_array($ratings)) {
					foreach ($ratings as $key => $rating) {
						if (is_array($rating)) {
							if ($rating['username'] == $operator_username) {
								$operator_rating = $rating['average'];
								break;
							}
						}
					}
				}
				
				if ($_REQUEST['Format'] == 'xml') {
?>
<Operator ID="<?php echo($operator_id); ?>" Updated="<?php echo($operator_updated); ?>" Authentication="<?php echo($authentication); ?>" Device="<?php echo(xmlattribinvalidchars($operator_device)); ?>">
<Username><?php echo(xmlelementinvalidchars($operator_username)); ?></Username>
<Firstname><?php echo(xmlelementinvalidchars($operator_firstname)); ?></Firstname>
<Lastname><?php echo(xmlelementinvalidchars($operator_lastname)); ?></Lastname>
<Email><?php echo(xmlelementinvalidchars($operator_email)); ?></Email>
<Department><?php echo(xmlelementinvalidchars($operator_department)); ?></Department>
<?php if ($operator_image != '') { ?><Image><![CDATA[<?php echo(xmlelementinvalidchars($operator_image)); ?>]]></Image><?php } ?>
<Datetime><?php echo(xmlelementinvalidchars($operator_datetime)); ?></Datetime>
<Refresh><?php echo(xmlelementinvalidchars($operator_refresh)); ?></Refresh>
<Privilege><?php echo($operator_privilege); ?></Privilege>
<Disabled><?php echo($operator_disabled); ?></Disabled>
<Status><?php echo($operator_status); ?></Status>
<Rating><?php echo(xmlelementinvalidchars($operator_rating)); ?></Rating>
</Operator>
<?php
				}
				else {

					// Devices
					$devices = array();
					if ((float)$_SETTINGS['SERVERVERSION'] >= 4.10) {
						$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "devices WHERE `user` = '%d'", $operator_id);
						$rows = $SQL->selectall($query);
						if (is_array($rows)) {
							foreach ($rows as $key => $row) {
								$device = array(
									'ID' => $row['id'],
									'Datetime' => $row['datetime'],
									'Device' => $row['device'],
									'OS' => $row['os'],
									'Token' => $row['token']
								);
								$devices[] = $device;
							}
						}
					}

					$operator = array(
						'ID' => $operator_id,
						'Updated' => $operator_updated,
						'Authentication' => $authentication,
						'Device' => $operator_device,
						'Devices' => $devices,
						'Username' => $operator_username,
						'Firstname' => $operator_firstname,
						'Lastname' => $operator_lastname,
						'Email' => $operator_email,
						'Department' => $operator_department,
						'Datetime' => $operator_datetime,
						'Refresh' => $operator_refresh,
						'Privilege' => $operator_privilege,
						'Disabled' => $operator_disabled,
						'Status' => $operator_status,
						'Rating' => $operator_rating
					);

					// Image
					if (!empty($operator_image)) {
						$operator['Image'] = $operator_image;
					}

					$operators[] = $operator;
				}
			}
		}
		if ($_REQUEST['Format'] == 'xml') {
?>
</Operators>
<?php
		}
		else {
			if (isset($limit)) {
				$json = array('Operators' => array('Limit' => $limit, 'Operator' => $operators));
			} else {
				$json = array('Operators' => array('Operator' => $operators));
			}
			$json = json_encode($json);
			echo($json);
		}
	}
	else {
		if ($_REQUEST['Format'] == 'xml') {
?>
<Operators xmlns="urn:LiveHelp"/>
<?php
		}
		else {
			$operators = array('Operators' => null);
			$json = json_encode($operators);
			echo($json);
		}
	}
}

function Statistics() {

	global $SQL;
	global $_SETTINGS;
	
	if (!isset($_REQUEST['Timezone'])){ $_REQUEST['Timezone'] = $_SETTINGS['SERVERTIMEZONE']; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	
	$hours = 0; $minutes = 0;
	$timezone = $_SETTINGS['SERVERTIMEZONE']; $from = ''; $to = '';
	if ($timezone != $_REQUEST['Timezone']) {
	
		$sign = substr($_REQUEST['Timezone'], 0, 1);
		$hours = substr($_REQUEST['Timezone'], -4, 2);
		$minutes = substr($_REQUEST['Timezone'], -2, 2);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$local = $sign . $hours . $minutes;
	
		$sign = substr($timezone, 0, 1);
		$hours = substr($timezone, 1, 2);
		$minutes = substr($timezone, 3, 4);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$remote = $sign . $hours . $minutes;
	
		// Convert to eg. +/-0430 format
		$hours = substr(sprintf("%04d", $local - $remote), 0, 2);
		$minutes = substr(sprintf("%04d", $local - $remote), 2, 4);
		if ($minutes != 0) { $minutes = ($minutes * 0.6); }
		$difference = ($hours * 60 * 60) + ($minutes * 60);
		
		if ($difference != 0) {
			$from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
			$to = date('Y-m-d H:i:s', mktime(24, 0, 0, date('m'), date('d'), date('Y')));
		}
	}

	if (empty($from) && empty($to)) {
		$from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
		$to = date('Y-m-d H:i:s', mktime(24, 0, 0, date('m'), date('d'), date('Y')));
	}
	
	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Statistics xmlns="urn:LiveHelp">
<?php
	}
	else {
		header('Content-type: application/json; charset=utf-8');
	}

	// Visitors Statistics - 30 days
	$dates = array_pad(array(), 30, 0);
	$data = ''; $start = '';
	
	$interval = $SQL->escape($hours . ':' . $minutes);
	$query = sprintf("SELECT DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)) AS `date`", $interval);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$date = $row['date'];
		$start = date('Y-m-d', mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2) - 30, substr($date, 0, 4)));
	}

	$query = sprintf("SELECT DATE(DATE_ADD(`datetime`, INTERVAL '%s' HOUR_MINUTE)) AS `date`, COUNT(*) AS `total` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE DATE_ADD(`datetime`, INTERVAL '%s' HOUR_MINUTE) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 30 DAY) GROUP BY `date` ORDER BY `date` DESC LIMIT 0, 30", $interval, $interval, $interval);
	$rows = $SQL->selectall($query);
	if (is_array($rows)) {
		$i = 0;
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
			
				$current = $row['date'];
				$time = mktime(0, 0, 0, substr($current, 5, 2), substr($current, 8, 2), substr($current, 0, 4));
				
				$dates[$i] = (int)$row['total'];
				$i++;
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
			$data = implode(', ', $dates);
?>
	<Visitors Date="<?php echo(xmlattribinvalidchars($start)); ?>" Data="<?php echo(xmlattribinvalidchars($data)); ?>"/>
<?php
		} else {
			$data = array('Date' => $start, 'Data' => $dates);
			$visitors = $data;
		}
	}

	$query = sprintf("SELECT DISTINCT chats.id, UNIX_TIMESTAMP(DATE_ADD(`refresh`, INTERVAL '%s' HOUR_MINUTE)) - UNIX_TIMESTAMP(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) AS `duration` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats AS chats JOIN " . $_SETTINGS['TABLEPREFIX'] . "messages AS messages ON (chats.id = messages.chat) WHERE DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 30 DAY) ORDER BY `duration` ASC", $interval, $interval, $interval, $interval);
	$rows = $SQL->selectall($query);
	
	// Duration Statistics - 30 Days
	$duration = array();
	
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {		
				$duration[] = (int)$row['duration'];
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
			$data = implode(', ', $duration);
?>
	<Duration Data="<?php echo(xmlattribinvalidchars($data)); ?>"/>
<?php
		} else {
			$duration = array('Data' => $duration);
		}
	}
	
	// Chat Statistics - 30 days
	$dates = array();
	$data = ''; $start = '';
	
	$query = sprintf("SELECT DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)) AS `date`", $interval);
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
		$date = $row['date'];
		$start = date('Y-m-d', mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2) - 30, substr($date, 0, 4)));
		for ($i = 29; $i >= 0; $i--) {
			$time = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2) - $i, substr($date, 0, 4));
			$dates[date('Y-m-d', $time)] = 0;
		}
	}
	
	$query = sprintf("SELECT DISTINCT DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) AS `date`, COUNT(DISTINCT chats.id) AS `total` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS `chats` JOIN `" . $_SETTINGS['TABLEPREFIX'] . "messages` AS `messages` ON (chats.id = messages.chat) WHERE DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 30 DAY) GROUP BY `date` ORDER BY `date` ASC LIMIT 0, 30", $interval, $interval, $interval);
	$rows = $SQL->selectall($query);
	$i = 0; $total = count($rows);
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
			
				$current = $row['date'];
				$time = mktime(0, 0, 0, substr($current, 5, 2), substr($current, 8, 2), substr($current, 0, 4));
				$date = date('Y-m-d', $time);
				if (isset($dates[$date])) { $dates[$date] = (int)$row['total']; }
				
				$i++;
			}
		}

		if ($_REQUEST['Format'] == 'xml') {
			$data = implode(', ', $dates);
?>
	<Chats Date="<?php echo(xmlattribinvalidchars($start)); ?>" Data="<?php echo(xmlattribinvalidchars($data)); ?>"/>
<?php
		} else {
			$data = array();
			foreach ($dates as $key => $row) {
				$data[] = (int)$row;
			}
			$data = array('Date' => $start, 'Data' => $data);
			$chats = $data;
		}
	}

	$query = sprintf("SELECT DATE_FORMAT(DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)), '%%w') AS `day`, COUNT(DISTINCT DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE))) AS `days` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS `chats` JOIN `" . $_SETTINGS['TABLEPREFIX'] . "messages` AS `messages` ON (chats.id = messages.chat) WHERE DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 365 DAY) GROUP BY `day` ORDER BY DATE_FORMAT(DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)), '%%w') ASC", $interval, $interval, $interval, $interval, $interval);
	$weeks = $SQL->selectall($query);

	$query = sprintf("SELECT DISTINCT DATE_FORMAT(DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)), '%%w') AS `day` , COUNT(DISTINCT chats.id) AS `total` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS `chats` JOIN `" . $_SETTINGS['TABLEPREFIX'] . "messages` AS `messages` ON (chats.id = messages.chat) WHERE DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 365 DAY) GROUP BY `day` ORDER BY DATE_FORMAT(DATE(DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE)), '%%w') ASC LIMIT 0, 30", $interval, $interval, $interval, $interval);
	$rows = $SQL->selectall($query);

	// Chats - Weekday Average
	if (is_array($rows)) {
		$data = array();
		$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		for ($i = 0; $i < 7; $i++) {
			$data[$i] = array('Day' => $days[$i], 'Total' => 0, 'Average' => 0);
		}

		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$day = $row['day'];
				$total = (int)$row['total'];
				$average = 0;
				if (is_array($weeks)) {
					foreach ($weeks as $index => $weekday) {
						if ($day == $weekday['day']) {
							$average = round($total / (int)$weekday['days'], 1);
							break;
						}
					}
				}
				$data[$day] = array('Day' => $days[$day], 'Total' => $total, 'Average' => $average);
			}
		}
		if ($_REQUEST['Format'] == 'json') {
			$chats['Weekday'] = $data;
		}
	}

	$query = sprintf("SELECT `rating`, COUNT(*) AS `total` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` WHERE DATE(`datetime`) > DATE_SUB(DATE(DATE_ADD(NOW(), INTERVAL '%s' HOUR_MINUTE)), INTERVAL 30 DAY) GROUP BY `rating` ORDER BY `rating` ASC", $interval);
	$rows = $SQL->selectall($query);
	
	// Rating Statistics - 30 Days
	$excellent = 0;
	$verygood= 0;
	$good = 0;
	$poor = 0;
	$verypoor = 0;
	$unrated = 0;
	
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
			
				$rating = (int)$row['rating'];
				$total = $row['total'];
				switch($rating) {
					case 5:
						$excellent = (int)$total;
						break;
					case 4:
						$verygood = (int)$total;
						break;
					case 3:
						$good = (int)$total;
						break;
					case 2:
						$poor = (int)$total;
						break;
					case 1:
						$verypoor = (int)$total;
						break;
					default:
						$unrated =(int) $total;
						break;
				}
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {
?>
	<Rating Excellent="<?php echo($excellent); ?>" VeryGood="<?php echo($verygood); ?>" Good="<?php echo($good); ?>" Poor="<?php echo($poor); ?>" VeryPoor="<?php echo($verypoor); ?>" Unrated="<?php echo($unrated); ?>"/>
<?php
		} else {
			$rating = array('Excellent' => $excellent, 'VeryGood' => $verygood, 'Good' => $good, 'Poor' => $poor, 'VeryPoor' => $verypoor, 'Unrated' => $unrated);
		}
	}
	
	if ($_REQUEST['Format'] == 'xml') {
?>
</Statistics>
<?php
	} else {
		$statistics = array('Visitors' => $visitors, 'Chats' => $chats, 'Duration' => $duration, 'Rating' => $rating);
		$json = array('Statistics' => $statistics);
		$json = json_encode($json);
		echo($json);
	}

}

function History() {

	global $_SETTINGS;
	global $_OPERATOR;
	global $SQL;

	if (!isset($_REQUEST['StartDate'])){ $_REQUEST['StartDate'] = ''; }
	if (!isset($_REQUEST['EndDate'])){ $_REQUEST['EndDate'] = ''; }
	if (!isset($_REQUEST['Timezone'])){ $_REQUEST['Timezone'] = ''; }
	if (!isset($_REQUEST['Transcripts'])){ $_REQUEST['Transcripts'] = ''; }
	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Version'])){ $_REQUEST['Version'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }

	if ($_REQUEST['Format'] == 'xml') {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
	} else {
		header('Content-type: application/json; charset=utf-8');
	}
	
	// View History if authorized
	if ($_OPERATOR['PRIVILEGE'] > 2) {
		if ($_REQUEST['Transcripts'] == '') {
			if ($_REQUEST['Format'] == 'xml') {
?>
<VisitorHistory xmlns="urn:LiveHelp"/>
<?php
			} else {
				$history = array('VisitorHistory' => null);
				$json = json_encode($history);
				echo($json);
			}
		exit();
		}
	}

	// Live Help Messenger 2.95 Compatibility
	if (isset($_REQUEST['Date'])) {
		list($from_year, $from_month, $from_day) = explode('-', $_REQUEST['Date']);
		list($to_year, $to_month, $to_day) = explode('-', $_REQUEST['Date']);
	} else {
		list($from_year, $from_month, $from_day) = explode('-', $_REQUEST['StartDate']);
		list($to_year, $to_month, $to_day) = explode('-', $_REQUEST['EndDate']);
	}

	$timezone = $_SETTINGS['SERVERTIMEZONE']; $from = ''; $to = ''; $fromtime = ''; $totime = '';
	if ($timezone != $_REQUEST['Timezone']) {
	
		$sign = substr($_REQUEST['Timezone'], 0, 1);
		$hours = substr($_REQUEST['Timezone'], -4, 2);
		$minutes = substr($_REQUEST['Timezone'], -2, 2);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$local = $sign . $hours . $minutes;
	
		$sign = substr($timezone, 0, 1);
		$hours = substr($timezone, 1, 2);
		$minutes = substr($timezone, 3, 4);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$remote = $sign . $hours . $minutes;
	
		// Convert to eg. +/-0430 format
		$hours = substr(sprintf("%04d", $local - $remote), 0, 2);
		$minutes = substr(sprintf("%04d", $local - $remote), 2, 4);
		if ($minutes != 0) { $minutes = ($minutes * 0.6); }
		$difference = ($hours * 60 * 60) + ($minutes * 60);
		
		if ($difference != 0) {
			$fromtime = mktime(0 - $hours, 0 - $minutes, 0, $from_month, $from_day, $from_year);
			$totime = mktime(0 - $hours, 0 - $minutes, 0, $to_month, $to_day + 1, $to_year);
			$from = date('Y-m-d H:i:s', $fromtime);
			$to = date('Y-m-d H:i:s', $totime);
		}
	}

	if (empty($from) && empty($to)) {
		$fromtime = mktime(0, 0, 0, $from_month, $from_day, $from_year);
		$totime = mktime(24, 0, 0, $to_month, $to_day, $to_year);
		$from = date('Y-m-d H:i:s', $fromtime);
		$to = date('Y-m-d H:i:s', $totime);
	}
	
	if ($_REQUEST['Transcripts'] != '') {
		
		$interval = $hours . ':' . $minutes;
		$query = '';
		if ($timezone != $_REQUEST['Timezone']) {
			if ($difference != 0) {
				$query = sprintf("SELECT DISTINCT chats.id AS chat, chats.request, messages.username AS operator, `firstname`, `lastname`, `ipaddress`, `useragent`, `city`, `state`, `country`, `referrer`, `url`, `path`, DATE_ADD(chats.datetime, INTERVAL '%s' HOUR_MINUTE) AS `datetime`, DATE_ADD(chats.refresh, INTERVAL '%s' HOUR_MINUTE) AS `refresh`, chats.username, chats.department, chats.email, `rating`, `active` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS chats LEFT JOIN `" . $_SETTINGS['TABLEPREFIX'] . "requests` AS requests ON (chats.request = requests.id) LEFT JOIN " . $_SETTINGS['TABLEPREFIX'] . "messages AS messages ON (chats.id = messages.chat) LEFT JOIN `" . $_SETTINGS['TABLEPREFIX'] . "users` AS users ON (messages.username = users.username) WHERE chats.datetime > '%s' AND chats.datetime < '%s' AND (messages.status = '1' OR messages.status = '7') AND chats.id > %d", $SQL->escape($interval), $SQL->escape($interval), $SQL->escape($from), $SQL->escape($to), $_REQUEST['ID']);		
			}
		}
		if ($query == '') {		
			$query = sprintf("SELECT DISTINCT chats.id AS chat, chats.request, messages.username AS operator, `firstname`, `lastname`, `ipaddress`, `useragent`, `city`, `state`, `country`, `referrer`, `url`, `path`, chats.datetime AS `datetime`, chats.refresh AS `refresh`, chats.username, chats.department, chats.email, `rating`, `active` FROM `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS chats LEFT JOIN `" . $_SETTINGS['TABLEPREFIX'] . "requests` AS requests ON (chats.request = requests.id) LEFT JOIN `" . $_SETTINGS['TABLEPREFIX'] . "messages` AS messages ON (chats.id = messages.chat) LEFT JOIN `" . $_SETTINGS['TABLEPREFIX'] . "users` AS users ON (messages.username = users.username) WHERE chats.datetime > '%s' AND chats.datetime < '%s' AND (messages.status = '1' OR messages.status = '7') AND chats.id > %d", $SQL->escape($from), $SQL->escape($to), $_REQUEST['ID']);
		}
		
		// Limit History if not Administrator
		if ($_OPERATOR['PRIVILEGE'] > 2) {
			$query .= sprintf(" AND users.username = '%s'", $SQL->escape($_REQUEST['Username']));
		}
		$query .= ' GROUP BY chats.id ORDER BY chats.datetime';

		if ($_REQUEST['Format'] == 'xml') {
?>
<ChatHistory xmlns="urn:LiveHelp">
<?php
		} else {
			$visitors = array();
		}

		$rows = $SQL->selectall($query);
		if (is_array($rows)) {
			foreach ($rows as $key => $row) {
				if (is_array($row)) {
				
					$id = $row['chat'];
					$request = $row['request'];
					$ipaddress = (!empty($row['ipaddress'])) ? $row['ipaddress'] : 'Unavailable';
					$useragent = (!empty($row['useragent'])) ? $row['useragent'] : 'Unavailable';
					$referer = (!empty($row['referrer'])) ? $row['referrer'] : 'Unavailable';
					$city = $row['city'];
					$state = $row['state'];
					$country = (!empty($row['country'])) ? $row['country'] : 'Unavailable';
					$url =  (!empty($row['url'])) ? $row['url'] : 'Unavailable';
					$path =  (!empty($row['path'])) ? $row['path'] : 'Unavailable';
					$username = $row['username'];
					$operator = (!empty($row['firstname'])) ? $row['firstname'] . ' ' . $row['lastname'] : $row['operator'];
					$department = $row['department'];
					$email = $row['email'];
					$rating = $row['rating'];
					$active = $row['active'];
					$datetime = $row['datetime'];
					$refresh = $row['refresh'];
					
					$custom = '';
					$reference = '';
					
					// Page Path Limit
					$paths = explode('; ', $path);
					$total = count($paths);
					$paths = array_slice($paths, $total - 20);
					$path = implode('; ', $paths);
					
					// Integration
					$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = %d", $request);
					$integration = $SQL->selectquery($query);
					if (is_array($integration)) {
						$custom = $integration['custom'];
						$reference = $integration['reference'];
					}
					
					if ($_REQUEST['Format'] == 'xml') {	
?>
<Visitor ID="<?php echo($request); ?>" Session="<?php echo($id); ?>" Active="<?php echo($active); ?>" Username="<?php echo(xmlattribinvalidchars($username)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Custom="<?php echo(xmlattribinvalidchars($custom)); ?>" Reference="<?php echo(xmlattribinvalidchars($reference)); ?>">
<Date><?php echo(xmlelementinvalidchars($datetime)); ?></Date>
<Refresh><?php echo(xmlelementinvalidchars($refresh)); ?></Refresh>
<Hostname><?php echo(xmlelementinvalidchars($ipaddress)); ?></Hostname>
<UserAgent><?php echo(xmlelementinvalidchars($useragent)); ?></UserAgent>
<CurrentPage><?php echo(xmlelementinvalidchars($url)); ?></CurrentPage>
<SiteTime><?php echo($timezone); ?></SiteTime>
<Referrer><?php echo(xmlelementinvalidchars($referer)); ?></Referrer>
<Country City="<?php echo(xmlattribinvalidchars($city)); ?>" State="<?php echo(xmlattribinvalidchars($state)); ?>"><?php echo(xmlelementinvalidchars($country)); ?></Country>
<PagePath><?php echo(xmlelementinvalidchars($path)); ?></PagePath>
<Operator><?php echo(xmlelementinvalidchars($operator)); ?></Operator>
<Department><?php echo(xmlelementinvalidchars($department)); ?></Department>
<Rating><?php echo(xmlelementinvalidchars($rating)); ?></Rating>
</Visitor>
<?php
					} else {
					
						$visitor = array("ID" => $request, "Session" => $id, "Active" => $active, "Username" => $username, "Email" => $email, "Date" => $datetime, "Refresh" => $refresh, "Hostname" => $ipaddress, "UserAgent" => $useragent, "CurrentPage" => $url, "SiteTime" => $timezone, "Referrer" => $referer, "City" => $city, "State" => $state, "Country" => $country, "PagePath" => $path, "Operator" => $operator, "Department" => $department, "Rating" => $rating);
						$visitors[] = array("Visitor" => $visitor);
						
					}
				}
			}
		}
		
		if ($_REQUEST['Format'] == 'xml') {	
?>
</ChatHistory>
<?php
		} else {

			$json = array("ChatHistory" => $visitors);
			echo(json_encode($json));
		}
	}
	else { // $_REQUEST['Transcripts'] == ''
		$query = '';
		if ($timezone != $_REQUEST['Timezone']) {
			if ($difference != 0) {
				$query = sprintf("SELECT *, DATE_ADD(`datetime`, INTERVAL '%s' HOUR_MINUTE) AS `timezone`, ((UNIX_TIMESTAMP(`refresh`) - UNIX_TIMESTAMP(`datetime`))) AS `sitetime`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`))) AS `pagetime` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `datetime` > '%s' AND `datetime` < '%s' AND `status` = '0' AND `id` > %d ORDER BY `request`", $interval, $SQL->escape($_REQUEST['Username']), $SQL->escape($from), $SQL->escape($to), $_REQUEST['ID']);
			}
		}
		if ($query == '') {		
				$query = sprintf("SELECT *, `datetime` AS `timezone`, ((UNIX_TIMESTAMP(`refresh`) - UNIX_TIMESTAMP(`datetime`))) AS `sitetime`, ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`))) AS `pagetime` FROM " . $_SETTINGS['TABLEPREFIX'] . "requests WHERE `datetime` > '%s' AND `datetime` < '%s' AND `status` = '0' AND `id` > %d ORDER BY `request`", $SQL->escape($from), $SQL->escape($to), $_REQUEST['ID']);
		}
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
?>
<VisitorHistory xmlns="urn:LiveHelp">
<?php
			while ($row) {
				if (is_array($row)) {
					$id = $row['id'];
					$ipaddress = $row['ipaddress'];
					$useragent = $row['useragent'];
					$resolution = $row['resolution'];
					$city = $row['city'];
					$state = $row['state'];
					$country = $row['country'];
					$datetime = $row['timezone'];
					$pagetime = $row['pagetime'];
					$sitetime = $row['sitetime'];
					$url = $row['url'];
					$title = $row['title'];
					$referer = $row['referrer'];
					$path = $row['path'];
					
					$pages = explode('; ', $path);
					$total = count($path);
					if ($total > 20) {
						$path = '';
						for ($i = $total - 20; $i < $total; $i++) {
							$path .= $pages[$i] . '; ';
						}
					}
?>
<Visitor ID="<?php echo($id); ?>">
<Hostname><?php echo(xmlelementinvalidchars($ipaddress)); ?></Hostname>
<UserAgent><?php echo(xmlelementinvalidchars($useragent)); ?></UserAgent>
<Resolution><?php echo(xmlelementinvalidchars($resolution)); ?></Resolution>
<Country City="<?php echo(xmlattribinvalidchars($city)); ?>" State="<?php echo(xmlattribinvalidchars($state)); ?>"><?php echo(xmlelementinvalidchars($country)); ?></Country>
<Date><?php echo(xmlelementinvalidchars($datetime)); ?></Date>
<PageTime><?php echo($pagetime); ?></PageTime>
<SiteTime><?php if (!isset($_REQUEST['Version'])) { echo($datetime); } else { echo($sitetime); } ?></SiteTime>
<CurrentPage><?php echo(xmlelementinvalidchars($url)); ?></CurrentPage>
<CurrentPageTitle><?php echo(xmlelementinvalidchars($title)); ?></CurrentPageTitle>
<Referrer><?php echo(xmlelementinvalidchars($referer)); ?></Referrer>
<PagePath><?php echo(xmlelementinvalidchars($path)); ?></PagePath>
</Visitor>
<?php
					$row = $SQL->selectnext();
				}
			}
?>
</VisitorHistory>
<?php
		}
		else {
?>
<VisitorHistory xmlns="urn:LiveHelp"/>
<?php
		}
	}	
	
}

function Send() {

	global $_OPERATOR;
	global $SQL;
	global $_SETTINGS;
	global $hooks;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Message'])){ $_REQUEST['Message'] = ''; }
	if (!isset($_REQUEST['Staff'])){ $_REQUEST['Staff'] = ''; }
	if (!isset($_REQUEST['Type'])){ $_REQUEST['Type'] = ''; }
	if (!isset($_REQUEST['Name'])){ $_REQUEST['Name'] = ''; }
	if (!isset($_REQUEST['Content'])){ $_REQUEST['Content'] = ''; }
	if (!isset($_REQUEST['Status'])){ $_REQUEST['Status'] = 1; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }
	
	$result = '0';
	
	// Check if the message contains any content else return headers
	if (empty($_REQUEST['Message']) && empty($_REQUEST['Type']) && empty($_REQUEST['Name']) && empty($_REQUEST['Content'])) {
		if ($_REQUEST['Format'] == 'xml') {	
			header('Content-type: text/xml; charset=utf-8');
			echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<SendMessage xmlns="urn:LiveHelp"/>
<?php
			exit();
		} else {
?>
{"SendMessage": null}
<?php
		}
	}
	

	if ($_REQUEST['Type'] != '' && $_REQUEST['Name'] != '' && $_REQUEST['Content'] != '') {
	
		// Strip the slashes because slashes will be added to whole string
		$type = $_REQUEST['Type'];
		$name = stripslashes(trim($_REQUEST['Name']));
		$content = stripslashes(trim($_REQUEST['Content']));
		$operator = '';
		
		switch ($type) {
			case 'LINK':
			case 'HYPERLINK':
				$type = 2;
				$command = addslashes($name . " \r\n " . $content);
				break;
			case 'IMAGE':
				$type = 3;
				$command = addslashes($name . " \r\n " . $content);
				break;
			case 'PUSH':
				$type = 4;
				$command = addslashes($content);
				$operator = addslashes('The ' . $name . ' has been PUSHed to the visitor.');
				break;
			case 'JAVASCRIPT':
				$type = 5;
				$command = addslashes($content);
				$operator = addslashes('The ' . $name . ' has been sent to the visitor.');
				break;
			case 'FILE':
				$type = 6;
				$command = addslashes($content);
				//$operator = addslashes('The ' . $name . ' has been sent to the visitor.');
				break;
		}
		
		if ($command != '') {
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`, `status`) VALUES ('%d', '', NOW(), '%s', '2', '%d')", $SQL->escape($_REQUEST['ID']), $SQL->escape($command), $SQL->escape($type));
			if ($operator != '') {
				$query .= sprintf(", ('%d', '', NOW(), '%s', '2', '-1')", $SQL->escape($_REQUEST['ID']), $SQL->escape($operator));
			}
			$id = $SQL->insertquery($query);
			if ($id != false) {
				$result = '1';
			}
		}
		
	}
	
	// Format the message string
	$message = trim($_REQUEST['Message']);
		
	if (!empty($message)) {
		if (!$_REQUEST['Staff']) {
			// Send Message
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "messages (`chat`, `username`, `datetime`, `message`, `align`, `status`) VALUES('%d', '%s', NOW(), '%s', '1', '%d')", $SQL->escape($_REQUEST['ID']), $SQL->escape($_OPERATOR['USERNAME']), $SQL->escape($_REQUEST['Message']), $SQL->escape($_REQUEST['Status']));
			$id = $SQL->insertquery($query);
			if ($id != false) {
				$result = '1';
			}

			$hooks->run('SendMessage', array('id' => $id, 'chat' => (int)$_REQUEST['ID'], 'username' => $_OPERATOR['USERNAME'], 'message' => $_REQUEST['Message'], 'status' => $_REQUEST['Status']));
		}
		else {
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "administration (`user`, `username`, `datetime`, `message`, `align`, `status`) VALUES('%d', '%s', NOW(), '%s', '1', '%d')", $SQL->escape($_REQUEST['ID']), $SQL->escape($_OPERATOR['USERNAME']), $SQL->escape($_REQUEST['Message']), $SQL->escape($_REQUEST['Status']));
			$id = $SQL->insertquery($query);
			if ($id != false) {
				$result = '1';
			}
		}
	}
	
	if ($_REQUEST['Format'] == 'xml') {	
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<SendMessage xmlns="urn:LiveHelp" Result="<?php echo($result); ?>"></SendMessage>
<?php
	} else {
?>
{"SendMessage": {"Result": <?php echo(json_encode($result)); ?>}}
<?php
	}

}

function EmailChat() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Email'])){ $_REQUEST['Email'] = ''; }

	// Determine EOL
	$server = strtoupper(substr(PHP_OS, 0, 3));
	if ($server == 'WIN') { 
		$eol = "\r\n"; 
	} elseif ($server == 'MAC') { 
		$eol = "\r"; 
	} else { 
		$eol = "\n"; 
	}
	
	// Language
	$language = file_get_contents('../locale/en/admin.json');
	if (file_exists('../locale/' . LANGUAGE . '/admin.json')) {
		$language = file_get_contents('../locale/' . LANGUAGE . '/admin.json');
	}
	$_LOCALE = json_decode($language, true);

	$query = sprintf("SELECT `username`, `message`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND `status` <= '3' OR `status` = '7' ORDER BY `datetime`", $_REQUEST['ID']);
	$row = $SQL->selectquery($query);
	$htmlmessages = ''; $textmessages = '';
	while ($row) {
		if (is_array($row)) {
			$username = $row['username'];
			$message = $row['message'];
			$status = $row['status'];
			
			// Remove HTML code
			$message = str_replace('<', '&lt;', $message);
			$message = str_replace('>', '&gt;', $message);
			
			// Operator
			if ($status) {
				$htmlmessages .= '<div style="color:#666666">' . $username . ' says:</div> <div style="margin-left:15px; color:#666666;">' . $message . '</div>'; 
				$textmessages .= $username . ' ' . $_LOCALE['says'] . ':' . $eol . '	' . $message . $eol; 
			}
			// Guest
			if (!$status) {
				$htmlmessages .= '<div>' . $username . ' says:</div> <div style="margin-left: 15px;">' . $message . '</div>'; 
				$textmessages .= $username . ' ' . $_LOCALE['says'] . ':' . $eol . '	' . $message . $eol; 
			}
	
			$row = $SQL->selectnext();
		}
	}

	$htmlmessages = preg_replace("/(\r\n|\r|\n)/", '<br/>', $htmlmessages);
	
	$html = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<!--

div, p {
	font-family: Calibri, Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #000000;
}

//-->
</style>
</head>

<body>
<p><img src="{$_SETTINGS['CHATTRANSCRIPTHEADERIMAGE']}" alt="{$_LOCALE['chattranscript']}" /></p>
<p><strong>{$_LOCALE['chattranscript']}:</strong></p>
<p>$htmlmessages</p>
<p><img src="{$_SETTINGS['CHATTRANSCRIPTFOOTERIMAGE']}" alt="{$_SETTINGS['NAME']}" /></p>
</body>
</html>
END;
	
	$email = $_SETTINGS['EMAIL'];
	if (!empty($_REQUEST['Email'])) {
		$email = $_REQUEST['Email'];
	}

	$mail = new PHPMailer(true);
	try {
		$mail->CharSet = 'UTF-8';
		$mail->AddReplyTo($_SETTINGS['EMAIL'], $_SETTINGS['NAME']);
		$mail->AddAddress($email);
		$mail->SetFrom($_SETTINGS['EMAIL']);
		$mail->Subject = $_SETTINGS['NAME'] . ' ' . $_LOCALE['chattranscript'];
		$mail->MsgHTML($html);
		$mail->Send();
		$result = true;
	} catch (phpmailerException $e) {
		trigger_error('Email Error: ' . $e->errorMessage(), E_USER_ERROR); 
		$result = false;
	} catch (Exception $e) {
		trigger_error('Email Error: ' . $e->getMessage(), E_USER_ERROR); 
		$result = false;
	}

}

function Calls() {

	global $SQL;
	global $_SETTINGS;
	
	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Operator'])){ $_REQUEST['Operator'] = ''; }
	if (!isset($_REQUEST['Status'])){ $_REQUEST['Status'] = ''; }
	
	if ($_REQUEST['ID'] != '' && $_REQUEST['Status'] != '') {
			$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "callback SET `operator` = '%d', `status` = '%d' WHERE `id` = '%d'", $_REQUEST['Operator'], $_REQUEST['Status'], $_REQUEST['ID']);
			$SQL->updatequery($query);
	}
	

	$query = "SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "callback WHERE `status` <> 5 ORDER BY `datetime`";
	$row = $SQL->selectquery($query);
	if (is_array($row)) {
	
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Calls xmlns="urn:LiveHelp" IPAddress="<?php echo(ip_address()); ?>">
<?php
		while ($row) {
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$datetime = $row['datetime'];
				$email = $row['email'];
				$country = $row['country'];
				$timezone = $row['timezone'];
				$dial = $row['dial'];
				$telephone = $row['telephone'];
				$message = $row['message'];
				$operator = $row['operator'];
				$status = $row['status'];
?>
<Call ID="<?php echo($id); ?>" Name="<?php echo(xmlattribinvalidchars($name)); ?>" Email="<?php echo(xmlattribinvalidchars($email)); ?>" Operator="<?php echo(xmlattribinvalidchars($operator)); ?>" Status="<?php echo(xmlattribinvalidchars($status)); ?>">
<Datetime><?php echo($datetime); ?></Datetime>
<Country><?php echo(xmlelementinvalidchars($country)); ?></Country>
<Timezone><?php echo(xmlelementinvalidchars($timezone)); ?></Timezone>
<Telephone Prefix="<?php echo(xmlattribinvalidchars($dial)); ?>"><?php echo(xmlelementinvalidchars($telephone)); ?></Telephone>
<Message><?php echo(xmlelementinvalidchars($message)); ?></Message>
</Call>
<?php
		
				$row = $SQL->selectnext();
			}
		}	
?>
</Calls>
<?php
	} else {
		header('Content-type: text/xml; charset=utf-8');
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Calls xmlns="urn:LiveHelp"/>
<?php
	}
	
	
}

function Responses() {

	global $SQL;
	global $_RESPONSES;
	global $_PLUGINS;
	global $_SETTINGS;
	global $hooks;
	
	if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
	if (!isset($_REQUEST['Operator'])){ $_REQUEST['Operator'] = ''; }
	if (!isset($_REQUEST['Department'])){ $_REQUEST['Department'] = ''; }
	if (!isset($_REQUEST['ResponsesArray'])){ $_REQUEST['ResponsesArray'] = ''; }
	if (!isset($_REQUEST['Name'])){ $_REQUEST['Name'] = ''; }
	if (!isset($_REQUEST['Category'])){ $_REQUEST['Category'] = ''; }
	if (!isset($_REQUEST['Content'])){ $_REQUEST['Content'] = ''; }
	if (!isset($_REQUEST['Type'])){ $_REQUEST['Type'] = ''; }
	if (!isset($_REQUEST['Tags'])){ $_REQUEST['Tags'] = ''; }
	if (!isset($_REQUEST['Cached'])){ $_REQUEST['Cached'] = ''; }
	if (!isset($_REQUEST['Format'])){ $_REQUEST['Format'] = 'xml'; }

	if ($_REQUEST['ResponsesArray'] != '') {
		$lines = preg_split("/(\r\n|\r|\n)/", trim($_REQUEST['ResponsesArray']));

		// Add Responses
		foreach ($lines as $key => $line) {

			$id = ''; $name = ''; $category = ''; $content = ''; $type = ''; $tags = '';
			list($id, $name, $category, $content, $type, $tags) = explode('|', $line);
			
			// Add / Update Response
			if (!empty($name) && !empty($content)) {
				if (!empty($id)) {
					$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "responses WHERE `id` = '%d' LIMIT 1", $id);
					$row = $SQL->selectquery($query);
					if (is_array($row)) {
						$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "responses SET `name` = '%s', `category` = '%s', `type` = '%d', `content` = '%s', `tags` = '%s', `datetime` = NOW() WHERE `id` = '%d'", $SQL->escape($name), $SQL->escape($category), $type, $SQL->escape($content), $SQL->escape($tags), $id);
						$result = $SQL->updatequery($query);
					}
				}
				else {
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "responses(`name`, `datetime`, `category`, `type`, `content`, `tags`) VALUES('%s', NOW(), '%s', '%d', '%s', '%s')", $SQL->escape($name), $SQL->escape($category), $type, $SQL->escape($content), $SQL->escape($tags));
					$result = $SQL->insertquery($query);
				}
			}
			
		}
	} else if (!empty($_REQUEST['Name']) && !empty($_REQUEST['Content']) && !empty($_REQUEST['Type'])) {
		$id = $_REQUEST['ID'];
		$name = $_REQUEST['Name'];
		$category = $_REQUEST['Category'];
		$content = $_REQUEST['Content'];
		$type = $_REQUEST['Type'];
		$tags = $_REQUEST['Tags'];

		// Add / Update Response
		if (!empty($id)) {
			$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "responses WHERE `id` = '%d' LIMIT 1", $id);
			$row = $SQL->selectquery($query);
			if (is_array($row)) {
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "responses SET `name` = '%s', `category` = '%s', `type` = '%d', `content` = '%s', `tags` = '%s', `datetime` = NOW() WHERE `id` = '%d'", $SQL->escape($name), $SQL->escape($category), $type, $SQL->escape($content), $SQL->escape($tags), $id);
				$result = $SQL->updatequery($query);
			}
		}
		else {
			$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "responses(`name`, `datetime`, `category`, `type`, `content`, `tags`) VALUES('%s', NOW(), '%s', '%d', '%s', '%s')", $SQL->escape($name), $SQL->escape($category), $type, $SQL->escape($content), $SQL->escape($tags));
			$result = $SQL->insertquery($query);
		}
	}
	
	if (!empty($_REQUEST['ID']) && empty($_REQUEST['Name']) && empty($_REQUEST['Content']) && empty($_REQUEST['Type'])) {
		$id = $_REQUEST['ID'];
		$query = sprintf("DELETE FROM " . $_SETTINGS['TABLEPREFIX'] . "responses WHERE `id` = '%d' LIMIT 1", $id);
		$SQL->deletequery($query);
	}
	
	$query = "SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "responses ORDER BY `type` , `category`";
	if ($_REQUEST['Cached'] != '') {
		$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "responses WHERE `datetime` > '%s' ORDER BY `type` , `category`", $SQL->escape($_REQUEST['Cached']));
	}
	$rows = $SQL->selectall($query);

	if ($_REQUEST['Format'] == 'json') {
		header('Content-type: application/json; charset=utf-8');

		$json = array();
		$text = array();
		$hyperlink = array();
		$image = array();
		$push = array();
		$javascript = array();
		$other = array();
		$lastupdated = '';
		
		if ($rows != false && count($rows) > 0) {
		
			foreach($rows as $key => $row) {
			
				$id = $row['id'];
				$name = $row['name'];
				$datetime = $row['datetime'];
				$content = $row['content'];
				$category = $row['category'];
				$type = (int)$row['type'];
				$tags = $row['tags'];
				if ($tags != '') {
					$tags = explode(';', $tags);
				} else {
					$tags = array();
				}
				
				// Last Updated
				if ($datetime == '') { $lastupdated = $datetime; }
				if (strtotime($datetime) - strtotime($lastupdated) > 0) {
					$lastupdated = $datetime;
				}
				
				switch($type) {
					case '1': // Text
						$text[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $category, 'Type' => $type, 'Tags' => $tags);
						break;
					case '2': // Hyperlink
						$hyperlink[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $category, 'Type' => $type, 'Tags' => $tags);
						break;
					case '3': // Image
						$image[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $category, 'Type' => $type, 'Tags' => $tags);
						break;
					case '4': // PUSH
						$push[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $category, 'Type' => $type, 'Tags' => $tags);
						break;
					case '5': // JavaScript
						$javascript[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $category, 'Type' => $type, 'Tags' => $tags);
						break;
				}
			}
			
			// Custom Responses Hook
			$other = $hooks->run('ResponsesCustom', 'json');
			if (!is_array($other)) {
				$other = '';
			}

			// Responses JSON
			$json['Responses'] = array('LastUpdated' => $lastupdated,'Text' => $text, 'Hyperlink' => $hyperlink, 'Image' => $image, 'PUSH' => $push, 'JavaScript' => $javascript, 'Other' => $other);
			
			echo(json_encode($json));
			exit();
			
		} else {
		
			if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 304 Not Modified'); } else { header('Status: 304 Not Modified'); }
			exit();
		}
		
	}
	
	$text = array();
	$hyperlink = array();
	$image = array();
	$push = array();
	$javascript = array();
	if ($rows != false && count($rows) > 0) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$type = $row['type'];
				switch($type) {
					case '1': // Text
						$text[] = $row;
						break;
					case '2': // Hyperlink
						$hyperlink[] = $row;
						break;
					case '3': // Image
						$image[] = $row;
						break;
					case '4': // PUSH
						$push[] = $row;
						break;
					case '5': // JavaScript
						$javascript[] = $row;
						break;
				}
			}
		}
	}
	
	header('Content-type: text/xml; charset=utf-8');
	echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");

?>
<Responses xmlns="urn:LiveHelp">
  <Text>
<?php

	if (is_array($text)) {
		while (count($text) > 0) {
			$row = $text[count($text) - 1];
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$content = $row['content'];
				$category = $row['category'];
				$type = $row['type'];

				if ($category != '') {
?>
	<Category Name="<?php echo(xmlattribinvalidchars($category)); ?>">
<?php
					for ($i = count($text) - 1; $i >= 0; $i--) {
						$row = $text[$i];
						if ($row['category'] == $category) {
							$id = $row['id'];
							$name = $row['name'];
							$content = $row['content'];
							$type = $row['type'];
?>
		<Response ID="<?php echo($id); ?>">
		  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
		  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
		  <Tags>
<?php
							$tags = explode(';', $row['tags']);
							if (count($tags) > 0) {
								foreach ($tags as $key => $tag) {
?>
			<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
								}
							}
?>
		  </Tags>
		</Response>
<?php
							array_splice($text, $i, 1);
						}
					}
?>
	</Category>
<?php
				} else {
?>
	<Response ID="<?php echo($id); ?>">
	  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
	  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
	  <Tags>
<?php
						$tags = explode(';', $row['tags']);
						if (count($tags) > 0) {
							foreach($tags as $key => $tag) {
?>
		<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
							}
						}
?>
	  </Tags>
	</Response>
<?php
					$popped = array_pop($text);
				}
			} else {
				$popped = array_pop($text);
			}
		}
	}
?>
  </Text>
  <Hyperlink>
<?php
	if (is_array($hyperlink)) {
		while (count($hyperlink) > 0) {
			$row = $hyperlink[count($hyperlink) - 1];
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$content = $row['content'];
				$category = $row['category'];
				$type = $row['type'];
				
				if ($category != '') {
?>
	<Category Name="<?php echo(xmlattribinvalidchars($category)); ?>">
<?php
					for($i = count($hyperlink) - 1; $i >= 0; $i--) {
						$row = $hyperlink[$i];
						if ($row['category'] == $category) {
							$id = $row['id'];
							$name = $row['name'];
							$content = $row['content'];
							$type = $row['type'];
?>
		<Response ID="<?php echo($id); ?>">
		  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
		  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
		  <Tags>
<?php
							$tags = explode(';', $row['tags']);
							if (count($tags) > 0) {
								foreach($tags as $key => $tag) {
?>
			<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
								}
							}
?>
		  </Tags>
		</Response>
<?php
							array_splice($hyperlink, $i, 1);
						}
					}
?>
	</Category>
<?php
				} else {
?>
	<Response ID="<?php echo($id); ?>">
	  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
	  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
	  <Tags>
<?php
					$tags = explode(';', $row['tags']);
					if (count($tags) > 0) {
						foreach($tags as $key => $tag) {
?>
		<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
						}
					}
?>
	  </Tags>
	</Response>
<?php
					$popped = array_pop($hyperlink);
				}
			} else {
				$popped = array_pop($hyperlink);
			}
		}
	}
?>
  </Hyperlink>
  <Image>
<?php
	if (is_array($image)) {
		while (count($image) > 0) {
			$row = $image[count($image) - 1];
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$content = $row['content'];
				$category = $row['category'];
				$type = $row['type'];
				
				if ($category != '') {
?>
	<Category Name="<?php echo(xmlattribinvalidchars($category)); ?>">
<?php
					for($i = count($image) - 1; $i >= 0; $i--) {
						$row = $image[$i];
						if ($row['category'] == $category) {
							$id = $row['id'];
							$name = $row['name'];
							$content = $row['content'];
							$type = $row['type'];
?>
		<Response ID="<?php echo($id); ?>">
		  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
		  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
		  <Tags>
<?php
							$tags = explode(';', $row['tags']);
							if (count($tags) > 0) {
								foreach ($tags as $key => $tag) {
?>
			<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
								}
							}
?>
		  </Tags>
		</Response>
<?php
							array_splice($image, $i, 1);
						}
					}
?>
	</Category>
<?php
				} else {
?>
	<Response ID="<?php echo($id); ?>">
	  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
	  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
	  <Tags>
<?php
					$tags = explode(';', $row['tags']);
					if (count($tags) > 0) {
						foreach($tags as $key => $tag) {
?>
		<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
						}
					}
?>
	  </Tags>
	</Response>
<?php
					$popped = array_pop($image);
				}
			} else {
				$popped = array_pop($image);
			}
		}
	}
?>
  </Image>
  <PUSH>
<?php
	if (is_array($push)) {
		while (count($push) > 0) {
			$row = $push[count($push) - 1];
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$content = $row['content'];
				$category = $row['category'];
				$type = $row['type'];
				
				if ($category != '') {
?>
	<Category Name="<?php echo(xmlattribinvalidchars($category)); ?>">
<?php
					for($i = count($push) - 1; $i >= 0; $i--) {
						$row = $push[$i];
						if ($row['category'] == $category) {
							$id = $row['id'];
							$name = $row['name'];
							$content = $row['content'];
							$type = $row['type'];
?>
		<Response ID="<?php echo($id); ?>">
		  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
		  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
		  <Tags>
<?php
							$tags = explode(';', $row['tags']);
							if (count($tags) > 0) {
								foreach($tags as $key => $tag) {
?>
			<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
								}
							}
?>
		  </Tags>
		</Response>
<?php
							array_splice($push, $i, 1);
						}
					}
?>
	</Category>
<?php
				} else {
?>
	<Response ID="<?php echo($id); ?>">
	  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
	  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
	  <Tags>
<?php
					$tags = explode(';', $row['tags']);
					if (count($tags) > 0) {
						foreach ($tags as $key => $tag) {
?>
		<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
						}
					}
?>
	  </Tags>
	</Response>
<?php
					$popped = array_pop($push);
				}
			} else {
				$popped = array_pop($push);
			}
		}
	}
?>
  </PUSH>
  <JavaScript>
<?php
	if (is_array($javascript)) {
		while (count($javascript) > 0) {
			$row = $javascript[count($javascript) - 1];
			if (is_array($row)) {
				$id = $row['id'];
				$name = $row['name'];
				$content = $row['content'];
				$category = $row['category'];
				$type = $row['type'];
				
				if ($category != '') {
?>
	<Category Name="<?php echo(xmlattribinvalidchars($category)); ?>">
<?php
					for($i = count($javascript) - 1; $i >= 0; $i--) {
						$row = $javascript[$i];
						if ($row['category'] == $category) {
							$id = $row['id'];
							$name = $row['name'];
							$content = $row['content'];
							$type = $row['type'];
?>
		<Response ID="<?php echo($id); ?>">
		  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
		  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
		  <Tags>
<?php
							$tags = explode(';', $row['tags']);
							if (count($tags) > 0) {
								foreach($tags as $key => $tag) {
?>
			<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
								}
							}
?>
		  </Tags>
		</Response>
<?php
							array_splice($javascript, $i, 1);
						}
					}
?>
	</Category>
<?php
				} else {
?>
	<Response ID="<?php echo($id); ?>">
	  <Name><?php echo(xmlelementinvalidchars($name)); ?></Name>
	  <Content><?php echo(xmlelementinvalidchars($content)); ?></Content>
	  <Tags>
<?php
					$tags = explode(';', $row['tags']);
					if (count($tags) > 0) {
						foreach($tags as $key => $tag) {
?>
		<Tag><?php echo(xmlelementinvalidchars($tag)); ?></Tag>
<?php
						}
					}
?>
	  </Tags>
	</Response>
<?php
					$popped = array_pop($javascript);
				}
			} else {
				$popped = array_pop($javascript);
			}
		}
	}
?>
  </JavaScript>
  <Other>
<?php
	if (isset($_RESPONSES) && is_array($_RESPONSES)) {
		foreach ($_RESPONSES as $key => $response) {
			
			// Output Knowledge Base Responses
			$custom = @file_get_contents($response);
			if ($custom !== false) {
				$custom = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $custom);
				if (!empty($custom)) {
					echo($custom);
				}
			}
		}
	}
	
	// Custom Responses Hook
	$hooks->run('ResponsesCustom', 'xml');
?>
  </Other>
</Responses>
<?php

}

function ResetPassword() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['Username'])){ $_REQUEST['Username'] = ''; }
	if (!isset($_REQUEST['Email'])){ $_REQUEST['Email'] = ''; }

	header('Content-type: text/xml; charset=utf-8');

	$language = file_get_contents('../locale/en/admin.json');
	if (file_exists('../locale/' . LANGUAGE . '/admin.json')) {
		$language = file_get_contents('../locale/' . LANGUAGE . '/admin.json');
	}
	$_LOCALE = json_decode($language, true);

	$password = '';
	$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	for ($index = 1; $index <= 10; $index++) {
		$number = rand(1, strlen($chars));
		$password .= substr($chars, $number - 1, 1);
	}
	
	// Change Password
	if (function_exists('hash') && in_array('sha512', hash_algos())) {
		$hash = hash('sha512', $password);
	} else {
		$hash = sha1($password);
	}
	
	// Reset Password
	$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `password` = '%s' WHERE `username` LIKE BINARY '%s' AND `email` = '%s'", $hash, $SQL->escape($_REQUEST['Username']), $SQL->escape($_REQUEST['Email']));
	$result = $SQL->updatequery($query);
	
	// Server
	$protocols = array('http://', 'https://');
	$server = str_replace($protocols, '', $_SETTINGS['URL']);
	
	if ($result !== false) {
		
		$html = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<!--
div, p {
	font-family: Calibri, Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #000000;
}
//-->
</style>
</head>

<body>
<div><img src="{$_SETTINGS['PASSWORDRESETHEADERIMAGE']}" alt="Password Reset" /></div>
<div></div><br/>
<div>{$_LOCALE['server']}: $server</div>
<div>{$_LOCALE['username']}: {$_REQUEST['Username']}</div>
<div>{$_LOCALE['password']}: $password</div><br/>
<div><img src="{$_SETTINGS['PASSWORDRESETFOOTERIMAGE']}" alt="{$_SETTINGS['NAME']}" /></div>
</body>
</html>
END;
		
		$mail = new PHPMailer(true);
		try {
			$mail->CharSet = 'UTF-8';
			$mail->AddReplyTo($_SETTINGS['EMAIL']);
			$mail->AddAddress($_REQUEST['Email']);
			$mail->SetFrom($_SETTINGS['EMAIL'], $_SETTINGS['NAME']);
			$mail->Subject = $_SETTINGS['NAME'] . ' ' . $_LOCALE['resetpassword'];
			$mail->MsgHTML($html);
			$mail->Send();
			$result = true;
		} catch (phpmailerException $e) {
			trigger_error('Email Error: ' . $e->errorMessage(), E_USER_ERROR); 
			$result = false;
		} catch (Exception $e) {
			trigger_error('Email Error: ' . $e->getMessage(), E_USER_ERROR); 
			$result = false;
		}
		
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<ResetPassword xmlns="urn:LiveHelp" Value="<?php echo($result); ?>"></ResetPassword>
<?php
		
	}
	else {
		if (strpos(php_sapi_name(), 'cgi') === false) { header('HTTP/1.0 403 Forbidden'); } else { header('Status: 403 Forbidden'); }	
	}

}

function Activity() {

	global $_OPERATOR;
	global $_SETTINGS;
	global $SQL;

	if (!isset($_REQUEST['Record'])){ $_REQUEST['Record'] = '0'; }
	if (!isset($_REQUEST['Total'])){ $_REQUEST['Total'] = '500'; }
	if (!isset($_REQUEST['Timezone'])){ $_REQUEST['Timezone'] = ''; }

	$timezone = $_SETTINGS['SERVERTIMEZONE']; $from = ''; $to = '';
	if ($timezone != $_REQUEST['Timezone']) {
	
		$sign = substr($_REQUEST['Timezone'], 0, 1);
		$hours = substr($_REQUEST['Timezone'], -4, 2);
		$minutes = substr($_REQUEST['Timezone'], -2, 2);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$local = $sign . $hours . $minutes;
	
		$sign = substr($timezone, 0, 1);
		$hours = substr($timezone, 1, 2);
		$minutes = substr($timezone, 3, 4);
		if ($minutes != 0) { $minutes = ($minutes / 0.6); }
		$remote = $sign . $hours . $minutes;
	
		// Convert to eg. +/-0430 format
		$hours = substr(sprintf("%04d", $local - $remote), 0, 2);
		$minutes = substr(sprintf("%04d", $local - $remote), 2, 4);
		if ($minutes != 0) { $minutes = ($minutes * 0.6); }
		$difference = ($hours * 60 * 60) + ($minutes * 60);

	}

	header('Content-type: text/xml; charset=utf-8');
	
	$interval = $hours . ':' . $minutes;
	$query = '';
	if ($timezone != $_REQUEST['Timezone']) {
		if ($difference != 0) {
			$query = sprintf("SELECT `id`, `user`, `chat`, `username`, DATE_ADD(`datetime`, INTERVAL '%s' HOUR_MINUTE) AS `datetime`, `activity`, `duration`, `type`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "activity WHERE (`user` <> %d OR `status` = 0) AND `id` > %d ORDER BY `id` DESC LIMIT %d", $SQL->escape($interval), $_OPERATOR['ID'], $_REQUEST['Record'], $_REQUEST['Total']);
		}
	}
	if (empty($query)) {
		$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "activity WHERE (`user` <> %d OR `status` = 0) AND `id` > %d ORDER BY `id` DESC LIMIT %d", $_OPERATOR['ID'], $_REQUEST['Record'], $_REQUEST['Total']);
	}

	if (isset($_REQUEST['Update'])) {
		if ($timezone != $_REQUEST['Timezone']) {
			if ($difference != 0) {
				$query = sprintf("SELECT `id`, `user`, `chat`, `username`, DATE_ADD(`datetime`, INTERVAL '%s' HOUR_MINUTE) AS `datetime`, `activity`, `duration`, `type`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "activity WHERE (`user` <> %d OR `status` = 0) AND `id` < %d ORDER BY `id` DESC LIMIT %d", $SQL->escape($interval), $_OPERATOR['ID'], $_REQUEST['Update'], $_REQUEST['Total']);
			}
		}
		if (empty($query)) {
			$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "activity WHERE (`user` <> %d OR `status` = 0) AND `id` < %d ORDER BY `id` DESC LIMIT %d", $_OPERATOR['ID'], $_REQUEST['Update'], $_REQUEST['Total']);
		}
	}
	$rows = $SQL->selectall($query);
	
	echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<Activity xmlns="urn:LiveHelp">
<?php
	if (is_array($rows)) {
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$id = $row['id'];
				$user = $row['user'];
				$chat = $row['chat'];
				$username = xmlattribinvalidchars($row['username']);
				$datetime = xmlattribinvalidchars($row['datetime']);
				$activity = xmlelementinvalidchars($row['activity']);
				$duration = $row['duration'];
				$type = $row['type'];
				$status = $row['status'];
				
				// User
				// Visitor or Operator ID
				// See Status for ID type
				
				// Activity Type
				// 1: Signed In
				// 2: Signed Out
				// 3: Changed Status Hidden
				// 4: Changed Status Online
				// 5: Changed Status Be Right Back
				// 6: Changed Status Away
				// 7: Accepted Chat
				// 8: Requested Live Help
				// 9: Closed Chat
				
				// Status
				// 0: Visitor / Guest
				// 1: Operator
				
				// Accepted / Chat Closed
				if ($type == 7 || $type == 9) {
					
					if ($type == 7) {
						$query = sprintf("SELECT `request`, `active`, `email` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d LIMIT 1", $chat);
					} else {
						$query = sprintf("SELECT `request`, `active`, `email` FROM " . $_SETTINGS['TABLEPREFIX'] . "chats WHERE `id` = %d LIMIT 1", $user);
					}
					$row = $SQL->selectquery($query);
					if (is_array($row)) {
						$request = $row['request'];
						$active = $row['active'];
						$email = xmlattribinvalidchars($row['email']);
						
						$custom = '';
						$reference = '';
						
						// Visitor Session
						$query = sprintf("SELECT `id` FROM `" . $_SETTINGS['TABLEPREFIX'] . "requests` AS `requests` WHERE `id` = '%d' LIMIT 1", $request);
						$row = $SQL->selectquery($query);
						if (is_array($row)) {
							$request = $row['id'];
							
							// Integration
							$query = sprintf("SELECT * FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = '%d'", $request);
							$integration = $SQL->selectquery($query);
							if (is_array($integration)) {
								$custom = $integration['custom'];
								$reference = $integration['reference'];
							}
							
						}
						
						// Accepted Chat
						if ($type == 7) {
							$query = sprintf("SELECT `firstname`, `lastname` FROM " . $_SETTINGS['TABLEPREFIX'] . "users WHERE `id` = %d LIMIT 1", $user);
							$row = $SQL->selectquery($query);
							if (is_array($row)) {
								if (!empty($row['lastname'])) {
									$operator = $row['firstname'] . ' ' . $row['lastname'];
								} else {
									$operator = $row['firstname'];
								}
?>
<Item ID="<?php echo($id); ?>" User="<?php echo($user); ?>" Session="<?php echo($chat); ?>" Request="<?php echo($request); ?>" Active="<?php echo($active); ?>" Operator="<?php echo($operator); ?>" Username="<?php echo($username); ?>" Datetime="<?php echo($datetime); ?>" Email="<?php echo($email); ?>" Type="<?php echo($type); ?>" Status="<?php echo($status); ?>" Duration="<?php echo($duration); ?>" Custom="<?php echo($custom); ?>" Reference="<?php echo($reference); ?>"><?php echo($activity); ?></Item>
<?php
							}
						} else {
?>
<Item ID="<?php echo($id); ?>" User="<?php echo($user); ?>" Session="<?php echo($chat); ?>" Request="<?php echo($request); ?>" Active="<?php echo($active); ?>" Username="<?php echo($username); ?>" Datetime="<?php echo($datetime); ?>" Email="<?php echo($email); ?>" Type="<?php echo($type); ?>" Status="<?php echo($status); ?>" Duration="<?php echo($duration); ?>" Custom="<?php echo($custom); ?>" Reference="<?php echo($reference); ?>"><?php echo($activity); ?></Item>
<?php
						}
						continue;
					}
				}
?>
<Item ID="<?php echo($id); ?>" User="<?php echo($user); ?>" Username="<?php echo($username); ?>" Datetime="<?php echo($datetime); ?>" Type="<?php echo($type); ?>" Status="<?php echo($status); ?>"><?php echo($activity); ?></Item>
<?php
			}
		}
	}

?>
</Activity>
<?php
	
}

?>

