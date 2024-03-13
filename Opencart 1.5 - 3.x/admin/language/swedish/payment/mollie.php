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
$_['text_mollie_przelewy24']  	 = $method_list_logo;
$_['text_mollie_applepay']  	 = $method_list_logo;
$_['text_mollie_voucher']    	 = $method_list_logo;
$_['text_mollie_in3']      	     = $method_list_logo;
$_['text_mollie_mybank']      	 = $method_list_logo;
$_['text_mollie_billie']      	 = $method_list_logo;
$_['text_mollie_klarna']      	 = $method_list_logo;
$_['text_mollie_twint']      	 = $method_list_logo;
$_['text_mollie_blik']      	 = $method_list_logo;
$_['text_mollie_bancomatpay']    = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "inställningar";
$_['title_payment_status']    = "Betalningsstatus";
$_['title_mod_about']         = "Om denna modul";
$_['footer_text']             = "Betaltjänster";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Banköverföring";
$_['name_mollie_belfius']        = "Belfius Direct Net";
$_['name_mollie_creditcard']     = "Creditcard";
$_['name_mollie_ideal']          = "iDEAL";
$_['name_mollie_kbc']            = "KBC/CBC Payment Button";
$_['name_mollie_bancontact']     = "Bancontact";
$_['name_mollie_paypal']         = "PayPal";
$_['name_mollie_paysafecard']    = "paysafecard";
$_['name_mollie_sofort']         = "SOFORT Banking";
$_['name_mollie_giftcard']       = 'Giftcard';
$_['name_mollie_eps']            = 'EPS';
$_['name_mollie_giropay']        = 'Giropay';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnapaynow']   = 'Klarna Pay Now';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in3']            = "IN3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";
$_['name_mollie_twint']          = "Twint";
$_['name_mollie_blik']           = "Blik";
$_['name_mollie_bancomatpay']    = "Bancomat Pay";

// Text
$_['text_edit']                    = "Redigera";
$_['text_payment']                 = "Betalning";
$_['text_success']                 = "Framgång: Du har lyckats ändra dina Mollie-inställningar!";
$_['text_missing_api_key']         = "Fyll i din API-nyckel på fliken <a data-toggle='tab' href='#' class='settings'>Inställningar</a>.";
$_['text_activate_payment_method'] = 'Aktivera den här betalningsmetoden i din <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">Mollie-instrumentpanel</a>.';
$_['text_no_status_id']            = "- Uppdatera inte beställningsstatus (rekommenderas inte) -";
$_['text_enable']                  = "Gör det möjligt";
$_['text_disable']                 = "Inaktivera";
$_['text_connection_success']      = "Framgång: Anslutningen till Mollie lyckad!";
$_['text_error']                   = "Varning: Något gick fel. Försök igen senare!";
$_['text_creditcard_required']     = "Kräver kreditkort";
$_['text_mollie_api']              = "Mollie API";
$_['text_mollie_app']              = "Mollie-appen";
$_['text_general']                 = "Allmänt";
$_['text_enquiry']                 = "Hur kan vi hjälpa dig?";
$_['text_enquiry_success']         = "Framgång: Din förfrågan har skickats. Vi återkommer snart. Tack!";
$_['text_update_message']          = 'Mollie: En ny version (%s) är tillgänglig. Klicka <a href="%s">här</a> för att uppdatera. Vill du inte se det här meddelandet igen? Klicka på <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">här</a>.';
$_['text_update_message_warning']  = 'Mollie: En ny version (%s) är tillgänglig. Uppdatera din PHP-version till %s eller högre för att uppdatera modulen eller fortsätt att använda den nuvarande versionen. Vill du inte se det här meddelandet igen? Klicka på <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">här</a>.';
$_['text_update_success']          = "Framgång: Mollie-modulen har uppdaterats till version %s.";
$_['text_default_currency']        = "Valutan som används i butiken";
$_['text_custom_css']              = "Anpassad CSS för Mollie-komponenter";
$_['text_contact_us']              = "Kontakta oss - Teknisk support";
$_['text_bg_color']                = "Bakgrundsfärg";
$_['text_color']                   = "Färg";
$_['text_font_size']               = "Teckenstorlek";
$_['text_other_css']               = "Annan CSS";
$_['text_module_by']               = "Modul efter Quality Works - Teknisk support";
$_['text_mollie_support']          = "Mollie - Support";
$_['text_contact']                 = "Kontakt";
$_['text_allowed_variables']       = "Tillåtna variabler: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Bläddra';
$_['text_clear']                   = 'Rensa';
$_['text_image_manager']           = 'Bildhanterare';
$_['text_left']                    = 'Vänster';
$_['text_right']                   = 'Höger';
$_['text_more']                    = 'Mer';
$_['text_no_maximum_limit']        = 'Ingen maxbeloppsgräns';
$_['text_standard_total']          = 'Standardtotal: %s';
$_['text_advance_option']          = 'Avancerade alternativ för %s';
$_['text_payment_api']             = 'Betalnings-API';
$_['text_order_api']               = 'Beställningar API';
$_['text_info_orders_api']         = 'Varför använda Orders API?';
$_['text_pay_link_variables']      = "Tillåtna variabler: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Återkommande betalning";
$_['text_payment_link']            = "Betalningslänk";
$_['text_coming_soon']             = "Kommer snart";

// Entry
$_['entry_payment_method']         = "Betalningsmetod";
$_['entry_activate']               = "Aktivera";
$_['entry_sort_order']             = "Sorteringsordning";
$_['entry_api_key']                = "API-nyckel";
$_['entry_description']            = "Beskrivning";
$_['entry_show_icons']             = "Visa ikoner";
$_['entry_show_order_canceled_page'] = "Visa meddelande om betalningen avbryts";
$_['entry_geo_zone']               = "Geozon";
$_['entry_client_id']              = "Kund-ID";
$_['entry_client_secret']          = "Kundens hemlighet";
$_['entry_redirect_uri']           = "Omdirigera URI";
$_['entry_payment_screen_language'] = "Standardspråk för betalningsskärmen";
$_['entry_mollie_connect']         = "Mollie connect";
$_['entry_name']                   = "Namn";
$_['entry_email']                  = "E-mail";
$_['entry_subject']                = "Ämne";
$_['entry_enquiry']                = "Förfrågan";
$_['entry_debug_mode']             = "Felsökningsläge";
$_['entry_mollie_component']       = "Mollie komponenter";
$_['entry_test_mode']              = "Testläge";
$_['entry_mollie_component_base']  = "Anpassad CSS för basinmatningsfält";
$_['entry_mollie_component_valid'] = "Anpassad CSS för giltigt inmatningsfält";
$_['entry_mollie_component_invalid'] = "Anpassad CSS för ogiltigt inmatningsfält";
$_['entry_default_currency']       = "Betala alltid med";
$_['entry_email_subject']          = "Ämne";
$_['entry_email_body']             = "Bräck";
$_['entry_title']                  = "Titel";
$_['entry_image']                  = "Bild";
$_['entry_status']                 = "Status";
$_['entry_align_icons']            = "Justera ikoner";
$_['entry_single_click_payment']   = "Enkelklicksbetalning";
$_['entry_order_expiry_days']      = "Beställningens utgångsdagar";
$_['entry_partial_refund']         = "Delvis återbetalning";
$_['entry_amount']                 = "Belopp (exempel: 5 eller 5%)";
$_['entry_payment_fee']            = "Betalningsavgift";
$_['entry_payment_fee_tax_class']  = "Betalningsavgiftsskattklass";
$_['entry_total']                  = "Totalt";
$_['entry_minimum']                = "Minimum";
$_['entry_maximum']                = "Maximal";
$_['entry_api_to_use']             = "API att använda";
$_['entry_payment_link']  		     = "Skicka betalningslänk";
$_['entry_payment_link_sep_email']   = "Skicka ett separat mejl";
$_['entry_payment_link_ord_email']   = "Skicka in orderbekräftelsemail";
$_['entry_partial_credit_order']     = 'Skapa kreditorder på (del) återbetalning';

// Help
$_['help_view_profile']            = 'Du kan hitta din API-nyckel i <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link" >dina profiler på Mollie-webbplatsen</a>.';
$_['help_status']                  = "Aktivera modulen";
$_['help_api_key']                 = 'Ange <code>api_key</code> för webbplatsprofilen du vill använda. API-nyckeln börjar med <code>test_</code> eller <code>live_</code>.';
$_['help_description']             = 'Denna beskrivning kommer att visas på din kunds bank-/kortutdrag. Du får använda max 29 tecken. TIPS: Använd <code>%</code>, detta kommer att ersättas av betalningens order-id. Glöm inte att <code>%</code> kan vara flera tecken långt!';
$_['help_show_icons']              = 'Visa ikoner bredvid Mollie betalningsmetoder på kassasidan.';
$_['help_show_order_canceled_page'] = 'Visa ett meddelande till kunden om en betalning avbryts, innan du omdirigerar kunden tillbaka till sin kundvagn.';
$_['help_redirect_uri']            = 'Omdirigera URI i din mollie instrumentpanel måste matcha med denna URI.';
$_['help_mollie_app']              = 'Genom att registrera din modul som en app på Mollie-instrumentpanelen låser du upp ytterligare funktioner. Detta krävs inte för att använda Mollie-betalningar.';
$_['help_apple_pay']               = 'Apple Pay kräver att kreditkort är aktiverat på din webbplatsprofil. Vänligen aktivera kreditkortsmetoden först.';
$_['help_mollie_component']        = 'Mollie-komponenter låter dig visa fält som behövs för kreditkortsinnehavarens data till din egen kassa.';
$_['help_single_click_payment']    = 'Möjliggör dina kunder att debitera ett tidigare använt kreditkort med ett enda klick.';
$_['help_total'] = 'Lägsta och högsta belopp för kassan innan denna betalningsmetod blir aktiv.';
$_['help_payment_link']				= 'När du skapar beställningar från admin kommer en <strong>Mollie Payment Link</strong>-metod att vara tillgänglig för att skicka betalningslänk till kunden för betalning. Du kan ställa in e-posttext under e-postfliken.';

// Info
$_['entry_module']      = "Modul";
$_['entry_mod_status']  = "Modulstatus";
$_['entry_comm_status'] = "Kommunikationsstatus";
$_['entry_support']     = "Support";
$_['entry_version']     = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']             = "Varning: Du har inte behörighet att ändra Mollie betalningsmetoder.";
$_['error_api_key']                = "Mollie API-nyckel krävs!";
$_['error_api_key_invalid']        = "Ogiltig API-nyckel!";
$_['error_description']            = "Beskrivning krävs!";
$_['error_file_missing']           = "Filen finns inte";
$_['error_name']                   = 'Varning: Namnet måste vara mellan 3 och 25 tecken!';
$_['error_email']                  = 'Varning: E-postadressen verkar inte vara giltig!';
$_['error_subject']                = 'Varning: Ämnet måste vara 3 tecken långt!';
$_['error_enquiry']                = 'Varning: Förfrågans text måste vara 25 tecken lång!';
$_['error_no_api_client']          = 'API-klienten hittades inte.';
$_['error_api_help']               = 'Du kan be din värdleverantör att hjälpa till med detta.';
$_['error_comm_failed']            = '<strong>Kommunikationen med Mollie misslyckades:</strong><br/>%s<br/><br/>Kontrollera följande villkor. Du kan be din värdleverantör att hjälpa till med detta.<ul><li>Se till att externa anslutningar till %s inte är blockerade.</li><li>Se till att SSL v3 är inaktiverat på din server. Mollie stöder inte SSL v3.</li><li>Se till att din server är uppdaterad och att de senaste säkerhetskorrigeringarna har installerats.</li></ul><br/>Kontakta <a href= "mailto:info@mollie.nl">info@mollie.nl</a> om detta fortfarande inte löser ditt problem.';
$_['error_no_api_key']             = 'Ingen API-nyckel tillhandahålls. Vänligen infoga din API-nyckel.';
$_['error_order_expiry_days']      = 'Varning: Det är inte möjligt att använda Klarna Slice it eller Klarna Pay senare som metod när utgångsdatumet är mer än 28 dagar i framtiden.';
$_['error_mollie_payment_fee']     = 'Varning: Mollie Betalningsavgift ordersumman är inaktiverad!';
$_['error_min_php_version']        = 'Varning: Denna Mollie-modul behöver PHP-version %s eller högre för att fungera. Kontakta din webbutvecklare för att åtgärda det här problemet!';

// Status
$_['entry_pending_status']         = "Betalning skapad status";
$_['entry_failed_status']          = "Betalningen misslyckades status";
$_['entry_canceled_status']        = "Status avbruten betalning";
$_['entry_expired_status']         = "Betalningen har upphört att gälla";
$_['entry_processing_status']      = "Betalningen lyckad status";
$_['entry_refund_status']          = "Betalningsåterbetalningsstatus";
$_['entry_partial_refund_status']  = "Status för partiell återbetalning";
$_['entry_shipping_status']        = "Beställning skickad status";
$_['entry_shipment']               = "Skapa försändelse";
$_['entry_create_shipment_status'] = "Skapa leverans efter orderstatus";
$_['help_shipment']                = "Försändelsen kommer att skapas direkt efter att beställningen skapats. Välj 'Nej' för att skapa leverans när beställningen når en specifik status och välj beställningsstatus nedan.";

$_['text_create_shipment_automatically']      = "Skapa leverans automatiskt när beställning skapas";
$_['text_create_shipment_on_status']          = "Skapa leverans när beställningen ställs in på denna status";
$_['text_create_shipment_on_order_complete']  = "Skapa försändelse vid beställning för att beställa fullständig status";
$_['entry_create_shipment_on_order_complete'] = "Skapa leverans när beställningen är klar";

// Button
$_['button_update']         = "Uppdatera";
$_['button_mollie_connect'] = "Anslut via Mollie";
$_['button_advance_option'] = "Avancerat alternativ";
$_['button_save_close']     = "Spara och stäng";

// Error log
$_['text_log_success']  = 'Framgång: Du har rensat din mollie-logg!';
$_['text_log_list']     = 'Logg';
$_['error_log_warning'] = 'Varning: Din mollie-loggfil %s är %s!';
$_['button_download']   = 'Ladda ner';

// Summernote
$_['summernote']                    = 'sv-SE';
