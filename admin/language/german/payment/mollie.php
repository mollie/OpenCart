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
 * @author		OSWorX https://osworx.net
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.com
 */

// These are called automatically by the Payment modules list - do not change the names
$method_list_logo					= '<a href="https://www.mollie.com" target="_blank"><img src="view/image/payment/mollie_logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']		= $method_list_logo;
$_['text_mollie_belfius']			= $method_list_logo;
$_['text_mollie_creditcard']		= $method_list_logo;
$_['text_mollie_directdebit']		= $method_list_logo;
$_['text_mollie_ideal']				= $method_list_logo;
$_['text_mollie_kbc']				= $method_list_logo;
$_['text_mollie_mistercash']		= $method_list_logo;
$_['text_mollie_paypal']			= $method_list_logo;
$_['text_mollie_paysafecard']		= $method_list_logo;
$_['text_mollie_sofort']			= $method_list_logo;
$_['text_mollie_giftcard']			= $method_list_logo;
$_['text_mollie_inghomepay']		= $method_list_logo;
$_['text_mollie_eps']				= $method_list_logo;
$_['text_mollie_giropay']			= $method_list_logo;
$_['text_mollie_klarnapaylater']	= $method_list_logo;
$_['text_mollie_klarnasliceit']		= $method_list_logo;
$_['text_mollie_przelewy24']		= $method_list_logo;
$_['text_mollie_applepay']			= $method_list_logo;

// Heading
$_['heading_title']					= 'Mollie';
$_['title_global_options']			= 'Einstellungen';
$_['title_payment_status']			= 'Zahlungsstatus';
$_['title_mod_about']				= 'Über diese Erweiterung';
$_['footer_text']					= 'Zahlungsdienste';

// Module names
$_['name_mollie_bancontact']		= 'Bancontact';
$_['name_mollie_banktransfer']		= 'Banküberweisung';
$_['name_mollie_belfius']			= 'Belfius Direct Net';
$_['name_mollie_creditcard']		= 'Kreditkarte';
$_['name_mollie_directdebit']		= 'SEPA Lastschrift';
$_['name_mollie_ideal']				= 'iDEAL';
$_['name_mollie_kbc']				= 'KBC/CBC Bezahlung';
$_['name_mollie_paypal']			= 'PayPal';
$_['name_mollie_paysafecard']		= 'paysafecard';
$_['name_mollie_sofort']			= 'Sofortüberweisung';
$_['name_mollie_giftcard']			= 'Geschenkkarte';
$_['name_mollie_inghomepay']		= 'ING Home\'Pay';
$_['name_mollie_eps']				= 'EPS';
$_['name_mollie_giropay']			= 'Giropay';
$_['name_mollie_klarnapaylater']	= 'Klarna Rechnung';
$_['name_mollie_klarnasliceit']		= 'Klarna Ratenkauf';
$_['name_mollie_przelewy24']		= 'P24';
$_['name_mollie_applepay']			= 'Apple Pay';

// Deprecated names
$_['name_mollie_bitcoin']			= 'Bitcoin';
$_['name_mollie_mistercash']		= 'Bancontact/MisterCash';

// Text
$_['text_edit']						= 'Zahlart bearbeiten';
$_['text_payment']					= 'Zahlart';
$_['text_success']					= 'Einstellungen erfolgreich bearbeitet';;
$_['text_missing_api_key']			= 'Bitte den API-Schlüssel im Feld API-Schlüssel unter <a data-toggle="tab" href="#" class="settings">Einstellungen</a> angeben.';
$_['text_enable_payment_method']	= 'Bitte diese Zahlungsart über das <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie Dashboard</a> aktivieren.';
$_['text_activate_payment_method']	= 'Im <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie Dashboard</a> oder die App unter "Einstellungen" konfigurieren, um es auf dieser Seite zu aktivieren.';
$_['text_no_status_id']				= '- Status nicht ändern (nicht empfohlen) -';
$_['text_enable']					= 'Aktivieren';
$_['text_disable']					= 'Deaktivieren';
$_['text_connection_success']		= 'Verbindung zu Mollie ist erfolgreich';
$_['text_error']					= 'Es ist ein Fehler aufgetreten .. Bitte später nochmal versuchen';
$_['text_creditcard_required']		= 'Kreditkarte&nbsp;erforderlich';
$_['text_mollie_api']				= 'Mollie API';
$_['text_mollie_app']				= 'Mollie App';
$_['text_general']					= 'Allgemein';
$_['text_enquiry']					= 'Wie können wir helfen?';
$_['text_enquiry_success']			= 'Die Anfrage wurde erfolgreich übermittelt .. wir werden uns umgehend melden. Danke.';
$_['text_update_message']			= 'Nachricht von Mollie: es ist eine neue Version (%s) dieses Moduls verfügbar. Zum Aktualisieren <a href="%s">hier klicken</a>';
$_['text_update_success']			= 'Das Modul wurde erfolgreich auf Version %s aktualisiert.';
$_['text_default_currency']			= 'Im Geschäft verwendete Währung';

// Entry
$_['entry_payment_method']				= 'Zahlungsarten';
$_['entry_activate']					= 'Status';
$_['entry_sort_order']					= 'Reihenfolge';
$_['entry_api_key']						= 'API-Schlüssel';
$_['entry_description']					= 'Beschreibung';
$_['entry_show_icons']					= 'Icons anzeigen';
$_['entry_show_order_canceled_page']	= 'Meldung bei stornierten Zahlungen anzeigen';
$_['entry_geo_zone']					= 'Geozone';
$_['entry_client_id']					= 'Kundennummer';
$_['entry_client_secret']				= 'Geheimbegriff';
$_['entry_redirect_uri']				= 'Umleitungs-URL';
$_['entry_payment_screen_language']		= 'Standardsprache des Zahlungsbildschirms';
$_['entry_mollie_connect']				= 'Mollie Connect';
$_['entry_name']						= 'Name';
$_['entry_email']						= 'Email';
$_['entry_subject']						= 'Betreff';
$_['entry_enquiry']						= 'Anfrage';
$_['entry_debug_mode']					= 'Debugmodus';
$_['entry_mollie_component']			= 'Mollie Komponenten';
$_['entry_test_mode']					= 'Testmodus';
$_['entry_mollie_component_base']		= 'Eig. CSS für Basiseingabefeld';
$_['entry_mollie_component_valid']		= 'Eig. CSS für gültige Eingabe';
$_['entry_mollie_component_invalid']	= 'Eig. CSS für ungültige Eingabe';
$_['entry_default_currency']			= 'Standardwährung';

// Help
$_['help_view_profile']					= 'Der API-SChlüssel ist im Mollie Dashboard unter <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link">Webseiten-Profile</a> zu finden.';
$_['help_status']						= 'Das Modul aktivieren';
$_['help_api_key']						= 'Hier den <code>api_key</code> des Webseiten-Profils eingeben welcher angewendet werden soll.<br>Der API-Schlüssel beginnt mit <code>test_</code> oder <code>live_</code>.';
$_['help_description']					= 'Beschreibung wie sie auf der Banküberweisung des Kunden aufscheint und in der Mollieverwaltung angezeigt wird.<br>Es düfen maximal 29 Zeichen verwendet werden.<br>TIPP: % (Prozentzeichen anwenden), es wird dann durch die Auftragsnummer ersetzt. Die Auftragsnummer selbst kann auch mehrere Zeichen lang sein.';
$_['help_show_icons']					= 'Soll neben dem Text im Kassenberiech auch das Icon angezeigt werden';
$_['help_show_order_canceled_page']		= 'Dem Kunden im Kassenbereich eine Meldung anzeigen wenn eine Zahlung storniert/abgebrochen wurde.';
$_['help_redirect_uri']					= 'Die Umleitungs-URL im Mollie-Dashboard muss mit der hier angezeigten übereinstimmen!';
$_['help_mollie_app']					= 'Wird das Modul als App im Mollie-Dashboard registriert, werden zusätzliche Funktionen freigeschaltet. Dies ist nicht erforderlich, um Mollie-Zahlarten anzuwenden.';
$_['help_apple_pay']					= 'Apple Pay benötigt die Aktivierung der Kreditkartenzahlung im Webprofil. Zusätzlich muss dafür auch die Zahlung mit Kreditkarten freigeschaltet sein.';
$_['help_mollie_component']				= 'Mollie Komponenten ermöglichen es Felder für Kreditkarten im eigenen KAsenberiech anzuzeigen';

// Info
$_['entry_module']						= 'Erweiterung';
$_['entry_mod_status']					= 'Status';
$_['entry_comm_status']					= 'Kommunikationsstatus';
$_['entry_support']						= 'Unterstützung';

$_['entry_version']						= '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie für OpenCcart</a>';

// Error
$_['error_permission']					= 'Keine Berechtigung diese Erweiterung zu bearbeiten';
$_['error_api_key']						= 'Mollie API-SChlüssel ist verpflichtend';
$_['error_api_key_invalid']				= 'Ungültiger Mollie API-Schlüssel';
$_['error_description']					= 'Ein Text ist erforderlich';
$_['error_file_missing']				= 'Die Datei existiert nicht';
$_['error_name']						= 'Name muss zwischen 3 und 25 Zeichen lang sein';
$_['error_email']						= 'Emailadresse scheint nicht gültig zu sein';
$_['error_subject']						= 'Betreff muss mindestens 3 Zeichen lang sein';
$_['error_enquiry']						= 'Der Anfragetext muss mindestens 25 Zeichen lang sein';

// Status
$_['entry_pending_status']				= 'Zahlung erstellt';
$_['entry_failed_status']				= 'Zahlung fehlgeschlagen';
$_['entry_canceled_status']				= 'Zahlung storniert';
$_['entry_expired_status']				= 'Zahlung abgelaufen';
$_['entry_processing_status']			= 'Zahlung erfolgreich';
$_['entry_refund_status']				= 'Zahlung rückerstattet';

$_['entry_shipping_status']				= 'Versandstatus der Bestellung';
$_['entry_shipment']					= 'Versand erstellen';
$_['entry_create_shipment_status']		= 'Versand nach Bestellstatus erstellen';
$_['help_shipment']						= 'Versand (nur für Klarna-Bezahlarten)<br>Wird direkt nach dem Erstellen der Bestellung erstellt.<br>Gewünschte Aktion auswählen';

$_['text_create_shipment_automatically']		= 'Versand automatisch bei Auftragsanlage erstellen';
$_['text_create_shipment_on_status']			= 'Versand automatisch bei unten ausgewählten Status erstellen';
$_['text_create_shipment_on_order_complete']	= 'Versand automatisch erstellen wenn Auftrag komplett ist';
$_['entry_create_shipment_on_order_complete']	= 'Versand nach Abschluss der Bestellung erstellen';

//Button
$_['button_update']								= 'Aktualisieren';
$_['button_mollie_connect']						= 'Mit Mollie verbinden';

//Error log
$_['text_log_success']		= 'Berichte erfolgreich gelöscht';
$_['text_log_list']			= 'Berichte';
$_['error_log_warning']		= 'Achtung: die Berichtsdatei %s ist %s groß';
$_['button_download']		= 'Herunterladen';
