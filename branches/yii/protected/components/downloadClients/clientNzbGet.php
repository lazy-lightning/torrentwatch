<?php

class clientNzbGet extends clientExecutable {

  public function addByData($data) {
    $filename = $this->saveTemp($data);

    $this->execClient(
        '-c '.escapeshellarg($this->client->nzbgetConf).' -A '.escapeshellarg($filename)
    );
  }

  public function getClassName() {
    return __CLASS__;
  }
}

