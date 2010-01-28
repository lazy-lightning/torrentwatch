<?php

/**
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 */
define('TEST_BASE_URL','http://xbmclive/nmtdvr/nmtdvr-test.php/');

/**
 * The base class for functional test cases.
 * In this class, we set the base URL for the test application.
 * We also provide some common methods to be used by concrete test classes.
 */
class WebTestCase extends CWebTestCase
{
  public $autostop = false;

  /**
   * Sets up the class before a test is run
   * primarily used to login before testing
   */
  protected function assertPreConditions()
  {
    $this->start();
    $this->login();
  }

  protected function assertPostConditions()
  {
    $this->stop();
  }

  protected function tearDown()
  {
    $app=Yii::app();
    if($app->hasComponent('fixture'))
      $app->getComponent('fixture')->resetSubFixture();
    parent::tearDown();
  }

  /**
   * Access the login form and receive authorization
   */
  protected function login()
  {
    // Open and perform login
    $this->open('?r=site/login');
    $this->type('name=LoginForm[username]','demo');
    $this->type('name=LoginForm[password]','demo');
    $this->clickAndWait("//input[@value='Login']");
    // Open page, wait for progress bar to finish
    $this->open('../index-test.html');
    $this->waitForElementPresent('link=Close');
    // Click the close wizard button
    $this->click('link=Close');
    $this->waitForElementNotVisible('link=Close');
  }

	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

  public function assertElementVisible($locator)
  {
    $this->assertEquals($this->getEval("selenium.isVisible('$locator');"), TRUE);
  }

  public function clickAndWaitFor($locator, $waitFor, $mid='id=progressbar')
  {
    $this->click($locator);
    !empty($mid) && $this->waitForElementVisible($mid);
    $this->waitForElementPresentAndVisible($waitFor);
  }

  /**
   * Pause execution until element is visible
   */
  public function waitForElementVisible($locator)
  {
    $this->waitForCondition("selenium.isVisible('$locator');");
  }

  /**
   * Pause execution until element is not visible
   */
  public function waitForElementNotVisible($locator)
  {
    $this->waitForCondition("!selenium.isVisible('$locator');");
  }

  public function waitForElementPresentAndVisible($locator)
  {
    $this->waitForElementPresent($locator);
    usleep(100000);
    $this->waitForElementVisible($locator);
  }
}
