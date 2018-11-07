<div class="header-perspective" id="head-perspective">
<header id="navbar" role="banner" class="<?php print $navbar_classes; ?> cart-bar-affix">

    <div id="mySidenav" class="sidenav">
	<?php
	$menu = menu_navigation_links('main-menu');
	foreach ($menu as $main_menu) {
	    if ($main_menu['title'] == 'Home') {

//		echo '<a href="' . $base_url . '">' . $main_menu['title'] . '</a>';
		echo '<a href="' . $base_url . '/#collapseFive_panel" class="page-scroll">Accessories</a>';
//		echo '<a href="' . $base_url . '/#collapseFive_panel_accessories" class="accessories-scroll">Accessories</a>';
		echo '<a href="' . $base_url . '/#collapseOne_panel" class="page-scroll">Description</a>';
		echo '<a href="' . $base_url . '/#collapseFour_panel" class="page-scroll">How soon can I get it?</a>';
		echo '<a href="' . $base_url . '/#collapseTwo_panel" class="page-scroll">Does it fit my car?</a>';
		echo '<a href="' . $base_url . '/#collapseSix_panel" class="page-scroll">How does it attach?</a>';
		echo '<a href="' . $base_url . '/#collapseThree_panel" class="page-scroll">Reviews</a>';
	    } elseif ($main_menu['title'] == 'About Us') {
		echo '<a href="#" class="load_block_content"  data-block_id="10" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a>';
	    } elseif ($main_menu['title'] == 'Shipping') {
		echo '<a href="#" class="load_block_content shipping_warenty"  data-block_id="17" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a>';
	    } elseif ($main_menu['title'] == 'Contact Us') {
		echo '<a href="#" class="load_block_content "  data-block_id="13" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a>';
	    } elseif ($main_menu['title'] == 'Warranty/Returns') {
		echo '<a href="#" class="load_block_content shipping_warenty"  data-block_id="14" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a>';
	    } else {
		$link_alias = $main_menu['href'];
		$alias = drupal_get_path_alias($link_alias);
		echo '<a href="' . $base_url . '/' . $alias . '">' . $main_menu['title'] . '</a>';
	    }
	}
	?>


    </div>



    <div class="fadeMe" id="fadeMe" onclick="closeNav()"></div>
    <div class="fadeMe1" id="fadeMe1"></div>

    <div class="<?php print $container_class; ?>">
	<div class="navbar " style="margin-bottom:0px;" data-spy="affix" data-offset-top="197" >
	    <?php
	    if (!empty($primary_nav) || !empty($secondary_nav) || !empty($page['navigation'])) {
		?>
    	    <button type="button" class="navbar-toggle " type="button" onclick="openNav()" id="menu1"  style="float:left;" >
    		<span class="sr-only"><?php print t('Toggle navigation'); ?></span>
    		<span class="icon-bar"></span>
    		<span class="icon-bar"></span>
    		<span class="icon-bar"></span>
    	    </button>

    	    <nav role="navigation"  class="dropdown-menu dropdown-menu-left" >
		    <?php
		    if (!empty($primary_nav)):
			print render($primary_nav);
		    endif;
		    if (!empty($secondary_nav)):
			print render($secondary_nav);
		    endif;
		    if (!empty($page['navigation'])):
			print render($page['navigation']);
		    endif;
		    ?>
    	    </nav>
		<?php
	    }
	    if ($logo) {
		?>
    	    <a class="logo navbar-btn pull-left" style="position: absolute;left: 46%;margin-left: -65px;" href="<?php print $front_page; ?>" title="<?php print t('RoofBag Homepage'); ?>">
    		<div id="logo-text">RoofBag</div>
    		<div id="tag-line">Travel Made Easy</div>
    	    </a>

    	    <div class="navbar-btn pull-left" style="float:right !important;padding-top:2px;">
<!--                <i id="phone_btn" class="fa fa-phone" aria-hidden="true" data-toggle="collapse" data-target=".navbar-collapse" style="margin-right:25px;"></i>-->
		<a href="tel:800-276-6322" class=""><i id="phone_btn" class="fa fa-phone" aria-hidden="true"  style="margin-right:25px;"></i></a>
    		<!-- <a href="javascript:void(0)" class=""><i class="fa fa-comments" aria-hidden="true"></i></a>-->
    		<a href="javascript:void(Tawk_API.toggle())" class=""><i class="fa fa-comments" aria-hidden="true"></i></a>
		    <?php
		    if (!drupal_is_front_page()) {
			?>
			<a href="<?php echo $base_url; ?>" class="">
			    <i class="fa fa-home bounce animated"></i>
			</a>
		    <?php } ?>
    	    </div>

		<?php
	    }
	    if (!empty($primary_nav) || !empty($secondary_nav) || !empty($page['navigation'])) {
		?>
    	    <div class="navbar-collapse collapse" id="navbar-collapse" style="width:100%;border-top: 0;">
    		<nav role="navigation">
    		    <ul class="menu nav navbar-nav">
    			<li class="first leaf active"><a href="tel:800-276-6322" class="call-from">Call (from USA)</a></li>
    			<li class="last leaf"><a href="tel:619-662-0495" class="call-from">Call (from Outside USA)</a></li>
    		    </ul>
    		</nav>
    	    </div>
	    <?php } ?>
	</div>

	<?php
	if ($no_of_cart_items) {
	    if (current_path() == 'cart/checkout') {
		$top_bar_text = "See My Order";
	    } else {
		$top_bar_text = "See My Order & Checkout";
	    }
	    
	    if (isset($_SESSION["open_cart_default"])) {
		$show_hide_cart = '';
		$glyphicon_cart_bat = 'glyphicon glyphicon-minus';
		$top_bar_text = "Cart";
		unset($_SESSION["open_cart_default"]);
	    } else {
		$show_hide_cart = 'style="display:none;"';
		$glyphicon_cart_bat = 'glyphicon glyphicon-plus';
		
	    }
	    
	    ?>
        <input id="number_of_cart_item" value="<?php echo $no_of_cart_items; ?>" type="hidden">
    	<div class="stctik-cart-butn clearfix">
    	    <div id="see_my_order_btn_top" class="shopping-cart-bar main-container container see_my_order_btn_top_opened" onclick="open_shopingcart_block_new()">
    		<span id="sub-total-box-animation" class="cart-subtotal-new"><em><?php echo $total_items_count; ?></em><label id="car_bar_total_heading">Sub-total:</label><span id="car_bar_total_text"></span></span>
    		<span id="top_cart_bar_main_title" class="cart-title-new"><?php echo $top_bar_text; ?></span>
    		<span class="<?php echo $glyphicon_cart_bat; ?>" aria-hidden="true" style="float:right;"></span>
    	    </div>
    	</div>
	<?php } ?>
    </div>

    <div class="desktop-header-menu-container">
		<div class="container">
			<ul class="desktop_header_menu">
		      <?php
		      $menu = menu_navigation_links('main-menu');
		      foreach ($menu as $main_menu) {
		          if ($main_menu['title'] == 'Home') {

		        echo '<li><a href="' . $base_url . '">' . $main_menu['title'] . '</a></li>';
		        echo '<li><a href="' . $base_url . '/#collapseFive_panel" class="page-scroll">Accessories</a></li>';
		        echo '<li><a href="' . $base_url . '/#collapseOne_panel" class="page-scroll">Description</a></li>';
		        // echo '<li><a href="' . $base_url . '/#collapseFour_panel" class="page-scroll">How soon can I get it?</a></li>';
		        // echo '<li><a href="' . $base_url . '/#collapseTwo_panel" class="page-scroll">Does it fit my car?</a></li>';
		        // echo '<li><a href="' . $base_url . '/#collapseSix_panel" class="page-scroll">How does it attach?</a></li>';
		        // echo '<li><a href="' . $base_url . '/#collapseThree_panel" class="page-scroll">Reviews</a></li>';
		          } elseif ($main_menu['title'] == 'About Us') {
		        echo '<li><a href="#" class="load_block_content"  data-block_id="10" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a></li>';
		          } elseif ($main_menu['title'] == 'Shipping') {
		        echo '<li><a href="#" class="load_block_content shipping_warenty"  data-block_id="17" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a></li>';
		          } elseif ($main_menu['title'] == 'Contact Us') {
		        echo '<li><a href="#" class="load_block_content "  data-block_id="13" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a></li>';
		          } elseif ($main_menu['title'] == 'Warranty/Returns') {
		        echo '<li><a href="#" class="load_block_content shipping_warenty"  data-block_id="14" data-whatever="' . $main_menu['title'] . '">' . $main_menu['title'] . '</a></li>';
		          } else {
		        $link_alias = $main_menu['href'];
		        $alias = drupal_get_path_alias($link_alias);
		        echo '<li><a href="' . $base_url . '/' . $alias . '">' . $main_menu['title'] . '</a></li>';
		          }
		      } ?>
		    </ul>
		</div>
	</div>

<?php
$blockIps = ['67.220.165.162','68.8.201.94','27.54.165.218'];
$clientIps = get_client_ip();
?>
<?php if( !in_array($clientIps, $blockIps) ){ ?>
<!-- Global site tag (gtag.js) - Google AdWords: 951954227 02.02.18 JG -->
    
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-951954227"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-951954227');
  gtag('config', 'AW-1070747480');

</script>
<?php } ?>

</header>
     
</div>
<div class="stctik-cart-body" style="overflow: visible;background: #fff"><?php include "cstm_popup_cart_block.php"; ?></div>


