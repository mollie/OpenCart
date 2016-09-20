<?php

class ModelExtensionPaymentMollieIdealTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ModelExtensionPaymentMollieIdeal
	 */
	protected $model;

	protected $client;

	public function setUp()
	{
		parent::setUp();

		$this->model = $this->getMock("ModelExtensionPaymentMollieBase", array("getAPIClient", "getIssuers", "getCurrentDate"));
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

	/**
	 * Add mock payment methods to the list of payment methods the API client will return.
	 *
	 * @param string $id
	 * @param int    $min_amount
	 * @param int    $max_amount
	 *
	 * @return mixed
	 */
	protected function addPaymentMethod ($id = "method", $min_amount = 0, $max_amount = 999)
	{
		$method = $this->getMock("stub", array("getMinimumAmount", "getMaximumAmount"));

		$method->id          = $id;
		$method->description = $id;

		$method->image = new StdClass;
		$method->image->normal = NULL;
		$method->image->bigger = NULL;

		$method->method("getMinimumAmount")->willReturn($min_amount);
		$method->method("getMaximumAmount")->willReturn($max_amount);

		$this->client_methods[] = $method;

		return $method;
	}

	/**
	 * Retrieve the correct payment methods for a specified amount.
	 */
	public function testGetMethodCanReturnNULL ()
	{
		$method = $this->getMock("stub", array("getMinimumAmount", "getMaximumAmount"));

		$method->id          = NULL;
		$method->description = NULL;

		$this->client_methods
			->expects($this->exactly(3))
			->method("get")
			->with(NULL)
			->willReturn($method);

		$method
			->expects($this->exactly(3))
			->method("getMinimumAmount")
			->willReturn(100);

		$method
			->expects($this->exactly(3))
			->method("getMaximumAmount")
			->willReturn(200);

		$return_50  = $this->model->getMethod("some address", 50);
		$return_150 = $this->model->getMethod("some address", 150);
		$return_250 = $this->model->getMethod("some address", 250);

		$this->assertNull($return_50);
		$this->assertNotNull($return_150);
		$this->assertNull($return_250);
	}

	public function testSetPaymentReturnsFalseIfArgumentsOmitted()
	{
		$this->assertFalse($this->model->setPayment(NULL, NULL));
	}

	public function testSetPaymentNothingAffected()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`transaction_id`, `method`)
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
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`transaction_id`, `method`)
					 VALUES ('1337', '1bba1d8fdbd8103b46151634bdbe0a60', 'idl')");

		$this->model->db->expects($this->once())
			->method("countAffected")
			->will($this->returnValue(1));

		$this->assertTrue($this->model->setPayment(self::ORDER_ID, self::TRANSACTION_ID));
	}
}
