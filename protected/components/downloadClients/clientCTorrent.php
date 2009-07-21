<?php

class clientCTorrent extends clientPostFile
{

  private $_auth;

  protected function checkResult($result)
  {
    return True; // Not sure yet how to test
  }

  protected function getApi()
  {
    $paused = $this->config->startPaused;
    $auth = $this->getAuth();
    return $this->config->baseApi.'/upload?'.$auth.'?'.$paused;
  }

  /**
   * @return 32 character md5 based on username, challenge string, and password
   */
  protected function getAuth()
  {
    if(!empty($this->_auth))
    {
      $be = new browserEmulator();
      $be->customHttp = 'AUTH';
      $challenge = $be->file_get_contents($this->config->baseApi.'?0');
      $this->_auth = md5($this->config->username.trim($challenge).$this->config->password)
    }
    return $this->_auth;
  }

  protected function getClassName()
  {
    return __CLASS__;
  }

  protected function getFilePostName()
  {
    return 'Upload';
  }
}

