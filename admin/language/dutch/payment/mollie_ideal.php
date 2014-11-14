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
 * @package     Mollie
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */

// Heading
$_['heading_title']         = "Mollie (iDEAL, Mister Cash, Creditcard, PayPal & paysafecard)";
$_['title_payment_status']  = "Betaalstatussen";
$_['title_mod_about']       = "Over Deze Module";
$_['footer_text']           = "Betaaldiensten";

// Text 
$_['text_edit']             = "Bewerk Mollie";
$_['text_payment']          = "Betaling";
$_['text_success']          = "Gelukt: de instellingen voor de module zijn aangepast!";
$_['text_mollie_ideal']     = '<a href="https://www.mollie.nl" target="_blank"><img src="https://www.mollie.nl/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']          = "Status";
$_['entry_api_key']         = "API-sleutel";
$_['entry_description']     = "Omschrijving";
$_['entry_sort_order']      = "Sorteervolgorde";

// Help
$_['help_view_profile']    = 'U kunt uw API-sleutel vinden bij <a href="https://www.mollie.nl/beheer/account/profielen/" target="_blank" class="alert-link">uw Mollie-websiteprofielen</a>.';
$_['help_status']          = "Activeer de module";
$_['help_api_key']         = "Voer hier de <code>api_key</code> van het websiteprofiel in dat u wilt gebruiken. De API-sleutel begint met <code>test_</code> of <code>live_</code>.";
$_['help_description']     = "De omschrijving zal op het bankafschrift van uw klant verschijnen en kunt u terugvinden in het Mollie beheer. U kunt maximaal 29 tekens gebruiken. TIP: Gebruik <code>%</code>, dit zal vervangen worden door het ordernummer. Het ordernummer kan zelf ook meerdere tekens lang zijn!";

// Info
$_['entry_module']          = "Module";
$_['entry_mod_status']      = "Modulestatus";
$_['entry_comm_status']     = "Communicatiestatus";
$_['entry_support']         = "Support";

$_['entry_version']         = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']      = "Waarschuwing: U heeft geen toestemming om de module aan te passen.";
$_['error_api_key']         = "Mollie API-sleutel is verplicht!";
$_['error_description']     = "De omschrijving is verplicht!";
$_['error_file_missing']    = "Bestand bestaat niet";

// Status
$_['entry_pending_status']   = "Status betaling aangemaakt";
$_['entry_failed_status']    = "Status betaling mislukt";
$_['entry_canceled_status']  = "Status betaling geannuleerd";
$_['entry_expired_status']   = "Status betaling verlopen";
$_['entry_processing_status']= "Status betaling succesvol";