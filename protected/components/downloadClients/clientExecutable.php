<?php

abstract class clientExecutable extends BaseClient {

  // save the file to a temporary directory so it can be passed by commandline
  function saveTemp($data) {
    $filename = tempnam(Yii::app()->dvrConfig->tempDir, $this->manager->title);
    file_put_contents($filename, $data);
    return $filename;
  }

  function execClient($options) {
    $cmd = $this->config->executable;
    if(!file_exists($cmd)) {
      $this->_error = "client executable does not exist: $cmd";
      return False;
    }
    if(!is_executable($cmd)) {
      $this->_error = "client exutable does not have the right permissions: $cmd";
      return False;
    }

    Yii::log(__CLASS__." running: $cmd $options", CLogger::LEVEL_ERROR);
    exec($cmd.' '.$options, $output, $return);
    if($return == 0)
      return True;

    $this->_error = "$cmd exited with return status of $return";
    return False;
  }

}

