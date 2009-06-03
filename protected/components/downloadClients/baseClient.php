<?php

// base class which all download clients extend from
abstract class baseClient {
  private $_error;

  abstract function addByData($data, $opts);

  public function addByUrl($url, $opts) {
    $data = $this->getFile($url);
    return $data === False ? False : $this->addByData($data, $opts);
  }

  function getError() {
    return $this->_error;
  }

  function getFile($url) {
    // contains the feedAdapter_File and related simplepie classes
    // which automate http access through curl or fsockopen
    require_once('protected/components/feedAdapters/feedAdapter.php');
    $file = new feedAdapter_File($url, 10, 0);

    if($file->success) {
      return $file->body;
    } else {
      $this->_error = $file->error;
      // Throw exception instead?
      return False;
    }
  }

  function getSaveInDirectory($opts) {
    if(is_array($opts)) {
      $saveIn = $opts['favorite_saveIn'];
    }

    if(empty($saveIn))
      $saveIn = Yii::app()->dvrConfig->downloadDir;

    return $saveIn;
  }

  function getTitle($opts) {
    return is_array($opts) ? $opts['feedItem_title'] : $opts->title;
  }
}

