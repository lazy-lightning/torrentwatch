<?php

// This action can be attached to any controller managing
// an AR class that has a relation directly to feedItem

class makeFavoriteAction extends feedItemAction 
{
  protected function runAction($model)
  {
    $fav = $model->generateFavorite();
    $type=get_class($fav);
    $transaction = Yii::app()->db->beginTransaction();
    try {
      $fav->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    // After save to get the correct id
    $htmlId = $type.'-'.$fav->id;
    $this->response->showDialog = '#favorites';
    $this->response->showTab = "#".$type;
    $this->response->resetFeedItems = true;
    Yii::app()->getController->render("/$type/show", array(
          'model' => $fav,
          'response' => $this->response->getContent(),
          'feedsListData' => feed::getCHtmlListData(),
          'genresListData' => genre::getCHtmlListData(),
          'qualitysListData' => quality::getCHtmlListData(),
    ));
  }
}
