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

ini_set('magic_quotes_sybase', 0);

// Open MySQL Connection
$SQL = new MySQL;

/*
function stripslashes_string($value) {
	return is_array($value) ? stripslashes($value) : $value;
}
*/

if (get_magic_quotes_gpc()) {
	$_COOKIE = array_map('stripslashes', $_COOKIE);
	$_REQUEST = array_map('stripslashes', $_REQUEST);
}

//$_REQUEST = array_map('addslashes', $_REQUEST);

if (!isset($_SERVER['HTTP_REFERER'])){ $_SERVER['HTTP_REFERER'] = ''; }
if (!isset($_REQUEST['COOKIE'])){ $_REQUEST['COOKIE'] = ''; }
if (!isset($_REQUEST['SERVER'])){ $_REQUEST['SERVER'] = ''; }

$dir = dirname(__FILE__);
include($dir . '/class.hooks.php');

$query = "SELECT `name`, `value` FROM " . $table_prefix . "settings";
$rows = $SQL->selectall($query);
if (is_array($rows)) {

	if (!isset($_SETTINGS)) { $_SETTINGS = array(); } else { reset($_SETTINGS); }
	foreach ($rows as $key => $row) {
		if (is_array($row)) {
			$name = strtoupper($row['name']);
			if (!array_key_exists($name, $_SETTINGS)) {
				$_SETTINGS[strtoupper($name)] = $row['value'];
			}
		}
	}

	// Settings Loaded Hook
	$_SETTINGS = $hooks->run('SettingsLoaded', $_SETTINGS);
	
	// Override Language
	if (isset($_REQUEST['LANGUAGE']) && strlen($_REQUEST['LANGUAGE']) == 2) {
		$_SETTINGS['LOCALE'] = $_REQUEST['LANGUAGE'];
	}
	if (empty($_SETTINGS['LOCALE'])) { $_SETTINGS['LOCALE'] = 'en'; }
	define('LANGUAGE', $_SETTINGS['LOCALE']);

	// Default Settings
	if (!isset($_SETTINGS['LIMITHISTORY'])) { $_SETTINGS['LIMITHISTORY'] = 0; }
	if (!isset($_SETTINGS['TRANSCRIPTVISITORALERTS'])) { $_SETTINGS['TRANSCRIPTVISITORALERTS'] = false; }

	if (!isset($_SETTINGS['CHATWINDOWWIDTH'])) { $_SETTINGS['CHATWINDOWWIDTH'] = 625; }
	if (!isset($_SETTINGS['CHATWINDOWHEIGHT'])) { $_SETTINGS['CHATWINDOWHEIGHT'] = 435; }
	if (!isset($_SETTINGS['TEMPLATE']) || empty($_SETTINGS['TEMPLATE'])) { $_SETTINGS['TEMPLATE'] = 'default'; }
	if (!isset($_SETTINGS['LOCALE'])) { $_SETTINGS['LOCALE'] = 'en'; } elseif (empty($_SETTINGS['LOCALE'])) { $_SETTINGS['LOCALE'] = 'en'; }
	if (!isset($_SETTINGS['EMAILCOPY'])) { $_SETTINGS['EMAILCOPY'] = false; }

	if (!isset($_SETTINGS['OFFLINEEMAILHEADERIMAGE'])) { $_SETTINGS['OFFLINEEMAILHEADERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/OfflineEmail.gif'; }
	if (!isset($_SETTINGS['OFFLINEEMAILFOOTERIMAGE'])) { $_SETTINGS['OFFLINEEMAILFOOTERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/LogoSmall.png'; }
	if (!isset($_SETTINGS['CHATTRANSCRIPTHEADERIMAGE'])) { $_SETTINGS['CHATTRANSCRIPTHEADERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/ChatTranscript.gif'; }
	if (!isset($_SETTINGS['CHATTRANSCRIPTFOOTERIMAGE'])) { $_SETTINGS['CHATTRANSCRIPTFOOTERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/LogoSmall.png'; }
	if (!isset($_SETTINGS['PASSWORDRESETHEADERIMAGE'])) { $_SETTINGS['PASSWORDRESETHEADERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/PasswordReset.gif'; }
	if (!isset($_SETTINGS['PASSWORDRESETFOOTERIMAGE'])) { $_SETTINGS['PASSWORDRESETFOOTERIMAGE'] = $_SETTINGS['URL'] . '/livehelp-pro/locale/' . LANGUAGE . '/images/LogoSmall.png'; }

	if (!isset($_SETTINGS['APPNAME'])) { $_SETTINGS['APPNAME'] = 'Live Help'; }
	if (!isset($_SETTINGS['ITUNES'])) { $_SETTINGS['ITUNES'] = '359282303'; }
	if (!isset($_SETTINGS['TILEIMAGE'])) { $_SETTINGS['TILEIMAGE'] = './images/Win8Tile.png'; }
	if (!isset($_SETTINGS['TILECOLOR'])) { $_SETTINGS['TILECOLOR'] = '#E2E2E2'; }
	if (!isset($_SETTINGS['TABLEPREFIX'])) { $_SETTINGS['TABLEPREFIX'] = $table_prefix; }
	if (!isset($_SETTINGS['CONNECTIONTIMEOUT'])) { $_SETTINGS['CONNECTIONTIMEOUT'] = 30; }
	if (!isset($_SETTINGS['VISITORREFRESH'])) { $_SETTINGS['VISITORREFRESH'] = 15; }

	// DO NOT CHANGE
	if (!isset($_SETTINGS['VISITORTIMEOUT'])) { $_SETTINGS['VISITORTIMEOUT'] = $_SETTINGS['VISITORREFRESH'] * 4.5; }

	// Auto-detect cookie domain / TLD
	if ($_SERVER['SERVER_PORT'] == '443') {	$protocol = 'https://'; } else { $protocol = 'http://'; }
	$host = str_replace(array('http://', 'https://'), '', $_SETTINGS['URL']);
	$_SETTINGS['URL'] = $protocol . $host;


	if (!isset($_SETTINGS['COOKIEDOMAIN'])) {
		
		$domain = '';
		$tld = '';

		// Future updates - http://en.wikipedia.org/wiki/List_of_Internet_TLDs
		$gTlds = explode(',', str_replace(' ', '', 'aero, asia, biz, cat, com, coop, edu, gov, gen, info, int, jobs, mil, mobi, museum, name, net, org, pro, tel, travel, ltd, xxx')); 
		$cTlds = explode(',', str_replace(' ', '', 'ac, ad, ae, af, ag, ai, al, am, an, ao, aq, ar, as, at, au, aw, az, ax, ba, bb, bd, be, bf, bg, bh, bi, bj, bm, bn, bo, br, bs, bt, bv, bw, by, bz, ca, cc, cd, cf, cg, ch, ci, ck, cl, cm, cn, co, cr, cs, cu, cv, cx, cy, cz, dd, de, dj, dk, dm, do, dz, ec, ee, eg, eh, er, es, et, eu, fi, fj, fk, fm, fo, fr, ga, gb, gd, ge, gf, gg, gh, gi, gl, gm, gn, gp, gq, gr, gs, gt, gu, gw, gy, hk, hm, hn, hr, ht, hu, id, ie, il, im, in, io, iq, ir, is, it, je, jm, jo, jp, ke, kg, kh, ki, km, kn, kp, kr, kw, ky, kz, la, lb, lc, li, lk, lr, ls, lt, lu, lv, ly, ma, mc, md, me, mg, mh, mk, ml, mm, mn, mo, mp, mq, mr, ms, mt, mu, mv, mw, mx, my, mz, na, nc, ne, nf, ng, ni, nl, no, np, nr, nu, nz, om, pa, pe, pf, pg, ph, pk, pl, pm, pn, pr, ps, pt, pw, py, qa, re, ro, rs, ru, rw, sa, sb, sc, sd, se, sg, sh, si, sj, sk, sl, sm, sn, so, sr, ss, st, su, sv, sy, sz, tc, td, tf, tg, th, tj, tk, tl, tm, tn, to, tp, tr, tt, tv, tw, tz, ua, ug, uk, um, us, uy, uz, va, vc, ve, vg, vi, vn, vu, wf, ws, ye, yt, yu, za, zm, zw'));
		$tldarray = array_merge($gTlds, $cTlds); 

		$url = trim($_SERVER['HTTP_HOST']); 
		  
		$domainarray = explode('.', $url);
		$top = count($domainarray);

		for ($i = 0; $i < $top; $i++) { 
			$domainsection = array_pop($domainarray); 
			if (in_array($domainsection, $tldarray)) { 
				$tld = '.' . $domainsection . $tld;
			} 
			else {
				$domain = $domainsection;
				break;
			} 
		}

		// Set cookie domain - blank for localhost
		if (strpos($_SERVER['HTTP_HOST'], '.') === false) {
			$_SETTINGS['COOKIEDOMAIN'] = '';
		}
		elseif ($_REQUEST['COOKIE'] != '') {
			$_SETTINGS['COOKIEDOMAIN'] = '.' . $_REQUEST['COOKIE'];
		}
		elseif ($domain != '') {
			$_SETTINGS['COOKIEDOMAIN'] = '.' . $domain . $tld;
		}
		else {
			$_SETTINGS['COOKIEDOMAIN'] = '.' . $_SETTINGS['DOMAIN'];
		}

		// Remove .www. if at the start of string
		if (substr($_SETTINGS['COOKIEDOMAIN'], 0,5) == '.www.') {
			$_SETTINGS['COOKIEDOMAIN'] = substr($_SETTINGS['COOKIEDOMAIN'], 4);
		}
	}

	if ($_REQUEST['SERVER'] != '' && $_SERVER['HTTP_HOST'] != 'localhost') {
		$server = $_REQUEST['SERVER'];
		if ($server == '//') {
			$server = '';
		}
	}
	else {
	
		// Change Server HTTP / HTTPS
		$protocols = array('http://', 'https://'); 
		if ($_SERVER['SERVER_PORT'] == '443') {
			$protocol = 'https://';
			$server = str_replace('http://', $protocol, $_SETTINGS['URL']); 
		}
		else {
			$protocol = 'http://';
			$server = str_replace('https://', $protocol, $_SETTINGS['URL']); 
		}
	}
	
	// Override Templates
	if (isset($_REQUEST['TEMPLATE']) && file_exists('templates/' . $_REQUEST['TEMPLATE'] . '/')) {
		$_SETTINGS['TEMPLATE'] = $_REQUEST['TEMPLATE'];
	}
	if (empty($_SETTINGS['TEMPLATE'])) { $_SETTINGS['TEMPLATE'] = 'default'; }
	define('TEMPLATE', $_SETTINGS['TEMPLATE']);

	$language_directory = '/livehelp-pro/locale/' . LANGUAGE . '/images/';
	if (isset($_REQUEST['IMAGES']) && $_REQUEST['IMAGES'] !=''){ $language_directory = $_REQUEST['IMAGES']; }

	$_SETTINGS['LOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['LOGO']);
	$_SETTINGS['CAMPAIGNIMAGE'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['CAMPAIGNIMAGE']);
	$_SETTINGS['OFFLINELOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['OFFLINELOGO']);
	$_SETTINGS['ONLINELOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['ONLINELOGO']);
	$_SETTINGS['OFFLINEEMAILLOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['OFFLINEEMAILLOGO']);
	$_SETTINGS['BERIGHTBACKLOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['BERIGHTBACKLOGO']);
	$_SETTINGS['AWAYLOGO'] = preg_replace('%/?livehelp-pro/locale/[a-zA-Z]{2}/images/%', $language_directory, $_SETTINGS['AWAYLOGO']);

	$timezone = (function_exists('date_default_timezone_get')) ? date_default_timezone_get() : ini_get('date.timezone');
	if (empty($timezone)) {
		if (function_exists('date_default_timezone_set')) {
			if ($_SETTINGS['TIMEZONE'] == 0) {
				$timezone = 'GMT';
			} else {
				$sign = substr($_SETTINGS['TIMEZONE'], 0, 1);
				$hours = substr($_SETTINGS['TIMEZONE'], 1, 2);
				
				if ($sign == '+') { $sign = '-'; } else { $sign = '+';}
				$timezone = 'Etc/GMT' . $sign . sprintf("%01d", $hours);
			}
			date_default_timezone_set($timezone);
			unset($timezone);
		}
	}
	$_SETTINGS['SERVERTIMEZONE'] = date('O');


	if ($_SERVER['SERVER_PORT'] == '443') {	$protocol = 'https://'; } else { $protocol = 'http://'; }
	$host = str_replace(array('http://', 'https://'), '', $_SETTINGS['URL']);

	$_SETTINGS['HTMLHEAD'] = <<<END
<!-- stardevelop.com Live Help International Copyright - All Rights Reserved //-->
<!--  BEGIN stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
<script type="text/JavaScript" src="{$_SETTINGS['URL']}/livehelp-pro/scripts/jquery-latest.js"></script>
<script type="text/javascript">
<!--
	var LiveHelpSettings = {};
	LiveHelpSettings.server = '{$host}';
	LiveHelpSettings.embedded = true;

	(function(d, $, undefined) { 
		$(window).ready(function() {
			var LiveHelp = d.createElement('script'); LiveHelp.type = 'text/javascript'; LiveHelp.async = true;
			LiveHelp.src = ('https:' == d.location.protocol ? 'https://' : 'http://') + LiveHelpSettings.server + '/livehelp-pro/scripts/jquery.livehelp.js';
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
<a href="#" class="LiveHelpButton default"><img src="{$_SETTINGS['URL']}/livehelp-pro/include/status.php" id="LiveHelpStatusDefault" name="LiveHelpStatusDefault" border="0" alt="Live Help" class="LiveHelpStatus"/></a>
<!--  END stardevelop.com Live Help Messenger Code - Copyright - NOT PERMITTED TO MODIFY COPYRIGHT LINE / LINK //-->
END;

	return true;
	
}
else {
	return true;
}

?>