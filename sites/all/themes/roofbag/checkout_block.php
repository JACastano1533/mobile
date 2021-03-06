<style type="text/css">
.form-fields-1 {
    float: left;
    margin-right: 10px;
    width: 30%;
}
.zip-custom-field .zip-value, #shipping_information_state, #billing_information_state { font-weight: normal; clear: both; display: block; padding-top: 8px;}
.state-custom-field { float: left; width:24%; /*word-wrap: break-word;*/ margin-top: 0!important;}

@media only screen and (max-width: 375px) {
   .state-custom-field, .form-fields-1, .city-name-info { float: none; width:auto!important; margin-right: 0; } 
   .zip-custom-field .zip-value, #shipping_information_state { padding-top: 0;}
}

}
</style>
<?php
// Report all errors except E_NOTICE   
error_reporting(E_ALL ^ E_NOTICE);  


if (!empty($_GET['mobile'])) {
    header("Location: /?mobile=" . $_GET['mobile']);
    exit;
}
/*echo '<pre>';
print_r($_SESSION);
echo '</pre>';*/

?>
<div class="new_checkout_layout">
    <div class="shipping_information_section">
        <div class="header-title">
            <h1>Shipping Information</h1>
        </div>
        <div class="header-sub-title">
            <label>* Required Fields</label>
        </div>
        <div class="form-fields">
            <label>Country*</label>
            <select data-clear-input id="shipping_information_country" class="getOrignalValues" data-valid-error="Country" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-country">
            </select>
        </div>
        <div class="form-fields">
            <label>First Name*</label>
            <input autocomplete="off" type="text" id="shipping_information_first_name" class="getOrignalValues" data-valid-error="First Name" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-first-name"/>
        </div>
        <div class="form-fields">
            <label>Last Name*</label>
            <input autocomplete="off" type="text" id="shipping_information_last_name" class="getOrignalValues" data-valid-error="Last Name" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-last-name"/>
        </div>
        <div class="form-fields">
            <label>Phone</label>
            <input autocomplete="off" type="text" id="shipping_information_phone" class="getOrignalValues" data-validity="false" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-phone"/>
        </div>
        <div class="form-fields">
            <label>Shipping Address*</label>
            <input autocomplete="off" type="text" id="shipping_information_street1" class="getOrignalValues" data-valid-error="Shipping Address" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-street1"/>
        </div>
        <div class="form-fields">
            <label>Apt/Suite</label>
            <input autocomplete="off" type="text" id="shipping_information_street2" class="getOrignalValues" data-validity="false" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-street2"/>
        </div>
        <div class="form-fields">
            <label>Company</label>
            <input autocomplete="off" type="text" id="shipping_information_company" class="getOrignalValues" data-validity="false" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-company"/>
        </div>
        <div class="form-fields">
            <div class="card-info-cus city-name-info">
                <label>City*</label>
                <input autocomplete="off" type="text" id="shipping_information_city" class="getOrignalValues" data-valid-error="City" data-validity="false" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-city"/>
            </div>
        </div>
        <div class="form-fields zip-custom-field">
            <div class="form-fields-1">
            <label style="float:left;">Zip/Postal Code*</label>
                <div class="zip-value"><?php echo strtoupper($_SESSION['savedzip']); ?></div>
                <!--<span id="shipping_information_zip_code" class="getOrignalValues" data-valid-error="Zip Code" readonly data-validity="false" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-postal-code"><?php echo $_SESSION['savedzip']; ?></span>-->
                <!---->
                <a data-clear-input style="font-size: 12px;margin-top:8px;display:block;">Change</a>
            <div class="clearable-input">                    
                
                <input type="hidden" id="shipping_information_zip_code" class="getOrignalValues" data-valid-error="Zip Code" readonly data-validity="false" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-postal-code"/>
                <!--<span data-clear-input>&times;</span> //kp -->                    
            </div>
            </div>
        </div>
        <div class="form-fields state-custom-field">    
                <label>State/Province*</label>
                <?php if(!empty($_SESSION['savedzip'])) { ?>
                    <select id="shipping_information_state" class="getOrignalValues" data-validity="false" data-valid-error="State" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-delivery-delivery-zone"></select>    
                    <span id="shipping_information_state" class="getOrignalValues" data-validity="false" data-valid-error="State"></span>
                <?php } ?>
                
                
        </div>
        </div>
    </div>
    <div class="custom-one-boder-sp"> <span class="border-full-width"></span> </div>
    <div class="payment_method_section">
        <div class="header-sub-title">
            <label>Payment Method</label>
            <span id="change_payment_method" style="display: none;">Change payment method</span> 
        </div>
        <div class="method_container"> 
            <span id="payment_method_credit_card" class="payment_main_title custom-checkout-butn">Credit Card</span>
            <div id="payment_method_credit_card_details" class="card-sec-list" style="display: none;">
                <ul class="card-list-pay">
                    <li> <img src="/sites/all/themes/roofbag/images/visa.png" /> </li>
                    <li> <img src="/sites/all/themes/roofbag/images/master-card.png" /> </li>
                    <li> <img src="/sites/all/themes/roofbag/images/american-express.png" /> </li>
                    <li> <img src="/sites/all/themes/roofbag/images/Discover.png" /> </li>
                </ul>
                <div class="card-info">
                    <div id="credit_card_details" class="card-nuber-ful">
                        <label style="display: block;">Card Number</label>
<!--                        <input id="credit_card_number" class="credit_card_number credit" type="text"  data-valid-error="Credit Card Number" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-payment-details-cc-number"/>-->
                        <div id="creditholder" class="creditholder">
                            <input autocomplete="off" id="creditpart1" type="number" maxlength="4" class="creditcardcomplete" />
                            <input autocomplete="off" id="creditpart2" type="number" maxlength="4" class="creditcardcomplete" />
                            <input autocomplete="off" id="creditpart3" type="number" maxlength="4" class="creditcardcomplete" />
                            <input autocomplete="off" id="creditpart4" type="number" maxlength="4" class="creditcardcomplete" />
                        </div>

                    </div>
                    <div class="card-date-info"> 
                        <span class="custom-width-card-info">
                            <label>Expires</label>
                            <select id="credit_card_expires_month" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-payment-details-cc-exp-month">
                            </select>
                            <select id="credit_card_expires_year" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-payment-details-cc-exp-year">
                            </select>
                        </span> 
                        <span class="custom-width-card-info">
                            <label>Card Code</label>
                            <input autocomplete="off" id="credit_card_code" maxlength="4" data-valid-error="Credit Card Code" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-payment-details-cc-cvv"/>
                        </span> 
                    </div>
                </div>
                <div class="main-custom-billing-opt">
                    <div class="billing_information">
                        <div class="header-sub-title">
                            <label>Billing Information</label>
                        </div>
                        <span id="same_as_shipping" class="custom-select-sec actvie">Same as shipping information</span> 
                        <span id="different_as_shipping" class="custom-select-sec">Different billing information</span>
                        <div id="billing_information_section" class="shipping_information_section" style="display: none;">
                            <div class="header-title">
                                <h1>Billing Information</h1>
                            </div>
                            <div class="header-sub-title">
                                <label>* Required Fields</label>
                            </div>
                            <div class="form-fields">
                                <label>Country*</label>
                                <select id="billing_information_country" data-valid-error="Country" class="getOrignalValues" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-country">
                                </select>
                            </div>
                            <div class="form-fields">
                                <label>First Name*</label>
                                <input autocomplete="off" type="text" id="billing_information_first_name" data-valid-error="First Name" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-first-name"/>
                            </div>
                            <div class="form-fields">
                                <label>Last Name*</label>
                                <input autocomplete="off" type="text" id="billing_information_last_name" data-valid-error="Last Name" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-last-name"/>
                            </div>
                            <div class="form-fields">
                                <label>Phone</label>
                                <input autocomplete="off" type="text" id="billing_information_phone" data-validity="false" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-phone"/>
                            </div>
                            <div class="form-fields">
                                <label>Billing Address*</label>
                                <input autocomplete="off" type="text" id="billing_information_street1" data-valid-error="Shipping Address" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-street1"/>
                            </div>
                            <div class="form-fields">
                                <label>Apt/Suite</label>
                                <input autocomplete="off" type="text" id="billing_information_street2" data-validity="false" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-street2"/>
                            </div>
                            <div class="form-fields">
                                <label>Company</label>
                                <input autocomplete="off" type="text" id="billing_information_company" data-validity="false" class="removeOrignalValues" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-company"/>
                            </div>
                            <div class="form-fields">
                                <div class="card-info-cus">
                                    <label>Zip Code*</label>
                                    <input autocomplete="off" type="text" id="billing_information_zip_code" data-valid-error="Zip Code" class="removeOrignalValues" data-validity="false" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-postal-code"/>
                                </div>
                                <div class="card-info-cus">
                                    <label>City*</label>
                                    <input autocomplete="off" type="text" id="billing_information_city" data-valid-error="City" class="removeOrignalValues" data-validity="false" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-city"/>
                                </div>
                                <div class="card-info-cus">
                                    <label>State/Province*</label>
                                    <!--<span id="billing_information_state" data-validity="false" data-valid-error="State" class="getOrignalValues" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-zone">-->
                                   <!--  <select data-validity="false" data-valid-error="State" onchange="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-zone" id="billing_information_state_auto_fill">
                                        
                                    </select> -->
                                    <input autocomplete="off" type="text" id="billing_information_state_auto_fill" data-validity="false" data-valid-error="State" onkeyup="changeOrignalFieldValue(this);" data-origFieldId="edit-panes-billing-billing-zone">                                        
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="submit_container"> <label id="credit_card_submit_payment" class="custom-checkout-butn">Submit Payment</label> </div>
            </div>
        </div>
    </div>

   <!-- <div class="method_container">
        <label id="payment_method_paypal" class="payment_main_title custom-checkout-butn">PayPal</label>
    </div> -->

    <?php
    //  $curr_day = date('D');
    //  if ($curr_day != "Sat" && $curr_day != "Sun") {
    // $curr_time = date('H:i');
//    if ($curr_time >= "07:30" && $curr_time < "16:00") {
    ?>
    <div class="method_container">
        <label id="payment_method_pay_by_phone" class="payment_main_title custom-checkout-butn">Save Order and Pay by Phone</label>
    </div>
    <?php
    //  }
    //   }
    ?>
</div>

<?php
$current_page_variable = get_defined_vars();

/* echo '<pre>';
print_r($_SESSION); 
echo '</pre>';*/ //kp

//$orderid = $current_page_variable["page"]["content"]["system_main"]["panes"]["delivery"]["address"]["#default_value"]->order_id;
$orderid = $_SESSION['cart_order'];

$_SESSION['check_order'] = $orderid;
?>
