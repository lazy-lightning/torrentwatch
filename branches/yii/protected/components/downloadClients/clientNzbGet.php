<?php

class clientNzbGet extends clientExecutable {
  public function addByData($data, $opts) {
    $filename = $this->saveTemp($data, $opts);

    $this->execClient(
        '/mnt/syb8634/bin/nzbget',
        '-c /share/.nzbget/nzbget.conf -A '.escapeshellarg($filename)
    );
  }
}

