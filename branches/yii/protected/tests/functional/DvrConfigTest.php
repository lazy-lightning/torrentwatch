<?php
class dvrConfigTest extends WebTestCase
{
  public $autoStop = false;

  protected function assertPreConditions()
  {
    parent::assertPreConditions();
    $this->clickAndWaitFor('link=Configure', 'id=configuration', false);
    $this->assertVisible('id=global_config');
  }

  protected function assertPostConditions()
  {
    if($this->isVisible('css=div.close'))
      $this->click('css=div.close');
  }

  function testDefaultSave()
  {
    $this->assertElementPresent('link=Save');
    $this->clickAndWaitFor('link=Save', 'id=actionResponse');
    $this->assertText('id=actionResponse', 'successfull');
  }

  function testUpdateGlobalConfig()
  {
    $this->type('id=dvrConfig_webItemsPerLoad', 100);
    $this->clickAndWaitFor('link=Save', 'id=actionResponse');
    $this->assertText('id=actionResponse', 'successfull');
  }

  public function clickAndWaitFor($locator, $waitFor = 'id=configuration', $mid='id=progressbar')
  {
    parent::clickAndWaitFor($locator, $waitFor, $mid);
  }
}
