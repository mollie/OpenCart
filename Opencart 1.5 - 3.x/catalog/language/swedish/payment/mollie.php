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
$_['heading_title']                     = 'Betalning av Mollie';
$_['ideal_title']                       = 'Din betalning';
$_['text_title']                        = 'Betala online';
$_['text_redirected']                   = 'Klienten har hänvisats till betalningsskärmen';
$_['text_issuer_giftcard']              = 'Välj ditt presentkort';
$_['text_issuer_kbc']                   = 'Välj din betalningsknapp.';
$_['text_issuer_voucher']               = 'Välj ditt varumärke.';
$_['text_card_details']                 = 'Ange dina kreditkortsuppgifter.';
$_['text_mollie_payments']              = 'Säker betalning från %s';
$_['text_recurring_desc']               = 'Beställ %s, %s - %s, varje %s för %s';
$_['text_recurring']                    = '%s varje %s %s';
$_['text_length']                       = ' för %s betalningar';
$_['text_trial']                        = '%s varje %s %s för %s betalningar sedan ';
$_['text_error_report_success']         = 'Fel har rapporterats framgångsrikt!';
$_['text_payment_link_title']	        = 'Mollie betalningslänk';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	    = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']                         = 'För att se din beställning klicka på länken nedan:';
$_['text_footer']                       = 'Vänligen svara på det här e-postmeddelandet om du har några frågor.';
$_['text_payment_link_full_title']	    = 'Mollie betalningslänk - Hela beloppet';
$_['text_payment_link_open_title']	    = 'Mollie betalningslänk - Öppet belopp';

// Button
$_['button_retry']  = 'Återgå till kassasidan';
$_['button_report'] = 'Rapportera fel';
$_['button_submit'] = 'Skicka';

// Entry
$_['entry_card_holder']         = 'Kortinnehavarens namn';
$_['entry_card_number']         = 'Kortnummer';
$_['entry_expiry_date']         = 'Utgångsdatum';
$_['entry_verification_code']   = 'CVV';

// Error
$_['error_card']            = 'Kontrollera dina kortuppgifter.';
$_['error_missing_field']   = 'Nödvändig information saknas. Kontrollera om grundläggande adressuppgifter finns.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed'] = 'Din betalning har inte slutförts';
$_['msg_failed']     = 'Tyvärr har din betalning misslyckats. Vänligen klicka på knappen nedan för att gå tillbaka till kassasidan och försöka göra en betalning igen.';

// Status page: payment pending.
$_['heading_unknown']   = 'Din betalning väntar';
$_['msg_unknown']       = 'Din betalning har inte tagits emot ännu. Vi kommer att skicka ett bekräftelsemail till dig när betalningen tas emot.';

// Status page: API failure.
$_['heading_error'] = 'Ett fel uppstod när betalningen gjordes';
$_['text_error']    = 'Ett fel uppstod när du ställde in betalningen med Mollie. Klicka på knappen nedan för att återgå till kassasidan.';

// Payment link
$_['heading_payment_success'] = 'Betalningen har tagits emot';
$_['text_payment_success'] = 'Din betalning har slutförts. Tack!';
$_['heading_payment_failed'] = 'Betalningen är okänd';
$_['text_payment_failed'] = 'Din betalning har inte tagits emot ännu eller betalningsstatusen är okänd. Vi kommer att meddela dig när betalningen tas emot.';

// Response
$_['response_success']      = 'Betalningen har tagits emot';
$_['response_none']         = 'Betalningen har inte tagits emot ännu';
$_['response_cancelled']    = 'Klienten har avbrutit betalningen';
$_['response_failed']       = 'Tyvärr gick något fel. Försök betalningen igen.';
$_['response_expired']      = 'Betalningen har gått ut';
$_['response_unknown']      = 'Ett okänt fel inträffade';
$_['shipment_success']      = 'Sändningen är skapad';
$_['refund_cancelled']      = 'Återbetalning har avbrutits.';
$_['refund_success']        = 'Återbetalning har behandlats framgångsrikt!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Kreditkort';
$_['method_bancontact']     = 'Bankontakt';
$_['method_banktransfer']   = 'Banköverföring';
$_['method_belfius']        = 'Belfius direktnät';
$_['method_kbc']            = 'KBC/CBC-betalningsknapp';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Presentkort';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna betala senare';
$_['method_klarnapaynow']   = 'Klarna betala nu';
$_['method_klarnasliceit']  = 'Klarna Slice It';
$_['method_przelewy24']     = 'P24';
$_['method_applepay']       = 'Apple Pay';
$_['method_voucher']        = 'Kupong';
$_['method_in3']            = 'IN3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Betala med Klarna';
$_['method_twint']          = 'Twint';
$_['method_blik']           = 'Blik';
$_['method_bancomatpay']    = 'Bancomat Pay';

//Round Off Description
$_['roundoff_description'] = 'Avrundningsskillnad på grund av valutaomvandling';

//Warning
$_['warning_secure_connection'] = 'Se till att du använder en säker anslutning.';
