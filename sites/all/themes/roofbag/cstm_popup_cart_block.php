<style type="text/css">
#map-canvas-modal, #map-canvas{
  width:auto;
	height:350px;
  padding-bottom:5px}
</style>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">RoofBag Store Address</div>
      <div class="modal-body">
      	<div id="map-canvas-modal"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn_map_close" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAchBklREVr4C8W-4CPSR__HtGiqLp5FsM"></script>
<script type="text/javascript">
// code for Pickup at Factory View Map code
jQuery('#myModal').on('shown.bs.modal', function () {
	var storeArray = new Array(["32.568235", "-116.955641", "<p style='border-radious:10px;color:black;padding:5px;'>Roofbag.com Factory, 1533 Olivella Way, San Diego, California 92154</p>"]);
	var myOptions = {
	  center: new google.maps.LatLng(storeArray[0][0], storeArray[0][1]),
	  zoom: 14,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
  	};

  	var map = new google.maps.Map(document.getElementById("map-canvas-modal"), myOptions); 
  	for (i = 0; i < storeArray.length; i++) {  
		marker = new google.maps.Marker({
    		position: new google.maps.LatLng(storeArray[i][0], storeArray[i][1]),
    		map: map
	    });
	 
	
	    var infowindow = new google.maps.InfoWindow({
			content: storeArray[i][2]		
		});
		infowindow.open(map, marker); 	        
  	}
  	google.maps.event.addDomListener(window, 'load');
	var mapNode = map.getDiv();
    jQuery('#map-canvas-modal').append(mapNode);

});

jQuery( ".btn_map_close" ).click(function() {
  jQuery( "#myModal" ).fadeOut( "slow", function() {
    // Animation complete.
  });
});
</script>
<div id="cstm_popup_cart_block_contianer" class="cart-img-desp-sattik-show clearfix" style="display:none;">
    <?php if (!isset($_SESSION["savedzip"])) { ?>
        <div class="alert alert-info" role="alert">Enter Shipping Zip Code & Proceed to Checkout</div>
    <?php } ?>
    <form action="/<?php echo current_path(); ?>" method="POST">
	<script>var no_of_cart_items = 0;</script>
	<?php
	$sub_total = 0;
	$counter_items = 0;
	foreach ($items as $item) {
	    $item_total = $item->price * $item->qty;
	    $sub_total = $sub_total + $item_total;
	    if (isset($item->uc_product_image['und'])) {
		$img_url = $item->uc_product_image['und'][0]['uri'];
		$style = 'thumbnail';
	    }
	    ?>
    	<script>no_of_cart_items += <?php echo $item->qty; ?>;</script>
    	<div class="inner-wapper-cart-imgs">
    	    <h2 class="cart-prdct-title">
		    <?php echo $item->title; ?> 
		    <?php if ($item->nid != 294) { ?>
			<a class="cart-close" href="/<?php echo current_path(); ?>/?remove_id=<?php echo $item->cart_item_id; ?>">x</a>
		    <?php } ?>
    	    </h2>
    	    <div class="container" style="margin-bottom: 30px;">
    		<div class="row">


    		    <div class="left-cart-img">
			    <?php if (isset($item->uc_product_image['und'])) { ?>
				<img class="img-responsive" src="<?php print image_style_url($style, $img_url) ?>">
			    <?php } ?>
    		    </div>
    		    <div class="midller-content-sec-cus">
			    <?php
			    if ($item->nid != 1 && $item->nid != 289 && $item->nid != 292 && $item->nid != 293) {
				if (isset($item->body['und']['0']['summary']))
				    echo $item->body['und']['0']['summary'];
			    }
			    ?>
			    <?php
			    if ($item->nid == 1 || $item->nid == 289 || $item->nid == 292 || $item->nid == 293) {
				if (isset($item->data["attributes"][3]) || isset($item->data["attributes"][1]) || isset($item->data["attributes"][2])) {
				    ?>
	    			<ul>
					<?php
					if (isset($item->data["attributes"][3])) {
					    if ($item->data["attributes"][3] == 5) {
						echo "<li>11 cubic feet</li>";
					    } else if ($item->data["attributes"][3] == 6) {
						echo "<li>15 cubic feet</li>";
					    }
					}
					if (isset($item->data["attributes"][1])) {
					    if ($item->data["attributes"][1] == 1) {
						echo "<li>(2) Straps for Rack Included</li>";
					    } else if ($item->data["attributes"][1] == 2) {
						echo "<li>(2) Straps for No Rack Included</li>";
					    }
					}
					if (isset($item->data["attributes"][2])) {
					    if ($item->data["attributes"][2] == 3) {
						echo "<li>Color, Black</li>";
					    } else if ($item->data["attributes"][2] == 4) {
						echo "<li>Color, Gray</li>";
					    }
					}
					?>
	    			</ul>
				    <?php
				}
			    }
			    ?>
    			<input type="hidden" value="<?php echo $item->cart_item_id; ?>" name="cart_item_id[]" />
    			<input type="hidden" value="Qty Submit" name="qty_submit" />
    		    </div>
    		    <!--<div class="input-group qunty-number-sec">
    			<span class="input-group-btn">
    			    <button type="button" class="btn btn-default qty-number numbtn" data-type="minus" data-field="cart_item_qty">
    				<span class="glyphicon glyphicon-minus"></span>
    			    </button>
    			</span>
    			<input name="cart_item_qty[]" class="form-control item_qty_field numfield" value="<?php echo $item->qty; ?>" min="1" max="10" type="text">
    			<span class="input-group-btn">
    			    <button type="button" class="btn btn-default qty-number numbtn" data-type="plus" data-field="cart_item_qty">
    				<span class="glyphicon glyphicon-plus"></span>
    			    </button>
    			</span>
    		    </div>-->
			<?php // if ($counter_items++ == count($items) - 1) {    ?>
    		    <!--			<div class="bototm-btn-go-cus">
    						<input class="go-popup-bottm-cus" type="submit" name="qty_submit" value="Update Cart" >
    					    </div>-->
			<?php // }    ?>

    		</div>
    	    </div>
    	    <div class="clear cart-btm-new">
    		<div class="midller-content-sec-cus">
    		    <p class="product-qty-field">
			    <?php if ($item->nid != 294) { ?>
				<strong>QTY</strong> <input type="number" name="cart_item_qty[]" class="input-cus-velue-style cart_qty_inputs" value="<?php echo $item->qty; ?>" />
			    <?php } ?>
    <!--<button class="btn btn-default submit_cart_item_qty" type="button"><span class="glyphicon glyphicon-ok"></span></button>-->
    		    </p>
    		</div>
    		<div class="left-cart-img">
    		    <p class="price-botm-cart-sec">
    			<strong class=""><?php
				if ($item->nid != 294)
				    echo "$" . (float) number_format((float) $item->price, 2, '.', ''); 
    				//echo "$" . number_format((float) $item->price, 2, '.', '');  //kp
				    
				else
					echo "Included";
				?></strong>
    		    </p>
    		</div>
    	    </div>
    	    <div class="designed-list">
		    <?php
		    if ($item->nid == 1 || $item->nid == 289 || $item->nid == 292 || $item->nid == 293) {
			if (isset($item->body['und']['0']['summary']))
			    echo $item->body['und']['0']['summary'];
		    }
		    ?>
    	    </div>
    	</div>
	    <?php
	}
	?>
    </form>
    <div class="totel-sub-main-info">
	<div class="inner-pro-cart-deatil-sec with-border">
	    <div class="order-totel-txt">
		<strong id="order_subtotal_cart" class="">
			<?php //echo '$'.$sub_total; //kp ?>
			<?php echo "$" . number_format((float) $sub_total, 2, '.', ''); ?>
		</strong>
	    </div>
	    <div class="right-totle-sub">
		<strong>Sub-total</strong>
	    </div>
	</div>
	<div class="inner-pro-cart-deatil-sec clearfix" style="clear: both">

	    <div class="right-totle-sub shipping_inner_wrap">
		<?php 
			/*echo '<pre>';
			print_r($_SESSION); 
			echo '</pre>';*/  //kp
		?>
 <?php //if (empty($_SESSION['sdata'])) {  //webplanex?>
		<div id="cart_shipping_selected" class="shipping_selected" <?php if (!isset($_SESSION['sdata']) && empty($_SESSION['sdata'])) echo 'style="display: none;"'; ?> >
		    <?php
		    if (empty($_SESSION['savedzip'])) {//kp
		    	if (isset($_SESSION['sdata']) && !empty($_SESSION['sdata'])) {
					//echo $_SESSION['sdata'];
		    	}
	    	}//kp
		    ?>
		</div>
       <?php //}  //webplanex?>
			
		<div id="cart_zip_flash">
		
		<!-- Code Comment By WebPlanex -->
		<!--<p class='ship_to_txt'>Enter Shipping Zip Code & Proceed to Checkout</p>-->
		<?php if (!isset($_SESSION['savedzip']) && empty($_SESSION['savedzip'])) { ?>
				<div class="input-group boots-cus-style-butn">
				<input id="cart_postal_code" style="text-transform:uppercase" autocomplete="off" name="panes[delivery][delivery_postal_code]" size="30" maxlength="10" class="form-text ajax-processed progress-disabled form-control" placeholder="Enter delivery zip or postal code" type="text" value="<?php if (isset($_SESSION["savedzip"])) echo $_SESSION["savedzip"]; ?>">
				</div>
		<?php  } else { ?>
						<input id="cart_postal_code" style="text-transform:uppercase" autocomplete="off" name="panes[delivery][delivery_postal_code]" size="30" maxlength="10" class="form-text ajax-processed progress-disabled form-control" placeholder="Enter delivery zip or postal code" type="text" value="<?php if (isset($_SESSION["savedzip"])) echo $_SESSION["savedzip"]; ?>">
						<input id="cart_postal_code" style="text-transform:uppercase" autocomplete="off" name="panes[delivery][delivery_postal_code]" size="30" maxlength="10" class="form-text ajax-processed progress-disabled form-control" placeholder="Enter delivery zip or postal code" type="hidden" value="<?php if (isset($_SESSION["savedzip"])) echo $_SESSION["savedzip"]; ?>">
			<?php } //webplanex ?>
				</div>
			    </div>
			    <div class="clearfix clear"></div>
		</div>
	<div class="inner-search-boxes-zip-code">
	    <div id="loaderimg1" class="loaderimg_wraper for-popup-wapper" style="display:none;"><img id="imgloader" src="<?php echo $base_url . '/sites/all/themes/roofbag/images/'; ?>loader-new.svg" ></div>

	    <?php
	    if (isset($_SESSION['savedzip']) && !empty($_SESSION['savedzip'])) {
	    	echo "<p class='lbl_shipping_option_selected'><b>Shipping option selected<b></p>";
    	}
	    if (isset($_SESSION['parent_html']) && !empty($_SESSION['parent_html'])) {

			echo '<div id="cart_postal_code_result" class="shipping_cal">' . $_SESSION['parent_html'] . '</div>';
			?>
			<script type="text/javascript">
        // SELECTED METHOD SHOW AT FIRST 
        var sel_ele = "<div class='form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio selected-quote'>" + jQuery('#cart_postal_code_result').find(".selected-quote").html() + "</div>";
        //alert(sel_ele);
        jQuery('#cart_postal_code_result').find(".selected-quote").remove();
        jQuery('#cart_postal_code_result').prepend(sel_ele);
        //jQuery('#cart_postal_code_result').prepend(sel_ele); //kp 			
			</script>
			<?php
	    } else {
			echo '<div id="cart_postal_code_result" class="shipping_cal"></div>';
	    }
	    ?>
	    <div id="border-green-totle-botom" class="border-green-totle-botom" <?php if (!isset($_SESSION['parent_html'])) echo 'style="display: none;"'; ?>>
		<div class="order-totel-txt"> 
		    <strong>Order Total</strong>
		</div>
		<?php
		$order_total_amount = $sub_total;
		if (isset($_SESSION['service_rate']) && !empty($_SESSION['service_rate'])) {
		    $order_total_amount = $sub_total + $_SESSION['service_rate'];
		}
		?>
		<input type="hidden" id="order_sub_total_amount" value="<?php echo $sub_total; ?>" >
		<input type="hidden" id="order_total_amount" value="<?php echo $order_total_amount; ?>" >
		<div class="right-totle-sub"><strong id="order_formated_total_amount" class="order_formated_total_amount "><?php echo setRBAmount($order_total_amount); ?></strong> </div>
	    </div>
	    <div class="clearfix clear"></div>
	    <div id="proceed_to_checkout_btn" class="btn-yellowbig <?php if (!isset($_SESSION['parent_html'])) echo "disabled_cart_btn"; ?> panel-body" style="margin-top:10px;">
		<a href="/cart/checkout" class="node-add-to-cart btn btn-success form-submit">Proceed to checkout</a>
	    </div>

	    <!-- Added by KP -->
	    <a href="javascript:void(0)" onclick="askUserMobileNumber(this);"> 
		<div class="text_me_link_container">Text me a link to this page</div>
	    </a>

	</div>
	<div class="clearfix clear"></div>
    </div>

</div>
