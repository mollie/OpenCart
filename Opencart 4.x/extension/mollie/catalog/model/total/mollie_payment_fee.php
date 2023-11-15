<?php
namespace Opencart\Catalog\Model\Extension\Mollie\Total;
class MolliePaymentFee extends \Opencart\System\Engine\Model {
	public function getTotal(array &$totals, array &$taxes, float &$total): void {
		if (isset($this->session->data['payment_method']) && (substr($this->session->data['payment_method']['code'], 0, 6) == 'mollie')) {
			$this->load->language('extension/mollie/total/mollie_payment_fee');

	        $payment_method = str_replace('mollie_', '', explode('.', $this->session->data['payment_method']['code'])[1]);

			if (isset($this->session->data['payment_address']) && !empty($this->session->data['payment_address'])) {
				$country_id = $this->session->data['payment_address']['country_id'];
				$zone_id = $this->session->data['payment_address']['zone_id'];
			} else {
				$country_id = $this->config->get('config_country_id');
				$zone_id = 0;
			}

			$sort_order = $this->config->get('total_mollie_payment_fee_sort_order');
			$title = $this->language->get('text_mollie_payment_fee');
			$amount = 0;

			if ($this->config->get('total_mollie_payment_fee_charge')) {
				$charges = $this->config->get('total_mollie_payment_fee_charge');
			} else {
				$charges = array();
			}

			$payment_fee = array();
			foreach ($charges as $charge) {
				if (isset($charge['store']) && !empty($charge['payment_method'])) {
					if (in_array($this->config->get('config_store_id'), $charge['store']) && ($charge['payment_method'] == $payment_method) && (($charge['customer_group_id'] <= 0) || ($charge['customer_group_id'] == $this->config->get('config_customer_group_id')))) {
						$geo_zone_status = true;
						if ($charge['geo_zone_id'] > 0) {
							$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$charge['geo_zone_id'] . "' AND country_id = '" . (int)$country_id . "' AND (zone_id = '" . (int)$zone_id . "' OR zone_id = '0')");
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
									$amount = ($total * str_replace("%", "", $charge['cost'])) / 100;				
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
				$totals[] = [
					'extension'  => 'mollie',
					'code'       => 'mollie_payment_fee',
					'title'      => $title,
					'value'      => $amount,
					'sort_order' => (int)$sort_order
				];

				if ($this->config->get("total_mollie_payment_fee_tax_class_id")) {
					$tax_rates = $this->tax->getRates((float)$amount, (int)$this->config->get('total_mollie_payment_fee_tax_class_id'));

					foreach ($tax_rates as $tax_rate) {
						if (!isset($taxes[$tax_rate['tax_rate_id']])) {
							$taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
						} else {
							$taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
						}
					}
				}

				$total += (float)$amount;
			}
        }
	}
}
