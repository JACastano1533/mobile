var country_gets_selected = false;
var page_loaded = false;
var item_qty_field_elem = "";
var global_saved_make_started = false;
var cart_opened = false;
var cart_postal_code_result_var;
var clicked_different_billing = false;
var checkIfMultiFieldsChanged = 0;
var shippingRatesAjaxChecker = false;
var selectPreSelectedValue = false;
jQuery(document).ready(function () {

    // var selectedZip = jQuery("#cart_postal_code").val();
    // if( selectedZip != "" ){
    //     var myArray231 = [91901,91902,91903,91905,91906,91908,91909,91910,91911,91912,91913,91914,91915,91916,91917,91921,91931,91932,91933,91934,
    //             91935,91941,91942,91943,91944,91945,91946,91947,91948,91950,91951,91962,91963,91976,91977,91978,91979,91980,92003,92004,
    //             92007,92008,92009,92013,92014,92018,92019,92020,92021,92022,92023,92024,92025,92026,92027,92028,92029,92030,92033,
    //             92036,92037,92038,92039,92040,,92046,92049,92051,92052,92054,92055,92056,92057,92058,92059,92060,92061,,92064,92065,
    //             92066,92067,92068,92069,92070,92071,92072,92074,92075,92078,92079,92082,92083,92084,92085,,92088,92091,92092,
    //             92093,92101,92102,92103,92104,92105,92106,92107,92108,92109,92110,92111,92112,92113,92114,92115,92116,92117,92118,92119,
    //             92120,92121,92122,92123,92124,92126,92127,92128,92129,92130,92131,92132,92133,92134,92135,92136,92137,92138,92139,92140,
    //             92142,92143,92145,92147,92149,92150,92152,92153,92154,92155,92159,92160,92162,92163,92164,92165,92166,92167,92168,92169,
    //             92170,92171,92172,92173,92174,92175,92176,92177,92178,92182,92186,92190,92191,92192,92193,92194,92195,92196,92197,92198,92199];

    //     needle = parseInt(selectedZip);
    //     var pick_from_factory = jQuery.inArray(needle, myArray231);
    //     if(pick_from_factory !== -1) {
            
    //         var factory_method_html = '<div id="pickup_from_factory1" class="shipping_rates_bar form-item form-item-panes-quotes-quotes-quote-option form-type-radio radio"><label class="control-label"><input style="display:none;" name="panes[quotes][quotes][quote_option]" class="form-radio ajax-processed" type="radio" value="flatrate_1---0"><span class="service_name">Pick Up at factory</span><span class="service_rate">0.00</span><span class="service_arrival"><button data-toggle="modal" data-target="#myModal" class="btn_view_map"><span class="glyphicon glyphicon-map-marker"></span> View Map</button></span></label></div>';
    //         if( jQuery("#pickup_from_factory1").length <= 0 ){
    //             jQuery(factory_method_html).insertBefore( jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first'));
    //         }
            
    //         jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();
    //         jQuery('#quotes_all1 div:nth-child(2), #cart_postal_code_result div:nth-child(2)').show(); // kp
            
    //         jQuery('#quotes_all1 > div:gt(1)').wrapAll('<span class="extra_rates"></span>');
    //         jQuery('#cart_postal_code_result > div:gt(1)').wrapAll('<span class="extra_rates"></span>');
            
    //     } else {
            
    //         jQuery('#cart_postal_code_result div').hide();                                         
    //         jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();
            
    //         jQuery('#quotes_all1 > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
    //         jQuery('#cart_postal_code_result > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
    //         //jQuery('#cart_postal_code_result > .extra_rates, #quotes_all1 > .extra_rates').find('#pickup_from_factory').remove();
    //         //jQuery('#cart_postal_code_result  div:not:first').wrapAll('<div class="sorting_shipping_price"></div>');
    //     }
    // }

    jQuery("#edit-panes-delivery-delivery-postal-code").focusout(function () {
        var cart_postal_code_old_value = jQuery("#cart_postal_code").val();
        var tmp_checkout_postal_code = jQuery("#edit-panes-delivery-delivery-postal-code").val();
        if (cart_postal_code_old_value != tmp_checkout_postal_code) {
            jQuery("#cart_postal_code").val(tmp_checkout_postal_code);
            jQuery("#cart_postal_submit").trigger("click");
        }
    });

    var offset = 250;
    var duration = 200;
    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration + 100);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });

    jQuery('.back-to-top').click(function (event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
    });

    if (window.location.hash == "#collapseOne_panel" || window.location.hash == "#collapseThree_panel" ||
            window.location.hash == "#collapseFour_panel" || window.location.hash == "#collapseFive_panel" || window.location.hash == "#collapseSix_panel") {
        collapseOpenLinkPanel();
    }
    jQuery('#mySidenav .page-scroll, .attach_block .page-scroll, .choose_my_carrier_btn .page-scroll').click(function () {
        collapseOpenLinkPanel();
    });

    var triggerPopUpShippingRateBar = false;
    var tmp_selected_val = "";
    jQuery("#quote div.form-item-panes-quotes-quotes-quote-option").click(function (e) {
        tmp_selected_val = jQuery(this).find("label").find("input").val();
        triggerPopUpShippingRateBar = true;
    });


    jQuery(document).ajaxComplete(function () {
        jQuery("#opt_loaderimg").hide();
        jQuery(".opt_loaderimg").hide();
        if (jQuery.active == 1) {
            shippingRatesAjaxChecker = false;
            jQuery("#opt_loaderimg1").hide();
            jQuery("#opt_loaderimg2").hide();

            if (selectPreSelectedValue) {
                jQuery("#" + selected_shipping_id).trigger("click");
                selectPreSelectedValue = false;
            }
        }
        // SELECTED METHOD SHOW AT FIRST 
        //var sel_ele = "<div class='form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio selected-quote'>" + jQuery('#quotes_all1').find(".selected-quote").html() + "</div>";
        //alert(sel_ele);
        //jQuery('#quotes_all1').find(".selected-quote").remove();
        //jQuery('#quotes_all1').prepend(sel_ele);
        //jQuery('#cart_postal_code_result').prepend(sel_ele); //kp 
        
        
        if (jQuery(".node-wrapper").find(".item_qty_field").length > 0) {
            jQuery(".node-wrapper").each(function (idx, elem) {
                jQuery(elem).find(".item_qty_field").trigger("change");
            });
        }

        sortStripesDivs(); // on checkout page sort stripes according to price.

        jQuery("#quote div.form-item-panes-quotes-quotes-quote-option").click(function (e) {
            jQuery("#quote div.form-item-panes-quotes-quotes-quote-option").removeClass("selected_option_label");
            jQuery(this).addClass("selected_option_label");
            tmp_selected_val = jQuery(this).find("label").find("input").val();
            triggerPopUpShippingRateBar = true;
            setTimeout(function () {
                if (jQuery.active > 0) {
                    jQuery("#opt_loaderimg1").show();
                }
            }, 200);
        });
        if (jQuery(".page-cart-checkout #quote").length > 0) {
            jQuery("#quotes-pane .panel-body .help-block").hide();
            jQuery("#quotes-pane .panel-body .help-block").next().next().hide();

            jQuery("#selected_option_label").removeAttr("id").parent().parent().addClass("selected_option_label");

            if (triggerPopUpShippingRateBar) {
                jQuery("#cart_postal_code_result div").each(function (idx, elem) {
                    if (jQuery(elem).find("label").find("input").val() == tmp_selected_val) {
                        jQuery(elem).trigger("click");
                        triggerPopUpShippingRateBar = false;
                    }
                });
            }

            if (jQuery("input[id*='edit-panes-payment-details-cc-number']").length > 0) {
                updateUbercartCreditCartValue();
                jQuery("input[id*='edit-panes-payment-details-cc-cvv']").val(jQuery("#credit_card_code").val());
            }
        }

        if (global_if_country_changed != "") {
            if (global_if_country_changed == "shipping_information_state") {

                var tmp_orig_country = "edit-panes-delivery-delivery-zone";
            } else if (global_if_country_changed == "billing_information_state") {

                var tmp_orig_country = "edit-panes-billing-billing-zone";
            }
            jQuery("#" + global_if_country_changed).html(jQuery("select[id^='" + tmp_orig_country + "']").html());

            global_if_country_changed = "";
        }
        if (global_credit_card_details) {
            if (jQuery.active == 1) {
                setTimeout(function () {
                    jQuery("#credit_card_expires_month").html(jQuery("select[name='panes[payment][details][cc_exp_month]']").html());
                    jQuery("#credit_card_expires_year").html(jQuery("select[name='panes[payment][details][cc_exp_year]']").html());

                }, 300);
            }



            global_credit_card_details = false;
        }
        if (global_saved_make_started) {
            if (jQuery.active == 1) {
                jQuery("#model_select > option").each(function () {
                    if (jQuery(this).text() == saved_model) {
                        jQuery(this).prop('selected', true).attr("selected", "selected").parent().change();
                    }
                });
                global_saved_make_started = false;
            }
        }
        changeCarBarTotalText();

        if (checkIfMultiFieldsChanged > 1) {
            jQuery("input[id*='edit-panes-delivery-delivery-postal-code']").trigger("blur");
            jQuery("#opt_loaderimg1").hide();
        }
        checkIfMultiFieldsChanged = 0;
    });

    jQuery(".node_container .roofbag_type_button_container").click(function () {
        jQuery(".node_container .roofbag_type_button_container").removeClass("custom-active");
        jQuery(this).addClass("custom-active");

        jQuery(".node_container .attributes_cstm_wrapper").hide();
        jQuery(".node_container .attributes_cstm_wrapper[data-node='" + jQuery(this).data("node") + "']").show();

        jQuery(".node_container .node-prod-images").hide();
        jQuery(".node_container .node-prod-images[data-node='" + jQuery(this).data("node") + "']").show();

        if (jQuery(this).data("node") == "node-1") {
            jQuery("#cross_country_description_container").show();
            jQuery("#explorer_description_container").hide();
        } else if (jQuery(this).data("node") == "node-289") {
            jQuery("#explorer_description_container").show();
            jQuery("#cross_country_description_container").hide();
        }

    });

    jQuery("#selected_option_label").removeAttr("id").parent().parent().addClass("selected_option_label");

    jQuery("form[id^='uc-product-add-to-cart-form-" + 276 + "']").on("click","input[id^='edit-attributes-3-5']",function(){
        jQuery(".check-size-node-276-5").addClass('custom-active');
        jQuery(".check-size-node-276-6").removeClass('custom-active');
    });
    jQuery(".check-size-node-276-5").click(function(){
        jQuery("form[id^='uc-product-add-to-cart-form-" + 276 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
    });
    jQuery("form[id^='uc-product-add-to-cart-form-" + 276 + "']").on("click","input[id^='edit-attributes-3-6']",function(){
        jQuery(".check-size-node-276-5").removeClass('custom-active');
        jQuery(".check-size-node-276-6").addClass('custom-active');
    });
    jQuery(".check-size-node-276-6").click(function(){
        jQuery("form[id^='uc-product-add-to-cart-form-" + 276 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
    });

    jQuery("form[id^='uc-product-add-to-cart-form-" + 278 + "']").on("click","input[id^='edit-attributes-3-5']",function(){
        jQuery(".check-size-node-278-5").addClass('custom-active');
        jQuery(".check-size-node-278-6").removeClass('custom-active');
    });
    jQuery(".check-size-node-278-5").click(function(){
        jQuery("form[id^='uc-product-add-to-cart-form-" + 278 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
    });
    jQuery("form[id^='uc-product-add-to-cart-form-" + 278 + "']").on("click","input[id^='edit-attributes-3-6']",function(){
        jQuery(".check-size-node-278-5").removeClass('custom-active');
        jQuery(".check-size-node-278-6").addClass('custom-active');
    });
    jQuery(".check-size-node-278-6").click(function(){
        jQuery("form[id^='uc-product-add-to-cart-form-" + 278 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
    });
    
    jQuery(".check-size1").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";
        var tmp_attr_form_1 = "276";
        var tmp_attr_form_2 = "278";

        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
        
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_attr_form_1 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_attr_form_2 + "']").find("input[id^='edit-attributes-3-5']").trigger("click");
    });

    jQuery(".check-size2").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";
        var tmp_attr_form_1 = "276";
        var tmp_attr_form_2 = "278";

        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
    
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_attr_form_1 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_attr_form_2 + "']").find("input[id^='edit-attributes-3-6']").trigger("click");
    });

    jQuery("#uc-product-add-to-cart-form-1").find("input[id^='edit-attributes-2-3']").click();
    jQuery("#uc-product-add-to-cart-form-1--3").find("input[id^='edit-attributes-2-3']").click();
    jQuery("#uc-product-add-to-cart-form-1--4").find("input[id^='edit-attributes-2-3']").click();

    jQuery(".check-color1").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";

        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-2-3']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-2-3']").trigger("click");
    });

    jQuery(".check-color2").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];

        var tmp_form_1 = "1";
        var tmp_form_2 = "289";

        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-2-4']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-2-4']").trigger("click");
    });
    jQuery(".check-strap1").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";

        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-1-1']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-1-1']").trigger("click");
    });
    jQuery(".check-strap2").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-1-2']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-1-2']").trigger("click");
    });

    jQuery(".check-size1, .check-size2, .check-color1, .check-color2, .check-strap1, .check-strap2").click(function () {
        var tmp_node = jQuery(this).data("node").split("-")[0];
        var tmp_form_1 = "1";
        var tmp_form_2 = "289";

        
        if (!jQuery(this).hasClass("custom-active")) {
            if (jQuery.active > 0) {
                jQuery(this).parentsUntil("." + tmp_node + "_container").find(".opt_loaderimg").show();
            }
        }
        var check_selected_div = "";
        if (jQuery(this).hasClass("check-selected-size"))
            check_selected_div = ".check-selected-size";
        if (jQuery(this).hasClass("check-selected-strap"))
            check_selected_div = ".check-selected-strap";
        if (jQuery(this).hasClass("check-selected-color"))
            check_selected_div = ".check-selected-color";
        jQuery("." + tmp_node + "_container").find(check_selected_div).removeClass("custom-active");

        var clicked_button;
        if (jQuery(this).hasClass("check-size1"))
            clicked_button = ".check-size1";
        if (jQuery(this).hasClass("check-size2"))
            clicked_button = ".check-size2";
        if (jQuery(this).hasClass("check-color1"))
            clicked_button = ".check-color1";
        if (jQuery(this).hasClass("check-color2"))
            clicked_button = ".check-color2";
        if (jQuery(this).hasClass("check-strap1"))
            clicked_button = ".check-strap1";
        if (jQuery(this).hasClass("check-strap2"))
            clicked_button = ".check-strap2";

        jQuery("." + tmp_node + "_container").find(clicked_button).addClass("custom-active");
        // kp
        if (jQuery( ".check-selected-strap" ).hasClass( "custom-active" ) && jQuery( ".check-selected-size" ).hasClass( "custom-active" )) {
            jQuery(".show_carrier_button_in_how_soon_section").hide();
            jQuery(".show_cart_button_in_how_soon_section").show();
        }
        // end kp
        
    });

    jQuery('#edit-panes-delivery-delivery-postal-code2, #cart_postal_code').keypress(function (e) {
        //alert("Correct..."); //kp
        //var regex = new RegExp("^[a-zA-Z0-9]+$"); //kp
        var regex = new RegExp("^[a-zA-Z0-9\\-\\s]+$"); // kp
        
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str)) {
            return true;
        }

        e.preventDefault();
        return false;
    });

    jQuery(document).on('change', '#make_select', function () {
        car_selected = false;
        jQuery(".vehicle_feature button").removeClass("selected_feature");
        jQuery("#recomended_items_container").slideUp();
        var myKeyVals = {make: jQuery('#make_select :selected').val()}
        var saveData = jQuery.ajax({
            type: 'POST',
            url: '/custom/data/fetch',
            data: myKeyVals,
            success: function (resultData) {
                jQuery('#model_select').html(resultData);
                jQuery('#info_div').html('');
                jQuery('#info_div1').html('');
            }
        });
        saveData.error(function () {
            //  alert("Something went wrong");
        });
    });

    jQuery('.load_block_content').on('click', function (event) {

//        var modal = jQuery(this);
//        modal.find('.modal-body').html('');
//        var button = jQuery(event.relatedTarget);
//       
//        var block_id = button.data('block_id');
//        var block_title = button.data('whatever');

        var block_id = jQuery(this).attr('data-block_id');

        var block_title = jQuery(this).attr('data-whatever');

        var saveData = jQuery.ajax({
            type: 'POST',
            url: '/custom/data/block',
            data: {'block_id': block_id},
            success: function (resultData) {


                var block_content = resultData;
                //  modal.find('.modal-title').text(block_title);
                //  modal.find('.modal-body').html(block_content);

                jQuery('#load_block_content .modal-title').html(block_title);
                jQuery('#load_block_content .modal-body').html(block_content);
                jQuery('#load_block_content').modal("show");





            }
        });
    });



    jQuery(document).on('change', '#model_select', function () {
        jQuery(".vehicle_feature button").removeClass("selected_feature");
        jQuery("#recomended_items_container").slideUp();
        var myKeyVals = {make: jQuery('#make_select :selected').val(), model: jQuery('#model_select :selected').val()}
        var saveData = jQuery.ajax({
            type: 'POST',
            url: '/custom/data/fetch',
            data: myKeyVals,
            success: function (resultData) {
                if (resultData == "check-size1" || resultData == "check-size2") {
                    model_select_result = resultData;
                    car_selected = true;
                    showSuggestedCarrier();
                }
            }
        });
        saveData.error(function () {
            //  alert("Something went wrong");
        });
    });


    jQuery(document).on('change', '#body_style', function () {
        var myKeyVals = {make: jQuery('#make_select :selected').val(), model: jQuery('#model_select :selected').val(), body_style: jQuery('#body_style :selected').val()}
        var saveDatasize = jQuery.ajax({
            type: 'POST',
            url: '/custom/data/sizeinfo',
            data: myKeyVals,
            success: function (resultData) {
                jQuery('#modal-suggest').html(resultData);
                jQuery('#myModal11').modal('show');
            }
        });
        saveDatasize.error(function () {
            //  alert("Something went wrong");
        });
    });

    
    //jQuery("section#home-page").load(function () {
        jQuery("#cart_postal_code, #edit-panes-delivery-delivery-postal-code2").keyup(function (e) {
        
        clearTimeout(cart_postal_code_result_var);

        // kp start code for zipcode box allow space
        
        var zipcode_value = jQuery(this).val();
        //alert(zipcode_value);
        //var reg_val = new RegExp('/^ *$/');
        
        //var reg_val = new RegExp('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/');


        /*var regex = new RegExp("^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$");
        if (regex.test(jQuery(this).val())){
            alert("true" + zipcode_value);
        } else {
            alert("false" + zipcode_value);
        }*/
        //var letter_number = /^[0-9a-zA-Z]+$/; 
        var only_number = /^\d+$/;
        /*if((zipcode_value.match(only_number)))   
        {  
            
           alert("USA");
        }  
        else
        {   
           alert("canada");   
           
        } */ 

        //var re = new RegExp('^[a-zA-Z]{2}[0-9]{2}$');

        //var product_page_zipcode = jQuery('#edit-panes-delivery-delivery-postal-code2').val();
        //var checkout_page_zipcode = jQuery('#cart_postal_code').val();
        
        /*if (product_page_zipcode != '' || product_page_zipcode.length != 0) {
            var hasSpace = jQuery('#edit-panes-delivery-delivery-postal-code2').val().indexOf(' ')>=0;
        }

        if (checkout_page_zipcode != '' || checkout_page_zipcode.length != 0) {
            var hasSpace1 = jQuery('#cart_postal_code').val().indexOf(' ')>=0;
        }*/
        
        /*if ((typeof checkout_page_zipcode != 'undefined' || checkout_page_zipcode != '' || checkout_page_zipcode.length != 0) || (typeof product_page_zipcode != 'undefined' || product_page_zipcode != '' || product_page_zipcode.length != 0))  {
            var hasSpace = '';
            var hasSpace1 = '';*/
            //zipcode_value.match(reg_val);
            //var regex_test_val = zipcode_value.match(reg_val);
            
            //alert(zipcode_value.match(reg_val));
            
            var hasSpace = zipcode_value.indexOf(' ')>=0;
            //var hasSpace1 = jQuery('#cart_postal_code').val().indexOf(' ')>=0;   
            //var us_canada_test = RegExp("(^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$)|(^\d{5}(-\d{4})?$)");
            
            //alert(us_canada_test);
            //if (zipcode_value.indexOf(' ') === -1)
            if ((zipcode_value.match(only_number)))
            {
                
                if (zipcode_value.length < 5) {
                    return false;
                } else if (zipcode_value.length == 5) {
                    value_value = zipcode_value;
                    cart_postal_code_result();       
                } else {
                    value_value = zipcode_value;
                    cart_postal_code_result_var = setTimeout(cart_postal_code_result, 1000);
                }
             
            } else {
                
                if (zipcode_value.length < 6) {
                    return false;
                } else if (zipcode_value.length >= 6) {
                    value_value = zipcode_value;
                    cart_postal_code_result();       
                } /*else {
                    value_value = zipcode_value;
                    cart_postal_code_result_var = setTimeout(cart_postal_code_result, 1000);
                }*/
                
            }
        //}
       });
    //});
    function cart_postal_code_result() {
        if (!shippingRatesAjaxChecker) {
            if (jQuery('#quotes_all1, #cart_postal_code_result').html()) {
                
                jQuery('#quotes_all1, #cart_postal_code_result').empty();
            }
            shippingRatesAjaxChecker = true;
            jQuery('#quotes_all1, #cart_postal_code_result').hide();
            //        if (jQuery('#edit-panes-delivery-delivery-postal-code2').length) {
            //            value_value = jQuery('#edit-panes-delivery-delivery-postal-code2').val();
            //        } else if (jQuery("#cart_postal_code").length) {
            //            value_value = jQuery("#cart_postal_code").val();
            //        }
            tocountry = jQuery("#edit-country").val();

            if (value_value.length == 6) {
                value_value = [value_value.slice(0, 3), " ", value_value.slice(3)].join('');
                jQuery('#cart_postal_code, #edit-panes-delivery-delivery-postal-code2').val(value_value);
            }

            jQuery(".loaderimg_wraper").show();
            var saveData = jQuery.ajax({
                type: 'POST',
                url: '/custom/data/fetch_usps',
                data: {id: value_value},
                success: function (resultData) {
                    jQuery('#quotes_all1, #cart_postal_code_result').remove('.pickup_from_factory');
                    if (resultData != "wrong_postal") {
                        resultData = resultData.replace("USPS USPS", "USPS");

                        if (jQuery("#number_of_cart_item").val()) {
                            //jQuery('#quotes_all1, #cart_postal_code_result').html('<p class="custom_ship_option">Select Shipping Option</p>');
                            jQuery('#quotes_all1, #cart_postal_code_result').append(resultData);

                        } else {
                            jQuery('#quotes_all1, #cart_postal_code_result').html(resultData);
                        }
                        //                    jQuery('#cart_postal_code_result').html('<p class="custom_ship_option">Select Shipping Option</p>');
                        //                    jQuery('#cart_postal_code_result').append(resultData);
                        //                    jQuery('#quotes_all1').html(resultData);




                        jQuery('#quotes_all1 div, #cart_postal_code_result div').hide();

                        jQuery('#quotes_all1 #edit-panes-quotes-quote-button').hide();

                        var saveData2 = jQuery.ajax({
                            type: 'POST',
                            url: '/custom/data/fetch_fedex',
                            data: {fromzip: '92154', tozip: value_value, height: '6', width: '16', length: '12', weight: '9'},
                            success: function (resultData2) {
                                
                                jQuery('#quotes_all1, #cart_postal_code_result').append(resultData2);
                                jQuery('#quotes_all1 div, #cart_postal_code_result div').hide();


                                var saveData1 = jQuery.ajax({
                                    type: 'POST',
                                    url: '/custom/data/fetch_ups',
                                    data: {fromzip: '92154', tozip: value_value, height: '7', width: '13', length: '13', weight: '9'},
                                    success: function (resultData1) {
                                        jQuery(".loaderimg_wraper").hide();
                                        //console.log(resultData1);

                                        jQuery('#quotes_all1, #cart_postal_code_result').append(resultData1);
                                        jQuery('#quotes_all1 #edit-panes-quotes-quote-button').hide();
                                        sortUsingNestedText(jQuery('#quotes_all1'), "div", 'span.service_rate');
                                        sortUsingNestedText(jQuery('#cart_postal_code_result'), "div", 'span.service_rate');


                                        

                                        jQuery('#quotes_all1, #cart_postal_code_result').show();
                                        jQuery('#quotes_all1 div, #cart_postal_code_result div').hide();
                                         
                                        jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();
                                        //jQuery('#quotes_all1 div:nth-child(1), #cart_postal_code_result div:nth-child(1)').show(); // kp

                                        //jQuery('#quotes_all1 div:nth-child(3), #cart_postal_code_result div:nth-child(3)').show(); //kp
                                        //jQuery('#quotes_all1 div:nth-child(4), #cart_postal_code_result div:nth-child(4)').show();

                                        jQuery('#quotes_all1 > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
                                        jQuery('#cart_postal_code_result > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
                                        jQuery('.extra_rates').hide();

                                        //below code added kp
                                        
                                                //jQuery('#quotes_all1, #cart_postal_code_result').append('<a href="javascript:void(0)" style="color: inherit" onclick="showAllQuotes()" class="see_more_rates">See more rates</a>');
                                                                                
                                        
                                        

                                        // kp start

                                        //alert(jQuery('.see_more_rates').val());    


                                        if (jQuery('.see_more_rates').val() == null){
                                            //alert('in null');    
                                        }
                                        
                                        if (typeof jQuery('.see_more_rates').val() == 'undefined' || jQuery('.see_more_rates').val() == null){ //kp

                                            jQuery('#quotes_all1, #cart_postal_code_result').append('<a href="javascript:void(0)" style="color: inherit" onclick="showAllQuotes()" class="see_more_rates">Change shipping option</a>');
                                        } else {

                                        }

                                        // kp end

                                        //jQuery('#quotes_all1, #cart_postal_code_result').append('<a href="javascript:void(0)" style="color: inherit" onclick="showAllQuotes()" class="see_more_rates">See more rates</a>'); //comment by kp

                                        jQuery('#quotes_all1, #cart_postal_code_result').append('<a href="javascript:void(0)" style="color: inherit; display:none;" onclick="closeAllQuotes()" class="close_more_rates">See fewer rates</a>');
                                        jQuery('#cart_postal_code_result').html(jQuery('#quotes_all1').html());
                                        jQuery('#quotes_all1').html(jQuery('#cart_postal_code_result').html());
                                        jQuery("#cart_postal_code, #edit-panes-delivery-delivery-postal-code2").val(value_value);
                                        jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').trigger("click");
                                    }
                                });
                                saveData1.error(function () {
                                    //  alert("Something went wrong");
                                });
                            }
                        });
                        saveData2.error(function () {
                            //  alert("Something went wrong");
                        });
                        /* get FedEx Quotes*/
                    } else {
                        alert("Please re-enter your zip code");
                        jQuery('#quotes_all1, #cart_postal_code_result').empty();
                        jQuery(".loaderimg_wraper").hide();

                        // added by webplanex
                        var cartPriceSecond = jQuery("#order_subtotal_cart").html();
                        // if( jQuery("#uc-order-total-preview").length > 0 ){
                        //     if( jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").length > 0 ){
                        //         var totalTaxAmount = jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").html();
                        //         cartPriceSecond = cartPriceSecond.substr(1);
                        //         totalTaxAmount = totalTaxAmount.substr(1);
                        //         cartPriceSecond = "$"+( parseFloat(cartPriceSecond) + parseFloat(totalTaxAmount) );
                        //     }
                        // }

                        jQuery("#order_formated_total_amount").html(cartPriceSecond);
                        jQuery("#proceed_to_checkout_btn").addClass("disabled_cart_btn");
                        jQuery("#border-green-totle-botom").hide();
                    }
                }
            });
            saveData.error(function () {
                // alert("Something went wrong");
            });
            /* get UPs Quotes*/
            makeCartDivScrollAsScreenHeight();
        }
    }

    jQuery('#accordion').on('show.bs.collapse', function (e) {
        jQuery(e.target).siblings('.panel-heading').find('a span').removeClass('glyphicon-plus').addClass('glyphicon-minus');
    });

    jQuery('#accordion').on('hide.bs.collapse', function (e) {
        jQuery(e.target).siblings('.panel-heading').find('a span').removeClass('glyphicon-minus').addClass('glyphicon-plus');
    });

    jQuery('.navbar').on('show.bs.dropdown', function (e) {
        jQuery('.main-container').css('filter', 'blur(4px)');
        jQuery('.main-container').css('-webkit-filter', 'blur(4px)');
    });

    jQuery('.navbar').on('hide.bs.dropdown', function (e) {
        jQuery('.main-container').css('filter', 'blur(0px)');
        jQuery('.main-container').css('-webkit-filter', 'blur(0px)');
    });


    jQuery('#navbar-collapse').on('show.bs.collapse', function (e) {
        document.getElementById("fadeMe1").style.display = "block";
        jQuery("#see_my_order_btn_top").hide();
    });
    jQuery('#navbar-collapse').on('hide.bs.collapse', function (e) {
        document.getElementById("fadeMe1").style.display = "none";
        jQuery("#see_my_order_btn_top").show();
    });

    jQuery("html,body").on("click", ".shipping_rates_bar", function (e) {

        var sdata = jQuery(this).html();
        var savedzip = '';
        if (jQuery('#how_soon_carrier_btn').length > 0)
            jQuery("#how_soon_carrier_btn").css("display", "block");

        
        jQuery("#quotes_all1 div , #cart_postal_code_result div").removeClass('selected-quote'); //kp
        jQuery(this).addClass('selected-quote');
        
        var parent_html = jQuery(this).parents('.shipping_cal').html();  //kp

        jQuery('#cart_postal_code_result').html(parent_html); //kp
        jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();

        var savedzip = jQuery('#edit-panes-delivery-delivery-postal-code2').val(); // kp
        //var chk_quotes_all = jQuery('#quotes_all1').html();

        //alert(chk_quotes_all); 

        /*if (typeof savedzip !== "undefined") { //kp
            var sel_ele_cart = "<div class='form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio selected-quote'>" + jQuery('#cart_postal_code_result').find(".selected-quote").html() + "</div>"; //kp
           
            jQuery('#cart_postal_code_result').find(".selected-quote").removeClass("selected-quote"); //kp
            jQuery('#cart_postal_code_result').prepend(sel_ele_cart); //kp
        }*/ //kp

        jQuery('#quotes_all1').html(parent_html);  //kp

        
        var service_rate = jQuery(this).find('span.service_rate').text();
        var service_name = jQuery(this).find('span.service_name').text();
        var service_arrival = jQuery(this).find('span.service_arrival').text();
        var service_value = jQuery(this).find('input').val();
        var selected_shipping_id = jQuery(this).attr("id");
        var savedzip = jQuery('#edit-panes-delivery-delivery-postal-code2').val();
        //alert(savedzip + 'web'); //kp
        //kp
        if (typeof savedzip === "undefined" || savedzip == ''){
            savedzip = jQuery('#cart_postal_code').val();
        } //kp
        //alert(savedzip); //kp
        //alert(parent_html); //kp
        if (jQuery("#order_subtotal_cart").length) {
            var order_sub_total_amount =jQuery.trim(jQuery("#order_subtotal_cart").html()).slice(1);
            //alert(order_sub_total_amount);
        }
        service_rate = service_rate.replace('$', '');
        service_name = service_name.replace('USPS ', '');

        var sData1 = jQuery.ajax({
            type: 'POST',
            url: '/custom/data/create_session',
            data: {parent_html: parent_html, sdata: sdata, service_rate: service_rate, service_name: service_name, service_value: service_value, service_arrival: service_arrival, selected_shipping_id: selected_shipping_id, savedzip: savedzip},
            success: function (sData1) {
                var update_order_total_amount = parseFloat(service_rate) + parseFloat(order_sub_total_amount);
                update_order_total_amount = parseFloat(Math.round(update_order_total_amount * 100) / 100).toFixed(2);
                //alert("Service Rate" + service_rate + " Order Amount " + order_sub_total_amount + "  Update amount" + update_order_total_amount); //kp
                
                // added by webplanex
                // if( jQuery("#uc-order-total-preview").length > 0 ){
                //     if( jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").length > 0 ){
                //         var totalTaxAmount = jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").html();
                //         var totalTaxAmount = totalTaxAmount.substr(1);
                //         console.log(update_order_total_amount+"Four = "+totalTaxAmount);
                //         update_order_total_amount = parseFloat(update_order_total_amount) + parseFloat(totalTaxAmount);
                //     }
                // }
                jQuery('.order_formated_total_amount').text('$' + update_order_total_amount);
                jQuery('.shipping_selected').html(sdata);

                //jQuery('#cart_postal_code_result').html(parent_html); //kp
                //jQuery('#quotes_all1').html(parent_html); //kp

                //var shipping_data = jQuery('#cart_postal_code_result').html();
                //alert(parent_html);
                //var current_selected_method = jQuery('#cart_postal_code_result').find('.selected-quote').remove();
                //var shipping_data = jQuery('#cart_postal_code_result').html();
                //jQuery('#cart_postal_code_result').html(shipping_data); 

                // task no:16 [selected shipping method shown on top with grey color and other methods hide]

                var sel_ele_cart1 = "<div class='form-item shipping_rates_bar selected-for-sorting form-item-panes-quotes-quotes-quote-option form-type-radio radio selected-quote'>" + jQuery('#quotes_all1, #cart_postal_code_result').find(".selected-quote").html() + "</div>";
                //alert(sel_ele_cart1);
                jQuery('#quotes_all1, #cart_postal_code_result').find(".selected-quote").remove(); //kp
                jQuery('#quotes_all1, #cart_postal_code_result').prepend(sel_ele_cart1);  //kp

                
                
                //var prev_selected_item = jQuery('#quotes_all1, #cart_postal_code_result').find(".selected-for-sorting").html();
                //alert(prev_selected_item);

                // shipping price sorting 
                //sortUsingNestedText(jQuery('#quotes_all1'), "div", 'span.service_rate');
                //sortUsingNestedText(jQuery('#cart_postal_code_result'), "div", 'span.service_rate');
                // end

                
                
                
                // pickup from at factory code -- KP    
                // var myArray231 = [91901,91902,91903,91905,91906,91908,91909,91910,91911,91912,91913,91914,91915,91916,91917,91921,91931,91932,91933,91934,
                //                 91935,91941,91942,91943,91944,91945,91946,91947,91948,91950,91951,91962,91963,91976,91977,91978,91979,91980,92003,92004,
                //                 92007,92008,92009,92013,92014,92018,92019,92020,92021,92022,92023,92024,92025,92026,92027,92028,92029,92030,92033,
                //                 92036,92037,92038,92039,92040,,92046,92049,92051,92052,92054,92055,92056,92057,92058,92059,92060,92061,,92064,92065,
                //                 92066,92067,92068,92069,92070,92071,92072,92074,92075,92078,92079,92082,92083,92084,92085,,92088,92091,92092,
                //                 92093,92101,92102,92103,92104,92105,92106,92107,92108,92109,92110,92111,92112,92113,92114,92115,92116,92117,92118,92119,
                //                 92120,92121,92122,92123,92124,92126,92127,92128,92129,92130,92131,92132,92133,92134,92135,92136,92137,92138,92139,92140,
                //                 92142,92143,92145,92147,92149,92150,92152,92153,92154,92155,92159,92160,92162,92163,92164,92165,92166,92167,92168,92169,
                //                 92170,92171,92172,92173,92174,92175,92176,92177,92178,92182,92186,92190,92191,92192,92193,92194,92195,92196,92197,92198,92199];

                // needle = parseInt(savedzip);
                // var pick_from_factory = jQuery.inArray(needle, myArray231);
                // if(pick_from_factory !== -1) {
                    
                //     var factory_method_html = '<div id="pickup_from_factory" class="shipping_rates_bar form-item form-item-panes-quotes-quotes-quote-option form-type-radio radio"><label class="control-label"><input style="display:none;" name="panes[quotes][quotes][quote_option]" class="form-radio ajax-processed" type="radio" value="flatrate_1---0"><span class="service_name">Pick Up at factory</span><span class="service_rate">0.00</span><span class="service_arrival"><button data-toggle="modal" data-target="#myModal" class="btn_view_map"><span class="glyphicon glyphicon-map-marker"></span> View Map</button></span></label></div>';
                //     if( jQuery("#pickup_from_factory").length <= 0 ){
                //         jQuery(factory_method_html).insertBefore( jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first'));
                //     }
                    
                //     jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();
                //     jQuery('#quotes_all1 div:nth-child(2), #cart_postal_code_result div:nth-child(2)').show(); // kp
                    
                //     jQuery('#quotes_all1 > div:gt(1)').wrapAll('<span class="extra_rates"></span>');
                //     jQuery('#cart_postal_code_result > div:gt(1)').wrapAll('<span class="extra_rates"></span>');
                    
                // } else {
                    
                //     jQuery('#cart_postal_code_result div').hide();                                         
                //     jQuery('#quotes_all1 div:first, #cart_postal_code_result div:first').show();
                    
                //     jQuery('#quotes_all1 > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
                //     jQuery('#cart_postal_code_result > div:gt(0)').wrapAll('<span class="extra_rates"></span>');
                //     //jQuery('#cart_postal_code_result > .extra_rates, #quotes_all1 > .extra_rates').find('#pickup_from_factory').remove();
                //     //jQuery('#cart_postal_code_result  div:not:first').wrapAll('<div class="sorting_shipping_price"></div>');
                // }
                // end  -- KP
                jQuery('#cart_postal_code_result > .extra_rates').find('#pickup_from_factory').remove();
                jQuery('#quotes_all1 > .extra_rates').find('#pickup_from_factory').remove();
                /*jQuery('#pickup_from_factory').click(function () {

                    jQuery("#pickup_from_factory").addClass("selected-quote");
                    jQuery('span.extra_rates > div:contains("undefined")').remove();
                    jQuery('span.extra_rates > div:contains("undefined")').remove();
                    
                    changeCarBarTotalText();
                });*/
                    
                jQuery("#quotes_all1 > #pickup_from_factory").click(function () {
                    jQuery("#pickup_from_factory").addClass("selected-quote");
                    //jQuery('extra_rates').find('.selected-for-sorting').remove();
                    /*jQuery('span.extra_rates > div:contains("undefined")').remove();
                    jQuery('#quotes_all1 > div:contains("undefined")').remove();*/
                    jQuery('#quotes_all1 div').remove();
                });
                

                jQuery('.extra_rates').hide();
                jQuery('.close_more_rates').hide();
                jQuery('.see_more_rates').show();
                // end task no:16 code
                
                if (jQuery('#proceed_to_checkout_btn').length > 0) {
                    jQuery("#proceed_to_checkout_btn").removeClass("disabled_cart_btn");
                    jQuery("#border-green-totle-botom").show();
                }
            }
        });
    });

    // on checkout page sort stripes according to price.
    sortStripesDivs();

    if (typeof saved_make != "undefined") {
        jQuery("#make_select > option").each(function () {
            if (jQuery(this).html() == saved_make) {
                jQuery(this).prop('selected', true).attr("selected", "selected").parent().change();
                if (typeof saved_model != "undefined") {
                    global_saved_make_started = true;
                }
            }
        });
    }
    // Check if zip is entered before enter in every postal code field.
    if (typeof savedzip != "undefined") {
        jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").val(savedzip);
//        jQuery("#cart_postal_code").val(savedzip);
    }
    if (jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").length > 0) {
        if (jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").val().length > 0) {
            if (jQuery("#quotes_all1").children().length == 0) {
                jQuery("#delivery_postal_submit").trigger("click");
            }
        }
    }

    if (jQuery(".page-cart-checkout #quote").length > 0) {
        jQuery("#quotes-pane .panel-body .help-block").hide();
        jQuery("#quotes-pane .panel-body .help-block").next().next().hide();
    }
    jQuery('#collapseFive_panel').on('shown.bs.collapse', function () {
    }).on('show.bs.collapse', function () {
        jQuery('#collapseFive .field-item').css({left: '-600px', display: 'block'}).animate({"left": "0px"}, "slow");
    });




    jQuery('.qty-number').click(function (e) {
        e.preventDefault();

        fieldName = jQuery(this).attr('data-field');
        type = jQuery(this).attr('data-type');

        var input = jQuery(this).parent().parent().find("input");
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            if (type == 'minus') {
                if (currentVal > input.attr('min')) {
                    var cval = currentVal - 1;
                    input.val(cval).change();
                }
                if (parseInt(input.val()) == input.attr('min')) {
                    jQuery(this).attr('disabled', true);
                }

            } else if (type == 'plus') {

                if (currentVal < input.attr('max')) {
                    var cval = currentVal + 1;
                    input.val(cval).change();
                }
                if (parseInt(input.val()) == input.attr('max')) {
                    jQuery(this).attr('disabled', true);
                }
            }
        } else {
            input.val(0);
        }

    });
    jQuery('.item_qty_field').focusin(function () {
        jQuery(this).data('oldValue', jQuery(this).val());
    });
    jQuery('.item_qty_field').change(function () {

        minValue = parseInt(jQuery(this).attr('min'));
        maxValue = parseInt(jQuery(this).attr('max'));
        valueCurrent = parseInt(jQuery(this).val());

        name = jQuery(this).attr('name');
        if (valueCurrent >= minValue) {
            jQuery(".qty-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the minimum value was reached');
            jQuery(this).val(jQuery(this).data('oldValue'));
        }
        if (valueCurrent <= maxValue) {
            jQuery(".qty-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the maximum value was reached');
            jQuery(this).val(jQuery(this).data('oldValue'));
        }

        var tmp_price_class = "";
        if (jQuery(this).parents(".node-wrapper").length > 0) {
            tmp_price_class = ".uc-price";
        } else if (jQuery(this).parents(".accessories-wrapper").length > 0) {
            tmp_price_class = ".price-bottm-sec";
        }

        if (tmp_price_class != "") {
            var cur_product_price = jQuery("#" + jQuery(this).data("node")).find(tmp_price_class).text();
            cur_product_price = cur_product_price.replace('$', '');
            var uc_prince = parseFloat(cur_product_price * jQuery(this).val()).toFixed(2);
            jQuery(this).parent().next().find("span").html(uc_prince);

            jQuery("#" + jQuery(this).data("node")).find("input[id^='edit-qty']").val(jQuery(this).val());
        }

    });
    jQuery(".item_qty_field").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                        // Allow: home, end, left, right
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the Event
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });


    jQuery(".accordion-toggle").click(function () {
        var tmp_change = false;
        if (jQuery(this).find("span.glyphicon").hasClass("glyphicon-plus")) {
            tmp_change = true;
        }
        if (tmp_change) {
            closeAllBars();

            jQuery(this).removeClass('collapsed');
            jQuery(this).find('.glyphicon').removeClass('glyphicon-plus').addClass('glyphicon-minus');
            jQuery(this).parentsUntil(".panel-heading").next().addClass('in');
        } else {
            jQuery('.panel .accordion-toggle .glyphicon').removeClass('glyphicon-minus').addClass('glyphicon-plus');
        }
    });
    jQuery("#home-page .accordion-toggle").click(function () {
//        jQuery('html,body').animate({
//            scrollTop: jQuery(this).offset().top - getOffset()},
//                800);
        scrollToDiv(this);
    });

    //For new checkout page.
    if (jQuery(".page-cart-checkout #quote").length > 0) {
        jQuery(document).ajaxComplete(function () {
            getOrignalValues();
            jQuery("#payment_method_credit_card, #payment_method_paypal, #payment_method_pay_by_phone").removeClass("disabled_div");

            if (clicked_different_billing) {
                var selected_contry_value = jQuery("#shipping_information_country").find(":selected").val();
                jQuery("select[id^='billing_information_country'] option[value='" + selected_contry_value + "']").prop('selected', true).attr("selected", "selected").trigger("blur");
                jQuery(".removeOrignalValues").each(function (idx, elem) {
                    jQuery(this).val();
                });
                clicked_different_billing = false;
            }
        });

        if (jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").val().length > 0) {
            jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").trigger("blur");
            
            //var postal_code_billing = jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").val(); // add by KP
            //jQuery("input[id^='billing_information_zip_code']").val(postal_code_billing.toUpperCase()); // add by KP

            jQuery("#payment_method_credit_card, #payment_method_paypal, #payment_method_pay_by_phone").addClass("disabled_div");
        }
        
        // add by KP
        /*if (jQuery("input[id^='edit-panes-delivery-delivery-city']").val().length > 0) {
            var billing_city = jQuery("input[id^='edit-panes-delivery-delivery-city']").val();
            
            jQuery("input[id^='billing_information_city']").val(billing_city);
        }

        // add by KP

        jQuery('span#billing_information_state').text(jQuery("select[id^='edit-panes-delivery-delivery-zone'] option:selected").text());*/


        jQuery(document).ajaxSend(function () {
            jQuery("#opt_loaderimg1").show();
        });
        if (jQuery("#edit-panes-billing-copy-address:checked").length == 0) {
            jQuery("#edit-panes-billing-copy-address").trigger("click");
        }
        getOrignalValues();
    }
    getOrignalValues();

    jQuery("#change_payment_method").click(function () {
        jQuery("#credit_card_expires_month").val('');
        jQuery("#credit_card_expires_year").val('');
        jQuery("#credit_card_code").val('');
        // jQuery("#credit_card_number").val('');
        jQuery("#payment_method_credit_card_details").slideUp();
        jQuery("#change_payment_method").hide();
        jQuery("#payment_method_paypal, #payment_method_pay_by_phone").show();

    });
    jQuery("#payment_method_credit_card").on('click', function () {
        global_credit_card_details = true;

        // jQuery("#credit_card_expires_month").html(jQuery("input[id*='edit-panes-payment-details-cc-exp-month']").html());
        // jQuery("#credit_card_expires_year").html(jQuery("input[id*='edit-panes-payment-details-cc-exp-year']").html());

        jQuery("#credit_card_expires_month").html(jQuery("select[name='panes[payment][details][cc_exp_month]']").html());
        jQuery("#credit_card_expires_year").html(jQuery("select[name='panes[payment][details][cc_exp_year]']").html());
        jQuery("input[id*='edit-panes-payment-payment-method-credit']").trigger("click");



        if (jQuery.active > 0)
            jQuery("#opt_loaderimg1").show();
        jQuery("#payment_method_credit_card_details").slideDown();
        jQuery("#change_payment_method").show();
        jQuery("#payment_method_paypal, #payment_method_pay_by_phone").hide();
    });
    jQuery("#payment_method_paypal").click(function () {
        jQuery("input[id*='edit-panes-payment-payment-method-paypal-wps']").trigger("click");
        jQuery("#payment_method_credit_card_details").slideUp();
        if (newCheckoutLayoutValidation(false)) {
            fun_payment_method_selected();
        }
    });
    jQuery("#credit_card_submit_payment").click(function () {
        updateUbercartCreditCartValue();
        jQuery("input[id*='edit-panes-payment-details-cc-cvv']").val(jQuery("#credit_card_code").val());
        if (newCheckoutLayoutValidation(true)) {
            fun_payment_method_selected();
            jQuery("#opt_loaderimg1").show();
            creditCardCheckErrors();
        }
    });
    jQuery("#payment_method_pay_by_phone").click(function () {
        if (newCheckoutLayoutValidation(false)) {
            if (jQuery("input[id*='edit-panes-payment-payment-method-cod']").is(":checked")) {
                jQuery("input[id*='edit-panes-delivery-delivery-postal-code']").trigger("blur");
            } else {
                jQuery("input[id*='edit-panes-payment-payment-method-cod']").trigger("click");
            }
            jQuery(document).ajaxComplete(function () {
                window.location.href = "/cart";
            });
        } else {
            jQuery("#opt_loaderimg1").hide();
        }
        jQuery("#payment_method_credit_card_details").slideUp();
    });
    jQuery("#same_as_shipping").click(function () {
        jQuery(this).addClass("actvie");
        jQuery("#different_as_shipping").removeClass("actvie");
        if (jQuery("#edit-panes-billing-copy-address:checked").length == 0) {
            jQuery("#edit-panes-billing-copy-address").trigger("click");
        }
        jQuery("#billing_information_section").slideUp();
    });
    jQuery("#different_as_shipping").click(function () {
        jQuery(this).addClass("actvie");
        jQuery("#same_as_shipping").removeClass("actvie");
        if (jQuery("#edit-panes-billing-copy-address:checked").length > 0) {
            jQuery("#edit-panes-billing-copy-address").trigger("click");
        }
        jQuery("#billing_information_section").slideDown();
        clicked_different_billing = true;
    });
    //For new checkout page.

    if (typeof localStorage['selected_feature'] != 'undefined') {
        triggerSelectedFeature();
    }

//    jQuery("#credit_card_number").keyup(function () {
//        if (jQuery(this).val().length == 4 || jQuery(this).val().length == 9 || jQuery(this).val().length == 14) {
//            jQuery(this).val(jQuery(this).val() + " ")
//        }
//    });


//    jQuery(function ($) {
//        $('[data-numeric]').payment('restrictNumeric');
//        $('.credit_card_number').payment('formatCardNumber');
//
//    });
    jQuery(function ($) {
        $(".credit").credit();
    });

//    var lastChar = 0;
//    jQuery('#credit_card_number').on('keyup change', function (e) {
//        var val = jQuery(this).val();
//
//        if (e.which != 8)
//        {
//            if (val.length < 19) {
//
//                var newval = '';
//                val = val.replace(/\s/g, '');
//
//                for (var i = 0; i < val.length; i++) {
//                    if (i % 4 == 0 && i > 0)
//                        newval = newval.concat(' ');
//                    newval = newval.concat(val[i]);
//
//                }
//                jQuery(this).val(newval);
//            } else {
//                e.preventDefault();
//                jQuery(this).val(val.substring(0, 19));
//            }
//
//        } else {
//            var lastChar = globalval[globalval.length - 1];
//
//            if (lastChar == " ") {
//                val = val.slice(0, -1);
//
//                jQuery(this).val(val);
//            }
//        }
//
//        globalval = jQuery(this).val();
//
//
//    });

//    jQuery('#credit_card_number').on('keydown change', function (e) {
//
//        var val = this.value;
//        if (e.which != 8)
//        {
//            if (val.length < 19) {
//                if (val.replace(/\s/g, '').length % 4 == 0 && val.length !=0) {
//                    jQuery(this).val(val + ' ');
//                }
//            } else {
//                 e.preventDefault();
//                jQuery(this).val(val);
//            }
//
//        } else {
//            var lastChar = val[val.length - 1];
//
//
//            if (lastChar === " ") {
//
//                val = val.slice(0, -1);
//
//                jQuery(this).val(val);
//            }
//        }
//
//    });


    jQuery(".cart_qty_inputs").blur(function (e) {
        jQuery("#cart_postal_code").keyup();
        jQuery('html, body').animate({
            scrollTop: jQuery("#cart_postal_code").offset().top
        }, 2000);
    });

    jQuery(".cart_qty_inputs").keyup(function () {
        if (jQuery(this).val().length > 0) {
            if (jQuery(this).val() < 1) {
                jQuery(this).val("1")
            } else if (jQuery(this).val() > 10) {
                jQuery(this).val("10")
            }

            var form = jQuery(this).closest("form");
            jQuery.post('/custom/data/change_cart_quantity', jQuery(form).serialize(), function (data, status) {
                cart_subtotal = 0;
                jQuery(".inner-wapper-cart-imgs").each(function () {
                    if (!isNaN(jQuery(this).find(".price-botm-cart-sec strong").html().slice(1)))
                        cart_subtotal += jQuery(this).find(".price-botm-cart-sec strong").html().slice(1) * jQuery(this).find(".cart_qty_inputs").val();
                });
                tmp_all_qty = 0;
                jQuery(".cart_qty_inputs").each(function () {
                    tmp_all_qty += parseInt(jQuery(this).val());
                });
                jQuery("#sub-total-box-animation em").html(tmp_all_qty);
                jQuery("#order_subtotal_cart").html("$" + cart_subtotal.toFixed(2));
                if (jQuery("#cart_shipping_selected .service_rate").length) {
                    var order_total = eval(jQuery("#cart_shipping_selected .service_rate").html().slice(1)) + eval(cart_subtotal);
                } else {
                    var order_total = eval(jQuery("#order_subtotal_cart").html().slice(1));
                }
                // added by webplanex
                // if( jQuery("#uc-order-total-preview").length > 0 ){
                //     if( jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").length > 0 ){
                //         var totalTaxAmount = jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").html();
                //         totalTaxAmount = totalTaxAmount.substr(1);
                //         order_total = ( parseFloat(order_total) + parseFloat(totalTaxAmount) );
                //     }
                // }
                jQuery("#order_formated_total_amount").html("$" + order_total.toFixed(2));

            });

        }
    });

    if (jQuery("#home-page").length) {
        jQuery("#home-page").addClass("cstm_popup_cart_block_opened");
    }

    if (jQuery("#collapseSix .vehicle_feature button").length) {
        jQuery("#collapseSix .vehicle_feature button").click(function () {
            if (!jQuery(this).hasClass("how_attach_selected")) {
                collapseSixVehicleFeatureDiv = jQuery(this);
                if (jQuery("#collapseSix .vehicle_feature .attach_block.how_attach_selected_div_show").length) {
                    jQuery("#collapseSix .vehicle_feature .attach_block.how_attach_selected_div_show").slideUp(function () {
                        jQuery("#collapseSix .vehicle_feature button").removeClass("how_attach_selected").next().removeClass("how_attach_selected_div_show");
                        jQuery(collapseSixVehicleFeatureDiv).addClass("how_attach_selected").next().addClass("how_attach_selected_div_show").slideDown(function () {

//                            jQuery('html,body').animate({
//                                scrollTop: jQuery(jQuery(this).prev()).offset().top - getOffset()},
//                                    800);
                            scrollToDiv(jQuery(this).prev());
                        });
                    });
                } else {
                    jQuery(collapseSixVehicleFeatureDiv).addClass("how_attach_selected").next().addClass("how_attach_selected_div_show").slideDown(function () {

//                        jQuery('html,body').animate({
//                            scrollTop: jQuery(jQuery(this).prev()).offset().top - getOffset()},
//                                800);
                        scrollToDiv(jQuery(this).prev());
                    });
                }
            } else {
                jQuery("#collapseSix .vehicle_feature .attach_block.how_attach_selected_div_show").slideUp();
                jQuery(this).removeClass("how_attach_selected").next().removeClass("how_attach_selected_div_show");
            }
        });
    }
    changeCarBarTotalText();
    if (jQuery(".accessories-wrapper .add-to-cart").length) {
        if (data_carrier == 1) {
            jQuery(".accessories-wrapper .add-to-cart").each(function () {
                jQuery(this).find("input[id^='edit-attributes-3-5']").trigger("click");
            });
        } else if (data_carrier == 2) {
            jQuery(".accessories-wrapper .add-to-cart").each(function () {
                jQuery(this).find("input[id^='edit-attributes-3-6']").trigger("click");
            });
            jQuery(".accessories-wrapper").each(function () {
                var old_val = jQuery(this).find(".accessories-prdct-price").html().split("$")[1];
                var new_val = jQuery(this).find(".add-to-cart").find("div[id^='edit-attributes-3--'] div:last-child").text().split("+$")[1];
                if (typeof new_val != "undefined") {
                    var result = parseFloat(old_val) + parseFloat(new_val);
                    jQuery(this).find(".accessories-prdct-price").html("$" + result.toFixed(2));
                }
            });
        }
    }

    jQuery("#shipping_information_zip_code").change(function () {
        jQuery("#cart_postal_code").val(jQuery(this).val());
        cart_postal_code_result();
    });

    jQuery(document).click(function (event) {
        var clickover = jQuery(event.target);
        var _opened = jQuery("#navbar-collapse").hasClass("in");
        if (_opened === true && !clickover.hasClass("navbar-toggle")) {
            jQuery("i#phone_btn").click();
        }
    });

    jQuery("#proceed_to_checkout_btn").click(function () {
        if (jQuery(this).hasClass("disabled_cart_btn")) {
            var checkclick = jQuery(this);
            if (checkclick.hasClass("clicked-once")) {
                jQuery("#cart_zip_flash").addClass("flash").fadeTo(100, 0.3, function () {
                    jQuery("#cart_zip_flash").fadeTo(500, 1.0, function () {
                        jQuery("#cart_zip_flash").fadeTo(100, 0.3, function () {
                            jQuery("#cart_zip_flash").fadeTo(500, 1.0, function () {
                                //jQuery("#cart_zip_flash").removeClass("flash");
                            });
                        });
                    });
                });

            } else {
                jQuery("#cart_zip_flash").addClass("flash").fadeTo(100, function () {

                });

                checkclick.addClass("clicked-once");
            }
        }
    });

    jQuery("#cart_postal_code").click(function () {
        jQuery("#cart_zip_flash").removeClass("flash");
        // jQuery("#cart_postal_code").blur(); 
    });

    jQuery(document).ready(function () {

        //  jQuery(".select_color li").first().addClass("active");

        jQuery(".select_color li a").click(function () {
            jQuery(".select_color li").removeClass("active");
            jQuery(this).closest("li").addClass("active");

        });

//             jQuery(".custom-active").on('click',function() {
//             jQuery("#node-289product-image0").closest('li').addClass("active");
//            });

    })


    /*jQuery(".clearable-input>span[data-clear-input]").click(function () { 
        jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").trigger("blur");
        jQuery("#opt_loaderimg1").hide();
        showHeaderCartBar();
        open_shopingcart_block_new();
        jQuery("#cart_postal_code").val("");
        jQuery('#cart_postal_code_result').empty();
        jQuery("#proceed_to_checkout_btn").addClass("disabled_cart_btn");
        jQuery("#border-green-totle-botom").hide();
//         jQuery("#shipping_information_zip_code").val("");
//          jQuery("#shipping_information_state").val("");
//           jQuery("#shipping_information_city").val("");

    });*/

    //kp
    jQuery("a[data-clear-input]").click(function () {
        
        jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").trigger("blur");
        jQuery("#opt_loaderimg1").hide();
        showHeaderCartBar();
        open_shopingcart_block_new();
        jQuery("#cart_postal_code").val("");
        jQuery('#cart_postal_code_result').empty();
        jQuery("#proceed_to_checkout_btn").addClass("disabled_cart_btn");
        jQuery("#border-green-totle-botom").hide();

        //kp
        jQuery.post('/custom/data/remove_zip_code', function (data, status) {
            jQuery('.selected_zip').hide(); // kp
            jQuery('#cart_shipping_selected').hide(); // kp
            jQuery('p.lbl_shipping_option_selected').hide(); // kp
            jQuery('#shipping_information_zip_code').show(); // kp
            jQuery('#shipping_information_zip_code').show(); // kp
            jQuery('#cart_postal_code').attr('type', 'text');
        });
        

    });

    jQuery("select[data-clear-input]").change(function () {
        jQuery("input[id^='edit-panes-delivery-delivery-postal-code']").trigger("blur");
        jQuery("#opt_loaderimg1").hide();
        showHeaderCartBar();
        open_shopingcart_block_new();
        jQuery("#cart_postal_code").val("");
        jQuery('#cart_postal_code_result').empty();
        jQuery("#proceed_to_checkout_btn").addClass("disabled_cart_btn");
        jQuery("#border-green-totle-botom").hide();

        //location.reload();  //kp
        //kp
        
        jQuery.post('/custom/data/remove_zip_code', function (data, status) {
            jQuery('.selected_zip').hide(); // kp
            jQuery('#cart_shipping_selected').hide(); // kp
            jQuery('p.lbl_shipping_option_selected').hide(); // kp
            jQuery('#shipping_information_zip_code').show(); // kp
            jQuery('#shipping_information_zip_code').show(); // kp
            jQuery('#cart_postal_code').attr('type', 'text');
            //location.reload();
            //jQuery('#opt_loaderimg1').show(); // kp
            


        });   //kp
        //alert("HELLO....12345"); //kp
//         jQuery("#shipping_information_zip_code").val("");
//          jQuery("#shipping_information_state").val("");
//           jQuery("#shipping_information_city").val("");

    });
    
//    if (jQuery("#cart_postal_code").val().length) {
//        jQuery("#cart_postal_code").trigger("keyup");
//    }

    if (open_cart_default) {
        jQuery("#see_my_order_btn_top").trigger("click");
    }
});

function askUserMobileNumber(elem) {
    jQuery("#mobile_display_modal").modal("show");

}

function tog(v) {
    return v ? 'addClass' : 'removeClass';
}

function goToSection(destination_div) {
    closeAllBars();
//    jQuery('html,body').animate({
//        scrollTop: jQuery(destination_div).offset().top - getOffset()},
//            800);
    scrollToDiv(destination_div);
}

function closeAllBars() {
    jQuery('.panel .accordion-toggle').addClass('collapsed');
    jQuery('.panel .accordion-toggle .glyphicon').removeClass('glyphicon-minus').addClass('glyphicon-plus');
    jQuery('.panel .panel-collapse').addClass('out').removeClass('in');

    jQuery("#collapseSix .vehicle_feature .attach_block.how_attach_selected_div_show").hide();
    jQuery("#collapseSix .vehicle_feature button").removeClass("how_attach_selected").next().removeClass("how_attach_selected_div_show");
}

//var lastScrollTop = 0;
//jQuery(window).scroll(function (event) {
//        var st = jQuery(this).scrollTop();
//        if (st > lastScrollTop) { // downscroll code
//            jQuery(".header-perspective").addClass("cart-bar-affix-bottom").removeClass("cart-bar-affix-top");
//        } else { // upscroll code
//            jQuery(".header-perspective").removeClass("cart-bar-affix-bottom").addClass("cart-bar-affix-top");
//        }
//        lastScrollTop = st;
//});

//var lastScrollTop = 1;
//jQuery(window).bind('mousewheel DOMMouseScroll', function (event) {
//    var st = jQuery(this).scrollTop();
//    if (st != lastScrollTop) {
//        if (event.originalEvent.wheelDelta > 0 || event.originalEvent.detail < 0) {
//            jQuery("header#navbar").removeClass("cart-bar-affix-top").addClass("cart-bar-affix");
//        } else {
//            jQuery("header#navbar").addClass("cart-bar-affix-top").removeClass("cart-bar-affix");
//        }
//    }
//    if (st != 0)
//        lastScrollTop = st;
//    else
//        lastScrollTop = 1;
//});

function showHeaderCartBar() {
    jQuery("header#navbar").removeClass("cart-bar-affix-top").addClass("cart-bar-affix");
}

/*jQuery(window).scroll(function() {    
 var scroll = jQuery(window).scrollTop();
 if (scroll >= 5) {
 jQuery("header#navbar").addClass("cart-bar-affix-top");
 }else { // upscroll code
 jQuery("header#navbar").removeClass("cart-bar-affix-top");
 
 }
 });
 jQuery(window).scroll(function() {    
 var scroll = jQuery(window).scrollTop();
 if (scroll >= 15) {
 jQuery("header#navbar").addClass("cart-bar-affix-close");
 }else { // upscroll code
 jQuery("header#navbar").removeClass("cart-bar-affix-close");
 
 }
 });*/

function clearRecommendedCarriers(elem) {
    //Change texts
    
    jQuery("#select_your_carrier_section").html('Select your carrier');
    jQuery(".select_size_txt").html('Select size');
    jQuery(".select_type_straps_txt").html('Select type of straps');

    jQuery(".clear_recommended_carriers_container").html("");
    jQuery(".node_container .check-selected-size, .node_container .check-selected-strap").removeClass("custom-active").removeClass("disabled_div").show();

    jQuery("#make_select option:selected, #model_select option:selected").removeAttr("selected");
    jQuery("#make_select, #model_select").prop("selected", false).attr("selected", "selected").trigger("change");
    jQuery(".vehicle_feature button").removeClass("selected_feature");

    car_selected = false;
    localStorage['selected_feature'] = "";
}


// added by webplanex

jQuery(document).ready(function(){
    jQuery('.shippingOrderTotalDetail').remove();
    if( jQuery("#edit-panes-delivery-delivery-postal-code").length > 0 ){
        jQuery("#edit-panes-delivery-delivery-postal-code").blur();
    }
});

jQuery(document).ready(function(){

    if (jQuery("#uc-order-total-preview").length > 0) {
        // if( jQuery(".shippingOrderTotalDetail").length <= 0 ) {   
            var shippingDetailTable = jQuery("#uc-order-total-preview").html();
            jQuery("<div class='shippingOrderTotalDetail'><table class='table'>"+shippingDetailTable+"</table></div>").insertBefore('.payment_method_section');
            if( jQuery("#shipping_information_state").val() != 12 ){
                jQuery('div.shippingOrderTotalDetail').find('.line-item-tax_subtotal').remove();
                jQuery('div.shippingOrderTotalDetail').find('.line-item-tax').remove();
            }
            setInterval(function(){ 
                var shippingDetailTable = jQuery("#uc-order-total-preview").html();
                jQuery(".shippingOrderTotalDetail table.table").html(shippingDetailTable);
                if( jQuery("#shipping_information_state").val() != 12 ){
                    jQuery('div.shippingOrderTotalDetail').find('.line-item-tax_subtotal').remove();
                    jQuery('div.shippingOrderTotalDetail').find('.line-item-tax').remove();
                }
            }, 100);
        // }
    }
    
    // if (jQuery("#order_formated_total_amount").length) {
    //     var cartPrice = jQuery("#order_formated_total_amount").html();
    //     if( jQuery("#uc-order-total-preview").length > 0 ){
    //         if( jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").length > 0 ){
    //             var totalTaxAmount = jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").html();
    //             cartPrice = cartPrice.substr(1);
    //             totalTaxAmount = totalTaxAmount.substr(1);
    //             cartPrice = "$"+( parseFloat(cartPrice) + parseFloat(totalTaxAmount) );
    //         }
    //     }
    //     jQuery("#order_formated_total_amount").html(cartPrice);
    // }

});

function changeCarBarTotalText() {
    if (jQuery("#order_formated_total_amount").length) {
        var cartPrice = jQuery("#order_formated_total_amount").html();
        jQuery("#car_bar_total_text").html(cartPrice);
        if (jQuery(".selected-quote").length) {
            jQuery("#car_bar_total_heading").html("Total&nbsp;&nbsp;&nbsp;");
        } else {
            jQuery("#car_bar_total_heading").html("Sub-total:");
        }
    }
    
}

function toggleThankYouPageAccordians(elem) {
    if (jQuery(elem).find("span.glyphicon").hasClass("glyphicon-minus")) {
        jQuery(elem).find("span.glyphicon").removeClass("glyphicon-minus").addClass("glyphicon-plus")
    } else {
        jQuery(elem).find("span.glyphicon").removeClass("glyphicon-plus").addClass("glyphicon-minus")
    }
    jQuery(elem).next().slideToggle(100);
}

var cart_subtotal = 0;
function newCheckoutLayoutValidation(check_credit_card) {
    if (jQuery("#shipping_information_country").val() === null) {
        alert(jQuery("#shipping_information_country").attr("data-valid-error") + " is required");
        return false;
    } else if (jQuery("#shipping_information_first_name").val().length == 0) {
        alert(jQuery("#shipping_information_first_name").attr("data-valid-error") + " is required");
        return false;
    } else if (jQuery("#shipping_information_last_name").val().length == 0) {
        alert(jQuery("#shipping_information_last_name").attr("data-valid-error") + " is required");
        return false;
    } else if (jQuery("#shipping_information_street1").val().length == 0) {
        alert(jQuery("#shipping_information_street1").attr("data-valid-error") + " is required");
        return false;
    } else if (jQuery("#shipping_information_city").val().length == 0) {
        alert(jQuery("#shipping_information_city").attr("data-valid-error") + " is required");
        return false;
    } else if (jQuery("#shipping_information_zip_code").val().length == 0) {
        alert(jQuery("#shipping_information_zip_code").attr("data-valid-error") + " is required");
        return false;
    }

    var shipping_information_state_selected = false;
    
    jQuery('#shipping_information_state option').each(function () {
        if (jQuery('#shipping_information_state').val() != 0) {
            shipping_information_state_selected = true;
        }
    });
    if (!shipping_information_state_selected) {
        alert(jQuery("#shipping_information_state").attr("data-valid-error") + " is required");
        return false;
    }

    if (check_credit_card) {
        var creditcardnumber = updateUbercartCreditCartValue();

        if (creditcardnumber.length == 0) {
            alert("Credit Card Number is required");
            return false;
        } else if (jQuery("#credit_card_code").val().length == 0) {
            alert(jQuery("#credit_card_code").attr("data-valid-error") + " is required");
            return false;
        }
    }

    if (check_credit_card && jQuery("#edit-panes-billing-copy-address:checked").length == 0) {
        if (jQuery("#billing_information_country").val() === null) {
            alert(jQuery("#billing_information_country").attr("data-valid-error") + " is required");
            return false;
        } else if (jQuery("#billing_information_first_name").val().length == 0) {
            alert(jQuery("#billing_information_first_name").attr("data-valid-error") + " is required");
            return false;
        } else if (jQuery("#billing_information_last_name").val().length == 0) {
            alert(jQuery("#billing_information_last_name").attr("data-valid-error") + " is required");
            return false;
        } else if (jQuery("#billing_information_street1").val().length == 0) {
            alert(jQuery("#billing_information_street1").attr("data-valid-error") + " is required");
            return false;
        } else if (jQuery("#billing_information_city").val().length == 0) {
            alert(jQuery("#billing_information_city").attr("data-valid-error") + " is required");
            return false;
        } else if (jQuery("#billing_information_zip_code").val().length == 0) {
            alert(jQuery("#billing_information_zip_code").attr("data-valid-error") + " is required");
            return false;
        }else if(jQuery("#billing_information_state_auto_fill").val().length == 0){
            alert(jQuery("#billing_information_state_auto_fill").attr("data-valid-error") + " is required");
            return false;
        }

        // var billing_information_state_selected = false;
        // jQuery('#billing_information_state option').each(function () {
        //     if (jQuery('#billing_information_state').val() != 0) {
        //         billing_information_state_selected = true;
        //     }
        // });
        // if (!billing_information_state_selected) {
        //     alert(jQuery("#billing_information_state").attr("data-valid-error") + " is required");
        //     return false;
        // }
    }
    return true;
}

function updateUbercartCreditCartValue() {
    var creditcardnumber = jQuery("#creditpart1").val() + jQuery("#creditpart2").val() + jQuery("#creditpart3").val() + jQuery("#creditpart4").val();
    jQuery("input[id*='edit-panes-payment-details-cc-number']").val(creditcardnumber);
    return creditcardnumber;
}

function creditCardCheckErrors() {
    if (jQuery("#uc_stripe_messages").text() != "") {
        alert(jQuery("#uc_stripe_messages").html());
        jQuery("#uc_stripe_messages").html("");
        jQuery("#opt_loaderimg1").hide();

        if (jQuery("input[id*='edit-panes-payment-details-cc-number']").length > 0) {
            if (jQuery("input[id*='edit-panes-payment-details-cc-number']").val().length == 0) {
                updateUbercartCreditCartValue();
            }
        }
        if (jQuery("input[id*='edit-panes-payment-details-cc-cvv']").length > 0) {
            if (jQuery("input[id*='edit-panes-payment-details-cc-cvv']").val().length == 0) {
                jQuery("input[id*='edit-panes-payment-details-cc-cvv']").val(jQuery("#credit_card_code").val());
            }
        }

    } else {
        setTimeout(creditCardCheckErrors, 100);
    }
}

function triggerSelectedFeature() {
    if (jQuery.active == 0) {
        jQuery(localStorage['selected_feature']).addClass("selected_feature");
    } else {
        setTimeout(triggerSelectedFeature, 100);
    }
}

function getOrignalValues() {
    jQuery(".getOrignalValues").each(function (idx) {
        var tmp_orig_elem_id = jQuery(this).attr("data-origFieldId");
        if (this.tagName == "INPUT") {
            jQuery(this).val(jQuery("input[id*='" + tmp_orig_elem_id + "']").val());
        } else if (this.tagName == "SELECT") {
            var defaultCountryId = "edit-panes-delivery-delivery-country";
            if( jQuery("#"+tmp_orig_elem_id).length <= 0 ){
                tmp_orig_elem_id = defaultCountryId;
            }
            jQuery(this).html(jQuery("select[id*='" + tmp_orig_elem_id + "']").html());
            
            jQuery('span#shipping_information_state').text(jQuery("select#shipping_information_state option:selected").text());
            jQuery('span#billing_information_state').text(jQuery("select#shipping_information_state option:selected").text()); // add by KP

            jQuery('select#shipping_information_state').hide();
//            if (tmp_orig_elem_id == "edit-panes-billing-billing-country" || tmp_orig_elem_id == "edit-panes-billing-billing-zone") {
//                jQuery(this).find("option:selected").removeAttr("selected");
//                jQuery(this).prop("selected", false).attr("selected", "selected");
//            }
        }
    });
}

function fun_payment_method_selected() {
    if (jQuery.active == 0) {
        jQuery("#edit-continue").trigger("click");
    } else {
        setTimeout(fun_payment_method_selected, 300);
    }
}

var global_credit_card_details = false;
var global_if_country_changed = "";
function changeOrignalFieldValue(elem) {
    var tmp_orig_elem_id = jQuery(elem).attr("data-origFieldId");
    if (tmp_orig_elem_id == "edit-panes-delivery-delivery-country") {
        global_if_country_changed = "shipping_information_state";
    } else if (tmp_orig_elem_id == "edit-panes-billing-billing-country") {
        global_if_country_changed = "billing_information_state";
    }
    var elemAttrId = jQuery(elem).attr("id");
    if( elemAttrId == 'billing_information_state_auto_fill' ){
        var tmp_selected_value = jQuery(elem).val();
        jQuery("select[id*='" + tmp_orig_elem_id + "'] option").removeAttr("selected");
        jQuery("select[id*='" + tmp_orig_elem_id + "'] option:contains('" + tmp_selected_value + "')").prop("selected", true).attr("selected", "selected");
        jQuery("select[id*='" + tmp_orig_elem_id + "']").change();
    }else if (elem.tagName == "INPUT") {
        jQuery("input[id*='" + tmp_orig_elem_id + "']").val(jQuery(elem).val()).trigger("blur");
    } else if (elem.tagName == "SELECT") {
        var tmp_selected_value = jQuery(elem).find(":selected").val();
        jQuery("select[id*='" + tmp_orig_elem_id + "'] option").removeAttr("selected");
        jQuery("select[id*='" + tmp_orig_elem_id + "'] option[value='" + tmp_selected_value + "']").prop("selected", true).attr("selected", "selected");
        jQuery("select[id*='" + tmp_orig_elem_id + "']").change();
    }
}

function changeImageAccordingToAttributes(elem) {
    var tmp_node_id = jQuery(elem).data("node");
    var change_image = false;
    var tmp_rack = "";
    var tmp_color = "";
    var tmp_size = "";
    var tmp_item = tmp_node_id.split("-");

    if (tmp_item[1] == "1")
        tmp_item = "CC";
    else if (tmp_item[1] == "289")
        tmp_item = "EX";

    if (checkIfAttributeIsSelected(elem, ".check-strap1")) {
        jQuery("#" + tmp_node_id + "product-image1 img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Rack-Bk.jpg");
        jQuery("#" + tmp_node_id + "product-image1main img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Rack-Bk.jpg");
        jQuery("#" + tmp_node_id + "product-image2 img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Rack-Gy.jpg");
        jQuery("#" + tmp_node_id + "product-image2main img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Rack-Gy.jpg");
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2")) {
        jQuery("#" + tmp_node_id + "product-image1 img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Bk.jpg");
        jQuery("#" + tmp_node_id + "product-image1main img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Bk.jpg");
        jQuery("#" + tmp_node_id + "product-image2 img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Gy.jpg");
        jQuery("#" + tmp_node_id + "product-image2main img").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier-Gy.jpg");
    }

    if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color1") && checkIfAttributeIsSelected(elem, ".check-size1")) {
        tmp_rack = "-Rack";
        tmp_color = "-Bk";
        tmp_size = "-11";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color1") && checkIfAttributeIsSelected(elem, ".check-size2")) {
        tmp_rack = "-Rack";
        tmp_color = "-Bk";
        tmp_size = "-15";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color2") && checkIfAttributeIsSelected(elem, ".check-size1")) {
        tmp_rack = "-Rack";
        tmp_color = "-Gy";
        tmp_size = "-11";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color2") && checkIfAttributeIsSelected(elem, ".check-size2")) {
        tmp_rack = "-Rack";
        tmp_color = "-Gy";
        tmp_size = "-15";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color1") && checkIfAttributeIsSelected(elem, ".check-size1")) {
        tmp_rack = "";
        tmp_color = "-Bk";
        tmp_size = "-11";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color1") && checkIfAttributeIsSelected(elem, ".check-size2")) {
        tmp_rack = "";
        tmp_color = "-Bk";
        tmp_size = "-15";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color2") && checkIfAttributeIsSelected(elem, ".check-size1")) {
        tmp_rack = "";
        tmp_color = "-Gy";
        tmp_size = "-11";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color2") && checkIfAttributeIsSelected(elem, ".check-size2")) {
        tmp_rack = "";
        tmp_color = "-Gy";
        tmp_size = "-15";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color1")) {
        tmp_rack = "-Rack";
        tmp_color = "-Bk";
        tmp_size = "";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1") && checkIfAttributeIsSelected(elem, ".check-color2")) {
        tmp_rack = "-Rack";
        tmp_color = "-Gy";
        tmp_size = "";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color1")) {
        tmp_rack = "";
        tmp_color = "-Bk";
        tmp_size = "";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2") && checkIfAttributeIsSelected(elem, ".check-color2")) {
        tmp_rack = "";
        tmp_color = "-Gy";
        tmp_size = "";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap1")) {
        tmp_rack = "-Rack";
        tmp_color = "-Bk";
        tmp_size = "";
        change_image = true;
    } else if (checkIfAttributeIsSelected(elem, ".check-strap2")) {
        tmp_rack = "";
        tmp_color = "-Bk";
        tmp_size = "";
        change_image = true;
    }

    if (change_image) {
        jQuery('.node_container .node-prod-images[data-node="' + tmp_node_id + '"] .field-name-uc-product-image').hide();
        jQuery("#" + tmp_node_id + "_change_image").parent().parent().parent().show();
        jQuery("#" + tmp_node_id + "_change_image").attr("src", "https://www.roofbag.com/Images/Products/RoofBag-" + tmp_item + "-Car-Top-Carrier" + tmp_rack + tmp_color + tmp_size + ".jpg");
    }
}

function checkIfAttributeIsSelected(elem, check_class) {
    return jQuery(elem).closest(".attributes_cstm_wrapper").find(check_class).hasClass("custom-active");
}

function collapseOpenLinkPanel() {
    closeAllBars();

    setTimeout(function () {
        var panel = window.location.hash;
        var sub_panel = panel.split('_panel');
        sub_panel = sub_panel[0];
        jQuery(panel + ' .accordion-toggle').removeClass('collapsed');
        jQuery(panel + ' .accordion-toggle .glyphicon').removeClass('glyphicon-plus');
        jQuery(panel + ' .accordion-toggle .glyphicon').addClass('glyphicon-minus');
        jQuery(panel + ' ' + sub_panel).addClass('in');
        jQuery(panel + ' ' + sub_panel).css('height', 'auto');
        if (page_loaded) {
            section_scroll();
        }
    }, 300);
    jQuery('.field-item').css({left: '-600px', display: 'block'}).animate({"left": "0px"}, "slow");

}

function sortStripesDivs() {
    if (jQuery(".page-cart-checkout #quote").length > 0) {

        jQuery(".page-cart-checkout #quote div").each(function (index) {
            var val = jQuery(this).find(".service_rate").text();
            var myString = val.substr(val.indexOf("$") + 1);
            if (myString != "")
                jQuery(this).attr("data-sort", myString);

            jQuery(this).next().appendTo(jQuery(this));
        });

        sortUsingText(jQuery('#quote'), "div");

        jQuery(".page-cart-checkout #quote div").each(function (index) {
            jQuery(this).children().last().insertAfter(jQuery(this));
        });
    }
}


var car_selected = false;
function showSuggestedCarrier() {
    if (car_selected && jQuery(".vehicle_feature button.selected_feature").length > 0) {

        if (model_select_result == "check-size1") {
            var show_div = ".check-size1";
            var hide_div = ".check-size2";
            var tmp_size_edit_attributes_id = "input[id^='edit-attributes-3-5']";
        } else if (model_select_result == "check-size2") {
            var show_div = ".check-size2";
            var hide_div = ".check-size1";
            var tmp_size_edit_attributes_id = "input[id^='edit-attributes-3-6']";
        } else {
            return false;
        }

        //Change texts
    
        jQuery("#select_your_carrier_section").html('<span class="based_on_notice">Based on your entries these carriers fit your car.</span>');
        jQuery(".select_size_txt").html('Recommended Size');
        jQuery(".select_type_straps_txt").html('Recommended straps (included)');
        jQuery(".clear_recommended_carriers_container").html("");
        jQuery(".clear_recommended_carriers_container").html('<div class="clear_recommended_carriers" onclick="clearRecommendedCarriers(this);">Clear recommended carriers</div>');

        // Show-Hide buttons
        jQuery(".node_container").find(show_div).show().addClass("custom-active").addClass("disabled_div");
        jQuery(".node_container").find(hide_div).hide().removeClass("custom-active").removeClass("disabled_div");

        // Trigger Fields
        jQuery("form[id^='uc-product-add-to-cart-form-1']").find(tmp_size_edit_attributes_id).trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-289']").find(tmp_size_edit_attributes_id).trigger("click");
        if (jQuery.active > 0) {
            jQuery("#opt_loaderimg1").show();
        }

        setTimeout(function () {
            var show_elem = "";
            var hide_elem = "";
            var id_elem = "";
            if (jQuery("#block_third.selected_feature").length > 0 || jQuery("#block_fourth.selected_feature").length > 0) {
                show_elem = ".check-strap2";
                hide_elem = ".check-strap1";
                id_elem = "input[id^='edit-attributes-1-2']";
            } else if (jQuery("#block_first.selected_feature").length > 0 || jQuery("#block_five.selected_feature").length > 0 || jQuery("#block_second.selected_feature").length > 0) {
                show_elem = ".check-strap1";
                hide_elem = ".check-strap2";
                id_elem = "input[id^='edit-attributes-1-1']";
            }
            if (show_elem != "" && hide_elem != "" && id_elem != "") {
                jQuery(".node_container").find(show_elem).show().addClass("custom-active").addClass("disabled_div");
                jQuery(".node_container").find(hide_elem).hide().removeClass("custom-active").removeClass("disabled_div");

                jQuery("form[id^='uc-product-add-to-cart-form-1']").find(id_elem).trigger("click");
                jQuery("form[id^='uc-product-add-to-cart-form-289']").find(id_elem).trigger("click");
            }

            closeAllBars();
//            jQuery('html,body').animate({
//                scrollTop: jQuery("#select_your_carrier_section").offset().top - getOffset()},
//                    800);
            scrollToDiv("#select_your_carrier_section");
            
        }, 2000);

    }
}

/* For opening attach block */
function openBlock(id) {

    if (!car_selected) {
        jQuery("#load_message_display_modal .modal-body").html('<div class="row"><div class="col-md-12">Please select your car make, model, and rack style.</div></div>')
        jQuery("#load_message_display_modal").modal("show");
    }

    jQuery("#collapseSix .vehicle_feature .attach_block.how_attach_selected_div_show").hide();
    jQuery("#collapseSix .vehicle_feature button").removeClass("how_attach_selected").next().removeClass("how_attach_selected_div_show");

    jQuery(".vehicle_feature button").removeClass("selected_feature");
    jQuery("#" + id).addClass("selected_feature");
    localStorage['selected_feature'] = "#" + id;

    showSuggestedCarrier();
}

function openProductSection() {
//    jQuery('html,body').animate({
//        scrollTop: jQuery(".region-content").offset().top - getOffset()},
//            800);
    scrollToDiv(".region-content");
    jQuery('#node-1').slideDown('slow', function () {
        jQuery('.field-item').css({left: '-600px', display: 'block'}).animate({"left": "0px"}, "slow");
    });
}

function openProductSectionBottom() {
    jQuery('#node-1').slideDown('slow', function () {
        jQuery('.field-item').css({left: '-600px', display: 'block'}).animate({"left": "0px"}, "slow");
    });
    setTimeout(function () {
//        jQuery('html,body').animate({
//            scrollTop: jQuery("#recomended_items_container").offset().top - getOffset()},
//                800);
        scrollToDiv("#recomended_items_container");
    }, 100);
}

function section_scroll() {
    var ScrolLDiv = window.location.hash;
    if (jQuery("#home-page").length > 0) {
        if (ScrolLDiv) {
            scrollToDiv(ScrolLDiv);
            closeNav();
        }
    }
}

function scrollToDiv(ScrolLDiv) {
    jQuery("header#navbar").addClass("cart-bar-affix-top").removeClass("cart-bar-affix");
    jQuery('html,body').animate({
        scrollTop: jQuery(ScrolLDiv).offset().top - getOffset()},
            800);
}

function getOffset() {
    var offset = 0;
    if (jQuery("header#navbar").hasClass("cart-bar-affix-top")) {
        var offset1 = jQuery('#admin-menu').height();
        var offset2 = jQuery('.navbar-fixed-top').height();
        var offset3 = jQuery('#see_my_order_btn_top').outerHeight(true);
        var offset = offset1 + offset2 + offset3 + 0.5;
    }

    return offset;
}

jQuery(window).load(function () {
    if (jQuery("#see_my_order_btn_top").length > 0) {
        jQuery(".stctik-cart-body").css("margin-top", jQuery("#see_my_order_btn_top").outerHeight(true));
    }
    if (recently_added_to_cart) {
        jQuery('#sub-total-box-animation').addClass('animated tada');
        setTimeout(function () {
            jQuery('#sub-total-box-animation').removeClass('animated tada');
        }, 1000);
    }
    section_scroll();
    page_loaded = true;
});



function trim(value) {
    return value.replace(/^\s+|\s+$/g, "");
}
function clickAccessoriesCartButton(form_submit_id, action) {
    if (action != "")
        jQuery("#node-" + form_submit_id + " form").attr('action', "/" + action);
    jQuery('#edit-submit-' + form_submit_id).trigger('click');
    cart_postal_code_result();
}
function clickCartButton(container, action) {

    if (jQuery(container).find('.check-selected-size').hasClass('custom-active') &&
//            jQuery(container).find('.check-selected-color').hasClass('custom-active') &&
            jQuery(container).find('.check-selected-strap').hasClass('custom-active')) {
            
        if (action != "") {
            jQuery("form[id^='uc-product-add-to-cart-form-1'], form[id^='uc-product-add-to-cart-form-289']").attr('action', "/" + action);
        }
        jQuery('#edit-submit-' + jQuery(container + " .roofbag_type_button_container.custom-active").data("node").split('-')[1]).trigger('click');
    } else {
        var error_msg = "Please select:<ul>";
        if (!jQuery(container).find('.check-selected-size').hasClass('custom-active')) {
            error_msg += "<li>Size</li>";
        }
        if (!jQuery(container).find('.check-selected-strap').hasClass('custom-active')) {
            error_msg += "<li>Type of straps</li>";
        }
//        if (!jQuery("#" + form_submit_id).find('.check-selected-color').hasClass('custom-active')) {
//            error_msg += "<li>Color</li>";
//        }
        jQuery("#load_message_display_modal .modal-body").html('<div class="row"><div class="col-md-12">' + error_msg + '</div></div>')
        jQuery("#load_message_display_modal").modal("show");
    }
}

function getCountryBasedQuotes()
{
    //alert(jQuery('#shippinglocCountry').val());
    //alert(jQuery('#shippinglocCountry :selected').text());
    /* get UPs Quotes*/
    var tozip = '';
    if (jQuery('#shippinglocCountry :selected').text().indexOf('India') > -1)
    {
        tozip = '160059';
    }
    var saveData1 = jQuery.ajax({
        type: 'POST',
        url: '/custom/data/fetch_ups',
        data: {fromzip: '92154', tozip: tozip, tocountry: jQuery('#shippinglocCountry :selected').text(), height: '7', width: '13', length: '16', weight: '9'},
        success: function (resultData1) {
            jQuery('#quotes_all1').html(resultData1);

            jQuery('#quotes_all1 #edit-panes-quotes-quote-button').hide();
            jQuery(".loaderimg_wraper").hide();
            sortUsingNestedText(jQuery('#quotes_all1'), "div", 'span.service_rate');

            jQuery('#quotes_all1 div').hide();
            jQuery('#quotes_all1 div:first').show();
            //jQuery('#quotes_all1 div:nth-child(2)').show();
            jQuery('#quotes_all1').append('<a href="javascript:void(0)" style="color: inherit" onclick="showAllQuotes()" class="see_more_rates">See more rates</a>');


            //jQuery('#cart_postal_code_result').html(resultData1); //kp
            //var sel_ele_cart = "<div class='form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio selected-quote'>" + jQuery('#cart_postal_code_result').find(".selected-quote").html() + "</div>";
            //alert(sel_ele); 
            //jQuery('#cart_postal_code_result').find(".selected-quote").remove(); //kp
            //jQuery('#cart_postal_code_result').prepend(sel_ele_cart); //kp


        }
    });
    saveData1.error(function () {
        //  alert("Something went wrong");
    });
}

function saveToCart(e)
{
    // var service_rate = jQuery("#"+id).find('span.service_rate').text();
    // var order_total_amount = jQuery("#order_total_amount").val();
    // var update_order_total_amount = parseFloat(service_rate)+parseFloat(order_total_amount);
    // jQuery('.order_formated_total_amount').text('$'+update_order_total_amount);
    // var sData1 = jQuery.ajax({
    //  type: 'POST',
    //  url: '/custom/data/create_session',
    //  data: {sdata:jQuery("#"+id).html(),service_rate:parseFloat(service_rate)},
    //  success: function(sData1) {
    //      jQuery("#quotes_all1 div , #cart_postal_code_result div").removeClass('selected-quote');
    //      jQuery("#"+id).addClass('selected-quote');
    //  }
    // });
}

function sortUsingNestedText(parent, childSelector, keySelector) {

    var items = parent.children(childSelector).sort(function (a, b) {
        var vA = parseFloat(jQuery(keySelector, a).text());
        var vB = parseFloat(jQuery(keySelector, b).text());
        return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
    });
    jQuery(parent).find(keySelector).each(function () {
        jQuery(this).html("$" + jQuery(this).html());
    });
    parent.append(items);
}

function sortUsingText(parent, childSelector) {
    var items = parent.children(childSelector).sort(function (a, b) {
        var vA = parseFloat(jQuery(a).attr('data-sort'));
        var vB = parseFloat(jQuery(b).attr('data-sort'));
        return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
    });
    parent.append(items);
}

function showAllQuotes() {
    jQuery('.shipping_cal .extra_rates').slideDown('fast', function () {
        jQuery('.shipping_cal .extra_rates div').slideDown('fast');
    });
    jQuery('.see_more_rates').hide();
    jQuery('.close_more_rates').show();
}
function closeAllQuotes() {
    jQuery('.shipping_cal .extra_rates').slideUp('fast', function () {
        jQuery('.shipping_cal .extra_rates div').slideUp('fast');
    });
    jQuery('.see_more_rates').show();
    jQuery('.close_more_rates').hide();
}

function showImageProduct(id, node_id, color) {
    jQuery('.node-prod-images[data-node="' + node_id + '"] .field-name-uc-product-image').hide();
    jQuery('#' + id + 'main').show();

    var tmp_form_1 = "1";
    var tmp_form_2 = "289";

    if (color == 'black') {
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-2-3']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-2-3']").trigger("click");
    } else if (color == 'gray') {
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_1 + "']").find("input[id^='edit-attributes-2-4']").trigger("click");
        jQuery("form[id^='uc-product-add-to-cart-form-" + tmp_form_2 + "']").find("input[id^='edit-attributes-2-4']").trigger("click");
    }
}

function openNav() {
    if (document.getElementById("mySidenav").style.width == "60%")
    {
        document.getElementById("mySidenav").style.width = "0";
        document.getElementById("fadeMe").style.display = "none";
        jQuery("#head-perspective").addClass("header-perspective");
    } else
    {
        document.getElementById("mySidenav").style.width = "60%";
        document.getElementById("fadeMe").style.display = "block";
        jQuery("#head-perspective").removeClass("header-perspective");
    }
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("fadeMe").style.display = "none";
}

function open_shopingcart_block() {
    jQuery('#myModal_cart_content').modal('show');
}

function open_shopingcart_block_new() {
    if (jQuery("#see_my_order_btn_top span.glyphicon").hasClass("glyphicon-minus")) {
        
        var url = window.location;
        var checkoutPageUrl = "cart/checkout";
        var regex = new RegExp('\\b' + checkoutPageUrl + '\\b');
        if( url.href.search(regex) !== -1 ){
            jQuery("#proceed_to_checkout_btn").click();
            location.reload();
        }else{
            jQuery("#top_cart_bar_main_title").html(global_main_cart_title);
            jQuery("#home-page").addClass("cstm_popup_cart_block_opened");
            jQuery("#see_my_order_btn_top span.glyphicon").removeClass("glyphicon-minus").addClass("glyphicon-plus");
            jQuery("body").css("overflow", "auto");

            jQuery("#cstm_popup_cart_block_contianer").slideUp();
            jQuery("#main-container-before").removeClass("cart_bar_opened_container").show();
            
            jQuery("#main-container-before, footer").show();
            cart_opened = true;
            jQuery("#head-perspective").addClass("header-perspective");
        }

    } else {
        
        global_main_cart_title = jQuery("#top_cart_bar_main_title").html();
        jQuery("#top_cart_bar_main_title").html("Cart");
        jQuery("#home-page").removeClass("cstm_popup_cart_block_opened");
        jQuery("#see_my_order_btn_top span.glyphicon").removeClass("glyphicon-plus").addClass("glyphicon-minus");
        jQuery("#cstm_popup_cart_block_contianer").slideDown();
//        jQuery("body").css("overflow", "hidden");
        jQuery("body").css("overflow", "visible");
        jQuery("#main-container-before").addClass("cart_bar_opened_container").hide();
        
        jQuery("#main-container-before, footer").hide();
        cart_opened = false;
        jQuery("#head-perspective").removeClass("header-perspective");
    }
}

jQuery(function () {
    if (jQuery("#cart_postal_code_result .selected-quote").length == 0) {
        if (selected_shipping_id != "") {
            selectPreSelectedValue = true;
        }
        jQuery("#cart_postal_code").keyup();
    }

    jQuery('#mobile_no').on('keypress', function (e) {
        return e.metaKey || // cmd/ctrl
                e.which <= 0 || // arrow keys
                e.which == 8 || // delete key
                /[0-9]/.test(String.fromCharCode(e.which)); // numbers
    })

    jQuery(".send_mobile_text").click(function (e) {
        var mobile_num = jQuery("#mobile_no").val();

        if (mobile_num.length == 10 && mobile_num.length > 0) {
            mobile_num = mobile_num;

            jQuery(".text_me_link_container").closest('a').attr('href', "/?mobile=" + mobile_num);
            jQuery("#mobile_display_modal").modal("hide");
            window.location.href = "/cart/checkout/?mobile=" + mobile_num;

        } else {
            jQuery("#mobile_no").css({
                "border-color": "red",
                "box-shadow": "0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(255, 0, 0, 0.5)",
                "outline": "0 none"
            });
            jQuery("#verify-country-code-voice").css({
                "border-color": "red",
                "box-shadow": "0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(255, 0, 0, 0.5)",
                "outline": "0 none"
            });

            return false;
        }



    });




});


/* Slide Tap Feature */
/*   jQuery(".carousel-inner").click(function(){
 
 jQuery('#views-bootstrap-carousel-1').removeClass("stickyhead");
 jQuery("#views-bootstrap-carousel-1").animate({ marginTop: '0px' }, 1000);
 jQuery(".carousel").css('z-index','inherit');
 
 }); */
/*  For fetching models based on selection */

// $('.attributes-box-modal-lg').on('show.bs.modal', function (e) {
//   // do something...
// })

// function openProductSection()
// {
//  //jQuery( ".galleryview .gallery-slides" ).css( "height","height:312px;" );
//  //jQuery( ".galleryview .gallery-slides" ).attr( "width","auto;" );
//  jQuery('#node-1').attr('style','display:block');

//  var offset1 = jQuery('#admin-menu').height();
//  var offset2 = jQuery('.navbar-fixed-top').height();
//  var offset  = offset1 + offset2;
//  jQuery('html,body').animate({
//         scrollTop: jQuery(".region-content").offset().top-offset},
//         'slow');
//  //alert(jQuery( ".galleryview .gallery-thumbs .wrapper ul").html());


// }


function makeCartDivScrollAsScreenHeight() {


//    jQuery('.cart-img-desp-sattik-show').css('height', window.innerHeight - jQuery("#admin-menu").height() - jQuery(".navbar.affix-top").height() - jQuery(".navbar.affix").height() - jQuery("#see_my_order_btn_top").outerHeight());
    // jQuery('.cart-img-desp-sattik-show').css('height', window.innerHeight - jQuery("#head-perspective").height());
}




jQuery(window).load(makeCartDivScrollAsScreenHeight);
jQuery(window).resize(makeCartDivScrollAsScreenHeight);

jQuery(document).ready(function () {
    if (window.location.href.indexOf("checkout") > -1) {
        jQuery("#mynav").css("display", "none");
    }
    outputText = '';

    beforeCreditcardcompleteVar = "";
    jQuery('.creditcardcomplete').on('keydown', function (e) {
        if (jQuery(this).val().length == 4 && (e.which != 8 && e.which != 229)) {
            jQuery(this).next('.creditcardcomplete').focus();
        }
    });

    jQuery('.creditcardcomplete').on('keypress', function (e) {
        if (e.metaKey || // cmd/ctrl
                e.which <= 0 || // arrow keys
                e.which == 8 || // delete key
                /[0-9]/.test(String.fromCharCode(e.which))) {
            if (e.which === 8 || e.which === 229) {
                if (!jQuery(this).val()) {
                    var tmp_credit_val = jQuery(this).prev().val();
                    jQuery(this).prev().val('').val(tmp_credit_val);
                    jQuery(this).prev().focus();
                }
            } else if (jQuery(this).val().length >= "4") {
                return false;
            }
        } else {
            return false;
        }
    });

    jQuery(".creditcardcomplete").keyup(function (e) {
        if (e.which === 8 || e.which === 229) {
            if (!jQuery(this).val() || jQuery(this).val() == beforeCreditcardcompleteVar) {
                var tmp_credit_val = jQuery(this).prev().val();
                jQuery(this).prev().val('').val(tmp_credit_val);
                jQuery(this).prev().focus();
            }
            if (jQuery(this).val() == beforeCreditcardcompleteVar) {
                var str = jQuery(this).prev().val();
                str = str.substring(0, str.length - 1);
                jQuery(this).prev().val(str);
            }
        } else if (jQuery(this).val().length >= "4") {
            jQuery(this).next('.creditcardcomplete').focus();
        }
        beforeCreditcardcompleteVar = jQuery(this).val();
    });

    jQuery('#creditholder').on('click', function (e) {
        if (e.target.id == "creditpart2") {
            if (jQuery("#creditpart2").val().length == 0) {
                if (jQuery("#creditpart1").val().length < 4) {
                    jQuery("#creditpart1").focus();
                }
            }
        } else if (e.target.id == "creditpart3") {
            if (jQuery("#creditpart3").val().length == 0) {
                if (jQuery("#creditpart1").val().length < 4) {
                    jQuery("#creditpart1").focus();
                } else if (jQuery("#creditpart2").val().length < 4) {
                    jQuery("#creditpart2").focus();
                }
            }
        } else if (e.target.id == "creditpart4") {
            if (jQuery("#creditpart4").val().length == 0) {
                if (jQuery("#creditpart1").val().length < 4) {
                    jQuery("#creditpart1").focus();
                } else if (jQuery("#creditpart2").val().length < 4) {
                    jQuery("#creditpart2").focus();
                } else if (jQuery("#creditpart3").val().length < 4) {
                    jQuery("#creditpart3").focus();
                }
            }
        } else if (e.target === this) {
            if (jQuery("#creditpart4").val().length > 0 || jQuery("#creditpart3").val().length == 4) {
                var tmp_credit_val = jQuery("#creditpart4").val();
                jQuery("#creditpart4").val('').val(tmp_credit_val);
                jQuery("#creditpart4").focus();
            } else if (jQuery("#creditpart3").val().length > 0 || jQuery("#creditpart2").val().length == 4) {
                var tmp_credit_val = jQuery("#creditpart3").val();
                jQuery("#creditpart3").val('').val(tmp_credit_val);
                jQuery("#creditpart3").focus();
            } else if (jQuery("#creditpart2").val().length > 0 || jQuery("#creditpart1").val().length == 4) {
                var tmp_credit_val = jQuery("#creditpart2").val();
                jQuery("#creditpart2").val('').val(tmp_credit_val);
                jQuery("#creditpart2").focus();
            } else {
                var tmp_credit_val = jQuery("#creditpart1").val();
                jQuery("#creditpart1").val('').val(tmp_credit_val);
                jQuery("#creditpart1").focus();
            }
        }

    });

    jQuery(".creditcardcomplete").keyup(function () {
        updateUbercartCreditCartValue();
    });

    jQuery("input[data-origfieldid^='edit-panes-delivery-delivery-'], input[data-origfieldid^='edit-panes-billing-billing-'], select[data-origfieldid^='edit-panes-delivery-delivery-'], select[data-origfieldid^='edit-panes-billing-billing-']").change(function () {
        checkIfMultiFieldsChanged++;
        jQuery("input[id*='edit-panes-delivery-delivery-postal-code']").trigger("blur");
        jQuery("#opt_loaderimg1").hide();
    });


    /*jQuery('#pickup_from_factory').click(function (){
        alert("map button");
        jQuery('#pickup_from_factory').find('.form-item').addClass('selected-quote');
        jQuery('.shipping_rates_bar').find('.form-item').removeClass('selected-quote');
        jQuery('#load_factory_map').show();
        jQuery('#load_factory_map').modal("show");
    });*/

    jQuery("html,body").on("click", "#pickup_from_factory", function (e) {
        
        jQuery('#cart_shipping_selected').remove();

        jQuery('#quotes_all1 > .extra_rates').find('#pickup_from_factory').remove();
        jQuery('#cart_postal_code_result > .extra_rates').find('#pickup_from_factory').remove();
        
        jQuery('#cart_postal_code_result, #quotes_all1').find('#pickup_from_factory').addClass('selected-quote');
        jQuery('#cart_postal_code_result, #quotes_all1').find('.shipping_rates_bar').removeClass('selected-quote');
       
        return false;

        var service_rate = jQuery('#pickup_from_factory').find('span.service_rate').text();
        
        if (jQuery("#order_subtotal_cart").length) {
            var order_sub_total_amount =jQuery.trim(jQuery("#order_subtotal_cart").html()).slice(1);
            //alert(order_sub_total_amount);
        }
        //alert(service_rate.slice(1));


        var update_order_total_amount = parseFloat(service_rate.slice(1)) + parseFloat(order_sub_total_amount);
        //alert(update_order_total_amount);
        update_order_total_amount = parseFloat(Math.round(update_order_total_amount * 100) / 100).toFixed(2);
        // added by webplanex
        // if( jQuery("#uc-order-total-preview").length > 0 ){
        //     if( jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").length > 0 ){
        //         var totalTaxAmount = jQuery("#uc-order-total-preview .line-item-tax .price span.uc-price").html();
        //         totalTaxAmount = totalTaxAmount.substr(1);
        //         update_order_total_amount = ( parseFloat(update_order_total_amount) + parseFloat(totalTaxAmount) );
        //     }
        // }

        jQuery('.order_formated_total_amount').text('$' + update_order_total_amount);

        //jQuery('#cart_postal_code_result, #quotes_all1').find('.shipping_rates_bar').removeClass('selected-quote');
    });
    
    // BILLING INFO SECTION AUTO FILLUP CITY AND STATE TEXTBOX USING ZIP/POSTAL CODE

    function is_int(value) {
      if ((parseFloat(value) == parseInt(value)) && !isNaN(value)) {
        return true;
      } else {
        return false;
      }
    }

    jQuery("#billing_information_country").change(function(){
        jQuery("#billing_information_street1").val('');
        changeOrignalFieldValue(jQuery("#billing_information_street1")[0]);
        jQuery("#billing_information_street2").val('');
        changeOrignalFieldValue(jQuery("#billing_information_street2")[0]);
        jQuery("#billing_information_phone").val('');
        changeOrignalFieldValue(jQuery("#billing_information_phone")[0]);
        jQuery("#billing_information_zip_code").val('');
        changeOrignalFieldValue(jQuery("#billing_information_zip_code")[0]);
        jQuery("#billing_information_city").val('');
        changeOrignalFieldValue(jQuery("#billing_information_city")[0]);
        jQuery("#billing_information_state_auto_fill").val('');
        changeOrignalFieldValue(jQuery("#billing_information_state_auto_fill")[0]);
    });

    jQuery("#billing_information_zip_code").keyup(function() {

      // Cache
      var el = jQuery(this);    
      var selectedCountryValue = jQuery("#billing_information_country").val();

      // Did they type five integers?
      if( selectedCountryValue == 124 ){
            
            if ((el.val().length > 5)) {
                // Call Ziptastic for information
                jQuery.ajax({
                  url: "https://zip.getziptastic.com/v2/CA/" + el.val(),
                  cache: false,
                  dataType: "json",
                  type: "GET",
                  success: function(result, success) {

                    //jQuery(".zip-error, .instructions").slideUp(200);

                    jQuery("#billing_information_city").val(result.city);
                    changeOrignalFieldValue(jQuery("#billing_information_city")[0]);
                    jQuery("#billing_information_state_auto_fill").val(result.state_short);
                    changeOrignalFieldValue(jQuery("#billing_information_state_auto_fill")[0]);
                  },
                  error: function(result, success) {

                    jQuery(".zip-error").slideDown(300);

                  }

                });
            };

            
      }else if( selectedCountryValue == 840 ){

            if ((el.val().length == 5) && (is_int(el.val()))) {

                // Call Ziptastic for information
                jQuery.ajax({
                  url: "https://zip.getziptastic.com/v2/US/" + el.val(),
                  cache: false,
                  dataType: "json",
                  type: "GET",
                  success: function(result, success) {

                    //jQuery(".zip-error, .instructions").slideUp(200);

                    jQuery("#billing_information_city").val(result.city);
                    changeOrignalFieldValue(jQuery("#billing_information_city")[0]);
                    jQuery("#billing_information_state_auto_fill").val(result.state_short);
                    changeOrignalFieldValue(jQuery("#billing_information_state_auto_fill")[0]);
                    console.log("Update billing city field value");
                  },
                  error: function(result, success) {

                    jQuery(".zip-error").slideDown(300);

                  }

                });

            };

      }else{

        if ((el.val().length == 5) && (is_int(el.val()))) {

            // Call Ziptastic for information
            jQuery.ajax({
              url: "https://zip.getziptastic.com/v2/US/" + el.val(),
              cache: false,
              dataType: "json",
              type: "GET",
              success: function(result, success) {

                //jQuery(".zip-error, .instructions").slideUp(200);

                jQuery("#billing_information_city").val(result.city);
                changeOrignalFieldValue(jQuery("#billing_information_city")[0]);
                jQuery("#billing_information_state_auto_fill").val(result.state_short);
                changeOrignalFieldValue(jQuery("#billing_information_state_auto_fill")[0]);
                console.log("Update billing city field value");
              },
              error: function(result, success) {

                jQuery(".zip-error").slideDown(300);

              }

            });

        };

        if ((el.val().length > 5)) {
            // Call Ziptastic for information
            jQuery.ajax({
              url: "https://zip.getziptastic.com/v2/CA/" + el.val(),
              cache: false,
              dataType: "json",
              type: "GET",
              success: function(result, success) {

                //jQuery(".zip-error, .instructions").slideUp(200);

                jQuery("#billing_information_city").val(result.city);
                changeOrignalFieldValue(jQuery("#billing_information_city")[0]);
                jQuery("#billing_information_state_auto_fill").val(result.state_short);
                changeOrignalFieldValue(jQuery("#billing_information_state_auto_fill")[0]);
              },
              error: function(result, success) {

                jQuery(".zip-error").slideDown(300);

              }

            });
        };

      }   

    });
   
    // BILLING INFO SECTION AUTO FILLUP

    jQuery("html,body").on("click", ".btn_view_map", function (e) {
        jQuery('#load_factory_map').show();
        jQuery('#load_factory_map').modal("show");
        google.maps.event.trigger(map, "resize"); 
    });
});