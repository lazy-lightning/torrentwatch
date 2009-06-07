<?php

class clientBTPD extends clientExecutable {
  public function addByData($data) {
    $filename = $this->saveTemp($data);
    $saveIn = $this->getSaveInDirectory($data);

    return $this->execClient(
        '-d '.escapeshellarg($this->config->directory).' add -d '.escapeshellarg($saveIn).' '.escapeshellarg($filename)
    );
  }

  public function getClassName() {
    return __CLASS__;
  }
}

