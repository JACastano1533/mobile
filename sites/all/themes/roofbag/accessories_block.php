<?php
$type = "product";
//$nodes = node_load_multiple(array(276, 278, 287, 286, 285, 284, 295), array('type' => $type, 'status' => 1,));
$nodes = node_load_multiple(array(276, 278, 297, 298, 286, 285, 284, 295, 299), array('type' => $type, 'status' => 1,));
?>
<div id="accessories_panel" class="">
    <div class="add-accessories">
	<?php
	$accessories_available = 0;
	foreach ($nodes as $node) {
	    $accessory_exists = false;
	    foreach ($items AS $item) {
		if ($item->nid == $node->nid) {
		    $accessory_exists = true;
		}
	    }
	    if (!$accessory_exists) {
		$accessories_available++;
	    }
	}
	if ($accessories_available) {
	?>
	<!--<h1>Add Recommended Accessories</h1>-->
	<?php
	}
	$accessories_available = 0;
	foreach ($nodes as $node) {
	    $accessory_exists = false;
	    foreach ($items AS $item) {
		if ($item->nid == $node->nid) {
		    $accessory_exists = true;
		}
	    }
	    if (!$accessory_exists) {
		$accessories_available++;
		_get_products_data($node->nid, $node);
	    }
	}
	if (!$accessories_available) {
	    echo "<p class='no_accessory_available'>You have added all available accessories to your cart.</p>";
	}
	?>
    </div>
</div>
<?php

function _get_products_data($nid, $node) {
    global $base_url;
    setlocale(LC_MONETARY, "en_US");

    $add_to_cart = array(
	'#theme' => 'uc_product_add_to_cart',
	'#form' => drupal_get_form('uc_product_add_to_cart_form_' . $nid, $node)
    );
    if (isset($node->uc_product_image['und'])) {
	$imge_url = $base_url . "/sites/default/files/" . $node->uc_product_image['und'][0]['filename'];
	$img_width = $node->uc_product_image['und'][0]['width'];
	$img_height = $node->uc_product_image['und'][0]['height'];
    }

    $body_full_formated_text = "";
    $do_need_formated_text = "";
    if (isset($node->body['und'])) {
	$body_full_formated_text = $node->body['und']['0']['value'];
    }
    if (isset($node->field_do_i_need_this['und'])) {
	$do_need_formated_text = $node->field_do_i_need_this['und']['0']['value'];
    }
    ?>
    <div class="accessories-wrapper main-row-porducts">
        <div id="node-<?php echo $nid; ?>">
			<div class="accessories-prdct-sect">
				<h2><?php echo $node->title; ?></h2>
				<div class="accessories-prdct-img">
					<?php if (isset($node->uc_product_image['und'])) echo '<img src="' . $imge_url . '" width="' . $img_width . '" height="' . $img_height . '" alt="" />'; ?>
				</div>
				<div class="accessories-prdct-details">
					<?php if (isset($node->body['und'])) echo $node->body['und']['0']['summary']; ?>
				</div>
				<?php if( $node->nid != 276 && $node->nid != 278 ){ ?>
				<div class="accessories-prdct-price"><?php echo setRBAmount($node->price); ?></div>
				<?php } ?>
			</div>

			<?php 
				// echo '<pre>';
				// print_r($node->attributes);
				// echo '</pre>';
				
				if( !empty( $node->attributes ) ){
					foreach( $node->attributes as $nda ){
						if($nda->name=='Size')
      					{
      						$countSizeOptions = 1;
      						foreach($nda->options as $nda_options)
        					{ ?>
								<div class="col-sm-12 border-ibc check-selected-size check-size-node-<?php print $nda_options->nid; ?>-<?php print $nda_options->oid; ?> <?php print ($countSizeOptions == 1) ? 'custom-active' : ''; ?>" data-node="node-1">
									<div class="main-row-cu-ft-wapper-info">
										<div class="top-cu-ft-info-rate">
											<span class="opt_name"><?php print $nda_options->name; ?></span>
											<span class=""><?php print setRBAmount(($node->price + $nda_options->price)); ?></span>
										</div>
										<div class="opt_sub_text">For all cars</div>
									</div>
								</div>
        					<?php 
        						$countSizeOptions++;
        					}
      					}
					}
				}
			?>

	    <?php
	    $added_to_cart = false;
	    $items = uc_cart_get_contents();
	    foreach ($items as $item) {
		if ($item->nid == $node->vid) {
		    $added_to_cart = true;
		}
	    }
	    if ($added_to_cart) {
		echo '<button class="added_to_cart_btn">Added to cart <span class="icon glyphicon glyphicon-ok" aria-hidden="true"></span></button>';
	    } else {
		print drupal_render($add_to_cart);
	    ?>

<!--            <div class="input-group qunty-number-sec">
    	    <span class="input-group-btn">
    		<button type="button" class="btn btn-default qty-number numbtn" data-type="minus" data-field="cart_item_qty">
    		    <span class="glyphicon glyphicon-minus"></span>
    		</button>
    	    </span>
    	    <input name="cart_item_qty" class="form-control item_qty_field numfield" value="1" min="1" max="10" type="text" data-node="node-<?php echo $nid; ?>">
    	    <span class="input-group-btn">
    		<button type="button" class="btn btn-default qty-number numbtn" data-type="plus" data-field="cart_item_qty">
    		    <span class="glyphicon glyphicon-plus"></span>
    		</button>
    	    </span>
            </div>-->
	    
	    <!--<div class="col-sm-12 price_div" style="margin-bottom: 5px;"><?php // echo setRBAmount($node->price); ?></div>-->
	    <div class="btn-yellowbig"><button class="node-add-to-cart btn btn-success form-submit" onclick="clickAccessoriesCartButton(<?php echo $node->vid.", '#collapseFive_panel'"; ?>)" type="button" id="" name="op" value="Add to cart"><span class="icon glyphicon"  aria-hidden="true"></span> Add to cart</button></div>
	    <?php } ?>
	</div>
	<div class="main-wapper-bottom-two-cus-btns">
	    <div class="bottom-two-cus-btns">
	    <a class="pro-desp" data-toggle="modal" data-target="#myModal<?php echo $nid; ?>" href="javascript:void(0);">Description</a>
		<?php description_model($nid, $body_full_formated_text); ?>
	    <a class="pro-do-it" data-toggle="modal" data-target="#myModal_do_need<?php echo $nid; ?>" href="javascript:void(0);">Do I need this?</a>
		<?php do_need_model($nid, $do_need_formated_text); ?>
	    </div>
	</div>
    </div>


    <?php
}

function description_model($nid, $body_full_formated_text) {
    ?>
    <div class="modal fade" id="myModal<?php echo $nid; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
    	<div class="modal-content">
    	    <div class="modal-header">
    		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    		<h4 class="modal-title">Description</h4>
    	    </div>
    	    <div class="modal-body">
    		<div><?php echo $body_full_formated_text; ?> </div>
    	    </div>
    	</div>
        </div>
    </div>
    <?php
}

function do_need_model($nid, $body_full_formated_text) {
    ?>
    <div class="modal fade" id="myModal_do_need<?php echo $nid; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
    	<div class="modal-content">
    	    <div class="modal-header">
    		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    		<h4 class="modal-title">Do I need this?</h4>
    	    </div>
    	    <div class="modal-body">
    		<div><?php echo $body_full_formated_text; ?> </div>
    	    </div>
    	</div>
        </div>
    </div>
    <?php
}
