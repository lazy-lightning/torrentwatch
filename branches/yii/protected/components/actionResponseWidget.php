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
      $this->jScript[] = "$('{$this->delete}').remove()";
    // FIXME: stupidly ugly
    if($this->alsoDelete)
      $this->jScript[] = "$('{$this->alsoDelete}').remove()";
    if($this->append)
      $this->jScript[] = "$('{$this->append['selector']}').remove().appendTo('{$this->append['parent']}');";
    if($this->resetFeedItems)
      $this->jScript[] = "$('#feedItems_container').tabsResetAjax()";
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

  public function run()
  {
    echo $this->getContent();
  }

}

