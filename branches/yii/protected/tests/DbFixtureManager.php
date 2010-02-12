<?php

Yii::import('system.test.CDbFixtureManager');
/**
 * DbFixtureManager 
 * 
 * @uses CDbFixtureManager
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class DbFixtureManager extends CDbFixtureManager {
  /**
   * isSubFixture 
   * 
   * @var mixed
   */
  private $isSubFixture = false;

  /**
   * setSubFixture 
   * 
   * @param mixed $dir 
   * @return void
   */
  public function setSubFixture($dir)
  {
    $this->resetSubFixture();
    if(!is_dir($this->basePath.DIRECTORY_SEPARATOR.$dir) || strpos($dir, DIRECTORY_SEPARATOR) !== false)
      throw new CException('Fixture sub directory is invalid: '.$dir);
    $this->basePath = $this->basePath.DIRECTORY_SEPARATOR.$dir;
    $this->isSubFixture = true;
  }

  /**
   * resetSubFixture 
   * 
   * @return void
   */
  public function resetSubFixture()
  {
    if($this->isSubFixture)
    {
      $this->basePath = dirname($this->basePath);
      $this->isSubFixture = false;
    }
  }

  /**
   * assertPostConditions 
   * 
   * @return void
   */
  protected function assertPostConditions()
  {
    $this->resetSubFixture();
    parent::assertPostConditions();
  }

  /**
   * reset sub fixture on tearDown
   * 
   * @return void
   */
  public function tearDown()
  {
    $this->resetSubFixture();
    parent::tearDown();
  }
}
