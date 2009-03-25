<?php

abstract class GroupTestController extends Controller {

  public $test;

  public function __call($method, $arguments) {
    $tests = is_array($arguments[0]) ? $arguments[0] : array();
    $tests[] = $method;

    foreach($tests as $test) {
      if(substr($test, 0, 6) != 'testOf') {
        continue;
      }
      $this->test->addTestCase($test);
    }
    if($this->success === False) {
      $this->index();
    }
  }

  public function __destruct() {
    $this->test->run(new HtmlReporter);
  }

  public function index() {
    $this->test->addAllTestCases();
    $this->success = True;
  }
} // End unitTestController class
