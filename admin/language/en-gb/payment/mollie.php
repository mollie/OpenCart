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
$method_list_logo              = '<a href="https://www.mollie.com" target="_blank"><img src="https://www.mollie.com/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']  = $method_list_logo;
$_['text_mollie_belfius']       = $method_list_logo;
$_['text_mollie_bitcoin']       = $method_list_logo;
$_['text_mollie_creditcard']    = $method_list_logo;
$_['text_mollie_directdebit']   = $method_list_logo;
$_['text_mollie_ideal']         = $method_list_logo;
$_['text_mollie_kbc']           = $method_list_logo;
$_['text_mollie_mistercash']    = $method_list_logo;
$_['text_mollie_paypal']        = $method_list_logo;
$_['text_mollie_paysafecard']   = $method_list_logo;
$_['text_mollie_sofort']        = $method_list_logo;
$_['text_mollie_giftcard']      = $method_list_logo;
$_['text_mollie_inghomepay']    = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Settings";
$_['title_payment_status']    = "Payment statuses";
$_['title_mod_about']         = "About this module";
$_['footer_text']             = "Payment services";

// Module names
$_['name_mollie_banktransfer']  = "Bank transfer";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_bitcoin']       = "Bitcoin";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Direct debit";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "KBC/CBC Payment Button";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";
$_['name_mollie_paypal']        = "PayPal";
$_['name_mollie_paysafecard']   = "paysafecard";
$_['name_mollie_sofort']        = "SOFORT Banking";
$_['name_mollie_giftcard']      = 'Giftcard';
$_['name_mollie_inghomepay']    = 'ING Home\'Pay';

// Text
$_['text_edit']                    = "Edit";
$_['text_payment']                 = "Payment";
$_['text_success']                 = "Success: You have successfully modified your Mollie settings!";
$_['text_missing_api_key']         = "Please fill out your API key below.";
$_['text_activate_payment_method'] = 'Enable this payment method in your <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank">Mollie dashboard</a>.';
$_['text_no_status_id']            = "- Do not update the order status (not recommended) -";

// Entry
$_['entry_payment_method']           = "Payment method";
$_['entry_activate']                 = "Activate";
$_['entry_sort_order']               = "Sort order";
$_['entry_api_key']                  = "API key";
$_['entry_description']              = "Description";
$_['entry_show_icons']               = "Show icons";
$_['entry_show_order_canceled_page'] = "Show message if payment is cancelled";
$_['entry_geo_zone']                 = "Geo Zone";

// Help
$_['help_view_profile']             = 'You can find your API key in <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank" class="alert-link">your Mollie website profiles</a>.';
$_['help_status']                   = "Activate the module";
$_['help_api_key']                  = 'Enter the <code>api_key</code> of the website profile you want to use. The API key starts with <code>test_</code> or <code>live_</code>.';
$_['help_description']              = 'This description will appear on the bank / card statement of your customer. You may use a maximum of 29 characters. TIP: Use <code>%</code>, this will be replaced by the order id of the payment. Don\'t forget <code>%</code> can be multiple characters long!';
$_['help_show_icons']               = 'Show icons next to the Mollie payment methods on the checkout page.';
$_['help_show_order_canceled_page'] = 'Show a message to the customer if a payment is cancelled, before redirecting the customer back to their shopping cart.';

// Info
$_['entry_module']            = "Module";
$_['entry_mod_status']        = "Module status";
$_['entry_comm_status']       = "Communication status";
$_['entry_support']           = "Support";

$_['entry_version']           = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']        = "Warning: You don't have permission to modify the Mollie payment methods.";
$_['error_api_key']           = "Mollie API key is required!";
$_['error_api_key_invalid']   = "Invalid API key!";
$_['error_description']       = "Description is required!";
$_['error_file_missing']      = "File does not exist";

// Status
$_['entry_pending_status']    = "Payment created status";
$_['entry_failed_status']     = "Payment failed status";
$_['entry_canceled_status']   = "Payment canceled status";
$_['entry_expired_status']    = "Payment expired status";
$_['entry_processing_status'] = "Payment successful status";
