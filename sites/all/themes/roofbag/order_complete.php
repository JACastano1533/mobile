<?php
//$query = db_select('uc_orders', 'uo');
//$query->addField('uo', 'order_id');
//$query->addField('uo', 'order_total');
//$query->addField('uo', 'created');
//$query->addField('uo', 'order_status'); // optional: $query->addField('uo', 'product_count');
//$query->addJoin('LEFT', 'uc_order_products', 'p', 'p.order_id = uo.order_id');
//$query->addField('p', 'nid');
//$query->condition('order_status', "Pending");
//$result = $query->orderBy('created', 'DESC')->range(0,1)->execute()->fetchAll();
//if ($order->payment_method == "cod" && $order->order_status == "in_checkout") {

$blockIps = ['67.220.165.162','68.8.201.94','27.54.165.218'];
$clientIps = get_client_ip();

//code added by webplanex
$Userip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
if( isset($_COOKIE['userTrack']) && $_COOKIE['userTrack'] == $Userip ){
    if( isset($_COOKIE['userTrackDate']) && $_COOKIE['userTrackDate'] == date("Y-m-d") ){
        $group = 0;
        $userIpds = str_replace(".", "", $Userip);
        $userIpds = str_split($userIpds);
        $userIpdTotal = array_sum($userIpds);
        $group = $userIpdTotal % 4;
        // $userIpds = explode(".",$Userip);
        // $userIpdTotal = array_sum($userIpds);
        // $userIpdSum = $userIpdTotal / 4;
        // $userIpdMulti = floor($userIpdSum) * 4;
        // $group = $userIpdTotal - $userIpdMulti;
        if( $group <= 0 ){
            $group = 0;
        }
        if( $group >= 3 ){
            $group = 3;
        }
        // if( isset($_COOKIE['abjs_t_1']) && $_COOKIE['abjs_t_1'] == 'e_2' ){
        //   $group = 2;
        // }
        // if( isset($_COOKIE['abjs_t_1']) && $_COOKIE['abjs_t_1'] == 'e_1' ){
        //   $group = 1;
        // }

        //$log  = date("m/d/Y H:i:s A")."    ".substr($_SERVER['HTTP_USER_AGENT'],0,50)."    ".$Userip."    ".str_replace(".","",$Userip)."    ".$group."   CTC".PHP_EOL;
        $log  = date("m/d/Y H:i:s A")."\t".substr($_SERVER['HTTP_USER_AGENT'],0,50)."\t".$Userip."\t".str_replace(".","",$Userip)."\t".$group."\t ThYou".PHP_EOL;

        setcookie('userTrack', '', time() - 3600);
        setcookie('userTrack', '', time() - 3600, '/');
        unset($_COOKIE['userTrack']);
        setcookie('userTrackDate', '', time() - 3600);
        setcookie('userTrackDate', '', time() - 3600, '/');
        unset($_COOKIE['userTrackDate']);
        file_put_contents('logs/log_track.txt', $log, FILE_APPEND);
    }
}

if (isset($_POST['submit_order_confirmation'])) {
    $order_o_id = $_POST['order_id'];
    $order_confimation_email = $_POST['order_confimation_email'];
    $driver_instructions = $_POST['driver_instructions'];

    $order_confirmation_msg = "Thanks for entering your email address - your confirmation has been sent.";
    db_query("UPDATE {uc_orders} SET primary_email = :primary_email, order_confirm = 1 WHERE order_id = :order_id", array(':primary_email' => $order_confimation_email, ':order_id' => $order_o_id,));

    if ($driver_instructions != "") {
	$order_confirmation_msg = "Thanks for entering your email address and delivery instructions - your confirmation has been sent.";
	uc_order_comment_save($order_o_id, $user->uid, $driver_instructions, $type = 'order', $status = "Driver Instructions", $notify = TRUE);
    }

    $order = uc_order_load($order_o_id);
    rules_invoke_event('order_confirmation_rule_event', $order);

    echo '<script>jQuery(document).ready(function () { jQuery("#load_message_display_modal .modal-body").html(\'<div class="row"><div class="col-md-12">' . $order_confirmation_msg . '</div></div>\'); jQuery("#load_message_display_modal").modal("show");  jQuery(".order_confirmation").hide();}); </script>';
} else {

    if (!isset($_SESSION["saved_order_id"])) {

	if (!isset($_SESSION["check_order"])) {
	    drupal_goto('<front>');
	} else {

	    $order_o_id = $_SESSION["check_order"];

	    $order = uc_order_load($order_o_id);
	    if ($order->payment_method == "cod") {
		$order_o_id = $_SESSION["check_order"];
	    } else {
		drupal_goto('<front>');
	    }
	}
    } else {

	$order_o_id = $_SESSION["saved_order_id"];
    }
    $order = uc_order_load($order_o_id);
}

$query = db_select('uc_orders', 'uc');
$query->addField('uc', 'order_confirm');
$query->condition('order_id', $order_o_id);
$order_confirm = $query->execute()->fetchField();



if ($order) {
//    if ($order->payment_method == "cod" && $order->order_status != "call_to_confirm") {
//	$num_updated = db_update('uc_orders')
//		->fields(array(
//		    'order_status' => 'call_to_confirm',
//		))
//		->condition('order_id', $order_o_id)
//		->execute();
//	$order = uc_order_load($order_o_id, true);
//    }

    $query = db_select('uc_zones', 'uz');
    $query->addField('uz', 'zone_name');
    $query->condition('zone_id', $order->delivery_zone);
    $delivery_zone = $query->execute()->fetchField();

    if ($order->payment_method == "cod") {
	$order_status = "Your order is saved-Payment is Pending";
    } else if ($order->order_status == "payment_received" || $order->payment_method == "paypal_wps") {
	$order_status = "Your order is paid and ready to ship!";
    } else {
	$order_status = ucfirst($order->order_status);
    }
    ?>
    <p class="text-center"><strong><?php echo $order_status; ?></strong></p>
    <div class="order_details order-confirmation-sect">
        <div class="panel panel-default" id="collapseOrder_panel">
    	<div class="panel-heading">
    	    <h4 class="panel-title text-center">
    		<a class="accordion-toggle text-center" data-toggle="collapse"  href="#collapseOrder">
    		    <span class="custom_orders"> Order #<?php echo $order->order_id; ?></span><span class="custom_totalss">
    			<strong>Total</strong>
    			<strong><?php echo setRBAmount($order->order_total); ?></strong></span> <span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
    		</a>

    	    </h4>
    	</div>
    	<div id="collapseOrder" class="panel-collapse collapse thanku-page">
    	    <div class="panel-body">
    		<div class="order_page_headings">
    		    <b>Order Summary</b>
    		</div>
    <?php
    foreach ($order->products AS $product) {
	$node = node_load($product->nid);
	if (isset($product->qty)) {
	    $tmp_qty = $product->qty;
	}
	$tmp_size = "";
	$tmp_strap = "";
	$tmp_color = "";
	$tmp_summary = "";
	if (isset($node->body["und"][0]["summary"])) {
	    $tmp_summary = $node->body["und"][0]["summary"];
	}
	if (isset($product->data["attributes"]["Size"][5])) {
	    $tmp_size = " / " . $product->data["attributes"]["Size"][5];
	}
	if (isset($product->data["attributes"]["Size"][6])) {
	    $tmp_size = " / " . $product->data["attributes"]["Size"][6];
	}
	if (isset($product->data["attributes"]["Strap"][1])) {
	    $tmp_strap = " / " . $product->data["attributes"]["Strap"][1];
	}
	if (isset($product->data["attributes"]["Strap"][2])) {
	    $tmp_strap = " / " . $product->data["attributes"]["Strap"][2];
	}
	if (isset($product->data["attributes"]["Color"][3])) {
	    $tmp_color = " / BLK";
	}
	if (isset($product->data["attributes"]["Color"][4])) {
	    $tmp_color = " / GRY";
	}
	?>
			<div class="purchased_products">
			    <table class="shipping-table">
				<tr>
				    <td><?php echo $product->title; ?></td>
				    <td rowspan="2" valign="middle" align="right"><?php echo setRBAmount($product->price); ?></td>
				</tr>
				<tr>
				    <td>Qty: <?php echo $tmp_qty . $tmp_size . $tmp_strap . $tmp_color; ?></td>
			    </table>
			</div>
    <?php } ?>
    		<table class="shipping-table">
    		    <tr>
    			<td>Order Subtotal  </td>
    			<td align="right"><?php echo setRBAmount($order->line_items[0]["amount"]); ?></td>
    		    </tr>
    		    <tr>
    			<td  height="10px" colspan="2"></td>
    		    </tr>
    		    <tr>
    			<td>Shipping  </td>
    			<td align="right"><?php echo setRBAmount($order->line_items[1]["amount"]); ?></td>
    		    </tr>
    		    <tr>
    			<td  height="10px" colspan="2"></td>
    		    </tr>
    <!--    		    <tr>
    			<td><strong>Total</strong></td>
    			<td align="right"><strong><?php echo setRBAmount($order->order_total); ?></strong></td>
    		    </tr>-->
    		    <tr>
    			<td colspan = "2">&nbsp;</td>
    		    </tr>
    		</table>
    		<div class="order_page_headings">
    		    <b>Shipping to</b>
    		</div>
    		<table class="shipping-table">

    		    <tr><td>Name: </td><td><?php echo $order->delivery_first_name; ?></td></tr>
    		    <tr><td>Address: </td><td><?php echo $order->delivery_street1 . ', ' . $order->delivery_street2; ?></td></tr>
    		    <tr><td>City: </td><td><?php echo $order->delivery_city; ?></td></tr>
    		    <tr><td>Zip: </td><td><?php echo $order->delivery_postal_code; ?></td></tr>
    		    <tr><td>State: </td><td><?php echo $delivery_zone; ?></td></tr>
    		    <tr><td>Phone: </td><td><?php echo $order->delivery_phone; ?></td></tr>
    		    <tr><td> </td><td></td></tr>
    <!--                    <tr><td>Ships Date: </td><td><?php //echo date('D M d ');           ?></td></tr>
    		   <tr><td>Delivery Date: </td><td><?php //echo  $_SESSION['service_arrival'];           ?></td></tr>-->

    		    <tr>
    			<td colspan = "2">&nbsp;</td>
    		    </tr>
    		</table>
    		<!--    		<div class="order_page_headings">
    				    <b>Order Status</b>
    				</div>-->
    		<p></p>
    	    </div>
    	</div>
        </div>
    <?php if ($order->payment_method == "cod" && $order->order_status == "in_checkout") { ?>
	    <div class="order_checkoouback">


		<a class="btn btn-callpay" href="tel:800-276-6322">Call and Pay</a>
		<a class="goback" href="/cart/checkout" id="goback"  >Go Back</a>


	    </div>
	    <style>
		#see_my_order_btn_top{
		    display: none !important;
		}
	    </style>
	<?php
    } else {
	if (!$order_confirm) {
	    ?>
	        <div class="order_confirmation">
	    	<form action="/cart/order_complete" method="POST" id="order_complete_email_form">
	    	    <div class="order_page_headings">
	    		<b>Order Confirmation</b>
	    	    </div>
	    	    <div class="order_card">
	    		<p>To receive order confirmation, please enter you email address:</p>
	    		<input type="email" name="order_confimation_email" id="order_confimation_email" required/>
	    		If needed, enter delivery instructions for driver:
	    		<textarea name="driver_instructions" rows="3" style="height: 85px;"></textarea>
	    		<input name="order_id" type="hidden" value="<?php echo $order_o_id; ?>"/>
	    		<input name="submit_order_confirmation" type="submit" class="submit_btn_order_confirmation" value="Submit"/>
	    	    </div>
	    	</form>
	        </div>
	    <?php
	}
    }
    ?>

    </div>
<?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#order_complete_email_form").submit(function(){
            var email = jQuery("#order_confimation_email").val();
            if( !validateEmail(email) ){
                alert("Please enter valid email address.");
                return false;
            }
        });
    });
    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
</script>

<?php if( !in_array($clientIps, $blockIps) ){ ?>
<!-- Google Code for Products Listing or Google Shopping Campaign 2018 Conversion Page added by J. Garcia 1/16/2018-->

<script type="text/javascript">
var value=parseFloat('<?php echo setRBAmount($order->order_total); ?>'.replace(/[^0-9.]/g,''));
</script>
<!-- Event snippet for Lead conversion page -->
<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-1070747480/JPisCJyftGMQ2J7J_gM',
      'value': value,
      'currency': 'USD'
  });
</script>

<!-- Event snippet for Lead conversion page -->
<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-951954227/KbSOCOiJ1FoQs9b2xQM',
      'value': value,
      'currency': 'USD'
  });
</script>

<!-- Bing conversion tracking code - 2/1/2018 -->
<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5039997"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script><noscript><img src="//bat.bing.com/action/0?ti=5039997&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>
<!-- End of Bing conversion tracking code -->
<?php } ?>
