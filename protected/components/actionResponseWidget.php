<?php

class actionResponseWidget extends CWidget{

  public $showDialog = false;

  public $showFavorite = false;

  public $showTab = false;

  public $dialog = false;

  public $resetFeedItems = false;

  protected $jScript = array();

  public function getContent()
  {
    if($this->resetFeedItems)
      $this->jScript[] = "$.resetFeedItems()";
    if($this->showTab)
      $this->jScript[] = "$.showTab('{$this->showTab}')";
    if($this->showDialog)
      $this->jScript[] = "$.showDialog('{$this->showDialog}')";
    elseif(!empty($this->dialog))
      $this->jScript[] = "$.showDialog('#actionResponse')";

    if($this->showFavorite)
      $this->jScript[] = "$.showFavorite('{$this->showFavorite}')";
    return $this->render('response', array(
        'jScript' => implode(";\n  ", $this->jScript),
        'dialog' => $this->dialog,
    ), true);
  }

}

