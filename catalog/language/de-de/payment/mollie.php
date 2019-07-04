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
 * German language file for iDEAL by Mollie
 */

// Text
$_['heading_title']         = 'Zahlung via Mollie';
$_['ideal_title']           = 'Ihre Zahlung';
$_['text_title']            = 'Online bezahlen';
$_['text_redirected']       = 'Der Kunde wurde auf den Zahlungs-Bildschirm umgeleitet';
$_['text_issuer_ideal']     = 'Wählen Sie Ihre Bank';
$_['text_issuer_giftcard']  = 'Wählen Sie Ihre Giftcard';
$_['text_issuer_kbc']       = 'Wählen Sie Ihren Bezahl-Button';
$_['button_retry']          = 'Erneut versuchen, abzurechnen';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Ihre Zahlung ist nicht abgeschlossen';
$_['msg_failed']         = 'Leider ist die Zahlung fehlgeschlagen. Klicken Sie auf die folgende Schaltfläche, um zum Abrechnungs-Bildschirm zurückzukehren.';

// Status page: payment pending.
$_['heading_unknown']    = 'Wir warten noch auf Ihre Zahlung';
$_['msg_unknown']        = 'Wir haben Ihre Zahlung noch nicht erhalten. Wir werden eine Bestätigungsmail verschicken, sobald die Zahlung eingegangen ist.';

// Status page: API failure.
$_['heading_error']      = 'Beim Erstellen der Zahlung ist ein Fehler aufgetreten';
$_['text_error']         = 'Beim Erstellen der Zahlung bei Mollie ist ein Fehler aufgetreten. Klicken Sie auf die folgende Schaltfläche, um zum Abrechnungs-Bildschirm zurückzukehren.';

// Response
$_['response_success']   = 'Die Zahlung wurde erhalten';
$_['response_none']      = 'Wir warten noch auf die Zahlung. Sie erhalten eine E-Mail, sobald uns der Status Ihrer Zahlung bekannt ist.';
$_['response_cancelled'] = 'Der Kunde hat die Zahlung annulliert';
$_['response_failed']    = 'Die Zahlung ist leider fehlgeschlagen. Versuchen Sie es bitte erneut.';
$_['response_expired']   = 'Die Zahlung ist verstrichen';
$_['response_unknown']   = 'Es ist ein unbekannter Fehler aufgetreten';
$_['shipment_success']   = 'Sendung wird erstellt';

// Methods
$_['method_ideal']                      = 'iDEAL';
$_['method_creditcard']                 = 'Creditcard';
$_['method_mistercash']                 = 'Bancontact';
$_['method_banktransfer']               = 'Übertragung';
$_['method_directdebit']                = 'Einmaliges Inkasso';
$_['method_belfius']                    = 'Belfius Direct Net';
$_['method_kbc']                        = 'KBC/CBC-Betaalknop';
$_['method_sofort']                     = 'SOFORT Banking';
$_['method_paypal']                     = 'PayPal';
$_['method_paysafecard']                = 'paysafecard';
$_['method_giftcard']                   = 'Giftcard';
$_['method_inghomepay']                 = 'ING Home\'Pay';
$_['method_eps']                        = 'EPS';
$_['method_giropay']                    = 'Giropay';
$_['method_klarnapaylater'] 			= 'Klarna Pay Later';
$_['method_klarnasliceit']  			= 'Klarna Slice It';
$_['method_przelewy24']  				= 'P24';

//Round Off Description
$_['roundoff_description']  = 'Rundungsdifferenzen aufgrund von Währungsumrechnungen';
