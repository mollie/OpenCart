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

require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");

use Mollie\Api\MollieApiClient;
use Mollie\Api\Http\Requests\CreatePaymentCaptureRequest;

class Mollie extends \Opencart\System\Engine\Controller {

    // List of accepted languages by Mollie
    private array $locales = [
        'en_US', 'nl_NL', 'nl_BE', 'fr_FR', 'fr_BE', 'de_DE', 'de_AT',
        'de_CH', 'es_ES', 'ca_ES', 'pt_PT', 'it_IT', 'nb_NO', 'sv_SE',
        'fi_FI', 'da_DK', 'is_IS', 'hu_HU', 'pl_PL', 'lv_LV', 'lt_LT'
    ];

    public $mollieHelper;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->mollieHelper = new \MollieHelper($registry);
    }

    /**
     * @return MollieApiClient|null
     */
    protected function getAPIClient(): ?MollieApiClient {
        return $this->mollieHelper->getAPIClient($this->config);
    }

    /**
     * Keep a log of Mollie transactions.
     *
     * @param string $line
     * @param bool $alsoEcho
     */
    protected function writeToMollieLog(string $line, bool $alsoEcho = false): void {
        $log = new \Opencart\System\Library\Log('Mollie.log');
        $log->write($line);
        if ($alsoEcho) {
            echo $line;
        }
    }

    /**
     * @param string $line
     * @param bool $alsoEcho
     */
    protected function writeToMollieDebugLog(string $line, bool $alsoEcho = false): void {
        $log = new \Opencart\System\Library\Log('Mollie_debug.log');
        $log->write($line);
        if ($alsoEcho) {
            echo $line;
        }
    }

    /**
     * @return object
     */
    protected function getModuleModel(): object {
        $model_name = "model_extension_mollie_payment_mollie_" . static::MODULE_NAME;

        if (!isset($this->$model_name)) {
            $this->load->model("extension/mollie/payment/mollie_" . static::MODULE_NAME);
        }

        return $this->$model_name;
    }

    /**
     * @return int|string|bool
     */
    protected function getOrderID(): int|string|bool {
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
     * @param int|string $order_id
     * @return array
     */
    protected function getOpenCartOrder(int|string $order_id): array {
        $this->load->model("checkout/order");
        $order = $this->model_checkout_order->getOrder($order_id);
        return is_array($order) ? $order : [];
    }

    /**
     * Get order products
     * * @param int|string $order_id
     * @return array
     */
    protected function getOrderProducts(int|string $order_id): array {
        $model = $this->getModuleModel();
        return $model->getOrderProducts($order_id);
    }

    /**
     * Get tax rate
     * * @param array $tax_rates
     * @return array
     */
    protected function getTaxRate(array $tax_rates = []): array {
        $rates = [];
        if (!empty($tax_rates)) {
            foreach ($tax_rates as $tax) {
                $rates[] = $tax['rate'];
            }
        }
        return $rates;
    }

    /**
     * Get Coupon Details
     * * @param int|string $orderID
     * @return array
     */
    protected function getCouponDetails(int|string $orderID): array {
        $model = $this->getModuleModel();
        return $model->getCouponDetails($orderID);
    }

    /**
     * Get Voucher Details
     * * @param int|string $orderID
     * @return array
     */
    protected function getVoucherDetails(int|string $orderID): array {
        $model = $this->getModuleModel();
        return $model->getVoucherDetails($orderID);
    }

    /**
     * Get Reward Point Details
     * * @param int|string $orderID
     * @return array
     */
    protected function getRewardPointDetails(int|string $orderID): array {
        $model = $this->getModuleModel();
        return $model->getRewardPointDetails($orderID);
    }

    /**
     * Format number based on currency
     * * @param float|string $amount
     * @return string
     */
    public function numberFormat(float|string $amount): string {
        $currency = $this->getCurrency();
        $intCurrencies = ["ISK", "JPY"];

        if (!in_array($currency, $intCurrencies)) {
            return number_format((float)$amount, 2, '.', '');
        }
        return number_format((float)$amount, 0, '', '');
    }

    /**
     * @return string
     */
    public function getCurrency(): string {
        if ($this->config->get($this->mollieHelper->getModuleCode() . "_default_currency") == "DEF") {
            return (string)($this->session->data['currency'] ?? '');
        }
        return (string)$this->config->get($this->mollieHelper->getModuleCode() . "_default_currency");
    }

    /**
     * @return string
     */
    private function getMethodSeparator(): string {
        $method_separator = '|';
        if (version_compare(VERSION, '4.0.2.0', '>=')) {
            $method_separator = '.';
        }
        return $method_separator;
    }

    /**
     * This gets called by OpenCart at the final checkout step and should generate a confirmation button.
     * @return string
     */
    public function index(): string {
        $this->load->language("extension/mollie/payment/mollie");

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $code = $this->session->data['payment_method']['code'] ?? '';
            $parts = explode('.', $code);
            $method = str_replace('mollie_', '', $parts[1] ?? '');
        } else {
            $method = str_replace('mollie_', '', $this->session->data['payment_method'] ?? '');
        }

        $method = str_replace('_', '', $method);

        try {
            if ($method == 'ideal') {
                $payment_method = $this->getAPIClient()->methods->get($method, ['currency' => $this->getCurrency()]);
            } else {
                $payment_method = $this->getAPIClient()->methods->get($method, ['currency' => $this->getCurrency(), 'include' => 'issuers']);
            }
        } catch (\Exception $e) {
            $this->writeToMollieLog("Error getting payment method " . $method . ": " . $e->getMessage());

            return '';
        }

        $data['action'] = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "payment", '', true);

        $data['image']                   = $payment_method->image->size1x ?? '';
        $data['message']                 = $this->language;
        $data['issuers']                 = $payment_method->issuers ?? [];

        if (!empty($data['issuers'])) {
            $data['text_issuer']         = $this->language->get("text_issuer_" . $method);
            $data['set_issuer_url']      = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "set_issuer", '', true);
        }

        $data['text_mollie_payments']    = sprintf($this->language->get('text_mollie_payments'), '<a href="https://www.mollie.com/" target="_blank"><img src="./image/mollie/mollie_logo.png" alt="Mollie" border="0"></a>');

        // Mollie components
        $data['mollieComponents'] = false;

        if ($method == 'creditcard') {
            if ($this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component") && !$this->config->get($this->mollieHelper->getModuleCode() . "_single_click_payment")) {

                try {
                    $data['currentProfile'] = $this->getAPIClient()->profiles->getCurrent()->id;
                } catch (\Exception $e) {
                    $this->writeToMollieLog("Error getting current profile for components: " . $e->getMessage());
                    $data['mollieComponents'] = false;
                }

                $language_code = (string)($this->session->data['language'] ?? $this->config->get('config_language'));

                $locale = 'en_US';
                if (str_contains($language_code, '-')) {
                    list ($lang, $country) = explode('-', $language_code);
                    $locale = strtolower($lang) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower($language_code) . '_' . strtoupper($language_code);
                }

                if (!in_array($locale, $this->locales)) {
                    $locale_setting = (string)$this->config->get($this->mollieHelper->getModuleCode() . "_payment_screen_language");
                    if (str_contains($locale_setting, '-')) {
                        list ($lang, $country) = explode('-', $locale_setting);
                        $locale = strtolower($lang) . '_' . strtoupper($country);
                    } else {
                        $locale = strtolower($locale_setting) . '_' . strtoupper($locale_setting);
                    }
                }

                if (in_array(strtolower($locale), ['en_gb', 'en_en'])) {
                    $locale = 'en_US';
                }

                $data['locale']            = $locale;
                $data['mollieComponents']  = true;
                $data['base_input_css']    = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_base");
                $data['valid_input_css']   = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_valid");
                $data['invalid_input_css'] = $this->config->get($this->mollieHelper->getModuleCode() . "_mollie_component_css_invalid");

                $apiKey = (string)$this->config->get($this->mollieHelper->getModuleCode() . "_api_key");
                $data['testMode'] = str_starts_with($apiKey, 'test_');
            }
        }

        $data['isJournalTheme'] = false;
        if (str_starts_with((string)$this->config->get('config_template'), 'journal2') && isset($this->journal2) && $this->journal2->settings->get('journal_checkout')) {
            $data['isJournalTheme'] = true;
        }

        return $this->load->view('extension/mollie/payment/mollie_checkout_form', $data);
    }

    /**
     * @param float|string $amount
     * @return string
     */
    protected function convertCurrency(float|string $amount): string {
        return (string)$this->currency->format((float)$amount, $this->getCurrency(), false, false);
    }

    /**
     * Format text securely
     * * @param string|null $text
     * @return string|null
     */
    protected function formatText(?string $text): ?string {
        if ($text) {
            return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        }
        return $text;
    }

    /**
     * Check if required billing/shipping address fields are present
     * * @param array $order
     * @return bool
     */
    public function addressCheck(array $order): bool {
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
                if (!in_array($order['payment_iso_code_2'] ?? '', $noPostCode)) {
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
                if (!in_array($order['shipping_iso_code_2'] ?? '', $noPostCode)) {
                    $valid = false;
                    $field = 'Shipping Postcode';
                }
            }
        }

        if (!$valid) {
            $this->writeToMollieLog("Mollie Payment Error: Mollie payment requires payment and shipping address details. Empty required field: " . $field);
        }

        return $valid;
    }

    public function payment(): void {
        // Load essentials
        $this->load->language("extension/mollie/payment/mollie");

        if (($this->request->server['REQUEST_METHOD'] ?? '') != 'POST') {
            $this->showErrorPage($this->language->get('warning_secure_connection'));
            $this->writeToMollieLog("Creating payment failed, connection is not secure.");
            return;
        }

        try {
            $api = $this->getAPIClient();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating payment failed, API did not load: " . htmlspecialchars($e->getMessage()));
            return;
        }

        $model = $this->getModuleModel();
        $order_id = $this->getOrderID();
        $order = $this->getOpenCartOrder($order_id);

        if (empty($order)) {
            $this->showErrorPage('Order not found.');
            return;
        }

        $currency = $this->getCurrency();
        $amount = (float)$this->convertCurrency((float)$order['total']);

        $config_desc = $this->config->get($this->mollieHelper->getModuleCode() . "_description");
        $lang_id = (int)$this->config->get('config_language_id');

        if (isset($config_desc[$lang_id]['title'])) {
            $description = $config_desc[$lang_id]['title'];
        } else {
            $description = 'Order %';
        }
        $description = str_replace("%", (string)$order['order_id'], html_entity_decode((string)$description, ENT_QUOTES, "UTF-8"));

        $return_url = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "callback&order_id=" . $order['order_id']);
        $issuer = $this->getIssuer();

        if (version_compare(VERSION, '4.0.1.1', '>')) {
            $code = $this->session->data['payment_method']['code'] ?? '';
            $parts = explode('.', $code);
            $method = str_replace('mollie_', '', $parts[1] ?? '');
        } else {
            $method = str_replace('mollie_', '', $this->session->data['payment_method'] ?? '');
        }

        $method = str_replace('_', '', $method);

        // Check for subscription profiles
        $subscription = false;
        if ($this->cart->hasSubscription()) {
            $subscription = true;
        }

        $singleClickPayment = false;
        $mollie_customer_id = '';
        if (($method == 'creditcard') && $this->config->get($this->mollieHelper->getModuleCode() . "_single_click_payment")) {
            $mollie_customer_id = $this->createCustomer($order);
            $singleClickPayment = true;
        } elseif ($subscription) {
            $mollie_customer_id = $this->createCustomer($order);
        }

        $mandate = false;
        if (!empty($mollie_customer_id)) {
            $customer = $api->customers->get($mollie_customer_id);
            $mandates = $customer->mandates();
            foreach ($mandates as $_mandate) {
                if ($_mandate->isValid() || $_mandate->isPending()) {
                    $mandate = true;
                    break;
                }
            }
        }

        try {
            $data = [
                "amount"        => ["currency" => $currency, "value" => (string)$this->numberFormat($amount)],
                "description"   => (string)$description,
                "redirectUrl"   => (string)$this->formatText($return_url),
                "webhookUrl"    => $this->getWebhookUrl(),
                "metadata"      => ["order_id" => $order['order_id']],
                "method"        => $method,
                "issuer"        => (string)$this->formatText($issuer)
            ];

            // Manual capture
            if (in_array($method, ["creditcard", "klarna", "klarnapaylater", "klarnapaynow", "klarnasliceit", "billie", "riverty"])) { // "paypal" is currently in beta phase. Will be added later.
                $data['captureMode'] = "manual";
            }

            if ((($singleClickPayment && $mandate) || $subscription) && !empty($mollie_customer_id)) {
                $data['customerId'] = (string)$mollie_customer_id;
            }

            if ($subscription) {
                $data['sequenceType'] = "first";
            }

            if (!empty($this->request->post['cardToken'])) {
                $data['cardToken'] = (string)$this->request->post['cardToken'];
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
                        'categories'    =>  [(string)$productDetails['voucher_category']],
                        'description'   =>  $this->formatText($orderProduct['name']),
                        'quantity'      =>  $qty,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($price + $tax))],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($total)],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($vatAmount)]
                    );
                } else {
                    $lines[] = array(
                        'type'          =>  'physical',
                        'description'   =>  $this->formatText($orderProduct['name']),
                        'quantity'      =>  $qty,
                        'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($price + $tax))],
                        'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($total)],
                        'vatRate'       =>  (string)$this->numberFormat($vatRate),
                        'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($vatAmount)]
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
                        'description'   =>  $this->formatText($title),
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
                        'description'   =>  $this->formatText($coupon_info['name']),
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
                    'description'   =>  $this->formatText($voucher['title']),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(-$this->convertCurrency($voucher['value']))],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat(-$this->convertCurrency($voucher['value']))],
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

                $rewardVATAmount = $this->numberFormat($unitPriceWithTax * ( $vatRate / (100 +  $vatRate)));

                $lineForRewardPoints[] = array(
                    'type'          =>  'discount',
                    'description'   =>  $this->formatText($rewardPoints['title']),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => "-$unitPriceWithTax"],
                    'totalAmount'   =>  ["currency" => $currency, "value" => "-$unitPriceWithTax"],
                    'vatRate'       =>  (string)$this->numberFormat($vatRate),
                    'vatAmount'     =>  ["currency" => $currency, "value" => "-$rewardVATAmount"]
                );

                $lines = array_merge($lines, $lineForRewardPoints);
            }

            // Gift Voucher
            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $key => $voucher) {
                    $voucherData[] = array(
                        'type'                  => 'physical',
                        'description'           => $voucher['description'],
                        'quantity'              => 1,
                        'unitPrice'             =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['amount']))],
                        'totalAmount'           =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($voucher['amount']))],
                        'vatRate'               =>  "0.00",
                        'vatAmount'             =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                    );
                }

                if (isset($voucherData) && !empty($voucherData)) {
                    $lines = array_merge($lines, $voucherData);
                }
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
                        'description'   =>  $this->formatText($orderTotals['title']),
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

            if($orderTotal < $orderLineTotal) {
                $amountDiff = $this->numberFormat($orderLineTotal - $orderTotal);
                $lineForDiscount[] = array(
                    'type'          =>  'discount',
                    'description'   =>  $this->formatText($this->language->get("roundoff_description")),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => "-$amountDiff"],
                    'totalAmount'   =>  ["currency" => $currency, "value" => "-$amountDiff"],
                    'vatRate'       =>  "0.00",
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                );

                $lines = array_merge($lines, $lineForDiscount);
            }

            if($orderTotal > $orderLineTotal) {
                $amountDiff = $this->numberFormat($orderTotal - $orderLineTotal);
                $lineForSurcharge[] = array(
                    'type'          =>  'surcharge',
                    'description'   =>  $this->formatText($this->language->get("roundoff_description")),
                    'quantity'      =>  1,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$amountDiff],
                    'vatRate'       =>  "0.00",
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat(0.00)]
                );

                $lines = array_merge($lines, $lineForSurcharge);
            }

            $data['lines'] = $lines;

            if (!$this->addressCheck($order)) {
                $this->showErrorPage($this->language->get('error_missing_field'));
                return;
            }

            if (version_compare(VERSION, '4.0.1.1', '>')) {
                $payment_address = $this->config->get('config_checkout_payment_address');
            } else {
                $payment_address = $this->config->get('config_checkout_address');
            }

            if ($payment_address) {
                $data["billingAddress"] = [
                    "givenName"             => (string)$this->formatText($order['payment_firstname'] ?? ''),
                    "familyName"            => (string)$this->formatText($order['payment_lastname'] ?? ''),
                    "organizationName"      => (string)$this->formatText($order['payment_company'] ?? ''),
                    "email"                 => (string)$this->formatText($order['email'] ?? ''),
                    "streetAndNumber"       => (string)$this->formatText($order['payment_address_1'] ?? ''),
                    "streetAdditional"      => (string)$this->formatText($order['payment_address_2'] ?? ''),
                    "city"                  => (string)$this->formatText($order['payment_city'] ?? ''),
                    "region"                => (string)$this->formatText($order['payment_zone'] ?? ''),
                    "postalCode"            => (string)$this->formatText($order['payment_postcode'] ?? ''),
                    "country"               => (string)$this->formatText($order['payment_iso_code_2'] ?? '')
                ];
            }

            if (isset($this->session->data['shipping_address'])) {
                if (!empty($order['shipping_firstname']) || !empty($order['shipping_lastname'])) {
                    $data["shippingAddress"] = [
                        "givenName"             => (string)$this->formatText($order['shipping_firstname'] ?? ''),
                        "familyName"            => (string)$this->formatText($order['shipping_lastname'] ?? ''),
                        "email"                 => (string)$this->formatText($order['email'] ?? ''),
                        "streetAndNumber"       => (string)$this->formatText($order['shipping_address_1'] ?? ''),
                        "streetAdditional"      => (string)$this->formatText($order['shipping_address_2'] ?? ''),
                        "city"                  => (string)$this->formatText($order['shipping_city'] ?? ''),
                        "region"                => (string)$this->formatText($order['shipping_zone'] ?? ''),
                        "postalCode"            => (string)$this->formatText($order['shipping_postcode'] ?? ''),
                        "country"               => (string)$this->formatText($order['shipping_iso_code_2'] ?? '')
                    ];
                } else {
                    if ($payment_address) {
                        $data["shippingAddress"] = $data["billingAddress"] ?? [];
                    }
                }
            }

            if (!$payment_address) {
                if (!empty($data["shippingAddress"])) {
                    $data["billingAddress"] = $data["shippingAddress"];
                } else {
                    $this->writeToMollieLog("Billing address is turned off, digital orders will not be able to be paid. You can turn on the billing address in settings");
                    $this->showErrorPage($this->language->get('error_missing_field'));
                    return;
                }
            }

            $language_code = (string)($this->session->data['language'] ?? $this->config->get('config_language'));
            if (str_contains($language_code, '-')) {
                list ($lang, $country) = explode('-', $language_code);
                $locale = strtolower($lang) . '_' . strtoupper($country);
            } else {
                $locale = strtolower($language_code) . '_' . strtoupper($language_code);
            }

            if (!in_array($locale, $this->locales)) {
                $locale_setting = (string)$this->config->get($this->mollieHelper->getModuleCode() . "_payment_screen_language");
                if (str_contains($locale_setting, '-')) {
                    list ($lang, $country) = explode('-', $locale_setting);
                    $locale = strtolower($lang) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower($locale_setting) . '_' . strtoupper($locale_setting);
                }
            }

            if (strtolower($locale) == 'en_gb' || strtolower($locale) == 'en_en') {
                $locale = 'en_US';
            }

            $data["locale"] = $locale;

			// Debug mode
            if ($this->config->get($this->mollieHelper->getModuleCode() . "_debug_mode")) {
                $this->writeToMollieDebugLog("===== DEBUG: DATA SENT TO MOLLIE (PAYMENT API) =====");
                $this->writeToMollieDebugLog("SESSION ID BEFORE LEAVING: " . $this->session->getId());
                $this->writeToMollieDebugLog("PAYLOAD: " . print_r($data, true));
                $this->writeToMollieDebugLog("====================================================");
            }

            // Create Payment
            $paymentObject = $api->payments->create($data);

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage(htmlspecialchars($e->getMessage()));
            $this->writeToMollieLog("Creating payment failed for order_id - " . $order['order_id'] . ' ; ' . htmlspecialchars($e->getMessage()));
            return;
        }

        if ($this->startAsPending()) {
            $this->addOrderHistory($order, $this->config->get($this->mollieHelper->getModuleCode() . "_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
        }

        if($model->setPaymentForPaymentAPI($order['order_id'], $paymentObject->id, $paymentObject->method)) {
            $this->writeToMollieLog("Payments API: Payment created : method - " . $method . ', ' . "order_id - " . $order['order_id'] . ', ' . "mollie_payment_id - " . $paymentObject->id);
        } else {
            $this->writeToMollieLog("Payments API: Payment created : method - " . $method . ', ' . "order_id - " . $order['order_id'] . ', ' . "mollie_payment_id - " . $paymentObject->id . ". Payment ID is not saved in the database. Should be updated when webhook called.");
        }

		$this->setSameSiteCookie();
        $this->redirect($paymentObject->_links->checkout->href, 303);
    }

    /**
     * Some payment methods can't be cancelled. They need 'pending' as an initial order status.
     *
     * @return bool
     */
    protected function startAsPending(): bool {
        return false;
    }

    /**
     * This action is getting called by Mollie to report the payment status
     */
    public function webhook(): bool {
        $id = (string)($this->request->post['id'] ?? '');

        if (empty($id)) {
            header("HTTP/1.0 400 Bad Request");
            $this->writeToMollieLog("Webhook called but no ID received.", true);
            return true;
        }

        // Check webhook for order/payment
        $temp = explode('_', $id);
        $idPrefix = $temp[0] ?? '';

        if ($idPrefix == 'ord') {
            $this->webhookForOrder($id);
        } elseif ($idPrefix == 'tr') {
            $this->webhookForPayment($id);
        } else {
            $this->webhookForPaymentLink($id);
        }

        header('HTTP/1.1 200 OK');
        return true;
    }

    private function webhookForPaymentLink(string $payment_link_id): bool {
        $this->writeToMollieLog("Received webhook for payment link : {$payment_link_id}");

        // Load essentials
        $this->load->model("extension/mollie/payment/mollie_payment_link");
        $this->load->model("checkout/order");
        $this->load->language("extension/mollie/payment/mollie");

        $moduleCode = $this->mollieHelper->getModuleCode();
        $molliePaymentLink = $this->getAPIClient()->paymentLinks->get($payment_link_id);

        if ($molliePaymentLink->isPaid()) {
            $date_payment = date("Y-m-d H:i:s", strtotime($molliePaymentLink->paidAt));
            $paymentLink = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLink($payment_link_id);

            if ($paymentLink) {
                $this->model_extension_mollie_payment_mollie_payment_link->updatePaymentLink($payment_link_id, $date_payment);

                $new_status_id = (int)$this->config->get($moduleCode . "_ideal_processing_status_id");
                $notify_customer = ($this->config->get($moduleCode . "_ideal_processing_status_notify")) ? true : false;

                if (!$new_status_id) {
                    $this->writeToMollieLog("The payment has been received. No 'processing' status ID is configured, so the order status for order {$paymentLink['order_id']} could not be updated.");
                } else {
                    $order = $this->model_checkout_order->getOrder($paymentLink['order_id']);
                    $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), $notify_customer);
                    $this->writeToMollieLog("The payment was received and the order {$paymentLink['order_id']} was moved to the 'processing' status (new status ID: {$new_status_id}).");
                }
            }
        }

        $this->writeToMollieLog("------------- End webhook for payment link -------------");
        return true;
    }

    private function webhookForPayment(string $payment_id): bool {
        // Load essentials
        $this->load->model("checkout/order");
        $this->load->language("extension/mollie/payment/mollie");

        $model = $this->getModuleModel();
        $moduleCode = $this->mollieHelper->getModuleCode();
        $molliePayment = $this->getAPIClient()->payments->get($payment_id, ["embed" => "refunds"]);

        if (!$molliePayment) {
            $this->writeToMollieLog("Received webhook for payment but transaction does not exist at mollie. Payment ID: {$payment_id}");
            return true;
        }

        // Get order_id of this transaction from db
        if (isset($molliePayment->metadata->order_id)) {
            $order = $this->model_checkout_order->getOrder($molliePayment->metadata->order_id);
        } else {
            $order = [];
        }

        if (!empty($order)) {
            $this->writeToMollieLog("Received webhook for payment: Payment ID: {$payment_id}, Order ID: {$order['order_id']}");
        }

        $paid_status_id = (int)$this->config->get($moduleCode . "_ideal_processing_status_id");
        $shipping_status_id = (int)$this->config->get($moduleCode . "_ideal_shipping_status_id");
        $pending_status_id = (int)$this->config->get($moduleCode . "_ideal_pending_status_id");
        $failed_status_id = (int)$this->config->get($moduleCode . "_ideal_failed_status_id");
        $canceled_status_id = (int)$this->config->get($moduleCode . "_ideal_canceled_status_id");
        $expired_status_id = (int)$this->config->get($moduleCode . "_ideal_expired_status_id");

        // Check for subscription payment
        if (!empty($molliePayment->subscriptionId)) {
            $firstPaymentDetails = $model->getPaymentBySubscriptionID($molliePayment->subscriptionId);

            if (!empty($firstPaymentDetails)) {
                $data = [
                    'transaction_id' => $payment_id,
                    'mollie_subscription_id' => (string)$molliePayment->subscriptionId,
                    'mollie_customer_id' => (string)$molliePayment->customerId,
                    'method' => (string)$molliePayment->method,
                    'status' => (string)$molliePayment->status,
                    'amount' => (float)$molliePayment->amount->value,
                    'currency' => (string)$molliePayment->amount->currency,
                    'order_subscription_id' => (int)$firstPaymentDetails['order_subscription_id']
                ];

                $model->addSubscriptionPayment($data);
                $this->writeToMollieLog("Webhook for payment: Subscription: mollie_subscription_id - {$molliePayment->subscriptionId}, transaction_id - {$payment_id}, status - {$data['status']}, mollie_customer_id - $molliePayment->customerId");
            }
            return true;
        }

        if (!empty($order)) {
            $order_id = $order['order_id'];

            // Set transaction ID
            $data = [
                'payment_id' => $payment_id,
                'status'     => (string)$molliePayment->status,
                'amount'     => (float)$molliePayment->amount->value
            ];

            $model->updatePaymentForPaymentAPI($order_id, $payment_id, $data);
            $this->writeToMollieLog("Webhook for payment: transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order_id}");

            if ((int)$order['order_status_id'] != 0) {
                // Check for refund safely! (PHP 8.1 strict mode fix for the bug you reported)
                $refund_cancel = false;

                if (!empty($molliePayment) && isset($molliePayment->_embedded, $molliePayment->_embedded->refunds)) {
                    $refunds = $molliePayment->_embedded->refunds;
                    foreach ($refunds as $refund) {
                        if ($refund->status == 'canceled') {
                            $refund_cancel = true;
                            break;
                        }
                    }
                }

                if (isset($molliePayment->amountRefunded, $molliePayment->amountRefunded->value) && ($molliePayment->amountRefunded->value > 0)) {
                    $data = [
                        'payment_id' => $payment_id,
                        'status'     => 'refunded',
                        'amount'     => (float)$molliePayment->amount->value
                    ];

                    $model->updatePaymentForPaymentAPI($order_id, $payment_id, $data);
                    $this->writeToMollieLog("Webhook for payment: Updated mollie payment. transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$order_id}");

                    $this->writeToMollieLog("Webhook for payment: Order status has been updated to 'Refunded' for order - {$order_id}, {$payment_id}");
                } elseif ($refund_cancel && !empty($order['order_status_id']) && $order['order_status_id'] == $this->config->get($moduleCode . "_ideal_refund_status_id")) {
                    $data['refund_id'] = '';

                    $model->cancelReturn($order_id, $data);

                    $notify_customer = ($this->config->get($moduleCode . "_ideal_processing_status_notify")) ? true : false;

                    $this->addOrderHistory($order, $paid_status_id, $this->language->get("refund_cancelled"), $notify_customer);

                    $this->writeToMollieLog("Webhook for payment: Refund has been cancelled for order - {$order_id}, {$payment_id}");
                    $this->writeToMollieLog("Webhook for payment: Order status has been updated to 'Processing' for order - {$order_id}, {$payment_id}, {$payment_id}");
                }
            }

            // Only process the status if the order is stateless or in 'pending' status.
            if (!empty($order['order_status_id']) && $order['order_status_id'] != $pending_status_id) {
                $this->writeToMollieLog("Webhook for payment : The order {$order_id}, {$payment_id} was already processed (order status ID: " . (int)$order['order_status_id'] . ")");
                return true;
            }

            $new_status_id = 0;
            $status = '';
            $response = '';
            $notify_customer = false;

            if ($molliePayment->isPaid() || $molliePayment->isAuthorized()) {
                $new_status_id = $paid_status_id;
                $status = 'paid/authorized';
                $response = $this->language->get("response_success");
                $notify_customer = ($this->config->get($moduleCode . "_ideal_processing_status_notify")) ? true : false;
            } elseif ($molliePayment->isCanceled()) {
                $new_status_id = $canceled_status_id;
                $status = 'cancelled';
                $response = $this->language->get("response_cancelled");
                $notify_customer = ($this->config->get($moduleCode . "_ideal_canceled_status_notify")) ? true : false;
            } elseif ($molliePayment->isExpired()) {
                $new_status_id = $expired_status_id;
                $status = 'expired';
                $response = $this->language->get("response_expired");
                $notify_customer = ($this->config->get($moduleCode . "_ideal_expired_status_notify")) ? true : false;
            } else {
                $new_status_id = $failed_status_id;
                $status = 'failed';
                $response = $this->language->get("response_unknown");
                $notify_customer = ($this->config->get($moduleCode . "_ideal_failed_status_notify")) ? true : false;
            }

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment: Payment status: {$status}, No '{$status}' status ID is configured, so the order status for order {$order_id} could not be updated.");
            } else {
                $this->writeToMollieLog("Webhook for payment: Payment status: {$status}. The order {$order_id} has been moved to the '{$status}' status (new status ID: {$new_status_id}).");
                $this->addOrderHistory($order, $new_status_id, $response, $notify_customer);
            }

            /* Check module module setting for capture creation,
            $this->config->get($moduleCode . "_create_shipment")) == 1,
            satisfies the 'Create capture immediately after order creation' condition. */
            $orderStatuses = $model->getOrderStatuses($order_id);
            if($molliePayment->isAuthorized() && ($this->config->get($moduleCode . "_create_shipment") == 1) && !in_array($shipping_status_id, $orderStatuses) && ($order['total'] > 0)) {
                try {
                    $captureObject = $this->getAPIClient()->send(
                        new CreatePaymentCaptureRequest(
                            paymentId: $payment_id,
                            description: "Capture for order - $order_id",
                            metadata: [
                                "order_id" => $order_id,
                                "transaction_id" => $payment_id
                            ]
                        )
                    );

                    $this->writeToMollieLog("Capture created for order - {$order_id}, {$payment_id}, {$captureObject->id}");

                    $notify_customer = ($this->config->get($moduleCode . "_ideal_shipping_status_notify")) ? true : false;

                    $this->addOrderHistory($order, $shipping_status_id, $this->language->get("capture_success"), $notify_customer);
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $this->writeToMollieLog("Webhook for payment: Capture could not be created for order - {$order_id}, {$payment_id}; " . htmlspecialchars($e->getMessage()));
                }
            }
        }

        $this->writeToMollieLog("------------- End webhook for payment -------------");

        return true;
    }

    /**
     * Gets called via AJAX from the checkout form to store the selected issuer.
     */
    public function set_issuer(): void {
        if (!empty($this->request->post['mollie_issuer_id'])) {
            $this->session->data['mollie_issuer'] = (string)$this->request->post['mollie_issuer_id'];
        } else {
            $this->session->data['mollie_issuer'] = null;
        }

        echo (string)$this->session->data['mollie_issuer'];
    }

    /**
     * Retrieve the issuer if one was selected. Return null otherwise.
     *
     * @return string|null
     */
    protected function getIssuer(): ?string {
        if (!empty($this->request->post['mollie_issuer'])) {
            return (string)$this->request->post['mollie_issuer'];
        }

        if (!empty($this->session->data['mollie_issuer'])) {
            return (string)$this->session->data['mollie_issuer'];
        }

        return null;
    }

    /**
     * Create shipment at Mollie after the order reaches a specific status.
     *
     * @param string $route
     * @param array  $data
     * @param mixed  $orderID
     * @param mixed  $orderStatusID
     *
     * @return bool
     */
    public function createShipment(string &$route, array &$data, mixed $orderID = "", mixed $orderStatusID = ""): bool {
        // 1. Determine Order ID and Status ID from event data or arguments
        if (!empty($data)) {
            $order_id = (int)($data[0] ?? 0);
            $order_status_id = (int)($data[1] ?? 0);
        } else {
            $order_id = (int)$orderID;
            $order_status_id = (int)$orderStatusID;
        }

        $moduleCode = $this->mollieHelper->getModuleCode();

        // 2. Load required models and language
        $this->load->model("checkout/order");
        $this->load->model('extension/mollie/payment/mollie_ideal');
        $this->load->language("extension/mollie/payment/mollie");

        $orderModel = $this->model_checkout_order;
        $mollieModel = $this->model_extension_mollie_payment_mollie_ideal;

        // 3. Fetch order details
        $order = $orderModel->getOrder($order_id);
        $mollie_payment_id = $mollieModel->getPaymentID($order_id);

        if (!empty($order) && !empty($mollie_payment_id) && ($order['total'] > 0)) {
            try {
                $molliePayment = $this->getAPIClient()->payments->get($mollie_payment_id);

                // 4. Check if order is authorized and capture creation is enabled (setting != 1 which means 'Off')
                if ($molliePayment->isAuthorized() && ($this->config->get($moduleCode . "_create_shipment") != 1)) {

                    // Determine target status for capture
                    $target_statuses = [];

                    // Setting = 2 means specific status, otherwise use complete statuses
                    if ($this->config->get($moduleCode . "_create_shipment") == 2) {
                        $target_statuses[] = (int)$this->config->get($moduleCode . "_create_shipment_status_id");
                    } else {
                        $statuses = $this->config->get('config_complete_status') ?: (array)$this->config->get('config_complete_status_id');
                        foreach ($statuses as $status_id) {
                            $target_statuses[] = (int)$status_id;
                        }
                    }

                    // 5. Create capture if status matches
                    if (in_array($order_status_id, $target_statuses)) {
                        $captureObject = $this->getAPIClient()->send(
                            new CreatePaymentCaptureRequest(
                                paymentId: $mollie_payment_id,
                                description: "Capture for order - $order_id",
                                metadata: [
                                    "order_id" => $order_id,
                                    "transaction_id" => $mollie_payment_id
                                ]
                            )
                        );

                        $this->writeToMollieLog("Capture created for order - {$order_id}, {$mollie_payment_id}, {$captureObject->id}");
                    }
                }
            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                $this->writeToMollieLog("Capture could not be created for order - {$order_id}, {$mollie_payment_id}; " . htmlspecialchars($e->getMessage()));
            }
        }

        return true;
    }

    /**
     * Create a subscription for a recurring order.
     *
     * @param array $order
     * @param object $paymentDetails Mollie Payment/Order Object
     *
     * @return bool
     */
    public function createSubscription(array $order, object $paymentDetails): bool {
        $this->load->language("extension/mollie/payment/mollie");
        $this->load->model("checkout/order");

        $model = $this->getModuleModel();

        $mollie_payment_id = $model->getPaymentID($order['order_id']);
        $mollie_customer_id = $model->getMollieCustomer($order['email']);

        if (!empty($mollie_customer_id) && $paymentDetails->isPaid()) {
            if (isset($paymentDetails->mandateId)) {
                $mandate_id = $paymentDetails->mandateId;

                $api = $this->getAPIClient();
                $customer = $api->customers->get($mollie_customer_id);

                // Fetch mandates to verify valid mandate exists
                $mandates = $customer->mandates();

                foreach ($mandates as $mandate) {
                    if (($mandate->isValid() || $mandate->isPending()) && ($mandate->id == $mandate_id)) {
                        $order_products = $this->model_checkout_order->getProducts($order['order_id']);

                        foreach ($order_products as $product) {
                            // Check if product has subscription info
                            $order_subscription_info = $this->model_checkout_order->getSubscription($order['order_id'], $product['order_product_id']);

                            if ($order_subscription_info) {
                                $unit_price = (float)$order_subscription_info['price'] + (float)$order_subscription_info['tax'];
                                $total = $this->numberFormat((float)$this->convertCurrency($unit_price));
                                $duration = (int)$order_subscription_info['duration'];
                                $cycle = (int)$order_subscription_info['cycle'];

                                // Map OpenCart frequency to Mollie interval
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

                                // Prepare subscription data
                                $subscriptionData = [
                                    "amount"      => ["currency" => $this->getCurrency(), "value" => (string)$this->numberFormat($total)],
                                    "times"       => $duration,
                                    "interval"    => $interval,
                                    "mandateId"   => $mandate->id,
                                    "startDate"   => $subscription_start->modify('+' . $cycle . ' ' . $frequency)->format('Y-m-d'),
                                    "description" => sprintf($this->language->get('text_subscription_desc'), $order['order_id'], $order['store_name'], date('Y-m-d H:i:s'), $interval, $product['name']),
                                    "webhookUrl"  => $this->getWebhookUrl()
                                ];

                                if ($duration <= 0) {
                                    unset($subscriptionData['times']); // Infinite subscription
                                }

                                try {
                                    $subscription = $customer->createSubscription($subscriptionData);

                                    $this->writeToMollieLog("Subscription created: mollie_subscription_id - {$subscription->id}, order_id - {$order['order_id']}");

                                    // Save subscription to local DB
                                    $model->subscriptionPayment($order_subscription_info, $subscription->id, $mollie_payment_id);
                                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                                    $this->writeToMollieLog("Creating subscription failed for order_id - " . $order['order_id'] . ' ; ' . htmlspecialchars($e->getMessage()));
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Customer returning from the bank with an transaction_id
     * Depending on what the state of the payment is they get redirected to the corresponding page
     *
     * @return mixed
     */
    public function callback(): mixed {
        $order_id = $this->getOrderID();
        $moduleCode = $this->mollieHelper->getModuleCode();

		 // Debug mode
        if ($this->config->get($moduleCode . "_debug_mode")) {
            $this->writeToMollieDebugLog("===== DEBUG: RETURNED FROM MOLLIE (CALLBACK) =====");
            $this->writeToMollieDebugLog("CURRENT SESSION ID: " . $this->session->getId());
            $this->writeToMollieDebugLog("RECEIVED COOKIES: " . print_r($this->request->cookie, true));
            $this->writeToMollieDebugLog("RECEIVED GET URL VARS: " . print_r($this->request->get, true));
            $this->writeToMollieDebugLog("CURRENT SESSION DATA: " . print_r($this->session->data, true));
            $this->writeToMollieDebugLog("==================================================");
        }

        if ($order_id === false) {
            $this->writeToMollieLog("Callback : Failed to get order id.");

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        $this->writeToMollieLog("Received callback for order : " . $order_id);

        $order = $this->getOpenCartOrder($order_id);

        if (empty($order)) {
            $this->writeToMollieLog("Callback: Failed to get order for order id: " . $order_id);

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        // Load required translations.
        $this->load->language("extension/mollie/payment/mollie");
        $model = $this->getModuleModel();

        $mollie_payment_id = $model->getPaymentID($order['order_id']);

        if (empty($mollie_payment_id)) {
            $this->writeToMollieLog("Callback: Error getting mollie_payment_id for order " . $order['order_id']);

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        $success_redirect = false;

        if (!empty($mollie_payment_id)) {
            $paymentDetails = $this->getAPIClient()->payments->get($mollie_payment_id);

            // Debug mode
            if ($this->config->get($moduleCode . "_debug_mode")) {
                $this->writeToMollieDebugLog("===== DEBUG: MOLLIE API ORDER DETAILS =====");
                $this->writeToMollieDebugLog(print_r(json_decode(json_encode($paymentDetails), true), true));
                $this->writeToMollieDebugLog("===========================================");
            }

            // Create subscriptions if any
            $this->createSubscription($order, $paymentDetails);

            if ($paymentDetails->isPaid() || $paymentDetails->isAuthorized() || $paymentDetails->isPending()) {
                $success_redirect = true;
            }
        }

        $args = 'language=' . $this->config->get('config_language');

        if ($success_redirect) {
            $this->writeToMollieLog("Callback: Success redirect to success page for order - {$order['order_id']}, {$mollie_payment_id}");
            unset($this->session->data['mollie_issuer']);

            $this->setSameSiteCookie();
            $this->redirect($this->url->link('checkout/success', $args, true));
            return true;
        } else {
            if (!(bool)$this->config->get($moduleCode . "_show_order_canceled_page")) {
                $this->writeToMollieLog("Callback: Payment failed redirect to checkout page for order - {$order['order_id']}, {$mollie_payment_id}");

                $this->setSameSiteCookie();
                $this->redirect($this->url->link('checkout/checkout', $args, true));
                return true;
            } else {
                $this->writeToMollieLog("Callback: Payment failed redirect to failed page for order - {$order['order_id']}, {$mollie_payment_id}");
                return $this->showReturnPage(
                    $this->language->get("heading_failed"),
                    $this->language->get("msg_failed")
                );
            }
        }
    }

    /**
     * @param string $message
     * @return mixed
     */
    protected function showErrorPage(string $message): mixed {
        $this->load->language("extension/mollie/payment/mollie");

        $this->log->write("Mollie Event: Error setting up transaction with Mollie: {$message}.");

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
     * @param string $title
     * @param string $body
     * @param string|null $api_error
     * @param bool $show_retry_button
     * @param bool $show_report_button
     *
     * @return mixed
     */
    protected function showReturnPage(string $title, string $body, ?string $api_error = null, bool $show_retry_button = true, bool $show_report_button = false): mixed {
        $this->load->language("extension/mollie/payment/mollie");

        $data['message_title'] = $title;
        $data['message_text'] = $body;

        if ($api_error) {
            $data['mollie_error'] = $api_error;
        }

        $args = 'language=' . $this->config->get('config_language');

        if ($show_retry_button) {
            $data['checkout_url'] = $this->url->link('checkout/checkout', $args, true);
            $data['button_retry'] = $this->language->get("button_retry");
        }

        $data['show_retry_button'] = $show_retry_button;
        $data['method_separator']  = $this->getMethodSeparator();

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home', $args, true);

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
        return true;
    }

    /**
     * @return string|null
     */
    public function getWebhookUrl(): ?string {
        $system_webhook_url = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "webhook");

        if (str_contains((string)$system_webhook_url, $this->getAdminDirectory())) {
            return str_replace($this->getAdminDirectory(), "", (string)$system_webhook_url);
        }

        return $system_webhook_url ?: null;
    }

    /**
     * @return string
     */
    protected function getAdminDirectory(): string {
        if (!defined('HTTP_ADMIN')) {
            return "admin/";
        }
        return str_replace(HTTP_SERVER, "", HTTP_ADMIN);
    }

    /**
     * @param array $order
     * @param int|string $order_status_id
     * @param string $comment
     * @param bool $notify
     */
    protected function addOrderHistory(array $order, int|string $order_status_id, string $comment = "", bool $notify = false): void {
        $this->model_checkout_order->addHistory((int)$order['order_id'], (int)$order_status_id, $comment, $notify);
    }

    /**
     * @param string $url
     * @param int $status
     */
    protected function redirect(string $url, int $status = 302): void {
        $this->response->redirect($url, $status);
    }

	private function setSameSiteCookie(): void {
        $session_id = $this->session->getId();
        $session_name = $this->config->get('session_name') ?? session_name() ?? 'OCSESSID';

        if ($session_id) {
            setcookie($session_name, $session_id, [
                'expires'  => time() + 86400,
                'path'     => ini_get('session.cookie_path') ?: '/',
                'domain'   => ini_get('session.cookie_domain') ?: '',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'None'
            ]);
        }
    }

    private function createCustomer(array $data): string {
        $model = $this->getModuleModel();
        $api = $this->getAPIClient();

        // Check if customer already exists
        $mollie_customer_id = $model->getMollieCustomer($data['email']);
        if (!empty($mollie_customer_id)) {
            try {
                $customer = $api->customers->get($mollie_customer_id);
                return (string)$mollie_customer_id;
            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                // Remove customer from database
                $model->deleteMollieCustomer($data['email']);
                $this->writeToMollieLog("Customer does not exist, will be created: " . htmlspecialchars($e->getMessage()));
            }
        }

        $_data = [
            "name" => $data['firstname'] . ' ' . $data['lastname'],
            "email" => $data['email'],
            "metadata" => ["customer_id" => $data['customer_id']],
        ];

        $customer = $api->customers->create($_data);
        if (!empty($customer->id)) {
            $customerData = [
                "mollie_customer_id" => $customer->id,
                "customer_id" => $data['customer_id'],
                "email" => $data['email']
            ];
            $model->addCustomer($customerData);
            $mollie_customer_id = (string)$customer->id;
        } else {
            $mollie_customer_id = '';
        }

        $this->writeToMollieLog("Customer created: mollie_customer_id - {$mollie_customer_id}, customer_id - {$data['customer_id']}");

        return $mollie_customer_id;
    }

    public function setApplePaySession(): void {
        $apple_pay = (int)($this->request->post['apple_pay'] ?? 0);

        $this->session->data['applePay'] = $apple_pay;
        sleep(1);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    public function reportError(): void {
        $json = [];
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['mollie_error'])) {
            $this->load->language("extension/mollie/payment/mollie");

            $name = (string)$this->config->get('config_name');
            $email = (string)$this->config->get('config_email');
            $subject = 'Mollie Error: Front-end mollie error report';
            $message = (string)$this->request->post['mollie_error'];
            $message .= "<br>Opencart version : " . VERSION;
            $message .= "<br>Mollie version : " . \MollieHelper::PLUGIN_VERSION;

            if ($this->config->get('config_mail_engine')) {
                if (version_compare(VERSION, '4.0.2.0', '<')) {
                    $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode((string)$this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
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
                        'smtp_password' => html_entity_decode((string)$this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
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

    public function payLinkCallback(): mixed {
        $this->load->language("extension/mollie/payment/mollie");

        $payment_success = false;

        $order_id = (int)($this->request->get['order_id'] ?? 0);
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

                    if (empty($paymentLink['date_payment'])) {
                        $date_payment = date("Y-m-d H:i:s", strtotime($molliePaymentLink->paidAt));

                        $this->model_extension_mollie_payment_mollie_payment_link->updatePaymentLink($payment_link_id, $date_payment);

                        $new_status_id = (int)$this->config->get($moduleCode . "_ideal_processing_status_id");

                        if (!$new_status_id) {
                            $this->writeToMollieLog("Callback for payment link : The payment has been received. No 'processing' status ID is configured, so the order status for order {$paymentLink['order_id']} could not be updated.");
                            return $this->showReturnPage($this->language->get("heading_payment_failed"), $this->language->get("text_payment_failed"), null, false, false);
                        }

                        $order = $this->model_checkout_order->getOrder($paymentLink['order_id']);

                        $notify_customer = ($this->config->get($moduleCode . "_ideal_processing_status_notify")) ? true : false;

                        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), $notify_customer);

                        $this->writeToMollieLog("Callback for payment link : The payment was received and the order {$paymentLink['order_id']} was moved to the 'processing' status (new status ID: {$new_status_id}).");
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

        return $this->showReturnPage($title, $text, null, false, false);
    }

    // Credit Order
    public function creditOrder(): void {
        $json = [];
        $no_stock_mutation = [];

        $this->load->model('checkout/order');
        $order_id = (int)($this->request->get['order_id'] ?? 0);
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!empty($order_info)) {
            $creditData = $order_info;
            $creditData['products'] = [];
            $credit_product = [];

            if (!empty($this->request->post['productline']) && is_array($this->request->post['productline'])) {
                foreach ($this->request->post['productline'] as $order_product_id => $line) {
                    if (isset($line['selected'])) {
                        $credit_product[$order_product_id] = [
                            "order_product_id" => $order_product_id,
                            "quantity" => (int)$line['quantity']
                        ];
                    }

                    if (!isset($line['stock_mutation'])) {
                        $no_stock_mutation[] = $order_product_id;
                    }
                }
            }

            $order_sub_total = 0.0;
            $order_tax = 0.0;
            $order_total = 0.0;

            $order_products = $this->model_checkout_order->getProducts($order_id);
            foreach ($order_products as $product) {
                if (!empty($credit_product)) {
                    if (array_key_exists($product['order_product_id'], $credit_product)) {
                        $quantity = (int)$credit_product[$product['order_product_id']]['quantity'];
                        $price = (float)$product['price'];
                        $tax = (float)$product['tax'];

                        $order_sub_total += $price * $quantity;
                        $order_tax += $tax * $quantity;

                        $stock_mutation = !in_array($product['order_product_id'], $no_stock_mutation);

                        $creditData['products'][] = [
                            'product_id' => $product['product_id'],
                            'master_id'  => $product['master_id'] ?? 0,
                            'name'       => $product['name'],
                            'model'      => $product['model'],
                            'subscription' => false,
                            'option'     => $this->model_checkout_order->getOptions($order_id, $product['order_product_id']),
                            'quantity'   => -$quantity,
                            'price'      => $price,
                            'total'      => -($price * $quantity),
                            'tax'        => $tax,
                            'stock_mutation' => $stock_mutation,
                            'reward'     => -((float)$product['reward'])
                        ];
                    }
                } else {
                    $quantity = (int)$product['quantity'];
                    $price = (float)$product['price'];
                    $tax = (float)$product['tax'];

                    $order_sub_total += $price * $quantity;
                    $order_tax += $tax * $quantity;

                    $creditData['products'][] = [
                        'product_id' => $product['product_id'],
                        'master_id'  => $product['master_id'] ?? 0,
                        'name'       => $product['name'],
                        'model'      => $product['model'],
                        'subscription' => false,
                        'option'     => $this->model_checkout_order->getOptions($order_id, $product['order_product_id']),
                        'quantity'   => -$quantity,
                        'price'      => $price,
                        'total'      => -($price * $quantity),
                        'tax'        => $tax,
                        'stock_mutation' => true,
                        'reward'     => -((float)$product['reward'])
                    ];
                }
            }

            $order_total = $order_sub_total + $order_tax;
            $creditData['total'] = -$order_total;
            $creditData['totals'] = [];

            $order_totals = $this->model_checkout_order->getTotals($order_id);
            foreach ($order_totals as $_order_total) {
                if ($_order_total['code'] == 'sub_total') {
                    $creditData['totals'][] = [
                        "extension" => $_order_total['extension'],
                        "code" => $_order_total['code'],
                        "title" => $_order_total['title'],
                        "value" => -$order_sub_total,
                        "sort_order" => $_order_total['sort_order']
                    ];
                } elseif ($_order_total['code'] == 'tax') {
                    $creditData['totals'][] = [
                        "extension" => $_order_total['extension'],
                        "code" => $_order_total['code'],
                        "title" => $_order_total['title'],
                        "value" => -$order_tax,
                        "sort_order" => $_order_total['sort_order']
                    ];
                } elseif ($_order_total['code'] == 'total') {
                    $creditData['totals'][] = [
                        "extension" => $_order_total['extension'],
                        "code" => $_order_total['code'],
                        "title" => $_order_total['title'],
                        "value" => -$order_total,
                        "sort_order" => $_order_total['sort_order']
                    ];
                }
            }

            $credit_order_id = $this->model_checkout_order->addOrder($creditData);
            $this->model_checkout_order->addHistory($credit_order_id, (int)$this->config->get('config_order_status_id'));

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function cancelSubscription(): void {
        $this->load->language("extension/mollie/payment/mollie");
        $this->load->model("account/subscription");

        $model = $this->getModuleModel();
        $subscription_id = (int)($this->request->get['subscription_id'] ?? 0);

        $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);
        if ($subscription_info) {
            $order_id = $subscription_info['order_id'];
        } else {
            return;
        }

        $mollie_payment = $model->getPayment($order_id);
        $mollie_subscription_id = $mollie_payment['mollie_subscription_id'] ?? '';
        $mollie_customer = $model->getMollieCustomerById();

        if (!$mollie_customer || empty($mollie_subscription_id)) {
            $this->redirect($this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . ($this->session->data['customer_token'] ?? '') . '&subscription_id=' . $subscription_id));
            return;
        }

        $api = $this->getAPIClient();
        $customer = $api->customers->get($mollie_customer['mollie_customer_id']);

        try {
            $subscription = $customer->cancelSubscription($mollie_subscription_id);
            $this->writeToMollieLog("Subscription cancelled: subscription_id - {$subscription->id}, order_id - {$order_id}");

            // Update subscription status
            $this->load->model('checkout/subscription');
            $this->model_checkout_subscription->addHistory($subscription_id, (int)$this->config->get('config_subscription_canceled_status_id'));

            $this->session->data['success'] = $this->language->get('text_cancelled');
            $this->redirect($this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . ($this->session->data['customer_token'] ?? '') . '&subscription_id=' . $subscription_id));
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->writeToMollieLog("Error canceling subscription: " . htmlspecialchars($e->getMessage()));
            $this->session->data['error'] = sprintf($this->language->get('error_not_cancelled'), htmlspecialchars($e->getMessage()));
            $this->redirect($this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . ($this->session->data['customer_token'] ?? '') . '&subscription_id=' . $subscription_id));
        }
    }

    public function checkoutController(string &$route, array &$args): void {
        $script_path = 'extension/mollie/catalog/view/javascript/mollie.js';

        if (is_file(DIR_OPENCART . $script_path)) {
            $this->document->addScript($script_path);
        }

        $this->document->addScript('https://js.mollie.com/v1/mollie.js');
    }

    public function loginController(string &$route, array &$args): void {
        if (!empty($this->session->data['user_token'])) {
            $this->session->data['admin_login'] = true;
        }
    }

    public function checkoutPaymentMethodController(string &$route, array &$data, mixed &$template_code): void {
        if (empty($data['code']) || !is_string($data['code'])) {
            return;
        }

        if (str_contains($data['code'], 'mollie')) {
            $this->load->language('extension/mollie/payment/mollie');

            $parts = explode('.', $data['code']);
            $base_code = $parts[0];
            $payment_method = str_starts_with($base_code, 'mollie_') ? substr($base_code, 7) : $base_code;
            $description = $this->language->get('method_' . $payment_method);
            $title = $description;

            if (isset($this->mollieHelper)) {
                $moduleCode = $this->mollieHelper->getModuleCode();
                $language_id = (int)$this->config->get('config_language_id');
                $config_key = $moduleCode . "_" . $payment_method . "_description";
                $custom_descriptions = $this->config->get($config_key);

                if (is_array($custom_descriptions) && !empty($custom_descriptions[$language_id]['title'])) {
                    $title = $custom_descriptions[$language_id]['title'];
                }
            }
            $data['payment_method'] = $title;
        }
    }

    public function mailOrderController(string &$route, array &$data, mixed &$template_code): void {
        $data['payment_link_order_email'] = false;
        $order_id = (int)($data['order_id'] ?? 0);

        if ($order_id <= 0) {
            return;
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (empty($order_info)) {
            return;
        }

        $payment_code = (string)($order_info['payment_method']['code'] ?? $order_info['payment_code'] ?? '');

        if (!str_contains($payment_code, 'mollie_payment_link')) {
            return;
        }

        if (str_contains($route, 'order_history')) {
            $mollie_toggled = !empty($this->request->post['mollie_send_payment_link']);
            if (!$mollie_toggled) {
                return;
            }
        }

        $this->load->model('extension/mollie/payment/mollie_payment_link');
        $result = $this->model_extension_mollie_payment_mollie_payment_link->sendPaymentLink($order_info);

        $moduleCode = isset($this->mollieHelper) ? $this->mollieHelper->getModuleCode() : 'payment_mollie_ideal';
        $setting_key = $moduleCode . '_payment_link';

        $is_combined = (bool)$this->config->get($setting_key);

        if ($is_combined && !empty($result['payment_link'])) {
            $data['payment_link_order_email'] = true;
            $data['payment_link'] = $result['payment_link'];
        }
    }

    public function mailOrderTemplate(string &$route, array &$data, mixed &$template_code): void {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        if (empty($template_buffer)) {
            return;
        }

        $search_pattern = '/({{\s*text_footer\s*}})/i';
        $payment_link_html = '{% if payment_link_order_email %}<p style="margin-top: 0px; margin-bottom: 20px;">{{ payment_link }}</p>{% endif %}';

        $replaced_buffer = preg_replace($search_pattern, $payment_link_html . PHP_EOL . '$1', (string)$template_buffer, 1);

        if ($replaced_buffer !== null && $replaced_buffer !== $template_buffer) {
            $template_code = $replaced_buffer;
        } else {
            $this->log->write('Mollie Event: mailOrderTemplate - WARNING! Could not find {{ text_footer }} in the email template.');
        }
    }

    public function getPaymentMethodsAfter(string &$route, array &$args, array &$output): void {
        $current_route = (string)($this->request->get['route'] ?? $route);

        if (!str_starts_with($current_route, 'api/')) {
            return;
        }

        $mollie_active = false;
        foreach ($output as $key => $value) {
            if (str_contains((string)$key, 'mollie')) {
                $mollie_active = true;
                break;
            }
        }

        if (!$mollie_active) {
            return;
        }

        $this->load->language('extension/mollie/payment/mollie');
        $this->load->model('extension/mollie/payment/mollie_payment_link');

        $order_id = (int)($this->session->data['order_id'] ?? 0);
        $payment_link_details = [];

        if ($order_id > 0) {
            $payment_link_details = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLinkByOrderID($order_id);
        }

        $is_paid = !empty($payment_link_details) && !empty($payment_link_details['date_payment']);

        if ($is_paid) {
            $output['mollie_payment_link_full'] = [
                'code'       => 'mollie_payment_link_full',
                'name'       => $this->language->get('text_payment_link_full_title'),
                'option'     => [
                    'mollie_payment_link_full' => [
                        'code' => 'mollie_payment_link_full.mollie_payment_link_full',
                        'name' => $this->language->get('text_payment_link_full_title')
                    ]
                ],
                'sort_order' => 0
            ];

            $output['mollie_payment_link_open'] = [
                'code'       => 'mollie_payment_link_open',
                'name'       => $this->language->get('text_payment_link_open_title'),
                'option'     => [
                    'mollie_payment_link_open' => [
                        'code' => 'mollie_payment_link_open.mollie_payment_link_open',
                        'name' => $this->language->get('text_payment_link_open_title')
                    ]
                ],
                'sort_order' => 0
            ];
        } else {
            $output['mollie_payment_link'] = [
                'code'       => 'mollie_payment_link',
                'name'       => $this->language->get('text_payment_link_title'),
                'option'     => [
                    'mollie_payment_link' => [
                        'code' => 'mollie_payment_link.mollie_payment_link',
                        'name' => $this->language->get('text_payment_link_title')
                    ]
                ],
                'sort_order' => 0
            ];
        }
    }

    public function addOrderAfter(string &$route, array &$args, mixed &$output): void {
        $data = $args[0] ?? [];
        $order_id = (int)$output;

        if ($order_id <= 0 || empty($data['products'])) {
            return;
        }

        $this->processStockMutation($order_id, $data['products']);
    }

    public function editOrderAfter(string &$route, array &$args, mixed &$output): void {
        $order_id = (int)($args[0] ?? 0);
        $data = $args[1] ?? [];

        if ($order_id <= 0 || empty($data['products'])) {
            return;
        }

        $this->processStockMutation($order_id, $data['products']);
    }

    private function processStockMutation(int $order_id, array $products): void {
        foreach ($products as $product) {
            if (!isset($product['stock_mutation'])) {
                continue;
            }

            $stock_mutation = (int)$product['stock_mutation'];

            if (!empty($product['option']) && is_array($product['option'])) {
                $first_option = reset($product['option']);

                if (isset($first_option['product_option_value_id'], $first_option['value'])) {
                    $sql = "SELECT order_product_id FROM `" . DB_PREFIX . "order_option`
                            WHERE `order_id` = '" . (int)$order_id . "'
                            AND `product_option_value_id` = '" . (int)$first_option['product_option_value_id'] . "'
                            AND `value` = '" . $this->db->escape((string)$first_option['value']) . "'
                            LIMIT 1";

                    $query = $this->db->query($sql);

                    if ($query->num_rows) {
                        $order_product_id = (int)$query->row['order_product_id'];
                        $this->db->query("UPDATE `" . DB_PREFIX . "order_product`
                                          SET `stock_mutation` = '" . $stock_mutation . "'
                                          WHERE order_product_id = '" . $order_product_id . "'");
                    }
                }
            } else {
                $this->db->query("UPDATE `" . DB_PREFIX . "order_product`
                                  SET `stock_mutation` = '" . $stock_mutation . "'
                                  WHERE product_id = '" . (int)$product['product_id'] . "'
                                  AND order_id = '" . (int)$order_id . "'");
            }
        }
    }

    public function addHistoryAfter(string &$route, array &$args, mixed &$output): void {
        $this->load->model('checkout/order');

        $order_id = (int)($args[0] ?? 0);
        $order_status_id = (int)($args[1] ?? 0);

        if ($order_id <= 0 || $order_status_id <= 0) {
            return;
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (empty($order_info)) {
            return;
        }

        $order_products = $this->model_checkout_order->getProducts($order_id);

        $processing_statuses = (array)$this->config->get('config_processing_status');
        $complete_statuses = (array)$this->config->get('config_complete_status');
        $active_statuses = array_merge($processing_statuses, $complete_statuses);

        $old_status = (int)$order_info['order_status_id'];
        $new_status = $order_status_id;

        $was_active = in_array($old_status, $active_statuses);
        $is_active  = in_array($new_status, $active_statuses);

        if ($was_active === $is_active) {
            return;
        }

        foreach ($order_products as $order_product) {
            $has_stock_mutation = !empty($order_product['stock_mutation']);

            if (!$has_stock_mutation) {
                $qty = (int)$order_product['quantity'];
                $product_id = (int)$order_product['product_id'];
                $master_id = (int)($order_product['master_id'] ?? 0);

                if (!$was_active && $is_active) {
                    $operator = '+';
                } else {
                    $operator = '-';
                }

                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` {$operator} {$qty}) WHERE `product_id` = '" . $product_id . "' AND `subtract` = '1'");

                if ($master_id > 0) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` {$operator} {$qty}) WHERE `product_id` = '" . $master_id . "' AND `subtract` = '1'");
                }

                $order_options = $this->model_checkout_order->getOptions($order_id, $order_product['order_product_id']);

                foreach ($order_options as $order_option) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET `quantity` = (`quantity` {$operator} {$qty}) WHERE `product_option_value_id` = '" . (int)$order_option['product_option_value_id'] . "' AND `subtract` = '1'");
                }
            }
        }
    }

    protected function getTemplateBuffer(string $route, mixed $event_template_buffer): string {
        if (!empty($event_template_buffer) && is_string($event_template_buffer)) {
            return $event_template_buffer;
        }

        $clean_route = $route;
        if (str_contains($clean_route, 'extension/ocmod/')) {
            $clean_route = str_replace('extension/ocmod/', '', $clean_route);
        }

        if (str_contains($clean_route, 'catalog/view/template/')) {
            $clean_route = str_replace('catalog/view/template/', '', $clean_route);
        }

        $dir_template = (defined('DIR_CATALOG') ? DIR_CATALOG . 'view/template/' : DIR_TEMPLATE);

        $paths = [
            DIR_EXTENSION . 'ocmod/catalog/view/template/' . $clean_route . '.twig',
            $dir_template . $clean_route . '.twig'
        ];

        // OpenCart 4 Extension & Theme Path Resolution
        if (str_starts_with($clean_route, 'extension/')) {
            $parts = explode('/', $clean_route);

            if (count($parts) >= 3) {
                $developer = $parts[1];
                $module = $parts[2];

                $sub_path_1 = implode('/', array_slice($parts, 3));
                $sub_path_2 = implode('/', array_slice($parts, 2));

                $paths[] = DIR_EXTENSION . $developer . '/' . $module . '/catalog/view/template/' . $sub_path_1 . '.twig';
                $paths[] = DIR_EXTENSION . $developer . '/catalog/view/template/' . $sub_path_2 . '.twig';
                $paths[] = DIR_EXTENSION . $developer . '/theme/catalog/view/template/' . $sub_path_2 . '.twig';
            }
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                return file_get_contents($path);
            }
        }

        $this->log->write('Mollie Event Warning: Template file not found for route: ' . $route);
        return '';
    }

    public function getLanguageData(array $keys = []): array {
        $this->load->language("extension/mollie/payment/mollie");

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->language->get($key);
        }

        return $data;
    }

    public function accountSubscriptionController(string &$route, array &$data, mixed &$template_code): void {
        if (!str_contains($route, 'subscription_info')) {
            return;
        }

        $this->load->model('extension/mollie/payment/mollie_ideal');
        $this->load->language('extension/mollie/payment/mollie');

        $data['cancel_subscription'] = '';

        $order_id = (int)($data['order_id'] ?? 0);
        if ($order_id <= 0) {
            return;
        }

        $subscription_info = $this->model_extension_mollie_payment_mollie_ideal->getSubscription($order_id);
        $payment_method = (string)($data['payment_method'] ?? '');

        if (!empty($payment_method) && str_contains($payment_method, 'mollie')) {
            if ($subscription_info && isset($subscription_info->status)) {
                if (in_array($subscription_info->status, ['pending', 'active'])) {
                    $subscription_id = (int)($this->request->get['subscription_id'] ?? 0);

                    if ($subscription_id > 0) {
                        $route_path = 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'cancelSubscription';
                        $data['cancel_subscription'] = $this->url->link($route_path, 'subscription_id=' . $subscription_id, true);
                    }
                } else {
                    $data['cancel_subscription'] = false;
                }
            }
        }
    }

    public function accountSubscriptionTemplate(string &$route, array &$data, mixed &$template_code): void {
        if (!str_contains($route, 'subscription_info')) {
            return;
        }

        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        if (empty($template_buffer)) {
            return;
        }

        $search_pattern = '/(<button[^>]*id="button-cancel"[^>]*>.*?<\/button>)/is';

        $cancel_html = '{% if cancel_subscription %}
            <a href="{{ cancel_subscription }}" onclick="return confirm(\'{{ text_subscription_cancel_confirm }}\');" data-bs-toggle="tooltip" title="{{ button_subscription_cancel }}" class="btn btn-danger"><i class="fa-solid fa-ban me-2"></i> {{ button_subscription_cancel }}</a>
        {% else %}
            $1
        {% endif %}';

        $replaced_buffer = preg_replace($search_pattern, $cancel_html, (string)$template_buffer, 1);

        if ($replaced_buffer !== null && $replaced_buffer !== $template_buffer) {
            $template_code = $replaced_buffer;
        } else {
            $fallback_pattern = '/({{ content_bottom }})/i';
            $fallback_html = '{% if cancel_subscription %}<div class="text-end mb-3"><a href="{{ cancel_subscription }}" onclick="return confirm(\'{{ text_subscription_cancel_confirm }}\');" class="btn btn-danger"><i class="fa-solid fa-ban me-2"></i> {{ button_subscription_cancel }}</a></div>{% endif %}$1';
            $template_code = preg_replace($fallback_pattern, $fallback_html, (string)$template_buffer, 1);
        }
    }

    public function customerOrderInfoController(string &$route, array &$data, mixed &$template_code): void {
        if (!str_contains($route, 'order_info')) {
            return;
        }

        $order_id = (int)($this->request->get['order_id'] ?? 0);

        if ($order_id <= 0) {
            return;
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (empty($order_info)) {
            return;
        }

        $payment_code = (string)($order_info['payment_method']['code'] ?? $order_info['payment_code'] ?? '');

        if (!str_contains($payment_code, 'mollie_payment_link')) {
            return;
        }

        $this->load->model('extension/mollie/payment/mollie_payment_link');
        $link_info = $this->model_extension_mollie_payment_mollie_payment_link->getPaymentLinkByOrderID($order_id);

        if (!empty($link_info) && empty($link_info['date_payment'])) {
            try {
                $this->load->language('extension/mollie/payment/mollie');

                require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");
                $mollieHelper = new \MollieHelper($this->registry);
                $moduleCode = $mollieHelper->getModuleCode();

                $this->config->set($moduleCode . "_api_key", $mollieHelper->getApiKey((int)$order_info['store_id']));
                $mollieApi = $mollieHelper->getAPIClient($this->config);

                $paymentLink = $mollieApi->paymentLinks->get($link_info['payment_link_id']);
                $formatted_amount = $this->currency->format((float)$link_info['amount'], $link_info['currency_code'], 1);
                $text_open_payment = $this->language->get('text_mollie_open_payment');

                $data['mollie_payment_link_url'] = (string)$paymentLink->getCheckoutUrl();
                $data['text_mollie_open_payment'] = sprintf($text_open_payment, $formatted_amount);

            } catch (\Exception $e) {
                $this->log->write('Mollie API Error in customerOrderInfo: ' . $e->getMessage());
            }
        }
    }

    public function customerOrderInfoTemplate(string &$route, array &$data, mixed &$template_code): void {
        if (!str_contains($route, 'order_info')) {
            return;
        }

        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        if (empty($template_buffer)) {
            return;
        }

        $search_pattern = '/(<[^>]*>{{\s*text_order_detail\s*}}<\/[^>]*>)/i';

        $alert_html = '{% if mollie_payment_link_url %}
        <div class="alert alert-info d-flex align-items-center mb-4 mt-3 shadow-sm border-0" style="background-color: #e8f4fd; border-left: 5px solid #007bff !important;">
            <i class="fa-solid fa-circle-info me-3 fs-3" style="color: #007bff;"></i>
            <div class="flex-grow-1" style="color: #004085; font-size: 15px;">
                {{ text_mollie_open_payment }}
            </div>
            <div>
                <a href="{{ mollie_payment_link_url }}" class="btn btn-primary fw-bold text-uppercase px-4">{{ button_mollie_pay_now }}</a>
            </div>
        </div>
        {% endif %}';

        $replaced_buffer = preg_replace($search_pattern, '$1' . PHP_EOL . $alert_html, (string)$template_buffer, 1);

        if ($replaced_buffer !== null && $replaced_buffer !== $template_buffer) {
            $template_code = $replaced_buffer;
        } else {
            $fallback_pattern = '/({{ content_top }})/i';
            $template_code = preg_replace($fallback_pattern, '$1' . PHP_EOL . $alert_html, (string)$template_buffer, 1);
        }
    }
}
