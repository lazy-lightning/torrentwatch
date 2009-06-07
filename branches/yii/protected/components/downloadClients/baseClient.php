<?php

// base class which all download clients extend from
abstract class baseClient {
  private $_error;
  public $manager;
  public $config;

  abstract function addByData($data);
  abstract function getClassName();

  public function __construct($manager) {
    $this->manager = $manager;
    $class = $this->getClassName();
    $this->config = Yii::app()->dvrConfig->$class;
  }

  public function addByUrl($url) {
    $data = $this->getFile();
    return $data === False ? False : $this->addByData($data);
  }

  public function getError() {
    return $this->_error;
  }

  protected function getFile() {
    $file = new feedAdapter_File($this->manager->getUrl(), 10, 0);

    if($file->success) {
      return $file->body;
    } else {
      Yii::log($file->error, CLogger::LEVEL_ERROR);
      $this->_error = $file->error;
      // Throw exception instead?
      return False;
    }
  }

  protected function getSaveInDirectory() {
    $opts = $this->manager->opts;
    if(is_array($opts)) {
      $saveIn = $opts['favorite_saveIn'];
    }

    if(empty($saveIn))
      $saveIn = Yii::app()->dvrConfig->downloadDir;

    return $saveIn;
  }

}

