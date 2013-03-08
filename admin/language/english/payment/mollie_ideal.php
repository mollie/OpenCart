<?php

/**
 * Copyright (c) 2012, Mollie B.V.
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
 * @author      Mollie B.V. (info@mollie.nl)
 * @version     v4.8
 * @copyright   Copyright (c) 2012 Mollie B.V. (http://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 * 
 **/

// Heading
$_['heading_title']         = 'iDEAL by Mollie';

// Text 
$_['text_payment']          = "Payment";
$_['text_success']          = "Success: You have successfully modified your iDEAL settings!";
$_['text_mollie_ideal']     = '<a onclick="window.open(\'https://www.mollie.nl\');"><img src="http://www.mollie.nl/images/badge-ideal-small.png" alt="iDEAL via Mollie" title="iDEAL via Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']          = "Status: <br/><span class='help'>Activate the module</span>";
$_['entry_testmode']        = "Testmode: <br/><span class='help'>Set 'true' for testing purposes</span>";
$_['entry_partnerid']       = "Mollie partner ID: <br/><span class='help'>Mollie partner ID. For example 123456. This partner ID will be used to register the payments and can be found [<a target='new' href='https://www.mollie.nl/beheer/account/'>here</a>]</span>";
$_['entry_profilekey']      = "Profilekey: <br/><span class='help'>Enter here the profilekey of the payment profile you want to use. [<a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>view available profiles</a>]</span>";
$_['entry_description']     = "Description: <br/><span class='help'>This description will appear on the bank statement of your customer. You may use a maximum of 29 characters. TIP: Use '%', this will be replaced by the order id of the payment. Don't forget % can be multiple characters long!</span>";
$_['entry_total']           = "Minimal order amount: <br/><span class='help'>Minimal amount before we show iDEAL as payment method in your webshop (TYPE IN AS CENTS!)</span>";
$_['entry_sort_order']      = "Sort Order:";

// Info
$_['entry_module']          = "Module:";
$_['entry_status']          = "Module Status:";
$_['entry_version']         = "<a href='https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/' target='_blank'>iDEAL</a> version 4.8";
$_['entry_support']	    	= "Support:";

// Error
$_['error_permission']      = "Warning: You don't have permission to modify payment method iDEAL!";
$_['error_partnerid']       = "Mollie partner ID is required!";
$_['error_profilekey']      = "Profilekey is required!";
$_['error_description']     = "Description is required!";
$_['error_total']           = "Please enter a minimal amount. TIP: Use 118, because that is the minimum of iDEAL";

// Status
$_['entry_failed_status']    = 'Failed Status:';
$_['entry_canceled_status']  = 'Canceled Status:';
$_['entry_expired_status']   = 'Expired Status:';
$_['entry_pending_status']   = 'Pending Status:';
$_['entry_processing_status']= 'Processing Status:';
$_['entry_processed_status'] = 'Processed Status:';
