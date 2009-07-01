<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');

// Add all the modules/models
class TwGroupTest extends GroupTest {

  public function __construct() {
    parent::__construct();
    $this->testDir = dirname(realpath(__FILE__));
  }

  public function addAllTestCases() {
    $dh = opendir($this->testDir);
    while(($file = readdir($dh)) !== False) {
      if(substr($file, -4) != '.php' ||
         substr($file, 0, 6) != 'testOf') {
        continue;
      }
      require_once($file);
      $class = substr($file, 0, -4);
      parent::addTestCase(new $class);
    }
  }

  public function addTestCase($testCase) {
    $file = $this->testDir."/$test.php";
    if(file_exists($file)) {
      require_once($file);
      parent::addTestCase(new $testCase);
      return True;
    }
    return False;
  }

}