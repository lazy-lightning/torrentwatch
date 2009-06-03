<?php

abstract class clientExecutable extends baseClient {

  // save the file to a temporary directory so it can be passed by commandline
  function saveTemp($data, $opts) {
    $filename = tempnam(Yii::app()->dvrConfig->tempDir, $this->getTitle($opts));
    file_put_contents($filename, $data);
    return $filename;
  }

  function execClient($cmd, $options) {
    if(!file_exists($cmd))
      return "client executable does not exist: $cmd";

    exec($cmd.' '.$options, $output, $return);
    if($return == 0)
      return True;

    $this->_error = "$cmd exited with return status of $return";
    return False;
  }

}

