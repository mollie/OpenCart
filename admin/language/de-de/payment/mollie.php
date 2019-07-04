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

// Heading
$_['heading_title']         = "Mollie";
$_['title_global_options']  = "Einstellungen";
$_['title_payment_status']  = "Bezahlungs-Status";
$_['title_mod_about']       = "Über dieses Modul";
$_['footer_text']           = "Zahlungsdienste";

// Module names
$_['name_mollie_banktransfer']  = "Übertragung";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Einmaliges Inkasso";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "KBC/CBC-Betaalknop";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";
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

// Text
$_['text_edit']                    = "Mollie bearbeiten";
$_['text_payment']                 = "Bezahlung";
$_['text_success']                 = "Erfolg: Die Einstellungen für dieses Modul wurden angepasst!";
$_['text_missing_api_key']         = "Bitte füllen Sie Ihren API-Schlüssel auf der Registerkarte <a data-toggle='tab' href='#' class='settings'>Einstellungen</a> aus.";
$_['text_activate_payment_method'] = 'Aktivieren Sie diese Zahlungsart über das <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank">Mollie Dashboard</a>.';
$_['text_no_status_id']            = "- Status nicht ändern (nicht empfohlen) -";

// Entry
$_['entry_payment_method']           = "Zahlungsart";
$_['entry_activate']                 = "Aktivieren";
$_['entry_sort_order']               = "Sortierreihenfolge";
$_['entry_api_key']                  = "API Key";
$_['entry_description']              = "Beschreibung";
$_['entry_show_icons']               = "Icons anzeigen";
$_['entry_show_order_canceled_page'] = "Meldung bei annullierten Bezahlungen anzeigen";
$_['entry_geo_zone']                 = "Geo Zone";

// Help
$_['help_view_profile']             = 'Sie können Ihren API Key auf <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank" class="alert-link">Ihren Mollie-Webseiten-Profilen finden</a>.';
$_['help_status']                   = "Das Modul aktivieren";
$_['help_api_key']                  = "Geben Sie hier den <code>api_key</code> des Webseiten-Profils ein, das Sie verwenden wollen. Der API Key beginnt mit <code>test_</code> oder <code>live_</code>.";
$_['help_description']              = "Die Beschreibung soll auf der Banküberweisung Ihres Kunden erscheinen und Sie können sie in der Mollie Verwaltung sehen. Sie können maximal 29 Zeichen verwenden. TIPP: Verwenden Sie %, dies wird durch die Auftragsnummer ersetzt werden. Die Auftragsnummer selbst kann auch mehrere Zeichen lang sein!";
$_['help_show_icons']               = "Icons neben den Zahlungsarten von Mollie auf der Zahlungsseite anzeigen.";
$_['help_show_order_canceled_page'] = "Eine Meldung für den Kunden anzeigen, wenn eine Zahlung annulliert wurde, bevor der Kunde zurück zum Warenkorb verwiesen wird.";

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

// Status
$_['entry_pending_status']   = "Status Zahlung erstellt";
$_['entry_failed_status']    = "Status Zahlung fehlgeschlagen";
$_['entry_canceled_status']  = "Status Zahlung annulliert";
$_['entry_expired_status']   = "Status Zahlung verstrichen";
$_['entry_processing_status']= "Status Zahlung erfolgreich";

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
