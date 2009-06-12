<?php

class clientTransmissionRPC extends BaseClient {

  function addByData($data) {
    $dest = $this->getSaveInDirectory($data);
    // transmission dies with bad folder if dest doesn't end in a /
    if(substr($dest, strlen($dest)-1, 1) != '/')
      $dest .= '/';


    $args = array('download-dir' => $dest, 
                  'metainfo'     => base64_encode($data));
    // The new transmission doesn't let you set seedratio from the initial add, have to send
    // a modify request
    //$seedRatio = $this->config->seedRatio;
    //if($seedRatio != "" && $seedRatio >= 0)
    //  $args['ratio-limit'] = $seedRatio;

    $be = new browserEmulator();
    if(!empty($this->config->username))
      $be->setAuth($this->config->username, $this->config->password);
    $be->addPostData(json_encode(array('method'=>'torrent-add', 'arguments'=>$args)));
    $be->addHeader('Content-Type', 'application/json');
    $be->addHeader('Connection', 'Close');

    $responce = json_decode($be->file_get_contents($this->config->api));
    
    if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
      return True;

    if(isset($responce['result']))
      $this->_error = "Transmission RPC Error: ".print_r($responce);
    else 
      $this->_error = "Failure connecting to Transmission RPC at ";

    return False;
  }

  public function getClassName() {
    return __CLASS__;
  }
}

