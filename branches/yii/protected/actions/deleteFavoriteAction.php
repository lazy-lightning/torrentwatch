<?php
// This action can be attached to any AR Controller controlling a
// model decended from BaseFavorite

class deleteFavoriteAction extends CAction
{
  protected $response;

  protected function getMatchingIds($model)
  {
    $class = get_class($model);
    $sql = "SELECT feedItem_id FROM matching${class}s WHERE ${class}s_id = {$model->id} AND feedItem_status NOT IN".
                "('".feedItem::STATUS_AUTO_DL."', '".feedItem::STATUS_MANUAL_DL."');";

    $reader = $model->getDbConnection()->CreateCommand($sql)->queryAll();
    $ids = array();
    foreach($reader as $row)
    {
      $ids[] = $row['feedItem_id'];
    }
    return $ids;
  }

  public function run()
  {
    $this->response = new actionResponseWidget;
    $this->response->dialog = array('header'=>'Delete Favorite');

    if(isset($_GET['id']) && is_numeric($_GET['id']))
    {
      $this->attachBehavior('loadModel', array('class'=>'loadControllerARModelBehavior'));

      // Have to get the matching information before deleting the row
      // TODO: with the model information already loaded, does this really have
      //       to be done ahead of time?
      $model = $this->asa('loadModel')->loadModel($_GET['id']);
      $ids = $this->getMatchingIds($model);

      $transaction = $model->getDbConnection()->beginTransaction();
      try {
        if($model->deleteByPk($_GET['id']))
        {
          // Reset feedItem status on anything this was matching, then rerun
          // matching routine incase something else matches the reset items
          feedItem::model()->updateByPk($ids, array('status'=>feedItem::STATUS_NEW));
          Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
          $this->response->dialog['content'] = 'Your favorite has been successfully deleted';
          $this->response->resetFeedItems = true;
          $this->response->delete = array(
              '#'.get_class($model).'-li-'.$_GET['id'],
              '#'.get_class($model).'-'.$_GET['id'],
          );
        }
        else
        {
          $this->response->dialog['content'] = 'Unable to delete favorite';
          $this->response->dialog['error'] = True;
        }
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }

    echo $this->response->getContent();
  }
}
