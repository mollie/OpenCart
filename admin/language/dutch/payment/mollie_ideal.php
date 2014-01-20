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
 * @version     v5.0.2
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */

// Heading
$_['heading_title']         = 'Mollie (iDEAL, Creditcard, Mister Cash & paysafecard)';
$_['footer_text']           = 'Payment services';

// Text 
$_['text_payment']          = "Betaling";
$_['text_success']          = "Gelukt: de instellingen voor de module zijn aangepast!";
$_['text_mollie_ideal']     = '<a href="https://www.mollie.nl" target="_blank"><img src="https://www.mollie.nl/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']          = "Status: <br/><span class='help'>Activate the module</span>";
$_['entry_api_key']         = "API key: <br/><span class='help'>Voer hier de <code>api_key</code> van het websiteprofiel in dat u wilt gebruiken. De api_key begint met <code>test_</code> or <code>live_</code>. <br>[<a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>bekijk uw websiteprofielen</a>]</span>";
$_['entry_webhook']         = "Webhook:";
$_['entry_webhook_help']    = "Kopieer deze webhook in uw profiel binnen het <a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>Mollie Beheer</a>. U kunt dezelfde webhook als <em>Live webhook</em> en <em>Test webhook</em> gebruiken.";
$_['entry_description']     = "Omschrijving: <br/><span class='help'>De omschrijving zal op het bankafschrift van uw klant verschijnen en kunt u terugvinden in het Mollie beheer. U kunt maximaal 29 tekens gebruikt. TIP: Gebruik <code>%'</code>, dit zal vervangen worden door het ordernummer. Het ordernummer kan zelf ook meerdere tekens lang zijn!</span>";
$_['entry_sort_order']      = "Sorteervolgorde:";

// Info
$_['entry_module']          = "Module:";
$_['entry_status']          = "Module status:";
$_['entry_version']         = "<a href='https://www.mollie.nl/betaaldiensten/ideal/' target='_blank'>Mollie API</a> version 5.0";
$_['entry_support']	    	= "Support:";

// Error
$_['error_permission']      = "Waarschuwing: U heeft geen toestemming om de module aan te passen.";
$_['error_api_key']         = "De API key is verplicht!";
$_['error_description']     = "De omschrijving is verplicht!";

// Status
$_['entry_failed_status']    = 'Mislukt status:';
$_['entry_canceled_status']  = 'Geannuleerd status:';
$_['entry_expired_status']   = 'Verlopen status:';
$_['entry_pending_status']   = 'In afwachting status:';
$_['entry_processing_status']= 'Bezig met verwerken tatus:';
$_['entry_processed_status'] = 'Verwerkt status:';
