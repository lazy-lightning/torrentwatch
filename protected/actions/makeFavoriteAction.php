<?php

// This action can be attached to any controller managing
// an AR class that has a relation directly to feedItem

class makeFavoriteAction extends feedItemAction 
{
  protected function runAction($model)
  {
    $this->response['dialog']['header'] = 'Add new favorite';
    $fav = $model->generateFavorite();
    $type=get_class($fav).'s';
    $transaction = Yii::app()->db->beginTransaction();
    try {
      if($fav->save())
      {
        $this->response['dialog']['content'] = 'New favorite successfully saved';
        $htmlId = $type.'-'.$fav->id;
      }
      else
      {
        $this->response['dialog']['error'] = true;
        $this->response['dialog']['content'] = 'Failure saving new favorite';
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    // After save to get the correct id
    $this->response[get_class($fav)] = $fav;
    $this->response['showFavorite'] = '#'.$type.'-'.$fav->id;
    $this->response['showTab'] = "#".$type;
  }
}
