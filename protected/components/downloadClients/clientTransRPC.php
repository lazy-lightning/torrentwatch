<?php

class clientTransRPC extends BaseClient {

  /**
   * @var browserEmulator an instance configured for communicating with transmission
   */
  private $_be = null;

  /**
   * @param contents of a .torrent file to be added to transmission
   * @return boolean if download was started
   */
  function addByData($data) {
    $be = $this->getBrowserEmulator();

    $be->addPostData(json_encode(array(
            'method'=>'torrent-add', 
            'arguments'=>array(
              'download-dir' => $this->getSaveInDirectory(),
              'metainfo'     => base64_encode($data),
            ),
    )));

    $response = $this->getTransmissionResponse($be);

    return $this->checkResponse($response);
  }

  /**
   * @param array responce array returned from transmission json request
   * @return boolean if transmission successfully received the torrent
   */
  function checkResponse($response)
  {
    if(isset($response->result) AND ($response->result == 'success' or $response->result == 'duplicate torrent'))
      return True;

    if(isset($response->result))
      $this->_error = "Transmission RPC Error: ".print_r($response);
    else 
      $this->_error = "Failure connecting to Transmission RPC at ".$this->getApi();

    return False;
  }

  /**
   * @return string URL for communicating with transmission via RPC
   */
  public function getApi()
  {
    // transmission doesn't like a doubled up / in the request uri
    return rtrim($this->config->baseApi, '/').'/rpc';
  }

  /**
   * @return browserEmulator object for communicating with transmission via RPC
   */
  function getBrowserEmulator()
  {
    if($this->_be === null) 
    {
      $this->_be = new browserEmulator();
      if(!empty($this->config->username))
        $this->_be->setAuth($this->config->username, $this->config->password);
      $this->_be->addHeaderLine('Content-Type', 'application/json');
      $this->_be->addHeaderLine('Connection', 'Close');
    }
    $this->_be->resetPostData();
    return $this->_be;
  }

  /**
   * @return string directory to save download to
   */
  protected function getSaveInDirectory()
  {
    // transmission dies with bad folder if dest doesn't end in a /
    return rtrim(parent::getSaveInDirectory(), '/').'/';
  }

  /**
   * @param browserEmulator instance fully configured with post data to query transmission
   * @return mixed response from transmission
   */
  function getTransmissionResponse($be)
  {
    $api = $this->getApi();
    $response = $be->file_get_contents($api);

    // Invalid session id, set it and try again
    if(substr($response, 0, 7) === '<h1>409')
    {
      if(preg_match('/X-Transmission-Session-Id: ([A-Za-z0-9]+)/', $response, $regs)) {
        $be->addHeaderLine('X-Transmission-Session-Id', $regs[1]);
        $response = $be->file_get_contents($api);
      }
    }
        
    return json_decode($response);
  }

  public function getClassName() {
    return __CLASS__;
  }
}

