<?php
/**
 * @covers iDEAL_Payment
 */
class idealClassTest extends PHPUnit_Framework_TestCase
{
	protected static $banks_xml = <<< EOX
<?xml version="1.0" ?>
<response>
	<bank>
		<bank_id>1234</bank_id>
		<bank_name>Test bank 1</bank_name>
	</bank>
	<bank>
		<bank_id>0678</bank_id>
		<bank_name>Test bank 2</bank_name>
	</bank>
	<message>This is the current list of banks and their ID's that currently support iDEAL-payments</message>
</response>
EOX;

	protected static $expectedBanks = array (
		'1234' => 'Test bank 1',
		'0678' => 'Test bank 2'
	);

	protected static $check_payment_xml = <<< EOX
<?xml version="1.0"?>
<response>
	<order>
		<transaction_id>1234567890</transaction_id>
		<amount>1000</amount>
		<currency>EUR</currency>
		<payed>true</payed>
		<message>This iDEAL-order has successfuly been payed for, and this is the first time you check it.</message>
	</order>
</response>
EOX;

	protected static $create_payment_xml = <<< EOX
<?xml version="1.0"?>
<response>
	<order>
		<transaction_id>1234567890</transaction_id>
		<amount>1000</amount>
		<currency>EUR</currency>
		<URL>http://bankurl.com/?transaction_id=1234567890</URL>
		<message>Your iDEAL-payment has succesfuly been setup. Your customer should visit the given URL to make the payment</message>
	</order>
</response>
EOX;

	public function testBankListActionReturnsArrayOfBanks()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));
		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->with("ssl://secure.mollie.nl", 443, "/xml/ideal/", "a=banklist&partner_id=1001")
			->will($this->returnValue(self::$banks_xml));

		$banks = $iDEAL->getBanks();

		$this->assertEquals($banks, self::$expectedBanks);
	}

	public function testBankListRespectsTestMode ()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));
		$iDEAL->setTestmode(TRUE);

		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->with("ssl://secure.mollie.nl", 443, "/xml/ideal/", "a=banklist&partner_id=1001&testmode=true")
			->will($this->returnValue(self::$banks_xml));

		$banks = $iDEAL->getBanks();
		$this->assertEquals($banks, self::$expectedBanks);
	}

	public function testBankListReturnsFalseIfNoResponseReceived()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));
		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->with("ssl://secure.mollie.nl", 443, "/xml/ideal/", "a=banklist&partner_id=1001")
			->will($this->returnValue(FALSE));

		$this->assertFalse($iDEAL->getBanks());
	}

	public function testCreatePaymentActionRequiresParameters()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));

		$iDEAL->expects($this->never())
			->method("_sendRequest");

		$parameters = array (
			'bank_id' => '0031',
			'amount' => '1000',
			'description' => 'Description',
			'return_url' => 'http://customer.local/return.php',
			'report_url' => 'http://customer.local/report.php'
		);

		foreach (array('bank_id','amount','description','return_url','report_url') as $parameter)
		{
			$testParameters = $parameters;
			$testParameters[$parameter] = NULL;

			$result = call_user_func_array(array($iDEAL, 'createPayment'), $testParameters);

			$this->assertFalse($result);
			$this->assertNotEmpty($iDEAL->getErrorMessage());
		}
	}

	public function testCheckPaymentsRespectsTestMode ()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));
		$iDEAL->setTestmode(TRUE);

		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->with("ssl://secure.mollie.nl", 443, "/xml/ideal/", "a=check&partnerid=1001&transaction_id=09f911029d74e35bd84156c5635688c0&testmode=true")
			->will($this->returnValue(self::$create_payment_xml));

		$iDEAL->checkPayment("09f911029d74e35bd84156c5635688c0");
	}

	public function testCreatePaymentCanSendProfileKey()
	{
		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));
		$iDEAL->setProfileKey('12341234');

		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->with("ssl://secure.mollie.nl", 443, "/xml/ideal/", "a=fetch&partnerid=1001&bank_id=0031&amount=1000&description=Description&reporturl=http%3A%2F%2Fcustomer.local%2Freport.php&returnurl=http%3A%2F%2Fcustomer.local%2Freturn.php&profile_key=12341234")
			->will($this->returnValue(self::$create_payment_xml));

		$result = $iDEAL->createPayment(
			'0031',
			'1000',
			'Description',
			'http://customer.local/return.php',
			'http://customer.local/report.php'
		);

		$this->assertTrue($result);

		$this->assertEquals("http://bankurl.com/?transaction_id=1234567890", $iDEAL->getBankUrl());
	}

	public function testCreatePaymentActionFailureSetsErrorVariables()
	{
		$output = <<< EOX
<?xml version="1.0" ?>
<response>
	<item type="error">
		<errorcode>-3</errorcode>
		<message>The Report URL you have specified has an issue</message>
	</item>
</response>
EOX;

		$iDEAL = $this->getMock("iDEAL_Payment", array("_sendRequest"), array(1001));

		$iDEAL->expects($this->once())
			->method("_sendRequest")
			->will($this->returnValue($output));

		$result = $iDEAL->createPayment(
			'0031',
			'1000',
			'Description',
			'http://customer.local/return.php',
			'http://customer.local/report.php'
		);

		$this->assertFalse($result);
		$this->assertEquals($iDEAL->getErrorMessage(), 'The Report URL you have specified has an issue');
		$this->assertEquals($iDEAL->getErrorCode(), '-3');

	}

	public function testCheckPaymentActionChecksTransactionId()
	{
		$iDEAL = new iDEAL_Payment(1001);
		$result = $iDEAL->checkPayment(NULL);

		$this->assertFalse($result);
		$this->assertEquals("Er is een onjuist transactie ID opgegeven", $iDEAL->getErrorMessage());
	}

	public function testAPIErrorDetectedCorrectly ()
	{
		$method = new ReflectionMethod("iDEAL_Payment::_XMLisError");
		$method->setAccessible(TRUE);

		$iDEAL = new iDEAL_Payment(1001);

		$xml = new SimpleXMLElement("<?xml version=\"1.0\" ?>
		<response>
			<item type=\"error\">
				<errorcode>42</errorcode>
				<message>The flux capacitator is over capacity</message>
			</item>
		</response>");

		$this->assertTrue($method->invokeArgs($iDEAL, array($xml)));
	}

	public function testNormalXmlIsNotAnError()
	{
		$method = new ReflectionMethod("iDEAL_Payment::_XMLisError");
		$method->setAccessible(TRUE);

		$iDEAL = new iDEAL_Payment(1001);

		$xml = new SimpleXMLElement(self::$banks_xml);

		$this->assertFalse($method->invokeArgs($iDEAL, array($xml)));	}

	public function testBankErrorDetectedCorrectly()
	{
		$method = new ReflectionMethod("iDEAL_Payment::_XMLisError");
		$method->setAccessible(TRUE);

		$iDEAL = new iDEAL_Payment(1001);

		$xml = new SimpleXMLElement("<?xml version=\"1.0\" ?>
		<response>
			<order>
				<transaction_id></transaction_id>
				<amount></amount>
				<currency></currency>
				<URL>https://www.mollie.nl/files/idealbankfailure.html</URL>
				<error>true</error>
				<message>Your iDEAL-payment has not been setup because of a temporary technical error at the bank</message>
			</order>
		</response>");

		$this->assertTrue($method->invokeArgs($iDEAL, array($xml)));
	}

	public function testInvalidXmlDetected ()
	{
		$method = new ReflectionMethod("iDEAL_Payment::_XMLtoObject");
		$method->setAccessible(TRUE);

		$iDEAL = new iDEAL_Payment(1001);
		$xml = "invalid xml";
		$this->assertFalse($method->invokeArgs($iDEAL, array($xml)));
		$this->assertEquals(-2, $iDEAL->getErrorCode());
		$this->assertEquals("Kon XML resultaat niet verwerken", $iDEAL->getErrorMessage());
	}

	public function testSetAmountCannotBeLessThanMinimum()
	{
		$iDEAL = new iDEAL_Payment(1001);
		$this->assertFalse($iDEAL->setAmount(117));
		$this->assertEmpty($iDEAL->getAmount());

		$this->assertEquals(118, $iDEAL->setAmount(118));
		$this->assertEquals(118, $iDEAL->getAmount());
	}

	public function testCannotSetPartnerIdToProfileKey()
	{
		$iDEAL = new iDEAL_Payment(1001);
		$this->assertFalse($iDEAL->setPartnerId("decafbad"));
	}
}