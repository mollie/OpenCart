<?php
require_once(DIR_SYSTEM . "library/mollie/helper.php");
require_once(DIR_SYSTEM . "/library/mollie/mollieHttpClient.php");

class ModelPaymentMollie extends Model {
    public $mollieHelper;

	public function __construct($registry) {
		parent::__construct($registry);
    
    	$this->mollieHelper = new MollieHelper($registry);
	}

    protected function getAPIClient($store) {
        $data = $this->config;
        $data->set($this->mollieHelper->getModuleCode() . "_api_key", $this->mollieHelper->getApiKey($store));

        return $this->mollieHelper->getAPIClient($data);
    }

    public function numberFormat($amount, $currency) {
        $intCurrencies = array("ISK", "JPY");
        if(!in_array($currency, $intCurrencies)) {
            $formattedAmount = number_format((float)$amount, 2, '.', '');
        } else {
            $formattedAmount = number_format($amount, 0);
        }   
        return $formattedAmount;    
    }

    protected function convertCurrency($amount, $currency) {
        $this->load->model("localisation/currency");

        $currencies = $this->model_localisation_currency->getCurrencies();
        $convertedAmount = $amount * $currencies[$currency]['value'];

        return $convertedAmount;
    }

    public function getMolliePayment($order_id) {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE order_id = '" . (int)$order_id . "' ORDER BY payment_attempt DESC LIMIT 1");

        if ($_query->num_rows) {
            return $_query->row;
        }

        return null;
    }

    public function getPaymentID($order_id) {
		if (!empty($order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . (int)$order_id . "'");
			if($results->num_rows == 0) return FALSE;

			return $results->row['transaction_id'];
		}

		return FALSE;
	}

    public function getOrderID($order_id) {
		if (!empty($order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . (int)$order_id . "' ORDER BY payment_attempt DESC LIMIT 1");
			if($results->num_rows == 0) return FALSE;

			return isset($results->row['mollie_order_id']) ? $results->row['mollie_order_id'] : '';
		}

		return FALSE;
	}

    public function getMolliePaymentLinks($order_id) {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payment_link` WHERE order_id = '" . (int)$order_id . "'");

        return $_query->rows;
    }

    public function getMolliePayments($order_info) {
        $mollie_payments = array();

        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE order_id = '" . (int)$order_info['order_id'] . "' ORDER BY payment_attempt DESC");

        foreach ($_query->rows as $mollie_payment) {
            $mollie_payments[] = array(
                "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment['date_modified'])),
                "method" => ucfirst($mollie_payment['method']),
                "amount" => (isset($mollie_payment['amount']) && !empty($mollie_payment['amount'])) ? $this->currency->format($mollie_payment['amount'], $order_info['currency_code'], 1) : $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
                "status" => !empty($mollie_payment['bank_status']) ? ucfirst($mollie_payment['bank_status']) : '',
            );
        }

        if ($order_info['payment_code'] == 'mollie_payment_link') {
            $mollie_payment_links = $this->getMolliePaymentLinks($order_info['order_id']);

            foreach($mollie_payment_links as $mollie_payment_link) {
                $data['mollie_payments'][] = array(
                    "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_created'])),
                    "method" => 'N/A',
                    "amount" => (isset($mollie_payment_link['amount']) && !empty($mollie_payment_link['amount'])) ? $this->currency->format($mollie_payment_link['amount'], $order_info['currency_code'], 1) : $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
                    "status" => ($mollie_payment_link['date_payment']) ? 'Paid (' . date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_payment'])) . ')' : 'Open'
                );
            }
        }

        return $mollie_payments;
    }

    public function updateMolliePaymentForPaymentAPI($mollie_payment_id, $refund_id, $payment_status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET refund_id = '" . $this->db->escape($refund_id) . "', bank_status = '" . $this->db->escape($payment_status) . "' WHERE transaction_id = '" . $this->db->escape($mollie_payment_id) . "'");
    }

    public function updateMollieRefundStatus($refund_id, $transaction_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "mollie_refund` SET status = '" . $this->db->escape($status) . "' WHERE refund_id = '" . $this->db->escape($refund_id) . "' AND transaction_id = '" . $this->db->escape($transaction_id) . "'");
    }

    public function addMollieRefund($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mollie_refund SET refund_id = '" . $this->db->escape($data['refund_id']) . "', order_id = '" . (int)$data['order_id'] . "', transaction_id = '" . $this->db->escape($data['transaction_id']) . "', amount = '" . (float)$data['amount'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', status = '" . $this->db->escape($data['status']) . "', date_created = NOW()");
    }

    public function stockMutation($order_id, $data = array()) {
        $this->load->model('sale/order');

        foreach ($data as $stock_mutation_data) {
            $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$stock_mutation_data['order_product_id'] . "'");

            $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$stock_mutation_data['quantity'] . ") WHERE product_id = '" . (int)$order_product_query->row['product_id'] . "' AND subtract = '1'");

            $order_options = $this->model_sale_order->getOrderOptions($order_id, $stock_mutation_data['order_product_id']);

            foreach ($order_options as $order_option) {
                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$stock_mutation_data['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
            }
        }
    }

    public function showRefundButton($store_id) {
        $apiKey = $this->mollieHelper->getApiKey($store_id);

        $showRefundButton = true;
        if(!$apiKey || ($apiKey == '')) {
            $showRefundButton = false;
        }

        return $showRefundButton;
    }

    public function showPartialRefundButton($store_id) {
        $apiKey = $this->mollieHelper->getApiKey($store_id);

        $showPartialRefundButton = true;
        if(!$apiKey || ($apiKey == '')) {
            $showPartialRefundButton = false;
        }

        return $showPartialRefundButton;
    }

    public function getModuleCode() {
        $code = $this->mollieHelper->getModuleCode();

        return $code;
    }

    public function getMollieRefunds($order_info) {
        $mollie_refunds = array();

        $_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mollie_refund WHERE order_id = '" . (int)$order_info['order_id'] . "'");

        if ($_query->rows) {
            foreach ($_query->rows as $refund) {
                $mollieRefund = $this->getAPIClient($order_info['store_id'])->payments->get($refund['transaction_id'])->getRefund($refund['refund_id']);
                
                if ($mollieRefund->status != $refund['status']) {
                    $this->updateMollieRefundStatus($refund['refund_id'], $refund['transaction_id'], $mollieRefund->status);
                }

                $mollie_refunds[] = array(
                    "date_added" => date($this->language->get('date_format_short'), strtotime($refund['date_created'])),
                    "amount" => $this->currency->format($refund['amount'], $order_info['currency_code'], 1),
                    "status" => ucfirst($mollieRefund->status)
                );
            }
        }

        return $mollie_refunds;
    }

    public function getMolliePaymentMethod($order_info, $showRefundButton) {
        $payment_status = '';
        $paymentMethod = '';

        $molliePaymentDetails = $this->getMolliePayment($order_info['order_id']);
        if(isset($molliePaymentDetails['transaction_id']) && !empty($molliePaymentDetails['transaction_id'])) {
            try {
                $molliePayment = $this->getAPIClient($order_info['store_id'])->payments->get($molliePaymentDetails['transaction_id']);
                $payment_status = $molliePayment->status;
                $paymentMethod = $molliePayment->method;

                // Voucher amount cannot be refunded
                if ($molliePayment->method == 'voucher') {
                    $showRefundButton = false;
                }

                // Check for refunds and settlements
                if($molliePayment->hasRefunds()) {
                    $payment_status = 'refunded';
                }

                /*if(!empty($molliePayment->settlementId)) {
                    $accessToken = $this->mollieHelper->generateAccessToken($order_info['store_id']);
                    if($accessToken && !empty($accessToken)) {
                        $mollieSettlement = $this->mollieHelper->getAPIClientForAccessToken($accessToken)->settlements->get($molliePayment->settlementId);
                        if($mollieSettlement->isPaidout()) {
                            $payment_status = 'settled';
                        }
                    }
                }*/
            } catch (Mollie\Api\Exceptions\ApiException $e) {
                $log = new Log('Mollie.log');
                $log->write(htmlspecialchars($e->getMessage()));
            }
        }

        if ($order_info['payment_code'] == 'mollie_payment_link') {
            $paid = false;

            $mollie_payment_links = $this->getMolliePaymentLinks($order_info['order_id']);
            foreach($mollie_payment_links as $mollie_payment_link) {
                if ($mollie_payment_link['date_payment']) {
                    $paid = true;

                    break;
                }
            }

            if ($paid) {
                $payment_status = 'paid';
            } else {
                $payment_status = 'open';
            }
        }

        return [
            "payment_status" => $payment_status,
            "paymentMethod" => $paymentMethod,
            "showRefundButton" => $showRefundButton
        ];
    }

    /**
     * Quantity per order_product_id that has already been refunded (product line refunds only).
     * Refunds that failed or were canceled at Mollie don't count.
     *
     * @param \Mollie\Api\Resources\Payment $molliePayment
     * @return array order_product_id => refunded quantity
     */
    public function getRefundedQuantities($molliePayment) {
        $refundedQuantities = array();

        if (!empty($molliePayment->refunds())) {
            foreach ($molliePayment->refunds() as $refund) {
                if (isset($refund->status) && in_array($refund->status, array('failed', 'canceled'))) {
                    continue;
                }

                $order_products = array();

                if (isset($refund->metadata) && isset($refund->metadata->order_product_id)) {
                    $order_products = json_decode($refund->metadata->order_product_id, true);
                }

                if (is_array($order_products)) {
                    foreach ($order_products as $order_product) {
                        $id = $order_product['order_product_id'];
                        $refundedQuantities[$id] = (isset($refundedQuantities[$id]) ? $refundedQuantities[$id] : 0) + (int)$order_product['quantity'];
                    }
                }
            }
        }

        return $refundedQuantities;
    }

    /**
     * Stop OpenCart from restocking (or re-subtracting) these order lines when the order status
     * changes. After a partial refund the order is moved to the partial refund status, and
     * OpenCart would otherwise restock the whole original order on top of the stock the refund
     * itself already handled (credit order or manual stock mutation).
     */
    public function disableStockMutation($order_id) {
        $column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` LIKE 'stock_mutation'");

        if ($column->num_rows) {
            $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '0' WHERE order_id = '" . (int)$order_id . "'");
        }
    }

    public function getProductLines($order_info, $products) {
        $productlines = array();

        $molliePayment = $this->getMolliePayment($order_info['order_id']);

        if ($molliePayment) {
            if (!empty($molliePayment['transaction_id'])) {
                try {
                    $molliePayment = $this->getAPIClient($order_info['store_id'])->payments->get($molliePayment['transaction_id']);

                    $refundedQuantities = $this->getRefundedQuantities($molliePayment);

                    foreach ($products as $_product) {
                        $refunded = isset($refundedQuantities[$_product['order_product_id']]) ? $refundedQuantities[$_product['order_product_id']] : 0;
                        $remaining = (int)$_product['quantity'] - $refunded;

                        // A line stays refundable until its whole quantity has been refunded
                        if ($remaining > 0) {
                            $productlines[] = array(
                                "name" => $_product['name'],
                                "option" => $_product['option'],
                                "quantity" => $remaining,
                                "order_product_id" => $_product['order_product_id']
                            );
                        }
                    }
                } catch (Mollie\Api\Exceptions\ApiException $e) {
                    $log = new Log('Mollie.log');
                    $log->write(htmlspecialchars($e->getMessage()));
                }
            }
        }

        return $productlines;
    }
}
