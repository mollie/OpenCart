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
 * French language file for iDEAL by Mollie
 */

// Text
$_['heading_title']             = 'Paiement par Mollie';
$_['ideal_title']               = 'Votre paiement';
$_['text_title']                = 'Payez en ligne';
$_['text_redirected']           = 'Le client a été renvoyé à l\'écran de paiement';
$_['text_issuer_ideal']         = 'Selectionnez votre banque:';
$_['text_issuer_giftcard']      = 'Sélectionnez votre carte-cadeau:';
$_['text_issuer_kbc']           = 'Sélectionnez votre bouton de paiement:';
$_['text_issuer_voucher']       = 'Sélectionnez votre marque:';
$_['text_card_details']         = 'S\'il vous plaît entrer les détails de votre carte de crédit.';
$_['text_mollie_payments']      = 'Paiements sécurisés fournis par %s';
$_['text_recurring_desc']       = 'Commande %s, %s - %s, tous les %s pour %s';
$_['text_recurring']		    = '%s tous les %s %s';
$_['text_length']			    = ' pour les paiements %s';
$_['text_trial']			    = '%s tous les %s %s pour %s paiements puis ';
$_['text_error_report_success']	= 'L\'erreur a été signalée avec succès!';

// Button
$_['button_retry']          = 'Retour à la page de paiement';
$_['button_report']         = 'Report Error';
$_['button_submit']         = 'Soumettre';

// Entry
$_['entry_card_holder']     	= 'Card Holder Name';
$_['entry_card_number']     	= 'Card Number';
$_['entry_expiry_date']     	= 'Expiry Date';
$_['entry_verification_code']	= 'CVV';

// Error
$_['error_card']				= 'Veuillez vérifier les détails de votre carte.';
$_['error_missing_field']	    = 'Informations obligatoires manquantes. Veuillez vérifier si les coordonnées de base sont fournies.';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Votre paiement n\'a pas été achevée';
$_['msg_failed']         = 'Malheureusement, votre paiement est échoué.';

// Status page: payment pending.
$_['heading_unknown']    = 'Votre paiement est en attente';
$_['msg_unknown']        = 'Votre paiement n\'a pas encore été reçu. Nous vous enverrons un e-mail de confirmation au moment où le paiement est reçu.';

// Status page: API failure.
$_['heading_error']      = 'Une erreur s\'est produite lors de la mise en place du paiement';
$_['text_error']         = 'Une erreur s\'est produite lors de la mise en place du paiement avec Mollie:';

// Response
$_['response_success']   = 'Le paiement est reçu';
$_['response_none']      = 'Le paiement n\'est pas encore reçu';
$_['response_cancelled'] = 'Le client a annulé le paiement';
$_['response_failed']    = 'Malheureusement une erreur s\'est produite. S\'il vous plaît réessayer le paiement.';
$_['response_expired']   = 'Le paiement a expiré';
$_['response_unknown']   = 'Une erreur inconnue s\'est produite';
$_['shipment_success']   = 'L\'envoi est créé';
$_['refund_cancelled']   = 'Le remboursement a été annulé.';
$_['refund_success'] 	 = 'Le remboursement a été traité avec succès!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Creditcard';
$_['method_bancontact']     = 'Bancontact';
$_['method_banktransfer']   = 'Bank transfer';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = "Bouton de paiement KBC/CBC";
$_['method_sofort']         = 'SOFORT Banking';
$_['method_paypal']         = 'PayPal';
$_['method_paysafecard']    = 'paysafecard';
$_['method_giftcard']       = 'Giftcard';
$_['method_eps']            = 'EPS';
$_['method_giropay']        = 'Giropay';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnapaynow']   = 'Klarna Pay Now';
$_['method_klarnasliceit']  = 'Klarna Slice It';
$_['method_przelewy24']     = 'P24';
$_['method_applepay']    	= 'Apple Pay';
$_['method_voucher']    	= 'Voucher';
$_['method_in3']    	    = 'IN3';

//Round Off Description
$_['roundoff_description']  = 'Dû à la conversion de devise, il se peut qu’il y ait un écart d’arrondi';

//Warning
$_['warning_secure_connection']  = 'Veuillez vous assurer que vous utilisez une connexion sécurisée.';
