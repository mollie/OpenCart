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
$method_list_logo                = '<a href="https://www.mollie.com" target="_blank"><img src="../image/mollie/mollie_logo.png" alt="Mollie" title="Mollie" style="border:0px" /></a>';
$_['text_mollie_banktransfer']   = $method_list_logo;
$_['text_mollie_belfius']        = $method_list_logo;
$_['text_mollie_creditcard']     = $method_list_logo;
$_['text_mollie_ideal']          = $method_list_logo;
$_['text_mollie_kbc']            = $method_list_logo;
$_['text_mollie_bancontact']     = $method_list_logo;
$_['text_mollie_paypal']         = $method_list_logo;
$_['text_mollie_paysafecard']    = $method_list_logo;
$_['text_mollie_sofort']         = $method_list_logo;
$_['text_mollie_giftcard']       = $method_list_logo;
$_['text_mollie_eps']            = $method_list_logo;
$_['text_mollie_giropay']        = $method_list_logo;
$_['text_mollie_klarnapaylater'] = $method_list_logo;
$_['text_mollie_klarnapaynow']   = $method_list_logo;
$_['text_mollie_klarnasliceit']  = $method_list_logo;
$_['text_mollie_przelewy_24']  	 = $method_list_logo;
$_['text_mollie_applepay']  	 = $method_list_logo;
$_['text_mollie_voucher']    	 = $method_list_logo;
$_['text_mollie_in_3']    	     = $method_list_logo;
$_['text_mollie_mybank']      	 = $method_list_logo;
$_['text_mollie_billie']      	 = $method_list_logo;
$_['text_mollie_klarna']      	 = $method_list_logo;

// Heading
$_['heading_title']           = "Mollie";
$_['title_global_options']    = "Definições";
$_['title_payment_status']    = "Status de pagamento";
$_['title_mod_about']         = "Sobre este módulo";
$_['footer_text']             = "Serviços de pagamento";
$_['title_mail']              = "Email";

// Module names
$_['name_mollie_banktransfer']   = "Transferência bancária";
$_['name_mollie_belfius']        = "Belfius Direct Net";
$_['name_mollie_creditcard']     = "Creditcard";
$_['name_mollie_ideal']          = "iDEAL";
$_['name_mollie_kbc']            = "KBC/CBC Payment Button";
$_['name_mollie_bancontact']     = "Bancontact";
$_['name_mollie_paypal']         = "PayPal";
$_['name_mollie_paysafecard']    = "paysafecard";
$_['name_mollie_sofort']         = "SOFORT Banking";
$_['name_mollie_giftcard']       = 'Giftcard';
$_['name_mollie_eps']            = 'EPS';
$_['name_mollie_giropay']        = 'Giropay';
$_['name_mollie_klarnapaylater'] = 'Klarna Pay Later';
$_['name_mollie_klarnapaynow']   = 'Klarna Pay Now';
$_['name_mollie_klarnasliceit']  = 'Klarna Slice It';
$_['name_mollie_przelewy_24']  	 = 'P24';
$_['name_mollie_applepay']  	 = 'Apple Pay';
$_['name_mollie_voucher']        = "Voucher";
$_['name_mollie_in_3']           = "IN3";
$_['name_mollie_mybank']         = "MyBank";
$_['name_mollie_billie']         = "Billie";
$_['name_mollie_klarna']         = "Pay with Klarna";

// Text
$_['text_edit']                    = "Editar";
$_['text_payment']                 = "Pagamento";
$_['text_success']                 = "Sucesso: Você modificou com sucesso suas configurações de Mollie!";
$_['text_missing_api_key']         = "Preencha sua chave de API na guia <a data-toggle='tab' href='javascript:void(0);' class='settings'>Configurações</a>.";
$_['text_activate_payment_method'] = 'Ative esta forma de pagamento em seu <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank">painel Mollie</a>.';
$_['text_no_status_id']            = "- Não atualize o status do pedido (não recomendado) -";
$_['text_enable']                  = "Permitir";
$_['text_disable']                 = "Desativar";
$_['text_connection_success']      = "Sucesso: Conexão com Mollie bem-sucedida!";
$_['text_error']                   = "Aviso: Algo deu errado. Tente novamente mais tarde!";
$_['text_creditcard_required']     = "Requer cartão de crédito";
$_['text_mollie_api']              = "API Mollie";
$_['text_mollie_app']              = "Aplicativo Mollie";
$_['text_general']                 = "Geral";
$_['text_enquiry']                 = "Como podemos ajudá-lo?";
$_['text_enquiry_success']         = "Sucesso: Sua consulta foi enviada. Entraremos em contato em breve. Obrigado!";
$_['text_update_message']          = 'Mollie: Uma nova versão (%s) está disponível. Clique <a href="%s">aqui</a> para atualizar. Não quer ver esta mensagem novamente? Clique em <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">aqui</a>.';
$_['text_update_message_warning']  = 'Mollie: Uma nova versão (%s) está disponível. Atualize sua versão do PHP para %s ou superior para atualizar o módulo ou continue usando a versão atual. Não quer ver esta mensagem novamente? Clique em <a href="javascript:void(0);" onclick="document.cookie=\'hide_mollie_update_message_version=%s\'; $(this).parent().hide();">aqui</a>.';
$_['text_update_success']          = "Sucesso: O módulo Mollie foi atualizado para a versão %s.";
$_['text_default_currency']        = "Moeda usada na loja";
$_['text_custom_css']              = "CSS personalizado para componentes Mollie";
$_['text_contact_us']              = "Fale Conosco - Suporte Técnico";
$_['text_bg_color']                = "Cor de fundo";
$_['text_color']                   = "Cor";
$_['text_font_size']               = "Tamanho da fonte";
$_['text_other_css']               = "Outro CSS";
$_['text_module_by']               = "Módulo da Quality Works - Suporte Técnico";
$_['text_mollie_support']          = "Mollie - Suporte";
$_['text_contact']                 = "Contato";
$_['text_allowed_variables']       = "Variáveis ​​permitidas: {firstname}, {lastname}, {next_payment}, {product_name}, {order_id}, {store_name}";
$_['text_browse']                  = 'Procurar';
$_['text_clear']                   = 'Limpar';
$_['text_image_manager']           = 'Gerenciador de Imagens';
$_['text_left']                    = 'Esquerda';
$_['text_right']                   = 'Certo';
$_['text_more']                    = 'Mais';
$_['text_no_maximum_limit']        = 'Sem limite de valor máximo';
$_['text_standard_total']          = 'Total padrão: %s';
$_['text_advance_option']          = 'Opções avançadas para %s';
$_['text_payment_api']             = 'API de pagamentos';
$_['text_order_api']               = 'API de pedidos';
$_['text_info_orders_api']         = 'Por que usar a API de Pedidos?';
$_['text_pay_link_variables']      = "Variáveis ​​permitidas: {firstname}, {lastname}, {amount}, {order_id}, {store_name}, {payment_link}";
$_['text_pay_link_text']           = "Dear customer, <br /><br /> Click on the link below to complete your payment of {amount} for the order {order_id}.<br /><br /> {payment_link}<br /><br /><br /><br />Regards,<br /><br />{store_name}";
$_['text_recurring_payment']       = "Pagamento recorrente";
$_['text_payment_link']            = "Link de pagamento";

// Entry
$_['entry_payment_method']         = "Forma de pagamento";
$_['entry_activate']               = "Ativar";
$_['entry_sort_order']             = "Ordem de classificação";
$_['entry_api_key']                = "Chave de API";
$_['entry_description']            = "Descrição";
$_['entry_show_icons']             = "Mostrar ícones";
$_['entry_show_order_canceled_page'] = "Mostrar mensagem se o pagamento for cancelado";
$_['entry_geo_zone']               = "Zona geográfica";
$_['entry_client_id']              = "ID do cliente";
$_['entry_client_secret']          = "Segredo do cliente";
$_['entry_redirect_uri']           = "URI de redirecionamento";
$_['entry_payment_screen_language'] = "Idioma padrão da tela de pagamento";
$_['entry_mollie_connect']         = "Mollie conectar";
$_['entry_name']                   = "Nome";
$_['entry_email']                  = "E-mail";
$_['entry_subject']                = "Assunto";
$_['entry_enquiry']                = "Consulta";
$_['entry_debug_mode']             = "Modo de depuração";
$_['entry_mollie_component']       = "Componentes Mollie";
$_['entry_test_mode']              = "Modo de teste";
$_['entry_mollie_component_base']  = "CSS personalizado para campo de entrada Base";
$_['entry_mollie_component_valid'] = "CSS personalizado para campo de entrada válido";
$_['entry_mollie_component_invalid'] = "CSS personalizado para campo de entrada inválido";
$_['entry_default_currency']       = "Sempre pagar com";
$_['entry_email_subject']          = "Assunto";
$_['entry_email_body']             = "Corpo";
$_['entry_title']                  = "Título";
$_['entry_image']                  = "Imagem";
$_['entry_status']                 = "Status";
$_['entry_align_icons']            = "Alinhar ícones";
$_['entry_single_click_payment']   = "Pagamento por clique único";
$_['entry_order_expiry_days']      = "Dias de validade do pedido";
$_['entry_partial_refund']         = "Reembolso Parcial";
$_['entry_amount']                 = "Valor (exemplo: 5 ou 5%)";
$_['entry_payment_fee']            = "Taxa de pagamento";
$_['entry_payment_fee_tax_class']  = "Classe de Taxa de Pagamento";
$_['entry_total']                  = "Total";
$_['entry_minimum']                = "Mínimo";
$_['entry_maximum']                = "Máximo";
$_['entry_api_to_use']             = "API a ser usada";
$_['entry_payment_link']  		     = "Enviar link de pagamento";
$_['entry_payment_link_sep_email']   = "Enviar em um e-mail separado";
$_['entry_payment_link_ord_email']   = "Enviar no e-mail de confirmação do pedido";
$_['entry_partial_credit_order']     = 'Criar ordem de crédito no reembolso (parcial)';

// Help
$_['help_view_profile']            = 'Você pode encontrar sua chave de API em <a href="https://www.mollie.com/dashboard/settings/profiles/" target="_blank" class="alert-link" >os seus perfis do site Mollie</a>.';
$_['help_status']                  = "Ativar o módulo";
$_['help_api_key']                 = 'Digite o <code>api_key</code> do perfil do site que deseja usar. A chave da API começa com <code>test_</code> ou <code>live_</code>.';
$_['help_description']             = 'Esta descrição aparecerá no extrato bancário/cartão do seu cliente. Você pode usar no máximo 29 caracteres. DICA: Use <code>%</code>, isso será substituído pelo ID do pedido do pagamento. Não se esqueça que <code>%</code> pode ter vários caracteres!';
$_['help_show_icons']              = 'Mostrar ícones ao lado dos métodos de pagamento Mollie na página de checkout.';
$_['help_show_order_canceled_page'] = 'Mostrar uma mensagem ao cliente se um pagamento for cancelado, antes de redirecionar o cliente de volta ao carrinho de compras.';
$_['help_redirect_uri']            = 'O URI de redirecionamento em seu painel mollie deve corresponder a este URI.';
$_['help_mollie_app']              = 'Ao registrar seu módulo como um aplicativo no painel Mollie, você desbloqueará funcionalidades adicionais. Isso não é necessário para usar os pagamentos da Mollie.';
$_['help_apple_pay']               = 'O Apple Pay requer que o cartão de crédito esteja ativado no perfil do seu site. Por favor, habilite o método de cartão de crédito primeiro.';
$_['help_mollie_component']        = 'Os componentes Mollie permitem que você mostre os campos necessários para os dados do titular do cartão de crédito em seu próprio checkout.';
$_['help_single_click_payment']    = 'Permitir que seus clientes debitem um cartão de crédito usado anteriormente com um único clique.';
$_['help_total']                   = 'O valor mínimo e máximo do checkout antes que este método de pagamento se torne ativo.';
$_['help_payment_link']				= 'Ao criar pedidos do administrador, um método <strong>Mollie Payment Link</strong> estará disponível para enviar o link de pagamento ao cliente para pagamento. Você pode definir o texto do e-mail na guia de e-mail.';

// Info
$_['entry_module']      = "Módulo";
$_['entry_mod_status']  = "Status do módulo";
$_['entry_comm_status'] = "Status da comunicação";
$_['entry_support']     = "Suporte";
$_['entry_version']     = '<a href="https://github.com/mollie/OpenCart/releases" target="_blank">Mollie Opencart</a>';

// Error
$_['error_permission']         = "Aviso: Você não tem permissão para modificar os métodos de pagamento da Mollie.";
$_['error_api_key']            = "A chave da API Mollie é obrigatória!";
$_['error_api_key_invalid']    = "Chave de API inválida!";
$_['error_description']        = "A descrição é obrigatória!";
$_['error_file_missing']       = "O arquivo não existe";
$_['error_name']               = 'Aviso: o nome deve ter entre 3 e 25 caracteres!';
$_['error_email']              = 'Aviso: o endereço de e-mail não parece ser válido!';
$_['error_subject']            = 'Aviso: O assunto deve ter 3 caracteres!';
$_['error_enquiry']            = 'Aviso: O texto da consulta deve ter 25 caracteres!';
$_['error_no_api_client']      = 'Cliente de API não encontrado.';
$_['error_api_help']           = 'Você pode pedir ajuda ao seu provedor de hospedagem.';
$_['error_comm_failed']        = '<strong>Falha na comunicação com Mollie:</strong><br/>%s<br/><br/>Por favor, verifique as seguintes condições. Você pode pedir ajuda ao seu provedor de hospedagem.<ul><li>Certifique-se de que as conexões externas com %s não estejam bloqueadas.</li><li>Certifique-se de que o SSL v3 esteja desativado em seu servidor. Mollie não suporta SSL v3.</li><li>Verifique se seu servidor está atualizado e se os patches de segurança mais recentes foram instalados.</li></ul><br/>Contato <a href= "mailto:info@mollie.nl">info@mollie.nl</a> se isso ainda não resolver o problema.';
$_['error_no_api_key']         = 'Nenhuma chave de API fornecida. Por favor, insira sua chave de API.';
$_['error_order_expiry_days']  = 'Aviso: Não é possível usar Klarna Slice it ou Klarna Pay mais tarde como método quando a data de expiração for superior a 28 dias no futuro.';
$_['error_mollie_payment_fee'] = 'Aviso: O total do pedido da Taxa de Pagamento Mollie está desabilitado!';
$_['error_file']               = 'Aviso: o arquivo %s não foi encontrado!';
$_['error_address']            = 'O endereço de cobrança está desativado, os pedidos digitais não poderão ser pagos. Você pode ativar o endereço de cobrança nas <a href="%s">configurações</a>.';

// Status
$_['entry_pending_status']        = "Status do pagamento criado";
$_['entry_failed_status']         = "Status de falha no pagamento";
$_['entry_canceled_status']       = "Status do pagamento cancelado";
$_['entry_expired_status']        = "Status do pagamento expirado";
$_['entry_processing_status']     = "Pagamento com sucesso";
$_['entry_refund_status']         = "Status do reembolso do pagamento";
$_['entry_partial_refund_status'] = "Status do reembolso parcial";
$_['entry_shipping_status']       = "Status do pedido enviado";
$_['entry_shipment']              = "Criar envio";
$_['entry_create_shipment_status'] = "Criar envio após o status do pedido";
$_['help_shipment']               = "O envio será criado logo após a criação do pedido. Selecione 'Não' para criar o envio quando o pedido atingir um status específico e selecione o status do pedido abaixo.";

$_['text_create_shipment_automatically']      = "Criar envio automaticamente após a criação do pedido";
$_['text_create_shipment_on_status']          = "Criar envio ao definir o pedido para este status";
$_['text_create_shipment_on_order_complete']  = "Criar envio ao definir pedido para status de pedido completo";
$_['entry_create_shipment_on_order_complete'] = "Criar envio após a conclusão do pedido";

// Button
$_['button_update']         = "Atualizar";
$_['button_mollie_connect'] = "Conectar via Mollie";
$_['button_advance_option'] = "Opção Avançada";
$_['button_save_close']     = "Salvar e fechar";

// Error log
$_['text_log_success']  = 'Sucesso: Você limpou com sucesso seu log mollie!';
$_['text_log_list']     = 'Log';
$_['error_log_warning'] = 'Aviso: Seu arquivo de log mollie %s é %s!';
$_['button_download']   = 'Baixar';
