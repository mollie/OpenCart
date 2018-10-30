<?php

class ModelPaymentMollieIdealTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ModelPaymentMollieIdeal
	 */
	protected $model;

	protected $client;

	public function setUp()
	{
		parent::setUp();

		$this->model = $this->getMock("ModelPaymentMollieBase", array("getAPIClient", "getIssuers", "getCurrentDate"));
		$this->model->db = $this->getMock("stub", array("query", "escape", "countAffected"));

		// Mock API client.
		$this->client          = $this->getMock("stub");
		$this->client_methods  = $this->getMock("stub", array("all", "get"));
		$this->client->methods = $this->client_methods;

		$this->model
			->method("getAPIClient")
			->willReturn($this->client);

		// Mock model methods.
		$this->model
			->method("getCurrentDate")
			->will($this->returnValue('2013-05-05 12:12:12'));

		$this->model->db
			->method("escape")
			->will($this->returnCallback(function ($arg0)
			{
				return addslashes($arg0);
			}));

		$this->model->load = $this->getMock("stdClass", array("model", "language"));

		$this->model->language = $this->getMock("stub", array("get"));
		$this->model->language->expects($this->any())
			->method("get")
			->will($this->returnArgument(0));

		$this->model->config = $this->getMock("stdClass", array("get"));
		$this->model->config->expects($this->any())
			->method("get")
			->will($this->returnValueMap(array(
			array("mollie_ideal_sort_order", self::CONFIG_SORT_ORDER),
		)));

		$this->model->url = $this->getMock("stub", array("link"));
	}

	public function testSetPaymentReturnsFalseIfArgumentsOmitted()
	{
		$this->assertFalse($this->model->setPayment(NULL, NULL));
	}

	public function testSetPaymentNothingAffected()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`mollie_order_id`, `method`)
					 VALUES ('1337', '1bba1d8fdbd8103b46151634bdbe0a60', 'idl')");

		$this->model->db->expects($this->once())
			->method("countAffected")
			->will($this->returnValue(0));

		$this->assertFalse($this->model->setPayment(self::ORDER_ID, self::TRANSACTION_ID));
	}

	public function testSetPaymentPaymentSet()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`mollie_order_id`, `method`)
					 VALUES ('1337', '1bba1d8fdbd8103b46151634bdbe0a60', 'idl')");

		$this->model->db->expects($this->once())
			->method("countAffected")
			->will($this->returnValue(1));

		$this->assertTrue($this->model->setPayment(self::ORDER_ID, self::TRANSACTION_ID));
	}
}
