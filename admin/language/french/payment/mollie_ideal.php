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
$_['heading_title']           = 'Mollie (iDEAL, Mister Cash, Creditcard, PayPal & paysafecard)';
$_['footer_text']             = 'Services paiement';

// Text
$_['text_payment']            = "Paiement";
$_['text_success']            = "Succès: Vous avez réussi à modifier les paramètres Mollie!";
$_['text_mollie_ideal']       = '<a href="https://www.mollie.nl" target="_blank"><img src="https://www.mollie.nl/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']            = "État: <br/><span class='help'>Activer le module</span>";
$_['entry_api_key']           = "Clé API: <br/><span class='help'>Entrer ici le <code>api_key</code> du profil que vous souhaitez utiliser. Le clé API commence par <code>test_</code> ou <code>live_</code>. <br>[<a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>voir les profiles disponibles</a>]</span>";
$_['entry_description']       = "Description: <br/><span class='help'>Cette description apparaîtra sur le relevé bancaire de votre client. Vous pouvez utiliser un maximum de 29 caractères. ASTUCE: Utilisez le '%', il sera remplacé par l'id de la commande du paiement. N'oubliez pas que % peut devenir plusieurs caractères!</span>";
$_['entry_sort_order']        = "Ordre de triage:";

// Info
$_['entry_module']            = "Module:";
$_['entry_status']            = "État du Module:";
$_['entry_version']           = "<a href='https://github.com/mollie/OpenCart/releases' target='_blank'>MollieOpenCart</a>";
$_['entry_support']	    	  = "Assistance:";

// Error
$_['error_permission']        = "Attention: Vous n'avez pas l'autorisation de modifier les méthodes de paiement Mollie.";
$_['error_api_key']           = "La clé API est nécessaire!";
$_['error_description']       = "Description est nécessaire!";

// Status
$_['entry_failed_status']     = 'État echoué:';
$_['entry_canceled_status']   = 'État annulé:';
$_['entry_expired_status']    = 'État expiré:';
$_['entry_pending_status']    = 'État attente:';
$_['entry_processing_status'] = 'État traitement:';