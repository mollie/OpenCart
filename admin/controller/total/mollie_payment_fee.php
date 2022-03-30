<?php

require_once(DIR_SYSTEM . "library/mollie/helper.php");

class ControllerTotalMolliePaymentFee extends Controller {
	protected $error = array();

	// Holds multistore configs
	protected $data = array();
	private $token;
	private $moduleCode;
	public $mollieHelper;

	public function __construct($registry) {
		parent::__construct($registry);

		if (isset($this->session->data['user_token'])) {
			$this->token = 'user_token=' . $this->session->data['user_token'];
			$this->moduleCode = 'total_mollie_payment_fee';
		} else {
			$this->token = 'token=' . $this->session->data['token'];
			$this->moduleCode = 'mollie_payment_fee';
		}

		$this->mollieHelper = new MollieHelper($registry);
	}

	public function install() {
		// Check frontend model file
		if (version_compare(VERSION, '2.2.0.0', '<')) {
			if (is_file(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee_2.1.x.php')) {
				if (is_file(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee.php')) {
					unlink(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee.php');
				}
				rename(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee_2.1.x.php', DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee.php');
			}
		} else {
			if (is_file(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee_2.1.x.php')) {
				unlink(DIR_SYSTEM.'../catalog/model/total/mollie_payment_fee_2.1.x.php');
			}
		}
	}

	public function index () {
		$this->install();
		// Load essential models
		$this->load->model('setting/setting');
		$this->load->model("localisation/language");
		$this->load->model("localisation/geo_zone");
		$this->load->model('localisation/tax_class');
		if (version_compare(VERSION, '2.1', '>=')) {
			$this->load->model("customer/customer_group");
		} else {
			$this->load->model("sale/customer_group");
		}

		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/total/mollie_payment_fee');
	    } else {
	      $this->load->language('total/mollie_payment_fee');
	    }

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

        $data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
	        'text'      => $this->language->get('text_home'),
	        'href'      => $this->url->link('common/home', $this->token, 'SSL'),
	      	'separator' => false
   		);

		if (version_compare(VERSION, '3', '>=')) {
			$extension_link = $this->url->link('marketplace/extension', 'type=total&' . $this->token, 'SSL');
		} elseif (version_compare(VERSION, '2.3', '>=')) {
			$extension_link = $this->url->link('extension/extension', 'type=total&' . $this->token, 'SSL');
		} else {
			$extension_link = $this->url->link('extension/total', $this->token, 'SSL');
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled']  = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_edit']     = $this->language->get('text_edit');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_select'] = $this->language->get('text_select');

		$data['entry_status']  = $this->language->get('entry_status');
		$data['entry_sort_order']  = $this->language->get('entry_sort_order');
		$data['entry_tax_class']  = $this->language->get('entry_tax_class');
		$data['entry_title']  = $this->language->get('entry_title');
		$data['entry_payment_method']  = $this->language->get('entry_payment_method');
		$data['entry_cost']  = $this->language->get('entry_cost');
		$data['entry_store']  = $this->language->get('entry_store');
		$data['entry_customer_group']  = $this->language->get('entry_customer_group');
		$data['entry_geo_zone']  = $this->language->get('entry_geo_zone');
		$data['entry_priority']  = $this->language->get('entry_priority');

		$data['tab_general']  = $this->language->get('tab_general');
		$data['tab_charge']  = $this->language->get('tab_charge');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add_charge'] = $this->language->get('button_add_charge');
		$data['button_remove_charge'] = $this->language->get('button_remove_charge');
      
   		$data['breadcrumbs'][] = array(
	       	'text'      => $this->language->get('text_extension'),
	        'href'      => $extension_link,
	      	'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
	       	'text'      => strip_tags($this->language->get('heading_title')),
	        'href'      => (version_compare(VERSION, '2.3', '>=')) ? $this->url->link('extension/total/mollie_payment_fee', $this->token, true) : $this->url->link('total/mollie_payment_fee', $this->token, 'SSL'),
	        'separator' => ' :: '
   		);
		
		$data['action'] = (version_compare(VERSION, '2.3', '>=')) ? $this->url->link('extension/total/mollie_payment_fee', $this->token, true) : $this->url->link('total/mollie_payment_fee', $this->token, 'SSL');
		
		$data['cancel'] = $extension_link;
        $data['code']   = $this->moduleCode;
        $data['payment_methods']   = $this->mollieHelper->MODULE_NAMES;
        $data['stores']   = $this->getStores();
		$data['geo_zones']			= $this->model_localisation_geo_zone->getGeoZones();
		$data['tax_classes']        = $this->model_localisation_tax_class->getTaxClasses();
		$data['languages']			= $this->model_localisation_language->getLanguages();
		foreach ($data['languages'] as &$language) {
	      if (version_compare(VERSION, '2.2', '>=')) {
	        $language['image'] = 'language/'.$language['code'].'/'.$language['code'].'.png';
	      } else {
	        $language['image'] = 'view/image/flags/'. $language['image'];
	      }
	    }
		if (version_compare(VERSION, '2.1', '>=')) {
			$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		} else {
			$data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		}

		if(isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->request->post[$this->moduleCode . '_status'])) {
			$data['mollie_payment_fee_status'] = $this->request->post[$this->moduleCode . '_status'];
		} else {
			$data['mollie_payment_fee_status'] = $this->config->get($this->moduleCode . '_status');
		}

		if (isset($this->request->post[$this->moduleCode . '_sort_order'])) {
			$data['mollie_payment_fee_sort_order'] = $this->request->post[$this->moduleCode . '_sort_order'];
		} else {
			$data['mollie_payment_fee_sort_order'] = $this->config->get($this->moduleCode . '_sort_order');
		}

		if (isset($this->request->post[$this->moduleCode . '_tax_class_id'])) {
			$data['mollie_payment_fee_tax_class_id'] = $this->request->post[$this->moduleCode . '_tax_class_id'];
		} else {
			$data['mollie_payment_fee_tax_class_id'] = $this->config->get($this->moduleCode . '_tax_class_id');
		}

		if (isset($this->request->post[$this->moduleCode . '_charge'])) {
			$data['mollie_payment_fee_charge'] = $this->request->post[$this->moduleCode . '_charge'];
		} elseif ($this->config->get($this->moduleCode . '_charge')) {
			$data['mollie_payment_fee_charge'] = $this->config->get($this->moduleCode . '_charge');
		} else {
			$data['mollie_payment_fee_charge'] = array();;
		}

		if (version_compare(VERSION, '2', '>=')) {
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			
			if (version_compare(VERSION, '3', '>=')) {
				$this->config->set('template_engine', 'template');
				$this->response->setOutput($this->load->view('total/mollie_payment_fee', $data));
			} else {
				$this->response->setOutput($this->load->view('total/mollie_payment_fee.tpl', $data));
			}
		} else {
			$data['column_left'] = '';
			$this->data = &$data;
			$this->template = 'total/mollie_payment_fee(max_1.5.6.4).tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
      
			$this->response->setOutput($this->render());
		}
	}

	private function validate () {
		$route = (version_compare(VERSION, '2.3', '>=')) ? 'extension/total/mollie_payment_fee' : 'total/mollie_payment_fee';
		if (!$this->user->hasPermission("modify", $route)) {
			$this->error['warning'] = $this->language->get("error_permission");
		}
		
		return !$this->error;
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
