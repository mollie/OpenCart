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
$_['text_mollie_paysafecard']    = $method_list_logo;
$_['text_mollie_sofort']         = $method_list_logo;
$_['text_mollie_giftcard']       = $method_list_logo;
$_['text_mollie_eps']            = $method_list_logo;
$_['text_mollie_giropay']        = $method_list_logo;
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

// Heading
$_['heading_title']         = "Mollie";
$_['title_global_options']  = "Instellingen";
$_['title_payment_status']  = "Betaalstatussen";
$_['title_mod_about']       = "Over deze module";
$_['footer_text']           = "Betaaldiensten";
$_['title_mail']            = "E-mail";

// Module names
$_['name_mollie_banktransfer']   = "Overboeking";
$_['name_mollie_belfius']        = "Belfius Direct Net";
$_['name_mollie_creditcard']     = "Creditcard";
$_['name_mollie_ideal']          = "iDEAL";
$_['name_mollie_kbc']            = "KBC/CBC-Betaalknop";
$_['name_mollie_bancontact']     = "Bancontact";
$_['name_mollie_paypal']         = "PayPal";
$_['name_mollie_paysafecard']    = "paysafecard";
$_['name_mollie_sofort']         = "SOFORT Banking";
$_['name_mollie_giftcard']       = 'Giftcard';
$_['name_mollie_eps']            = 'EPS';
$_['name_mollie_giropay']        = 'Giropay';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnapaynow']   = 'Klarna Pay Now';
$_['name_mollie_klarnasliceit']  = 'Klarna Betaal in 3 delen';
$_['name_mollie_przelewy_24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in_3']           = "IN3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";
$_['name_mollie_twint']          = "Twint";
$_['name_mollie_blik']           = "Blik";
$_['name_mollie_bancomatpay']    = "Bancomat Pay";

// Text
$_['text_edit']                    = "Bewerk Mollie";
$_['text_extension']               = 'Extensions';
$_['text_success']                 = "Gelukt: de instellingen voor de module zijn aangepast!";
$_['text_missing_api_key']         = "Vul uw API-key in bij de <a data-toggle='tab' href='javascript:void(0);' class='settings'>Instellingen</a>.";
$_['text_activate_payment_method'] = 'Activeer deze betaalmethode via het <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie-dashboard</a>.';
$_['text_no_status_id']            = "- Status niet wijzigen (niet aanbevolen) -";
$_['text_enable']                  = "Activeren";
$_['text_disable']                 = "Deactiveren";
$_['text_connection_success']      = "Succes: verbinding met Mollie gelukt!";
$_['text_error'] 			       = "Waarschuwing: er is iets misgegaan. Probeer het later opnieuw!";
$_['text_creditcard_required']     = "Credit Card verplicht";
$_['text_mollie_api']              = "Mollie API";
$_['text_mollie_app']              = "Mollie App";
$_['text_general'] 	               = "Algemeen";
$_['text_enquiry'] 	               = "Hoe kunnen we u helpen?";
$_['text_enquiry_success'] 	       = "Succes: Uw aanvraag is ingediend. We nemen zo snel mogelijk contact met u op.";
$_['text_update_message']          = 'Mollie: Er is een bijgewerkte versie (%s) beschikbaar van de Mollie-module. Klik <a href="%s">hier</a> om bij te werken. Wil je dit bericht niet meer zien? Klik <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">hier</a>.';
$_['text_update_message_warning']  = 'Mollie: Er is een bijgewerkte versie (%s) beschikbaar van de Mollie-module. Werk uw PHP-versie bij naar %s of hoger om de module bij te werken of blijf de huidige versie gebruiken. Wil je dit bericht niet meer zien? Klik <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">hier</a>.';
$_['text_update_success']          = "Succes: Mollie module is geüpdatet naar versie %s.";
$_['text_default_currency']        = "Gebruikte valuta in de winkel";
$_['text_custom_css']              = "Custom CSS For Mollie Components";
$_['text_contact_us']              = "Neem contact met ons op - Technische ondersteuning";
$_['text_bg_color']                = "Background color";
$_['text_color']                   = "Color";
$_['text_font_size']               = "Font size";
$_['text_other_css']               = "Other CSS";
$_['text_module_by']               = "Module by Quality Works - Technical Support";
$_['text_mollie_support']          = "Mollie - Support";
$_['text_contact']                 = "Contact";
$_['text_allowed_variables']       = "Toegestane variabelen: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Browse';
$_['text_clear']                   = 'Clear';
$_['text_image_manager']           = 'Image Manager';
$_['text_left']                    = 'Links';
$_['text_right']                   = 'Rechts';
$_['text_more']                    = 'Meer';
$_['text_no_maximum_limit']        = 'Geen maximum bedrag limiet';
$_['text_standard_total']          = 'Standaard totaal: %s';
$_['text_advance_option']          = 'Geavanceerde opties voor %s';
$_['text_payment_api']             = 'Betalingen API';
$_['text_order_api']               = 'Bestellingen API';
$_['text_info_orders_api']         = 'Waarom Orders API gebruiken?';
$_['text_pay_link_variables']      = "Toegestane variabelen: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Terugkomende betaling";
$_['text_payment_link']            = "Betalingslink";
$_['text_coming_soon']             = "Binnenkort beschikbaar";

// Entry
$_['entry_payment_method']           = "Betaalmethode";
$_['entry_activate']                 = "Activeren";
$_['entry_sort_order']               = "Sorteervolgorde";
$_['entry_api_key']                  = "API-sleutel";
$_['entry_description']              = "Omschrijving";
$_['entry_show_icons']               = "Toon icoontjes";
$_['entry_show_order_canceled_page'] = "Toon melding bij geannuleerde betalingen";
$_['entry_geo_zone']                 = "Geo Zone";
$_['entry_client_id']                = "Client ID";
$_['entry_client_secret']            = "Client Secret";
$_['entry_redirect_uri']             = "Redirect URI";
$_['entry_payment_screen_language']  = "Standaardtaal betaalscherm";
$_['entry_mollie_connect'] 			 = "Mollie connect";
$_['entry_name'] 			 		 = "Naam";
$_['entry_email'] 			 		 = "E-mail";
$_['entry_subject'] 			     = "Onderwerpen";
$_['entry_enquiry'] 			 	 = "Onderzoek";
$_['entry_debug_mode'] 			 	 = "Debug mode";
$_['entry_mollie_component'] 		 = "Mollie components";
$_['entry_mollie_component_base'] 	 = "Custom CSS for Base input field";
$_['entry_mollie_component_valid'] 	 = "Custom CSS for Valid input field";
$_['entry_mollie_component_invalid'] = "Custom CSS for Invalid input field";
$_['entry_default_currency'] 		 = "Altijd betalen met";
$_['entry_email_subject'] 		 	 = "Onderwerp";
$_['entry_email_body'] 			 	 = "Body";
$_['entry_title']	 			 	 = "Titel";
$_['entry_image']	 			 	 = "Afbeelding";
$_['entry_status']	 			 	 = "Status";
$_['entry_align_icons']              = "Uitlijnen icoontjes";
$_['entry_single_click_payment']     = "Betaling met één klik";
$_['entry_order_expiry_days']        = "Vervaldagen van bestelling";
$_['entry_partial_refund']           = "Gedeeltelijke terugbetaling";
$_['entry_amount']                   = "Amount (voorbeeld: 5, 5%)";
$_['entry_payment_fee']              = "Payment Fee";
$_['entry_payment_fee_tax_class']    = "Payment Fee Tax Class";
$_['entry_total']				     = "Totaal";
$_['entry_minimum']				     = "Minimum";
$_['entry_maximum']				     = "Maximaal";
$_['entry_api_to_use']  		     = "API om te gebruiken";
$_['entry_payment_link']  		     = "Betaallink verzenden";
$_['entry_payment_link_sep_email']   = "Stuur een aparte e-mail";
$_['entry_payment_link_ord_email']   = "E-mail met orderbevestiging verzenden";
$_['entry_partial_credit_order']     = 'Kredietbestelling aanmaken bij (gedeeltelijke) restitutie';

// Help
$_['help_view_profile']             = 'U kunt uw API-sleutel vinden bij <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link">uw Mollie-websiteprofielen</a>.';
$_['help_status']                   = "Activeer de module";
$_['help_api_key']                  = "Voer hier de <code>api_key</code> van het websiteprofiel in dat u wilt gebruiken. De API-sleutel begint met <code>test_</code> of <code>live_</code>.";
$_['help_description']              = "De omschrijving zal op het bankafschrift van uw klant verschijnen en kunt u terugvinden in het Mollie beheer. U kunt maximaal 29 tekens gebruiken. TIP: Gebruik <code>%</code>, dit zal vervangen worden door het ordernummer. Het ordernummer kan zelf ook meerdere tekens lang zijn!";
$_['help_show_icons']               = "Toon icoontjes naast de betaalmethodes van Mollie op de betaalpagina.";
$_['help_show_order_canceled_page'] = "Toon een melding aan de klant als een betaling geannuleerd wordt, alvorens de klant terug naar het winkelmandje te verwijzen.";
$_['help_redirect_uri']				= 'URI omleiden in uw mollie-dashboard moet overeenkomen met deze URI.';
$_['help_mollie_app']				= 'Door uw module te registreren als een app op het Mollie-dashboard, ontgrendeld u extra functionaliteiten. Dit is niet vereist om Mollie-betalingen te gebruiken.';
$_['help_apple_pay']				= 'Voor Apple Pay dient credit card betaling geactiveerd te zijn op uw Mollie profiel. Activeer Credit Cards eerst.';
$_['help_mollie_component']			= 'Met Mollie-componenten kunt u velden weergeven die nodig zijn voor gegevens van creditcardhouders aan uw eigen kassa.';
$_['help_single_click_payment']		= 'Hiermee kunnen uw klanten met één klik een eerder gebruikte creditcard belasten.';
$_['help_total']					= 'Het minimum- en maximumbedrag voor het afrekenen voordat deze betaalmethode actief wordt.';
$_['help_payment_link']				= 'Tijdens het aanmaken van bestellingen vanuit de admin zal een <strong>Mollie Payment Link</strong> methode beschikbaar zijn om een ​​betaallink naar de klant te sturen voor betaling. U kunt e-mailtekst instellen op het tabblad e-mail.';

// Info
$_['entry_module']          = "Module";
$_['entry_mod_status']      = "Modulestatus";
$_['entry_comm_status']     = "Communicatiestatus";
$_['entry_support']         = "Support";

$_['entry_version']         = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']         = "Waarschuwing: U heeft geen toestemming om de module aan te passen.";
$_['error_api_key']            = "Mollie API-sleutel is verplicht!";
$_['error_api_key_invalid']    = "Ongeldige Mollie API-sleutel!";
$_['error_description']        = "De omschrijving is verplicht!";
$_['error_file_missing']       = "Bestand bestaat niet";
$_['error_name']               = 'Waarschuwing: naam moet tussen 3 en 25 tekens bevatten!';
$_['error_email']              = 'Waarschuwing: E-mailadres lijkt niet geldig te zijn!';
$_['error_subject']            = 'Waarschuwing: onderwerp moet 3 tekens lang zijn!';
$_['error_enquiry']            = 'Waarschuwing: onderzoekstekst moet 25 tekens lang zijn!';
$_['error_no_api_client']      = 'API client not found.';
$_['error_api_help']           = 'You can ask your hosting provider to help with this.';
$_['error_comm_failed']        = '<strong>Communicating with Mollie failed:</strong><br/>%s<br/><br/>Please check the following conditions. You can ask your hosting provider to help with this.<ul><li>Make sure outside connections to %s are not blocked.</li><li>Make sure SSL v3 is disabled on your server. Mollie does not support SSL v3.</li><li>Make sure your server is up-to-date and the latest security patches have been installed.</li></ul><br/>Contact <a href="mailto:info@mollie.nl">info@mollie.nl</a> if this still does not fix your problem.';
$_['error_no_api_key']         = 'No API key provided. Please insert your API key.';
$_['error_order_expiry_days']  = 'Waarschuwing: het is niet mogelijk om Klarna Slice it of Klarna Pay later als methode te gebruiken wanneer de vervaldatum meer dan 28 dagen in de toekomst ligt.';
$_['error_mollie_payment_fee'] = 'Waarschuwing: Mollie Payment Fee ordertotaal is uitgeschakeld!';
$_['error_file']               = 'Waarschuwing: %s bestand kon niet worden gevonden!';
$_['error_address']            = 'Factuuradres staat uit, digitale bestellingen kunnen niet betaald worden. U kunt het factuuradres inschakelen in de <a href="%s">instellingen</a>.';
 $_['error_telephone']         = 'Telefoonveld is vereist bij sommige betaalmethoden. Schakel dit in via de <a href="%s">instellingen</a> en maak het verplicht.';

// Status
$_['entry_pending_status']            = "Status betaling aangemaakt";
$_['entry_failed_status']             = "Status betaling mislukt";
$_['entry_canceled_status']           = "Status betaling geannuleerd";
$_['entry_expired_status']            = "Status betaling verlopen";
$_['entry_processing_status']         = "Status betaling succesvol";
$_['entry_refund_status']	          = "Status betaling terugbetaling";
$_['entry_partial_refund_status']	  = "Gedeeltelijke terugbetalingsstatus";
$_['entry_shipping_status']           = "Status bestelling verzonden";
$_['entry_shipment']       			  = "Maak verzending";
$_['entry_create_shipment_status']    = "Maak verzending aan na order status";
$_['help_shipment'] 				  = "Verzending wordt direct na het maken van de bestelling gemaakt. Selecteer 'Nee' om een ​​zending te creëren wanneer de order een specifieke status bereikt en selecteer de bestelstatus van onder.";

$_['text_create_shipment_automatically']            = "Maak automatisch een zending bij het maken van de bestelling";
$_['text_create_shipment_on_status']                = "Maak verzending bij het plaatsen van de bestelling naar deze status";
$_['text_create_shipment_on_order_complete']        = "Maak verzending bij het plaatsen van bestelling om de volledige status te bestellen";
$_['entry_create_shipment_on_order_complete'] 		= "Maak verzending bij bestelling compleet";

//Button
$_['button_update']         = "Bijwerken";
$_['button_mollie_connect'] = "Connect via Mollie";
$_['button_advance_option'] = "Advance Option";
$_['button_save_close']     = "Opslaan en sluiten";

//Error log
$_['text_log_success']	   = 'Succes: u hebt met succes uw foutenlogboek gewist!';
$_['text_log_list']        = 'Foutenlijst';
$_['error_log_warning']	   = 'Waarschuwing: uw foutenlogbestand %s is %s!';
$_['button_download']	   = 'Download';

//admin/language/nl-nl/sale/order.php
$_['button_refund'] = 'Terugbetaling';
$_['text_order_not_found'] = 'Mollie bestelgegevens niet gevonden!';
$_['text_no_refund'] = 'Restitutie kan niet worden verwerkt!';
$_['text_refunded_already'] = 'Restitutie is al verwerkt!';
$_['text_refund_success']     = 'Terugbetaling is succesvol verwerkt!';
$_['text_confirm_refund']     = 'U staat op het punt deze betaling terug te betalen. Dit kan niet ongedaan worden gemaakt. Weet u zeker dat u wilt doorgaan?';
$_['entry_amount']     = 'Amount';
$_['button_partial_refund']     = 'Gedeeltelijke terugbetaling';
$_['error_refund_amount']       = 'Waarschuwing: voer een correct bedrag in om terug te betalen!';
$_['text_partial_refund_success']     = 'Gedeeltelijke terugbetaling van bedrag %s is succesvol verwerkt!';
$_['entry_partial_refund_type']     = 'Terugbetalingstype';
$_['text_custom_amount']     = 'Terugbetaling aangepast bedrag';
$_['text_productline']       = 'Productlijn Terugbetaling';
$_['entry_productline']      = 'Productlijnen';
$_['entry_quantity']         = 'Aantel';
$_['help_quantity']          = 'Te restitueren aantal. Mag het bestelde aantal niet overschrijden.';
$_['error_productline']      = 'Waarschuwing: Selecteer een productlijn om terug te betalen!';
$_['tab_mollie']             = 'Mollie';
$_['text_mollie_payment']    = 'Betalingen';
$_['text_mollie_refund']     = 'Terugbetalingen';
$_['column_date_added']      = 'Datum toegevoegd';
$_['column_payment_method']  = 'Betalingsmiddel';
$_['column_amount']          = 'Bedrag';
$_['column_status']          = 'Status';
$_['button_payment_link']    = 'Betalingslink verzenden';
$_['column_stock_mutation']   = 'Voorraad mutatie';
$_['help_stock_mutation']    = 'Selecteer of u de gecrediteerde producten opnieuw wilt bevoorraden';
$_['entry_mollie_payment_link'] = 'Verstuur Mollie Betaallink';

//admin/language/nl-nl/catalog/product.php
$_['entry_voucher_category']            = 'Vouchercategorie';
$_['help_voucher_category']             = 'Kies uit categorieën om mollie voucher betalingen te gebruiken.';
