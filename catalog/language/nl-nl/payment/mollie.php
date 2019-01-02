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
$_['heading_title']         = 'Betaling via Mollie';
$_['ideal_title']           = 'Uw betaling';
$_['text_title']            = 'Online betalen';
$_['text_redirected']       = 'De klant is doorgestuurd naar het betaalscherm';
$_['text_issuer_ideal']     = 'Kies uw bank';
$_['text_issuer_giftcard']  = 'Kies uw giftcard';
$_['text_issuer_kbc']       = 'Kies uw betaalknop';
$_['button_retry']          = 'Opnieuw proberen af te rekenen';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Uw betaling is niet voltooid';
$_['msg_failed']         = 'Helaas is de betaling mislukt. Klik op onderstaande knop om terug te keren naar het afrekenscherm.';

// Status page: payment pending.
$_['heading_unknown']    = 'We wachten nog op uw betaling';
$_['msg_unknown']        = 'We hebben uw betaling nog niet ontvangen. Wij zullen een bevestigingsmail versturen op het moment dat de betaling binnen is.';

// Status page: API failure.
$_['heading_error']      = 'Er is een fout opgetreden bij het opzetten van de betaling';
$_['text_error']         = 'Er is een fout opgetreden bij het opzetten van de betaling bij Mollie. Klik op onderstaande knop om terug te keren naar het afrekenscherm.';

// Response
$_['response_success']   = 'De betaling is ontvangen';
$_['response_none']      = 'We wachten nog op de betaling. U krijgt een email zodra de status van de betaling bij ons bekend is.';
$_['response_cancelled'] = 'De klant heeft de betaling geannuleerd';
$_['response_failed']    = 'De betaling is helaas mislukt. Probeer het alstublieft opnieuw.';
$_['response_expired']   = 'De betaling is verlopen';
$_['response_unknown']   = 'Er is een onbekende fout opgetreden';
$_['shipment_success']   = 'Zending is gemaakt';

// Methods
$_['method_ideal']        = 'iDEAL';
$_['method_creditcard']   = 'Creditcard';
$_['method_bancontact']   = 'Bancontact';
$_['method_banktransfer'] = 'Overboeking';
$_['method_directdebit']  = 'Eenmalige incasso';
$_['method_belfius']      = 'Belfius Direct Net';
$_['method_kbc']          = 'KBC/CBC-Betaalknop';
$_['method_bitcoin']      = 'Bitcoin';
$_['method_sofort']       = 'SOFORT Banking';
$_['method_paypal']       = 'PayPal';
$_['method_paysafecard']  = 'paysafecard';
$_['method_giftcard']     = 'Giftcard';
$_['method_inghomepay']   = 'ING Home\'Pay';
$_['method_eps']          = 'EPS';
$_['method_giropay']      = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnasliceit']  = 'Klarna Slice It';

//Round Off Description
$_['roundoff_description']  = 'Afrondingsverschil door valutaomrekening';
