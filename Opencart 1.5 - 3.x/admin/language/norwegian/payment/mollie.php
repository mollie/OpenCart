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
$method_list_logo                   = '<a href="https://www.mollie.com" target="_blank"><img src="../image/mollie/mollie_logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']      = $method_list_logo;
$_['text_mollie_belfius']           = $method_list_logo;
$_['text_mollie_creditcard']        = $method_list_logo;
$_['text_mollie_ideal']             = $method_list_logo;
$_['text_mollie_kbc']               = $method_list_logo;
$_['text_mollie_bancontact']        = $method_list_logo;
$_['text_mollie_paypal']            = $method_list_logo;
$_['text_mollie_giftcard']          = $method_list_logo;
$_['text_mollie_eps']               = $method_list_logo;
$_['text_mollie_klarnapaylater']    = $method_list_logo;
$_['text_mollie_klarnapaynow']      = $method_list_logo;
$_['text_mollie_klarnasliceit']     = $method_list_logo;
$_['text_mollie_przelewy24']  	    = $method_list_logo;
$_['text_mollie_applepay']  	    = $method_list_logo;
$_['text_mollie_voucher']    	    = $method_list_logo;
$_['text_mollie_in3']      	        = $method_list_logo;
$_['text_mollie_mybank']      	    = $method_list_logo;
$_['text_mollie_billie']      	    = $method_list_logo;
$_['text_mollie_klarna']      	    = $method_list_logo;
$_['text_mollie_twint']      	    = $method_list_logo;
$_['text_mollie_blik']      	    = $method_list_logo;
$_['text_mollie_bancomatpay']       = $method_list_logo;
$_['text_mollie_trustly']           = $method_list_logo;
$_['text_mollie_alma']              = $method_list_logo;
$_['text_mollie_riverty']           = $method_list_logo;
$_['text_mollie_payconiq']          = $method_list_logo;
$_['text_mollie_satispay']          = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Innstillinger";
$_['title_payment_status']    = "Betalingsstatuser";
$_['title_mod_about']         = "Om denne modulen";
$_['footer_text']             = "Betalingstjenester";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Bankoverføring";
$_['name_mollie_belfius']        = "Belfius Direct Net";
$_['name_mollie_creditcard']     = "Creditcard";
$_['name_mollie_ideal']          = "iDEAL";
$_['name_mollie_kbc']            = "KBC/CBC Payment Button";
$_['name_mollie_bancontact']     = "Bancontact";
$_['name_mollie_paypal']         = "PayPal";
$_['name_mollie_giftcard']       = 'Giftcard';
$_['name_mollie_eps']            = 'EPS';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnapaynow']   = 'Klarna Pay Now';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in3']            = "iDEAL in3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";
$_['name_mollie_twint']          = "Twint";
$_['name_mollie_blik']           = "Blik";
$_['name_mollie_bancomatpay']    = "Bancomat Pay";
$_['name_mollie_trustly']        = "Trustly";
$_['name_mollie_alma']           = "Alma";
$_['name_mollie_riverty']        = "Riverty";
$_['name_mollie_payconiq']       = "Payconiq";
$_['name_mollie_satispay']       = "Satispay";

// Text
$_['text_edit']                    = "Redigere";
$_['text_payment']                 = "innbetaling";
$_['text_success']                 = "Suksess: Du har endret Mollie-innstillingene dine!";
$_['text_missing_api_key']         = "Fyll ut API-nøkkelen din i <a data-toggle='tab' href='#' class='settings'>Innstillinger</a>-fanen.";
$_['text_activate_payment_method'] = 'Aktiver denne betalingsmåten i <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie-oversikten</a>.';
$_['text_no_status_id']            = "- Ikke oppdater bestillingsstatusen (anbefales ikke) -";
$_['text_enable']                  = "Muliggjøre";
$_['text_disable']                 = "Deaktiver";
$_['text_connection_success']      = "Suksess: Tilkobling til Mollie vellykket!";
$_['text_error']                   = "Advarsel: Noe gikk galt. Prøv igjen senere!";
$_['text_creditcard_required']     = "Krever kredittkort";
$_['text_mollie_api']              = "Mollie API";
$_['text_mollie_app']              = "Mollie-appen";
$_['text_general']                 = "Generelt";
$_['text_enquiry']                 = "Hvordan kan vi hjelpe deg?";
$_['text_enquiry_success']         = "Suksess: Din forespørsel er sendt. Vi kommer tilbake til deg snart. Takk!";
$_['text_update_message']          = 'Mollie: En ny versjon (%s) er tilgjengelig. Klikk <a href="%s">her</a> for å oppdatere. Vil du ikke se denne meldingen igjen? Klikk <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">her</a>.';
$_['text_update_message_warning']  = 'Mollie: En ny versjon (%s) er tilgjengelig. Vennligst oppdater PHP-versjonen din til %s eller høyere for å oppdatere modulen eller fortsett å bruke gjeldende versjon. Vil du ikke se denne meldingen igjen? Klikk <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">her</a>.';
$_['text_update_success']          = "Suksess: Mollie-modulen har blitt oppdatert til versjon %s.";
$_['text_default_currency']        = "Valuta brukt i butikken";
$_['text_custom_css']              = "Egendefinert CSS for Mollie-komponenter";
$_['text_contact_us']              = "Kontakt oss - teknisk støtte";
$_['text_bg_color']                = "Bakgrunnsfarge";
$_['text_color']                   = "Farge";
$_['text_font_size']               = "Skriftstørrelse";
$_['text_other_css']               = "Annen CSS";
$_['text_module_by']               = "Modul etter Quality Works - teknisk støtte";
$_['text_mollie_support']          = "Mollie - Støtte";
$_['text_contact']                 = "Kontakt";
$_['text_allowed_variables']       = "Tillatte variabler: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Bla gjennom';
$_['text_clear']                   = 'Slett';
$_['text_image_manager']           = 'Bildebehandling';
$_['text_left']                    = 'Venstre';
$_['text_right']                   = 'Høyre';
$_['text_more']                    = 'Mer';
$_['text_no_maximum_limit']        = 'Ingen maksimumsbeløpsgrense';
$_['text_standard_total']          = 'Standard totalt: %s';
$_['text_advance_option']          = 'Avanserte alternativer for %s';
$_['text_payment_api']             = 'Betalings-API';
$_['text_order_api']               = 'Bestillinger API';
$_['text_info_orders_api']         = 'Hvorfor bruke Orders API?';
$_['text_pay_link_variables']      = "Tillatte variabler: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Gjentakende betaling";
$_['text_payment_link']            = "Betalingslenke";
$_['text_coming_soon']             = "Kommer snart";

// Entry
$_['entry_payment_method']         = "Betalingsmetode";
$_['entry_activate']               = "Aktiver";
$_['entry_sort_order']             = "Sorteringsrekkefølge";
$_['entry_api_key']                = "API-nøkkel";
$_['entry_description']            = "Beskrivelse";
$_['entry_show_icons']             = "Vis ikoner";
$_['entry_show_order_canceled_page'] = "Vis melding hvis betalingen kanselleres";
$_['entry_geo_zone']               = "Geosone";
$_['entry_client_id']              = "Klient-ID";
$_['entry_client_secret']          = "Klienthemmelighet";
$_['entry_redirect_uri']           = "Omdiriger URI";
$_['entry_payment_screen_language'] = "Standardspråk for betalingsskjerm";
$_['entry_mollie_connect']         = "Mollie koble til";
$_['entry_name']                   = "Navn";
$_['entry_email']                  = "E-mail";
$_['entry_subject']                = "Emne";
$_['entry_enquiry']                = "Forespørsel";
$_['entry_debug_mode']             = "Feilsøkingsmodus";
$_['entry_mollie_component']       = "Mollie-komponenter";
$_['entry_test_mode']              = "Testmodus";
$_['entry_mollie_component_base']  = "Egendefinert CSS for Base-inndatafelt";
$_['entry_mollie_component_valid'] = "Egendefinert CSS for gyldig inndatafelt";
$_['entry_mollie_component_invalid'] = "Egendefinert CSS for ugyldig inndatafelt";
$_['entry_default_currency']       = "Betal alltid med";
$_['entry_email_subject']          = "Emne";
$_['entry_email_body']             = "Bruk";
$_['entry_title']                  = "Tittel";
$_['entry_image']                  = "Bilde";
$_['entry_status']                 = "Status";
$_['entry_align_icons']            = "Juster ikoner";
$_['entry_single_click_payment']   = "Enkeltklikksbetaling";
$_['entry_order_expiry_days']      = "Bestillingsutløpsdager";
$_['entry_partial_refund']         = "Delvis refusjon";
$_['entry_amount']                 = "Beløp (eksempel: 5 eller 5%)";
$_['entry_payment_fee']            = "Betalingsgebyr";
$_['entry_payment_fee_tax_class']  = "Betalingsgebyr Skatteklasse";
$_['entry_total']                  = "Totalt";
$_['entry_minimum']                = "Minimum";
$_['entry_maximum']                = "Maksimum";
$_['entry_api_to_use']             = "API å bruke";
$_['entry_payment_link']  		     = "Send betalingslenke";
$_['entry_payment_link_sep_email']   = "Send inn en egen e-post";
$_['entry_payment_link_ord_email']   = "Send e-post med ordrebekreftelse";
$_['entry_partial_credit_order']     = 'Opprett kreditordre på (delvis) refusjon';

// Help
$_['help_view_profile']             = 'Du finner API-nøkkelen din i <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link" >profilene dine på Mollie-nettstedet</a>.';
$_['help_status']                   = "Aktiver modulen";
$_['help_api_key']                  = 'Skriv inn <code>api_key</code> til nettsideprofilen du vil bruke. API-nøkkelen starter med <code>test_</code> eller <code>live_</code>.';
$_['help_description']              = 'Denne beskrivelsen vil vises på bank-/kortutskriften til kunden din. Du kan maksimalt bruke 29 tegn. TIPS: Bruk <code>%</code>, dette vil bli erstattet av bestillings-IDen til betalingen. Ikke glem at <code>%</code> kan bestå av flere tegn!';
$_['help_show_icons']               = 'Vis ikoner ved siden av Mollie-betalingsmåtene på betalingssiden.';
$_['help_show_order_canceled_page'] = 'Vis en melding til kunden hvis en betaling kanselleres, før du omdirigerer kunden tilbake til handlekurven.';
$_['help_redirect_uri']             = 'Omdiriger URI i mollie-dashbordet må samsvare med denne URI.';
$_['help_mollie_app']               = 'Ved å registrere modulen din som en app på Mollie-dashbordet, vil du låse opp ekstra funksjoner. Dette er ikke nødvendig for å bruke Mollie-betalinger.';
$_['help_apple_pay']                = 'Apple Pay krever at kredittkort er aktivert på din nettstedsprofil. Vennligst aktiver kredittkortmetoden først.';
$_['help_mollie_component']         = 'Mollie-komponenter lar deg vise feltene som trengs for kredittkortholderdata til din egen kassen.';
$_['help_single_click_payment']     = 'Gjør det mulig for kundene dine å belaste et tidligere brukt kredittkort med et enkelt klikk.';
$_['help_total']                    = 'Minimums- og maksimumsbeløp for betaling før denne betalingsmåten blir aktiv.';
$_['help_payment_link']				= 'Når du oppretter bestillinger fra admin, vil en <strong>Mollie Payment Link</strong>-metode være tilgjengelig for å sende betalingslenke til kunden for betaling. Du kan angi e-posttekst under e-postfanen.';

// Info
$_['entry_module']      = "Modul";
$_['entry_mod_status']  = "Modulstatus";
$_['entry_comm_status'] = "Kommunikasjonsstatus";
$_['entry_support']     = "Støtte";
$_['entry_version']     = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']       = "Advarsel: Du har ikke tillatelse til å endre Mollie-betalingsmetodene.";
$_['error_api_key']          = "Mollie API-nøkkel er nødvendig!";
$_['error_api_key_invalid']  = "Ugyldig API-nøkkel!";
$_['error_description']      = "Beskrivelse er nødvendig!";
$_['error_file_missing']     = "Filen finnes ikke";
$_['error_name']             = 'Advarsel: Navnet må være mellom 3 og 25 tegn!';
$_['error_email']            = 'Advarsel: E-postadressen ser ikke ut til å være gyldig!';
$_['error_subject']          = 'Advarsel: Emnet må være 3 tegn langt!';
$_['error_enquiry']          = 'Advarsel: Forespørselsteksten må være på 25 tegn!';
$_['error_no_api_client']    = 'API-klient ikke funnet.';
$_['error_api_help']         = 'Du kan be din vertsleverandør om å hjelpe med dette.';
$_['error_comm_failed']      = '<strong>Kommunikasjon med Mollie mislyktes:</strong><br/>%s<br/><br/>Kontroller følgende forhold. Du kan be vertsleverandøren din om å hjelpe med dette.<ul><li>Sørg for at eksterne forbindelser til %s ikke er blokkert.</li><li>Sørg for at SSL v3 er deaktivert på serveren din. Mollie støtter ikke SSL v3.</li><li>Sørg for at serveren din er oppdatert og at de nyeste sikkerhetsoppdateringene er installert.</li></ul><br/>Kontakt <a href= "mailto:info@mollie.nl">info@mollie.nl</a> hvis dette fortsatt ikke løser problemet.';
$_['error_no_api_key']        = 'Ingen API-nøkkel oppgitt. Vennligst sett inn din API-nøkkel.';
$_['error_order_expiry_days'] = 'Advarsel: Det er ikke mulig å bruke Klarna Slice it eller Klarna Pay senere som metode når utløpsdatoen er mer enn 28 dager i fremtiden.';
$_['error_mollie_payment_fee'] = 'Advarsel: Bestillingssummen for Mollie betalingsgebyr er deaktivert!';
$_['error_min_php_version']   = 'Advarsel: Denne Mollie-modulen trenger PHP-versjon %s eller høyere for å fungere. Ta kontakt med webutvikleren din for å fikse dette problemet!';

// Status
$_['entry_pending_status']        = "Status for opprettet betaling";
$_['entry_failed_status']         = "Betaling mislyktes status";
$_['entry_canceled_status']       = "Betaling kansellert status";
$_['entry_expired_status']        = "Status for utløpt betaling";
$_['entry_processing_status']     = "Betalingen vellykket status";
$_['entry_refund_status']         = "Refusjonsstatus for betaling";
$_['entry_partial_refund_status'] = "Delvis refusjonsstatus";
$_['entry_shipping_status']       = "Ordre sendt status";
$_['entry_shipment']              = "Opprett forsendelse";
$_['entry_create_shipment_status'] = "Opprett forsendelse etter ordrestatus";
$_['help_shipment']               = "Forsendelse vil bli opprettet rett etter opprettelse av bestilling. Velg 'Nei' for å opprette forsendelse når bestillingen når en spesifikk status og velg bestillingsstatus nedenfor.";

$_['text_create_shipment_automatically']      = "Opprett forsendelse automatisk ved bestilling";
$_['text_create_shipment_on_status']          = "Opprett forsendelse ved å sette ordren til denne statusen";
$_['text_create_shipment_on_order_complete']  = "Opprett forsendelse ved å sette ordre for å bestille fullstendig status";
$_['entry_create_shipment_on_order_complete'] = "Opprett forsendelse når bestillingen er fullført";

// Button
$_['button_update']         = "Oppdater";
$_['button_mollie_connect'] = "Koble til via Mollie";
$_['button_advance_option'] = "Forhåndsalternativ";
$_['button_save_close']     = "Lagre og lukk";

// Error log
$_['text_log_success']  = 'Suksess: Du har tømt mollie-loggen din!';
$_['text_log_list']     = 'Logg';
$_['error_log_warning'] = 'Advarsel: Din mollie-loggfil %s er %s!';
$_['button_download']   = 'Download';

// Summernote
$_['summernote']                    = 'nb-NO';
