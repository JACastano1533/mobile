<?php

function roofbag_process_node(&$vars) {

    //dsm($vars);
    //dsm($vars['theme_hook_suggestions']);
    // If some node type then...
    if ($vars['node']->type == 'product') {
	// add a template suggestion
	$vars['theme_hook_suggestions'][] = 'node__product';
    } else if ($vars['node']->type == 'page') {
	// add a template suggestion
	$vars['theme_hook_suggestions'][] = 'node__page';
    }
}

function roofbag_theme($existing, $type, $theme, $path) {
    // Ex 1: the "story" node edit form.
    $items['uc_cart_view_form'] = array(
	'render element' => 'form',
	'template' => 'form-cart',
	'path' => drupal_get_path('theme', 'roofbag') . '/template',
    );



    return $items;
}

function roofbag_preprocess_page(&$vars) {
    drupal_add_js('//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js', 'external');
}

function roofbag_js_alter(&$javascript) {
    foreach ($javascript as $name => $values) {
	if ($name == "//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js") {
	    $values['async'] = TRUE;
	    break;
	}
    }
}

function roofbag_mail($key, &$message, $params) {
    switch ($key) {
	case 'order_confirmation':
	    $message['subject'] = $params['subject'];
	    $message['body'][] = $params['body'];
	    break;
    }
}

function setRBAmount($amount) {
    if (strpos($amount, ".") !== false) {
	return "$" . number_format($amount, 2, '.', ',');
    } else {
	return "$" . $amount;
    }
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function bb2html($text) {
  $bbcode = array(
    "[strong]", "[/strong]",
    "[b]", "[/b]",
    "[u]", "[/u]",
    "[i]", "[/i]",
    "[em]", "[/em]",
    "[amp]", "[theta]", "[degree]", "[prime]", "[doubleprime]", "[squareroot]"
  );
  $htmlcode = array(
    "<strong>", "</strong>",
    "<strong>", "</strong>",
    "<u>", "</u>",
    "<em>", "</em>",
    "<em>", "</em>",
    "&amp;", "&theta;", "&#176;", "&prime;", "&Prime;", "&radic;"
  );
  return str_replace($bbcode, $htmlcode, $text);
}

function bb_strip($text) {
  $bbcode = array(
    "[strong]", "[/strong]",
    "[b]", "[/b]",
    "[u]", "[/u]",
    "[i]", "[/i]",
    "[em]", "[/em]",
    "&amp;", "&theta;", "&#176;", "&prime;", "&Prime;", "&radic;"
  );
  return str_replace($bbcode, '', $text);
}