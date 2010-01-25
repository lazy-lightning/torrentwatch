<?php

class actionResponseWidget extends CWidget{

  public $showDialog = false;

  public $showFavorite = false;

  public $showTab = false;

  public $dialog = false;

  protected $jScript = array();

  public function getContent()
  {
    Yii::log(print_r($this,true));
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

