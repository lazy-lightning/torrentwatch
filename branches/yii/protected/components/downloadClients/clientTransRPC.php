<?php

class clientTransRPC extends BaseClient {

  // browser emulator
  private $be = null;

  function addByData($data) {
    $dest = $this->getSaveInDirectory($data);
    // transmission dies with bad folder if dest doesn't end in a /
    if(substr($dest, -1, 1) !== '/')
      $dest .= '/';
    // transmission also doesn't like a doubled up / in the request uri
    $api = $this->config->baseApi;
    if(substr($api, -1, 1) !== '/')
      $api .= '/';
    $api .= 'rpc';

    if($this->be === null) {
      $this->be = new browserEmulator();
      if(!empty($this->config->username))
        $this->be->setAuth($this->config->username, $this->config->password);
      $this->be->addHeaderLine('Content-Type', 'application/json');
      $this->be->addHeaderLine('Connection', 'Close');
    }

    $this->be->resetPostData();
    $args = array('download-dir' => $dest, 
                  'metainfo'     => base64_encode($data));
    $this->be->addPostData(json_encode(array('method'=>'torrent-add', 'arguments'=>$args)));


    $response = $this->be->file_get_contents($api);

    // Invalid session id, set it and try again
    if(substr($response, 0, 7) === '<h1>409')
    {
      if(preg_match('/X-Transmission-Session-Id: ([A-Za-z0-9]+)/', $response, $regs)) {
        $this->be->addHeaderLine('X-Transmission-Session-Id', $regs[1]);
        $response = $this->be->file_get_contents($api);
      }
    }
        
    $response = json_decode($response);

    if(isset($response->result) AND ($response->result == 'success' or $response->result == 'duplicate torrent'))
      return True;

    file_put_contents('/tmp/transRpc.Failure', $this->be->lastRequest."\n\n".$this->be->lastResponse);

    if(isset($response->result))
      $this->_error = "Transmission RPC Error: ".print_r($response);
    else 
      $this->_error = "Failure connecting to Transmission RPC at ";

    return False;
  }

  public function getClassName() {
    return __CLASS__;
  }
}

