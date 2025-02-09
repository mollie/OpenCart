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
$_['heading_title']             = 'Pago a través de Mollie';
$_['ideal_title']               = 'su pago';
$_['text_title']                = 'Pagar en línea';
$_['text_redirected']           = 'El cliente ha sido redirigido a la pantalla de pago';
$_['text_issuer_giftcard']      = 'Seleccione su tarjeta regalo';
$_['text_issuer_kbc']           = 'Seleccione su botón de pago';
$_['text_issuer_voucher']       = 'Selecciona tu marca';
$_['text_card_details']         = 'Por favor ingrese los detalles de su tarjeta de crédito.';
$_['text_mollie_payments']      = 'Pagos seguros proporcionados por %s';
$_['text_subscription_desc']    = 'Pedido %s, %s - %s, cada %s durante %s';
$_['text_subscription']		    = '%s cada %s %s';
$_['text_length']			    = ' para pagos de %s';
$_['text_trial']			    = '%s cada %s %s para %s pagos luego ';
$_['text_error_report_success']	= 'El error se ha informado correctamente!';
$_['text_payment_link_title']	= 'Enlace de pago de Mollie';
$_['text_payment_link_email_subject']	= 'Payment Link';
$_['text_payment_link_email_text']	= "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_link']             = 'Para ver su pedido, haga clic en el siguiente enlace:';
$_['text_footer']           = 'Responda a este correo electrónico si tiene alguna pregunta.';
$_['text_payment_link_full_title']	    = 'Enlace de pago de Mollie - Monto total';
$_['text_payment_link_open_title']	    = 'Enlace de pago de Mollie - Cantidad abierta';
$_['text_cancelled']                    = 'Se ha cancelado el pago recurrente';
$_['text_subscription_cancel_confirm']  = '¿Quieres cancelar la suscripción?';

// Button
$_['button_retry']          = 'Intente pagar de nuevo';
$_['button_report']         = 'Report Error';
$_['button_submit']         = 'Enviar';
$_['button_subscription_cancel'] = 'Cancelar suscripción';

// Entry
$_['entry_card_holder']     	= 'Card Holder Name';
$_['entry_card_number']     	= 'Card Number';
$_['entry_expiry_date']     	= 'Expiry Date';
$_['entry_verification_code']	= 'CVV';

// Error
$_['error_card']				= 'Por favor verifique los detalles de su tarjeta.';
$_['error_missing_field']	    = 'Falta información requerida. Verifique si se proporcionan los detalles básicos de la dirección.';
$_['error_not_cancelled']       = 'Error: %s';

// Status page: payment failed (e.g. cancelled).
$_['heading_failed']     = 'Su pago no se ha completado';
$_['msg_failed']         = 'Lamentablemente, no se ha efectuado el pago. Haga clic en el siguiente botón para volver a la pantalla de pago.';

// Status page: payment pending.
$_['heading_unknown']    = 'Seguimos esperando su pago';
$_['msg_unknown']        = 'Aún no hemos recibido su pago. Le enviaremos un correo electrónico de confirmación tan pronto como recibamos el pago.';

// Status page: API failure.
$_['heading_error']      = 'Se ha producido un error en la configuración del pago';
$_['text_error']         = 'Se ha producido un error en la configuración del pago en Mollie. Haga clic en el siguiente botón para volver a la pantalla de pago.';

// Payment link
$_['heading_payment_success'] = 'Se recibió el pago';
$_['text_payment_success'] = 'Su pago se completó con éxito. ¡Gracias!';
$_['heading_payment_failed'] = 'El pago es desconocido';
$_['text_payment_failed'] = 'Su pago aún no se ha recibido o se desconoce el estado del pago. Le informaremos en el momento en que se reciba el pago.';

// Response
$_['response_success']   = 'El pago ha sido recibido';
$_['response_none']      = 'Seguimos esperando el pago. Recibirá un correo electrónico tan pronto como sepamos el estado del pago.';
$_['response_cancelled'] = 'El cliente ha cancelado el pago';
$_['response_failed']    = 'Lamentablemente, no se ha efectuado el pago. Por favor, inténtelo de nuevo.';
$_['response_expired']   = 'El pago ha caducado';
$_['response_unknown']   = 'Se ha producido un error desconocido';
$_['shipment_success']   = 'Se crea el envio';
$_['refund_cancelled']   = 'El reembolso ha sido cancelado.';
$_['refund_success'] 	 = '¡El reembolso ha sido procesado con éxito!';

// Methods
$_['method_ideal']          = 'iDEAL';
$_['method_creditcard']     = 'Creditcard';
$_['method_mistercash']     = 'Bancontact';
$_['method_banktransfer']   = 'Transferencia bancaria';
$_['method_belfius']        = 'Belfius Direct Net';
$_['method_kbc']            = 'KBC/CBC-Betaalknop';
$_['method_paypal']         = 'PayPal';
$_['method_giftcard']       = 'Giftcard';
$_['method_eps']            = 'EPS';
$_['method_klarnapaylater'] = 'Klarna Pay Later';
$_['method_klarnapaynow']   = 'Klarna Pay Now';
$_['method_klarnasliceit']  = 'Klarna Slice It';
$_['method_przelewy24']  	= 'P24';
$_['method_applepay']    	= 'Apple Pay';
$_['method_voucher']    	= 'Voucher';
$_['method_in3']    	    = 'iDEAL in3';
$_['method_mybank']         = 'MyBank';
$_['method_billie']         = 'Billie';
$_['method_klarna']         = 'Paga con Klarna';
$_['method_twint']          = 'Twint';
$_['method_blik']           = 'Blik';
$_['method_bancomatpay']    = 'Bancomat Pay';
$_['method_trustly']        = 'Trustly';
$_['method_alma']           = 'Alma';
$_['method_riverty']        = 'Riverty';
$_['method_payconiq']       = 'Payconiq';
$_['method_satispay']       = 'Satispay';

//Round Off Description
$_['roundoff_description']  = ' Diferencia de redondeo por conversión de moneda';

//Warning
$_['warning_secure_connection']  = 'Asegúrese de utilizar una conexión segura.';
