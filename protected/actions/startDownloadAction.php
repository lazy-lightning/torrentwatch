<?php

class startDownloadAction extends feedItemAction 
{
  protected function runAction($model)
  {
    $this->response['dialog']['header'] = 'Download Feed Item';
    if(Yii::app()->dlManager->startDownload($model, feedItem::STATUS_MANUAL_DL))
    {
        $this->response['dialog']['content'] = $model->title.' has been Started';
    }
    else
    {
      $this->response['dialog']['error'] = true;
      $this->response['dialog']['content'] = CHtml::errorSummary(Yii::app()->dlManager);
    }
  }
}
