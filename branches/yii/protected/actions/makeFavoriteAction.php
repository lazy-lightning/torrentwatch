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
    $this->response->showTab = "#{$type}_tab a";
    $this->response->showFavorite = "#{$type}-";
    $this->response->append = array(
        array(
          'selector'=>"#{$type}-",
          'parent'=>"#{$type}_container",
    ));
    Yii::app()->getController()->render("/$type/show", array(
          'model' => $fav,
          'success' => false,
          'create' => false,
          'response' => $this->response->getContent(),
          'feedsListData' => feed::getCHtmlListData(),
          'genresListData' => genre::getCHtmlListData(),
          'qualitysListData' => quality::getCHtmlListData(),
    ));
  }
}
