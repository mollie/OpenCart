<?php

class ControllerPaymentMollieIdealReportTest extends Mollie_OpenCart_TestCase
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
		$this->controller = $this->getMock("ControllerPaymentMollieIdeal", array("getIdealPaymentObject", "render", "outputMollieInfo"));
		$this->controller->request = new stdClass();
		$this->controller->request->get = array();
		$this->controller->request->post = array();
		$this->controller->request->server = array();

		$this->controller->cart = $this->getMock("stub", array("clear"));

		$this->ideal = $this->getMock("iDEAL_Payment", array("setPartnerId", "setProfileKey", "setTestmode", "getBanks", "checkPayment", "getBankStatus", "getAmount"), array(self::CONFIG_PARTNER_ID));

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
			array("mollie_ideal_processed_status_id", self::ORDER_STATUS_SUCCESS_ID),
			array("mollie_ideal_processing_status_id", self::ORDER_STATUS_PROCESSING_ID),
			array("mollie_ideal_failed_status_id", self::ORDER_STATUS_FAILED_ID),
			array("mollie_ideal_canceled_status_id", self::ORDER_STATUS_CANCELED_ID),
			array("mollie_ideal_expired_status_id", self::ORDER_STATUS_EXPIRED_ID),
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

	public function testReportNothingHappensIncaseOfNoTransactionId()
	{
		$this->controller->request->get['transaction_id'] = NULL;
		$this->controller->expects($this->never())
			->method("getIdealPaymentObject");
		$this->controller->report();
	}

	public function testReportHappyPath()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_SUCCESS);
	}

	public function testReportAmountsMisMatch()
	{
		$this->reportActionTester($amounts_correct = FALSE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_SUCCESS);
	}

	public function testReportCustomerCanceled()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_CANCELLED);
	}

	public function testBankFailure()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_FAILURE);
	}

	public function testPaymentExpired()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_EXPIRED);
	}

	public function testUnknownBankStatus()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = "unknown");
	}

	public function testStatusCheckedBefore()
	{
		$this->reportActionTester($amounts_correct = TRUE, $bank_status = ModelPaymentMollieIdeal::BANK_STATUS_CHECKEDBEFORE);
	}

	protected function reportActionTester($amounts_correct, $bank_status)
	{
		$this->controller->request->get['transaction_id'] = self::TRANSACTION_ID;

		$this->ideal->expects($this->once())
			->method("setProfileKey")
			->with(self::CONFIG_PROFILE_KEY);

		$this->ideal->expects($this->once())
			->method("checkPayment")
			->with(self::TRANSACTION_ID)
			->will($this->returnValue(TRUE));

		$this->controller->load->expects($this->exactly(2))
			->method("model")
			->with($this->logicalOr("checkout/order", "payment/mollie_ideal"));

		$this->controller->load->expects($this->once())
			->method("language")
			->with("payment/mollie_ideal");

		$this->controller->model_payment_mollie_ideal = $this->getMock("ModelPaymentMollieIdeal", array("getPaymentById", "getOrderById", "updatePayment"));
		$this->controller->model_payment_mollie_ideal->expects($this->once())
			->method("getPaymentById")
			->with(self::TRANSACTION_ID)
			->will($this->returnValue(array(
				"order_id" => self::ORDER_ID,
				"transaction_id" => self::TRANSACTION_ID,
		)));

		$this->controller->model_checkout_order = $this->getMock("ModelCheckoutOrder", array("update"));

		$this->controller->model_payment_mollie_ideal->expects($this->once())
			->method("getOrderById")
			->with(self::ORDER_ID)
			->will($this->returnValue($this->controller->model_checkout_order));

		$this->controller->model_checkout_order['order_id'] = self::ORDER_ID;
		$this->controller->model_checkout_order['order_status_id']    = self::ORDER_STATUS_PROCESSING_ID;
		$this->controller->model_checkout_order['total']    = 15.99;

		$this->ideal->expects($this->atLeastOnce())
			->method("getBankStatus")
			->will($this->returnValue($bank_status));

		$this->ideal->expects($bank_status != ModelPaymentMollieIdeal::BANK_STATUS_CHECKEDBEFORE ? $this->atLeastOnce() : $this->never())
			->method("getAmount")
			->will($this->returnValue($amounts_correct ? 1599 : 1699));

		/*
		 * You are not allowed to clear the cart in this thread (cart has session state, so not available here).
		 */
		$this->controller->cart->expects($this->never())->method("clear");

		if ($amounts_correct && $bank_status === ModelPaymentMollieIdeal::BANK_STATUS_SUCCESS)
		{
			$this->controller->model_checkout_order->expects($this->once())
				->method("update")
				->with(self::ORDER_ID, self::ORDER_STATUS_SUCCESS_ID, "response_success", TRUE);
		}

		if ($bank_status === ModelPaymentMollieIdeal::BANK_STATUS_CANCELLED)
		{
			$this->controller->model_checkout_order->expects($this->once())
				->method("update")
				->with(self::ORDER_ID, self::ORDER_STATUS_CANCELED_ID, "response_cancelled", FALSE);
		}

		if ($bank_status === ModelPaymentMollieIdeal::BANK_STATUS_FAILURE)
		{
			$this->controller->model_checkout_order->expects($this->once())
				->method("update")
				->with(self::ORDER_ID, self::ORDER_STATUS_FAILED_ID, "response_failed", TRUE);
		}

		if ($bank_status === ModelPaymentMollieIdeal::BANK_STATUS_EXPIRED)
		{
			$this->controller->model_checkout_order->expects($this->once())
				->method("update")
				->with(self::ORDER_ID, self::ORDER_STATUS_EXPIRED_ID, "response_expired", FALSE);
		}

		if ($bank_status === "unknown")
		{
			$this->controller->model_checkout_order->expects($this->once())
				->method("update")
				->with(self::ORDER_ID, self::ORDER_STATUS_FAILED_ID, "response_unknown", FALSE);
		}

		if ($bank_status === ModelPaymentMollieIdeal::BANK_STATUS_CHECKEDBEFORE)
		{
			$this->controller->model_checkout_order->expects($this->never())
				->method("update");
		}

		if (!$amounts_correct)
		{
			$this->controller->model_checkout_order->expects($this->never())
				->method("update");
		}

		$this->controller->report();
	}
}