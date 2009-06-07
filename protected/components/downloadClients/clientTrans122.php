<?php

class clientTrans122 extends clientExecutable {

  public function addByData($data) {
    $filename = $this->saveTemp($data);
    $saveIn = $this->getSaveInDirectory();

    return $this->execClient(
        '-g '.escapeshellarg($this->config->directory).' -a '.escapeshellarg($filename)
    );
  }

  public function getClassName() {
    return __CLASS__;
  }
}

