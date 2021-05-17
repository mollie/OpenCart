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

            $moduleCode = version_compare(VERSION, '3.0.0.0', '>=') ? 'payment_mollie' : 'mollie';
	        $payment_method = str_replace('mollie_', '', $this->session->data['payment_method']['code']);

	        if(isset($this->config->get($moduleCode . "_" . $payment_method . "_payment_fee")['description'][$this->config->get('config_language_id')])) {
				$title = $this->config->get($moduleCode . "_" . $payment_method . "_payment_fee")['description'][$this->config->get('config_language_id')]['title'];
			} else {
				$title = $this->language->get('text_mollie_payment_fee');
			}

			if(!empty($this->config->get($moduleCode . "_" . $payment_method . "_payment_fee")['amount'])) {
				$amount = $this->config->get($moduleCode . "_" . $payment_method . "_payment_fee")['amount'];
				if (substr($amount, -1) == "%") {
					$amount = ($total['total'] * str_replace("%", "", $amount)) / 100;					
				}
			} else {
				$amount = 0;
			}

			if (version_compare(VERSION, '3.0.0.0', '>=')) {
				$sort_order = $this->config->get('total_mollie_payment_fee_sort_order');
			} else {
				$sort_order = $this->config->get('mollie_payment_fee_sort_order');
			}

			if ($amount > 0) {
				$total['totals'][] = array(
					'code'       => 'mollie_payment_fee',
					'title'      => $title,
					'value'      => $amount,
					'sort_order' => $sort_order
				);

				if ($this->config->get($moduleCode . "_payment_fee_tax_class_id")) {
					$tax_rates = $this->tax->getRates($amount, $this->config->get($moduleCode . "_payment_fee_tax_class_id"));

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
