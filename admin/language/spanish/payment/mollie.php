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
$_['text_mollie_eps']           = $method_list_logo;
$_['text_mollie_giropay']       = $method_list_logo;
$_['text_mollie_klarnapaylater'] = $method_list_logo;
$_['text_mollie_klarnasliceit']  = $method_list_logo;
$_['text_mollie_przelewy24']  	 = $method_list_logo;

// Heading
$_['heading_title']         = "Mollie";
$_['title_global_options']  = "Ajustes";
$_['title_payment_status']  = "Estados de pago";
$_['title_mod_about']       = "Acerca de este módulo";
$_['footer_text']           = "Servicios de pago";

// Module names
$_['name_mollie_banktransfer']  = "Transferencia bancaria";
$_['name_mollie_belfius']       = "Belfius Direct Net";
$_['name_mollie_creditcard']    = "Creditcard";
$_['name_mollie_directdebit']   = "Adeudo bancario";
$_['name_mollie_ideal']         = "iDEAL";
$_['name_mollie_kbc']           = "KBC/CBC-Betaalknop";
$_['name_mollie_mistercash']    = "Bancontact/MisterCash";
$_['name_mollie_paypal']        = "PayPal";
$_['name_mollie_paysafecard']   = "paysafecard";
$_['name_mollie_sofort']        = "SOFORT Banking";
$_['name_mollie_giftcard']      = 'Giftcard';
$_['name_mollie_inghomepay']    = 'ING Home\'Pay';
$_['name_mollie_eps']           = 'EPS';
$_['name_mollie_giropay']       = 'Giropay';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy24']  	 = 'P24';

// Text
$_['text_edit']                    = "Corregir Mollie";
$_['text_payment']                 = "Pago";
$_['text_success']                 = "Realizado con éxito: ¡los ajustes para el módulo han sido modificados!";
$_['text_missing_api_key']         = "Por favor, complete su clave API en la pestaña <a data-toggle='tab' href='#' class='settings'>Configuración</a>.";
$_['text_activate_payment_method'] = 'Active esta forma de pago a través del <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank">panel de control de Mollie</a>.';
$_['text_no_status_id']            = "- No cambiar de estado (no recomendado) -";

// Entry
$_['entry_payment_method']           = "Método de pago";
$_['entry_activate']                 = "Activar";
$_['entry_sort_order']               = "Orden de clasificación";
$_['entry_api_key']                  = "Clave API";
$_['entry_description']              = "Descripción";
$_['entry_show_icons']               = "Mostrar iconos";
$_['entry_show_order_canceled_page'] = "Mostrar notificación en caso de cancelación de pagos";
$_['entry_geo_zone']                 = "Zona Geo";


// Help
$_['help_view_profile']             = 'Puede encontrar su clave API en  <a href="https://www.mollie.com/beheer/account/profielen/" target="_blank" class="alert-link">sus 
perfiles de la web de Mollie</a>.';
$_['help_status']                   = "Activar módulo";
$_['help_api_key']                  = "Introduzca aquí la <code>clave_api</code> del perfil de la web que desea utilizar. La clave API comienza con <code>test_</code> o <code>live_</code>.";
$_['help_description']              = "La descripción aparecerá en el estado de cuenta de su cliente y se puede encontrar en la administración de Mollie. Puede utilizar hasta 29 caracteres. CONSEJO: Use<code>%</code>, que será reemplazado por el número de pedido. ¡El número de pedido puede tener algunos caracteres más!";
$_['help_show_icons']               = "Mostrar iconos junto a los métodos de pago de Mollie en la página de pago.";
$_['help_show_order_canceled_page'] = "Mostrar una notificación al cliente si un pago es cancelado, antes de redirigir al cliente de nuevo a la cesta de la compra.";

// Info
$_['entry_module']          = "Module";
$_['entry_mod_status']      = "Modulestatus";
$_['entry_comm_status']     = "Estado de comunicación";
$_['entry_support']         = "Ayuda";

$_['entry_version']         = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']      = "Advertencia: no tienes permiso para modificar el módulo.";
$_['error_api_key']         = "¡La clave API de Mollie es obligatoria!";
$_['error_api_key_invalid'] = "¡Clave inválida de Mollie API!";
$_['error_description']     = "¡La descripción es obligatoria!";
$_['error_file_missing']    = "El archivo no existe";

// Status
$_['entry_pending_status']   = "¡Clave de API Mollie inválida!";
$_['entry_failed_status']    = "Estado de pago creado";
$_['entry_canceled_status']  = "Estado del pago fallido";
$_['entry_expired_status']   = "Estado del pago cancelado";
$_['entry_processing_status']= "Estado del pago vencido";

$_['entry_shipping_status']   = "Estado del pedido enviado";
$_['entry_shipment']       			 = "Crear envío";
$_['entry_create_shipment_status']   = "Crear envío después del estado del pedido";
$_['help_shipment'] 				 = "El envío (solo para los métodos de klarna) se creará justo después de crear el pedido. Seleccione 'No' para crear el envío cuando el pedido llegue a un estado específico y seleccione el estado del pedido a continuación.";

$_['text_create_shipment_automatically']            = "Crear envío automáticamente al crear el pedido";
$_['text_create_shipment_on_status']                = "Crear envío al establecer orden a este estado";
$_['text_create_shipment_on_order_complete']        = "Crear envío al establecer orden para ordenar estado completo";
$_['entry_create_shipment_on_order_complete'] 		= "Crear envío al completar el pedido";

//Button
$_['button_update'] = "Actualizar";
