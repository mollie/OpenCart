<?php

class TranslationTest extends PHPUnit_Framework_TestCase
{
	private static $LANGUAGES = array("dutch", "english");

	private static $keys = array();

	/**
	 * @param $path
	 * @dataProvider dpTranslationPaths
	 */
	public function testTranslationFilesHaveIdenticalKeys($orig_path)
	{
		$path = $orig_path . DIRECTORY_SEPARATOR . "language";

		$keys = array();

		foreach (self::$LANGUAGES as $language)
		{
			$lang_path = $path . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . "payment" . DIRECTORY_SEPARATOR . "mollie_ideal.php";
			$_ = array();
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

	public static $OPENCART_TEXTKEYS = array(
		"text_enabled",
		"text_disabled",
		"text_yes",
		"text_no",
		"text_none",
		"button_save",
		"button_cancel",
		"tab_general",
		"text_home",
		"button_confirm",
	);

	/**
	 * @depends testTranslationFilesHaveIdenticalKeys
	 * @param $path
	 * @dataProvider dpTranslationPaths()
	 */
	public function testAllUsedKeysPresent($orig_path)
	{
		$path = $orig_path . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . "payment" . DIRECTORY_SEPARATOR . "mollie_ideal.php";

		preg_match_all('!->language->get\(\'(?P<text_key>\w+)\'\)!', file_get_contents($path), $matches);

		foreach (self::$LANGUAGES as $language)
		{
			foreach (array_filter($matches["text_key"], function ($each) {
				return !in_array($each, TranslationTest::$OPENCART_TEXTKEYS);
			}) as $text_key)
			{
				$this->assertContains($text_key, self::$keys[$orig_path][$language]);
			}
		}
	}
}