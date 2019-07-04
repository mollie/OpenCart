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
 * Spanish language file for iDEAL by Mollie
 */

// Text
$_['heading_title']         = 'Pago a través de Mollie';
$_['ideal_title']           = 'su pago';
$_['text_title']            = 'Pagar en línea';
$_['text_redirected']       = 'El cliente ha sido redirigido a la pantalla de pago';
$_['text_issuer_ideal']     = 'Seleccione su banco';
$_['text_issuer_giftcard']  = 'Seleccione su tarjeta regalo';
$_['text_issuer_kbc']       = 'Seleccione su botón de pago';
$_['button_retry']          = 'Intente pagar de nuevo';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Su pago no se ha completado';
$_['msg_failed']         = 'Lamentablemente, no se ha efectuado el pago. Haga clic en el siguiente botón para volver a la pantalla de pago.';

// Status page: payment pending.
$_['heading_unknown']    = 'Seguimos esperando su pago';
$_['msg_unknown']        = 'Aún no hemos recibido su pago. Le enviaremos un correo electrónico de confirmación tan pronto como recibamos el pago.';

// Status page: API failure.
$_['heading_error']      = 'Se ha producido un error en la configuración del pago';
$_['text_error']         = 'Se ha producido un error en la configuración del pago en Mollie. Haga clic en el siguiente botón para volver a la pantalla de pago.';

// Response
$_['response_success']   = 'El pago ha sido recibido';
$_['response_none']      = 'Seguimos esperando el pago. Recibirá un correo electrónico tan pronto como sepamos el estado del pago.';
$_['response_cancelled'] = 'El cliente ha cancelado el pago';
$_['response_failed']    = 'Lamentablemente, no se ha efectuado el pago. Por favor, inténtelo de nuevo.';
$_['response_expired']   = 'El pago ha caducado';
$_['response_unknown']   = 'Se ha producido un error desconocido';
$_['shipment_success']   = 'Se crea el envio';

// Methods
$_['method_ideal']                      = 'iDEAL';
$_['method_creditcard']                 = 'Creditcard';
$_['method_mistercash']                 = 'Bancontact';
$_['method_banktransfer']               = 'Transferencia bancaria';
$_['method_directdebit']                = 'Adeudo bancario';
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
$_['roundoff_description']  = ' Diferencia de redondeo por conversión de moneda';
