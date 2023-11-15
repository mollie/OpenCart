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
$_['heading_title']                     = 'Betaling af Mollie';
$_['ideal_title']                       = 'Din betaling';
$_['text_title']                        = 'Betal online';
$_['text_redirected']                   = 'Klienten er blevet henvist til betalingsskærmen';
$_['text_issuer_ideal']                 = 'Vælg din bank';
$_['text_issuer_giftcard']              = 'Vælg dit gavekort';
$_['text_issuer_kbc']                   = 'Vælg din betalingsknap.';
$_['text_issuer_voucher']               = 'Vælg dit brand.';
$_['text_card_details']                 = 'Indtast venligst dine kreditkortoplysninger.';
$_['text_mollie_payments']              = 'Sikkere betalinger leveret af %s';
$_['text_recurring_desc']               = 'Bestil %s, %s - %s, hver %s for %s';
$_['text_recurring']                    = '%s hver %s %s';
$_['text_length']                       = ' for %s betalinger';
$_['text_trial']                        = '%s hver %s %s for %s betalinger derefter ';
$_['text_error_report_success']         = 'Fejl er rapporteret med succes!';
$_['text_payment_link_title']	        = 'Mollie betalingslink';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	    = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']                         = 'For at se din ordre klik på linket nedenfor:';
$_['text_footer']                       = 'Besvar venligst denne e-mail, hvis du har spørgsmål.';
$_['text_payment_link_full_title']	    = 'Mollie betalingslink - Fuldt beløb';
$_['text_payment_link_open_title']	    = 'Mollie betalingslink - Åbent beløb';

// Button
$_['button_retry']  = 'Vend tilbage til betalingssiden';
$_['button_report'] = 'Rapportér fejl';
$_['button_submit'] = 'Send';

// Entry
$_['entry_card_holder'] = 'Kortindehaverens navn';
$_['entry_card_number'] = 'Kortnummer';
$_['entry_expiry_date'] = 'Udløbsdato';
$_['entry_verification_code'] = 'CVV';

// Error
$_['error_card']            = 'Kontroller venligst dine kortoplysninger.';
$_['error_missing_field']   = 'Manglende nødvendige oplysninger. Tjek venligst om grundlæggende adresseoplysninger er angivet.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']    = 'Din betaling er ikke blevet gennemført';
$_['msg_failed']        = 'Din betaling mislykkedes desværre. Klik venligst på knappen nedenfor for at vende tilbage til betalingssiden og prøve at oprette en betaling igen.';

// Status page: payment pending.
$_['heading_unknown']   = 'Din betaling afventer';
$_['msg_unknown']       = 'Din betaling er ikke modtaget endnu. Vi sender dig en bekræftelsesmail i det øjeblik, betalingen er modtaget.';

// Status page: API failure.
$_['heading_error'] = 'Der opstod en fejl under opsætning af betalingen';
$_['text_error']    = 'Der opstod en fejl under opsætning af betalingen med Mollie. Klik på knappen nedenfor for at vende tilbage til betalingssiden.';

// Payment link
$_['heading_payment_success'] = 'Betalingen er modtaget';
$_['text_payment_success'] = 'Din betaling er gennemført. Tak skal du have!';
$_['heading_payment_failed'] = 'Betalingen er ukendt';
$_['text_payment_failed'] = 'Din betaling er ikke modtaget endnu, eller betalingsstatus er ukendt. Vi giver dig besked i det øjeblik, betalingen er modtaget.';

// Response
$_['response_success']      = 'Betalingen er modtaget';
$_['response_none']         = 'Betalingen er ikke modtaget endnu';
$_['response_cancelled']    = 'Klienten har annulleret betalingen';
$_['response_failed']       = 'Desværre gik noget galt. Prøv venligst betalingen igen.';
$_['response_expired']      = 'Betalingen er udløbet';
$_['response_unknown']      = 'Der opstod en ukendt fejl';
$_['shipment_success']      = 'Forsendelse er oprettet';
$_['refund_cancelled']      = 'Refusion er blevet annulleret.';
$_['refund_success']        = 'Refusion er blevet behandlet med succes!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Kreditkort';
$_['method_bancontact']     = 'Bancontact';
$_['method_banktransfer']   = 'Bankoverførsel';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'KBC/CBC betalingsknap';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Gavekort';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna betal senere';
$_['method_klarnapaynow']   = 'Klarna Betal nu';
$_['method_klarnasliceit']  = 'Klarna skær det';
$_['method_przelewy24']     = 'P24';
$_['method_applepay']       = 'Apple Pay';
$_['method_voucher']        = 'Voucher';
$_['method_in3']            = 'IN3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Betal med Klarna';

//Round Off Description
$_['roundoff_description'] = 'Afrundingsforskel på grund af valutaomregning';

//Warning
$_['warning_secure_connection'] = 'Sørg for at du bruger en sikker forbindelse.';
