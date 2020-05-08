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
 * @author		OSWorX https://osworx.net
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.com
 */

/**
 * German language file for iDEAL by Mollie
 */

// Text
$_['heading_title']				= 'Bezahlen mit Mollie';
$_['ideal_title']				= 'Meine Zahlung';
$_['text_title']				= 'Online bezahlen';
$_['text_redirected']			= 'Kunde wurde auf den Zahlungsbildschirm umgeleitet';
$_['text_issuer_ideal']			= 'Bitte die Bank wählen';
$_['text_issuer_giftcard']		= 'Bitte die Geschenkskarte wählen';
$_['text_issuer_kbc']			= 'Bitte Bezahl-Button wählen';
$_['button_retry']				= 'Noch einmal versuchen';
$_['text_card_details']			= 'Bitte die Kreditkartendaten eingeben';
$_['text_mollie_payments']		= 'Sicher bezahlen mit %s';

// Entry
$_['entry_card_holder']			= 'Name Karteninhaber';
$_['entry_card_number']			= 'Kartennummer';
$_['entry_expiry_date']			= 'Ablaufdatum';
$_['entry_verification_code']	= 'CVV';

// Error
$_['error_card']				= 'Bitte die Kartendaten überprüfen';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']			= 'Zahlung ist nicht abgeschlossen';
$_['msg_failed']				= 'Leider ist die Zahlung fehlgeschlagen. Bitte auf folgende Schaltfläche klicken um zum Abrechnungsbildschirm zurückzukehren.';

// Status page: payment pending.
$_['heading_unknown']			= 'Zahlung ausstehend';
$_['msg_unknown']				= 'Wir haben die Zahlung noch nicht erhalten. Sobald diese eintrifft werden wir ein Bestätigungsmail zusenden.';

// Status page: API failure.
$_['heading_error']				= 'Beim Erstellen der Zahlung ist ein Fehler aufgetreten';
$_['text_error']				= 'Beim Erstellen der Zahlung bei Mollie ist ein Fehler aufgetreten. Bitte auf folgende Schaltfläche klicken um zum Abrechnungsbildschirm zurückzukehren.';

// Response
$_['response_success']			= 'Zahlung erfolgreich eingegangen';
$_['response_none']				= 'Wir warten noch auf die Zahlungsbestätigung. Sobald diese eingetroffen ist, senden wir eine Benachrichtigug.';
$_['response_cancelled']		= 'Kunde hat die Zahlung storniert';
$_['response_failed']			= 'Zahlung ist leider fehlgeschlagen .. bitte nochmal versuchen';
$_['response_expired']			= 'Zahlungszeitraum ist abgelaufen';
$_['response_unknown']			= 'Es ist ein unbekannter Fehler aufgetreten';
$_['shipment_success']			= 'Lieferung wird erstellt';
$_['refund_cancelled']			= 'Rückerstattung wurde storniert';
$_['refund_success']			= 'Rückerstattung wurde erfolgreich durchgeführt';

// Methods
$_['method_ideal']				= 'iDEAL';
$_['method_creditcard']			= 'Kreditkarte';
$_['method_mistercash']			= 'Bancontact';
$_['method_banktransfer']		= 'Überweisung';
$_['method_directdebit']		= 'SEPA Lastschrift';
$_['method_belfius']			= 'Belfius Direct Net';
$_['method_kbc']				= 'KBC/CBC Bezahlung';
$_['method_sofort']				= 'Sofortüberweisung';
$_['method_paypal']				= 'PayPal';
$_['method_paysafecard']		= 'paysafecard';
$_['method_giftcard']			= 'Geschenkskarte';
$_['method_inghomepay']			= 'ING Home\'Pay';
$_['method_eps']				= 'EPS';
$_['method_giropay']			= 'Giropay';
$_['method_klarnapaylater']		= 'Klarna Rechnung';
$_['method_klarnasliceit']		= 'Klarna Ratenkauf';
$_['method_przelewy24']			= 'P24';

//Round Off Description
$_['roundoff_description']		= 'Rundungsdifferenzen aufgrund von Währungsumrechnungen';
