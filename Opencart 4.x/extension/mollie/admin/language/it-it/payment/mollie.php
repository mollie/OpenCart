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

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Impostazioni";
$_['title_payment_status']    = "Stati di pagamento";
$_['title_mod_about']         = "A proposito di questo modulo";
$_['footer_text']             = "Servizi di pagamento";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Trasferimento bancario";
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
$_['name_mollie_przelewy_24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in_3']           = "IN3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";

// Text
$_['text_edit']                    = "Modificare";
$_['text_payment']                 = "Pagamento";
$_['text_success']                 = "Successo: hai modificato con successo le impostazioni di Mollie!";
$_['text_missing_api_key']         = "Compila la tua chiave API nella scheda <a data-toggle='tab' href='javascript:void(0);' class='settings'>Impostazioni</a>.";
$_['text_activate_payment_method'] = 'Abilita questo metodo di pagamento nella tua <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">dashboard Mollie</a>.';
$_['text_no_status_id']            = "- Non aggiornare lo stato dell'ordine (non consigliato) -";
$_['text_enable']                  = "Abilitare";
$_['text_disable']                 = "Disabilita";
$_['text_connection_success']      = "Successo: Connessione a Mollie riuscita!";
$_['text_error']                   = "Attenzione: qualcosa è andato storto. Riprova più tardi!";
$_['text_creditcard_required']     = "Richiede carta di credito";
$_['text_mollie_api']              = "API Mollie";
$_['text_mollie_app']              = "App Mollie";
$_['text_general']                 = "Generale";
$_['text_enquiry']                 = "Come possiamo aiutarti?";
$_['text_enquiry_success']         = "Successo: la tua richiesta è stata inviata. Ti ricontatteremo presto. Grazie!";
$_['text_update_message']          = 'Mollie: è disponibile una nuova versione (%s). Fai clic <a href="%s">qui</a> per aggiornare. Non vuoi più vedere questo messaggio? Fare clic su <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">qui</a>.';
$_['text_update_message_warning']  = 'Mollie: è disponibile una nuova versione (%s). Aggiorna la tua versione PHP a %s o superiore per aggiornare il modulo o continua a utilizzare la versione corrente. Non vuoi più vedere questo messaggio? Fare clic su <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">qui</a>.';
$_['text_update_success']          = "Successo: il modulo Mollie è stato aggiornato alla versione %s.";
$_['text_default_currency']        = "Valuta utilizzata nel negozio";
$_['text_custom_css']              = "CSS personalizzato per componenti Mollie";
$_['text_contact_us']              = "Contattaci - Assistenza tecnica";
$_['text_bg_color']                = "Colore di sfondo";
$_['text_color']                   = "Colore";
$_['text_font_size']               = "Dimensione carattere";
$_['text_other_css']               = "Altri CSS";
$_['text_module_by']               = "Modulo di Quality Works - Supporto tecnico";
$_['text_mollie_support']          = "Mollie - Supporto";
$_['text_contact']                 = "Contatto";
$_['text_allowed_variables']       = "Variabili consentite: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Sfoglia';
$_['text_clear']                   = 'Cancella';
$_['text_image_manager']           = 'Gestione immagini';
$_['text_left']                    = 'Sinistra';
$_['text_right']                   = 'Destra';
$_['text_more']                    = 'Altro';
$_['text_no_maximum_limit']        = 'Nessun limite di importo massimo';
$_['text_standard_total']          = 'Totale standard: %s';
$_['text_advance_option']          = 'Opzioni avanzate per %s';
$_['text_payment_api']             = 'API Pagamenti';
$_['text_order_api']               = 'API degli ordini';
$_['text_info_orders_api']         = 'Perché usare l\'API degli ordini?';
$_['text_pay_link_variables']      = "Variabili consentite: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Pagamenti ricorrenti";
$_['text_payment_link']            = "Collegamento di pagamento";

// Entry
$_['entry_payment_method']         = "Metodo di pagamento";
$_['entry_activate']               = "Attiva";
$_['entry_sort_order']             = "Ordina l'ordine";
$_['entry_api_key']                = "Chiave API";
$_['entry_description']            = "Descrizione";
$_['entry_show_icons']             = "Mostra icone";
$_['entry_show_order_canceled_page'] = "Mostra messaggio se il pagamento viene annullato";
$_['entry_geo_zone']               = "Zona Geo";
$_['entry_client_id']              = "ID cliente";
$_['entry_client_secret']          = "Segreto cliente";
$_['entry_redirect_uri']           = "Reindirizza URI";
$_['entry_payment_screen_language'] = "Lingua predefinita della schermata di pagamento";
$_['entry_mollie_connect']         = "Mollie si connette";
$_['entry_name']                   = "Nome";
$_['entry_email']                  = "E-mail";
$_['entry_subject']                = "Oggetto";
$_['entry_enquiry']                = "Richiesta";
$_['entry_debug_mode']             = "Modalità debug";
$_['entry_mollie_component']       = "Componenti Mollie";
$_['entry_test_mode'] = "Modalità di prova";
$_['entry_mollie_component_base']  = "CSS personalizzato per campo di immissione Base";
$_['entry_mollie_component_valid'] = "CSS personalizzato per campo di input valido";
$_['entry_mollie_component_invalid'] = "CSS personalizzato per campo di input non valido";
$_['entry_default_currency']       = "Paga sempre con";
$_['entry_email_subject']          = "Oggetto";
$_['entry_email_body']             = "Corpo";
$_['entry_title']                  = "Titolo";
$_['entry_image']                  = "Immagine";
$_['entry_status']                 = "Stato";
$_['entry_align_icons']            = "Allinea icone";
$_['entry_single_click_payment']   = "Pagamento con un clic";
$_['entry_order_expiry_days']      = "Giorni di scadenza dell'ordine";
$_['entry_partial_refund']         = "Rimborso parziale";
$_['entry_amount']                 = "Importo (esempio: 5 o 5%)";
$_['entry_payment_fee']            = "Commissione di pagamento";
$_['entry_payment_fee_tax_class']  = "Classe di imposta della commissione di pagamento";
$_['entry_total']                  = "Totale";
$_['entry_minimum']                = "Minimo";
$_['entry_maximum']                = "Massimo";
$_['entry_api_to_use']             = "API da utilizzare";
$_['entry_payment_link']  		     = "Invia link di pagamento";
$_['entry_payment_link_sep_email']   = "Invia in un'e-mail separata";
$_['entry_payment_link_ord_email']   = "Invia un'e-mail di conferma dell'ordine";
$_['entry_partial_credit_order']     = 'Creare un ordine di credito sul rimborso (parziale)';


// Help
$_['help_view_profile']            = 'Puoi trovare la tua chiave API in <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link" >i profili del tuo sito web Mollie</a>.';
$_['help_status']                  = "Attiva il modulo";
$_['help_api_key']                 = 'Inserisci il <code>api_key</code> del profilo del sito web che vuoi usare. La chiave API inizia con <code>test_</code> o <code>live_</code>.';
$_['help_description']             = 'Questa descrizione apparirà sull\'estratto conto della banca/della carta del tuo cliente. Puoi utilizzare un massimo di 29 caratteri. SUGGERIMENTO: Usa <code>%</code>, questo sarà sostituito dall\'ID ordine del pagamento. Non dimenticare che <code>%</code> può contenere più caratteri!';
$_['help_show_icons']              = 'Mostra icone accanto ai metodi di pagamento Mollie nella pagina di pagamento.';
$_['help_show_order_canceled_page'] = 'Mostra un messaggio al cliente se un pagamento viene annullato, prima di reindirizzare il cliente al carrello.';
$_['help_redirect_uri']            = 'L\'URI di reindirizzamento nella dashboard di Mollie deve corrispondere a questo URI.';
$_['help_mollie_app']              = 'Registrando il tuo modulo come App nella dashboard di Mollie, sbloccherai funzionalità aggiuntive. Questo non è necessario per utilizzare i pagamenti Mollie.';
$_['help_apple_pay']               = 'Apple Pay richiede che la carta di credito sia abilitata sul profilo del tuo sito web. Si prega di abilitare prima il metodo della carta di credito.';
$_['help_mollie_component']        = 'I componenti Mollie ti consentono di mostrare i campi necessari per i dati del titolare della carta di credito alla tua cassa.';
$_['help_single_click_payment']    = 'Consenti ai tuoi clienti di addebitare una carta di credito utilizzata in precedenza con un solo clic.';
$_['help_total']                   = 'L\'importo minimo e massimo del checkout prima che questo metodo di pagamento diventi attivo.';
$_['help_payment_link']				= 'Durante la creazione degli ordini dall\'amministratore, sarà disponibile un metodo <strong>Mollie Payment Link</strong> per inviare il link di pagamento al cliente per il pagamento. È possibile impostare il testo dell\'e-mail nella scheda e-mail.';

// Info
$_['entry_module']      = "Modulo";
$_['entry_mod_status']  = "Stato del modulo";
$_['entry_comm_status'] = "Stato comunicazione";
$_['entry_support']     = "Supporto";
$_['entry_version']     = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']      = "Attenzione: non sei autorizzato a modificare i metodi di pagamento Mollie.";
$_['error_api_key']         = "È richiesta la chiave API Mollie!";
$_['error_api_key_invalid'] = "Chiave API non valida!";
$_['error_description']     = "Descrizione richiesta!";
$_['error_file_missing']    = "Il file non esiste";
$_['error_name']            = 'Attenzione: il nome deve contenere da 3 a 25 caratteri!';
$_['error_email']           = 'Attenzione: l\'indirizzo e-mail non sembra essere valido!';
$_['error_subject']         = 'Attenzione: l\'oggetto deve essere lungo 3 caratteri!';
$_['error_enquiry']         = 'Attenzione: il testo della richiesta deve essere lungo 25 caratteri!';
$_['error_no_api_client']   = 'Client API non trovato.';
$_['error_api_help']        = 'Puoi chiedere aiuto al tuo provider di hosting.';
$_['error_comm_failed']     = '<strong>Comunicazione con Mollie non riuscita:</strong><br/>%s<br/><br/>Verifica le seguenti condizioni. Puoi chiedere al tuo provider di hosting di aiutarti.<ul><li>Assicurati che le connessioni esterne a %s non siano bloccate.</li><li>Assicurati che SSL v3 sia disabilitato sul tuo server. Mollie non supporta SSL v3.</li><li>Assicurati che il tuo server sia aggiornato e che siano state installate le ultime patch di sicurezza.</li></ul><br/>Contatta <a href= "mailto:info@mollie.nl">info@mollie.nl</a> se il problema persiste.';
$_['error_no_api_key']      = 'Nessuna chiave API fornita. Inserisci la tua chiave API.';
$_['error_order_expiry_days'] = 'Attenzione: Non è possibile utilizzare Klarna Slice it o Klarna Pay più tardi come metodo quando la data di scadenza è superiore a 28 giorni nel futuro.';
$_['error_mollie_payment_fee'] = 'Attenzione: il totale dell\'ordine della Commissione di pagamento Mollie è disabilitato!';
$_['error_file']               = 'Attenzione: impossibile trovare il file %s!';
$_['error_address']            = 'L\'indirizzo di fatturazione è disattivato, gli ordini digitali non potranno essere pagati. Puoi attivare l\'indirizzo di fatturazione nelle <a href="%s">impostazioni</a>.';

// Status
$_['entry_pending_status']        = "Stato pagamento creato";
$_['entry_failed_status']         = "Stato pagamento fallito";
$_['entry_canceled_status']       = "Stato pagamento annullato";
$_['entry_expired_status']        = "Stato pagamento scaduto";
$_['entry_processing_status']     = "Stato pagamento riuscito";
$_['entry_refund_status']         = "Stato rimborso pagamento";
$_['entry_partial_refund_status'] = "Stato di rimborso parziale";
$_['entry_shipping_status']       = "Stato dell'ordine spedito";
$_['entry_shipment']              = "Crea spedizione";
$_['entry_create_shipment_status'] = "Crea spedizione dopo lo stato dell'ordine";
$_['help_shipment']               = "La spedizione verrà creata subito dopo la creazione dell'ordine. Seleziona 'No' per creare la spedizione quando l'ordine raggiunge uno stato specifico e seleziona lo stato dell'ordine dal basso.";

$_['text_create_shipment_automatically']      = "Crea la spedizione automaticamente alla creazione dell'ordine";
$_['text_create_shipment_on_status']          = "Crea spedizione dopo aver impostato l'ordine su questo stato";
$_['text_create_shipment_on_order_complete']  = "Crea spedizione impostando l'ordine per completare lo stato dell'ordine";
$_['entry_create_shipment_on_order_complete'] = "Crea spedizione al completamento dell'ordine";

// Button
$_['button_update']         = "Aggiorna";
$_['button_mollie_connect'] = "Connetti tramite Mollie";
$_['button_advance_option'] = "Opzione anticipata";
$_['button_save_close']     = "Salva e chiudi";

// Error Log
$_['text_log_success']  = 'Successo: Hai cancellato con successo il tuo registro Mollie!';
$_['text_log_list']     = 'Registro';
$_['error_log_warning'] = 'Attenzione: il tuo file log di Mollie %s è %s!';
$_['button_download']   = 'Download';
