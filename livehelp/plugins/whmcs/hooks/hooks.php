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

if (isset($_PLUGINS) && $_PLUGINS['WHMCS'] == true) {
	require(dirname(__FILE__) . '/../functions.php');
}

/*
 * Hook class name must end with Hooks
 * i.e. ExampleHooks or TestHooks
 *
 */
class WHMCSHooks {

	function WHMCSHooks() {
		// Init Hook
	}

	function CloseChat($args) {

		global $SQL;
		global $_SETTINGS;

		// Arguments
		list($chat, $name) = $args;

		if (isset($_SETTINGS['WHMCSTICKETS']) && $_SETTINGS['WHMCSTICKETS'] == false) {
			return false;
		}

		// Close Chat Event
		$query = sprintf("SELECT `custom` FROM `" . $_SETTINGS['TABLEPREFIX'] . "custom` AS custom, `" . $_SETTINGS['TABLEPREFIX'] . "chats` AS chats WHERE custom.request = chats.request AND chats.id = %d ORDER BY custom.id LIMIT 1", $chat);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$session = $row['custom'];
			
			// Log Chat Ticket
			$seeds = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$c = null;
			$seeds_count = strlen($seeds) - 1;
			for ($i = 0; 8 > $i; $i++) {
				$c .= $seeds[rand(0, $seeds_count)];
			}
			
			// Department
			$query = "SELECT `id` FROM `tblticketdepartments` WHERE `hidden` = '' ORDER BY `order` ASC LIMIT 1";
			$row = $SQL->selectquery($query);
			if (is_array($row)) {
				$department = $row['id'];
				
				// Chat Transcript
				$query = sprintf("SELECT `username`, `message`, `status` FROM " . $_SETTINGS['TABLEPREFIX'] . "messages WHERE `chat` = %d AND `status` <= '3' ORDER BY `datetime`", $chat);
				$rows = $SQL->selectall($query);
				$transcript = ''; $textmessages = '';
				$names = array();
				
				// Determine EOL
				$server = strtoupper(substr(PHP_OS, 0, 3));
				if ($server == 'WIN') { 
					$eol = "\r\n"; 
				} elseif ($server == 'MAC') { 
					$eol = "\r"; 
				} else { 
					$eol = "\n"; 
				}
				
				$transcript .= '[div="chat"]';
				foreach ($rows as $key => $row) {
					if (is_array($row)) {
						$username = $row['username'];
						$message = $row['message'];
						$status = $row['status'];

						// Operator
						if ($status) {

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

							$transcript .= '[div="operator"][div="name"]' . $username . ' says:[/div][div="message"]' . $message . '[/div][/div]';  
							$textmessages .= $username . ' ' . $_LOCALE['says'] . ':' . $eol . '	' . $message . $eol; 
						}
						// Guest
						if (!$status) {
						
							// Replace HTML Code
							$message = str_replace('<', '&lt;', $message);
							$message = str_replace('>', '&gt;', $message);
						
							$transcript .= '[div="visitor"][div="name"]' . $username . ' says:[/div][div="message"]' . $message . '[/div][/div]';  
							$textmessages .= $username . ' ' . $_LOCALE['says'] . ':' . $eol . '	' . $message . $eol; 
						}
					}
				}
				$transcript .= '[/div]';
				$transcript = preg_replace("/(\r\n|\r|\n)/", '<br/>', $transcript);
				
				// Insert Live Help Chat
				$query = sprintf("INSERT INTO tbltickets(`did`, `userid`, `c`, `date`, `title`, `message`, `status`, `urgency`, `lastreply`) VALUES ('%s', '%s', '%s', NOW(), 'Chat Log %s', '%s', 'Closed', 'Medium', NOW())", $SQL->escape($department), $SQL->escape($session), $SQL->escape($c), $SQL->escape(date('d/m/Y H:i')), $SQL->escape($transcript));
				$id = $SQL->insertquery($query);

				// WHMCS Ticket Masking
				$mask = genTicketMask($id);

				// Update Mask Ticket ID
				$query = sprintf("UPDATE `tbltickets` SET `tid` = '%s' WHERE `id` = %d", $SQL->escape($mask), $SQL->escape($id));
				$SQL->updatequery($query);

			}
		}
	}


	function ResponsesCustom($format) {

		global $SQL;

		// KB URL
		$whmcs = whmcsURL(false);
		$kburl = $whmcs . 'knowledgebase.php?action=displayarticle&id=';

		if ($format == 'json') {

			// Custom Responses
			$other = array();

			// Output Knowledge Base Links
			$query = "SELECT `id`, `name` FROM `tblknowledgebasecats` AS `categories` WHERE `hidden` <> 'on'";
			$rows = $SQL->selectall($query);
			if (is_array($rows)) {
				$name = 'WHMCS Knowledgebase';
				$custom = array('Description' => $name);

				foreach ($rows as $key => $row) {
					$categoryid = $row['id'];
					$categoryname = $row['name'];
					$type = 2;

					$query = sprintf("SELECT `id`, `title` FROM `tblknowledgebaselinks` AS `links`, `tblknowledgebase` AS `kb` WHERE `id` = `articleid` AND `categoryid` = '%d' ORDER BY `views` DESC", $SQL->escape($categoryid));
					$links = $SQL->selectall($query);
					if (is_array($links)) {
						foreach ($links as $key => $link) {
							$id = $link['id'];
							$name = $link['title'];
							$content = $kburl . $id;

							// WHMCS Knowledgebase Link
							$custom[] = array('ID' => $id, 'Name' => $name, 'Content' => $content, 'Category' => $categoryname, 'Type' => $type);
						}
					}
				}
				$other[] = array('Custom' => $custom);
			}

			return $other;

		} else {

			// Output Knowledge Base Links
			$query = "SELECT `id`, `name` FROM `tblknowledgebasecats` AS `categories` WHERE `hidden` <> 'on'";
			$rows = $SQL->selectall($query);
			if (is_array($rows)) {
				$name = 'WHMCS Knowledgebase';
?>
		<Custom Description="<?php echo($name); ?>">
<?php
				foreach ($rows as $key => $row) {
					$categoryid = $row['id'];
					$categoryname = $row['name'];

					$query = sprintf("SELECT `id`, `title` FROM `tblknowledgebaselinks` AS `links`, `tblknowledgebase` AS `kb` WHERE `id` = `articleid` AND `categoryid` = '%d' ORDER BY `views` DESC", $SQL->escape($categoryid));
					$rows = $SQL->selectall($query);
					if (is_array($rows)) {
?>
			<Category Name="<?php echo($categoryname); ?>">
<?php
						foreach ($rows as $key => $row) {
							$id = $row['id'];
							$title = $row['title'];
							$link = $kburl . $id;
?>
				<Response ID="<?php echo($id); ?>" Type="Hyperlink">
					<Name><?php echo(xmlelementinvalidchars($title)); ?></Name>
					<Content><?php echo(xmlelementinvalidchars($link)); ?></Content>
					<Tags/>
				</Response>
<?php
						}
?>
			</Category>
<?php
					}
				}
?>
		</Custom>
<?php
			}
		}
	}

	function LoginCustomHash($password) {
		if (isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 4.0) {
			$password = md5(htmlspecialchars($password));
		}
		return $password;
	}

	function LoginCompleted($_OPERATOR) {

		global $SQL;

		// WHMCS Plugin
		if (isset($_PLUGINS) && $_PLUGINS['WHMCS'] == true) {
			$query = sprintf("SELECT `id`, `username`, `password`, `firstname`, `lastname`, `email`, `supportdepts` FROM `tbladmins` WHERE `username` LIKE BINARY '%s' LIMIT 1", $SQL->escape($username));
			$row = $SQL->selectquery($query);
			if (is_array($row)) {
				$username = $row['username'];
				$hash = $row['password'];
				$firstname = $row['firstname'];
				$lastname = $row['lastname'];
				$email = $row['email'];
				$departments = $row['supportdepts'];
				$custom = $row['id'];
				
				if ($hash == $password) {
					$departments = explode(',', $departments);
					$where = implode("' OR `id` = '", $departments);
					$query = sprintf("SELECT `name` FROM `tblticketdepartments` WHERE `id` = '%s'", $where);
					$rows = $SQL->selectall($query);
					if (is_array($rows)) {
						$departments = array();
						foreach ($rows as $key => $department) {
							if (is_array($department)) {
								$departments[] = $department['name'];
							}
						}
					}
					$department = implode('; ', $departments);
					
					$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `username` = '%s', `password` = '%s', `firstname` = '%s', `lastname` = '%s', `email` = '%s', `department` = '%s', `custom` = '%d' WHERE `id` = %d LIMIT 1", $SQL->escape($username), $SQL->escape($hash), $SQL->escape($firstname), $SQL->escape($lastname), $SQL->escape($email), $SQL->escape($department), $custom, $_OPERATOR['ID']);
					$SQL->updatequery($query);
					
					$_OPERATOR['USERNAME'] = $username;
					$_OPERATOR['PASSWORD'] = $hash;
					$_OPERATOR['NAME'] = (!empty($lastname)) ? $firstname . ' ' . $lastname : $firstname;
					$_OPERATOR['DEPARMENT'] = $department;
					return $_OPERATOR;
				}
			} else {
				$_OPERATOR['USERNAME'] = $username;
				$_OPERATOR['PASSWORD'] = $hash;
				$_OPERATOR['NAME'] = (!empty($lastname)) ? $firstname . ' ' . $lastname : $firstname;
				$_OPERATOR['DEPARMENT'] = $department;
				return $_OPERATOR;
			}
		}
		return $_OPERATOR;
	}

	function LoginFailed($data) {

		global $SQL;
		global $_SETTINGS;

		$_OPERATOR = $data['Operator'];
		$password = $data['Password'];

		// Sync WHMCS Account
		$query = sprintf("SELECT `id`, `username`, `password`, `firstname`, `lastname`, `email`, `supportdepts` FROM `tbladmins` WHERE `username` LIKE BINARY '%s'", $SQL->escape($_OPERATOR['USERNAME']));
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$username = $row['username'];
			$hash = $row['password'];
			$firstname = $row['firstname'];
			$lastname = $row['lastname'];
			$email = $row['email'];
			$departments = $row['supportdepts'];
			$custom = $row['id'];
			
			if ($hash == $password) {
				$departments = explode(',', $departments);
				$where = implode("' OR `id` = '", $departments);
				$query = sprintf("SELECT `name` FROM `tblticketdepartments` WHERE `id` = '%s'", $where);
				$rows = $SQL->selectall($query);
				if (is_array($rows)) {
					$departments = array();
					foreach ($rows as $key => $department) {
						if (is_array($department)) {
							$departments[] = $department['name'];
						}
					}
				}
				$department = implode('; ', $departments);
				
				$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `username` = '%s', `password` = '%s', `firstname` = '%s', `lastname` = '%s', `email` = '%s', `department` = '%s', `custom` = '%d' WHERE `id` = %d", $SQL->escape($username), $SQL->escape($hash), $SQL->escape($firstname), $SQL->escape($lastname), $SQL->escape($email), $SQL->escape($department), $custom, $SQL->escape($_OPERATOR['ID']));
				$SQL->updatequery($query);
				
				$_OPERATOR['USERNAME'] = $username;
				$_OPERATOR['PASSWORD'] = $hash;
				$_OPERATOR['NAME'] = (!empty($lastname)) ? $firstname . ' ' . $lastname : $firstname;
				$_OPERATOR['DEPARMENT'] = $department;
				return $_OPERATOR;
			}
		
		}

		return $_OPERATOR;
	}

	function LoginAccountMissing($data) {
		
		global $SQL;
		global $_SETTINGS;

		$username = $data['Username'];
		$password = $data['Password'];

		// MD5 Password Hash
		if (isset($_REQUEST['Version']) && $_REQUEST['Version'] >= 4.0) {
			$password = md5(htmlspecialchars($password));
		}

		// WHMCS Account
		$query = sprintf("SELECT `id`, `username`, `password`, `firstname`, `lastname`, `email`, `supportdepts` FROM `tbladmins` WHERE `username` LIKE BINARY '%s'", $SQL->escape($username));
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$username = $row['username'];
			$hash = $row['password'];
			$firstname = $row['firstname'];
			$lastname = $row['lastname'];
			$email = $row['email'];
			$departments = $row['supportdepts'];
			$custom = $row['id'];
			
			$departments = explode(',', $departments);
			$where = implode("' OR `id` = '", $departments);
			$query = sprintf("SELECT `name` FROM `tblticketdepartments` WHERE `id` = '%s'", $where);
			$rows = $SQL->selectall($query);
			if (is_array($rows)) {
				$departments = array();
				foreach ($rows as $key => $department) {
					if (is_array($department)) {
						$departments[] = $department['name'];
					}
				}
			}
			$department = implode('; ', $departments);
			
			// Operator Password
			if ($hash == $password) {
				
				$query = sprintf("SELECT * FROM `" . $_SETTINGS['TABLEPREFIX'] . "users` WHERE `custom` = %d", $custom);
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					$query = sprintf("UPDATE " . $_SETTINGS['TABLEPREFIX'] . "users SET `username` = '%s', `password` = '%s', `firstname` = '%s', `lastname` = '%s', `email` = '%s', `department` = '%s' WHERE `custom` = %d", $SQL->escape($username), $SQL->escape($hash), $SQL->escape($firstname), $SQL->escape($lastname), $SQL->escape($email), $SQL->escape($department), $custom);
					$SQL->updatequery($query);

					$id = $row['id'];
					$datetime = $row['datetime'];
					$privilege = $row['privilege'];
					$status = $row['status'];

				} else {
					$query = sprintf("INSERT INTO " . $_SETTINGS['TABLEPREFIX'] . "users (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `department`, `device`, `image`, `privilege`, `status`, `custom`) VALUES ('', '%s', '%s', '%s', '%s', '%s', '%s', '', '', '-1', '-1', '%d')", $SQL->escape($username), $SQL->escape($hash), $SQL->escape($firstname), $SQL->escape($lastname), $SQL->escape($email), $SQL->escape($department), $custom);
					$id = $SQL->insertquery($query);
					$privilege = -1;
					$status = -1;
				}
				
				$_OPERATOR['ID'] = $id;
				$_OPERATOR['USERNAME'] = $username;
				$_OPERATOR['PASSWORD'] = $hash;
				$_OPERATOR['NAME'] = (!empty($lastname)) ? $firstname . ' ' . $lastname : $firstname;
				$_OPERATOR['DEPARMENT'] = $department;
				
				if (!isset($datetime)) {
					$query = sprintf("SELECT `datetime` FROM `" . $_SETTINGS['TABLEPREFIX'] . "users` WHERE `custom` = %d", $custom);
					$row = $SQL->selectquery($query);
					if (is_array($row)) {
						$_OPERATOR['DATETIME'] = $row['datetime'];
					}
				}
				$_OPERATOR['PRIVILEGE'] = $privilege;
				$_OPERATOR['STATUS'] = $status;
				return $_OPERATOR;
				
			}
		}

		return false;
	}

	function SettingsLoaded($_SETTINGS) {

		global $SQL;

		$query = "SELECT `setting`, `value` FROM `tblconfiguration`";
		$row = $SQL->selectquery($query);
		$CONFIG = array();
		while ($row) {
			if (is_array($row)) {
				$CONFIG[$row['setting']] = $row['value'];
			}
			$row = $SQL->selectnext();
		}

		$domain = '';
		if (!empty($CONFIG['SystemSSLURL'])) {
			$domain = trim($CONFIG['SystemSSLURL']);
		} else {
			$domain = trim($CONFIG['SystemURL']);
		}
		if (substr($domain, -1) != '/') { $domain = $domain . '/'; }

		$host = str_replace(array('http://', 'https://'), '', $domain);
		
		$_SETTINGS['HTMLHEAD'] = <<<END
<!-- stardevelop.com Live Help International Copyright - All Rights Reserved //-->
<!--  BEGIN stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
<script type="text/JavaScript" src="{$domain}modules/livehelp/scripts/jquery-latest.js"></script>
<script type="text/javascript">
<!--
	var LiveHelpSettings = {};
	LiveHelpSettings.server = '{$host}';
	LiveHelpSettings.embedded = true;

	(function(d, $, undefined) { 
		$(window).ready(function() {
			var LiveHelp = d.createElement('script'); LiveHelp.type = 'text/javascript'; LiveHelp.async = true;
			LiveHelp.src = ('https:' == d.location.protocol ? 'https://' : 'http://') + LiveHelpSettings.server + '/livehelp/scripts/jquery.livehelp.js';
			var s = d.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(LiveHelp, s);
		});
	})(document, jQuery);
-->
</script>
<!--  END stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
END;

		$_SETTINGS['HTMLBODY'] = '';

		$_SETTINGS['HTMLIMAGE'] = <<<END
<!-- stardevelop.com Live Help International Copyright - All Rights Reserved //-->
<!--  BEGIN stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
<a href="#" class="LiveHelpButton"><img src="{$domain}modules/livehelp/include/status.php" id="LiveHelpStatusDefault" name="LiveHelpStatusDefault" border="0" alt="Live Help" class="LiveHelpStatus"/></a>
<!--  END stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
END;

		return $_SETTINGS;
	}

	function SettingsPlugin() {

		global $SQL;

?>
<Plugin ID="WHMCS">
<?php
		$query = "SELECT `value` FROM `tblconfiguration` WHERE `setting` = 'SystemSSLURL' LIMIT 1";
		$row = $SQL->selectquery($query);
		$address = $row['value'];
		if (empty($address)) {
			$query = "SELECT `value` FROM `tblconfiguration` WHERE `setting` = 'SystemURL' LIMIT 1";
			$row = $SQL->selectquery($query);
			$address = $row['value'];
		}
		
		if (substr($address, -1) != '/') {
			$address = $address . '/';
		}
		
		$customadminpath = '';
		require(dirname(__FILE__) . '/../../../../../configuration.php');
		
		if (!$customadminpath) { $customadminpath = 'admin'; }
		$address .= $customadminpath . '/';
	
?>
<QuickLinks Address="<?php echo($address); ?>">
<Link Name="Summary" Image="card-address">clientssummary.php?userid={0}</Link>
<Link Name="Orders" Image="shopping-basket">orders.php?client={0}</Link>
<Link Name="Products / Services" Image="box">clientshosting.php?userid={0}</Link>
<Link Name="Domains" Image="globe-medium-green">clientsdomains.php?userid={0}</Link>
<Link Name="Invoices" Image="document-invoice">clientsinvoices.php?userid={0}</Link>
<Link Name="Add Order" Image="shopping-basket--plus">ordersadd.php?userid={0}</Link>
<Link Name="Create Invoice" Image="document--plus">invoices.php?action=createinvoice&amp;userid={0}</Link>
<Link Name="Quotes" Image="documents-text">clientsquotes.php?userid={0}</Link>
<Link Name="Tickets" Image="ticket">supporttickets.php?view=any&amp;client={0}</Link>
<Link Name="Emails" Image="mail-open-document">clientsemails.php?userid={0}</Link>
</QuickLinks>
</Plugin>
<?php
	}

	function VisitorCustomDetails($id) {

		global $SQL;
		global $_SETTINGS;

		$query = sprintf("SELECT `custom`, `name`, `reference` FROM " . $_SETTINGS['TABLEPREFIX'] . "custom WHERE `request` = %d LIMIT 1", $id);
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$custom = $row['custom'];
			$username = $row['name'];
			$reference = $row['reference'];

			return array('Custom' => $custom, 'Username' => $username, 'Reference' => $reference);
		}

		return false;
	}

	function DepartmentsLoaded($departments) {

		global $SQL;

		$departs = $departments;
		$departments = array();
		if (is_array($departs)) {
			foreach ($departs as $key => $department) {
				$query = sprintf("SELECT `name`, `hidden` FROM `tblticketdepartments` WHERE `name` = '%s' LIMIT 1", $department);
				$row = $SQL->selectquery($query);
				if (is_array($row)) {
					if ($row['hidden'] != 'on') {
						$departments[] = $row['name'];
					}
				} else {
					$departments[] = $department;
				}
			}
			sort($departments);
			return $departments;
		}
		return $departs;
	}

}

// Add Hook Functions
// $hooks->add('ExampleHooks', 'EventName', 'FunctionName');
$class = 'WHMCSHooks';

if (isset($_PLUGINS) && $_PLUGINS['WHMCS'] == true) {
	$hooks->add($class, 'CloseChat', 'CloseChat');
	$hooks->add($class, 'LoginCustomHash', 'LoginCustomHash');
	$hooks->add($class, 'LoginCompleted', 'LoginCompleted');
	$hooks->add($class, 'LoginFailed', 'LoginFailed');
	$hooks->add($class, 'LoginAccountMissing', 'LoginAccountMissing');
	$hooks->add($class, 'SettingsLoaded', 'SettingsLoaded');
	$hooks->add($class, 'SettingsPlugin', 'SettingsPlugin');
	$hooks->add($class, 'VisitorCustomDetails', 'VisitorCustomDetails');
	$hooks->add($class, 'ResponsesCustom', 'ResponsesCustom');
	$hooks->add($class, 'DepartmentsLoaded', 'DepartmentsLoaded');
}

?>