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
$_['heading_title']         = "Mollie";
$_['title_global_options']  = "Instellingen";
$_['title_payment_status']  = "Betaalstatussen";
$_['title_mod_about']       = "Over deze module";
$_['footer_text']           = "Betaaldiensten";

// Module names
$_['name_mollie_banktransfer']  = "Overboeking";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_bitcoin']       = "Bitcoin";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Eenmalige incasso";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "KBC/CBC-Betaalknop";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";
$_['name_mollie_paypal']        = "PayPal";
$_['name_mollie_paysafecard']   = "paysafecard";
$_['name_mollie_sofort']        = "SOFORT Banking";
$_['name_mollie_giftcard']      = 'Giftcard';
$_['name_mollie_inghomepay']    = 'ING Home\'Pay';

// Text
$_['text_edit']                    = "Bewerk Mollie";
$_['text_payment']                 = "Betaling";
$_['text_success']                 = "Gelukt: de instellingen voor de module zijn aangepast!";
$_['text_missing_api_key']         = "Vul hieronder de API-key in.";
$_['text_activate_payment_method'] = 'Activeer deze betaalmethode via het <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank">Mollie-dashboard</a>.';
$_['text_no_status_id']            = "- Status niet wijzigen (niet aanbevolen) -";

// Entry
$_['entry_payment_method']           = "Betaalmethode";
$_['entry_activate']                 = "Activeren";
$_['entry_sort_order']               = "Sorteervolgorde";
$_['entry_api_key']                  = "API-sleutel";
$_['entry_description']              = "Omschrijving";
$_['entry_show_icons']               = "Toon icoontjes";
$_['entry_show_order_canceled_page'] = "Toon melding bij geannuleerde betalingen";
$_['entry_geo_zone']                 = "Geo Zone";

// Help
$_['help_view_profile']             = 'U kunt uw API-sleutel vinden bij <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank" class="alert-link">uw Mollie-websiteprofielen</a>.';
$_['help_status']                   = "Activeer de module";
$_['help_api_key']                  = "Voer hier de <code>api_key</code> van het websiteprofiel in dat u wilt gebruiken. De API-sleutel begint met <code>test_</code> of <code>live_</code>.";
$_['help_description']              = "De omschrijving zal op het bankafschrift van uw klant verschijnen en kunt u terugvinden in het Mollie beheer. U kunt maximaal 29 tekens gebruiken. TIP: Gebruik <code>%</code>, dit zal vervangen worden door het ordernummer. Het ordernummer kan zelf ook meerdere tekens lang zijn!";
$_['help_show_icons']               = "Toon icoontjes naast de betaalmethodes van Mollie op de betaalpagina.";
$_['help_show_order_canceled_page'] = "Toon een melding aan de klant als een betaling geannuleerd wordt, alvorens de klant terug naar het winkelmandje te verwijzen.";

// Info
$_['entry_module']          = "Module";
$_['entry_mod_status']      = "Modulestatus";
$_['entry_comm_status']     = "Communicatiestatus";
$_['entry_support']         = "Support";

$_['entry_version']         = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']      = "Waarschuwing: U heeft geen toestemming om de module aan te passen.";
$_['error_api_key']         = "Mollie API-sleutel is verplicht!";
$_['error_api_key_invalid'] = "Ongeldige Mollie API-sleutel!";
$_['error_description']     = "De omschrijving is verplicht!";
$_['error_file_missing']    = "Bestand bestaat niet";

// Status
$_['entry_pending_status']   = "Status betaling aangemaakt";
$_['entry_failed_status']    = "Status betaling mislukt";
$_['entry_canceled_status']  = "Status betaling geannuleerd";
$_['entry_expired_status']   = "Status betaling verlopen";
$_['entry_processing_status']= "Status betaling succesvol";
