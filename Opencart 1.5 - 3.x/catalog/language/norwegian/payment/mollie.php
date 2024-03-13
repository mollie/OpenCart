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
$_['heading_title']                     = 'Betaling av Mollie';
$_['ideal_title']                       = 'Din betaling';
$_['text_title']                        = 'Betal online';
$_['text_redirected']                   = 'Klienten har blitt henvist til betalingsskjermen';
$_['text_issuer_ideal']                 = 'Velg din bank';
$_['text_issuer_giftcard']              = 'Velg gavekortet ditt';
$_['text_issuer_kbc']                   = 'Velg betalingsknappen.';
$_['text_issuer_voucher']               = 'Velg merkevaren din.';
$_['text_card_details']                 = 'Vennligst skriv inn kredittkortopplysningene dine.';
$_['text_mollie_payments']              = 'Sikkere betalinger levert av %s';
$_['text_recurring_desc']               = 'Bestill %s, %s - %s, hver %s for %s';
$_['text_recurring']                    = '%s hver %s %s';
$_['text_length']                       = ' for %s betalinger';
$_['text_trial']                        = '%s hver %s %s for %s betalinger deretter ';
$_['text_error_report_success']         = 'Feil har blitt rapportert!';
$_['text_payment_link_title']	        = 'Mollie betalingslenke';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	    = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']                         = 'For å se bestillingen din klikk på lenken nedenfor:';
$_['text_footer']                       = 'Svar på denne e-posten hvis du har spørsmål.';
$_['text_payment_link_full_title']	    = 'Mollie betalingslenke - Hele beløpet';
$_['text_payment_link_open_title']	    = 'Mollie betalingslenke - Åpent beløp';

// Button
$_['button_retry']  = 'Gå tilbake til betalingssiden';
$_['button_report'] = 'Rapporter feil';
$_['button_submit'] = 'Send inn';

// Entry
$_['entry_card_holder'] = 'Kortinnehavers navn';
$_['entry_card_number'] = 'Kortnummer';
$_['entry_expiry_date'] = 'Utløpsdato';
$_['entry_verification_code'] = 'CVV';

// Error
$_['error_card']            = 'Vennligst sjekk kortdetaljene dine.';
$_['error_missing_field']   = 'Mangler nødvendig informasjon. Vennligst sjekk om grunnleggende adressedetaljer er oppgitt.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']    = 'Betalingen din er ikke fullført';
$_['msg_failed']        = 'Dessverre mislyktes betalingen din. Vennligst klikk på knappen nedenfor for å gå tilbake til betalingssiden og prøve å sette opp en betaling på nytt.';

// Status page: payment pending.
$_['heading_unknown']   = 'Betalingen din venter';
$_['msg_unknown']       = 'Betalingen din er ikke mottatt ennå. Vi vil sende deg en bekreftelse på e-post så snart betalingen er mottatt.';

// Status page: API failure.
$_['heading_error'] = 'Det oppsto en feil under oppsett av betalingen';
$_['text_error']    = 'Det oppsto en feil under oppsett av betalingen med Mollie. Klikk på knappen nedenfor for å gå tilbake til betalingssiden.';

// Payment link
$_['heading_payment_success'] = 'Betalingen er mottatt';
$_['text_payment_success'] = 'Betalingen din er fullført. Takk skal du ha!';
$_['heading_payment_failed'] = 'Betalingen er ukjent';
$_['text_payment_failed'] = 'Betalingen din er ikke mottatt ennå eller betalingsstatusen er ukjent. Vi vil gi deg beskjed i det øyeblikket betalingen er mottatt.';

// Response
$_['response_success']      = 'Betalingen er mottatt';
$_['response_none']         = 'Betalingen er ikke mottatt ennå';
$_['response_cancelled']    = 'Klienten har kansellert betalingen';
$_['response_failed']       = 'Dessverre gikk noe galt. Vennligst prøv betalingen på nytt.';
$_['response_expired']      = 'Betalingen har utløpt';
$_['response_unknown']      = 'En ukjent feil oppsto';
$_['shipment_success']      = 'Forsendelsen er opprettet';
$_['refund_cancelled']      = 'Refusjon har blitt kansellert.';
$_['refund_success']        = 'Refusjon har blitt behandlet vellykket!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Creditcard';
$_['method_bancontact']     = 'Bancontact';
$_['method_banktransfer']   = 'Bankoverføring';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'KBC/CBC Payment Button';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Giftcard';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnapaynow']   = 'Klarna Pay Now';
$_['method_klarnasliceit']  = 'Klarna Slice It';
$_['method_przelewy24']  	= 'P24';
$_['method_applepay']    	= 'Apple Pay';
$_['method_voucher']    	= 'Voucher';
$_['method_in3']    	    = 'IN3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Betal med Klarna';
$_['method_twint']          = 'Twint';
$_['method_blik']           = 'Blik';
$_['method_bancomatpay']    = 'Bancomat Pay';

//Round Off Description
$_['roundoff_description'] = 'Avrundingsforskjell på grunn av valutakonvertering';

//Warning
$_['warning_secure_connection'] = 'Vennligst sørg for at du bruker en sikker tilkobling.';
