<?php

// This action can be attached to any AR controller which has a relation to
// feedItem. The corresponding runAction class will be called with an AR
// feedItem.
abstract class feedItemAction extends CAction
{
  protected $response;

  abstract protected function runAction($model);

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

