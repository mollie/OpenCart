<?php
namespace Opencart\Catalog\Controller\Extension\Mollie;
/**
 * Copyright (c) 2012-2017, Mollie B.V.
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
 *
 * @property Config $config
 * @property Currency $currency
 * @property array $data
 * @property Document $document
 * @property Language $language
 * @property Loader $load
 * @property Log $log
 * @property ModelCheckoutOrder $model_checkout_order
 * @property Request $request
 * @property Response $response
 * @property Session $session
 * @property URL $url
 *
 * @method render
 */
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;

require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");

class Mollie extends \Opencart\System\Engine\Controller {
    // List of accepted languages by mollie
    private $locales = array(
                'en_US',
                'nl_NL',
                'nl_BE',
                'fr_FR',
                'fr_BE',
                'de_DE',
                'de_AT',
                'de_CH',
                'es_ES',
                'ca_ES',
                'pt_PT',
                'it_IT',
                'nb_NO',
                'sv_SE',
                'fi_FI',
                'da_DK',
                'is_IS',
                'hu_HU',
                'pl_PL',
                'lv_LV',
                'lt_LT'
            );
    
    public $mollieHelper;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->mollieHelper = new \MollieHelper($registry);
    }

    /**
     * @return MollieApiClient
     */
    protected function getAPIClient()
    {
        return $this->mollieHelper->getAPIClient($this->config);
    }

    /**
     *
     * Keep a log of Mollie transactions.
     *
     * @param $line string
     * @param $alsoEcho bool
     */
    protected function writeToMollieLog($line, $alsoEcho = false)
    {
        $log = new \Opencart\System\Library\Log('Mollie.log');
        $log->write($line);
        if ($alsoEcho) echo $line;
    }

    protected function writeToMollieDebugLog($line, $alsoEcho = false)
    {
        $log = new \Opencart\System\Library\Log('Mollie_debug.log');
        $log->write($line);
        if ($alsoEcho) echo $line;
    }

    /**
     * @return ModelExtensionPaymentMollie
     */
    protected function getModuleModel()
    {
        $model_name = "model_extension_mollie_payment_mollie_" . static::MODULE_NAME;

        if (!isset($this->$model_name)) {
            $this->load->model("extension/mollie/payment/mollie_" . static::MODULE_NAME);
        }

        return $this->$model_name;
    }

    /**
     * @return bool
     */
    protected function getOrderID()
    {
        if (empty($this->session->data['order_id']) && !isset($this->request->get['order_id'])) {
            return false;
        }
        if (isset($this->request->get['order_id'])) {
            return $this->request->get['order_id'];
        }
        return $this->session->data['order_id'];
    }

    /**
     * Get the order we are processing from OpenCart.
     *
     * @return array
     */
    protected function getOpenCartOrder($order_id)
    {
        $this->load->model("checkout/order");
        // Load last order from session
        return $this->model_checkout_order->getOrder($order_id);
    }

    //Get order products
    protected function getOrderProducts($order_id)
    {
        $model = $this->getModuleModel();

        return $model->getOrderProducts($order_id);
    }

    //Get tax rate
    protected function getTaxRate($tax_rates = array())
    {
        $rates = array();
        if(!empty($tax_rates)) {
            foreach($tax_rates as $tax) {
                $rates[] = $tax['rate'];
            }
        }
        return $rates;
    }

    //Get Coupon Details
    protected function getCouponDetails($orderID)
    {
        $model = $this->getModuleModel();

        return $model->getCouponDetails($orderID);
    }

    //Get Voucher Details
    protected function getVoucherDetails($orderID) 
    {
        $model = $this->getModuleModel();

        return $model->getVoucherDetails($orderID);
    }


    //Get Reward Point Details
    protected function getRewardPointDetails($orderID) 
    {
        $model = $this->getModuleModel();

        return $model->getRewardPointDetails($orderID);
    }

    public function numberFormat($amount) {
        $currency = $this->getCurrency();
        $intCurrencies = array("ISK", "JPY");
        if(!in_array($currency, $intCurrencies)) {
            $formattedAmount = number_format((float)$amount, 2, '.', '');
        } else {
            $formattedAmount = number_format($amount, 0);
        }   
        return $formattedAmount;    
    }

    public function getCurrency() {
        if($this->config->get($this->mollieHelper->getModuleCode() . "_default_currency") == "DEF") {
            $currency = $this->session->data['currency'];
        } else {
            $currency = $this->config->get($this->mollieHelper->getModuleCode() . "_default_currency");
        }
        return $currency;
    }

    private function getMethodSeparator() {
        $method_separator = '|';

        if(version_compare(VERSION, '4.0.2.0', '>=')) {
            $method_separator = '.';
        }

        return $method_separator;
    }

    /**
     * This gets called by OpenCart at the final checkout step and should generate a confirmation button.
     * @return string
     */
    public function index()
    {
        $this->load->language("extension/mollie/payment/mollie");

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $method = str_replace('mollie_', '', explode('.', $this->session->data['payment_method']['code'])[1]);
        } else {
            $method = str_replace('mollie_', '', $this->session->data['payment_method']);
        }

        $method = str_replace('_', '', $method);

        if ($method == 'ideal') {
            $payment_method = $this->getAPIClient()->methods->get($method);
        } else {
            $payment_method = $this->getAPIClient()->methods->get($method, array('include' => 'issuers'));
        }

        $api_to_use = $this->config->get($this->mollieHelper->getModuleCode() . "_" . static::MODULE_NAME . "_api_to_use");
        
        if (in_array($method, ['klarnapaylater', 'klarnasliceit', 'klarnapaynow', 'voucher', 'in3', 'klarna', 'billie', 'riverty'])) {
            $api_to_use = 'orders_api';
        } elseif (in_array($method, ['alma'])) {
            $api_to_use = 'payments_api';
        }

        if ($api_to_use == 'orders_api') {
            $data['action'] = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "order", '', true);
        } else {
            $data['action'] = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "payment", '', true);
        }
        
        $data['image']                   = $payment_method->image->size1x;
        $data['message']                 = $this->language;
        $data['issuers']                 = isset($payment_method->issuers) ? $payment_method->issuers : array();
        if (!empty($data['issuers'])) {
            $data['text_issuer']             = $this->language->get("text_issuer_" . $method);
            $data['set_issuer_url']          = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "set_issuer", '', true);
        }
        $data['entry_card_holder']       = $this->language->get('entry_card_holder');
        $data['entry_card_number']       = $this->language->get('entry_card_number');
        $data['entry_expiry_date']       = $this->language->get('entry_expiry_date');
        $data['entry_verification_code'] = $this->language->get('entry_verification_code');
        $data['text_card_details']       = $this->language->get('text_card_details');
        $data['error_card']              = $this->language->get('error_card');
        $data['text_mollie_payments']    = sprintf($this->language->get('text_mollie_payments'), '<a href="https://www.mollie.com/" target="_blank"><img src="./image/mollie/mollie_logo.png" alt="Mollie" border="0"></a>');

        // Mollie components
        $data['mollieComponents'] = false;
        if($method == 'creditcard') {
            if($this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component") && !$this->config->get($this->mollieHelper->getModuleCode() . "_single_click_payment")) {
                // Get current profile
                $data['currentProfile'] = $this->getAPIClient()->profiles->getCurrent()->id;

                if (strstr(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'), '-')) {
                    list ($language, $country) = explode('-', isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                    $locale = strtolower($language) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) . '_' . strtoupper(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                }

                if (!in_array($locale, $this->locales)) {
                    $locale = $this->config->get($this->mollieHelper->getModuleCode() . "_payment_screen_language");
                    if (strstr($locale, '-')) {
                        list ($language, $country) = explode('-', $locale);
                        $locale = strtolower($language) . '_' . strtoupper($country);
                    } else {
                        $locale = strtolower($locale) . '_' . strtoupper($locale);
                    }
                }

                if((strtolower($locale) == 'en_gb') || (strtolower($locale) == 'en_en')) {
                    $locale = 'en_US';
                }
                $data['locale']           = $locale;
                $data['mollieComponents'] = true;
                $data['base_input_css']    = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_base");
                $data['valid_input_css']   = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_valid");
                $data['invalid_input_css'] = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_invalid");
                $apiKey =  $this->config->get($this->mollieHelper->getModuleCode() . "_api_key");
                if(strpos($apiKey, 'test_') !== false) {
                    $data['testMode'] = true;
                } else {
                    $data['testMode'] = false;
                }
            }
        }

        $data['isJournalTheme'] = false;
        if (strpos($this->config->get('config_template'), 'journal2') === 0 && $this->journal2->settings->get('journal_checkout')) {
            $data['isJournalTheme'] = true;
        }

        return $this->load->view('extension/mollie/payment/mollie_checkout_form', $data);
    }

    protected function convertCurrency($amount) {
        $convertedAmount = $this->currency->format($amount, $this->getCurrency(), false, false);
        
        return $convertedAmount;
    }

    //Format text
    protected function formatText($text) {
        if ($text) {
            return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        } else {
            return $text;
        }
    }

    public function addressCheck($order) {
		$valid = true;
		$field = '';

        $noPostCode = ["AE", "AN", "AO", "AW", "BF", "BI", "BJ", "BO", "BS", "BV", "BW", "BZ", "CD", "CF", "CG", "CI", "CK", "CM", "DJ", "DM", "ER", "FJ", "GA", "GD", "GH", "GM", "GN", "GQ", "GY", "HK", "JM", "KE", "KI", "KM", "KN", "KP", "LC", "ML", "MO", "MR", "MS", "MU", "MW", "NA", "NR", "NU", "PA", "QA", "RW", "SB", "SC", "SL", "SO", "SR", "ST", "SY", "TF", "TK", "TL", "TO", "TT", "TV", "UG", "VU", "YE", "ZM", "ZW"];

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $payment_address = $this->config->get('config_checkout_payment_address');
        } else {
            $payment_address = $this->config->get('config_checkout_address');
        }

        if ($payment_address) {
            if (empty($order['payment_firstname'])) {
                $valid = false;
                $field = 'Billing Firstname';
            } elseif (empty($order['payment_lastname'])) {
                $valid = false;
                $field = 'Billing Lastname';
            } elseif (empty($order['payment_address_1'])) {
                $valid = false;
                $field = 'Billing Street';
            } elseif (empty($order['payment_city'])) {
                $valid = false;
                $field = 'Billing City';
            } elseif (empty($order['payment_postcode'])) {
                if (!in_array($order['payment_iso_code_2'], $noPostCode)) {
                    $valid = false;
                    $field = 'Billing Postcode';
                }
            }
        }
		
		if (isset($this->session->data['shipping_address'])) {
			if (empty($order['shipping_firstname'])) {
				$valid = false;
				$field = 'Shipping Firstname';
			} elseif (empty($order['shipping_lastname'])) {
				$valid = false;
				$field = 'Shipping Lastname';
			} elseif (empty($order['shipping_address_1'])) {
				$valid = false;
				$field = 'Shipping Street';
			} elseif (empty($order['shipping_city'])) {
				$valid = false;
				$field = 'Shipping City';
			} elseif (empty($order['shipping_postcode'])) {
				if (!in_array($order['shipping_iso_code_2'], $noPostCode)) {
                    $valid = false;
                    $field = 'Shipping Postcode';
                }
			}
		}

		if (!$valid) {
			$this->writeToMollieLog("Mollie Payment Error: Mollie payment require payment and shipping address details. Empty required field: " . $field);
		}

		return $valid;
	}

    /**
     * The payment action creates the payment and redirects the customer to the selected bank.
     *
     * It is called when the customer submits the button generated in the mollie_checkout_form template.
     */
    public function order()
    {
        // Load essentials
        $this->load->language("extension/mollie/payment/mollie");

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->showErrorPage($this->language->get('warning_secure_connection'));
            $this->writeToMollieLog("Creating order failed, connection is not secure.");
            return;
        }
        
        try {
            $api = $this->getAPIClient();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating payment failed, API did not load; " . htmlspecialchars($e->getMessage()));
            return;
        }

        $model = $this->getModuleModel();
        $order_id = $this->getOrderID();
        $order = $this->getOpenCartOrder($order_id);

        $currency = $this->getCurrency();
        $amount = $this->convertCurrency($order['total']);
        //$description = str_replace("%", $order['order_id'], html_entity_decode($this->config->get($this->mollieHelper->getModuleCode() . "_description"), ENT_QUOTES, "UTF-8"));
        $return_url = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "callback&order_id=" . $order['order_id']);
        $issuer = $this->getIssuer();

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $method = str_replace('mollie_', '', explode('.', $this->session->data['payment_method']['code'])[1]);
        } else {
            $method = str_replace('mollie_', '', $this->session->data['payment_method']);
        }
        
        $method = str_replace('_', '', $method);

        // Check for subscription profiles
        $subscription = false;
        if ($this->cart->hasSubscription()) {
            $subscription = true;
        }

        $singleClickPayment = false;
        if(($method == 'creditcard') && $this->config->get($this->mollieHelper->getModuleCode() . "_single_click_payment")) {
            $mollie_customer_id = $this->createCustomer($order);
            $singleClickPayment = true;
        } elseif ($subscription) {
            $mollie_customer_id = $this->createCustomer($order);
        }

        $mandate = false;
        if (!empty($mollie_customer_id)) {
            $customer = $api->customers->get($mollie_customer_id);
            $mandates = $customer->mandates();
            foreach($mandates as $_mandate) {
                if(($_mandate->isValid()) || ($_mandate->isPending())) {
                    $mandate = true;
                    break;
                }
            }
        }

        try {
            $data = array(
                "amount" => ["currency" => $currency, "value" => (string)$this->numberFormat($amount)],
                "orderNumber" => $order['order_id'],
                "redirectUrl" => $this->formatText($return_url),
                "webhookUrl" => $this->getWebhookUrl(),
                "metadata" => array("order_id" => $order['order_id']),
                "method" => $method,
            );

            if ($this->config->get($this->mollieHelper->getModuleCode() . "_order_expiry_days") && ($this->config->get($this->mollieHelper->getModuleCode() . "_order_expiry_days") > 0)) {
                $days = $this->config->get($this->mollieHelper->getModuleCode() . "_order_expiry_days");
                if ($days > 100) {
                    $days = 100;
                }
                $date = new \DateTime();
                $date->modify("+$days days");
                $data['expiresAt'] = (string)$date->format('Y-m-d');
            }    

            $data['payment'] = array(
                "issuer" => $this->formatText($issuer),
                "webhookUrl" => $this->getWebhookUrl()
            );

            if((($singleClickPayment && $mandate) || $subscription) && !empty($mollie_customer_id)) {
                $data['payment']['customerId'] = (string)$mollie_customer_id;
            }

            // Additional data for subscription profile
            if($subscription) {
                $data['payment']['sequenceType'] = "first";
            }

            // Send cardToken in case of creditcard(if available)
            if (isset($this->request->post['cardToken'])) {
                $data['payment']['cardToken'] = $this->request->post['cardToken'];
            }

            //Order line data
            $orderProducts = $this->getOrderProducts((int)$order['order_id']);
            $lines = array();

            $this->load->model('catalog/product');
            foreach($orderProducts as $orderProduct) {
                $productDetails = $this->model_catalog_product->getProduct($orderProduct['product_id']);
                $tax_rates = $this->tax->getRates($orderProduct['price'], $productDetails['tax_class_id']);
                $rates = $this->getTaxRate($tax_rates);
                //Since Mollie only supports VAT so '$rates' must contains only one(VAT) rate.
                $vatRate = isset($rates[0]) ? $rates[0] : 0;
                $total = $this->numberFormat($this->convertCurrency(($orderProduct['price'] + $orderProduct['tax']) * $orderProduct['quantity']));

                // Fix for qty < 1
                $qty = (int)$orderProduct['quantity'];
                if($qty < 1) {
                    $qty = 1;
                    $price = $orderProduct['price'] * $orderProduct['quantity'];
                    $tax = $orderProduct['tax'] * $orderProduct['quantity'];
                } else {
                    $qty = (int)$orderProduct['quantity'];
                    $price = $orderProduct['price'];
                    $tax = $orderProduct['tax'];
                }

                $vatAmount = $total * ( $vatRate / (100 +  $vatRate));

                $voucher_category = $model->getProductVoucherCategory($orderProduct['product_id']);

                if (!empty($voucher_category)) {
                    $lines[] = array(
                        'type'          =>  'physical',
                        'category'      =>  (string)$voucher_category,
                        'name'          =>  $this->formatText($orderProduct['name']),
                        'quantity'      =>  $qty,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($price + $tax))],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($total)],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($vatAmount)],
                        'metadata'      =>  array("order_product_id" => $orderProduct['order_product_id'])
                    );
                } else {
                    $lines[] = array(
                        'type'          =>  'physical',
                        'name'          =>  $this->formatText($orderProduct['name']),
                        'quantity'      =>  $qty,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($price + $tax))],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($total)],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($vatAmount)],
                        'metadata'      =>  array("order_product_id" => $orderProduct['order_product_id'])
                    );
                }
            }

            //Check for shipping fee
            if(isset($this->session->data['shipping_method'])) {
                if (version_compare(VERSION, '4.0.1.1', '>')) {
                    $shipping = explode('.', $this->session->data['shipping_method']['code']);
                } else {
                    $shipping = explode('.', $this->session->data['shipping_method']);
                }

                if (isset($shipping[0]) && isset($shipping[1]) && isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                    $shipping_method_info = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

                    $title = (version_compare(VERSION, '4.0.1.1', '>')) ? $shipping_method_info['name'] : $shipping_method_info['title'];
                    $cost = $shipping_method_info['cost'];
                    if (isset($this->session->data['shipping_method']['tax_class_id'])) {
                        $taxClass = $this->session->data['shipping_method']['tax_class_id'];
                    } else {
                        $taxClass = 0;
                    }
                    $tax_rates = $this->tax->getRates($cost, $taxClass);
                    $rates = $this->getTaxRate($tax_rates);
                    $vatRate = isset($rates[0]) ? $rates[0] : 0;
                    $costWithTax = $this->tax->calculate($cost, $taxClass, true);
                    $costWithTax = $this->numberFormat($this->convertCurrency($costWithTax));
                    $shippingVATAmount = $costWithTax * ( $vatRate / (100 +  $vatRate));
                    $lineForShipping[] = array(
                        'type'          =>  'shipping_fee',
                        'name'          =>  $this->formatText($title),
                        'quantity'      =>  1,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$costWithTax],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$costWithTax],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($shippingVATAmount)]
                    );

                    $lines = array_merge($lines, $lineForShipping);
                }
            }

            //Check if coupon applied
            $super_ultimate_coupons = false;
            if ($this->config->get('total_ultimate_coupons_status') || $this->config->get('total_super_coupons_status')) {
                $super_ultimate_coupons = true;
            }

            if(isset($this->session->data['coupon']) && !$super_ultimate_coupons) {
                //Get coupon data

                $this->load->model('marketing/coupon');

                $coupon_info = $this->model_marketing_coupon->getCoupon($this->session->data['coupon']);

                if ($coupon_info) {
                    $discount_total = 0;
                    $couponVATAmount = 0;

                    if (!$coupon_info['product']) {
                        $sub_total = $this->cart->getSubTotal();
                    } else {
                        $sub_total = 0;

                        foreach ($this->cart->getProducts() as $product) {
                            if (in_array($product['product_id'], $coupon_info['product'])) {
                                $sub_total += $product['total'];
                            }
                        }
                    }

                    if ($coupon_info['type'] == 'F') {
                        $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
                    }

                    foreach ($this->cart->getProducts() as $product) {
                        $discount = 0;

                        if (!$coupon_info['product']) {
                            $status = true;
                        } else {
                            $status = in_array($product['product_id'], $coupon_info['product']);
                        }

                        if ($status) {
                            if ($coupon_info['type'] == 'F') {
                                $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                            } elseif ($coupon_info['type'] == 'P') {
                                $discount = $product['total'] / 100 * $coupon_info['discount'];
                            }

                            if ($product['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                                foreach ($tax_rates as $tax_rate) {
                                    if ($tax_rate['type'] == 'P') {
                                        $couponVATAmount += $tax_rate['amount'];
                                    }
                                }
                            }
                        }

                        $discount_total += $discount;
                    }

                    if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
                        if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
                            if (version_compare(VERSION, '4.0.1.1', '>')) {
                                $shipping = explode('.', $this->session->data['shipping_method']['code']);
                            } else {
                                $shipping = explode('.', $this->session->data['shipping_method']);
                            }

                            if (isset($shipping[0]) && isset($shipping[1]) && isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                                $shipping_method_info = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

                                $tax_rates = $this->tax->getRates($shipping_method_info['cost'], $this->session->data['shipping_method']['tax_class_id']);

                                foreach ($tax_rates as $tax_rate) {
                                    if ($tax_rate['type'] == 'P') {
                                        $couponVATAmount += $tax_rate['amount'];
                                    }
                                }

                                $discount_total += $shipping_method_info['cost'];
                            }
                        }
                    }

                    $vatRate = ($couponVATAmount * 100) / ($discount_total);

                    $vatRate = $this->numberFormat($vatRate);

                    $unitPriceWithTax = $this->numberFormat($this->convertCurrency($discount_total + $couponVATAmount));

                    $couponVATAmount = $this->numberFormat($this->convertCurrency($couponVATAmount));

                    // Rounding fix
                    $couponVATAmount1 = $unitPriceWithTax * ($vatRate / (100 + $vatRate));
                    $couponVATAmount1 = $this->numberFormat($couponVATAmount1);
                    if($couponVATAmount != $couponVATAmount1) {
                        if($couponVATAmount1 > $couponVATAmount) {
                            $couponVATAmount = $couponVATAmount + ($couponVATAmount1 - $couponVATAmount);
                        } else {
                            $couponVATAmount = $couponVATAmount - ($couponVATAmount - $couponVATAmount1);
                        }
                    }

                    $lineForCoupon[] = array(
                        'type'          =>  'discount',
                        'name'          =>  $this->formatText($coupon_info['name']),
                        'quantity'      =>  1,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(-$unitPriceWithTax)],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat(-$unitPriceWithTax)],
                        'vatRate'       =>  (string)$vatRate,
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(-$couponVATAmount)]
                    );

                    $lines = array_merge($lines, $lineForCoupon);
                }
            }

            //Check if gift card applied
            if(isset($this->session->data['voucher'])) {
                //Get voucher data
                $voucher = $this->getVoucherDetails($order['order_id']);
                $lineForVoucher[] = array(
                    'type'          =>  'gift_card',
                    'name'          =>  $this->formatText($voucher['title']),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['value']))],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['value']))],
                    'vatRate'       =>  "0.00",
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                );

                $lines = array_merge($lines, $lineForVoucher);
            }

            //Check for reward points
            if(isset($this->session->data['reward'])) {
                //Get reward point data
                $rewardPoints = $this->getRewardPointDetails($order['order_id']);

                foreach ($this->cart->getProducts() as $product) {    
                    if ($product['points']) {
                        if ($product['tax_class_id']) {
                            $taxClass = $product['tax_class_id'];
                            $tax_rates = $this->tax->getRates($rewardPoints['value'], $taxClass);
                            $rates = $this->getTaxRate($tax_rates);
                            $vatRate = $rates[0];
                            break;
                        }
                    }
                }

                if(!isset($vatRate) || empty($vatRate)) {
                    $vatRate = 0;
                }

                $unitPriceWithTax = $this->tax->calculate($rewardPoints['value'], $taxClass, true);
                $unitPriceWithTax = $this->numberFormat($this->convertCurrency($unitPriceWithTax));

                $rewardVATAmount = $unitPriceWithTax * ( $vatRate / (100 +  $vatRate));

                $lineForRewardPoints[] = array(
                    'type'          =>  'discount',
                    'name'          =>  $this->formatText($rewardPoints['title']),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$unitPriceWithTax],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$unitPriceWithTax],
                    'vatRate'       =>  (string)$this->numberFormat($vatRate),
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($rewardVATAmount)]
                );

                $lines = array_merge($lines, $lineForRewardPoints);
            }

            // Gift Voucher
            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $key => $voucher) {
                    $voucherData[] = array(
                        'type'                  => 'physical',
                        'name'                  => $voucher['description'],
                        'quantity'              => 1,
                        'unitPrice'             =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['amount']))],
                        'totalAmount'           =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['amount']))],
                        'vatRate'               =>  "0.00",
                        'vatAmount'             =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                    );
                }

                $lines = array_merge($lines, $voucherData);
            }

            //Check for other totals (if any)
            $otherOrderTotals = $model->getOtherOrderTotals($order['order_id']);
            if(!empty($otherOrderTotals)) {
                $otherTotals = array();

                foreach($otherOrderTotals as $orderTotals) {

                    if($this->config->get('total_' . $orderTotals['code'] . '_tax_class_id')) {
                        $taxClass = $this->config->get('total_' . $orderTotals['code'] . '_tax_class_id');
                    } else {
                        $taxClass = 0;
                    }

                    $tax_rates = $this->tax->getRates($orderTotals['value'], $taxClass);
                    $rates = $this->getTaxRate($tax_rates);
                    $vatRate = isset($rates[0]) ? $rates[0] : 0;
                    $unitPriceWithTax = $this->tax->calculate($orderTotals['value'], $taxClass, true);
                    $unitPriceWithTax = $this->numberFormat($this->convertCurrency($unitPriceWithTax));
                    $totalsVATAmount = $unitPriceWithTax * ( $vatRate / (100 +  $vatRate));

                    $type = 'discount';
                    if($orderTotals['value'] > 0) {
                        $type = 'surcharge';
                    }

                    $otherTotals[] = array(
                        'type'          =>  $type,
                        'name'          =>  $this->formatText($orderTotals['title']),
                        'quantity'      =>  1,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$unitPriceWithTax],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$unitPriceWithTax],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($totalsVATAmount)]
                    );
                }

                $lines = array_merge($lines, $otherTotals);
            }
            
            //Check for rounding off issue in a general way (for all possible totals)
            $orderTotal = $this->numberFormat($amount);
            $orderLineTotal = 0;

            foreach($lines as $line) {
                $orderLineTotal += $line['totalAmount']['value'];
            }
            
            $orderLineTotal = $this->numberFormat($orderLineTotal);
            
            if($orderTotal > $orderLineTotal) {
                $amountDiff = $this->numberFormat(($orderTotal - $orderLineTotal));
                $lineForDiscount[] = array(
                    'type'          =>  'discount',
                    'name'          =>  $this->formatText($this->language->get("roundoff_description")),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'vatRate'       =>  "0",
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                );

                $lines = array_merge($lines, $lineForDiscount);
            }

            if($orderTotal < $orderLineTotal) {
                $amountDiff = $this->numberFormat(-($orderLineTotal - $orderTotal));
                $lineForSurcharge[] = array(
                    'type'          =>  'surcharge',
                    'name'          =>  $this->formatText($this->language->get("roundoff_description")),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'vatRate'       =>  "0",
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                );

                $lines = array_merge($lines, $lineForSurcharge);
            }
            $data['lines'] = $lines;

            // Validate address for missing required fields
            if (!$this->addressCheck($order)) {
                $this->showErrorPage($this->language->get('error_missing_field'));
                return;
            }

            /*
            * This data is sent along for credit card payments / fraud checks. You can remove this but you will
            * have a higher conversion if you leave it here.
            */
            if (version_compare(VERSION, '4.0.1.1', '>')) {
				$payment_address = $this->config->get('config_checkout_payment_address');
			} else {
				$payment_address = $this->config->get('config_checkout_address');
			}
            
            if ($payment_address) {
                $data["billingAddress"] = [
                    "givenName"     =>   $this->formatText($order['payment_firstname']),
                    "familyName"    =>   $this->formatText($order['payment_lastname']),
                    "email"         =>   $this->formatText($order['email']),
                    "streetAndNumber" => $this->formatText($order['payment_address_1'] . ' ' . $order['payment_address_2']),
                    "city" => $this->formatText($order['payment_city']),
                    "region" => $this->formatText($order['payment_zone']),
                    "postalCode" => $this->formatText($order['payment_postcode']),
                    "country" => $this->formatText($order['payment_iso_code_2'])
                ];

                if (isset($order['payment_company']) && !empty($order['payment_company'])) {
                    $data["billingAddress"]['organizationName'] = $this->formatText($order['payment_company']);
                }

                if (isset($order['telephone']) && !empty($order['telephone'])) {
                    //$data["billingAddress"]['phone'] = $this->formatText($order['telephone']);
                }
            }
			
			if (isset($this->session->data['shipping_address'])) {
				if (!empty($order['shipping_firstname']) || !empty($order['shipping_lastname'])) {
					$data["shippingAddress"] = [
						"givenName"     =>   $this->formatText($order['shipping_firstname']),
						"familyName"    =>   $this->formatText($order['shipping_lastname']),
						"email"         =>   $this->formatText($order['email']),
						"streetAndNumber" => $this->formatText($order['shipping_address_1'] . ' ' . $order['shipping_address_2']),
						"city" => $this->formatText($order['shipping_city']),
						"region" => $this->formatText($order['shipping_zone']),
						"postalCode" => $this->formatText($order['shipping_postcode']),
						"country" => $this->formatText($order['shipping_iso_code_2'])
					];

                    if (isset($order['shipping_company']) && !empty($order['shipping_company'])) {
                        $data["shippingAddress"]['organizationName'] = $this->formatText($order['shipping_company']);
                    }

                    if (isset($order['telephone']) && !empty($order['telephone'])) {
                        //$data["shippingAddress"]['phone'] = $this->formatText($order['telephone']);
                    }
				} else {
                    if ($payment_address) {
                        $data["shippingAddress"] = $data["billingAddress"];
                    }
				}
			}

            if (!$payment_address) {
                if (isset($data["shippingAddress"])) {
                    $data["billingAddress"] = $data["shippingAddress"];
                } else {
                    $this->writeToMollieLog("Billing address is turned off, digital orders will not be able to be paid. You can turn on the billing address in settings");

                    $this->showErrorPage($this->language->get('error_missing_field'));
                    return;
                }
            }

            if (strstr(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'), '-')) {
                list ($language, $country) = explode('-', isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                $locale = strtolower($language) . '_' . strtoupper($country);
            } else {
                $locale = strtolower(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) . '_' . strtoupper(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
            }

            if (!in_array($locale, $this->locales)) {
                $locale = $this->config->get($this->mollieHelper->getModuleCode() . "_payment_screen_language");
                if (strstr($locale, '-')) {
                    list ($language, $country) = explode('-', $locale);
                    $locale = strtolower($language) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower($locale) . '_' . strtoupper($locale);
                }
            }

            if((strtolower($locale) == 'en_gb') || (strtolower($locale) == 'en_en')) {
                $locale = 'en_US';
            }

            $data["locale"]=$locale;

            // Debug mode
            if($this->config->get($this->mollieHelper->getModuleCode() . "_debug_mode")) {
                $this->writeToMollieDebugLog("Mollie order creation data :");
                $this->writeToMollieDebugLog($data);
            }

            //Create Order
            $orderObject = $api->orders->create($data);

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating order failed for order_id - " . $order['order_id'] . ' ; ' . htmlspecialchars($e->getMessage()));
            return;
        }

        // Some payment methods can't be cancelled. They need an initial order status.
        if ($this->startAsPending()) {
            $this->addOrderHistory($order, $this->config->get($this->mollieHelper->getModuleCode() . "_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
        }

        if($model->setPayment($order['order_id'], $orderObject->id, $orderObject->method)) {
            $this->writeToMollieLog("Orders API: Order created : order_id - " . $order['order_id'] . ', ' . "mollie_order_id - " . $orderObject->id);
        } else {
            $this->writeToMollieLog("Orders API: Order created for order_id - " . $order['order_id'] . " but mollie_order_id - " . $orderObject->id . " not saved in the database. Should be updated when webhook called.");
        }

        // Redirect to payment gateway.
        $this->redirect($orderObject->_links->checkout->href, 303);
    }

    public function payment()
    {
        // Load essentials
        $this->load->language("extension/mollie/payment/mollie");

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->showErrorPage($this->language->get('warning_secure_connection'));
            $this->writeToMollieLog("Creating payment failed, connection is not secure.");
            return;
        }
        
        try {
            $api = $this->getAPIClient();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating payment failed, API did not load; " . htmlspecialchars($e->getMessage()));
            return;
        }

        $model = $this->getModuleModel();
        $order_id = $this->getOrderID();
        $order = $this->getOpenCartOrder($order_id);

        $currency = $this->getCurrency();
        $amount = $this->convertCurrency($order['total']);

        if(isset($this->config->get($this->mollieHelper->getModuleCode() . "_description")[$this->config->get('config_language_id')])) {
			$description = $this->config->get($this->mollieHelper->getModuleCode() . "_description")[$this->config->get('config_language_id')]['title'];
		} else {
			$description = 'Order %';
		}
        $description = str_replace("%", $order['order_id'], html_entity_decode($description, ENT_QUOTES, "UTF-8"));

        $return_url = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "callback&order_id=" . $order['order_id']);
        $issuer = $this->getIssuer();

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $method = str_replace('mollie_', '', explode('.', $this->session->data['payment_method']['code'])[1]);
        } else {
            $method = str_replace('mollie_', '', $this->session->data['payment_method']);
        }
        
        $method = str_replace('_', '', $method);

        // Check for subscription profiles
        $subscription = false;
        if ($this->cart->hasSubscription()) {
            $subscription = true;
        }

        $singleClickPayment = false;
        if(($method == 'creditcard') && $this->config->get($this->mollieHelper->getModuleCode() . "_single_click_payment")) {
            $mollie_customer_id = $this->createCustomer($order);
            $singleClickPayment = true;
        } elseif ($subscription) {
            $mollie_customer_id = $this->createCustomer($order);
        }

        $mandate = false;
        if (!empty($mollie_customer_id)) {
            $customer = $api->customers->get($mollie_customer_id);
            $mandates = $customer->mandates();
            foreach($mandates as $_mandate) {
                if(($_mandate->isValid()) || ($_mandate->isPending())) {
                    $mandate = true;
                    break;
                }
            }
        }

        try {
            $data = array(
                "amount" => ["currency" => $currency, "value" => (string)$this->numberFormat($amount)],
                "description" => $description,
                "redirectUrl" => $this->formatText($return_url),
                "webhookUrl" => $this->getWebhookUrl(),
                "metadata" => array("order_id" => $order['order_id']),
                "method" => $method,
                "issuer" => $this->formatText($issuer)
            );

            

            if((($singleClickPayment && $mandate) || $subscription) && !empty($mollie_customer_id)) {
                $data['customerId'] = (string)$mollie_customer_id;
            }

            // Additional data for subscription profile
            if($subscription) {
                $data['sequenceType'] = "first";
            }

            // Send cardToken in case of creditcard(if available)
            if (isset($this->request->post['cardToken'])) {
                $data['cardToken'] = $this->request->post['cardToken'];
            }

            // Validate address for missing required fields
            if (!$this->addressCheck($order)) {
                $this->showErrorPage($this->language->get('error_missing_field'));
                return;
            }

            /*
            * This data is sent along for credit card payments / fraud checks. You can remove this but you will
            * have a higher conversion if you leave it here.
            */
            if (version_compare(VERSION, '4.0.1.1', '>')) {
				$payment_address = $this->config->get('config_checkout_payment_address');
			} else {
				$payment_address = $this->config->get('config_checkout_address');
			}

            if ($payment_address) {
                $data["billingAddress"] = [
                    "streetAndNumber" => $this->formatText($order['payment_address_1'] . ' ' . $order['payment_address_2']),
                    "city" => $this->formatText($order['payment_city']),
                    "region" => $this->formatText($order['payment_zone']),
                    "postalCode" => $this->formatText($order['payment_postcode']),
                    "country" => $this->formatText($order['payment_iso_code_2'])
                ];
            }
			
			if (isset($this->session->data['shipping_address'])) {
				if (!empty($order['shipping_firstname']) || !empty($order['shipping_lastname'])) {
					$data["shippingAddress"] = [
						"streetAndNumber" => $this->formatText($order['shipping_address_1'] . ' ' . $order['shipping_address_2']),
						"city" => $this->formatText($order['shipping_city']),
						"region" => $this->formatText($order['shipping_zone']),
						"postalCode" => $this->formatText($order['shipping_postcode']),
						"country" => $this->formatText($order['shipping_iso_code_2'])
					];
				} else {
                    if ($payment_address) {
                        $data["shippingAddress"] = $data["billingAddress"];
                    }
				}
			}

            if (!$payment_address) {
                if (isset($data["shippingAddress"])) {
                    $data["billingAddress"] = $data["shippingAddress"];
                } else {
                    $this->writeToMollieLog("Billing address is turned off, digital orders will not be able to be paid. You can turn on the billing address in settings");

                    $this->showErrorPage($this->language->get('error_missing_field'));
                    return;
                }
            }

            if (strstr(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'), '-')) {
                list ($language, $country) = explode('-', isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                $locale = strtolower($language) . '_' . strtoupper($country);
            } else {
                $locale = strtolower(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) . '_' . strtoupper(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
            }

            if (!in_array($locale, $this->locales)) {
                $locale = $this->config->get($this->mollieHelper->getModuleCode() . "_payment_screen_language");
                if (strstr($locale, '-')) {
                    list ($language, $country) = explode('-', $locale);
                    $locale = strtolower($language) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower($locale) . '_' . strtoupper($locale);
                }
            }

            if((strtolower($locale) == 'en_gb') || (strtolower($locale) == 'en_en')) {
                $locale = 'en_US';
            }

            $data["locale"]=$locale;

            // Debug mode
            if($this->config->get($this->mollieHelper->getModuleCode() . "_debug_mode")) {
                $this->writeToMollieDebugLog("Mollie payment creation data :");
                $this->writeToMollieDebugLog($data);
            }

            //Create Payment
            $paymentObject = $api->payments->create($data);

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating payment failed for order_id - " . $order['order_id'] . ' ; ' . htmlspecialchars($e->getMessage()));
            return;
        }

        // Some payment methods can't be cancelled. They need an initial order status.
        if ($this->startAsPending()) {
            $this->addOrderHistory($order, $this->config->get($this->mollieHelper->getModuleCode() . "_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
        }

        if($model->setPaymentForPaymentAPI($order['order_id'], $paymentObject->id, $paymentObject->method)) {
            $this->writeToMollieLog("Payments API: Payment created : order_id - " . $order['order_id'] . ', ' . "mollie_payment_id - " . $paymentObject->id);
        } else {
            $this->writeToMollieLog("Payments API: Payment created for order_id - " . $order['order_id'] . " but mollie_payment_id - " . $paymentObject->id . " not saved in the database. Should be updated when webhook called.");
        }

        // Redirect to payment gateway.
        $this->redirect($paymentObject->_links->checkout->href, 303);
    }

    /**
     * Some payment methods can't be cancelled. They need 'pending' as an initial order status.
     *
     * @return bool
     */
    protected function startAsPending()
    {
        return false;
    }

    /**
     * This action is getting called by Mollie to report the payment status
     */
    public function webhook()
    {
        if (empty($this->request->post['id'])) {
            header("HTTP/1.0 400 Bad Request");
            $this->writeToMollieLog("Webhook called but no ID received.", true);
            return;
        }

        // Check webhook for order/payment
        $id = $this->request->post['id'];
        $temp = explode('_', $id);
        $idPrefix = $temp[0];
        if($idPrefix == 'ord') {
            $this->webhookForOrder($id);
        } elseif($idPrefix == 'tr') {
            $this->webhookForPayment($id);
        } else {
            $this->webhookForPaymentLink($id);
        }

    }

    private function webhookForPaymentLink($payment_link_id) {
        $moduleCode = $this->mollieHelper->getModuleCode();

        // Load essentials
        $this->load->model("extension/mollie/payment/mollie_payment_link");
        $this->load->model("checkout/order");
        $this->load->language("extension/mollie/payment/mollie");

        $this->writeToMollieLog("Received webhook for payment link : {$payment_link_id}");

        $molliePaymentLink = $this->getAPIClient()->paymentLinks->get($payment_link_id);

        if ($molliePaymentLink->isPaid()) {
            $date_payment = date("Y-m-d H:i:s", strtotime($molliePaymentLink->paidAt));
            $paymentLink = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLink($payment_link_id);

            if ($paymentLink) {
                $this->model_extension_mollie_payment_mollie_payment_link->updatePaymentLink($payment_link_id, $date_payment);

                $new_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));

                if (!$new_status_id) {
                    $this->writeToMollieLog("Webhook for payment link : The payment has been received. No 'processing' status ID is configured, so the order status for order {$paymentLink['order_id']}, {$paymentLink['order_id']} could not be updated.");

                    return;
                }

                $order = $this->model_checkout_order->getOrder($paymentLink['order_id']);

                $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
                
                $this->writeToMollieLog("Webhook for payment link : The payment was received and the order {$paymentLink['order_id']}, {$paymentLink['order_id']} was moved to the 'processing' status (new status ID: {$new_status_id}).");

                return;
            }
        }

        return;
    }

    private function webhookForPayment($payment_id) {

        $moduleCode = $this->mollieHelper->getModuleCode();
        
        // Load essentials
        $this->load->model("checkout/order");
        $model = $this->getModuleModel();
        $this->load->language("extension/mollie/payment/mollie");

        $this->writeToMollieLog("Received webhook for payment : {$payment_id}");

        $molliePayment = $this->getAPIClient()->payments->get($payment_id);

        // Check for subscription payment
        if(isset($molliePayment->subscriptionId) && !empty($molliePayment->subscriptionId)) {
            $firstPaymentDetails = $model->getPaymentBySubscriptionID($molliePayment->subscriptionId);
            if(!empty($firstPaymentDetails)) {
                $data = array(
                    'transaction_id' => $payment_id,
                    'mollie_subscription_id' => $molliePayment->subscriptionId,
                    'mollie_customer_id' => $molliePayment->customerId,
                    'method' => $molliePayment->method,
                    'status' => $molliePayment->status,
                    'amount' => $molliePayment->amount->value,
                    'currency' => $molliePayment->amount->currency,
                    'order_subscription_id' => $firstPaymentDetails['order_subscription_id']
                );
                $model->addSubscriptionPayment($data);
                $this->writeToMollieLog("Webhook for payment(subscription) : mollie_subscription_id - {$molliePayment->subscriptionId}, transaction_id - {$payment_id}, status - {$data['status']}, mollie_customer_id - $molliePayment->customerId");
            }
           
            return;            
        }
        
        if ($molliePayment->orderId) {
            $mollieOrderId = $molliePayment->orderId;
            $mollieOrder = $this->getAPIClient()->orders->get($mollieOrderId);
        } else {
            $mollieOrderId = '';
            $mollieOrder = '';
        }

        //Get order_id of this transaction from db
        if ($mollieOrder != '') {
            $order = $this->model_checkout_order->getOrder($mollieOrder->metadata->order_id);
        } else {
            $order = $this->model_checkout_order->getOrder($molliePayment->metadata->order_id);
        }

        if (empty($order)) {
            header("HTTP/1.0 404 Not Found");
            echo "Could not find order.";
            return;
        }

        //Set transaction ID
        $data = array();

        if($molliePayment) {
            $data = array(
                'payment_id' => $payment_id,
                'status'     => $molliePayment->status,
                'amount'     => $molliePayment->amount->value
            );
        }

        if(!empty($data)) {
            if ($mollieOrderId != '') {
                $model->updatePayment($order['order_id'], $mollieOrderId, $data);
                $this->writeToMollieLog("Webhook for payment : transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order['order_id']}, mollie_order_id - $mollieOrderId");
            } else {
                $model->updatePaymentForPaymentAPI($order['order_id'], $payment_id, $data);
                $this->writeToMollieLog("Webhook for payment : transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order['order_id']}");
            }
        }

        if($order['order_status_id'] != 0) {
            //Check for refund
            if(isset($molliePayment->amountRefunded->value) && ($molliePayment->amountRefunded->value > 0)) {
                $data = array(
                    'payment_id' => $payment_id,
                    'status'     => 'refunded',
                    'amount'     => $molliePayment->amount->value
                );

                if(!empty($data)) {
                    if ($mollieOrderId != '') {
                        $model->updatePayment($order['order_id'], $mollieOrderId, $data);
                        $this->writeToMollieLog("Webhook for payment : Updated mollie payment. transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order['order_id']}, mollie_order_id - $mollieOrderId");
                    } else {
                        $model->updatePaymentForPaymentAPI($order['order_id'], $payment_id, $data);
                        $this->writeToMollieLog("Webhook for payment : Updated mollie payment. transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order['order_id']}");
                    }
                }

                $this->writeToMollieLog("Webhook for payment : Order status has been updated to 'Refunded' for order - {$order['order_id']}, {$mollieOrderId}");
            } else {
                if (!empty($order['order_status_id']) && $order['order_status_id'] == $this->config->get($moduleCode . "_ideal_refund_status_id")) {
                    $data['refund_id'] = '';
                    $model->cancelReturn($order['order_id'], $mollieOrderId, $data);
                    $this->addOrderHistory($order, $this->config->get($moduleCode . "_ideal_processing_status_id"), $this->language->get("refund_cancelled"), true);
                    $this->writeToMollieLog("Webhook for payment : Refund has been cancelled for order - {$order['order_id']}, {$mollieOrderId}, {$payment_id}");
                    $this->writeToMollieLog("Webhook for payment : Order status has been updated to 'Processing' for order - {$order['order_id']}, {$mollieOrderId}, {$payment_id}");
                }
            }
        }

        // Only process the status if the order is stateless or in 'pending' status.
        if (!empty($order['order_status_id']) && $order['order_status_id'] != $this->config->get($moduleCode . "_ideal_pending_status_id")) {
            $this->writeToMollieLog("Webhook for payment : The order {$order['order_id']}, {$mollieOrderId}, {$payment_id} was already processed before (order status ID: " . intval($order['order_status_id']) . ")");
            return;
        }

        // Order paid ('processed').
        if ($molliePayment->isPaid()) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment : The payment has been received. No 'processing' status ID is configured, so the order status for order {$order['order_id']}, {$order['order_id']} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
            $this->writeToMollieLog("Webhook for payment : The payment was received and the order {$order['order_id']}, {$order['order_id']} was moved to the 'processing' status (new status ID: {$new_status_id}).");
            return;
        }

        // Payment cancelled.
        if ($molliePayment->status == PaymentStatus::STATUS_CANCELED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_canceled_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment : The payment was cancelled. No 'cancelled' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId}, {$payment_id} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
            $this->writeToMollieLog("Webhook for payment : The payment was cancelled and the order {$order['order_id']}, {$mollieOrderId}, {$payment_id} was moved to the 'cancelled' status (new status ID: {$new_status_id}).");
            return;
        }

        // Payment expired.
        if ($molliePayment->status == PaymentStatus::STATUS_EXPIRED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_expired_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment : The payment expired. No 'expired' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId}, {$payment_id} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
            $this->writeToMollieLog("Webhook for payment : The payment expired and the order {$order['order_id']}, {$mollieOrderId}, {$payment_id} was moved to the 'expired' status (new status ID: {$new_status_id}).");
            return;
        }

        // Otherwise, payment failed.
        $new_status_id = intval($this->config->get($moduleCode . "_ideal_failed_status_id"));

        if (!$new_status_id) {
            $this->writeToMollieLog("Webhook for payment : The payment failed. No 'failed' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId}, {$payment_id} could not be updated.");
            return;
        }
        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_unknown"), false);
        $this->writeToMollieLog("Webhook for payment : The payment failed for an unknown reason and the order {$order['order_id']}, {$mollieOrderId}, {$payment_id} was moved to the 'failed' status (new status ID: {$new_status_id}).");
        return;

    }

    private function webhookForOrder($order_id) {

        $moduleCode = $this->mollieHelper->getModuleCode();

        $this->writeToMollieLog("Received webhook for order : {$order_id}");

        $mollieOrder = $this->getAPIClient()->orders->get($order_id, ["embed" => "payments"]);

        // Check if order_id is saved in database
        $model = $this->getModuleModel();
        $mollieOrderIdExists = $model->checkMollieOrderID($order_id);
        if(!$mollieOrderIdExists) {
            $model->setPayment($mollieOrder->metadata->order_id, $order_id, $mollieOrder->method);
            $this->writeToMollieLog("Webhook for order : Updated database. order_id - {$mollieOrder->metadata->order_id}, mollie_order_id - {$order_id}");
        }

        // Update payment status
        if(!empty($mollieOrder->_embedded->payments)) {
            $payment = $mollieOrder->_embedded->payments[0];
            $paymentData = array(
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'amount'     => $payment->amount->value
            );
            $model->updatePayment($mollieOrder->metadata->order_id, $order_id, $paymentData);
            $this->writeToMollieLog("Webhook for order : Updated mollie payment. transaction_id - {$payment->id}, status - {$paymentData['status']}, order_id - {$mollieOrder->metadata->order_id}, mollie_order_id - $order_id");            
        }

        // Load essentials
        $this->load->model("checkout/order");
        $this->getModuleModel();
        $this->load->language("extension/mollie/payment/mollie");

        //Get order_id of this transaction from db
        $order = $this->model_checkout_order->getOrder($mollieOrder->metadata->order_id);

        if (empty($order)) {
            header("HTTP/1.0 404 Not Found");
            echo "Could not find order.";
            return;
        }

        // Only process the status if the order is stateless or in 'pending' status.
        if (!empty($order['order_status_id']) && $order['order_status_id'] != $this->config->get($moduleCode . "_ideal_pending_status_id")) {
            $this->writeToMollieLog("Webhook for order : The order {$order['order_id']}, {$order_id} was already processed before (order status ID: " . intval($order['order_status_id']) . ")");
            return;
        }

        // Order paid ('processed').
        if ($mollieOrder->isPaid() || $mollieOrder->isAuthorized()) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for order : The payment has been received/authorised. No 'processing' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
            $this->writeToMollieLog("Webhook for order : The payment was received/authorised and the order {$order['order_id']}, {$order_id} was moved to the 'processing' status (new status ID: {$new_status_id}).");
            return;
        }

        // Order cancelled.
        if ($mollieOrder->status == PaymentStatus::STATUS_CANCELED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_canceled_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for order : The payment was cancelled. No 'cancelled' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
            $this->writeToMollieLog("Webhook for order : The payment was cancelled and the order {$order['order_id']}, {$order_id} was moved to the 'cancelled' status (new status ID: {$new_status_id}).");
            return;
        }

        // Order expired.
        if ($mollieOrder->status == PaymentStatus::STATUS_EXPIRED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_expired_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for order : The payment expired. No 'expired' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.");
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
            $this->writeToMollieLog("Webhook for order : The payment expired and the order {$order['order_id']}, {$order_id} was moved to the 'expired' status (new status ID: {$new_status_id}).");
            return;
        }

        // Otherwise, order failed.
        $new_status_id = intval($this->config->get($moduleCode . "_ideal_failed_status_id"));

        if (!$new_status_id) {
            $this->writeToMollieLog("Webhook for order : The payment failed. No 'failed' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.");
            return;
        }
        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_unknown"), false);
        $this->writeToMollieLog("Webhook for order : The payment failed for an unknown reason and the order {$order['order_id']}, {$order_id} was moved to the 'failed' status (new status ID: {$new_status_id}).");
        return;

    }

    /**
     * Gets called via AJAX from the checkout form to store the selected issuer.
     */
    public function set_issuer()
    {
        if (!empty($this->request->post['mollie_issuer_id'])) {
            $this->session->data['mollie_issuer'] = $this->request->post['mollie_issuer_id'];
        } else {
            $this->session->data['mollie_issuer'] = null;
        }

        echo $this->session->data['mollie_issuer'];
    }

    /**
     * Retrieve the issuer if one was selected. Return null otherwise.
     *
     * @return string|null
     */
    protected function getIssuer()
    {
        if (!empty($this->request->post['mollie_issuer'])) {
            return $this->request->post['mollie_issuer'];
        }

        if (!empty($this->session->data['mollie_issuer'])) {
            return $this->session->data['mollie_issuer'];
        }

        return null;
    }

    //Create shipment after the order reach to a specific status
    public function createShipment(&$route, &$data, $orderID = "", $orderStatusID = "") {
        
        if (!empty($data)) {
            $order_id = $data[0];
            $order_status_id = $data[1];
        }
        else {
            $order_id = $orderID;
            $order_status_id = $orderStatusID;
        }
        
        $moduleCode = $this->mollieHelper->getModuleCode();

        $this->load->model("checkout/order");
        $orderModel = $this->model_checkout_order;
        $this->load->model('extension/mollie/payment/mollie_ideal');
        $mollieModel = $this->model_extension_mollie_payment_mollie_ideal;
        
        $this->load->language("extension/mollie/payment/mollie");

        //Get order_id of this transaction from db
        $order = $orderModel->getOrder($order_id);
        if (empty($order)) {
            header("HTTP/1.0 404 Not Found");
            echo "Could not find order.";
            return;
        }

        $mollie_order_id = $mollieModel->getOrderID($order_id);
        if (empty($mollie_order_id)) {
            $this->writeToMollieLog("Could not find mollie reference order id for shipment creation for order {$order['order_id']} (It could be a non-mollie order or the payments api has been used to create the order).");
            return;
        }
        
        /*Check if shipment is not created already at the time of order creation
        $this->config->get($moduleCode . "_create_shipment")
        -> '!= 1' (Shipment is not created already)
        -> '== 2' (Shipment needs to be created after one of the statuses set in the module setting)
        -> else, (Shipment needs to be created after one of the 'Order Complete Statuses' set in the store setting)
        */

        $mollieOrder = $this->getAPIClient()->orders->get($mollie_order_id);
        if(($mollieOrder->isAuthorized() || $mollieOrder->isPaid()) && ($this->config->get($moduleCode . "_create_shipment") != 1)) {
            if($this->config->get($moduleCode . "_create_shipment") == 2) {
                $shipment_status_id = $this->config->get($moduleCode . "_create_shipment_status_id");
            }
            else {
                $order_complete_statuses = array();
                $statuses = $this->config->get('config_complete_status') ?: (array)$this->config->get('config_complete_status_id');
                foreach($statuses as $status_id) {
                    $order_complete_statuses[] = $status_id;
                }
            }

            if(((isset($shipment_status_id) && $order_status_id == $shipment_status_id)) || ((isset($order_complete_statuses) && in_array($order_status_id, $order_complete_statuses)))) {
                try {
                    //Shipment lines
                    $shipmentLine = array();
                    foreach($mollieOrder->lines as $line) {
                        $shipmentLine[] = array(
                            'id'        =>  $line->id,
                            'quantity'  =>  $line->quantity
                        );
                    }

                    $shipmentData['lines'] = $shipmentLine;
                    $mollieShipment = $mollieOrder->createShipment($shipmentData);
                    $this->writeToMollieLog("Shipment created for order - {$order_id}, {$mollie_order_id}");
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $this->writeToMollieLog("Shipment could not be created for order - {$order_id}, {$mollie_order_id}; " . htmlspecialchars($e->getMessage()));
                }
            }
        }
    }

    /**
     * Customer returning from the bank with an transaction_id
     * Depending on what the state of the payment is they get redirected to the corresponding page
     *
     * @return string
     */
    public function callback()
    {
        $order_id = $this->getOrderID();
        $this->writeToMollieLog("Received callback for order : " . $order_id);

        $moduleCode = $this->mollieHelper->getModuleCode();
        if ($order_id === false) {
            $this->writeToMollieLog("Callback : Failed to get order id.");

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        $order = $this->getOpenCartOrder($order_id);

        if (empty($order)) {
            $this->writeToMollieLog("Failed to get order for order id: " . $order_id);

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        // Load required translations.
        $this->load->language("extension/mollie/payment/mollie");

        // Double-check whether or not the status of the order is correct.
        $model = $this->getModuleModel();
        $this->load->model('checkout/order');

        $paid_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));
        $pending_status_id = intval($this->config->get($moduleCode . "_ideal_pending_status_id"));
        $mollie_order_id = $model->getOrderID($order['order_id']);
        $mollie_payment_id = $model->getPaymentID($order['order_id']);

        if (!($mollie_order_id) && !($mollie_payment_id)) {
            $this->writeToMollieLog("Error getting mollie_order_id / mollie_payment_id for order " . $order['order_id']);

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        if (!empty($mollie_order_id)) {
            $orderDetails = $this->getAPIClient()->orders->get($mollie_order_id, ["embed" => "payments"]);
        } else {
            $orderDetails = $this->getAPIClient()->payments->get($mollie_payment_id);
        }

        // Create subscriptions if any
        $mollie_customer_id = $model->getMollieCustomer($order['email']);        
        if(!empty($mollie_customer_id) && $orderDetails->isPaid()) {
            if(!empty($orderDetails->_embedded->payments)) {
                $payment = $orderDetails->_embedded->payments[0];

                if ($payment->mandateId) {
                    $mandate_id = $payment->mandateId;

                    $api = $this->getAPIClient();
                    $customer = $api->customers->get($mollie_customer_id);
                    $mandates = $customer->mandates();
                    foreach($mandates as $mandate) {
                        if((($mandate->isValid()) || ($mandate->isPending())) && ($mandate->id == $mandate_id)) {
                            $order_products = $this->model_checkout_order->getProducts($order_id);
                            foreach ($order_products as $product) {
                                // Subscription
                                $order_subscription_info = $this->model_checkout_order->getSubscription($order_id, $product['order_product_id']);

                                if ($order_subscription_info) {
                                    $unit_price = $order_subscription_info['price'] + $order_subscription_info['tax'];
                        
                                    $total = $this->numberFormat($this->convertCurrency($unit_price));
                                    $duration = $order_subscription_info['duration'];
                                    $cycle = $order_subscription_info['cycle'];    
                                    switch ($order_subscription_info['frequency']) {
                                        case 'day':
                                            $frequency = 'day';
                                            break;
                                        case 'week':
                                            $frequency = 'week';
                                            break;
                                        case 'semi_month':
                                            $frequency = 'day';
                                            $cycle = $cycle * 15;
                                            break;
                                        case 'year':
                                            $frequency = 'month';
                                            $cycle = $cycle * 12;
                                            break;                                
                                        default:
                                            $frequency = 'month';
                                            break;
                                    }      
                                    $interval = ($cycle > 1) ? $cycle . ' ' .  $frequency . 's' : $cycle . ' ' .  $frequency;
                                    $subscription_start = new \DateTime('now');
                                                                
                                    $data = array(
                                        "amount" => ["currency" => $this->getCurrency(), "value" => (string)$this->numberFormat($total)],
                                        "times" => $duration,
                                        "interval" => $interval,
                                        "mandateId" => $mandate->id,
                                        "startDate" => date_format($subscription_start->modify('+' . $cycle . ' ' . $frequency), 'Y-m-d'),
                                        "description" => sprintf($this->language->get('text_subscription_desc'), $order['order_id'], $order['store_name'], date('Y-m-d H:i:s'), $interval, $product['name']),
                                        "webhookUrl" => $this->getWebhookUrl() 
                                    );

                                    if ($duration <= 0) {
                                        unset($data['times']);
                                    }

                                    try {
                                        $subscription = $customer->createSubscription($data);
                                        $this->writeToMollieLog("Subscription created: mollie_subscription_id - {$subscription->id}, order_id - {$order['order_id']}");

                                        // Add to subscription profile
                                        $model->subscriptionPayment($order_subscription_info, $subscription->id, $mollie_order_id, $mollie_payment_id);
                                    } catch (\Mollie\Api\Exceptions\ApiException $e) {
                                        $this->showErrorPage(htmlspecialchars($e->getMessage()));
                                        $this->writeToMollieLog("Creating subscription failed for order_id - " . $order['order_id'] . ' ; ' . htmlspecialchars($e->getMessage()));
                                    }     
                                }                   
                            }

                            break;
                        }                
                    }
                }
            }
        }

        // Update payment status
        if (!empty($mollie_order_id)) {
            if(!empty($orderDetails->_embedded->payments)) {
                $payment = $orderDetails->_embedded->payments[0];
                $paymentData = array(
                    'payment_id' => $payment->id,
                    'status'     => $payment->status,
                    'amount'     => $payment->amount->value
                );
                $model->updatePayment($orderDetails->metadata->order_id, $mollie_order_id, $paymentData);
                $this->writeToMollieLog("Updated mollie payment. transaction_id - {$payment->id}, status - {$paymentData['status']}, order_id - {$orderDetails->metadata->order_id}, mollie_order_id - $mollie_order_id");
                            
            }
        } elseif (!empty($mollie_payment_id)) {
            $payment = $orderDetails;
            $paymentData = array(
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'amount'     => $payment->amount->value
            );
            $model->updatePaymentForPaymentAPI($payment->metadata->order_id, $mollie_payment_id, $paymentData);
            $this->writeToMollieLog("Updated mollie payment. transaction_id - {$payment->id}, status - {$paymentData['status']}, order_id - {$payment->metadata->order_id}, mollie_payment_id - $mollie_payment_id");
        }

        $orderStatuses = $model->getOrderStatuses($order['order_id']);

        if (!empty($mollie_order_id)) {
            if (($orderDetails->isPaid() || $orderDetails->isAuthorized()) && !in_array($paid_status_id, $orderStatuses)) {
                $this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
                $order['order_status_id'] = $paid_status_id;
            } else if(!empty($orderDetails->_embedded->payments)) {    
                $payment = $orderDetails->_embedded->payments[0];
                if (($payment->status == 'paid') && !in_array($paid_status_id, $orderStatuses)) {
                    $this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
                    $order['order_status_id'] = $paid_status_id;
                } elseif(($payment->status == 'open') && !in_array($pending_status_id, $orderStatuses)) {
                    $this->addOrderHistory($order, $pending_status_id, $this->language->get("response_none"), true);
                    $order['order_status_id'] = $pending_status_id;
                }                
            }
        } else {
            if ($orderDetails->isPaid() && !in_array($paid_status_id, $orderStatuses)) {
                $this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
                $order['order_status_id'] = $paid_status_id;
            } else {    
                $payment = $orderDetails;
                if (($payment->status == 'paid') && !in_array($paid_status_id, $orderStatuses)) {
                    $this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
                    $order['order_status_id'] = $paid_status_id;
                } elseif(($payment->status == 'open') && !in_array($pending_status_id, $orderStatuses)) {
                    $this->addOrderHistory($order, $pending_status_id, $this->language->get("response_none"), true);
                    $order['order_status_id'] = $pending_status_id;
                }                
            }
        }

        /* Check module module setting for shipment creation,
        $this->config->get($moduleCode . "_create_shipment")) == 1,
        satisfies the 'Create shipment immediately after order creation' condition. */
        if (!empty($mollie_order_id)) {
            if(($orderDetails->isPaid() || $orderDetails->isAuthorized()) && ($this->config->get($moduleCode . "_create_shipment")) == 1) {
                try {
                    //Shipment lines
                    $shipmentLine = array();
                    foreach($orderDetails->lines as $line) {
                        $shipmentLine[] = array(
                            'id'        =>  $line->id,
                            'quantity'  =>  $line->quantity
                        );
                    }

                    $shipmentData['lines'] = $shipmentLine;
                    $mollieShipment = $orderDetails->createShipment($shipmentData);
                    $shipped_status_id = intval($this->config->get($moduleCode . "_ideal_shipping_status_id"));
                    $this->addOrderHistory($order, $shipped_status_id, $this->language->get("shipment_success"), true);
                    $this->writeToMollieLog("Shipment created for order - {$order_id}, {$mollie_order_id}");
                    $order['order_status_id'] = $shipped_status_id;
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $this->writeToMollieLog("Shipment could not be created for order - {$order_id}, {$mollie_order_id}; " . htmlspecialchars($e->getMessage()));
                }                
            }
        }
        // Show a 'transaction failed' page if we couldn't find the order or if the payment failed.
        $failed_status_id = $this->config->get($moduleCode . "_ideal_failed_status_id");

        if (!$order || ($failed_status_id && $order['order_status_id'] == $failed_status_id)) {
            if ($failed_status_id && $order['order_status_id'] == $failed_status_id) {
                $this->writeToMollieLog("Error payment failed for order - {$order['order_id']}, {$mollie_order_id}");
            } else {
                $this->writeToMollieLog("Error couldn't find order - {$order['order_id']}, {$mollie_order_id}");
            }

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        // If the order status is 'processing' (i.e. 'paid'), redirect to OpenCart's default 'success' page.
        if ($order["order_status_id"] == $this->config->get($moduleCode . "_ideal_processing_status_id") || $order["order_status_id"] == $this->config->get($moduleCode . "_ideal_shipping_status_id")) {
            $this->writeToMollieLog("Success redirect to success page for order - {$order['order_id']}, {$mollie_order_id}");

            unset($this->session->data['mollie_issuer']);

            // Redirect to 'success' page.
            $this->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true));
            return '';
        }

        // If the status is 'pending' (i.e. a bank transfer), the report is not delivered yet.
        if ($order['order_status_id'] == $this->config->get($moduleCode . "_ideal_pending_status_id")) {
            $this->writeToMollieLog("Unknown payment status for order - {$order['order_id']}, {$mollie_order_id}, {$mollie_payment_id}");

            if ($this->cart) {
                $this->cart->clear();
            }

            return $this->showReturnPage(
                $this->language->get("heading_unknown"),
                $this->language->get("msg_unknown"),
                null,
                false
            );
        }

        // The status is probably 'cancelled'. Allow the admin to redirect their customers back to the shopping cart directly in these cases.
        if (!(bool)$this->config->get($moduleCode . "_show_order_canceled_page")) {
            $this->redirect($this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'), true));
        }

        // Show a 'transaction failed' page if all else fails.
        $this->writeToMollieLog("Everything else failed for order - {$order['order_id']}, {$mollie_order_id}, {$mollie_payment_id}");

        return $this->showReturnPage(
            $this->language->get("heading_failed"),
            $this->language->get("msg_failed")
        );
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function showErrorPage($message)
    {
        $this->load->language("extension/mollie/payment/mollie");

        $this->log->write("Error setting up transaction with Mollie: {$message}.");

        $showReportButton = false;
        if (isset($this->session->data['admin_login'])) {
            $showReportButton = true;

        }

        return $this->showReturnPage(
            $this->language->get("heading_error"),
            $this->language->get("text_error"),
            $message,
            true,
            $showReportButton
        );
    }

    /**
     * Render a return page.
     *
     * @param string $title The title of the status page.
     * @param string $body The status message.
     * @param string|null $api_error Show an API error, if applicable.
     * @param bool $show_retry_button Show a retry button that redirects the customer back to the checkout page.
     *
     * @return string
     */
    protected function showReturnPage($title, $body, $api_error = null, $show_retry_button = true, $show_report_button = false)
    {
        $this->load->language("extension/mollie/payment/mollie");

        $data['message_title'] = $title;
        $data['message_text'] = $body;

        if ($api_error) {
            $data['mollie_error'] = $api_error;
        }

        if ($show_retry_button) {
            $data['checkout_url'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'), true);
            $data['button_retry'] = $this->language->get("button_retry");
        }

        $data['show_retry_button'] = $show_retry_button;
        $data['method_separator']  = $this->getMethodSeparator();

        $data['button_continue'] = $this->language->get('button_continue');
		$data['continue'] = $this->url->link('common/home');

        $data['show_report_button'] = $show_report_button;
        $data['button_report'] = $this->language->get("button_report");
        $data['button_submit'] = $this->language->get("button_submit");

        $this->document->setTitle($this->language->get('ideal_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

        $data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/mollie/payment/mollie_return', $data));
    }

    /**
     * We check for and remove the admin url in the webhook link.
     *
     * @return string|null
     */
    public function getWebhookUrl()
    {
        $system_webhook_url = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "webhook");

        if (strpos($system_webhook_url, $this->getAdminDirectory()) !== false) {
            return str_replace($this->getAdminDirectory(), "", $system_webhook_url);
        }

        return $system_webhook_url ? $system_webhook_url : null;
    }

    /**
     * Retrieves the admin directoryname from the catalog and admin urls.
     *
     * @return string
     */
    protected function getAdminDirectory()
    {
        // if no default admin URL defined in the config, use the default admin directory.
        if (!defined('HTTP_ADMIN')) {
            return "admin/";
        }

        return str_replace(HTTP_SERVER, "", HTTP_ADMIN);
    }

    /**
     * Map payment status history handling for different Opencart versions.
     *
     * @param array $order
     * @param int|string $order_status_id
     * @param string $comment
     * @param bool $notify
     */
    protected function addOrderHistory($order, $order_status_id, $comment = "", $notify = false)
    {
        $this->model_checkout_order->addHistory($order['order_id'], $order_status_id, $comment, $notify);
    }

    /**
     * @param string $url
     * @param int $status
     */
    protected function redirect($url, $status = 302)
    {
        $this->response->redirect($url, $status);
    }

    private function createCustomer($data) {
        $model = $this->getModuleModel();

        $api = $this->getAPIClient();
        
        // Check if customer already exists
        $mollie_customer_id = $model->getMollieCustomer($data['email']);
        if(!empty($mollie_customer_id)) {
            try {
                $customer = $api->customers->get($mollie_customer_id);
                return $mollie_customer_id;
            } catch (Mollie\Api\Exceptions\ApiException $e) {
                // Remove customer from database
                $model->deleteMollieCustomer($data['email']);

                $this->writeToMollieLog("Customer does not exist, will be created: " . htmlspecialchars($e->getMessage()));
            }
        }

        $_data = array(
            "name" => $data['firstname'] . ' ' . $data['lastname'],
            "email" => $data['email'],
            "metadata" => array("customer_id" => $data['customer_id']),
        );

        $api = $this->getAPIClient();
        $customer = $api->customers->create($_data);
        if(!empty($customer->id)) {
            $customerData = array(
                "mollie_customer_id" => $customer->id,
                "customer_id" => $data['customer_id'],
                "email" => $data['email']
            );
            $model->addCustomer($customerData);
            $mollie_customer_id = $customer->id;
        } else {
            $mollie_customer_id = '';
        }        
        $this->writeToMollieLog("Customer created: mollie_customer_id - {$customer->id}, customer_id - {$data['customer_id']}");

        return $mollie_customer_id;
    }

    public function setApplePaySession() {
        $this->session->data['applePay'] = isset($this->request->post['apple_pay']) ? $this->request->post['apple_pay'] : 0;
        sleep(1);

        return true;
    }

    public function reportError() {
        $json = array();
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['mollie_error'])) {
            $this->load->language("extension/mollie/payment/mollie");

            $name = $this->config->get('config_name');
            $email = $this->config->get('config_email');
            $subject = 'Mollie Error: Front-end mollie error report';
            $message = $this->request->post['mollie_error'];
            $message .= "<br>Opencart version : " . VERSION;
            $message .= "<br>Mollie version : " . \MollieHelper::PLUGIN_VERSION;

            if ($this->config->get('config_mail_engine')) {
                if (version_compare(VERSION, '4.0.2.0', '<')) {
                    $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        
                    $mail->setTo('support.mollie@qualityworks.eu');
                    $mail->setFrom($email);
                    $mail->setSender($name);
                    $mail->setSubject($subject);
                    $mail->setHtml($message);
                } else {
                    $mail_option = [
                        'parameter'     => $this->config->get('config_mail_parameter'),
                        'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
                        'smtp_username' => $this->config->get('config_mail_smtp_username'),
                        'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
                        'smtp_port'     => $this->config->get('config_mail_smtp_port'),
                        'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
                    ];
        
                    $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
                    $mail->setTo('support.mollie@qualityworks.eu');
                    $mail->setFrom($email);
                    $mail->setSender($name);
                    $mail->setSubject($subject);
                    $mail->setHtml($message);
                }

                $file = DIR_LOGS . 'Mollie.log';
                if (file_exists($file) && filesize($file) < 2147483648) {
                    $mail->addAttachment($file);
                }

                $file = DIR_LOGS . 'error.log';
                if (file_exists($file) && filesize($file) < 2147483648) {
                    $mail->addAttachment($file);
                }

                $mail->send();
            }

            $json['success'] = $this->language->get('text_error_report_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function payLinkCallback() {
        $this->load->language("extension/mollie/payment/mollie");

        $payment_success = false;

        $order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
        if ($order_id > 0) {
            $moduleCode = $this->mollieHelper->getModuleCode();

            // Load essentials
            $this->load->model("extension/mollie/payment/mollie_payment_link");
            $this->load->model("checkout/order");

            $paymentLink = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLinkByOrderID($order_id);
            if (!empty($paymentLink)) {
                $payment_link_id = $paymentLink['payment_link_id'];

                $this->writeToMollieLog("Received callback for payment link : {$payment_link_id}");

                $molliePaymentLink = $this->getAPIClient()->paymentLinks->get($payment_link_id);

                if ($molliePaymentLink->isPaid()) {
                    $payment_success = true;
                    
                    if (!$paymentLink['date_payment']) {
                        $date_payment = date("Y-m-d H:i:s", strtotime($molliePaymentLink->paidAt));
                    
                        $this->model_extension_mollie_payment_mollie_payment_link->updatePaymentLink($payment_link_id, $date_payment);

                        $new_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));

                        if (!$new_status_id) {
                            $this->writeToMollieLog("Callback for payment link : The payment has been received. No 'processing' status ID is configured, so the order status for order {$paymentLink['order_id']}, {$paymentLink['order_id']} could not be updated.");

                            return;
                        }

                        $order = $this->model_checkout_order->getOrder($paymentLink['order_id']);

                        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
                        
                        $this->writeToMollieLog("Callback for payment link : The payment was received and the order {$paymentLink['order_id']}, {$paymentLink['order_id']} was moved to the 'processing' status (new status ID: {$new_status_id}).");
                    }
                }
            }
        }

        if ($payment_success) {
            $title = $this->language->get("heading_payment_success");
            $text = $this->language->get("text_payment_success");
        } else {
            $title = $this->language->get("heading_payment_failed");
            $text = $this->language->get("text_payment_failed");
        }

        return $this->showReturnPage(
            $title,
            $text,
            null,
            false,
            false
        );        
    }

    // Credit Order
    public function creditOrder() {
        $json = array();
        $no_stock_mutation = array();

        if (!$json) {
            $this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = (int)$this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if (!empty($order_info)) {
                $creditData = $order_info;

                $creditData['products'] = array();

                $credit_product = array();

                if (isset($this->request->post['productline']) && !empty($this->request->post['productline'])) {
                    foreach ($this->request->post['productline'] as $order_product_id => $line) {
                        if (isset($line['selected'])) {
                            $credit_product[$order_product_id] = array(
                                "order_product_id" => $order_product_id,
                                "quantity" => $line['quantity']
                            );
                        }
    
                        if (!isset($line['stock_mutation'])) {
                            $no_stock_mutation[] = $order_product_id;
                        }
                    }
                }

                $order_sub_total = 0;
                $order_tax = 0;
                $order_total = 0;

                $order_products = $this->model_checkout_order->getProducts($order_id);
                foreach ($order_products as $product) {
                    if (!empty($credit_product)) {
                        if (array_key_exists($product['order_product_id'], $credit_product)) {
                            $quantity = $credit_product[$product['order_product_id']]['quantity'];
                            $price = $product['price'];
                            $tax = $product['tax'];
    
                            $order_sub_total += $price * $quantity;
                            $order_tax += $tax * $quantity;
    
                            $stock_mutation = true;
                            if (in_array($product['order_product_id'], $no_stock_mutation)) {
                                $stock_mutation = false;
                            }
    
                            $creditData['products'][] = array(
                                'product_id' => $product['product_id'],
                                'master_id'  => $product['master_id'],
                                'name'       => $product['name'],
                                'model'      => $product['model'],
                                'subscription' => false,
                                'option'     => $this->model_checkout_order->getOptions($order_id, $product['order_product_id']),
                                'quantity'   => -$quantity,
                                'price'      => $price,
                                'total'      => -($price * $quantity),
                                'tax'        => $tax,
                                'stock_mutation' => $stock_mutation,
                                'reward'     => -$product['reward']
                            );
                        }
                    } else {
                        $quantity = $product['quantity'];
                        $price = $product['price'];
                        $tax = $product['tax'];

                        $order_sub_total += $price * $quantity;
                        $order_tax += $tax * $quantity;

                        $stock_mutation = true;

                        $creditData['products'][] = array(
                            'product_id' => $product['product_id'],
                            'master_id'  => $product['master_id'],
                            'name'       => $product['name'],
                            'model'      => $product['model'],
                            'subscription' => false,
                            'option'     => $this->model_checkout_order->getOptions($order_id, $product['order_product_id']),
                            'quantity'   => -$quantity,
                            'price'      => $price,
                            'total'      => -($price * $quantity),
                            'tax'        => $tax,
                            'stock_mutation' => $stock_mutation,
                            'reward'     => -$product['reward']
                        );
                    }
                }

                $order_total = $order_sub_total + $order_tax;

                $creditData['total'] = -$order_total;

                $creditData['totals'] = array();
                $order_totals = $this->model_checkout_order->getTotals($order_id);
                foreach ($order_totals as $_order_total) {
                    if ($_order_total['code'] == 'sub_total') {
                        $creditData['totals'][] = array(
                            "extension" => $_order_total['extension'],
                            "code" => $_order_total['code'],
                            "title" => $_order_total['title'],
                            "value" => -$order_sub_total,
                            "sort_order" => $_order_total['sort_order']
                        );
                    } elseif ($_order_total['code'] == 'tax') {
                        $creditData['totals'][] = array(
                            "extension" => $_order_total['extension'],
                            "code" => $_order_total['code'],
                            "title" => $_order_total['title'],
                            "value" => -$order_tax,
                            "sort_order" => $_order_total['sort_order']
                        );
                    } elseif ($_order_total['code'] == 'total') {
                        $creditData['totals'][] = array(
                            "extension" => $_order_total['extension'],
                            "code" => $_order_total['code'],
                            "title" => $_order_total['title'],
                            "value" => -$order_total,
                            "sort_order" => $_order_total['sort_order']
                        );
                    }
                }

                $credit_order_id = $this->model_checkout_order->addOrder($creditData);

                $this->model_checkout_order->addHistory($credit_order_id, $this->config->get('config_order_status_id'));

                $json['success'] = true;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function checkoutController(&$route, &$args) {
        $this->document->addScript('extension/mollie/catalog/view/javascript/mollie.js');
        $this->document->addScript('https://js.mollie.com/v1/mollie.js');
    }

    public function loginController(&$route, &$args) {
        if (isset($this->session->data['user_token']) && !empty($this->session->data['user_token'])) {
            $this->session->data['admin_login'] = true;
        }
    }

    public function mailOrderController(&$route, &$data, &$template_code) {
        $this->load->model('checkout/order');

        $data['payment_link_order_email'] = false;

        $order_info = $this->model_checkout_order->getOrder((int)$data['order_id']);
        if (!empty($order_info)) {
            if (version_compare(VERSION, '4.0.1.1', '>')) {
                $payment_code = $order_info['payment_method']['code'];
            } else {
                $payment_code = $order_info['payment_code'];
            }

            if (str_contains($payment_code, 'mollie_payment_link')) {
                $this->load->model('extension/mollie/payment/mollie_payment_link');

                $result = $this->model_extension_mollie_payment_mollie_payment_link->sendPaymentLink($order_info);

                if (isset($result['payment_link'])) {
                    $data['payment_link_order_email'] = true;
                    $data['payment_link'] = $result['payment_link'];
                }
            }
        }
    }

    public function checkoutPaymentMethodController(&$route, &$data, &$template_code) {
        if (str_contains($data['code'], 'mollie')) {
            $this->load->language("extension/mollie/payment/mollie");

            $payment_method = explode('_', explode('.', $data['code'])[1])[1];

            $description = $this->language->get('method_' . $payment_method);

            // Custom title
            $moduleCode = $this->mollieHelper->getModuleCode();
            
            if(isset($this->config->get($moduleCode . "_" . $payment_method . "_description")[$this->config->get('config_language_id')])) {
                $title = $this->config->get($moduleCode . "_" . $payment_method . "_description")[$this->config->get('config_language_id')]['title'];
            } else {
                $title = $description;
            }

            $data['payment_method'] = $title;
        }
    }

    public function mailOrderTemplate(&$route, &$data, &$template_code) {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        $search  = '<p style="margin-top: 0px; margin-bottom: 20px;">{{ text_footer }}</p>';

		$replace = '{% if payment_link_order_email %}<p style="margin-top: 0px; margin-bottom: 20px;">{{ payment_link }}</p>{% endif %}<p style="margin-top: 0px; margin-bottom: 20px;">{{ text_footer }}</p>';

		$template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;
    }

    public function getPaymentMethodsAfter(&$route, &$args, &$output) {
        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];
        } else {
            $route = '';
        }

        $payment_address = $args[0];

        if (!empty($route) && (substr($route, 0, 3) == 'api')) {
            $mollie_payment = false;

            foreach ($output as $key => &$value) {
                if (strpos($key, 'mollie') !== false) {
                    $mollie_payment = true;

                    break;
                }
            }

            if ($mollie_payment) {
                $this->load->language("extension/mollie/payment/mollie");
                $this->load->model('extension/mollie/payment/mollie_payment_link');

                $order_id = 0;

                if (isset($this->session->data['order_id']) && ((int)$this->session->data['order_id'] > 0)) {
                    $order_id = (int)$this->session->data['order_id'];
                }

                $payment_link_details = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLinkByOrderID((int)$order_id);

                if(version_compare(VERSION, '4.0.2.0', '>=')) {
                    if (!empty($payment_link_details) && !empty($payment_link_details['date_payment'])) {
                        $option_data_full['mollie_payment_link_full'] = [
                            'code' => 'mollie_payment_link_full.mollie_payment_link_full',
                            'name' => $this->language->get('text_payment_link_full_title')
                        ];

                        $output['mollie_payment_link_full'] = [
                            'code'       => "mollie_payment_link_full",
                            'name'       => $this->language->get('text_payment_link_full_title'),
                            'option'     => $option_data_full,
                            'sort_order' => 0
                        ];

                        $option_data_open['mollie_payment_link_open'] = [
                            'code' => 'mollie_payment_link_open.mollie_payment_link_open',
                            'name' => $this->language->get('text_payment_link_open_title')
                        ];

                        $output['mollie_payment_link_open'] = [
                            'code'       => "mollie_payment_link_open",
                            'name'       => $this->language->get('text_payment_link_open_title'),
                            'option'     => $option_data_open,
                            'sort_order' => 0
                        ];
                    } else {
                        $option_data['mollie_payment_link'] = [
                            'code' => 'mollie_payment_link.mollie_payment_link',
                            'name' => $this->language->get('text_payment_link_title')
                        ];

                        $output['mollie_payment_link'] = [
                            'code'       => "mollie_payment_link",
                            'name'       => $this->language->get('text_payment_link_title'),
                            'option'     => $option_data,
                            'sort_order' => 0
                        ];
                    }
                } else {
                    if (!empty($payment_link_details) && !empty($payment_link_details['date_payment'])) {
                        $output['mollie_payment_link_full'] = array(
                            "code" => "mollie_payment_link_full",
                            "title" => $this->language->get('text_payment_link_full_title'),
                            "sort_order" => 0,
                            "terms" => NULL
                        );

                        $output['mollie_payment_link_open'] = array(
                            "code" => "mollie_payment_link_open",
                            "title" => $this->language->get('text_payment_link_open_title'),
                            "sort_order" => 0,
                            "terms" => NULL
                        );
                    } else {
                        $output['mollie_payment_link'] = array(
                            "code" => "mollie_payment_link",
                            "title" => $this->language->get('text_payment_link_title'),
                            "sort_order" => 0,
                            "terms" => NULL
                        );
                    }
                }
            }
        }
    }

    public function addOrderAfter(&$route, &$args, &$output) {
        $data = $args[0];
        $order_id = $output;

        if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
                if (isset($product['stock_mutation'])) {
                    if (!empty($product['option'])) {
                        $order_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE `order_id` = '" . (int)$order_id . "' AND `product_option_value_id` = '" . (int)$product['option'][0]['product_option_value_id'] . "' AND `value` = '" . $this->db->escape($product['option'][0]['value']) . "'");

                        $order_product_id = $order_option_query->row['order_product_id'];

                        $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '" . (int)$product['stock_mutation'] . "' WHERE order_product_id = '" . (int)$order_product_id . "' AND order_id = '" . (int)$order_id . "'");
                    } else {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '" . (int)$product['stock_mutation'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND order_id = '" . (int)$order_id . "'");
                    }
                }
			}
		}
    }

    public function editOrderAfter(&$route, &$args, &$output) {
        $order_id = $args[0];
        $data = $args[1];

        if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
                if (isset($product['stock_mutation'])) {
                    if (!empty($product['option'])) {
                        $order_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE `order_id` = '" . (int)$order_id . "' AND `product_option_value_id` = '" . (int)$product['option'][0]['product_option_value_id'] . "' AND `value` = '" . $this->db->escape($product['option'][0]['value']) . "'");

                        $order_product_id = $order_option_query->row['order_product_id'];

                        $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '" . (int)$product['stock_mutation'] . "' WHERE order_product_id = '" . (int)$order_product_id . "' AND order_id = '" . (int)$order_id . "'");
                    } else {
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '" . (int)$product['stock_mutation'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND order_id = '" . (int)$order_id . "'");
                    }
                }
			}
		}
    }

    public function addHistoryAfter(&$route, &$args, &$output) {
        $this->load->model('checkout/order');

        $order_id = $args[0];
        $order_status_id = $args[1];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!empty($order_info)) {
            // If current order status is not processing or complete but new status is processing or complete then commence completing the order
			if (!in_array($order_info['order_status_id'], array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status'))) && in_array($order_status_id, array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status')))) {
				foreach ($order_products as $order_product) {
                    if (!$order_product['stock_mutation']) {
                        // Stock subtraction (re-stock if no stock mutation)
                        $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` + " . (int)$order_product['quantity'] . ") WHERE `product_id` = '" . (int)$order_product['product_id'] . "' AND `subtract` = '1'");

                        // Stock subtraction from master product
                        if ($order_product['master_id']) {
                            $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` + " . (int)$order_product['quantity'] . ") WHERE `product_id` = '" . (int)$order_product['master_id'] . "' AND `subtract` = '1'");
                        }

                        $order_options = $this->model_checkout_order->getOptions($order_id, $order_product['order_product_id']);

                        foreach ($order_options as $order_option) {
                            $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET `quantity` = (`quantity` + " . (int)$order_product['quantity'] . ") WHERE `product_option_value_id` = '" . (int)$order_option['product_option_value_id'] . "' AND `subtract` = '1'");
                        }
                    }
				}
			}

            // If old order status is the processing or complete status but new status is not then commence restock, and remove coupon, voucher and reward history
			if (in_array($order_info['order_status_id'], array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status'))) && !in_array($order_status_id, array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status')))) {
                foreach ($order_products as $order_product) {
                    if (!$order_product['stock_mutation']) {
                        // Restock (subtract if no stock mutation)
                        $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` - " . (int)$order_product['quantity'] . ") WHERE `product_id` = '" . (int)$order_product['product_id'] . "' AND `subtract` = '1'");

                        // Restock the master product stock level if product is a variant
                        if ($order_product['master_id']) {
                            $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` - " . (int)$order_product['quantity'] . ") WHERE `product_id` = '" . (int)$order_product['master_id'] . "' AND `subtract` = '1'");
                        }

                        $order_options = $this->model_checkout_order->getOptions($order_id, $order_product['order_product_id']);

                        foreach ($order_options as $order_option) {
                            $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET `quantity` = (`quantity` - " . (int)$order_product['quantity'] . ") WHERE `product_option_value_id` = '" . (int)$order_option['product_option_value_id'] . "' AND `subtract` = '1'");
                        }
                    }
                }
			}
        }
    }

    // return template file contents as a string
	protected function getTemplateBuffer( $route, $event_template_buffer ) {
		// if there already is a modified template from view/*/before events use that one
		if ($event_template_buffer) {
			return $event_template_buffer;
		}

        $dir_template = (defined( 'DIR_CATALOG' ) ? DIR_CATALOG . 'view/template/' : DIR_TEMPLATE);

        $template_file = $dir_template . $route . '.twig';

        if (file_exists( $template_file ) && is_file( $template_file )) {
			return file_get_contents( $template_file );
		}

		exit;
	}
}
