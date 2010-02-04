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
      $success = $fav->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    // After save to get the correct id
    $this->response->showTab = "#{$type}_tab a";
    $this->response->showFavorite = "#{$type}-{$fav->id}";
    $this->response->resetFeedItems = true;
    $this->response->append = array(
        array(
          'selector'=>"#{$type}-{$fav->id}",
          'parent'=>"#{$type}_container",
    ));
    if($success)
    {
      $this->response->append[] = array(
          'selector' =>"#${type}-li-{$fav->id}",
          'parent'=>"#{$type}List",
      );
    }
    Yii::app()->getController()->render("/$type/show", array(
          'model' => $fav,
          'addLi' => $success,
          'response' => $this->response->getContent(),
          'feedsListData' => feed::getCHtmlListData(),
          'genresListData' => genre::getCHtmlListData(),
          'qualitysListData' => quality::getCHtmlListData(),
    ));
  }
}
