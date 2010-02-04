<?php

class actionResponseWidget extends CWidget{

  //TODO: maybee these should be functions instead of variables?
  //      benefit here though is that we control the order
  public $showDialog = false;

  public $showFavorite = false;

  public $showTab = false;

  public $dialog = false;

  public $resetFavorites = false;

  public $resetFeedItems = false;

  public $append = false;

  public $delete = false;

  public $alsoDelete = false;

  protected $jScript = array();

  public function getContent()
  {
    if($this->delete)
    {
      if(!is_array($this->delete))
        $this->delete = array($this->delete);
      foreach($this->delete as $delete)
        $this->jScript[] = "$('{$delete}').remove()";
    }
    if($this->append)
    {
      if(isset($this->append['selector']))
        $this->append = array($this->append);
      foreach($this->append as $row)
        $this->jScript[] = "$.ajaxAppend('{$row['selector']}', '{$row['parent']}')";
    }
    if($this->resetFeedItems)
      $this->jScript[] = "$('#feedItems_container').addClass('needsReset')";

    if($this->showTab)
      $this->jScript[] = "$.showTab('{$this->showTab}')";
    elseif($this->showDialog)
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

  public function run()
  {
    echo $this->getContent();
  }

}

