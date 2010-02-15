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
 * And ensures sub fixtures are reset
 * 
 * @uses CWebTestCase
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class WebTestCase extends CWebTestCase
{
  public $autostop = false;

  /**
   * Sets up the class before a test is run
   * primarily used to login before testing
   *
   * @return void
   */
  protected function assertPreConditions()
  {
    $this->start();
    $this->login();
  }

  /**
   * Stop the browser session after each test
   * 
   * @return void
   */
  protected function assertPostConditions()
  {
    $this->stop();
  }

  /**
   * Ensure visibility of items before clicking them
   * 
   * @param string $locator 
   * @return void
   */
  protected function click($locator)
  {
    $this->assertElementVisible($locator);
    parent::__call('click', array($locator));
  }

  /**
   * tearDown 
   * 
   * @return void
   */
  protected function tearDown()
  {
    $app=Yii::app();
    if($app->hasComponent('fixture'))
      $app->getComponent('fixture')->resetSubFixture();
    parent::tearDown();
  }

  /**
   * Access the login form and receive authorization
   *
   * @return void
   */
  protected function login()
  {
    // Open and perform login
    $this->open('?r=site/login');
    $this->type('name=LoginForm[username]','demo');
    $this->type('name=LoginForm[password]','demo');
    $this->clickAndWait("//input[@value='Login']");
    // Open page and close initial welcome screen
    $this->open('../index-test.html');
    $this->closeWelcome();
  }

  /**
   * Close the initial welcome screen
   *
   * @return void
   */
  protected function closeWelcome()
  {
    $this->waitForElementPresent('link=Close');
    // Click the close wizard button
    $this->click('link=Close');
    $this->waitForElementNotVisible('link=Close');
  }

	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
   * and the sub fixtures for the fixture manager
   *
   * @return void
	 */
	protected function setUp()
	{
    $this->getFixtureManager()->setSubFixture($this->toString());
  	parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

  /**
   * assertElementVisible 
   * 
   * @param string $locator 
   * @param string $message 
   * @return void
   */
  public function assertElementVisible($locator, $message = '')
  {
    $this->assertEquals(true, $this->isVisible($locator), $message);
  }

  /**
   * assertElementNotVisible 
   * 
   * @param string $locator 
   * @param string $message 
   * @return void
   */
  public function assertElementNotVisible($locator, $message = '')
  {
    $this->assertEquals(false, $this->isVisible($locator), $message);
  }

  /**
   * Click element and wait for another element, optionally with a mid point
   *
   * @param string $locator
   * @param string $waitFor
   * @param string $mid
   * @return void
   */
  public function clickAndWaitFor($locator, $waitFor, $mid='id=progressbar')
  {
    $this->click($locator);
    !empty($mid) && $this->waitForElementVisible($mid);
    $this->waitForElementPresentAndVisible($waitFor);
  }

  /**
   * Pause execution until element is visible
   *
   * @param string $locator
   * @return void
   */
  public function waitForElementVisible($locator)
  {
    $locator = escapeshellarg($locator); // only escapes single quotes
    $this->waitForCondition("selenium.isVisible($locator);");
  }

  /**
   * Pause execution until element is not visible
   *
   * @param string $locator
   * @return void
   */
  public function waitForElementNotVisible($locator)
  {
    $locator = escapeshellarg($locator); // only escapes single quotes
    $this->waitForCondition("!selenium.isVisible($locator);");
  }

  /**
   * Pause execution until element is present and visible
   *
   * @param string $locator
   * @return void
   */
  public function waitForElementPresentAndVisible($locator)
  {
    $this->waitForElementPresent($locator);
    usleep(100000);
    $this->waitForElementVisible($locator);
  }
}
