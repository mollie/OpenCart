<?php
require_once(DIR_SYSTEM . "library/mollie/helper.php");

class ModelPaymentMolliePaymentLink extends Model {
	public function getPaymentLink($payment_link_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payment_link` WHERE payment_link_id = '" . $this->db->escape($payment_link_id) . "'");

		return $query->row;
	}

	public function getPaymentLinkByOrderID($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payment_link` WHERE order_id = '" . $this->db->escape($order_id) . "'");

		return $query->row;
	}

	public function setPaymentForPaymentLinkAPI($order_id, $mollie_payment_link_id, $data = array()) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_payment_link` SET `order_id` = '" . (int)$order_id . "', `payment_link_id` = '" . $this->db->escape($mollie_payment_link_id) . "', `amount` = '" . (float)$data['amount'] . "', `currency_code` = '" . $this->db->escape($data['currency_code']) . "', date_created = NOW()");
	}

	public function updatePaymentLink($payment_link_id, $date) {
		$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payment_link` SET date_payment = '" . $this->db->escape($date) . "' WHERE payment_link_id = '" . $this->db->escape($payment_link_id) . "'");
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

	public function sendPaymentLink($order_info, $postData = array()) {
        // Do not send payment link if the payment is already made and the new order total is unchanged
        $payment_link_details = $this->getPaymentLinkByOrderID((int)$order_info['order_id']);
        if (!empty($payment_link_details) && !empty($payment_link_details['date_payment'])) {
            $new_order_total = $this->numberFormat($order_info['total'], $order_info['currency_code']);

            if (!isset($postData['mollie_payment_link_amount']) && ($new_order_total == $payment_link_details['amount'])) {
                $log = new Log('Mollie.log');
			    $log->write("Mollie payment link not sent for order_id " . $order_info['order_id'] . ". Same order total.");

                return [];
            }
        }

		$this->load->language('payment/mollie');
		$this->load->model('setting/setting');

		$data = array();

		if (version_compare(VERSION, '2.3', '>')) {
			$moduleCode = 'payment_mollie';
		} else {
			$moduleCode = 'mollie';
		}

		$desc = $this->config->get($moduleCode . "_description");
		if (!empty($desc) && isset($desc[$this->config->get('config_language_id')])) {
			$description = str_replace("%", $order_info['order_id'], html_entity_decode($desc[$this->config->get('config_language_id')]['title'], ENT_QUOTES, "UTF-8"));
		} else {
			$description = 'Order ' . $order_info['order_id'];
		}

		if (isset($postData['mollie_payment_link_amount']) && !empty($postData['mollie_payment_link_amount'])) {
			$order_total = (float)$postData['mollie_payment_link_amount'];
		} else {
			$order_total = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		}

        // Full or open amount
        $payment_code = $order_info['payment_code'];

        if (!empty($payment_link_details) && !empty($payment_link_details['date_payment'])) {
            if (strpos($payment_code, 'mollie_payment_link_full') !== false) {
                $order_total = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
            } elseif (strpos($payment_code, 'mollie_payment_link_open') !== false) {
                if ($order_info['total'] > $payment_link_details['amount']) {
                    $order_total = $this->currency->format(($order_info['total'] - $payment_link_details['amount']), $order_info['currency_code'], false, false);
                }
            }
        }
		
		$formattedAmount = $this->numberFormat($order_total, $order_info['currency_code']);
		
		$linkData = array(
			"description" => $description,
			"amount" => ["currency" => $order_info['currency_code'], "value" => (string)$formattedAmount],
			"redirectUrl" => $order_info['store_url'] . 'index.php?route=payment/mollie_payment_link/payLinkCallback&order_id=' . $order_info['order_id'],
			"webhookUrl" => $order_info['store_url'] . 'index.php?route=payment/mollie_payment_link/webhook'
		);

		try {
			$mollieHelper = new MollieHelper($this->registry);
			$config = $this->config;
			$config->set($mollieHelper->getModuleCode() . "_api_key", $mollieHelper->getApiKey($order_info['store_id']));
			$mollieHelper->getAPIClient($config);                
			$mollieApi = $mollieHelper->getAPIClient($config);

			$paymentLink = $mollieApi->paymentLinks->create($linkData);
			$payment_link = $paymentLink->getCheckoutUrl();

			// Save payment_link_id
			$this->setPaymentForPaymentLinkAPI($order_info['order_id'], $paymentLink->id, ["amount" => $formattedAmount, "currency_code" => $order_info['currency_code']]);

			$payment_link_setting = $this->config->get($moduleCode . '_payment_link_email');

			if (isset($payment_link_setting[$this->config->get('config_language_id')]) && !empty($payment_link_setting[$this->config->get('config_language_id')]['subject'])) {
				$payment_link_subject = $payment_link_setting[$this->config->get('config_language_id')]['subject'];
			} else {
				$payment_link_subject = $this->language->get('text_payment_link_email_subject');
			}

			if (isset($payment_link_setting[$this->config->get('config_language_id')]) && !empty($payment_link_setting[$this->config->get('config_language_id')]['body'])) {
				$payment_link_text = $payment_link_setting[$this->config->get('config_language_id')]['body'];
			} else {
				$payment_link_text = $this->language->get('text_payment_link_email_text');
			}

			$payment_link_text = html_entity_decode($payment_link_text, ENT_QUOTES, 'UTF-8');

			$find = array(
				'{firstname}',
				'{lastname}',
				'{amount}',
				'{order_id}',
				'{store_name}',
				'{payment_link}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'amount'   => html_entity_decode($this->currency->format($formattedAmount, $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8'),
				'order_id' => $order_info['order_id'],
				'store_name' => $order_info['store_name'],
				'payment_link'      => $payment_link
			);

			$data = $order_info;

			$data['text_link'] = $this->language->get('text_link');
			$data['title'] = $this->language->get('text_payment_link_title');
			$data['text_footer'] = $this->language->get('text_footer');

			$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
			
			if ($order_info['customer_id']) {
				$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];
			} else {
				$data['link'] = '';
			}

			$data['payment_link'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $payment_link_text))));
			
			if (!$this->config->get($moduleCode . '_payment_link')) {
				$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
			
				if (!$from) {
					$from = $this->config->get('config_email');
				}
				
				if (version_compare(VERSION, '2.3', '>')) {
					$mail = new Mail($this->config->get('config_mail_engine'));
				} else {
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
				}

				if (version_compare(VERSION, '2.2', '<')) {
					$template = 'default/template/payment/mollie_payment_link.tpl';
				} else {
					$template = 'payment/mollie_payment_link';
				}
				
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($order_info['email']);
				$mail->setFrom($from);
				$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($payment_link_subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($this->load->view($template, $data));
				$mail->send();
			}

			$log = new Log('Mollie.log');
			$log->write("Mollie payment link sent for order_id " . $order_info['order_id']);
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$log = new Log('Mollie.log');
			$log->write(htmlspecialchars($e->getMessage()));
		}

		return $data;
	}
}
