<?php  
global $base_url;
if(arg('1')=='1')
{
	
	?>
	<style>
  #uc_product_add_to_cart_form-1-attributes
  {
   display:none;
 }

 .field-label
 {
   display:none;
 }
 .list-price
 {
  display:none;
}
.cost
{
  display:none;
}
.display-price
{
  display:none;
}
#edit-actions
{
  display:none;
}
.field-name-body
{
  display:none;
}
.form-type-uc-quantity
{
	display:none;
}

</style>

<div id="node-1" class="panel-body">
  <div><p class="desp-prod-node-1"><b><?php echo $node->title; ?></b></p></div>
  <div class="inner-detail-node-1"><?php echo $node->body['und']['0']['summary']; ?> </div>
  <div class="container node-prod-images">

    <div class="row">
      <div style="display:none;"><?php  print render($content); ?></div>
      <?php
      $size_html='';
      $strap_html='';
      $color_html='';
      $thumbnail_html='';
      $count_image=0;
      foreach($node->uc_product_image['und'] as $image_product)
      {
        $image_product_path=file_create_url($image_product['uri']);
        if($count_image==0){
          ?>
          <div id="<?php echo 'product-image'.$count_image.'main'; ?>" class="field field-name-uc-product-image field-type-image field-label-above">
            <div class="field-label">Image:&nbsp;</div>
            <div class="field-items">
              <div class="field-item even">
                <img typeof="foaf:Image" class="img-responsive" src="<?php echo $image_product_path; ?>" width="850" height="600" alt="">
              </div>
            </div>
          </div>
          <?php	
        }
        else
        {
         ?>
         <div style="display:none;" id="<?php echo 'product-image'.$count_image.'main'; ?>" class="field field-name-uc-product-image field-type-image field-label-above">
          <div class="field-label">Image:&nbsp;
          </div>
          <div class="field-items">
            <div class="field-item even">
              <img typeof="foaf:Image" class="img-responsive" src="<?php echo $image_product_path; ?>" width="850" height="600" alt="">
            </div>
          </div>
        </div>

        <?php	
      }
      $dynamic_id='product-image'.$count_image;
      $thumbnail_html.='<li class="slide-0 active" style="width: 117px;float:left;"><a href="javascript:void(0)" id="'.$dynamic_id.'" onclick="showImageProduct(this.id)"><img typeof="foaf:Image" class="img-responsive" src="'.$image_product_path.'"  width="121" height="75" alt="" title="Cross Country Car Top Carrier image "></a></li>';
      $count_image++;
    }
    ?>
    <div class="row bottom-node-images-text">
      <div class="col-md-12" >
        <div class="wrapper">
          <ul style="list-style: none;">

            <?php echo $thumbnail_html; ?>       
          </ul>
        </div>
      </div>



    </div>
    <?php
    foreach($node->attributes as $nd)
    {

      $size_html = '';
      if($nd->name=='Size')
      {
        $count_size=1;

        foreach($nd->options as $options)
        {
          $option_price =  $node->price + $options->cost;
          if($options->oid == 5) {
	      $options->name = "11 cubic feet";
        $sub_opt_txt = 'For all cars'.'<br>';
	      //$sub_opt_txt .= 'Fits 3-4 medium sized suitcases'; //kp
        $sub_opt_txt .= 'Size is equivalent to 3-4 medium suitcases';
	  } else if($options->oid == 6) {
	      $options->name = "15 cubic feet";
        $sub_opt_txt = 'For full-size sedans, SUVs and vans'.'<br>';
	      //$sub_opt_txt .= 'Fits 4-5 medium sized suitcases'; //kp
        $sub_opt_txt .= 'Size is equivalent to 4-5 medium suitcases';
	  }
	  
          $size_html.='<div class="col-sm-12 border-ibc check-selected-size check-size'.$count_size.'">';
          $size_html.='<div class="main-row-cu-ft-wapper-info"><div class="top-cu-ft-info-rate">';
		  $size_html.='<span class="opt_name">'.$options->name.'</span>';
          $size_html.='<span class="opt_price">$'.$option_price.'</span>';
		  $size_html.='</div>';
          $size_html.='<div class="opt_sub_text">'.$sub_opt_txt.'</div></div>';
          $size_html.='</div>';

          $count_size++;
        }
      }
      else if($nd->name=='Strap')
      {

        $count_strap=1;
        $strap_html ='<div id="strap_div" style="display:none;">';
        foreach($nd->options as $options)
        {
	    if($options->oid == 1) {
		$options->name = "Straps for Rack";
		$sub_opt_txt = 'For side rails, cross bars, or carrier basket';
	    }
	    else if($options->oid == 2) {
		$options->name = "Straps for No Rack";
		$sub_opt_txt = 'Bare roof or flush side rails';
	    }
	    
          $strap_html.='<div class="col-sm-12 border-ibc check-selected-strap check-strap'.$count_strap.'">';
          $strap_html.='<div class="main-row-cu-ft-wapper-info"><div class="top-cu-ft-info-rate">';
		  $strap_html.='<span class="opt_name">'.$options->name.'</span>';
		  $strap_html.='</div>';
          $strap_html.='<div class="opt_sub_text">'.$sub_opt_txt.'</div></div>';
          $strap_html.='</div>';

          $count_strap++;
        }
        $strap_html .="</div></br>";
      }
      else if($nd->name=='Color')
      {
        $count_color=1;
        foreach($nd->options as $options)
        {
          if($options->oid == 3) $cls = 'clr_black';
          else if($options->oid == 4) $cls = 'clr_gray';
          $color_html.='<div class="col-sm-12 border-ibc  '.$cls.' check-selected-color check-color'.$count_color.'">'.$options->name.'</div>';
          $count_color++;
        }
      }
    }
    $loader_img = '<div id="opt_loaderimg" class="loaderimg_wraper" style="display:none;">
    <img id="imgloader" src="'.$base_url.'/sites/all/themes/roofbag/images/loader-new.svg" />
    </div>';
    echo '<div class="attributes_cstm_wrapper clearfix">'.$loader_img.$size_html.$strap_html.$color_html.'</div>';
    ?>

    <div class="input-group qunty-number-sec">
      <span class="input-group-btn">
        <button type="button" class="btn btn-default btn-number numbtn" disabled="disabled" data-type="minus" data-field="quant[1]">
          <span class="glyphicon glyphicon-minus"></span>
        </button>
      </span>
      <input type="text" name="quant[1]" class="form-control input-number numfield" value="1" min="1" max="10">
      <span class="input-group-btn">
        <button type="button" class="btn btn-default btn-number numbtn" data-type="plus" data-field="quant[1]">
          <span class="glyphicon glyphicon-plus"></span>
        </button>
      </span>
    </div>
    <div class="col-sm-12 price_div" style="margin-bottom: 5px;">$<span><?php echo number_format($node->price, 2, '.', '') ; ?></span></div>
    <div class="btn-yellowbig"><button class="node-add-to-cart btn btn-success form-submit" onclick="clickCartButton()" type="button" id="" name="op" value="Add to cart"><span class="icon glyphicon"  aria-hidden="true"></span> Add to cart</button></div>
  </div>
  <section id="block-block-7" class="block block-block contextual-links-region clearfix">

    <div class="container">
      <div class="footer-icon-wpper">
        <div class="cus-icon-footer-cus">
          <i class="fa fa-check-circle-o" aria-hidden="true"></i> In stock<br>
          <i class="fa fa-check-circle-o" aria-hidden="true"></i> 2-year warranty<br>
          <i class="fa fa-check-circle-o" aria-hidden="true"></i> 30-day returns
        </div>
      </div>
    </div>
  </section>
  <!-- Three Buttons Section -->

  <div class="row margin-cus-0">
    <div class="col-sm-12 border-ibc check-color2 botton-info-style-aac">	
      <a data-toggle="modal" data-target="#myModal" href="javascript:void(0);">Product Details</a>
      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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

  <div class="row margin-cus-0 custom-inner-margin">
    <div class="col-sm-12 border-ibc check-color2 botton-info-style-aac">	
     <a id="doesattach" href="javascript:void(0);">How does it attach?</a>
   </div>
 </div>

 <div class="row last-row margin-cus-0 custom-inner-margin">
  <div class="col-sm-12 border-ibc check-color2 botton-info-style-aac">	

    <a target="_blank"  href="/sites/default/files/RB.com-INSTRUCTIONS-10.15.15%281%29.pdf">Installation Manual</a>
  </div>
</div>

<div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php 	$module = 'block'; 
        $delta = 9;
        $block = block_load($module, $delta);
        print $block->title; ?></h4>
      </div>
      <div class="modal-body">
        <div><?php $block = module_invoke('block', 'block_view', 9); print render($block['content']); ?></div>
      </div>

    </div>
  </div>

</div>
</div>

<script>
	//plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/

jQuery('.btn-number').click(function(e){
  e.preventDefault();

  fieldName = jQuery(this).attr('data-field');
  type      = jQuery(this).attr('data-type');
  var input = jQuery("input[name='"+fieldName+"']");
  var currentVal = parseInt(input.val());
  if (!isNaN(currentVal)) {
    if(type == 'minus') {
      if(currentVal > input.attr('min')) {
        var cval=currentVal - 1;
        input.val(cval).change();
        jQuery('#edit-qty').val(cval);
        runtime_price_update(cval);
      } 
      if(parseInt(input.val()) == input.attr('min')) {
        jQuery(this).attr('disabled', true);
      }

    } else if(type == 'plus') {

      if(currentVal < input.attr('max')) {
        var cval=currentVal + 1;
        input.val(cval).change();
        jQuery('#edit-qty').val(cval);
        runtime_price_update(cval);
      }
      if(parseInt(input.val()) == input.attr('max')) {
        jQuery(this).attr('disabled', true);
      }

    }
  } else {
    input.val(0);
  }
});
jQuery('.input-number').focusin(function(){
 jQuery(this).data('oldValue', jQuery(this).val());
});
jQuery('.input-number').change(function() {

  minValue =  parseInt(jQuery(this).attr('min'));
  maxValue =  parseInt(jQuery(this).attr('max'));
  valueCurrent = parseInt(jQuery(this).val());

  name = jQuery(this).attr('name');
  if(valueCurrent >= minValue) {
    jQuery(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
  } else {
    alert('Sorry, the minimum value was reached');
    jQuery(this).val(jQuery(this).data('oldValue'));
  }
  if(valueCurrent <= maxValue) {
    jQuery(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
  } else {
    alert('Sorry, the maximum value was reached');
    jQuery(this).val(jQuery(this).data('oldValue'));
  }
});
jQuery(".input-number").keydown(function (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
         // Allow: Ctrl+A
         (e.keyCode == 65 && e.ctrlKey === true) || 
         // Allow: home, end, left, right
         (e.keyCode >= 35 && e.keyCode <= 39)) {
             // let it happen, don't do anything
           return;
         }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
      e.preventDefault();
    }
});

jQuery(function(){
  jQuery('.check-selected-size').click(function(){
    jQuery('.check-selected-size').removeClass('custom-active');
    jQuery(this).addClass('custom-active');
  });

  jQuery('.check-selected-color').click(function(){
    jQuery('.check-selected-color').removeClass('custom-active');
    jQuery(this).addClass('custom-active');
  });
  jQuery('.check-selected-strap').click(function(){
    jQuery('.check-selected-strap').removeClass('custom-active');
    jQuery(this).addClass('custom-active');
  });
});

function runtime_price_update(qval){
  var uc_prince = jQuery('.uc-price').html();
  uc_prince = uc_prince.replace('$','');
  uc_prince = parseFloat(uc_prince * qval);
  uc_prince = uc_prince.toFixed(2);
  jQuery('.price_div').html('$'+uc_prince);
}
</script>

<?php 

}

?>