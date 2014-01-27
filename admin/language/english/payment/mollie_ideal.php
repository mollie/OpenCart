<?php
/**
 * Copyright (c) 2012-2014, Mollie B.V.
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
 * @category    Mollie
 * @package     Mollie_Ideal
 * @version     v5.0.3
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */

// Heading
$_['heading_title']         = 'Mollie (iDEAL, Creditcard, Mister Cash & paysafecard)';
$_['footer_text']           = 'Payment services';

// Text 
$_['text_payment']          = "Payment";
$_['text_success']          = "Success: You have successfully modified your Mollie settings!";
$_['text_mollie_ideal']     = '<a href="https://www.mollie.nl" target="_blank"><img src="https://www.mollie.nl/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']          = "Status: <br/><span class='help'>Activate the module</span>";
$_['entry_api_key']         = "API key: <br/><span class='help'>Enter here the <code>api_key</code> of the website profile you want to use. The api_key starts with <code>test_</code> or <code>live_</code>. <br>[<a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>view available profiles</a>]</span>";
$_['entry_webhook']         = "Webhook:";
$_['entry_webhook_help']    = "Copy this webhook in your website profile inside the <a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>Mollie Beheer</a>. You can use the same webhook as both test and live webhook.";
$_['entry_description']     = "Description: <br/><span class='help'>This description will appear on the bank / card statement of your customer. You may use a maximum of 29 characters. TIP: Use '%', this will be replaced by the order id of the payment. Don't forget % can be multiple characters long!</span>";
$_['entry_sort_order']      = "Sort Order:";

// Info
$_['entry_module']          = "Module:";
$_['entry_status']          = "Module Status:";
$_['entry_version']         = "<a href='https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/' target='_blank'>iDEAL</a> version 4.8";
$_['entry_support']	    	= "Support:";

// Error
$_['error_permission']      = "Warning: You don't have permission to modify the Mollie payment methods.";
$_['error_api_key']         = "The API key is required!";
$_['error_description']     = "Description is required!";

// Status
$_['entry_failed_status']    = 'Failed Status:';
$_['entry_canceled_status']  = 'Canceled Status:';
$_['entry_expired_status']   = 'Expired Status:';
$_['entry_pending_status']   = 'Pending Status:';
$_['entry_processing_status']= 'Processing Status:';
$_['entry_processed_status'] = 'Processed Status:';
