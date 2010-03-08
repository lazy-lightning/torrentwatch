<?php

/*
 * This action can be attached to any AR controller which has a relation to
 * feedItem. The corresponding runAction function will be called with an AR
 * feedItem.
 * 
 * @uses CAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
abstract class feedItemAction extends CAction
{
  /**
   * @var actionResponseWidget
   */
  protected $response;

  /**
   * Basically the new run() function, but given a $model to work with
   * @param feedItem $model 
   * @return void
   */
  abstract protected function runAction($model);

  /**
   * load the specified model, and its feedItem.  Then call runAction with that feedItem.
   * @return void
   * @throws CHttpException when no feedItem is found
   */
  public function run()
  {
    $this->attachBehavior('loadModel', 'loadControllerARModelBehavior');
    $model = $this->asa('loadModel')->loadModel();

    // get the first related feedItem if exists
    if(isset($model->getMetaData()->relations['feedItem'])) {
      $related = $model->getRelated('feedItem');
      $model = is_array($related) ? reset($related) : $related;
    }
    if(!($model instanceof feedItem))
      throw new CHttpException(500, 'The requested feedItem could not be found:'.get_class($model));

    $this->response = new actionResponseWidget;
    $this->runAction($model);
  }
}

