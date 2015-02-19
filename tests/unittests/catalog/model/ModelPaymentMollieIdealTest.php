<?php

class ModelPaymentMollieIdealTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ModelPaymentMollieIdeal
	 */
	protected $model;

	protected $client;
	protected $client_methods = array();

	public function setUp()
	{
		parent::setUp();

		$this->model = $this->getMock("ModelPaymentMollieIdeal", array("getApiClient", "getIssuersForMethod", "getCurrentDate", "setPreOutput", "getMethodJavaScript"));
		$this->model->db = $this->getMock("stub", array("query", "escape", "countAffected"));

		// Mock API client.
		$this->client = $this->getMock("stub");
		$methods_stub = $this->getMock("stub", array("all"));

		$this->client->methods = $methods_stub;

		$this->model
			->method("getApiClient")
			->willReturn($this->client);

		$this->client->methods
			->method("all")
			->will($this->returnCallback(array($this, "getPaymentMethods")));

		$this->model
			->method("getIssuersForMethod")
			->willReturn(array());

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

	public function getPaymentMethods ()
	{
		return $this->client_methods;
	}

	/**
	 * Retrieve the correct payment methods for a specified amount.
	 */
	public function testGetApplicablePaymentMethods ()
	{
		$method_1 = $this->addPaymentMethod("method_1", 1, 3);
		$method_2 = $this->addPaymentMethod("method_2", 2, 4);

		$methods = $this->model->getApplicablePaymentMethods(1);
		$this->assertEquals(array("method_1"=>$method_1), $methods);

		$methods = $this->model->getApplicablePaymentMethods(2);
		$this->assertEquals(array("method_1"=>$method_1, "method_2"=>$method_2), $methods);

		$methods = $this->model->getApplicablePaymentMethods(4);
		$this->assertEquals(array("method_2"=>$method_2), $methods);
	}

	/**
	 * Add JavaScript to the output buffer when Opencart calls getMethod().
	 */
	public function testGetMethodAddsJavaScript ()
	{
		$this->addPaymentMethod();

		$this->model
			->expects($this->once())
			->method("setPreOutput");

		$this->model
			->expects($this->once())
			->method("getMethodJavaScript");

		$array = $this->model->getMethod("", .0);

		$this->assertEquals($array['code'],  "mollie_ideal");
		$this->assertEquals($array['title'], "method");
		$this->assertEquals($array['terms'], NULL);
	}

	public function dataProviderIsRoute ()
	{
		return array(
			array("checkout/checkout", array("checkout/checkout", "quickcheckout/checkout"), TRUE),
			array("checkout/checkout", "checkout/checkout",                                  TRUE),
			array("checkout/checkout", "quickcheckout/checkout",                             FALSE)
		);
	}

	/**
	 * @param $current_route
	 * @param $routes
	 * @param $expected_output
	 *
	 * @dataProvider dataProviderIsRoute
	 */
	public function testIsRoute ($current_route, $routes, $expected_output)
	{
		$_GET['route'] = $current_route;

		$output = $this->model->isRoute($routes);

		$this->assertEquals($expected_output, $output);
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