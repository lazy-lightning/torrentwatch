<?php

Yii::import('system.test.CDbFixtureManager');
class DbFixtureManager extends CDbFixtureManager {
  private $isSubFixture = false;

  public function setSubFixture($dir)
  {
    $this->resetSubFixture();
    if(!is_dir($this->basepath.DIRECTORY_SEPARATOR.$dir))
      throw new CException('Fixture sub directory does not exist: '.$dir);
    $this->basePath = $this->basepath.DIRECTORY_SEPARATOR.$dir;
    $this->isSubFixture = true;
    return this;
  }

  public function resetSubFixture()
  {
    if($this->isSubFixture)
    {
      $this->basePath = dirname($this->basePath);
      $this->isSubFixture = false;
    }
  }
}
