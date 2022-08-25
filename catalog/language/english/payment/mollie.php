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

/**
 * English language file for iDEAL by Mollie
 */

// Text
$_['heading_title']             = 'Payment by Mollie';
$_['ideal_title']               = 'Your payment';
$_['text_title']                = 'Pay online';
$_['text_redirected']           = 'The client has been referred to the payment screen';
$_['text_issuer_ideal']         = 'Select your bank';
$_['text_issuer_giftcard']      = 'Select your giftcard';
$_['text_issuer_kbc']           = 'Select your payment button.';
$_['text_issuer_voucher']       = 'Select your brand.';
$_['text_card_details']         = 'Please enter your credit card details.';
$_['text_mollie_payments']      = 'Secure payments provided by %s';
$_['text_recurring_desc']       = 'Order %s, %s - %s, Every %s for %s';
$_['text_recurring']		    = '%s every %s %s';
$_['text_length']			    = ' for %s payments';
$_['text_trial']			    = '%s every %s %s for %s payments then ';
$_['text_error_report_success']	= 'Error has been reported successfully!';

// Button
$_['button_retry']          = 'Return to checkout page';
$_['button_report']         = 'Report Error';
$_['button_submit']         = 'Submit';

// Entry
$_['entry_card_holder']     	= 'Card Holder Name';
$_['entry_card_number']     	= 'Card Number';
$_['entry_expiry_date']     	= 'Expiry Date';
$_['entry_verification_code']	= 'CVV';

// Error
$_['error_card']				= 'Please check your card details.';
$_['error_missing_field']	    = 'Missing required information. Please check if basic address details are provided.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Your payment has not been completed';
$_['msg_failed']         = 'Unfortunately your payment has failed. Please click the button below to return to the checkout page and retry setting up a payment.';

// Status page: payment pending.
$_['heading_unknown']    = 'Your payment is pending';
$_['msg_unknown']        = 'Your payment has not been received yet. We will send you a confirmation email the moment the payment is received.';

// Status page: API failure.
$_['heading_error']      = 'An error occurred when setting up the payment';
$_['text_error']         = 'An error occurred when setting up the payment with Mollie. Click the button below to return to the checkout page.';

// Response
$_['response_success']   = 'The payment is received';
$_['response_none']      = 'The payment is not received yet';
$_['response_cancelled'] = 'The client has canceled the payment';
$_['response_failed']    = 'Unfortunately something went wrong. Please retry the payment.';
$_['response_expired']   = 'The payment has expired';
$_['response_unknown']   = 'An unknown error occurred';
$_['shipment_success']   = 'Shipment is created';
$_['refund_cancelled']   = 'Refund has been cancelled.';
$_['refund_success'] 	 = 'Refund has been processed successfully!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Creditcard';
$_['method_bancontact']     = 'Bancontact';
$_['method_banktransfer']   = 'Bank transfer';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'KBC/CBC Payment Button';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Giftcard';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnapaynow']   = 'Klarna Pay Now';
$_['method_klarnasliceit']  = 'Klarna Slice It';
$_['method_przelewy24']  	= 'P24';
$_['method_applepay']    	= 'Apple Pay';
$_['method_voucher']    	= 'Voucher';
$_['method_in3']    	    = 'IN3';

//Round Off Description
$_['roundoff_description']  = 'Rounding difference due to currency conversion';

//Warning
$_['warning_secure_connection']  = 'Please ensure you are using a secure connection.';
