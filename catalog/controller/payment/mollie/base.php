<?php
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

require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie/helper.php");

use util\Util;

class ControllerPaymentMollieBase extends Controller
{
    // Current module name - should be overwritten by subclass using one of the values below.
    const MODULE_NAME = null;

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

    /**
     * @return MollieApiClient
     */
    protected function getAPIClient()
    {
        return MollieHelper::getAPIClient($this->config);
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
        $log = new Log('Mollie.log');
        $log->write($line);
        if ($alsoEcho) echo $line;
    }

    protected function writeToMollieDebugLog($line, $alsoEcho = false)
    {
        $log = new Log('Mollie_debug.log');
        $log->write($line);
        if ($alsoEcho) echo $line;
    }

    /**
     * @return ModelExtensionPaymentMollieBase
     */
    protected function getModuleModel()
    {
        $model_name = "model_payment_mollie_" . static::MODULE_NAME;

        if (!isset($this->$model_name)) {
            Util::load()->model("payment/mollie_" . static::MODULE_NAME);
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
        $orderModel = Util::load()->model("checkout/order");
        // Load last order from session
        return $orderModel->getOrder($order_id);
    }

    //Get order products
    protected function getOrderProducts($order_id)
    {
        $model = Util::load()->model("payment/mollie_" . static::MODULE_NAME);

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
        $model = Util::load()->model("payment/mollie_" . static::MODULE_NAME);

        return $model->getCouponDetails($orderID);
    }

    //Get Voucher Details
    protected function getVoucherDetails($orderID) {
        $model = Util::load()->model("payment/mollie_" . static::MODULE_NAME);

        return $model->getVoucherDetails($orderID);
    }


    //Get Reward Point Details
    protected function getRewardPointDetails($orderID) {
        $model = Util::load()->model("payment/mollie_" . static::MODULE_NAME);

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
        if($this->config->get(MollieHelper::getModuleCode() . "_default_currency") == "DEF") {
            $currency = $this->session->data['currency'];
        } else {
            $currency = $this->config->get(MollieHelper::getModuleCode() . "_default_currency");
        }
        return $currency;
    }

    /**
     * This gets called by OpenCart at the final checkout step and should generate a confirmation button.
     * @return string
     */
    public function index()
    {
        Util::load()->language("payment/mollie");

        $payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME, array('include' => 'issuers'));

        // Set template data.
        $data['action']                  = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/payment");
        $data['image']                   = $payment_method->image->size1x;
        $data['message']                 = $this->language;
        $data['issuers']                 = isset($payment_method->issuers) ? $payment_method->issuers : array();
        $data['text_issuer']             = $this->language->get("text_issuer_" . static::MODULE_NAME);
        $data['set_issuer_url']          = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/set_issuer");
        $data['entry_card_holder']       = $this->language->get('entry_card_holder');
        $data['entry_card_number']       = $this->language->get('entry_card_number');
        $data['entry_expiry_date']       = $this->language->get('entry_expiry_date');
        $data['entry_verification_code'] = $this->language->get('entry_verification_code');
        $data['text_card_details']       = $this->language->get('text_card_details');
        $data['error_card']              = $this->language->get('error_card');
        $data['text_mollie_payments']    = sprintf($this->language->get('text_mollie_payments'), '<a href="https://www.mollie.com/" target="_blank"><img src="./image/mollie/mollie_logo.png" alt="Mollie" border="0"></a>');

        // Mollie components
        $data['mollieComponents'] = false;
        if(static::MODULE_NAME == 'creditcard') {
            if($this->config->get(MollieHelper::getModuleCode() . "_mollie_component")) {
                // Get current profile
                $data['currentProfile'] = $this->getAPIClient()->profiles->getCurrent()->id;

                if (strstr(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'), '-')) {
                    list ($language, $country) = explode('-', isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                    $locale = strtolower($language) . '_' . strtoupper($country);
                } else {
                    $locale = strtolower(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) . '_' . strtoupper(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                }

                if (!in_array($locale, $this->locales)) {
                    $locale = $this->config->get(MollieHelper::getModuleCode() . "_payment_screen_language");
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
                $data['base_input_css']    = $this->config->get(MollieHelper::getModuleCode() . "_mollie_component_css_base");
                $data['valid_input_css']   = $this->config->get(MollieHelper::getModuleCode() . "_mollie_component_css_valid");
                $data['invalid_input_css'] = $this->config->get(MollieHelper::getModuleCode() . "_mollie_component_css_invalid");
                $apiKey =  $this->config->get(MollieHelper::getModuleCode() . "_api_key");
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

        // Return HTML output - it will get appended to confirm.tpl.
        return $this->renderTemplate('mollie_checkout_form', $data, array(), false);
    }

    protected function convertCurrency($amount) {
        $currency = Util::load()->model("localisation/currency");
        $currencies = $currency->getCurrencies();
        $convertedAmount = $amount * $currencies[$this->getCurrency()]['value'];

        return $convertedAmount;
    }

    //Format text
    protected function formatText($text) {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * The payment action creates the payment and redirects the customer to the selected bank.
     *
     * It is called when the customer submits the button generated in the mollie_checkout_form template.
     */
    public function payment()
    {
        if (Util::request()->server()->REQUEST_METHOD != 'POST') {
            return;
        }
        try {
            $api = $this->getAPIClient();
        } catch (Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage($e->getMessage());
            $this->writeToMollieLog("Creating payment failed, API did not load; " . $e->getMessage());
            return;
        }
        // Load essentials
        Util::load()->language("payment/mollie");

        $model = $this->getModuleModel();
        $order_id = $this->getOrderID();
        $order = $this->getOpenCartOrder($order_id);

        $currency = $this->getCurrency();
        $amount = $this->convertCurrency($order['total']);
        //$description = str_replace("%", $order['order_id'], html_entity_decode($this->config->get(MollieHelper::getModuleCode() . "_ideal_description"), ENT_QUOTES, "UTF-8"));
        $return_url = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/callback", "order_id=" . $order['order_id']);
        $issuer = $this->getIssuer();

        try {
            $data = array(
                "amount" => ["currency" => $currency, "value" => (string)$this->numberFormat($amount)],
                "orderNumber" => $order['order_id'],
                "redirectUrl" => $this->formatText($return_url),
                "webhookUrl" => $this->getWebhookUrl(),
                "metadata" => array("order_id" => $order['order_id']),
                "method" => static::MODULE_NAME,
            );

            $data['payment'] = array(
                "issuer" => $this->formatText($issuer),
                "webhookUrl" => $this->getWebhookUrl()
            );

            // Send cardToken in case of creditcard(if available)
            if (Util::request()->post()->cardToken) {
                $data['payment']['cardToken'] = Util::request()->post()->cardToken;
            }

            //Order line data
            $orderProducts = $this->getOrderProducts($order['order_id']);
            $lines = array();

            $productModel = Util::load()->model('catalog/product');
            foreach($orderProducts as $orderProduct) {
                $productDetails = $productModel->getProduct($orderProduct['product_id']);
                $tax_rates = $this->tax->getRates($orderProduct['price'], $productDetails['tax_class_id']);
                $rates = $this->getTaxRate($tax_rates);
                //Since Mollie only supports VAT so '$rates' must contains only one(VAT) rate.
                $vatRate = isset($rates[0]) ? $rates[0] : 0;
                $total = $this->numberFormat($this->convertCurrency(($orderProduct['price'] + $orderProduct['tax']) * $orderProduct['quantity']));

                // Fix for qty < 1
                if(!is_int($orderProduct['quantity'])) {
                    $qty = 1;
                    $price = $orderProduct['price'] * $orderProduct['quantity'];
                    $tax = $orderProduct['tax'] * $orderProduct['quantity'];
                } else {
                    $qty = $orderProduct['quantity'];
                    $price = $orderProduct['price'];
                    $tax = $orderProduct['tax'];
                }

                $vatAmount = $total * ( $vatRate / (100 +  $vatRate));
                $lines[] = array(
                    'type'          =>  'physical',
                    'name'          =>  $this->formatText($orderProduct['name']),
                    'quantity'      =>  $qty,
                    'unitPrice'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($this->convertCurrency($price + $tax))],
                    'totalAmount'   =>  ["currency" => $currency, "value" => (string)$this->numberFormat($total)],
                    'vatRate'       =>  (string)$this->numberFormat($vatRate),
                    'vatAmount'     =>  ["currency" => $currency, "value" => (string)$this->numberFormat($vatAmount)]
                );
            }

            //Check for shipping fee
            if(isset($this->session->data['shipping_method'])) {
                $title = $this->session->data['shipping_method']['title'];
                $cost = $this->session->data['shipping_method']['cost'];
                $taxClass = $this->session->data['shipping_method']['tax_class_id'];
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

            //Check if coupon applied
            if(isset($this->session->data['coupon'])) {
                //Get coupon data
                if(Util::version()->isMaximal("2.0.3.1")) {
                    $coupon = Util::load()->model('checkout/coupon');
                } else {
                    $coupon = Util::load()->model('total/coupon');
                }

                $coupon_info = $coupon->getCoupon($this->session->data['coupon']);

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
                            $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

                            foreach ($tax_rates as $tax_rate) {
                                if ($tax_rate['type'] == 'P') {
                                    $couponVATAmount += $tax_rate['amount'];
                                }
                            }
                        }

                        $discount_total += $this->session->data['shipping_method']['cost'];
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

                if(MollieHelper::isOpenCart3x()) {
                    $typePrefix = 'total_';
                } else {
                    $typePrefix = '';
                }

                foreach($otherOrderTotals as $orderTotals) {

                    if($this->config->get($typePrefix . $orderTotals['code'] . '_tax_class_id')) {
                        $taxClass = $this->config->get($typePrefix . $orderTotals['code'] . '_tax_class_id');
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
                /*
                 * This data is sent along for credit card payments / fraud checks. You can remove this but you will
                 * have a higher conversion if you leave it here.
                 */
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
            } else {
                $data["shippingAddress"] = $data["billingAddress"];
            }

            if (strstr(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'), '-')) {
                list ($language, $country) = explode('-', isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
                $locale = strtolower($language) . '_' . strtoupper($country);
            } else {
                $locale = strtolower(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) . '_' . strtoupper(isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'));
            }

            if (!in_array($locale, $this->locales)) {
                $locale = $this->config->get(MollieHelper::getModuleCode() . "_payment_screen_language");
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
            if($this->config->get(MollieHelper::getModuleCode() . "_debug_mode")) {
                $this->writeToMollieDebugLog("Mollie order creation data :");
                $this->writeToMollieDebugLog($data);
            }

            //Create Order
            $orderObject = $api->orders->create($data);

        } catch (Mollie\Api\Exceptions\ApiException $e) {
            $this->showErrorPage($e->getMessage());
            $this->writeToMollieLog("Creating order failed for order_id - " . $order['order_id'] . ' ; ' . $e->getMessage());
            return;
        }

        // Some payment methods can't be cancelled. They need an initial order status.
        if ($this->startAsPending()) {
            $this->addOrderHistory($order, $this->config->get(MollieHelper::getModuleCode() . "_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
        }

        if($model->setPayment($order['order_id'], $orderObject->id, $orderObject->method)) {
            $this->writeToMollieLog("Order created : order_id - " . $order['order_id'] . ', ' . "mollie_order_id - " . $orderObject->id);
        } else {
            $this->writeToMollieLog("Order created for order_id - " . $order['order_id'] . " but mollie_order_id - " . $orderObject->id . " not saved in the database. Should be updated when webhook called.");
        }

        // Redirect to payment gateway.
        $this->redirect($orderObject->_links->checkout->href, 303);
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
        } else {
            $this->webhookForPayment($id);
        }

    }

    private function webhookForPayment($payment_id) {

        $moduleCode = MollieHelper::getModuleCode();

        $this->writeToMollieLog("Received webhook for payment : {$payment_id}");

        $molliePayment = $this->getAPIClient()->payments->get($payment_id);

        $mollieOrderId = $molliePayment->orderId;

        $mollieOrder = $this->getAPIClient()->orders->get($mollieOrderId);

        // Load essentials
        Util::load()->model("checkout/order");
        $model = $this->getModuleModel();
        Util::load()->language("payment/mollie");

        //Get order_id of this transaction from db
        $order = $this->model_checkout_order->getOrder($mollieOrder->metadata->order_id);

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
                'status'     => $molliePayment->status
            );
        }

        if(!empty($data)) {
            $model->updatePayment($mollieOrder->metadata->order_id, $mollieOrderId, $data);
            $this->writeToMollieLog("Webhook for payment : transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$mollieOrder->metadata->order_id}, mollie_order_id - $mollieOrderId");
        }

        if($order['order_status_id'] != 0) {
            //Check for refund
            if(isset($molliePayment->amountRefunded->value) && ($molliePayment->amountRefunded->value > 0)) {
                $data = array(
                    'payment_id' => $payment_id,
                    'status'     => 'refunded'
                );

                if(!empty($data)) {
                    $model->updatePayment($mollieOrder->metadata->order_id, $mollieOrderId, $data);
                    $this->writeToMollieLog("Webhook for payment : Updated mollie payment. transaction_id - {$payment_id}, status - {$data['status']}, order_id - {$mollieOrder->metadata->order_id}, mollie_order_id - $mollieOrderId");
                }

                $this->writeToMollieLog("Webhook for payment : Order status has been updated to 'Refunded' for order - {$order['order_id']}, {$mollieOrderId}");
            } else {
                if (!empty($order['order_status_id']) && $order['order_status_id'] == $this->config->get($moduleCode . "_ideal_refund_status_id")) {
                    $data['refund_id'] = '';
                    $model->cancelReturn($mollieOrder->metadata->order_id, $mollieOrderId, $data);
                    $this->addOrderHistory($order, $this->config->get($moduleCode . "_ideal_processing_status_id"), $this->language->get("refund_cancelled"), true);
                    $this->writeToMollieLog("Webhook for payment : Refund has been cancelled for order - {$order['order_id']}, {$mollieOrderId}");
                    $this->writeToMollieLog("Webhook for payment : Order status has been updated to 'Processing' for order - {$order['order_id']}, {$mollieOrderId}");
                }
            }

            return;
        }

        // Only process the status if the order is stateless or in 'pending' status.
        if (!empty($order['order_status_id']) && $order['order_status_id'] != $this->config->get($moduleCode . "_ideal_pending_status_id")) {
            $this->writeToMollieLog("Webhook for payment : The order {$order['order_id']}, {$mollieOrderId} was already processed before (order status ID: " . intval($order['order_status_id']) . ")");
            return;
        }

        // Payment cancelled.
        if ($molliePayment->status == PaymentStatus::STATUS_CANCELED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_canceled_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment : The payment was cancelled. No 'cancelled' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId} could not be updated.", true);
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
            $this->writeToMollieLog("Webhook for payment : The payment was cancelled and the order {$order['order_id']}, {$mollieOrderId} was moved to the 'cancelled' status (new status ID: {$new_status_id}).", true);
            return;
        }

        // Payment expired.
        if ($molliePayment->status == PaymentStatus::STATUS_EXPIRED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_expired_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for payment : The payment expired. No 'expired' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId} could not be updated.", true);
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
            $this->writeToMollieLog("Webhook for payment : The payment expired and the order {$order['order_id']}, {$mollieOrderId} was moved to the 'expired' status (new status ID: {$new_status_id}).", true);
            return;
        }

        // Otherwise, payment failed.
        $new_status_id = intval($this->config->get($moduleCode . "_ideal_failed_status_id"));

        if (!$new_status_id) {
            $this->writeToMollieLog("Webhook for payment : The payment failed. No 'failed' status ID is configured, so the order status for order {$order['order_id']}, {$mollieOrderId} could not be updated.", true);
            return;
        }
        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_unknown"), false);
        $this->writeToMollieLog("Webhook for payment : The payment failed for an unknown reason and the order {$order['order_id']}, {$mollieOrderId} was moved to the 'failed' status (new status ID: {$new_status_id}).", true);
        return;

    }

    private function webhookForOrder($order_id) {

        $moduleCode = MollieHelper::getModuleCode();

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
                'status'     => $payment->status
            );
            $model->updatePayment($mollieOrder->metadata->order_id, $order_id, $paymentData);
            $this->writeToMollieLog("Webhook for order : Updated mollie payment. transaction_id - {$payment->id}, status - {$paymentData['status']}, order_id - {$mollieOrder->metadata->order_id}, mollie_order_id - $order_id");            
        }

        // Load essentials
        Util::load()->model("checkout/order");
        $this->getModuleModel();
        Util::load()->language("payment/mollie");

        //Get order_id of this transaction from db
        $order = $this->model_checkout_order->getOrder($mollieOrder->metadata->order_id);

        if (empty($order)) {
            header("HTTP/1.0 404 Not Found");
            echo "Could not find order.";
            return;
        }

        if($order['order_status_id'] != 0) {
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
                $this->writeToMollieLog("Webhook for order : The payment has been received/authorised. No 'processing' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.", true);
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
            $this->writeToMollieLog("Webhook for order : The payment was received/authorised and the order {$order['order_id']}, {$order_id} was moved to the 'processing' status (new status ID: {$new_status_id}).", true);
            return;
        }

        // Order cancelled.
        if ($mollieOrder->status == PaymentStatus::STATUS_CANCELED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_canceled_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for order : The payment was cancelled. No 'cancelled' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.", true);
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
            $this->writeToMollieLog("Webhook for order : The payment was cancelled and the order {$order['order_id']}, {$order_id} was moved to the 'cancelled' status (new status ID: {$new_status_id}).", true);
            return;
        }

        // Order expired.
        if ($mollieOrder->status == PaymentStatus::STATUS_EXPIRED) {
            $new_status_id = intval($this->config->get($moduleCode . "_ideal_expired_status_id"));

            if (!$new_status_id) {
                $this->writeToMollieLog("Webhook for order : The payment expired. No 'expired' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.", true);
                return;
            }
            $this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
            $this->writeToMollieLog("Webhook for order : The payment expired and the order {$order['order_id']}, {$order_id} was moved to the 'expired' status (new status ID: {$new_status_id}).", true);
            return;
        }

        // Otherwise, order failed.
        $new_status_id = intval($this->config->get($moduleCode . "_ideal_failed_status_id"));

        if (!$new_status_id) {
            $this->writeToMollieLog("Webhook for order : The payment failed. No 'failed' status ID is configured, so the order status for order {$order['order_id']}, {$order_id} could not be updated.", true);
            return;
        }
        $this->addOrderHistory($order, $new_status_id, $this->language->get("response_unknown"), false);
        $this->writeToMollieLog("Webhook for order : The payment failed for an unknown reason and the order {$order['order_id']}, {$order_id} was moved to the 'failed' status (new status ID: {$new_status_id}).", true);
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
        if (!empty(Util::request()->post()->mollie_issuer)) {
            return Util::request()->post()->mollie_issuer;
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
        
        $moduleCode = MollieHelper::getModuleCode();

        if (Util::version()->isMaximal("1.5.6.4")) {
            $orderModel = Util::load()->model("sale/order");
            $mollieModel = $orderModel;
        }
        else {
            $orderModel = Util::load()->model("checkout/order");
            $mollieModel = Util::load()->model('payment/mollie/base');
        }
        
        Util::load()->language("payment/mollie");

        //Get order_id of this transaction from db
        $order = $orderModel->getOrder($order_id);
        if (empty($order)) {
            header("HTTP/1.0 404 Not Found");
            echo "Could not find order.";
            return;
        } elseif(!in_array($order['payment_code'], ['mollie_klarnapaylater', 'mollie_klarnasliceit'])) {
            // Do nothing if payment method is not klarna
            return;
        }

        $mollie_order_id = $mollieModel->getOrderID($order_id);
        if (empty($mollie_order_id)) {
            $this->writeToMollieLog("Could not find mollie reference order id for shipment creation for order {$order['order_id']}, {$mollie_order_id} (It could be a non-mollie order).");
            return;
        }
        
        /*Check if shipment is not created already at the time of order creation
        $this->config->get($moduleCode . "_create_shipment")
        -> '!= 1' (Shipment is not created already)
        -> '== 2' (Shipment needs to be created after one of the statuses set in the module setting)
        -> else, (Shipment needs to be created after one of the 'Order Complete Statuses' set in the store setting)
        */

         $mollieOrder = $this->getAPIClient()->orders->get($mollie_order_id);
         if($mollieOrder->isAuthorized() && ($this->config->get($moduleCode . "_create_shipment") != 1)) {
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

        $moduleCode = MollieHelper::getModuleCode();
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
        Util::load()->language("payment/mollie");

        // Double-check whether or not the status of the order is correct.
        $model = $this->getModuleModel();

        $paid_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));
        $pending_status_id = intval($this->config->get($moduleCode . "_ideal_pending_status_id"));
        $mollie_order_id = $model->getOrderID($order['order_id']);

        if ($mollie_order_id === false) {
            $this->writeToMollieLog("Error getting mollie_order_id for order " . $order['order_id']);

            return $this->showReturnPage(
                $this->language->get("heading_failed"),
                $this->language->get("msg_failed")
            );
        }

        $orderDetails = $this->getAPIClient()->orders->get($mollie_order_id, ["embed" => "payments"]);

        // Update payment status
        if(!empty($orderDetails->_embedded->payments)) {
            $payment = $orderDetails->_embedded->payments[0];
            $paymentData = array(
                'payment_id' => $payment->id,
                'status'     => $payment->status
            );
            $model->updatePayment($orderDetails->metadata->order_id, $mollie_order_id, $paymentData);
            $this->writeToMollieLog("Updated mollie payment. transaction_id - {$payment->id}, status - {$paymentData['status']}, order_id - {$orderDetails->metadata->order_id}, mollie_order_id - $mollie_order_id");
                        
        }

        $orderStatuses = $model->getOrderStatuses($order['order_id']);

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

        /* Check module module setting for shipment creation,
        $this->config->get($moduleCode . "_create_shipment")) == 1,
        satisfies the 'Create shipment immediately after order creation' condition. */
        
        if($orderDetails->isAuthorized() && ($this->config->get($moduleCode . "_create_shipment")) == 1) {
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
            $this->redirect(Util::url()->link("checkout/success"));
            return '';
        }

        // If the status is 'pending' (i.e. a bank transfer), the report is not delivered yet.
        if ($order['order_status_id'] == $this->config->get($moduleCode . "_ideal_pending_status_id")) {
            $this->writeToMollieLog("Unknown payment status for order - {$order['order_id']}, {$mollie_order_id}");

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
            $this->redirect(Util::url()->link("checkout/checkout"));
        }

        // Show a 'transaction failed' page if all else fails.
        $this->writeToMollieLog("Everything else failed for order - {$order['order_id']}, {$mollie_order_id}");

        return $this->showReturnPage(
            $this->language->get("heading_failed"),
            $this->language->get("msg_failed")
        );
    }

    /**
     * @param &$data
     */
    protected function setBreadcrumbs(&$data)
    {
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            "href" => Util::url()->link("common/home"),
            "text" => $this->language->get("text_home"),
            "separator" => false,
        );
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function showErrorPage($message)
    {
        Util::load()->language("payment/mollie");

        $this->log->write("Error setting up transaction with Mollie: {$message}.");

        return $this->showReturnPage(
            $this->language->get("heading_error"),
            $this->language->get("text_error"),
            $message
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
    protected function showReturnPage($title, $body, $api_error = null, $show_retry_button = true)
    {
        Util::load()->language("payment/mollie");

        $data['message_title'] = $title;
        $data['message_text'] = $body;

        if ($api_error) {
            $data['mollie_error'] = $api_error;
        }

        if ($show_retry_button) {
            $data['checkout_url'] = Util::url()->link("checkout/checkout");
            $data['button_retry'] = $this->language->get("button_retry");
        }

        $this->document->setTitle($this->language->get("ideal_title"));

        $this->setBreadcrumbs($data);

        return $this->renderTemplate("mollie_return", $data, array(
            "column_left",
            "column_right",
            "content_top",
            "content_bottom",
            "footer",
            "header",
        ));
    }

    /**
     * We check for and remove the admin url in the webhook link.
     *
     * @return string|null
     */
    public function getWebhookUrl()
    {
        $system_webhook_url = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/webhook");

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
        if (MollieHelper::isOpenCart2x()) {
            $this->model_checkout_order->addOrderHistory($order['order_id'], $order_status_id, $comment, $notify);
        } else {
            if (empty($order['order_status_id'])) {
                $this->model_checkout_order->confirm($order['order_id'], $order_status_id, $comment, $notify);
            } else {
                $this->model_checkout_order->update($order['order_id'], $order_status_id, $comment, $notify);
            }
        }
    }

    /**
     * Map template handling for different Opencart versions.
     *
     * @param string $template
     * @param array $data
     * @param array $common_children
     * @param bool $echo
     * @return string
     */
    protected function renderTemplate($template, $data, $common_children = array(), $echo = true)
    {
        if(Util::version()->isMinimal("2.2.0.0")) {
            $template = 'payment/' . $template;            
        } elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/' . $template . '.tpl')) {
            $template = $this->config->get('config_template') . '/template/payment/' . $template . '.tpl';
        } else {
            $template = 'default/template/payment/' . $template . '.tpl';
        }

        if (MollieHelper::isOpenCart2x()) {
            foreach ($common_children as $child) {
                $data[$child] = Util::load()->controller("common/" . $child);
            }
            
            $html = $this->load->view($template, $data);

        } else {
            $this->template = $template;
            $this->children = array();

            foreach ($data as $field => $value) {
                $this->data[$field] = $value;
            }

            foreach ($common_children as $child) {
                if ($child === 'column_left') {
                    continue;
                }

                $this->children[] = "common/" . $child;
            }

            $html = $this->render();
        }

        if ($echo) {
            return $this->response->setOutput($html);
        }

        return $html;
    }

    /**
     * @param string $url
     * @param int $status
     */
    protected function redirect($url, $status = 302)
    {
        $this->response->redirect($url, $status);
    }
}
