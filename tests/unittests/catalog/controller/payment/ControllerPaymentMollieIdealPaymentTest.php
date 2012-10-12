<?php

class ControllerPaymentMollieIdealPaymentTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ControllerPaymentMollieIdeal|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $controller;

	/**
	 * @var iDEAL_Payment|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $ideal;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $config;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $language;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $url;

	public function setUp()
	{
		$this->controller = $this->getMock("ControllerPaymentMollieIdeal", array("getIdealPaymentObject", "render", "redirect"));
		$this->controller->request = new stdClass();
		$this->controller->request->get = array();
		$this->controller->request->post = array();
		$this->controller->request->server = array();

		$this->controller->cart = $this->getMock("stub", array("clear"));

		$this->ideal = $this->getMock("iDEAL_Payment", array("createPayment", "setPartnerId", "setProfileKey", "setTestmode", "getBanks", "checkPayment", "getBankStatus", "getAmount", "getBankUrl", "getErrorMessage"), array(self::CONFIG_PARTNER_ID));

		$this->controller->expects($this->any())
			->method("getIdealPaymentObject")
			->with(self::CONFIG_PARTNER_ID)
			->will($this->returnValue($this->ideal));

		$this->config = $this->getMock("stdClass", array("get"));
		$this->config->expects($this->any())
			->method("get")
			->will($this->returnValueMap(array(
			array("mollie_ideal_partnerid", self::CONFIG_PARTNER_ID),
			array("mollie_ideal_profilekey", self::CONFIG_PROFILE_KEY),
			array("mollie_ideal_testmode", self::CONFIG_TESTMODE),
			array("mollie_ideal_description", self::CONFIG_DESCRIPTION),
			array("mollie_ideal_processed_status_id", self::ORDER_STATUS_SUCCESS_ID),
			array("mollie_ideal_processing_status_id", self::ORDER_STATUS_PROCESSING_ID),
			array("mollie_ideal_failed_status_id", self::ORDER_STATUS_FAILED_ID),
			array("mollie_ideal_canceled_status_id", self::ORDER_STATUS_CANCELED_ID),
			array("mollie_ideal_expired_status_id", self::ORDER_STATUS_EXPIRED_ID),
			array("config_error_log", TRUE),
		)));

		$this->controller->config = $this->config;

		$this->language = $this->getMock("stub", array("get"));
		$this->language->expects($this->any())
			->method("get")
			->will($this->returnArgument(0));
		$this->controller->language = $this->language;

		$this->url = $this->getMock("stub", array("link"));
		$this->controller->url = $this->url;

		$this->controller->load = $this->getMock("stdClass", array("model", "language"));
	}

	public function testNothingHappensIfNotPost()
	{
		$this->controller->request->server["REQUEST_METHOD"] = "GET";

		$this->controller->load->expects($this->never())
			->method("model");

		$this->controller->payment();
	}

	public function testFirstPayment()
	{
		$this->reportActionTest($failed_order = FALSE, $create_payment_succeeds = TRUE);
	}

	public function testReloadFailedPayment()
	{
		$this->reportActionTest($failed_order = TRUE, $create_payment_succeeds = TRUE);
	}

	public function testCreatePaymentFails()
	{
		$this->reportActionTest($failed_order = FALSE, $create_payment_succeeds = FALSE);
	}

	public function reportActionTest($failed_order, $create_payment_succeeds)
	{
		$this->controller->request->server["REQUEST_METHOD"] = "POST";

		$this->controller->load->expects($this->exactly(2))
			->method("model")
			->with($this->logicalOr("checkout/order", "payment/mollie_ideal"));

		$this->controller->model_payment_mollie_ideal = $this->getMock("ModelPaymentMollieIdeal", array("getPaymentById", "getOrderById"));
		$this->controller->model_checkout_order = $this->getMock("stub", array("update", "confirm"));

		$this->controller->load->expects($this->once())
			->method("language")
			->with("payment/mollie_ideal");

		if ($failed_order)
		{
			$this->controller->request->post['transaction_id'] = self::TRANSACTION_ID;
			$this->controller->model_payment_mollie_ideal->expects($this->once())
				->method("getPaymentById")
				->with(self::TRANSACTION_ID)
				->will($this->returnValue(array(
				"order_id" => self::ORDER_ID,
			)));

		}
		else{
			$this->controller->session->data = array("order_id" => self::ORDER_ID);
		}

		$this->controller->model_payment_mollie_ideal->expects($this->once())
			->method("getOrderById")
			->with(self::ORDER_ID)
			->will($this->returnValue(array(
			"order_id" => self::ORDER_ID,
			"total" => 15.99,
		)));

		$this->controller->request->post['bank_id'] = self::BANK_ID;

		$this->controller->url->expects($this->exactly(2))
			->method("link")
			->with($this->logicalOr("payment/mollie_ideal/status", "payment/mollie_ideal/report"), "", "SSL")
			->will($this->returnArgument(0));

		$this->ideal->expects($this->once())
			->method("createPayment")
			->with(self::BANK_ID, 1599, self::CONFIG_DESCRIPTION_FINAL, "payment/mollie_ideal/status", "payment/mollie_ideal/report")
			->will($this->returnValue($create_payment_succeeds));

		if ($create_payment_succeeds)
		{
			if ($failed_order)
			{
				$this->controller->model_checkout_order->expects($this->once())
					->method("update")
					->with(self::ORDER_ID, self::ORDER_STATUS_PROCESSING_ID, "text_redirected", FALSE);
			}
			else
			{
				$this->controller->model_checkout_order->expects($this->once())
					->method("confirm")
					->with(self::ORDER_ID, self::ORDER_STATUS_PROCESSING_ID, "text_redirected", FALSE);
			}

			$this->ideal->expects($this->once())
				->method("getBankUrl")
				->will($this->returnValue(self::BANK_URL));

			$this->controller->expects($this->once())
				->method("redirect")
				->with(self::BANK_URL);
		}
		else
		{
			$this->ideal->expects($this->once())
				->method("getErrorMessage")
				->will($this->returnValue("The flux capacitors are over capacity."));

			$GLOBALS['log'] = $this->getMock("stub", array("write"));
			$GLOBALS['log']->expects($this->once())
				->method("write")
				->with($this->stringContains("The flux capacitors are over capacity."));

			$this->expectOutputRegex("!Kon geen betaling aanmaken!");
		}

		$this->controller->payment();
	}

}