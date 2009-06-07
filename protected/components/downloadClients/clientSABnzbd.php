<?
class clientSABnzbd extends baseClient {

  function addByData($data) {
    // Emulate submitting the add file box on the sabnzbd+ home page
    $be = new browserEmulator();
    $be->multiPartPost = true;
    $be->addPostData('nzbfile', array('filename'=>$this->manager->title.'.nzb', 'contents'=>$data));
    $be->addPostData('cat', 'Default');
    $be->addPostData('pp', '-1');
    $result = $be->file_get_contents($this->config->baseApi.'addFile');

    $successString = 'This resource resides temporarily at'
    return substr($result, 0, strlen($successString)) == $successString ? True : False;
  }

  function getClassName() {
    return __CLASS__;
  }
}

