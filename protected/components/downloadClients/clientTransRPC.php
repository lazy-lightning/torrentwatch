<?php

class clientTransmissionRPC extends baseClient {

  protected $api = "http://127.0.0.1:9091/transmission/rpc";

  function addByData($data, $opts) {
    $dest = $this->getSaveInDirectory($data, $opts);
    // transmission dies with bad folder if dest doesn't end in a /
    if(substr($dest, strlen($dest)-1, 1) != '/')
      $dest .= '/';

    $seedRatio = Yii::app()->dvrConfig->seedRatio;
    if(is_array($opts)) {
      $r = Yii::app()->db->createCommand("SELECT seedRatio FROM favorite WHERE id = ".$opts['favorite_id'])->queryScalar();
      if(is_numeric($r) && $r >= 0)
        $seedRatio = $r;
    }

    $args = array('download-dir' => $dest, 
                  'metainfo'     => base64_encode($data));
    if($seedRatio != "" && $seedRatio >= 0)
      $args['ratio-limit'] = $seedRatio;

    $be = new browserEmulator();
    $be->addPostData(json_encode(array('method'=>'torrent-add', 'arguments'=>$args)));
    $be->addHeader('Content-Type', 'application/json');
    $be->addHeader('Connection', 'Close');

    $responce = $be->file_get_contents($this->api);

    if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
      return True;

    if(isset($responce['result']))
      $this->_error = "Transmission RPC Error: ".print_r($responce);
    else 
      $this->_error = "Failure connecting to Transmission RPC at ";
    return False;
  }

}

