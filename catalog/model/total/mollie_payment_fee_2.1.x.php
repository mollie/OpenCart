<?php
class ModelTotalMolliePaymentFee extends Model
{
	public function getTotal(&$total_data, &$total, &$taxes) {
		if (isset($this->session->data['payment_method']) && (substr($this->session->data['payment_method']['code'], 0, 6) == 'mollie')) {
			$this->load->language('total/mollie_payment_fee');

	        $payment_method = str_replace('mollie_', '', $this->session->data['payment_method']['code']);

	        if(isset($this->config->get("mollie_" . $payment_method . "_payment_fee")['description'][$this->config->get('config_language_id')])) {
				$title = $this->config->get("mollie_" . $payment_method . "_payment_fee")['description'][$this->config->get('config_language_id')]['title'];
			} else {
				$title = $this->language->get('text_mollie_payment_fee');
			}

			if(!empty($this->config->get("mollie_" . $payment_method . "_payment_fee")['amount'])) {
				$amount = $this->config->get("mollie_" . $payment_method . "_payment_fee")['amount'];
				if (substr($amount, -1) == "%") {
					$amount = ($total * str_replace("%", "", $amount)) / 100;				
				}
			} else {
				$amount = 0;
			}

			if ($amount > 0) {
				$total_data[] = array(
					'code'       => 'mollie_payment_fee',
					'title'      => $title,
					'text'       => $this->currency->format($amount),
					'value'      => $amount,
					'sort_order' => $this->config->get('mollie_payment_fee_sort_order')
				);

				if ($this->config->get("mollie_payment_fee_tax_class_id")) {
					$tax_rates = $this->tax->getRates($amount, $this->config->get("mollie_payment_fee_tax_class_id"));

					foreach ($tax_rates as $tax_rate) {
						if (!isset($taxes[$tax_rate['tax_rate_id']])) {
							$taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
						} else {
							$taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
						}
					}
				}

				$total += $amount;
			}
        }
	}
}
