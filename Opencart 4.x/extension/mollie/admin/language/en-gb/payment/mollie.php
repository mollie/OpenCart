<?php
/**
 * Copyright (c) 2012-2015, Mollie B.V.
 * All rights reserved. 
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met: 
 * 
 * - Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright 
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH 
 * DAMAGE. 
 *
 * @package     Mollie
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.com>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.com
 */

// These are called automatically by the Payment modules list - do not change the names
$method_list_logo                = '<a href="https://www.mollie.com" target="_blank"><img src="../image/mollie/mollie_logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']   = $method_list_logo;
$_['text_mollie_belfius']        = $method_list_logo;
$_['text_mollie_creditcard']     = $method_list_logo;
$_['text_mollie_ideal']          = $method_list_logo;
$_['text_mollie_kbc']            = $method_list_logo;
$_['text_mollie_bancontact']     = $method_list_logo;
$_['text_mollie_paypal']         = $method_list_logo;
$_['text_mollie_giftcard']       = $method_list_logo;
$_['text_mollie_eps']            = $method_list_logo;
$_['text_mollie_klarnapaylater'] = $method_list_logo;
$_['text_mollie_klarnapaynow']   = $method_list_logo;
$_['text_mollie_klarnasliceit']  = $method_list_logo;
$_['text_mollie_przelewy_24']  	 = $method_list_logo;
$_['text_mollie_applepay']  	 = $method_list_logo;
$_['text_mollie_voucher']    	 = $method_list_logo;
$_['text_mollie_in_3']     	     = $method_list_logo;
$_['text_mollie_mybank']      	 = $method_list_logo;
$_['text_mollie_billie']      	 = $method_list_logo;
$_['text_mollie_klarna']      	 = $method_list_logo;
$_['text_mollie_twint']      	 = $method_list_logo;
$_['text_mollie_blik']      	 = $method_list_logo;
$_['text_mollie_bancomatpay']    = $method_list_logo;
$_['text_mollie_trustly']        = $method_list_logo;
$_['text_mollie_alma']           = $method_list_logo;
$_['text_mollie_riverty']        = $method_list_logo;
$_['text_mollie_payconiq']       = $method_list_logo;
$_['text_mollie_satispay']       = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Settings";
$_['title_payment_status']    = "Payment statuses";
$_['title_mod_about']         = "About this module";
$_['footer_text']             = "Payment services";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Bank transfer";
$_['name_mollie_belfius']        = "Belfius Direct Net";
$_['name_mollie_creditcard']     = "Creditcard";
$_['name_mollie_ideal']          = "iDEAL";
$_['name_mollie_kbc']            = "KBC/CBC Payment Button";
$_['name_mollie_bancontact']     = "Bancontact";
$_['name_mollie_paypal']         = "PayPal";
$_['name_mollie_giftcard']       = 'Giftcard';
$_['name_mollie_eps']            = 'EPS';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnapaynow']   = 'Klarna Pay Now';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy_24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in_3']           = "iDEAL in3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";
$_['name_mollie_twint']          = "Twint";
$_['name_mollie_blik']           = "Blik";
$_['name_mollie_bancomatpay']    = "Bancomat Pay";
$_['name_mollie_trustly']        = "Trustly";
$_['name_mollie_alma']           = "Alma";
$_['name_mollie_riverty']        = "Riverty";
$_['name_mollie_payconiq']       = "Payconiq";
$_['name_mollie_satispay']       = "Satispay";

// Text
$_['text_edit']                    = "Edit";
$_['text_extension']               = 'Extensions';
$_['text_success']                 = "Success: You have successfully modified your Mollie settings!";
$_['text_missing_api_key']         = "Please fill out your API key in the <a data-toggle='tab' href='javascript:void(0);' class='settings'>Settings</a> tab.";
$_['text_activate_payment_method'] = 'Enable this payment method in your <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie dashboard</a>.';
$_['text_no_status_id']            = "- Do not update the order status (not recommended) -";
$_['text_enable']                  = "Enable";
$_['text_disable']                 = "Disable";
$_['text_connection_success']      = "Success: Connection to Mollie successful!";
$_['text_error'] 			       = "Warning: Something went wrong. Please try again later!";
$_['text_creditcard_required']     = "Requires Credit Card";
$_['text_mollie_api']              = "Mollie API";
$_['text_mollie_app']              = "Mollie App";
$_['text_general'] 	               = "General";
$_['text_enquiry'] 	               = "How can we help you?";
$_['text_enquiry_success'] 	       = "Success: Your enquiry has been submitted. We'll get back to you soon. Thank you!";
$_['text_update_message']          = 'Mollie: There is an updated version (%s) available of the Mollie module. Click <a href="%s">here</a> to update. Do not want to see this message again? Click <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">here</a>.';
$_['text_update_message_warning']  = 'Mollie: There is an updated version (%s) available of the Mollie module. Please update your PHP version to %s or later to update the module or continue using the current version. Do not want to see this message again? Click <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">here</a>.';
$_['text_update_success']          = "Success: Mollie module has been updated to version %s.";
$_['text_default_currency']        = "Currency used in the store";
$_['text_custom_css']              = "Custom CSS For Mollie Components";
$_['text_contact_us']              = "Contact Us - Technical Support";
$_['text_bg_color']                = "Background color";
$_['text_color']                   = "Color";
$_['text_font_size']               = "Font size";
$_['text_other_css']               = "Other CSS";
$_['text_module_by']               = "Module by Quality Works - Technical Support";
$_['text_mollie_support']          = "Mollie - Support";
$_['text_contact']                 = "Contact";
$_['text_allowed_variables']       = "Allowed variables: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Browse';
$_['text_clear']                   = 'Clear';
$_['text_image_manager']           = 'Image Manager';
$_['text_left']                    = 'Left';
$_['text_right']                   = 'Right';
$_['text_more']                    = 'More';
$_['text_no_maximum_limit']        = 'No maximum amount limit';
$_['text_standard_total']          = 'Standard Total: %s';
$_['text_advance_option']          = 'Advanced Options For %s';
$_['text_payment_api']             = 'Payments API';
$_['text_order_api']               = 'Orders API';
$_['text_info_orders_api']         = 'Why Use Orders API?';
$_['text_pay_link_variables']      = "Allowed variables: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_subscription_payment']    = "Subscription Payment";
$_['text_payment_link']            = "Payment Link";
$_['text_coming_soon']             = "Coming Soon";

// Entry
$_['entry_payment_method']           = "Payment method";
$_['entry_activate']                 = "Activate";
$_['entry_sort_order']               = "Sort order";
$_['entry_api_key']                  = "API key";
$_['entry_description']              = "Description";
$_['entry_show_icons']               = "Show icons";
$_['entry_show_order_canceled_page'] = "Show message if payment is cancelled";
$_['entry_geo_zone']                 = "Geo Zone";
$_['entry_client_id']                = "Client ID";
$_['entry_client_secret']            = "Client Secret";
$_['entry_redirect_uri']             = "Redirect URI";
$_['entry_payment_screen_language']  = "Payment screen default language";
$_['entry_mollie_connect'] 			 = "Mollie connect";
$_['entry_name'] 			 		 = "Name";
$_['entry_email'] 			 		 = "E-mail";
$_['entry_subject'] 			     = "Subject";
$_['entry_enquiry'] 			 	 = "Enquiry";
$_['entry_debug_mode'] 			 	 = "Debug mode";
$_['entry_mollie_component'] 		 = "Mollie components";
$_['entry_test_mode'] 		 		 = "Test mode";
$_['entry_mollie_component_base'] 	 = "Custom CSS for Base input field";
$_['entry_mollie_component_valid'] 	 = "Custom CSS for Valid input field";
$_['entry_mollie_component_invalid'] = "Custom CSS for Invalid input field";
$_['entry_default_currency'] 		 = "Always pay with";
$_['entry_email_subject'] 		 	 = "Subject";
$_['entry_email_body'] 			 	 = "Body";
$_['entry_title']	 			 	 = "Title";
$_['entry_image']	 			 	 = "Image";
$_['entry_status']	 			 	 = "Status";
$_['entry_align_icons']              = "Align Icons";
$_['entry_single_click_payment']     = "Single Click Payment";
$_['entry_order_expiry_days']        = "Order Expiry Days";
$_['entry_partial_refund']           = "Partial Refund";
$_['entry_amount']                   = "Amount (example: 5 or 5%)";
$_['entry_payment_fee']              = "Payment Fee";
$_['entry_payment_fee_tax_class']    = "Payment Fee Tax Class";
$_['entry_total']				     = "Total";
$_['entry_minimum']				     = "Minimum";
$_['entry_maximum']				     = "Maximum";
$_['entry_api_to_use']  		     = "API to Use";
$_['entry_payment_link']  		     = "Send Payment Link";
$_['entry_payment_link_sep_email']   = "Send in a separate email";
$_['entry_payment_link_ord_email']   = "Send in order confirmation email";
$_['entry_partial_credit_order']     = 'Create credit order on (partial) refund';

// Help
$_['help_view_profile']             = 'You can find your API key in <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link">your Mollie website profiles</a>.';
$_['help_status']                   = "Activate the module";
$_['help_api_key']                  = 'Enter the <code>api_key</code> of the website profile you want to use. The API key starts with <code>test_</code> or <code>live_</code>.';
$_['help_description']              = 'This description will appear on the bank / card statement of your customer. You may use a maximum of 29 characters. TIP: Use <code>%</code>, this will be replaced by the order id of the payment. Don\'t forget <code>%</code> can be multiple characters long!';
$_['help_show_icons']               = 'Show icons next to the Mollie payment methods on the checkout page.';
$_['help_show_order_canceled_page'] = 'Show a message to the customer if a payment is cancelled, before redirecting the customer back to their shopping cart.';
$_['help_redirect_uri']				= 'Redirect URI in your mollie dashboard must match with this URI.';
$_['help_mollie_app']				= 'By registering your module as an App on the Mollie dashboard, you will unlock added functionalities. This is not required to use Mollie payments.';
$_['help_apple_pay']				= 'Apple Pay requires credit card to be enabled on your website profile. Please enable credit card method first.';
$_['help_mollie_component']			= 'Mollie components allow you to show fields needed for credit card holder data to your own checkout.';
$_['help_single_click_payment']		= 'Enabling your customers to charge a previously used credit card with a single click.';
$_['help_total']					= 'The checkout minimum and maximum amount before this payment method becomes active.';
$_['help_payment_link']				= 'While creating orders from admin, a <strong>Mollie Payment Link</strong> method will be available to send payment link to the customer for payment. You can set email text under email tab.';

// Info
$_['entry_module']            = "Module";
$_['entry_mod_status']        = "Module status";
$_['entry_comm_status']       = "Communication status";
$_['entry_support']           = "Support";

$_['entry_version']           = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']         = "Warning: You don't have permission to modify the Mollie payment methods.";
$_['error_api_key']            = "Mollie API key is required!";
$_['error_api_key_invalid']    = "Invalid API key!";
$_['error_description']        = "Description is required!";
$_['error_file_missing']       = "File does not exist";
$_['error_name']               = 'Warning: Name must be between 3 and 25 characters!';
$_['error_email']              = 'Warning: E-Mail Address does not appear to be valid!';
$_['error_subject']            = 'Warning: Subject must be 3 characters long!';
$_['error_enquiry']            = 'Warning: Enquiry text must be 25 characters long!';
$_['error_no_api_client']      = 'API client not found.';
$_['error_api_help']           = 'You can ask your hosting provider to help with this.';
$_['error_comm_failed']        = '<strong>Communicating with Mollie failed:</strong><br/>%s<br/><br/>Please check the following conditions. You can ask your hosting provider to help with this.<ul><li>Make sure outside connections to %s are not blocked.</li><li>Make sure SSL v3 is disabled on your server. Mollie does not support SSL v3.</li><li>Make sure your server is up-to-date and the latest security patches have been installed.</li></ul><br/>Contact <a href="mailto:info@mollie.nl">info@mollie.nl</a> if this still does not fix your problem.';
$_['error_no_api_key']         = 'No API key provided. Please insert your API key.';
$_['error_order_expiry_days']  = 'Warning: It is not posible to use Klarna Slice it or Klarna Pay later as method when expiry date is more than 28 days in the future.';
$_['error_mollie_payment_fee'] = 'Warning: Mollie Payment Fee order total is disabled!';
$_['error_file']               = 'Warning: %s file could not be found!';
$_['error_address']            = 'Billing address is turned off, digital orders will not be able to be paid. You can turn on the billing address in <a href="%s">settings</a>.';
$_['error_telephone']          = 'Telephone field is required by some payment methods. Please enable it from the <a href="%s">settings</a> and make it required.';

// Status
$_['entry_pending_status']           = "Payment created status";
$_['entry_failed_status']            = "Payment failed status";
$_['entry_canceled_status']          = "Payment canceled status";
$_['entry_expired_status']           = "Payment expired status";
$_['entry_processing_status']        = "Payment successful status";
$_['entry_refund_status']	         = "Payment refund status";
$_['entry_partial_refund_status']	 = "Partial Refund Status";
$_['entry_shipping_status']   		 = "Order shipped status";
$_['entry_shipment']       			 = "Create shipment";
$_['entry_create_shipment_status']   = "Create shipment after order status";
$_['help_shipment'] 				 = "Shipment will be created right after creating order. Select 'No' to create shipment when order reach to a specific status and select the order status from below.";

$_['text_create_shipment_automatically']            = "Create shipment automatically upon order creation";
$_['text_create_shipment_on_status']                = "Create shipment upon setting order to this status";
$_['text_create_shipment_on_order_complete']        = "Create shipment upon setting order to order complete status";
$_['entry_create_shipment_on_order_complete'] 		= "Create shipment upon order complete";

//Button
$_['button_update']         = "Update";
$_['button_mollie_connect'] = "Connect via Mollie";
$_['button_advance_option'] = "Advance Option";
$_['button_save_close']     = "Save & Close";

//Error log
$_['text_log_success']	   = 'Success: You have successfully cleared your mollie log!';
$_['text_log_list']        = 'Log';
$_['error_log_warning']	   = 'Warning: Your mollie log file %s is %s!';
$_['button_download']	   = 'Download';

//admin/language/en-gb/sale/order.php
$_['button_refund'] = 'Refund';
$_['text_order_not_found'] = 'Mollie order details not found!';
$_['text_no_refund'] = 'Refund cannot be processed!';
$_['text_refunded_already'] = 'Refund has been processed already!';
$_['text_refund_success']     = 'Refund has been processed successfully!';
$_['text_confirm_refund']     = 'You are about to refund this payment, this cannot be undone. Are you sure you would like to continue?';
$_['entry_amount']     = 'Amount';
$_['button_partial_refund']     = 'Partial Refund';
$_['error_refund_amount']       = 'Warning: Please enter a correct amount to refund!';
$_['text_partial_refund_success']     = 'Partial refund of amount %s has been processed successfully!';
$_['entry_partial_refund_type']     = 'Refund Type';
$_['text_custom_amount']     = 'Custom Amount Refund';
$_['text_productline']       = 'Productline Refund';
$_['entry_productline']      = 'Productlines';
$_['entry_quantity']         = 'Quantity';
$_['help_quantity']          = 'Quantity to refund. Should not exceed the ordered quantity.';
$_['error_productline']      = 'Warning: Please select a productline to refund!';
$_['tab_mollie']             = 'Mollie';
$_['text_mollie_payment']    = 'Payment(s)';
$_['text_mollie_refund']     = 'Refund(s)';
$_['column_date_added']      = 'Date Added';
$_['column_payment_method']  = 'Payment Method';
$_['column_amount']          = 'Amount';
$_['column_status']          = 'Status';
$_['button_payment_link']    = 'Send Payment Link';
$_['column_stock_mutation']   = 'Stock Mutation';
$_['help_stock_mutation']    = 'Select if you want to re-stock the credited products';
$_['entry_mollie_payment_link'] = 'Send Mollie Payment Link';

//admin/language/en-gb/catalog/product.php
$_['entry_voucher_category']            = 'Voucher Category';
$_['help_voucher_category']             = 'Select from categories to use mollie voucher payments.';
