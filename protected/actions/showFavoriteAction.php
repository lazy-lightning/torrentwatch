<?php

/**
 * showFavoriteAction 
 * 
 * @uses CAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class showFavoriteAction extends CAction
{
  /**
   * run 
   * 
   * @return void
   */
  public function run()
  {
    $this->attachBehavior('loadModel', array('class'=>'loadControllerARModelBehavior'));
    $model=$this->asa('loadModel')->loadModel();
    if(!$model)
      throw new CHttpException(500, 'The requested favorite could not be found.');
    Yii::app()->getController()->render('show', array(
          'model'=>$model,
          'feedsListData'=>feed::getCHtmlListData(),
          'genresListData'=>genre::getCHtmlListData(),
          'qualitysListData'=>quality::getCHtmlListData(),
    ));
  }
}

