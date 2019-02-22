<?php global $base_url;

?>
<div class="node-wrapper region region-content">
    <div class="panel-body">
        <div id="select_your_carrier_section" class="carrier-sect-title">Select your carrier</div>
        <div class="node_container">
          <?php
          foreach ($nodes as $node) {
            $node_id = "node-" . $node->nid;
            if ($node->nid == 289)
              $hide = "style='display:none;'";
            else
              $hide = "";
            $thumbnail_html = '';
            $count_image = 0;
            if ($node->uc_product_image) {
              foreach ($node->uc_product_image['und'] as $image_product) {
                $image_product_path = file_create_url($image_product['uri']);
                if ($count_image == 0) {
                  ?>
                    <div class="node-prod-images" data-node="<?php echo $node_id; ?>" <?php echo $hide; ?>>
                    <div id="<?php echo $node_id . 'product-image' . $count_image . 'main'; ?>" class="field field-name-uc-product-image field-type-image field-label-above" >
                        <div class="field-label"></div>
                        <div class="field-items">
                            <div class="field-item even">
                                <img typeof="foaf:Image" class="img-responsive" src="<?php echo $image_product_path; ?>" width="850" height="600" alt="">
                            </div>
                        </div>
                    </div>
                  <?php
                } else {
                  ?>
                    <div style="display:none;" id="<?php echo $node_id . 'product-image' . $count_image . 'main'; ?>" class="field field-name-uc-product-image field-type-image field-label-above">
                        <div class="field-label">
                        </div>
                        <div class="field-items">
                            <div class="field-item even">
                                <img typeof="foaf:Image" class="img-responsive" src="<?php echo $image_product_path; ?>" width="850" height="600" alt="">
                            </div>
                        </div>
                    </div>

                  <?php
                }
                if ($count_image == 0) {
                  $fun_color = "black";
                  $active = 'active';
                } else if ($count_image == 1) {
                  $fun_color = "gray";
                  $active = '';
                }
                $dynamic_id = $node_id . 'product-image' . $count_image;
                $thumbnail_html .= '<li class="slide-0 '.$active.'" style="width: 117px;float:left;"><a href="javascript:void(0)" id="' . $dynamic_id . '" onclick="showImageProduct(this.id, \'' . $node_id . '\', \'' . $fun_color . '\')"><img typeof="foaf:Image" class="img-responsive" src="' . $image_product_path . '"  width="121" height="75" alt="" title="Cross Country Car Top Carrier image "></a></li>';
                $count_image++;
              }
              ?>

                <div style="display:none;" class="field field-name-uc-product-image field-type-image field-label-above">
                    <div class="field-label"></div>
                    <div class="field-items">
                        <div class="field-item even">
                            <img id="<?php echo $node_id; ?>_change_image" typeof="foaf:Image" class="img-responsive" src="https://www.roofbag.com/Images/Products/RoofBag-CC-Car-Top-Carrier-Bk.jpg" width="850" height="600" alt="">
                        </div>
                    </div>
                </div>

                <!-- 6/17/2018 - remove Select Color section - will sell only black, which is default -->
                <!--<div class="default-panel">-->
                <!--<div class="default-panel-title">Select color</div>-->
                <!--<div class="default-panel-body">-->
                <!--<div class="row bottom-node-images-text">-->
                <!--<div class="col-md-12" >-->
                <!--<div class="wrapper"> -->
                <!-- <ul class="select_color" style="list-style: none;">
								<?php echo $thumbnail_html; ?>
								</ul> -->
                <!--</div>-->
                <!--</div>-->
                <!--</div>-->
                <!--</div>-->
                <!--</div>-->
                </div>
              <?php
            }
          }
          ?>
            <div class="default-panel">
                <div class="default-panel-title">Select Model</div>
                <div class="default-panel-body">
                    <div class="roofbag_type_button divided-sections">
                      <?php
                      foreach ($nodes as $node) {
                        $node_id = "node-" . $node->nid;
                        if ($node->nid == 289)
                          $custom_active = "";
                        else
                          $custom_active = "custom-active";
                        ?>
                          <div class="roofbag_type_button_container <?php echo $custom_active; ?>" data-node="<?php echo $node_id; ?>">
                              <p class="desp-prod-node-1"><b><?php echo $node->title; ?></b></p>
                            <?php echo $node->body['und']['0']['summary']; ?>
                          </div>
                      <?php } ?>
                    </div>
                </div>
            </div>

          <?php
          foreach ($nodes as $node) {

            $size_html = '';
            $strap_html = '';
            $color_html = '';
            $node_id = "node-" . $node->nid;
            if ($node->nid == 289)
              $hide = "style='display:none;'";
            else
              $hide = "";
            //		$added_to_cart = false;
            //		$items = uc_cart_get_contents();
            //		foreach ($items as $item) {
            //		    if ($item->nid == $node->vid) {
            //			$added_to_cart = true;
            //		    }
            //		}
            //		if ($added_to_cart) {
            //		    echo '<div class="added_to_cart_div"><button class="added_to_cart_btn">Added to cart <span class="icon glyphicon glyphicon-ok" aria-hidden="true"></span></button></div>';
            //		} else {
            $add_to_cart = array(
              '#theme' => 'uc_product_add_to_cart',
              '#form' => drupal_get_form('uc_product_add_to_cart_form_' . $node->nid, $node)
            );
            print drupal_render($add_to_cart);

            foreach ($node->attributes as $nd) {

              if ($nd->name == 'Size') {

                $count_size = 1;
                $size_html .= '<div class="default-panel-title select_size_txt" >Select size</div>';
                foreach ($nd->options as $options) {
                  $option_price = $node->price + $options->cost;
                  if ($options->oid == 5) {
                    $options->name = "11 cubic feet";
                    $sub_opt_txt = 'For all cars'.'<br>';
                    //$sub_opt_txt .= 'Fits 3-4 medium sized suitcases'; //kp
                    $sub_opt_txt .= 'Size is equivalent to 3-4 medium suitcases';
                  } else if ($options->oid == 6) {
                    $options->name = "15 cubic feet";
                    $sub_opt_txt = 'For full-size sedans, SUVs and vans'.'<br>';
                    //$sub_opt_txt .= 'Fits 4-5 medium sized suitcases'; //kp
                    $sub_opt_txt .= 'Size is equivalent to 4-5 medium suitcases';
                  }
                  $size_html .= '<div class="col-sm-12 border-ibc check-selected-size check-size' . $count_size . '" data-node="' . $node_id . '">';
                  $size_html .= '<div class="main-row-cu-ft-wapper-info"><div class="top-cu-ft-info-rate">';
                  $size_html .= '<span class="opt_name">' . $options->name . '</span>';
                  $size_html .= '<span class="">$' . $option_price . '</span>';
                  $size_html .= '</div>';
                  $size_html .= '<div class="opt_sub_text">' . $sub_opt_txt . '</div></div>';
                  $size_html .= '</div>';

                  $count_size++;
                }
              } else if ($nd->name == 'Strap') {

                $count_strap = 1;
                $strap_html = '<div class="default-panel-title select_type_straps_txt">My car has:</div>';
                $strap_html .= '<div class="strap_div">';
                foreach ($nd->options as $options) {
                  if ($options->oid == 1) {
                    $options->name = "Rack";
                    $sub_opt_txt = 'Side rails, cross bars, or carrier basket';
                  } else if ($options->oid == 2) {
                    $options->name = "No Rack";
                    $sub_opt_txt = 'Bare roof or flush side rails';
                  }

                  $strap_html .= '<div class="col-sm-12 border-ibc check-selected-strap check-strap' . $count_strap . '" data-node="' . $node_id . '">';
                  $strap_html .= '<div class="main-row-cu-ft-wapper-info"><div class="top-cu-ft-info-rate">';
                  $strap_html .= '<span class="opt_name">' . $options->name . '</span>';
                  $strap_html .= '</div>';
                  $strap_html .= '<div class="opt_sub_text">' . $sub_opt_txt . '</div></div>';
                  $strap_html .= '</div>';

                  $count_strap++;
                }
                $strap_html .= "</div>";
              } else if ($nd->name == 'Color') {
                $count_color = 1;
                $color_html .= '<p class="gray_txt">Select carrier color</p>';
                foreach ($nd->options as $options) {
                  if ($options->oid == 3)
                    $cls = 'clr_black';
                  else if ($options->oid == 4)
                    $cls = 'clr_gray';
                  $color_html .= '<div class="col-sm-12 border-ibc  ' . $cls . ' check-selected-color check-color' . $count_color . '" data-node="' . $node_id . '">' . $options->name . '</div>';
                  $count_color++;
                }
              }
            }
            $loader_img = '<div class="loaderimg_wraper opt_loaderimg" style="display:none;">
      <img id="imgloader" src="sites/all/themes/roofbag/images/loader-new.svg" />
      </div>';
            ?>

              <!-- Three Buttons Section -->
              <!--    		<div class="product_detail_btns_container">
					<div class="margin-cus-0 custom-inner-margin product_detail_btns">
					    <div class="col-sm-12 border-ibc botton-info-style-aac">
						<a data-toggle="modal" data-target="#myModal_<?php echo $node->nid; ?>" href="javascript:void(0);">Product Details</a>
						<div class="modal fade" id="myModal_<?php echo $node->nid; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						    <div class="modal-dialog" role="document">
							<div class="modal-content">
							    <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">Product Details</h4>
							    </div>
							    <div class="modal-body">
								<div><?php echo $node->body['und']['0']['value']; ?> </div>
							    </div>
							</div>
						    </div>
						</div>
					    </div>
					</div>

					<div class="margin-cus-0 custom-inner-margin product_detail_btns">
					    <div class="col-sm-12 border-ibc botton-info-style-aac">
						<a id="doesattach" href="javascript:void(0);">How does it attach?</a>
					    </div>
					</div>

					<div class="last-row margin-cus-0 custom-inner-margin product_detail_btns">
					    <div class="col-sm-12 border-ibc botton-info-style-aac">

						<a target="_blank"  href="/sites/default/files/RB.com-INSTRUCTIONS-10.15.15%281%29.pdf">Installation Manual</a>
					    </div>
					</div>
				    </div>-->
            <?php
            $container_roofbag = ".node_container";
            $reset_recommended = "";

            $questions_div = "";

            $curr_day = date('D');
            if ($curr_day != "Sat" && $curr_day != "Sun") {
              $curr_time = date('H:i');
              if ($curr_time >= "07:30" && $curr_time < "16:00") {
                //			    $questions_div = '<a href="javascript:void(0)" aria-hidden="true" data-toggle="collapse" id="tap_to_call" data-target=".navbar-collapse" onclick="showHeaderCartBar()"><div class="questions_link_container"><i class="fa fa-phone"></i> Questions? Tap to call us - our team is here to help</div></a>';
                $questions_div = '<a href="tel:800-276-6322" aria-hidden="true"  ><div class="questions_link_container"><i class="fa fa-phone"></i> Questions? Tap to call us - our team is here to help</div></a>';
              }
            }
            $clear_recommended_carriers = '<div class="clear_recommended_carriers_container"></div>';

            $add_to_cart_btn = '<div class="btn-yellowbig"><button class="node-add-to-cart btn btn-success form-submit" onclick="clickCartButton(\'.node_container\', \'#collapseFive_panel\');" type="button" id="" name="op" value="Add to cart"><span class="icon glyphicon"  aria-hidden="true"></span> Add to cart</button></div>';
            echo '<div class="attributes_cstm_wrapper clearfix" ' . $hide . ' data-node="' . $node_id . '">' . $loader_img .'<div class="default-panel">'. $size_html.'</div>'.
              ' <div class="default-panel">' . $strap_html . '</div>' . $questions_div . $clear_recommended_carriers . $add_to_cart_btn . '</div>';
            ?>

            <?php
            //		}
          }
          ?>
        </div>
        <!--	<section id="block-block-7" class="block block-block contextual-links-region clearfix">

              <div class="footer-icon-wpper">
            <div class="cus-icon-footer-cus">
                <i class="fa fa-check-circle-o" aria-hidden="true"></i> In stock<br>
                <i class="fa fa-check-circle-o" aria-hidden="true"></i> 2-year warranty<br>
                <i class="fa fa-check-circle-o" aria-hidden="true"></i> 30-day returns
            </div>
              </div>

          </section>-->
    </div>
</div>