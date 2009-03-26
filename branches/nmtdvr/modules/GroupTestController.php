<?php

abstract class GroupTestController extends Controller {

  protected $test;

  public function __construct($groupTest) {
    $this->test = $groupTest;

    Event::add('system.post_controller', array($this, '_run'));
  }

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

  public function _run() {
    $this->test->run(new HtmlReporter);
  }

  public function index() {
    $this->test->addAllTestCases();
    $this->success = True;
  }
} // End unitTestController class
