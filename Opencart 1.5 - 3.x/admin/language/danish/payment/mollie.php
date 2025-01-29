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
$_['title_global_options']    = "Indstillinger";
$_['title_payment_status']    = "Betalingsstatusser";
$_['title_mod_about']         = "Om dette modul";
$_['footer_text']             = "Betalingstjenester";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Bankoverførsel";
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
$_['text_payment']                 = "Betaling";
$_['text_success']                 = "Succes: Du har ændret dine Mollie-indstillinger!";
$_['text_missing_api_key']         = "Udfyld venligst din API-nøgle på fanen <a data-toggle='tab' href='#' class='settings'>Indstillinger</a>.";
$_['text_activate_payment_method'] = 'Aktiver denne betalingsmetode i dit <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie-dashboard</a>.';
$_['text_no_status_id']            = "- Opdater ikke ordrestatus (anbefales ikke) -";
$_['text_enable']                  = "Aktiver";
$_['text_disable']                 = "Deaktiver";
$_['text_connection_success']      = "Succes: Forbindelsen til Mollie lykkedes!";
$_['text_error']                   = "Advarsel: Noget gik galt. Prøv venligst igen senere!";
$_['text_creditcard_required']     = "Kræver kreditkort";
$_['text_mollie_api']              = "Mollie API";
$_['text_mollie_app']              = "Mollie App";
$_['text_general']                 = "Generelt";
$_['text_enquiry']                 = "Hvordan kan vi hjælpe dig?";
$_['text_enquiry_success']         = "Succes: Din forespørgsel er blevet sendt. Vi vender snart tilbage til dig. Tak!";
$_['text_update_message']          = 'Mollie: En ny version (%s) er tilgængelig. Klik <a href="%s">her</a> for at opdatere. Vil du ikke se denne besked igen? Klik på <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">her</a>.';
$_['text_update_message_warning']  = 'Mollie: En ny version (%s) er tilgængelig. Opdater venligst din PHP-version til %s eller højere for at opdatere modulet eller fortsæt med at bruge den nuværende version. Vil du ikke se denne besked igen? Klik på <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">her</a>.';
$_['text_update_success']          = "Succes: Mollie-modulet er blevet opdateret til version %s.";
$_['text_default_currency']        = "Valuta brugt i butikken";
$_['text_custom_css']              = "Tilpasset CSS til Mollie-komponenter";
$_['text_contact_us']              = "Kontakt os - Teknisk support";
$_['text_bg_color']                = "Baggrundsfarve";
$_['text_color']                   = "Farve";
$_['text_font_size']               = "Skriftstørrelse";
$_['text_other_css']               = "Anden CSS";
$_['text_module_by']               = "Modul efter Quality Works - Teknisk support";
$_['text_mollie_support']          = "Mollie - Support";
$_['text_contact']                 = "Kontakt";
$_['text_allowed_variables']       = "Tilladte variabler: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Gennemse';
$_['text_clear']                   = 'Ryd';
$_['text_image_manager']           = 'Billedhåndtering';
$_['text_left']                    = 'Venstre';
$_['text_right']                   = 'Højre';
$_['text_more']                    = 'Mere';
$_['text_no_maximum_limit']        = 'Ingen maksimumbeløbsgrænse';
$_['text_standard_total']          = 'Standard Total: %s';
$_['text_advance_option']          = 'Avancerede indstillinger for %s';
$_['text_payment_api']             = 'Betalings-API';
$_['text_order_api']               = 'Ordre API';
$_['text_info_orders_api']         = 'Hvorfor bruge Orders API?';
$_['text_pay_link_variables']      = "Tilladte variabler: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Tilbagevendende betaling";
$_['text_payment_link']            = "Betalingslink";
$_['text_coming_soon']             = "Kommer snart";

// Entry
$_['entry_payment_method']         = "Betalingsmetode";
$_['entry_activate']               = "Aktiver";
$_['entry_sort_order']             = "Sorteringsrækkefølge";
$_['entry_api_key']                = "API-nøgle";
$_['entry_description']            = "Beskrivelse";
$_['entry_show_icons']             = "Vis ikoner";
$_['entry_show_order_canceled_page'] = "Vis besked hvis betalingen annulleres";
$_['entry_geo_zone']               = "Geozone";
$_['entry_client_id']              = "Kunde-id";
$_['entry_client_secret']          = "Kundens hemmelighed";
$_['entry_redirect_uri']           = "Omdiriger URI";
$_['entry_payment_screen_language'] = "Standardsprog for betalingsskærmen";
$_['entry_mollie_connect']         = "Mollie forbinder";
$_['entry_name']                   = "Navn";
$_['entry_email']                  = "E-mail";
$_['entry_subject']                = "Emne";
$_['entry_enquiry']                = "Forespørgsel";
$_['entry_debug_mode']             = "Fejlretningstilstand";
$_['entry_mollie_component']       = "Mollie komponenter";
$_['entry_test_mode']              = "Testtilstand";
$_['entry_mollie_component_base']  = "Brugerdefineret CSS for Base input felt";
$_['entry_mollie_component_valid'] = "Tilpasset CSS for gyldigt inputfelt";
$_['entry_mollie_component_invalid'] = "Tilpasset CSS for ugyldigt inputfelt";
$_['entry_default_currency']       = "Betal altid med";
$_['entry_email_subject']          = "Emne";
$_['entry_email_body']             = "Brødtekst";
$_['entry_title']                  = "Titel";
$_['entry_image']                  = "Billede";
$_['entry_status']                 = "Status";
$_['entry_align_icons']            = "Juster ikoner";
$_['entry_single_click_payment']   = "Enkelt klik betaling";
$_['entry_order_expiry_days']      = "Ordreudløbsdage";
$_['entry_partial_refund']         = "Delvis tilbagebetaling";
$_['entry_amount']                 = "Beløb (eksempel: 5 eller 5%)";
$_['entry_payment_fee']            = "Betalingsgebyr";
$_['entry_payment_fee_tax_class']  = "Betalingsgebyr Skatteklasse";
$_['entry_total']                  = "I alt";
$_['entry_minimum']                = "Minimum";
$_['entry_maximum']                = "Maksimum";
$_['entry_api_to_use']             = "API til brug";
$_['entry_payment_link']  		     = "Send betalingslink";
$_['entry_payment_link_sep_email']   = "Send en separat e-mail";
$_['entry_payment_link_ord_email']   = "Send en ordrebekræftelse e-mail";
$_['entry_partial_credit_order']     = 'Opret kreditordre på (delvis) tilbagebetaling';

// Help
$_['help_view_profile']            = 'Du kan finde din API-nøgle i <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link" >dine Mollie hjemmeside-profiler</a>.';
$_['help_status']                  = "Aktiver modulet";
$_['help_api_key']                 = 'Indtast <code>api_key</code> for den webstedsprofil, du vil bruge. API-nøglen starter med <code>test</code> eller <code>live_</code>.';
$_['help_description']             = 'Denne beskrivelse vil fremgå af din kundes bank-/kortudtog. Du må maksimalt bruge 29 tegn. TIP: Brug <code>%</code>, dette vil blive erstattet af ordre-id\'et for betalingen. Glem ikke at <code>%</code> kan bestå af flere tegn!';
$_['help_show_icons']              = 'Vis ikoner ved siden af ​​Mollie-betalingsmetoderne på betalingssiden.';
$_['help_show_order_canceled_page'] = 'Vis en besked til kunden, hvis en betaling annulleres, før kunden omdirigeres tilbage til deres indkøbskurv.';
$_['help_redirect_uri']            = 'Omdiriger URI i dit mollie dashboard skal matche med denne URI.';
$_['help_mollie_app']              = 'Ved at registrere dit modul som en App på Mollie-dashboardet, vil du låse op for tilføjede funktioner. Dette er ikke påkrævet for at bruge Mollie-betalinger.';
$_['help_apple_pay']               = 'Apple Pay kræver, at kreditkort er aktiveret på din hjemmesideprofil. Aktiver venligst kreditkortmetoden først.';
$_['help_mollie_component']        = 'Mollie-komponenter giver dig mulighed for at vise de felter, der er nødvendige for kreditkortholderdata til din egen kasse.';
$_['help_single_click_payment']    = 'Gør det muligt for dine kunder at debitere et tidligere brugt kreditkort med et enkelt klik.';
$_['help_total']                   = 'Kassen minimum og maksimum beløb, før denne betalingsmetode bliver aktiv.';
$_['help_payment_link']				= 'Mens du opretter ordrer fra admin, vil en <strong>Mollie Payment Link</strong>-metode være tilgængelig til at sende betalingslink til kunden til betaling. Du kan indstille e-mail-tekst under fanen e-mail.';

// Info
$_['entry_module']      = "Modul";
$_['entry_mod_status']  = "Modulstatus";
$_['entry_comm_status'] = "Kommunikationsstatus";
$_['entry_support']     = "Support";
$_['entry_version']     = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']            = "Advarsel: Du har ikke tilladelse til at ændre Mollie betalingsmetoder.";
$_['error_api_key']               = "Mollie API nøgle er påkrævet!";
$_['error_api_key_invalid']       = "Ugyldig API-nøgle!";
$_['error_description']           = "Beskrivelse er påkrævet!";
$_['error_file_missing']          = "Filen findes ikke";
$_['error_name']                  = 'Advarsel: Navnet skal være mellem 3 og 25 tegn!';
$_['error_email']                 = 'Advarsel: E-mail-adressen ser ikke ud til at være gyldig!';
$_['error_subject']               = 'Advarsel: Emnet skal være på 3 tegn!';
$_['error_enquiry']               = 'Advarsel: Forespørgselstekst skal være på 25 tegn!';
$_['error_no_api_client']         = 'API-klient ikke fundet.';
$_['error_api_help']              = 'Du kan bede din hostingudbyder om at hjælpe med dette.';
$_['error_comm_failed']           = '<strong>Kommunikation med Mollie mislykkedes:</strong><br/>%s<br/><br/>Kontroller venligst følgende betingelser. Du kan bede din hostingudbyder om at hjælpe med dette.<ul><li>Sørg for, at eksterne forbindelser til %s ikke er blokeret.</li><li>Sørg for, at SSL v3 er deaktiveret på din server. Mollie understøtter ikke SSL v3.</li><li>Sørg for, at din server er opdateret, og at de seneste sikkerhedsrettelser er installeret.</li></ul><br/>Kontakt <a href= "mailto:info@mollie.nl">info@mollie.nl</a> hvis dette stadig ikke løser dit problem.';
$_['error_no_api_key']            = 'Ingen API-nøgle angivet. Indsæt venligst din API nøgle.';
$_['error_order_expiry_days']     = 'Advarsel: Det er ikke muligt at bruge Klarna Slice it eller Klarna Pay senere som metode, når udløbsdatoen er mere end 28 dage ude i fremtiden.';
$_['error_mollie_payment_fee']    = 'Advarsel: Mollie Betalingsgebyr ordre totalt er deaktiveret!';
$_['error_min_php_version']       = 'Advarsel: Dette Mollie-modul skal bruge PHP-version %s eller højere for at fungere. Kontakt venligst din webudvikler for at løse dette problem!';

// Status
$_['entry_pending_status']        = "Status for oprettet betaling";
$_['entry_failed_status']         = "Betaling mislykkedes status";
$_['entry_canceled_status']       = "Status for annulleret betaling";
$_['entry_expired_status']        = "Betalingens udløbsstatus";
$_['entry_processing_status']     = "Betalingen lykkedes";
$_['entry_refund_status']         = "Betalingsrefusionsstatus";
$_['entry_partial_refund_status'] = "Delvis tilbagebetalingsstatus";
$_['entry_shipping_status']       = "Ordre afsendt status";
$_['entry_shipment']              = "Opret forsendelse";
$_['entry_create_shipment_status'] = "Opret forsendelse efter ordrestatus";
$_['help_shipment']               = "Forsendelse vil blive oprettet lige efter oprettelse af ordre. Vælg 'Nej' for at oprette forsendelse, når ordren når en specifik status og vælg ordrestatus nedefra.";

$_['text_create_shipment_automatically']      = "Opret forsendelse automatisk ved oprettelse af ordre";
$_['text_create_shipment_on_status']          = "Opret forsendelse ved at sætte ordren til denne status";
$_['text_create_shipment_on_order_complete']  = "Opret forsendelse ved at indstille ordren for at bestille komplet status";
$_['entry_create_shipment_on_order_complete'] = "Opret forsendelse, når ordren er fuldført";

// Button
$_['button_update']         = "Opdater";
$_['button_mollie_connect'] = "Opret forbindelse via Mollie";
$_['button_advance_option'] = "Avanceret valgmulighed";
$_['button_save_close']     = "Gem og luk";

// Error Log
$_['text_log_success']  = 'Succes: Du har ryddet din mollie log!';
$_['text_log_list']     = 'Log';
$_['error_log_warning'] = 'Advarsel: Din mollie-logfil %s er %s!';
$_['button_download']   = 'Download';

// Summernote
$_['summernote']                    = 'da-DK';
