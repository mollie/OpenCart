<?php
/**
 * Copyright (c) 2012-2014, Mollie B.V.
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
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */

// Heading
$_['heading_title']           = "Mollie (iDEAL, Mister Cash, Creditcard, PayPal & paysafecard)";
$_['title_payment_status']    = "États de Paiement";
$_['title_mod_about']         = "Sur Ce Module";
$_['footer_text']             = "Services paiement";

// Text
$_['text_edit']               = "Éditer Mollie";
$_['text_payment']            = "Paiement";
$_['text_success']            = "Succès: Vous avez réussi à modifier les paramètres Mollie!";
$_['text_view_profile']       = '<a href="https://www.mollie.nl/beheer/account/profielen/" target="_blank">Voir votre profiles disponibles</a>';
$_['text_mollie_ideal']       = '<a href="https://www.mollie.nl" target="_blank"><img src="https://www.mollie.nl/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']            = "État";
$_['entry_api_key']           = "Clé API";
$_['entry_description']       = "Description";
$_['entry_sort_order']        = "Ordre de triage";

// Help
$_['help_view_profile']    = 'Vous pouvez trouver votre clé API dans <a href="https://www.mollie.nl/beheer/account/profielen/" target="_blank" class="alert-link">vos profils de site à Mollie</a>.';
$_['help_status']          = "Activer le module";
$_['help_api_key']         = 'Entrer ici le <code>api_key</code> du profil que vous souhaitez utiliser. Le cl&eacute; API commence par <code>test_</code> ou <code>live_</code>.';
$_['help_description']     = 'Cette description apparaîtra sur le relevé bancaire de votre client. Vous pouvez utiliser un maximum de 29 caractères. ASTUCE: Utilisez le <code>%</code>, il sera remplacé par l\'id de la commande du paiement. N\'oubliez pas que <code>%/code> peut devenir plusieurs caractères!';

// Info
$_['entry_module']            = "Module";
$_['entry_mod_status']        = "État du module";
$_['entry_comm_status']       = "État de la communication";
$_['entry_support']           = "Assistance";

$_['entry_version']           = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">MollieOpenCart</a>';

// Error
$_['error_permission']        = "Attention: Vous n'avez pas l'autorisation de modifier les méthodes de paiement Mollie.";
$_['error_api_key']           = "La clé API est nécessaire!";
$_['error_description']       = "Description est nécessaire!";
$_['error_file_missing']      = "Fichier ne existe pas";

// Status
$_['entry_pending_status']    = "État de paiement attente";
$_['entry_failed_status']     = "État de paiement échoué";
$_['entry_canceled_status']   = "État de paiement annulé";
$_['entry_expired_status']    = "État de paiement expiré";
$_['entry_processing_status'] = "État de paiement traitement";