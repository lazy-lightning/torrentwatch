<?php

/**
 * startDownloadAction 
 * 
 * @uses feedItemAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class startDownloadAction extends feedItemAction 
{
  /**
   * runAction 
   * 
   * @param mixed $model 
   * @return void
   */
  protected function runAction($model)
  {
    $this->response->dialog['header'] = 'Download Feed Item';
    if(Yii::app()->dlManager->startDownload($model, feedItem::STATUS_MANUAL_DL))
    {
      $this->response->dialog['content'] = $model->title.' has been Started';
      $this->response->resetFeedItems = true;
    }
    else
    {
      $this->response->dialog['error'] = true;
      $this->response->dialog['content'] = CHtml::errorSummary(Yii::app()->dlManager);
    }

    echo $this->response->getContent();
  }
}
