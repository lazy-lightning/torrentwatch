<?

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

  function getClassName() 
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
        'cat'=>'Default',
        'pp'=>'-1',
    );
  }
}

