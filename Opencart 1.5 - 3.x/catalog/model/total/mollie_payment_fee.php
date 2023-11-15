<?php
class ModelTotalMolliePaymentFee extends Model
{
	public function getTotal($total) {
		if (isset($this->session->data['payment_method']) && (substr($this->session->data['payment_method']['code'], 0, 6) == 'mollie')) {
			if (version_compare(VERSION, '2.3', '>=')) {
		      $this->load->language('extension/total/mollie_payment_fee');
		    } else {
		      $this->load->language('total/mollie_payment_fee');
		    }

            $moduleCode = version_compare(VERSION, '3.0.0.0', '>=') ? 'total_mollie_payment_fee' : 'mollie_payment_fee';

	        $payment_method = str_replace('mollie_', '', $this->session->data['payment_method']['code']);
			if (isset($this->session->data['payment_address_id'])) {
				$this->load->model('account/address');
	
				$address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
			} elseif (isset($this->session->data['payment_address'])) {
				$address = $this->session->data['payment_address'];
			} elseif (isset($this->session->data['guest']['payment'])) {
				$address = $this->session->data['guest']['payment'];
			}

			$sort_order = $this->config->get($moduleCode . '_sort_order');
			$title = $this->language->get('text_mollie_payment_fee');
			$amount = 0;
			if ($this->config->get($moduleCode . '_charge')) {
				$charges = $this->config->get($moduleCode . '_charge');
			} else {
				$charges = array();
			}

			$payment_fee = array();
			foreach ($charges as $charge) {
				if (isset($charge['store']) && !empty($charge['payment_method'])) {
					if (in_array($this->config->get('config_store_id'), $charge['store']) && ($charge['payment_method'] == $payment_method) && (($charge['customer_group_id'] <= 0) || ($charge['customer_group_id'] == $this->config->get('config_customer_group_id')))) {
						$geo_zone_status = true;
						if ($charge['geo_zone_id'] > 0) {
							$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$charge['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
							if (!$query->num_rows) {
								$geo_zone_status = false;
							}
						}

						if ($geo_zone_status) {
							if(isset($charge['description'][$this->config->get('config_language_id')])) {
								$title = $charge['description'][$this->config->get('config_language_id')]['title'];
							}
		
							if(!empty($charge['cost'])) {
								if (substr($charge['cost'], -1) == "%") {
									$amount = ($total['total'] * str_replace("%", "", $charge['cost'])) / 100;					
								} else {
									$amount = (float)$charge['cost'];
								}
							}

							$priority = (int)$charge['priority'];

							$payment_fee[$priority] = array(
								"title" => $title,
								"amount" => $amount
							);							
						}
					}
				}
			}

			if (!empty($payment_fee)) {
				ksort($payment_fee);
				reset($payment_fee);
				$charge = current($payment_fee);

				$title = $charge['title'];
				$amount = $charge['amount'];
			}

			if ($amount > 0) {
				$total['totals'][] = array(
					'code'       => 'mollie_payment_fee',
					'title'      => $title,
					'value'      => $amount,
					'sort_order' => $sort_order
				);

				if ($this->config->get($moduleCode . "_tax_class_id")) {
					$tax_rates = $this->tax->getRates($amount, $this->config->get($moduleCode . "_tax_class_id"));

					foreach ($tax_rates as $tax_rate) {
						if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
							$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
						} else {
							$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
						}
					}
				}

				$total['total'] += $amount;
			}
        }
	}
}
