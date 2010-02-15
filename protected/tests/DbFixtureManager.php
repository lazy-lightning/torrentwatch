<?php

Yii::import('application.tests.NDbFixtureManager');
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
class DbFixtureManager extends NDbFixtureManager {
  /**
   * subFixture 
   * 
   * @var mixed either boolean false or two item array indicating sub-dirs to check
   */
  private $subFixture = false;

  /**
   * setSubFixture 
   * 
   * @param string $testString format of className::funcName
   * @return void
   */
  public function setSubFixture($testString)
  {
    $breadcrumbs = explode('::', $testString);
    if(count($breadcrumbs)===2)
      $this->subFixture = $breadcrumbs;
  }

  /**
   * resetSubFixture 
   * 
   * @return void
   */
  public function resetSubFixture()
  {
    $this->subFixture = false;
  }

  public function getFixtureFile($file)
  {
    if($this->subFixture)
    {
      // try basePath/testClass/testMethod/$file
      $path=$this->basePath.DIRECTORY_SEPARATOR.$this->subFixture[0].DIRECTORY_SEPARATOR.$this->subFixture[1].DIRECTORY_SEPARATOR.$file;
      // if not, try basePath/testClass/$file
      if(!file_exists($path))
        $path=$this->basePath.DIRECTORY_SEPARATOR.$this->subFixture[0].DIRECTORY_SEPARATOR.$file;
    }
    if(empty($path) || !file_exists($path))
      $path = parent::getFixtureFile($file);

    return $path;
  }
}
