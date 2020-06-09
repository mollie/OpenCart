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
$method_list_logo              = '<a href="https://www.mollie.com" target="_blank"><img src="../image/mollie/mollie_logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']  = $method_list_logo;
$_['text_mollie_belfius']       = $method_list_logo;
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
$_['text_mollie_eps']           = $method_list_logo;
$_['text_mollie_giropay']       = $method_list_logo;
$_['text_mollie_klarnapaylater'] = $method_list_logo;
$_['text_mollie_klarnasliceit']  = $method_list_logo;
$_['text_mollie_przelewy24']  	 = $method_list_logo;
$_['text_mollie_applepay']  	 = $method_list_logo;

// Heading
$_['heading_title']         = "Mollie";
$_['title_global_options']  = "Einstellungen";
$_['title_payment_status']  = "Bezahlungs-Status";
$_['title_mod_about']       = "Über dieses Modul";
$_['footer_text']           = "Zahlungsdienste";

// Module names
$_['name_mollie_bancontact']    = "Bancontact";
$_['name_mollie_banktransfer']  = "Übertragung";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Einmaliges Inkasso";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "KBC/CBC-Betaalknop";
$_['name_mollie_paypal']        = "PayPal";
$_['name_mollie_paysafecard']   = "paysafecard";
$_['name_mollie_sofort']        = "SOFORT Banking";
$_['name_mollie_giftcard']      = 'Giftcard';
$_['name_mollie_inghomepay']    = 'ING Home\'Pay';
$_['name_mollie_eps']           = 'EPS';
$_['name_mollie_giropay']       = 'Giropay';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';

// Deprecated names
$_['name_mollie_bitcoin']       = "Bitcoin";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";

// Text
$_['text_edit']                     = "Mollie bearbeiten";
$_['text_payment']                  = "Bezahlung";
$_['text_success']                  = "Erfolg: Die Einstellungen für dieses Modul wurden angepasst!";
$_['text_missing_api_key']          = "Bitte füllen Sie Ihren API-Schlüssel auf der Registerkarte <a data-toggle='tab' href='#' class='settings'>Einstellungen</a> aus.";
$_['text_enable_payment_method']    = 'Aktivieren Sie diese Zahlungsart über das <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie Dashboard</a>.';
$_['text_activate_payment_method']  = 'Im <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie Dashboard</a> oder die App unter “Einstellungen” konfigurieren, um es auf dieser Seite zu aktivieren.';
$_['text_no_status_id']             = "- Status nicht ändern (nicht empfohlen) -";
$_['text_enable']                   = "Aktivieren";
$_['text_disable']                  = "Deaktivieren";
$_['text_connection_success']       = "Erfolg: Verbindung zu Mollie erfolgreich!";
$_['text_error'] 			        = "Warnung: Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal!";
$_['text_creditcard_required']      = "Kreditkarte erforderlich";
$_['text_mollie_api']               = "Mollie API";
$_['text_mollie_app']               = "Mollie App";
$_['text_general'] 	                = "Allgemeines";
$_['text_enquiry'] 	                = "Wie können wir Ihnen helfen?";
$_['text_enquiry_success'] 	        = "Erfolg: Ihre Anfrage wurde übermittelt. Wir werden uns umgehend bei Ihnen melden. Danke!";
$_['text_update_message']           = "Mollie: Es ist eine neue Version (%s) des Mollie Moduls verfügbar. Klicken Sie <a href='%s'>hier</a> für das Update.";
$_['text_update_success']          = "Erfolg: Das Mollie-Modul wurde auf Version %s aktualisiert.";
$_['text_default_currency']        = "Im Geschäft verwendete Währung";
$_['text_custom_css']              = "Custom CSS For Mollie Components";
$_['text_contact_us']              = "Kontaktieren Sie uns - Technischer Support";
$_['text_bg_color']                = "Background color";
$_['text_color']                   = "Color";
$_['text_font_size']               = "Font size";
$_['text_other_css']               = "Other CSS";
$_['text_module_by']               = "Module by Quality Works - Technical Support";
$_['text_mollie_support']          = "Mollie - Support";
$_['text_contact']                 = "Contact";

// Entry
$_['entry_payment_method']           = "Zahlungsart";
$_['entry_activate']                 = "Aktivieren";
$_['entry_sort_order']               = "Sortierreihenfolge";
$_['entry_api_key']                  = "API Key";
$_['entry_description']              = "Beschreibung";
$_['entry_show_icons']               = "Icons anzeigen";
$_['entry_show_order_canceled_page'] = "Meldung bei annullierten Bezahlungen anzeigen";
$_['entry_geo_zone']                 = "Geo Zone";
$_['entry_client_id']                = "Client ID";
$_['entry_client_secret']            = "Client Secret";
$_['entry_redirect_uri']             = "Redirect URI";
$_['entry_payment_screen_language']  = "Standardsprache des Zahlungsbildschirms";
$_['entry_mollie_connect'] 			 = "Mollie connect";
$_['entry_name'] 			 		 = "Name";
$_['entry_email'] 			 		 = "E-mail";
$_['entry_subject'] 			     = "Gegenstand";
$_['entry_enquiry'] 			 	 = "Anfrage";
$_['entry_debug_mode'] 			 	 = "Debug mode";
$_['entry_mollie_component_base'] 	 = "Custom CSS for Base input field";
$_['entry_mollie_component_valid'] 	 = "Custom CSS for Valid input field";
$_['entry_mollie_component_invalid'] = "Custom CSS for Invalid input field";
$_['entry_default_currency'] 		 = "Immer mit bezahlen";

// Help
$_['help_view_profile']             = 'Sie können Ihren API Key auf <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link">Ihren Mollie-Webseiten-Profilen finden</a>.';
$_['help_status']                   = "Das Modul aktivieren";
$_['help_api_key']                  = "Geben Sie hier den <code>api_key</code> des Webseiten-Profils ein, das Sie verwenden wollen. Der API Key beginnt mit <code>test_</code> oder <code>live_</code>.";
$_['help_description']              = "Die Beschreibung soll auf der Banküberweisung Ihres Kunden erscheinen und Sie können sie in der Mollie Verwaltung sehen. Sie können maximal 29 Zeichen verwenden. TIPP: Verwenden Sie %, dies wird durch die Auftragsnummer ersetzt werden. Die Auftragsnummer selbst kann auch mehrere Zeichen lang sein!";
$_['help_show_icons']               = "Icons neben den Zahlungsarten von Mollie auf der Zahlungsseite anzeigen.";
$_['help_show_order_canceled_page'] = "Eine Meldung für den Kunden anzeigen, wenn eine Zahlung annulliert wurde, bevor der Kunde zurück zum Warenkorb verwiesen wird.";
$_['help_redirect_uri']				= 'Der Redirect-URI in Ihrem Mollie-Dashboard muss mit diesem URI übereinstimmen.';
$_['help_mollie_app']				= 'Wenn Sie Ihr Modul als App im Mollie-Dashboard registrieren, werden zusätzliche Funktionen freigeschaltet. Dies ist nicht erforderlich, um Mollie-Zahlungen zu verwenden.';
$_['help_apple_pay']				= 'Apple Pay benötigt die Aktivierung der Kreditkartenzahlung in Ihrem Webprofil. Bitte schalten Sie zunächst die Kreditkarten Methode frei.';

// Info
$_['entry_module']          = "Module";
$_['entry_mod_status']      = "Modulestatus";
$_['entry_comm_status']     = "Kommunikationsstatus";
$_['entry_support']         = "Support";

$_['entry_version']         = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']      = "Warnung: Sie haben keine Berechtigung, das Modul zu bearbeiten.";
$_['error_api_key']         = "Mollie API Key ist verpflichtend!";
$_['error_api_key_invalid'] = "Ungültiger Mollie API Key!";
$_['error_description']     = "Die Beschreibung ist obligatorisch!";
$_['error_file_missing']    = "Die Datei existiert nicht";
$_['error_name']              = 'Achtung: Der Name muss zwischen 3 und 25 Zeichen lang sein!';
$_['error_email']             = 'Achtung: E-Mail-Adresse scheint nicht gültig zu sein!';
$_['error_subject']           = 'Achtung: Betreff muss 3 Zeichen lang sein!';
$_['error_enquiry']           = 'Achtung: Der Anfragetext muss 25 Zeichen lang sein!';
$_['error_no_api_client']     = 'API client not found.';
$_['error_api_help']          = 'You can ask your hosting provider to help with this.';
$_['error_comm_failed']       = '<strong>Communicating with Mollie failed:</strong><br/>%s<br/><br/>Please check the following conditions. You can ask your hosting provider to help with this.<ul><li>Make sure outside connections to %s are not blocked.</li><li>Make sure SSL v3 is disabled on your server. Mollie does not support SSL v3.</li><li>Make sure your server is up-to-date and the latest security patches have been installed.</li></ul><br/>Contact <a href="mailto:info@mollie.nl">info@mollie.nl</a> if this still does not fix your problem.';
$_['error_no_api_key']        = 'No API key provided. Please insert your API key.';

// Status
$_['entry_pending_status']   = "Status Zahlung erstellt";
$_['entry_failed_status']    = "Status Zahlung fehlgeschlagen";
$_['entry_canceled_status']  = "Status Zahlung annulliert";
$_['entry_expired_status']   = "Status Zahlung verstrichen";
$_['entry_processing_status']= "Status Zahlung erfolgreich";
$_['entry_refund_status']	  = "Status Zahlung rückerstattung";

$_['entry_shipping_status']   = "Versandstatus der Bestellung";
$_['entry_shipment']       			 = "Sendung erstellen";
$_['entry_create_shipment_status']   = "Erstellen Sie eine Sendung nach dem Bestellstatus";
$_['help_shipment'] 				 = "Versand (nur für Klarna-Methoden) wird direkt nach dem Erstellen der Bestellung erstellt. Wählen Sie 'Nein', um die Sendung zu erstellen, wenn die Bestellung einen bestimmten Status erreicht, und wählen Sie den Bestellstatus von unten aus.";

$_['text_create_shipment_automatically']            = "Erstellen Sie den Versand automatisch bei der Auftragserstellung";
$_['text_create_shipment_on_status']                = "Legen Sie eine Sendung an, wenn Sie den Auftrag auf diesen Status setzen";
$_['text_create_shipment_on_order_complete']        = "Erstellen Sie eine Sendung, nachdem Sie den Status zum Bestellen der Bestellung festgelegt haben";
$_['entry_create_shipment_on_order_complete'] 		= "Erstellen Sie den Versand nach Abschluss der Bestellung";

//Button
$_['button_update'] = "Aktualisieren";
$_['button_mollie_connect'] = "Connect via Mollie";

//Error log
$_['text_log_success']	   = 'Erfolg: Sie haben Ihr Fehlerprotokoll erfolgreich gelöscht!';
$_['text_log_list']        = 'Fehlerliste';
$_['error_log_warning']	   = 'Warnung: Ihre Fehlerprotokolldatei %s ist %s!';
$_['button_download']	   = 'Herunterladen';
