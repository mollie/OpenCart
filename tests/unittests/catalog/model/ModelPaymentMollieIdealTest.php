<?php

class ModelPaymentMollieIdealTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ModelPaymentMollieIdeal
	 */
	protected $model;

	public function setUp()
	{
		parent::setUp();

		$this->model = $this->getMock("ModelPaymentMollieIdeal", array("getCurrentDate"));
		$this->model->db = $this->getMock("stub", array("query", "escape", "countAffected"));

		$this->model->expects($this->any())
			->method("getCurrentDate")
			->will($this->returnValue('2013-05-05 12:12:12'));

		$this->model->db->expects($this->any())
			->method("escape")
			->will($this->returnCallback(function($arg0) {
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
	}

	public function testGetMethod()
	{
		$this->model->load->expects($this->once())
			->method("language")
			->with("payment/mollie_ideal");

		$this->assertEquals(array(
			"code" => "mollie_ideal",
			"title" => "text_title",
			"sort_order" => self::CONFIG_SORT_ORDER,
		), $this->model->getMethod("address", 0.0));
	}

	public function testGetOrderByIdReturnsFalse() {
		$this->assertFalse($this->model->getOrderById(NULL));
	}

	public function testGetOrderByIdWorks()
	{
		$this->model->load->expects($this->once())
			->method("model")
			->with("checkout/order");

		$this->model->model_checkout_order = $this->getMock("stub", array("getOrder"));
		$this->model->model_checkout_order->expects($this->once())
			->method("getOrder")
			->with(self::ORDER_ID)
			->will($this->returnValue(array("order_id" => self::ORDER_ID)));

		$this->assertInternalType("array", $this->model->getOrderById(self::ORDER_ID));
	}

	public function testGetPaymentByIdReturnsFalseIfNoTransactionIdPassed()
	{
		$this->assertFalse($this->model->getPaymentById(NULL));
	}

	public function testGetPaymentByIdCannotFindPayment()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("SELECT *
					 FROM `prefix_mollie_payments`
					 WHERE `transaction_id` = '1bba1d8fdbd8103b46151634bdbe0a60'")
			->will($this->returnValue((object) array(
			"num_rows" => 0
		)));

		$this->assertFalse($this->model->getPaymentById(self::TRANSACTION_ID));
	}

	public function testGetPaymentByIdFindsPayment()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("SELECT *
					 FROM `prefix_mollie_payments`
					 WHERE `transaction_id` = '1bba1d8fdbd8103b46151634bdbe0a60'")
			->will($this->returnValue((object) array(
			"num_rows" => 1,
			"row" => array(
				"order_id" => self::ORDER_ID,
				"transaction_id" => self::TRANSACTION_ID,
			)
		)));

		$this->assertEquals(array(
			"order_id" => self::ORDER_ID,
			"transaction_id" => self::TRANSACTION_ID,
		), $this->model->getPaymentById(self::TRANSACTION_ID));
	}

	public function testSetPaymentReturnsFalseIfArgumentsOmitted()
	{
		$this->assertFalse($this->model->setPayment(NULL, NULL));
	}

	public function testSetPaymentNothingAffected()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`transaction_id`, `method`, `created_at`)
					 VALUES ('1337', '1bba1d8fdbd8103b46151634bdbe0a60', 'idl', '2013-05-05 12:12:12')");

		$this->model->db->expects($this->once())
			->method("countAffected")
			->will($this->returnValue(0));

		$this->assertFalse($this->model->setPayment(self::ORDER_ID, self::TRANSACTION_ID));
	}

	public function testSetPaymentPaymentSet()
	{
		$this->model->db->expects($this->once())
			->method("query")
			->with("REPLACE INTO `prefix_mollie_payments` (`order_id` ,`transaction_id`, `method`, `created_at`)
					 VALUES ('1337', '1bba1d8fdbd8103b46151634bdbe0a60', 'idl', '2013-05-05 12:12:12')");

		$this->model->db->expects($this->once())
			->method("countAffected")
			->will($this->returnValue(1));

		$this->assertTrue($this->model->setPayment(self::ORDER_ID, self::TRANSACTION_ID));
	}
}