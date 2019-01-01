

<div class="main-container <?php print $container_class; ?>">
  <div class="row">
    <section<?php print $content_column_class; ?>>
      <?php print $messages; ?>
    </section>
  </div>
</div>
<?php
global $user;
if ((arg(0) != 'user') && (!$user->uid )) {
    ?>
    <script type="text/javascript">
        if (screen.width >= 768) {
//        if (screen.width >= 0) {
            window.location.href = "https://www.roofbag.com/";
        }
    </script>	
    <?php
}
global $base_url;

$items = uc_cart_get_contents();
if (!empty($_GET['order_number'])) {
    $query = db_select('saved_uc_order', 'suo');
    $query->addField('suo', 'items_array');
    $query->addField('suo', 'postal_code');
    $query->addField('suo', 'sdata');
    $query->addField('suo', 'parent_html');
    $query->addField('suo', 'service_rate');
    $query->condition('order_number', $_GET['order_number']);
    $saved_order = $query->execute()->fetchObject();

    $items = unserialize($saved_order->items_array);
    foreach ($items AS $item) {
	uc_cart_add_item($item->nid, $item->qty, $item->data);
    }
    $_SESSION["sdata"] = unserialize($saved_order->sdata);
    if ($saved_order->postal_code != "")
	$_SESSION["savedzip"] = $saved_order->postal_code;
    if ($saved_order->parent_html != "")
	$_SESSION["parent_html"] = $saved_order->parent_html;
    if ($saved_order->service_rate != "")
	$_SESSION["service_rate"] = $saved_order->service_rate;
    $_SESSION["open_cart_default"] = true;
    drupal_goto('<front>');
}
if (!empty($_GET['mobile'])) {
    $sdata = "";
    $postal_code = "";
    $parent_html = "";
    $service_rate = "";

    $serialize_items = serialize($items);

    if (isset($_SESSION['savedzip']) && !empty($_SESSION['savedzip'])) {
	$postal_code = $_SESSION["savedzip"];
    }
    if (isset($_SESSION['sdata']) && !empty($_SESSION['sdata'])) {
	$sdata = serialize($_SESSION['sdata']);
    }
    if (isset($_SESSION['parent_html']) && !empty($_SESSION['parent_html'])) {
	$parent_html = $_SESSION['parent_html'];
    }
    if (isset($_SESSION['service_rate']) && !empty($_SESSION['service_rate'])) {
	$service_rate = $_SESSION["service_rate"];
    }

    $order_id = $_SESSION["cart_order"];
    $order_number = substr(str_shuffle(md5(time())), 0, 32);
    $num_updated = db_update('uc_orders')
	    ->fields(array(
		'order_status' => 'texted_order',
	    ))
	    ->condition('order_id', $order_id)
	    ->execute();
    db_query("INSERT INTO {saved_uc_order}(order_id,order_number, items_array, postal_code, sdata, parent_html, service_rate) VALUES (:order_id,:order_number, :items_array, :postal_code, :sdata, :parent_html, :service_rate)", array(
	':order_id' => $order_id,
	':order_number' => $order_number,
	':items_array' => $serialize_items,
	':postal_code' => $postal_code,
	':sdata' => $sdata,
	':parent_html' => $parent_html,
	':service_rate' => $service_rate));

    $_SESSION["show_message_popup"] = "A link to your cart has been sent to your phone";

    include 'twiliokj.php';

    drupal_goto('<front>');
}

if (isset($_SESSION['show_message_popup']) && !empty($_SESSION['show_message_popup'])) {
    echo '<script>jQuery(document).ready(function () { jQuery("#load_message_display_modal .modal-body").html(\'<div class="row"><div class="col-md-12">' . $_SESSION['show_message_popup'] . '</div></div>\'); jQuery("#load_message_display_modal").modal("show"); }); </script>';
    unset($_SESSION["show_message_popup"]);
}
if (isset($_SESSION["open_cart_default"])) {
    ?>
    <script>
        var open_cart_default = true;
    </script>
    <?php
    unset($_SESSION["open_cart_default"]);
} else {
    ?>
    <script>
        var open_cart_default = false;
    </script>
    <?php
}

if (isset($_POST['qty_submit'])) {
    $cart_item_ids = $_POST['cart_item_id'];
    foreach ($cart_item_ids AS $key => $cart_item_id) {
	$item = $items[$cart_item_id];
	$item->qty = $_POST['cart_item_qty'][$key];
	uc_cart_update_item($item);
    }
    uc_cart_get_contents(NULL, 'rebuild');
    unset($_SESSION['parent_html']);
}

if (!empty($_GET['remove_id'])) {
    $cart_item_id = $_GET['remove_id'];
    if (array_key_exists($cart_item_id, $items)) {
	uc_cart_remove_item($items[$cart_item_id]->nid, $items[$cart_item_id]->cart_id, $items[$cart_item_id]->data);
    }
    uc_cart_get_contents(NULL, 'rebuild');
    unset($_SESSION['parent_html']);
    if ($items[$cart_item_id]->nid == 1 || $items[$cart_item_id]->nid == 289) {
	drupal_goto('<front>');
    } else {
	header("Location: /#collapseFive_panel");
	exit;
    }
}

$roofbag_exists = false;
//$straps_2_exists = false;
$data_carrier = 0;
$total_items_count = 0;

foreach ($items AS $item) {
    $total_items_count += $item->qty;
    if ($item->nid == 1 || $item->nid == 289 || $item->nid == 292 || $item->nid == 293) {
	$roofbag_exists = true;
	if (isset($item->data["attributes"][3])) {
//	    if ($item->data["attributes"][1] == 1) {
	    if ($item->data["attributes"][3] == 5) {
		$data_carrier = 1;
	    } else if ($item->data["attributes"][3] == 6) {
		$data_carrier = 2;
	    }
//	    foreach ($items AS $item1) {
//		if ($item1->nid == 278) {
//		    $straps_2_exists = true;
//		}
//	    }
//	    if (!$straps_2_exists) {
//		$straps_2_size = $item->data["attributes"][1] ? : 1;
//		uc_cart_add_item(278, 1, array(1 => $straps_2_size));
//		$items = uc_cart_get_contents(NULL, $action = 'rebuild');
//	    }
	}
//	}
    }
}
//if (!$roofbag_exists) {
//    foreach ($items AS $item0) {
//	if ($item0->nid == 278) {
//	    uc_cart_remove_item($item0->nid, $item0->cart_id, $item0->data);
//	    $items = uc_cart_get_contents(NULL, $action = 'rebuild');
//	}
//    }
//}
$no_of_cart_items = sizeof($items);
if (isset($_SESSION["old_cart_item_numbers"])) {
    if ($_SESSION["old_cart_item_numbers"] < $no_of_cart_items) {
	$recently_added_to_cart = true;
    } else {
	$recently_added_to_cart = false;
    }
} else {
    $recently_added_to_cart = false;
}
$_SESSION["old_cart_item_numbers"] = $no_of_cart_items;
?>
<script>
    var no_of_cart_items = '<?php echo $no_of_cart_items; ?>';
    var selected_shipping_id = '';
</script>
<?php if (isset($_SESSION["selected_shipping_id"])) { ?>
    <script>selected_shipping_id = '<?php echo $_SESSION["selected_shipping_id"]; ?>';</script>
<?php } ?>
<?php if (isset($_SESSION["saved_make"])) { ?>
    <script>var saved_make = '<?php echo $_SESSION["saved_make"]; ?>';</script>
<?php } ?>
<?php if (isset($_SESSION["saved_model"])) { ?>
    <script>var saved_model = '<?php echo $_SESSION["saved_model"]; ?>';</script>
<?php } ?>

<?php if (isset($_SESSION["savedzip"])) { ?>
    <script>var savedzip = '<?php echo $_SESSION["savedzip"]; ?>';</script>
<?php } ?>
<script>var data_carrier = '<?php echo $data_carrier; ?>';</script>
<?php if ($recently_added_to_cart) { ?>
    <script>var recently_added_to_cart = true;</script>
<?php } else { ?>
    <script>var recently_added_to_cart = false;</script>
<?php } ?>
<?php
//foreach ($items as $item) {
?>
<style type="text/css">
    .region-content-top{ display:none;}
    .alert-success , #block-system-main > #cart-form-pane {display: none;}
</style>

<?php
//}
//region-content-top
if (current_path() == 'cart/checkout') {
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#edit-panes-customer-primary-email').val('youremail@email.com');
        });

    </script>
    <style>
        #cart-pane, #customer-pane, #delivery-pane, #billing-pane, #quotes-pane, #payment-pane, #comments-pane, #edit-actions { display:none; }
    </style>
    <?php
}

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see bootstrap_preprocess_page()
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see bootstrap_process_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup templates
 */
$id = '';
$cls2 = '';
if (drupal_is_front_page()) {
    $id = 'home-page';
    $cls2 = 'full-width';
}

$cartcls = ($no_of_cart_items) ? ' cartblock-exit' : ' no-cartblock-exit';

include('page_header.php');
?>

<div id="opt_loaderimg1" class="loaderimg_wraper1"><img id="imgloader" src="<?php echo $base_url; ?>/sites/all/themes/roofbag/images/loader-new.svg"/></div>
<div id="opt_loaderimg2" class="loaderimg_wraper2"></div>
<div id="main-container-before" class="main-container <?php
print $container_class;
echo $cartcls;
?>" style="">
    <header role="banner" id="page-header">
	<?php
	if (!empty($site_slogan)) {
	    ?>
    	<p class="lead"><?php print $site_slogan; ?></p>
	    <?php
	}
	?>
	<?php print render($page['header']); ?>
    </header> <!-- /#page-header -->

    <div class="row <?php if (current_path() == 'cart' || request_path() == 'cart/order_complete') echo "order_complete_page"; ?>">
	<?php
//	print render($page['content_top']);
	if (!empty($page['sidebar_first'])) {
	    ?>
    	<aside class="col-sm-3" role="complementary">
		<?php print render($page['sidebar_first']); ?>
    	</aside>  <!-- /#sidebar-first -->
	    <?php
	}
	?>
        <?php if (drupal_is_front_page()) { ?>
        <section class="col-md-9 col-sm-12 cstm_popup_cart_block_opened" id="<?php echo $id; ?>">
        <?php }else{ ?>
        <section <?php print $content_column_class; ?> id="<?php echo $id; ?>">
        <?php } ?>
        <?php
	    if (!empty($page['highlighted'])):
		?>
    	    <div class="highlighted jumbotron">
		    <?php print render($page['highlighted']); ?>
    	    </div>
		<?php
	    endif;
	    if (!empty($breadcrumb))
		print $breadcrumb;

	    print $messages;

	    if (!empty($tabs))
		print render($tabs);

	    if (!empty($page['help']))
		print render($page['help']);

	    if (!empty($action_links)) {
		?>
    	    <ul class="action-links"><?php print render($action_links); ?></ul>
		<?php
	    }

	    if (current_path() == 'fetch/myorder') {
		echo '<div class="col-sm-12"><h1>My Orders</h1></div><hr /> ';
	    }

	    if ($title == 'Customer Reviews' || $title == 'Frequently Asked Questions') {
		?>
    	    <h1><?php print $title; ?></h1>
		<?php
	    }

	    if (drupal_is_front_page()) {
		include "custom_fornt_page.php";
	     } else if (request_path() == 'cart/order_complete') {
          include "order_complete.php";
        } else if (current_path() == 'cart') {
        header("Location: /cart/order_complete");
	    } else if (current_path() == 'cart/checkout/complete') {

		$_SESSION["saved_order_id"] = $page["content"]["system_main"]["#order"]->order_id;
		header("Location: /cart/");
		exit;
	    } else {
		print render($page['content']);
	    }

//	    if (($no_of_cart_items) && (current_path() == 'cart')) {
//		include "accessories_block.php";
//	    }
	    if (current_path() == 'cart/checkout') {
		unset($_SESSION["saved_order_id"]);
		unset($_SESSION["check_order"]);

		include "checkout_block.php";
	    }
	    if (current_path() != 'cart/checkout') {
		foreach ($_SESSION as $key => $value) {
		    if (strpos($key, 'PREVIOUS_') === 0) {
			unset($_SESSION[$key]);
		    }
		}
	    }
	    ?>


        </section>

	<?php
	if (!empty($page['sidebar_second'])) {
	    ?>
    	<aside class="col-sm-3 hide-sidebar-desktop" role="complementary">
		<?php print render($page['sidebar_second']); ?>
    	</aside>  <!-- /#sidebar-second -->
	<?php }
	?>
    </div>
</div>

<!-- Button to trigger modal -->

<?php
if (!empty($page['footer'])) {
    ?>
    <footer class="footer <?php print $container_class; ?>">
	<?php
	print render($page['footer']);
	?>
    </footer>
    <?php
}
?>

<div class="modal fade" id="load_block_content" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Block Title</h4>
            </div>
            <div class="modal-body">
                <div>Description</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ship_block_content" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Block Title</h4>
            </div>
            <div class="modal-body">
                <div>Description</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="load_message_display_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!--	    <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">
                                Block Title
                            </h4>
                        </div>-->
            <div class="modal-body">
                <!--<div>Description</div>-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mobile_display_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    Text  Number
                </h4>
            </div>
            <div class="modal-body">
                <h3>Enter your mobile number</h3>
                <div class="input-group">
                    <span id="verify-country-code-voice" class="input-group-addon">+1</span>
                    <input id="mobile_no" val="" maxlength="10" placeholder="" class="form-control sl_whiteout" type="text">
                </div>
                <!--<input type="text" id ="mobile_no" val="" maxlength="11" placeholder="Ex(+16195518458)" class="form-control">-->


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-custom send_mobile_text btn-secondary" >Send</button>
                <button type="button" class="btn btn-custom  btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>


            </div>
        </div>
    </div>
</div>
<!-- KP -->
<!--<div class="modal fade" id="load_factory_map" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Block Title</h4>
            </div>
            <div class="modal-body">
                <div>Description</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>-->



<?php
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";exit;
?>