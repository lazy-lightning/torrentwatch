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
    $htmlId = $type.'s-'.$fav->id;
    $this->response->showDialog = '#favorites';
    $this->response->showFavorite = '#'.$type.'s-'.$fav->id;
    $this->response->showTab = "#".$type.'s';
    $this->response->resetFeedItems = true;
    $this->render("/$type/show", array(
          'response' => $this->response->getContent(),
          'feedsListData' => feed::getCHtmlListData(),
          'genresListData' => genre::getCHtmlListData(),
          'qualitysListData' => quality::getCHtmlListData(),
    ));
  }
}
