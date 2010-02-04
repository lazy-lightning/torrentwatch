<?php

Yii::import('system.test.CDbFixtureManager');
class DbFixtureManager extends CDbFixtureManager {
  private $isSubFixture = false;

  public function setSubFixture($dir)
  {
    $this->resetSubFixture();
    if(!is_dir($this->basePath.DIRECTORY_SEPARATOR.$dir) || strpos($dir, DIRECTORY_SEPARATOR) !== false)
      throw new CException('Fixture sub directory is invalid: '.$dir);
    $this->basePath = $this->basePath.DIRECTORY_SEPARATOR.$dir;
    $this->isSubFixture = true;
  }

  public function resetSubFixture()
  {
    if($this->isSubFixture)
    {
      $this->basePath = dirname($this->basePath);
      $this->isSubFixture = false;
    }
  }

  protected function assertPostConditions()
  {
    $this->resetSubFixture();
    parent::assertPostConditions();
  }

  public function tearDown()
  {
    $this->resetSubFixture();
    parent::tearDown();
  }
}
