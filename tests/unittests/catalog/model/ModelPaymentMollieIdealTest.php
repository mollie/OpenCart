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