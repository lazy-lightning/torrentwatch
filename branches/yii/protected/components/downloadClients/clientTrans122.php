<?php

class clientTrans122 extends clientExecutable {

  public function addByData($data, $opts) {
    $filename = $this->saveTemp($data, $opts);
    $saveIn = $this->getSaveInDirectory($data, $opts);

    return $this->execClient(
        '/mnt/syb8634/bin/transmission-remote',
        '-g /share/.transmission -a '.escapeshellarg($filename)
    );
  }
}

