<div class="new_front_top_section">
	<?php //echo '<pre>'; print_r($_SESSION); echo '</pre>'; ?>
    <h1>The easy car storage solution<span style = "display: block;">100% Waterproof Soft Car Top Carrier</span></h1><br/><br/><br/>
    <img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/FitCloseUp-850.fw.png'; ?>" alt="Roofbag"/>
    <ul>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/usa-icon.png'; ?>" alt="usa-icon"/><span>Made in the USA</span></li>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/shipping-icon.png'; ?>" alt="shipping-icon"/><span>Ships today</span></li>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/harmful-chemicals-icon.png'; ?>" alt="harmful-chemicals-icon"/><span>Free of harmful chemicals</span></li>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/easy-returns-icon.png'; ?>" alt="easy-returns-icon"/><span>Easy returns</span></li>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/guarantee-icon.png'; ?>" alt="guarantee-icon"/><span>Guaranteed to fit</span></li>
		<li><img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/warranty-icon.png'; ?>" alt="warranty-icon"/><span>2-year warranty</span></li>
    </ul>
    <h1>Fits All Cars<span style = "display: block;">With Rack or Without Rack</span></h1>
    <img style="padding-bottom: 10px;" src="<?php echo $base_url . '/sites/all/themes/roofbag/images/GrayCarrier-BlueCar-Desert.fw.png'; ?>" alt="Roofbag"/>
     <img style="width: 100%" src="<?php echo $base_url . '/sites/all/themes/roofbag/images/RoofBag-Black-Cross-Bars-Desert.jpg'; ?>" alt="Roofbag"/>
</div>
<div class="new_roofbag_products_section">
    <?php
    $nodes = node_load_multiple(array(1, 289), array('type' => "product", 'status' => 1,));
    $create_add_to_cart = true;
    include "nodeblock.php";
    ?>
</div>




<!--
<script type="text/javascript">
	// code for Pickup at Factory View Map code
	var map;

	google.maps.event.addDomListener(window, 'load', initialize);

	function initialize() {
	   var mapCanvas = document.getElementById('map');
	   var mapOptions = {
	      center: new google.maps.LatLng(44.5403, -78.5463),
	      zoom: 8,
	      mapTypeId: google.maps.MapTypeId.ROADMAP
	   }
	   map = new google.maps.Map(mapCanvas, mapOptions);
	}

	$('#load_factory_map').on('shown.bs.modal', function () {
		alert("JJJJ");
	    google.maps.event.trigger(map, "resize");
	});

	var myKey = "AIzaSyAchBklREVr4C8W-4CPSR__HtGiqLp5FsM";
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=" + myKey + "&sensor=false&callback=initialize"></script>-->
<!--<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&callback=initialize"></script>-->
    
<div class="panel-group" id="accordion">
    <div class="panel panel-default" id="collapseOne_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle" data-toggle="collapse"  href="#collapseOne" id="Description">
		    Description <span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
		</a>
	    </h4>
	</div>
	<div id="collapseOne" class="panel-collapse collapse ">
	    <div class="panel-body">
		<div id="cross_country_description_container">
		<?php 
		    $nodes_des = node_load_multiple(array(1, 289), array('type' => "product", 'status' => 1,));
		    echo $nodes_des[1]->body['und']['0']['value']; ?>
		</div>
		<div id="explorer_description_container" style="display: none;">
		<?php echo $nodes_des[289]->body['und']['0']['value']; ?>
		</div>
	    </div>
	</div>
    </div>
<!--    <div class="panel panel-default" id="collapseOne_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle" data-toggle="collapse"  href="#collapseOne">
		    Why people love RoofBag? <span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
		</a>
	    </h4>
	</div>
	<div id="collapseOne" class="panel-collapse collapse ">
	    <div class="panel-body">
		<i class="fa fa-custom fa-globe " aria-hidden="true" style="padding-right:5px;"></i> Made in the USA<br><br>
		<i class="fa fa-custom fa-umbrella" aria-hidden="true" style="padding-right:5px;"></i> Waterproof<br><br>
		<i class="fa fa-custom fa-shield" aria-hidden="true" style="padding-right:8px;"></i> Warranty<br><br>
		<i class="fa fa-custom fa-truck" aria-hidden="true" style="padding-right:5px;"></i> Fast delivery - Ships today*<br>
	    </div>
	</div>
    </div>-->
    <div class="panel panel-default" id="collapseFour_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle"  data-toggle="collapse"  href="#collapseFour" id="How soon can I get it?">
		    How soon can I get it? <span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
		</a>
	    </h4>
	</div>
	<div id="collapseFour" class="panel-collapse collapse ">
	    <div class="panel-body">
		<!--		Select Country
				<div class="panel-body1 border-ib border-ib2">USA or CANADA</div>-->
		<div class="input-group boots-cus-style-butn">
		    <input id="edit-panes-delivery-delivery-postal-code2" style="text-transform:uppercase" type="text" name="panes[delivery][delivery_postal_code]" size="30" maxlength="10" class="form-text ajax-processed progress-disabled form-control" placeholder="Enter Zip Code or Postal Code" style="margin-top:6px;">
<!--		    <span class="input-group-btn">
			<button class="btn btn-default" id="delivery_postal_submit" type="button">Go!</button>
		    </span>-->
		</div>
		<div id="loaderimg1" class="loaderimg_wraper" style="display:none;"><img id="imgloader" src="<?php echo $base_url . '/sites/all/themes/roofbag/images/'; ?>loader-new.svg" ></div>
		<select style="width:100%; background-color:white !important;" class="form-control"  onchange="getCountryBasedQuotes();" id="shippinglocCountry" name="shippinglocCountry" size="1" >
		    <option class="country" value="">Select country</option>
		    <option value="AF" selected="selected">	Afghanistan </option>
		    <option value="AL">	Albania </option>
		    <option value="DZ">	Algeria </option>
		    <option value="AS">	American Samoa </option>
		    <option value="AD">	Andorra </option>
		    <option value="AO">	Angola </option>
		    <option value="AI">	Anguilla </option>
		    <option value="AQ">	Antarctica </option>
		    <option value="AG">	Antigua &amp; Barbuda </option>
		    <option value="AR">	Argentina </option>
		    <option value="AM">	Armenia </option>
		    <option value="AW">	Aruba </option>
		    <option value="AU">	Australia </option>
		    <option value="AT">	Austria </option>
		    <option value="AZ">	Azerbaijan </option>
		    <option value="BS">	Bahamas </option>
		    <option value="BH">	Bahrain </option>
		    <option value="BD">	Bangladesh </option>
		    <option value="BB">	Barbados </option>
		    <option value="BY">	Belarus </option>
		    <option value="BE">	Belgium </option>
		    <option value="BZ">	Belize </option>
		    <option value="BJ">	Benin </option>
		    <option value="BM">	Bermuda </option>
		    <option value="BT">	Bhutan </option>
		    <option value="BO">	Bolivia </option>
		    <option value="BA">	Bosnia &amp; Herzegovina </option>
		    <option value="BW">	Botswana </option>
		    <option value="BV">	Bouvet Island </option>
		    <option value="BR">	Brazil </option>
		    <option value="IO">	British Indian Ocean Terr. </option>
		    <option value="BN">	Brunei Darussalam </option>
		    <option value="BG">	Bulgaria </option>
		    <option value="BF">	Burkina Faso </option>
		    <option value="BI">	Burundi </option>
		    <option value="KH">	Cambodia </option>
		    <option value="CM">	Cameroon </option>
		    <option value="CA">	Canada </option>
		    <option value="CV">	Cape Verde </option>
		    <option value="KY">	Cayman Islands </option>
		    <option value="CF">	Central African Republic </option>
		    <option value="TD">	Chad </option>
		    <option value="CL">	Chile </option>
		    <option value="CN">	China </option>
		    <option value="CX">	Christmas Island </option>
		    <option value="CC">	Cocos (keeling) Islands </option>
		    <option value="CO">	Colombia </option>
		    <option value="KM">	Comoros </option>
		    <option value="CG">	Congo </option>
		    <option value="CD">	Congo, Republic Of </option>
		    <option value="CK">	Cook Islands </option>
		    <option value="CR">	Costa Rica </option>
		    <option value="CI">	Cote D'ivoire </option>
		    <option value="HR">	Croatia </option>
		    <option value="CU">	Cuba </option>
		    <option value="CY">	Cyprus </option>
		    <option value="CZ">	Czech Republic </option>
		    <option value="DK">	Denmark </option>
		    <option value="DJ">	Djibouti </option>
		    <option value="DM">	Dominica </option>
		    <option value="DO">	Dominican Republic </option>
		    <option value="TL">	East Timor </option>
		    <option value="EC">	Ecuador </option>
		    <option value="EG">	Egypt </option>
		    <option value="SV">	El Salvador </option>
		    <option value="EN">	England </option>
		    <option value="GQ">	Equatorial Guinea </option>
		    <option value="ER">	Eritrea </option>
		    <option value="EE">	Estonia </option>
		    <option value="ET">	Ethiopia </option>
		    <option value="FK">	Falkland Islands (malvinas) </option>
		    <option value="FO">	Faroe Islands </option>
		    <option value="FJ">	Fiji </option>
		    <option value="FI">	Finland </option>
		    <option value="FR">	France </option>
		    <option value="GF">	French Guiana </option>
		    <option value="PF">	French Polynesia </option>
		    <option value="TF">	French Southern Terr. </option>
		    <option value="GA">	Gabon </option>
		    <option value="GM">	Gambia </option>
		    <option value="GE">	Georgia </option>
		    <option value="DE">	Germany </option>
		    <option value="GH">	Ghana </option>
		    <option value="GI">	Gibraltar </option>
		    <option value="GB">	Great Britain and Northern Ireland </option>
		    <option value="GR">	Greece </option>
		    <option value="GL">	Greenland </option>
		    <option value="GD">	Grenada </option>
		    <option value="GP">	Guadeloupe </option>
		    <option value="GU">	Guam </option>
		    <option value="GT">	Guatemala </option>
		    <option value="GN">	Guinea </option>
		    <option value="GW">	Guinea-bissau </option>
		    <option value="GY">	Guyana </option>
		    <option value="HT">	Haiti </option>
		    <option value="HM">	Heard &amp; Mcdonald Islands </option>
		    <option value="VA">	Holy See (Vatican City) </option>
		    <option value="HN">	Honduras </option>
		    <option value="HK">	Hong Kong </option>
		    <option value="HU">	Hungary </option>
		    <option value="IS">	Iceland </option>
		    <option value="IN">	India </option>
		    <option value="ID">	Indonesia </option>
		    <option value="IR">	Iran </option>
		    <option value="IQ">	Iraq </option>
		    <option value="IE">	Ireland </option>
		    <option value="IL">	Israel </option>
		    <option value="IT">	Italy </option>
		    <option value="JM">	Jamaica </option>
		    <option value="JP">	Japan </option>
		    <option value="JO">	Jordan </option>
		    <option value="KZ">	Kazakhstan </option>
		    <option value="KE">	Kenya </option>
		    <option value="KI">	Kiribati </option>
		    <option value="KP">	Korea (North) </option>
		    <option value="KR">	Korea (South) </option>
		    <option value="KW">	Kuwait </option>
		    <option value="KG">	Kyrgyzstan </option>
		    <option value="LA">	Lao </option>
		    <option value="LV">	Latvia </option>
		    <option value="LB">	Lebanon </option>
		    <option value="LS">	Lesotho </option>
		    <option value="LR">	Liberia </option>
		    <option value="LY">	Libyan Arab Jamahiriya </option>
		    <option value="LI">	Liechtenstein </option>
		    <option value="LT">	Lithuania </option>
		    <option value="LU">	Luxembourg </option>
		    <option value="MO">	Macao </option>
		    <option value="MK">	Macedonia </option>
		    <option value="MG">	Madagascar </option>
		    <option value="MW">	Malawi </option>
		    <option value="MY">	Malaysia </option>
		    <option value="MV">	Maldives </option>
		    <option value="ML">	Mali </option>
		    <option value="MT">	Malta </option>
		    <option value="MH">	Marshall Islands </option>
		    <option value="MQ">	Martinique </option>
		    <option value="MR">	Mauritania </option>
		    <option value="MU">	Mauritius </option>
		    <option value="YT">	Mayotte </option>
		    <option value="MX">	Mexico </option>
		    <option value="FM">	Micronesia </option>
		    <option value="MD">	Moldova </option>
		    <option value="MC">	Monaco </option>
		    <option value="MN">	Mongolia </option>
		    <option value="MS">	Montserrat </option>
		    <option value="MA">	Morocco </option>
		    <option value="MZ">	Mozambique </option>
		    <option value="MM">	Myanmar </option>
		    <option value="MP">	N. Mariana Islands </option>
		    <option value="NA">	Namibia </option>
		    <option value="NR">	Nauru </option>
		    <option value="NP">	Nepal </option>
		    <option value="NL">	Netherlands </option>
		    <option value="AN">	Netherlands Antilles </option>
		    <option value="NC">	New Caledonia </option>
		    <option value="NZ">	New Zealand </option>
		    <option value="NI">	Nicaragua </option>
		    <option value="NE">	Niger </option>
		    <option value="NG">	Nigeria </option>
		    <option value="NU">	Niue </option>
		    <option value="NF">	Norfolk Island </option>
		    <option value="NO">	Norway </option>
		    <option value="OM">	Oman </option>
		    <option value="PK">	Pakistan </option>
		    <option value="PW">	Palau </option>
		    <option value="PS">	Palestinian Territory </option>
		    <option value="PA">	Panama </option>
		    <option value="PG">	Papua New Guinea </option>
		    <option value="PY">	Paraguay </option>
		    <option value="PE">	Peru </option>
		    <option value="PH">	Philippines </option>
		    <option value="PN">	Pitcairn </option>
		    <option value="PL">	Poland </option>
		    <option value="PT">	Portugal </option>
		    <option value="PR">	Puerto Rico </option>
		    <option value="QA">	Qatar </option>
		    <option value="RE">	Reunion </option>
		    <option value="RO">	Romania </option>
		    <option value="RU">	Russian Federation </option>
		    <option value="RW">	Rwanda </option>
		    <option value="GS">	S. Georgia &amp; Sandwich Isl. </option>
		    <option value="SH">	Saint Helena </option>
		    <option value="KN">	Saint Kitts &amp; Nevis </option>
		    <option value="LC">	Saint Lucia </option>
		    <option value="PM">	Saint Pierre &amp; Miquelon </option>
		    <option value="VC">	Saint Vincent &amp; Grenadines </option>
		    <option value="WS">	Samoa </option>
		    <option value="SM">	San Marino </option>
		    <option value="ST">	Sao Tome &amp; Principe </option>
		    <option value="SA">	Saudi Arabia </option>
		    <option value="SN">	Senegal </option>
		    <option value="SC">	Seychelles </option>
		    <option value="SL">	Sierra Leone </option>
		    <option value="SG">	Singapore </option>
		    <option value="SK">	Slovakia </option>
		    <option value="SI">	Slovenia </option>
		    <option value="SB">	Solomon Islands </option>
		    <option value="SO">	Somalia </option>
		    <option value="ZA">	South Africa </option>
		    <option value="ES">	Spain </option>
		    <option value="LK">	Sri Lanka </option>
		    <option value="SD">	Sudan </option>
		    <option value="SR">	Suriname </option>
		    <option value="SJ">	Svalbard &amp; Jan Mayen </option>
		    <option value="SZ">	Swaziland </option>
		    <option value="SE">	Sweden </option>
		    <option value="CH">	Switzerland </option>
		    <option value="SY">	Syrian Arab Republic </option>
		    <option value="TW">	Taiwan </option>
		    <option value="TJ">	Tajikistan </option>
		    <option value="TZ">	Tanzania </option>
		    <option value="TH">	Thailand </option>
		    <option value="TG">	Togo </option>
		    <option value="TK">	Tokelau </option>
		    <option value="TO">	Tonga </option>
		    <option value="TT">	Trinidad &amp; Tobago </option>
		    <option value="TN">	Tunisia </option>
		    <option value="TR">	Turkey </option>
		    <option value="TM">	Turkmenistan </option>
		    <option value="TC">	Turks &amp; Caicos Islands </option>
		    <option value="TV">	Tuvalu </option>
		    <option value="UG">	Uganda </option>
		    <option value="UA">	Ukraine </option>
		    <option value="AE">	United Arab Emirates </option>
		    <option value="UK">	United Kingdom </option>
		    <option value="US">	United States </option>
		    <option value="UY">	Uruguay </option>
		    <option value="UZ">	Uzbekistan </option>
		    <option value="VU">	Vanuatu </option>
		    <option value="VE">	Venezuela </option>
		    <option value="VN">	Viet Nam </option>
		    <option value="VG">	Virgin Islands, British </option>
		    <option value="VI">	Virgin Islands, U.S. </option>
		    <option value="WF">	Wallis &amp; Futuna </option>
		    <option value="EH">	Western Sahara </option>
		    <option value="YE">	Yemen </option>
		    <option value="YU">	Yugoslavia </option>
		    <option value="ZM">	Zambia </option>
		    <option value="ZW">	Zimbabwe </option>
		</select>
		<?php
		if (isset($_SESSION['parent_html']) && !empty($_SESSION['parent_html'])) {
		    $style_display_none = '';
		    echo '<div id="quotes_all1" class="shipping_cal">' . $_SESSION['parent_html'] . '</div>';
		} else {
		    $style_display_none = 'style="display: none;"';
		    echo '<div id="quotes_all1" class="shipping_cal"></div>';
		}
		
		?>
		<div id="how_soon_carrier_btn" class="btn-yellowbig choose_my_carrier_btn" <?php echo $style_display_none;?> >
		    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success show_carrier_button_in_how_soon_section" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a> <!--//kp -->
		    <div class="btn-yellowbig show_cart_button_in_how_soon_section" style="display:none;"><button class="node-add-to-cart btn btn-success form-submit" onclick="clickCartButton('.node_container', '#collapseFive_panel');" type="button" id="" name="op" value="Add carrier to cart"><span class="icon glyphicon"  aria-hidden="true"></span> Add carrier to cart</button></div> <!--//kp -->
		</div>	
	    </div>
	</div>
    </div>
    <?php
    $query = db_select('cardata', 'u');
    $query->fields('u', array('make'));
    $results = $query->distinct()->execute();
    $unsrot_record = array();
//	<a class="accordion-toggle" data-toggle="collapse"  href="#collapseTwo" aria-expanded="true">
//    if ($roofbag_exists) {
	$collapse = "collapse";
	$collapse_glyphicon = "glyphicon-plus";
//    } else {
//	$collapse = "out collapse in";
//	$collapse_glyphicon = "glyphicon-minus";
//    }
    echo '
	<div class="panel panel-default stays_open_bar" id="collapseTwo_panel">
	<div class="panel-heading">
	<h4 class="panel-title">
	<a class="accordion-toggle" data-toggle="collapse"  href="#collapseTwo" id="collapseTwoLink">
		Does it fit my car?<span class="glyphicon ' . $collapse_glyphicon . '" aria-hidden="true" style="float:right;"></span>
	</a></h4>
	</div><div id="collapseTwo" class="panel-collapse ' . $collapse . '" aria-expanded="true">
	<div class="panel-body"><select id="make_select" class="form-control">
	<option>Select make </option>
	';
    while ($record = $results->fetchAssoc()) {
	$unsrot_record[] = $record['make'];
    }
    sort($unsrot_record);
    $sorted_roecord = $unsrot_record;
    foreach ($sorted_roecord as $key) {
	echo '<option>' . $key . '</option>';
    }
    echo '</select>
	<select id="model_select" class="form-control">
	<option>Select model</option>
	</select>
	<div id="mvha">My vehicle has
	</div>
	<div class="vehicle_feature" >
	<button id="block_first" class="block_first" onclick="openBlock(this.id)"> Cross Bars</button>
	<button id="block_five" class="block_five" onclick="openBlock(this.id)">Cross Bars and Side Rails</button>
	<button id="block_second" class="block_second" onclick="openBlock(this.id)"> Side Rails with Slots </button>
	<button id="block_third" class="block_third" onclick="openBlock(this.id)"> Flush Side Rails   </button>
	<button id="block_fourth" class="block_fourth" onclick="openBlock(this.id)">Bare Roof (No Rack)</button>
	</div>
	<div class="modal fade attributes-box-modal-lg" tabindex="-1" role="dialog" aria-labelledby="attributesBoxModalLg">
	<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
	<div class="modal-header">
	</div>
	<div class="modal-body">
	<div class="row">
	<div class="col-md-12">Please select size, type of straps, and carrier color.</div>
	</div>
	</div>
	<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>
	</div>
	</div>
	</div>
	</div></div></div>';
    ?>
    <div class="panel panel-default" id="collapseSix_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle" data-toggle="collapse"  href="#collapseSix" id="collapseSixLink">
		    How does it attach?<span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
		</a>
	    </h4>
	</div>

	<div id="collapseSix" class="panel-collapse collapse ">
	    <div class="panel-body">
		<div class="vehicle_feature">
		    <p>Select your rack style</p>
		    <button class="block_fourth">Bare Roof (No Rack)</button>
		    <div class="attach_block">
			<?php
			$block = module_invoke('block', 'block_view', 6);
			print render($block['content']);
			?>
			<div class="btn-yellowbig choose_my_carrier_btn">
			    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a>
			</div>	
		    </div>
		    <button class="block_first"> Cross Bars</button>
		    <div class="attach_block">
			<?php
			$block = module_invoke('block', 'block_view', 4);
			print render($block['content']);
			?>
			<div class="btn-yellowbig choose_my_carrier_btn">
			    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a>
			</div>	
		    </div>
		    <button class="block_five">Cross Bars and Side Rails</button>
		    <div class="attach_block">
			<?php
			$block = module_invoke('block', 'block_view', 4);
			print render($block['content']);
			?>
			<div class="btn-yellowbig choose_my_carrier_btn">
			    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a>
			</div>	
		    </div>
		    <button class="block_second"> Side Rails with Slots </button>
		    <div class="attach_block">
			<?php
			$block = module_invoke('block', 'block_view', 5);
			print render($block['content']);
			?>
			<div class="btn-yellowbig choose_my_carrier_btn">
			    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a>
			</div>	
		    </div>
		    <button class="block_third"> Flush Side Rails   </button>
		    <div class="attach_block">
			<?php
			$block = module_invoke('block', 'block_view', 6);
			print render($block['content']);
			?>
			<div class="btn-yellowbig choose_my_carrier_btn">
			    <a href="javascript:void(0);" class="node-add-to-cart btn btn-success" onclick="goToSection('#select_your_carrier_section')">Select your carrier</a>
			</div>	
		    </div>
		</div>
	    </div>
	</div>
    </div>
<!--    <div class="panel panel-default" id="collapseThree_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle" data-toggle="collapse"  href="#collapseThree">
		    Customer Reviews<span class="glyphicon glyphicon-plus" aria-hidden="true" style="float:right;"></span>
		</a>
	    </h4>
	</div>
	<div id="collapseThree" class="panel-collapse collapse ">
	    <div class="panel-body">
		<?php
		$block = module_invoke('views', 'block_view', 'customer_reviews-block_1');
		print render($block['content']);
		?>
	    </div>
	</div>
    </div>-->

<?php if ($roofbag_exists) {
    $accessories_txt = "Add recommended accessories";
} else {
    $accessories_txt = "Accessories";
} ?>
    
 <!--5/19/2017 - heat map code from LuckyOrange.com -->
<script type='text/javascript'>
window.__lo_site_id = 83234;

	(function() {
		var wa = document.createElement('script'); wa.type = 'text/javascript'; wa.async = true;
		wa.src = 'https://d10lpsik1i8c69.cloudfront.net/w.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wa, s);
	  })();
	</script>
<!-- end of heatmap code from LuckyOrange.com -->
   
    
    
    <div class="panel panel-default" id="collapseFive_panel">
	<div class="panel-heading">
	    <h4 class="panel-title">
		<a class="accordion-toggle" data-toggle="collapse"  href="#collapseFive" id="Accessories">
		    <?php echo $accessories_txt; ?><span class="glyphicon glyphicon-plus" aria-hidden="true"  style="float:right;"></span>
		</a>
	    </h4>
	</div>
	<div id="collapseFive" class="panel-collapse collapse"  aria-expanded="true">
	    <div class="panel-body">
		<?php include "accessories_block.php"; ?>
	    </div>
	</div>
    </div>
    <div id="collapseThree_panel" class="customer_reviews_section">
	<div class="review_banner_container">
	    <img src="<?php echo $base_url . '/sites/all/themes/roofbag/images/RoofBag-Cargo-Carrier-Side-Rails.jpg'; ?>" alt="Customer Reviews"/>
	    <p>Customer Reviews</p>
	</div>

	<?php
		$Userip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
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
		if( $group > 1 ){
    ?>
	<!-- TrustBox widget - List  -->
	<div class="trustpilot-widget" style="display: block !important;" data-locale="en-US" data-template-id="539ad60defb9600b94d7df2c" data-businessunit-id="58e52e3e0000ff00059fe9d0" data-style-height="420px" data-style-width="100%" data-tags="SelectedReview" data-schema-type="Organization">
	    <a href="https://www.trustpilot.com/review/roofbag.com" target="_blank">Trustpilot</a>
	</div>
	<?php } ?>

    </div>
    <!-- End TrustBox widget -->
    <!--	<div class="btn-yellowbig panel-body" style="margin-top:24px;">
		    <button class="node-add-to-cart btn btn-success form-submit" onclick="openProductSection()" type="submit" id="edit-submit-11" name="op" value="SHOP CAR TOP CARRIERS">SHOP CAR TOP CARRIERS</button>
	    </div>-->
</div>
<style>
    #node-1
    {
	/*display: none;*/
	margin-top: 15px;
    }
    .stickyhead {
	position: fixed;
	top: 0;
	right:0;
	left:0;
	background: rgba(0,0,0,0.7);
	box-shadow:0 0 10px rgba(0,0,0,0.3);
    }
</style>

<?php


?>


<!--<script type="text/javascript">

</script>-->