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

// These are called automatically by the Payment modules list - do not change the names
$method_list_logo              = '<a href="https://www.mollie.com" target="_blank"><img src="https://www.mollie.com/images/logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']  = $method_list_logo;
$_['text_mollie_belfius']       = $method_list_logo;
$_['text_mollie_bitcoin']       = $method_list_logo;
$_['text_mollie_creditcard']    = $method_list_logo;
$_['text_mollie_directdebit']   = $method_list_logo;
$_['text_mollie_ideal']         = $method_list_logo;
$_['text_mollie_kbc']           = $method_list_logo;
$_['text_mollie_mistercash']    = $method_list_logo;
$_['text_mollie_paypal']        = $method_list_logo;
$_['text_mollie_paysafecard']   = $method_list_logo;
$_['text_mollie_sofort']        = $method_list_logo;
$_['text_mollie_giftcard']      = $method_list_logo;
$_['text_mollie_inghomepay']    = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Paramètres";
$_['title_payment_status']    = "États de paiement";
$_['title_mod_about']         = "Sur ce module";
$_['footer_text']             = "Services paiement";

// Module names
$_['name_mollie_banktransfer']  = "Virement bancaire";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_bitcoin']       = "Bitcoin";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Débit direct";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "Bouton de paiement KBC/CBC";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";
$_['name_mollie_paypal']        = "PayPal";
$_['name_mollie_paysafecard']   = "paysafecard";
$_['name_mollie_sofort']        = "SOFORT Banking";
$_['name_mollie_giftcard']      = 'Giftcard';
$_['name_mollie_inghomepay']    = 'ING Home\'Pay';

// Text
$_['text_edit']                    = "Éditer Mollie";
$_['text_payment']                 = "Paiement";
$_['text_success']                 = "Succès: Vous avez réussi à modifier les paramètres Mollie!";
$_['text_missing_api_key']         = "Remplir votre clé API ci-dessous.";
$_['text_activate_payment_method'] = 'Activer ce mode de paiement via le <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank">Mollie dashboard</a>.';
$_['text_no_status_id']            = '- Ne pas mettre à jour le statut (non recommandé) -';

// Entry
$_['entry_payment_method']           = "Procédé de paiement";
$_['entry_activate']                 = "Activate";
$_['entry_sort_order']               = "Ordre de triage";
$_['entry_api_key']                  = "Clé API";
$_['entry_description']              = "Description";
$_['entry_show_icons']               = "Afficher des icônes";
$_['entry_show_order_canceled_page'] = "Afficher un message si le paiement est annulé";

// Help
$_['help_view_profile']             = 'Vous pouvez trouver votre clé API dans <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank" class="alert-link">vos profils de site à Mollie</a>.';
$_['help_status']                   = "Activer le module";
$_['help_api_key']                  = 'Entrer ici le <code>api_key</code> du profil que vous souhaitez utiliser. Le cl&eacute; API commence par <code>test_</code> ou <code>live_</code>.';
$_['help_description']              = 'Cette description apparaîtra sur le relevé bancaire de votre client. Vous pouvez utiliser un maximum de 29 caractères. ASTUCE: Utilisez le <code>%</code>, il sera remplacé par l\'id de la commande du paiement. N\'oubliez pas que <code>%/code> peut devenir plusieurs caractères!';
$_['help_show_icons']               = 'Afficher les icônes à côté des méthodes de paiement Mollie sur la page de paiement.';
$_['help_show_order_canceled_page'] = 'Afficher un message au client si un paiement est annulé, avant de rediriger le client vers leur panier.';

// Info
$_['entry_module']            = "Module";
$_['entry_mod_status']        = "État du module";
$_['entry_comm_status']       = "État de la communication";
$_['entry_support']           = "Assistance";

$_['entry_version']           = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">MollieOpenCart</a>';

// Error
$_['error_permission']        = "Attention: Vous n'avez pas l'autorisation de modifier les méthodes de paiement Mollie.";
$_['error_api_key']           = "La clé API est nécessaire!";
$_['error_api_key_invalid']   = "La clé API est invalide!";
$_['error_description']       = "Description est nécessaire!";
$_['error_file_missing']      = "Fichier ne existe pas";

// Status
$_['entry_pending_status']    = "État de paiement attente";
$_['entry_failed_status']     = "État de paiement échoué";
$_['entry_canceled_status']   = "État de paiement annulé";
$_['entry_expired_status']    = "État de paiement expiré";
$_['entry_processing_status'] = "État de paiement traitement";
