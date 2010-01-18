<?php

class clientCTorrent extends clientPostFile
{

  private $_auth;

  public $successResponse = 'FILE UPLOADED';

  protected function checkResult($result)
  {
    return trim($result) === $this->successResponse;
  }

  protected function getApi()
  {
    $auth = $this->getAuth();
    // two ? marks is intentional, for some reason ctorrent expects that
    return rtrim($this->config->baseApi, '/')."/upload?{$auth}?{$this->config->startPaused}";
  }

  /**
   * @return 32 character md5 based on username, challenge string, and password
   */
  protected function getAuth()
  {
    if(empty($this->_auth))
    {
      $be = new browserEmulator();
      $be->customHttp = 'AUTH';
      $challenge = trim($be->file_get_contents(rtrim($this->config->baseApi, '/').'/0'));
      Yii::log('CTorrent challenge: '.$challenge, CLogger::LEVEL_INFO);
      $response = md5($this->config->username.$challenge.$this->config->password);
      Yii::log('Our Response: '.$response, CLogger::LEVEL_INFO);
      $auth = trim($be->file_get_contents(rtrim($this->config->baseApi, '/').'/1?'.$response));
      Yii::log('CTorrent auth: '.$auth, CLogger::LEVEL_INFO);
      $this->_auth = $auth;
    }
    return $this->_auth;
  }

  public function getClassName()
  {
    return __CLASS__;
  }

  protected function getFilePostName()
  {
    return 'Upload';
  }
}
