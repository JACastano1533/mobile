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

class DatabaseUpgrade {

	var $versions = array('3.30', '3.50', '3.60', '3.70', '3.80', '3.90', '3.95', '4.0', '4.10');
	var $current = '3.28';

	function process($version) {

		global $SQL;
		global $_SETTINGS;

		// Upgrade Database Schema
		if ((float)$version > (float)$this->current && file_exists('../install/mysql.schema.' . $version . '.upgrade.txt')) {
		
			$sqlfile = file('../install/mysql.schema.' . $version . '.upgrade.txt');
			if (is_array($sqlfile)) {
				$query = '';
				foreach ($sqlfile as $key => $line) {
					if (trim($line) != '' && substr(trim($line), 0, 1) != '#') {
						$line = str_replace('prefix_', $_SETTINGS['TABLEPREFIX'], $line);
						$query .= trim($line); unset($line);
						if (strpos($query, ';') !== false) {

							// Override MySQL Engine
							if (isset($_SETTINGS['FORCEINNODB'])) {
								$query = str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $query);
							}

							$result = $SQL->miscquery($query);
							if ($result == false) {
								return;
							}
							$query = '';
						}
					}
				}
				unset($sqlfile);
			}

			$version = (strlen(substr($version, strpos($version, '.'))) > 1) ? (float)$version : $version;
			if ($this->current == '3.28') {
				$query = sprintf("INSERT INTO `" . $_SETTINGS['TABLEPREFIX'] . "settings` (`name`, `value`) VALUES ('ServerVersion', '%s');", $SQL->escape($version));
				$SQL->insertquery($query);
			} else {
				$query = sprintf("UPDATE `" . $_SETTINGS['TABLEPREFIX'] . "settings` SET `value` = '%s' WHERE `" . $_SETTINGS['TABLEPREFIX'] . "settings`.`name` = 'ServerVersion' LIMIT 1;", $SQL->escape($version));
				$SQL->updatequery($query);
			}
			$this->current = (string)$version;
		}

	}

	function upgrade() {

		global $SQL;
		global $_SETTINGS;

		$query = "SELECT `value` FROM `" . $_SETTINGS['TABLEPREFIX'] . "settings` WHERE `name` = 'ServerVersion' LIMIT 1";
		$row = $SQL->selectquery($query);
		if (is_array($row)) {
			$this->current = $row['value'];
		}

		// Automatic Upgrade
		foreach ($this->versions as $key => $version) {
			$this->process($version);
		}
		
		return $this->current;
	}

}

?>