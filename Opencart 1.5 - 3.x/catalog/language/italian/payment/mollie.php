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

/**
 * English language file for iDEAL by Mollie
 */

// Text
$_['heading_title']                     = 'Pagamento da Mollie';
$_['ideal_title']                       = 'Il tuo pagamento';
$_['text_title']                        = 'Paga online';
$_['text_redirected']                   = 'Il cliente è stato indirizzato alla schermata di pagamento';
$_['text_issuer_ideal']                 = 'Seleziona la tua banca';
$_['text_issuer_giftcard']              = 'Seleziona la tua carta regalo';
$_['text_issuer_kbc']                   = 'Seleziona il tuo pulsante di pagamento.';
$_['text_issuer_voucher']               = 'Seleziona il tuo marchio.';
$_['text_card_details']                 = 'Inserisci i dettagli della tua carta di credito.';
$_['text_mollie_payments']              = 'Pagamenti sicuri forniti da %s';
$_['text_recurring_desc']               = 'Ordina %s, %s - %s, Ogni %s per %s';
$_['text_recurring']                    = '%s ogni %s %s';
$_['text_length']                       = 'per %s pagamenti';
$_['text_trial']                        = '%s ogni %s %s per %s pagamenti poi ';
$_['text_error_report_success']         = 'L\'errore è stato segnalato con successo!';
$_['text_payment_link_title']	        = 'Collegamento di pagamento Mollie';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	    = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']                         = 'Per visualizzare il tuo ordine clicca sul link qui sotto:';
$_['text_footer']                       = 'Per qualsiasi domanda, rispondi a questa email.';
$_['text_payment_link_full_title']	    = 'Collegamento di pagamento Mollie - Importo complessivo';
$_['text_payment_link_open_title']	    = 'Collegamento di pagamento Mollie - Importo aperto';

// Button
$_['button_retry']  = 'Torna alla pagina di pagamento';
$_['button_report'] = 'Segnala errore';
$_['button_submit'] = 'Invia';

// Entry
$_['entry_card_holder'] = 'Nome titolare della carta';
$_['entry_card_number'] = 'Numero carta';
$_['entry_expiry_date'] = 'Data di scadenza';
$_['entry_verification_code'] = 'CVV';

// Error
$_['error_card']            = 'Verifica i dettagli della tua carta.';
$_['error_missing_field']   = 'Informazioni richieste mancanti. Si prega di verificare se sono stati forniti i dettagli dell\'indirizzo di base.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']    = 'Il tuo pagamento non è stato completato';
$_['msg_failed']        = 'Purtroppo il tuo pagamento non è andato a buon fine. Fare clic sul pulsante in basso per tornare alla pagina di pagamento e riprovare a impostare un pagamento.';

// Status page: payment pending.
$_['heading_unknown']   = 'Il tuo pagamento è in sospeso';
$_['msg_unknown']       = 'Il tuo pagamento non è stato ancora ricevuto. Ti invieremo un\'e-mail di conferma nel momento in cui il pagamento sarà ricevuto.';

// Status page: API failure.
$_['heading_error'] = 'Si è verificato un errore durante l\'impostazione del pagamento';
$_['text_error']    = 'Si è verificato un errore durante l\'impostazione del pagamento con Mollie. Fare clic sul pulsante in basso per tornare alla pagina di pagamento.';

// Payment link
$_['heading_payment_success'] = 'Il pagamento è stato ricevuto';
$_['text_payment_success'] = 'Il tuo pagamento è stato completato con successo. Grazie!';
$_['heading_payment_failed'] = 'Il pagamento è sconosciuto';
$_['text_payment_failed'] = 'Il tuo pagamento non è stato ancora ricevuto o lo stato del pagamento è sconosciuto. Ti faremo sapere nel momento in cui il pagamento viene ricevuto.';

// Response
$_['response_success']      = 'Il pagamento è stato ricevuto';
$_['response_none']         = 'Il pagamento non è stato ancora ricevuto';
$_['response_cancelled']    = 'Il cliente ha annullato il pagamento';
$_['response_failed']       = 'Purtroppo qualcosa è andato storto. Si prega di riprovare il pagamento.';
$_['response_expired']      = 'Il pagamento è scaduto';
$_['response_unknown']      = 'Si è verificato un errore sconosciuto';
$_['shipment_success']      = 'La spedizione è stata creata';
$_['refund_cancelled']      = 'Il rimborso è stato annullato.';
$_['refund_success']        = 'Il rimborso è stato elaborato con successo!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Carta di credito';
$_['method_bancontact']     = 'Contatto ban';
$_['method_banktransfer']   = 'Trasferimento bancario';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'Pulsante di pagamento KBC/CBC';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Buono regalo';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna paga più tardi';
$_['method_klarnapaynow']   = 'Klarna paga ora';
$_['method_klarnasliceit']  = 'Klarna affettalo';
$_['method_przelewy24']     = 'P24';
$_['method_applepay']       = 'Apple Pay';
$_['method_voucher']        = 'Buono';
$_['method_in3']            = 'IN3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Paga con Klarna';
$_['method_twint']          = 'Twint';
$_['method_blik']           = 'Blik';
$_['method_bancomatpay']    = 'Bancomat Pay';

//Round Off Description
$_['roundoff_description'] = 'Differenza di arrotondamento dovuta alla conversione di valuta';

//Warning
$_['warning_secure_connection'] = 'Assicurati di utilizzare una connessione sicura.';
