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
    if($this->beforeSend($data))
    {
      $retVal = $this->addByData($data);
      $this->afterSend($retVal);
      return $retVal;
    }
    return false;
  }

  protected function afterSend($retVal)
  {
    return;
  }

  // for now just check for html, but could do full sanity check
  // to verify file is acceptable to send on to the client
  protected function beforeSend($fileData)
  {
    // $type = $this->manager->getDownloadType();
    if(empty($fileData) || strstr($fileData, '<html>') !== false)
      return false;

    return true;
  }

  public function getError() {
    return $this->_error;
  }

  protected function getFile() {
    // fake the useragent for the sake of a particular private feed
    $file = new feedAdapter_File($this->manager->getUrl(), 10, 0, null, 'Python-urllib/1.17');

    if($file->success) {
      return $file->body;
    } else {
      Yii::log($file->error, CLogger::LEVEL_ERROR);
      $this->_error = $file->error;
      // Throw exception instead?
      return False;
    }
  }

  protected function getSaveInDirectory()
  {
    $opts = $this->manager->opts;
    if(is_array($opts))
    {
      $saveIn = $opts['favorite_saveIn'];
    }
    elseif(false!==($fav=$opts->getFavorite()))
    {
      $saveIn = $fav->saveIn;
    }

    if(empty($saveIn))
      $saveIn = Yii::app()->dvrConfig->downloadDir;

    return $saveIn;
  }
}

