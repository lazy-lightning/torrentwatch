<?php

abstract class clientPostFile extends BaseClient {

  public $fileExtension = '';

  /**
   * @param string result page returned from http post
   * @return boolean true if file was successfully started
   */
  abstract protected function checkResult($result);

  /**
   * @return string the web page to post the file to
   */
  abstract protected function getApi();

  /**
   * @return string the variable name given to the webpage file input
   */
  abstract protected function getFilePostName();

  /**
   * Defaults to no additional post data
   * @return array of $key => $value form data to be posted along with the file.
   */
  protected function getPostData()
  {
    return array();
  }

  /**
   * Starts a download using HTTP POST
   * @param string data of the file to be posted
   * @return boolean was the download successfully started
   */
  function addByData($data) {
    Yii::trace(__CLASS__."::".__FUNCTION__);
    $be = new browserEmulator();
    $be->multiPartPost = true;

    foreach($this->getPostData() as $key=>$value)
      $be->addPostData($key, $value);

    $be->addPostData($this->getFilePostName(), array(
          'filename'=>$this->manager->title.$this->fileExtension,
          'contents'=>$data,
    ));
    $result = $be->file_get_contents($this->getApi());

    return $this->checkResult($result);
  }

}
