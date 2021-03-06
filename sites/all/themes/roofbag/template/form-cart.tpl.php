<?php

/**
 Cart Form template
 */
 //print render($form);
  // Render remaining form elements as usual.
 // echo '<pre>'; print_r($form);


function roofbag_uc_cart_checkout_form($form) {
  drupal_add_css(drupal_get_path('module', 'uc_cart') . '/uc_cart.css');
 
  $output = '<div id="checkout-instructions">' . check_markup(variable_get('uc_checkout_instructions', ''), variable_get('uc_checkout_instructions_format', FILTER_FORMAT_DEFAULT), FALSE) . '</div>';
 
  foreach (element_children($form['panes']) as $pane_id) {
    if (function_exists(($func = _checkout_pane_data($pane_id, 'callback')))) {
      $result = $func('theme', $form['panes'][$pane_id], NULL);
      if (!empty($result)) {
        $output .= $result;
        $form['panes'][$pane_id] = array();
      }
      else {
        $output .= drupal_render($form['panes'][$pane_id]);
      }
    }
    else {
      $output .= drupal_render($form['panes'][$pane_id]);
    }
  }
 
  $output .= '<div id="checkout-form-bottom">' . drupal_render($form) . '</div>';
 
  return $output;
}



 print drupal_render_children($form);
 if(isset($_SESSION['sdata']) && !empty($_SESSION['sdata'])) {
   echo $_SESSION['sdata'];
}
 
  ?>
 <div id="cart-actions">
 <?php 
 
 print render($form['actions']);
  
?>
</div>
<style>
.form-actions
{
	display:none;
}
#cart-actions .form-actions , .add-accessories .form-actions
{
	display:block!important;
}
</style>