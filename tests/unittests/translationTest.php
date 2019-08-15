<?php

class TranslationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var array
	 */
	private static $LANGUAGES = array("dutch", "english", "french", "nl-nl", "en-gb", "fr-fr");

	/**
	 * @var array
	 */
	private static $keys      = array();

	/**
	 * @param $orig_path
	 *
	 * @dataProvider dpTranslationPaths
	 */
	public function testTranslationFilesHaveIdenticalKeys($orig_path)
	{
		$path = $orig_path . DIRECTORY_SEPARATOR . "language";

		$keys = array();

		foreach (self::$LANGUAGES as $language)
		{
			$lang_path = $path .
                DIRECTORY_SEPARATOR . $language .
                DIRECTORY_SEPARATOR . "payment" .
                DIRECTORY_SEPARATOR . "mollie.php";
			$_         = array();

			include $lang_path;

			$keys[$language] = array_keys($_);
		}

		self::$keys[$orig_path] = $keys;

		$diff = call_user_func_array("array_diff", $keys);
		$this->assertCount(0, $diff);
	}

	public function dpTranslationPaths()
	{
		return array(
			array(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "admin"),
			array(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "catalog"),
		);
	}
}
