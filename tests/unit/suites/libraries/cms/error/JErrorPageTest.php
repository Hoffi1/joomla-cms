<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JErrorPage.
 */
class JErrorPageTest extends TestCaseDatabase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		TestReflection::setValue('JDocument', 'instances', array());
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @covers  JErrorPage::render
	 */
	public function testEnsureTheErrorPageIsCorrectlyRendered()
	{
		$documentResponse = '<title>500 - Testing JErrorPage::render()</title>';

		$key = serialize(array('error', array()));

		$mockErrorDocument = $this->getMockBuilder('JDocumentError')
			->setMethods(array('setError', 'setTitle', 'render'))
			->getMock();

		$mockErrorDocument->expects($this->any())
			->method('render')
			->willReturn($documentResponse);

		TestReflection::setValue('JDocument', 'instances', array($key => $mockErrorDocument));

		// Create an Exception to inject into the method
		$exception = new RuntimeException('Testing JErrorPage::render()', 500);

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		JErrorPage::render($exception);
		$output = ob_get_clean();

		// Validate the mocked response from JDocument was received
		$this->assertEquals($documentResponse, $output);
	}

	/**
	 * @covers  JErrorPage::render
	 */
	public function testEnsureTheErrorPageIsCorrectlyRenderedWithEngineExceptions()
	{
		// Only test for PHP 7+
		if (PHP_MAJOR_VERSION < 7)
		{
			$this->markTestSkipped('Test only applies to PHP 7+');
		}

		// Create an Error to inject into the method
		$exception = new Error('Testing JErrorPage::render()', 500);

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		JErrorPage::render($exception);
		$output = ob_get_clean();

		// Validate the <title> element was set correctly
		$this->assertContains('Testing JErrorPage::render()', $output);
	}

	/**
	 * @covers  JErrorPage::render
	 */
	public function testEnsureTheRenderMethodCorrectlyHandlesNonExceptionClasses()
	{
		// Create an object to inject into the method
		$object = new stdClass;

		// The render method echoes the output, so catch it in a buffer
		ob_start();
		JErrorPage::render($object);
		$output = ob_get_clean();

		// Validate the <title> element was set correctly
		$this->assertContains('Error displaying the error page', $output);
	}
}
