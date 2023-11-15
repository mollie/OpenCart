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
 * Dutch language file for iDEAL by Mollie
 */

// Text
$_['heading_title']             = 'Betaling via Mollie';
$_['ideal_title']               = 'Uw betaling';
$_['text_title']                = 'Online betalen';
$_['text_redirected']           = 'De klant is doorgestuurd naar het betaalscherm';
$_['text_issuer_ideal']         = 'Kies uw bank';
$_['text_issuer_giftcard']      = 'Kies uw giftcard';
$_['text_issuer_kbc']           = 'Kies uw betaalknop';
$_['text_issuer_voucher']       = 'Kies uw merk';
$_['text_card_details']         = 'Voer uw creditcardgegevens in.';
$_['text_mollie_payments']      = 'Veilige betalingen geleverd door %s';
$_['text_recurring_desc']       = 'Bestelling %s, %s - %s, elke %s voor %s';
$_['text_recurring']		    = '%s elke %s %s';
$_['text_length']			    = ' voor %s betalingen';
$_['text_trial']			    = '%s elke %s %s voor %s betalingen dan ';
$_['text_error_report_success']	= 'De fout is met succes gerapporteerd!';
$_['text_payment_link_title']	= 'Mollie Betaallink';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	= "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']             = 'Klik op onderstaande link om uw bestelling te bekijken:';
$_['text_footer']           = 'Beantwoord deze e-mail als u vragen heeft.';
$_['text_payment_link_full_title']	    = 'Mollie Betaallink - Volledige bedrag';
$_['text_payment_link_open_title']	    = 'Mollie Betaallink - Open hoeveelheid';

// Button
$_['button_retry']          = 'Opnieuw proberen af te rekenen';
$_['button_report']         = 'Report Error';
$_['button_submit']         = 'Verzenden';

// Entry
$_['entry_card_holder']     	= 'Kaarthouder naam';
$_['entry_card_number']     	= 'Kaartnummer';
$_['entry_expiry_date']     	= 'Vervaldatum';
$_['entry_verification_code']	= 'CVV';

// Error
$_['error_card']				= 'Controleer uw kaartgegevens.';
$_['error_missing_field']	    = 'Vereiste informatie ontbreekt. Controleer of er basisadresgegevens zijn verstrekt.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Uw betaling is niet voltooid';
$_['msg_failed']         = 'Helaas is de betaling mislukt. Klik op onderstaande knop om terug te keren naar het afrekenscherm.';

// Status page: payment pending.
$_['heading_unknown']    = 'We wachten nog op uw betaling';
$_['msg_unknown']        = 'We hebben uw betaling nog niet ontvangen. Wij zullen een bevestigingsmail versturen op het moment dat de betaling binnen is.';

// Status page: API failure.
$_['heading_error']      = 'Er is een fout opgetreden bij het opzetten van de betaling';
$_['text_error']         = 'Er is een fout opgetreden bij het opzetten van de betaling bij Mollie. Klik op onderstaande knop om terug te keren naar het afrekenscherm.';

// Payment link
$_['heading_payment_success'] = 'De betaling is ontvangen';
$_['text_payment_success'] = 'Uw betaling is succesvol afgerond. Bedankt!';
$_['heading_payment_failed'] = 'De betaling is onbekend';
$_['text_payment_failed'] = 'Uw betaling is nog niet ontvangen of de betalingsstatus is onbekend. We laten het je weten zodra de betaling is ontvangen.';

// Response
$_['response_success']   = 'De betaling is ontvangen';
$_['response_none']      = 'We wachten nog op de betaling. U krijgt een email zodra de status van de betaling bij ons bekend is.';
$_['response_cancelled'] = 'De klant heeft de betaling geannuleerd';
$_['response_failed']    = 'De betaling is helaas mislukt. Probeer het alstublieft opnieuw.';
$_['response_expired']   = 'De betaling is verlopen';
$_['response_unknown']   = 'Er is een onbekende fout opgetreden';
$_['shipment_success']   = 'Zending is gemaakt';
$_['refund_cancelled']   = 'Restitutie is geannuleerd.';
$_['refund_success'] 	 = 'Terugbetaling is succesvol verwerkt!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Creditcard';
$_['method_bancontact']     = 'Bancontact';
$_['method_banktransfer']   = 'Overboeking';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'KBC/CBC-Betaalknop';
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Giftcard';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnapaynow']   = 'Klarna Pay Now';
$_['method_klarnasliceit']  = 'Klarna Betaal in 3 delen';
$_['method_przelewy24']  	= 'P24';
$_['method_applepay']    	= 'Apple Pay';
$_['method_voucher']    	= 'Voucher';
$_['method_in3']    	    = 'IN3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Betaal met Klarna';

//Round Off Description
$_['roundoff_description']  = 'Afrondingsverschil door valutaomrekening';

//Warning
$_['warning_secure_connection']  = 'Zorg ervoor dat u een veilige verbinding gebruikt.';
