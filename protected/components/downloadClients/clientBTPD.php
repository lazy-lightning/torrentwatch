<?php

class clientBTPD extends clientExecutable {
  public function addByData($data, $opts) {
    $filename = $this->saveTemp($data, $opts);
    $saveIn = $this->getSaveInDirectory($data, $opts);

    return $this->execClient(
        '/mnt/syb8634/bin/btcli',
        '-d /share/.btpd add -d '.escapeshellarg($saveIn).' '.escapeshellarg($filename)
    );
  }

}

