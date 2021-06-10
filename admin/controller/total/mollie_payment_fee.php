<?php
class ControllerTotalMolliePaymentFee extends Controller {
	protected $error = array();

	// Holds multistore configs
	protected $data = array();
	private $token;
	private $moduleCode;

	public function __construct($registry) {
		parent::__construct($registry);

		if (isset($this->session->data['user_token'])) {
			$this->token = 'user_token=' . $this->session->data['user_token'];
			$this->moduleCode = 'total_mollie_payment_fee';
		} else {
			$this->token = 'token=' . $this->session->data['token'];
			$this->moduleCode = 'mollie_payment_fee';
		}
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

		$data['entry_status']  = $this->language->get('entry_status');
		$data['entry_sort_order']  = $this->language->get('entry_sort_order');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
      
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
}
