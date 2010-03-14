<?php

class clientSABnzbd extends clientPostFile {

  public $fileExtension = '.nzb';
  public $successString = 'This resource resides temporarily at';

  protected function checkResult($result)
  {
    return substr($result, 0, strlen($this->successString)) == $this->successString ? True : False;
  }

  protected function getApi()
  {
    return $this->config->baseApi.'addFile';
  }

  public function getClassName() 
  {
    return __CLASS__;
  }

  protected function getFilePostName()
  {
    return 'nzbfile';
  }

  protected function getPostData() 
  {
    return array(
        'cat'=>$this->config->category,
        'pp'=>'-1',
    );
  }
}

