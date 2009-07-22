<?php

class clientNZBGet extends clientExecutable {

  public function addByData($data) {
    $filename = $this->saveTemp($data);

    return $this->execClient(
        '-c '.escapeshellarg($this->config->nzbgetConf).' -A '.escapeshellarg($filename)
    );
  }

  public function getClassName() {
    return __CLASS__;
  }
}

