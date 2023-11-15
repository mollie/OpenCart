<?php
namespace Opencart\Admin\Controller\Extension\Mollie\Total;
use \Opencart\System\Helper AS Helper;

require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");

class MolliePaymentFee extends \Opencart\System\Engine\Controller {
	protected $error = array();

	// Holds multistore configs
	protected $data = array();
	private $token;
	private $moduleCode;
	public $mollieHelper;

	public function __construct($registry) {
		parent::__construct($registry);

		$this->token = 'user_token='.$this->session->data['user_token'];
    	$this->mollieHelper = new \MollieHelper($registry);
	}

	public function index (): void {
		// Load essential models
		$this->load->model('setting/setting');
		$this->load->model("localisation/language");
		$this->load->model("localisation/geo_zone");
		$this->load->model('localisation/tax_class');
		$this->load->model("customer/customer_group");

		$this->load->language('extension/mollie/total/mollie_payment_fee');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->moduleCode, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
        	if (version_compare(VERSION, '3', '>=')) {
				$this->response->redirect($this->url->link('marketplace/extension', 'type=total&' . $this->token, 'SSL'));
			} elseif (version_compare(VERSION, '2.3', '>=')) {
				$this->response->redirect($this->url->link('extension/extension', 'type=total&' . $this->token, 'SSL'));
			} elseif (version_compare(VERSION, '2.0', '>=')) {
				$this->response->redirect($this->url->link('extension/total', $this->token, 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/total', $this->token, 'SSL'));
			}
        }

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', $this->token . '&type=total')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/mollie/total/mollie_payment_fee', $this->token)
		];
		
		$data['save'] = $this->url->link('extension/mollie/total/mollie_payment_fee|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total');
		
        $data['payment_methods']    = $this->mollieHelper->MODULE_NAMES;
        $data['stores']             = $this->getStores();
		$data['geo_zones']			= $this->model_localisation_geo_zone->getGeoZones();
		$data['tax_classes']        = $this->model_localisation_tax_class->getTaxClasses();
		$data['languages']          = $this->model_localisation_language->getLanguages();
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		$data['total_mollie_payment_fee_status'] = $this->config->get('total_mollie_payment_fee_status');
		$data['total_mollie_payment_fee_sort_order'] = $this->config->get('total_mollie_payment_fee_sort_order');
		$data['total_mollie_payment_fee_tax_class_id'] = $this->config->get('total_mollie_payment_fee_tax_class_id');

		if ($this->config->get('total_mollie_payment_fee_charge')) {
			$data['total_mollie_payment_fee_charge'] = $this->config->get('total_mollie_payment_fee_charge');
		} else {
			$data['total_mollie_payment_fee_charge'] = array();;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/mollie/total/mollie_payment_fee', $data));
	}

	public function save(): void {
		$this->load->language('extension/mollie/total/mollie_payment_fee');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/mollie/total/mollie_payment_fee')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('total_mollie_payment_fee', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getStores() {
		$this->load->model('setting/store');
		$stores = array();
		$stores[0] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name')
		);

		$_stores = $this->model_setting_store->getStores();

		foreach ($_stores as $store) {
			$stores[$store['store_id']] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		return $stores;
	}
}
