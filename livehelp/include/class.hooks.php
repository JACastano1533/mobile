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

class Hooks {

	var $included = false;
	var $folders = array();
	var $hooks = array();

	function Hooks() {
		if ($pluginshandle = opendir(dirname(__FILE__) . '/../plugins/')) {
			while (false !== ($folder = readdir($pluginshandle))) {
				if ($folder != '.' && $folder != '..' && is_dir(dirname(__FILE__) . '/../plugins/' . $folder)) {
					if ($handle = opendir(dirname(__FILE__) . '/../plugins/' . $folder)) {
						while (false !== ($subfolder = readdir($handle))) {
							if ($subfolder == 'hooks') {
								$this->folders[] = dirname(__FILE__) . '/../plugins/' . $folder . '/' . $subfolder . '/';
								break;
							}
						}
						closedir($handle);
					}
				}
			}
			closedir($pluginshandle);
		}
	}

	function run($event, $data = null) {
		if ($data != null) {
			$result = $data;
		} else {
			$result = -1;
		}

		foreach ($this->hooks as $key => $hook) {
			// Validate Hook Class
			$valid = false;
			$length = strlen('Hooks');
			if ($length == 0) {
				$valid = true;
			}
			$valid = (substr($hook['class'], -$length) === 'Hooks');

			// Run Hook
			if ($hook['event'] == $event && $valid == true) {
				$class = new $hook['class']();
				if ($data != null) {
					$result = $class->$hook['function']($data);
					if ($result != null) {
						return $result;
					}
				} else {
					$result = $class->$hook['function']();
					if ($result != null) {
						return $result;
					}
				}
			}
		}

		return $result;
	}

	function output($event, $data = null) {
		if ($data != null) {
			$result = $data;
		} else {
			$result = -1;
		}

		foreach ($this->hooks as $key => $hook) {
			// Validate Hook Class
			$valid = false;
			$length = strlen('Hooks');
			if ($length == 0) {
				$valid = true;
			}
			$valid = (substr($hook['class'], -$length) === 'Hooks');

			// Output Hook
			if ($hook['event'] == $event && $valid == true) {
				$class = new $hook['class']();
				if ($data != null) {
					$result = $class->$hook['function']($data);
					if ($result != null) {
						echo $result;
					}
				} else {
					$result = $class->$hook['function']();
					if ($result != null) {
						echo $result;
					}
				}
			}
		}

		return $result;
	}

	function add($class, $event, $function) {
		$this->hooks[] = array('class' => $class, 'event' => $event, 'function' => $function);
	}

}

$hooks = new Hooks();

if (!$hooks->included) {
	foreach ($hooks->folders as $key => $folder) {
		$include = $folder . 'hooks.php';
		if (file_exists($include)) {
			ob_start();
			include($include);
			ob_end_clean();
		}
	}
	$hooks->included = true;
}

?>