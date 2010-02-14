<?php


/**
 * This action can be attached to any controller managing
 * an AR class that has a relation directly to feedItem
 * 
 * @uses feedItemAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class makeFavoriteAction extends feedItemAction 
{
  /**
   * generates and saves a favorite based on the given feed item.
   * Perhaps rest should be in view?
   * Then sets up an actionResponseWidget to display the new favorite
   * and append a new li to its list when needed 
   * 
   * @param feedItem $model 
   * @return void
   */
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
          'selector'=>"#{$type}-{$fav->id} li",
          'parent'=>"#{$type}List",
          'delete'=>"#{$type}List #{$type}-li-{$fav->id}"
      );
    }
    Yii::app()->getController()->render("/$type/show", array(
          'model' => $fav,
          'success' => $success,
          'create' => true,
          'response' => $this->response->getContent(),
          'feedsListData' => feed::getCHtmlListData(),
          'genresListData' => genre::getCHtmlListData(),
          'qualitysListData' => quality::getCHtmlListData(),
    ));
  }
}
