<?php

class updateFavoriteAction extends CAction
{
  protected $response;

  // When set to true will create a favorite rather than update
  public $create = False;

  // Updates the model from POST data
  protected function updateModel($model, $class = null)
  {
    if($class === null)
      $class = get_class($model);
    if(isset($_POST['quality_id']))
      $model->qualityIds = $_POST['quality_id'];
    $model->attributes = $_POST[$class];
    try {
      $transaction = $model->dbConnection->beginTransaction();
      $model->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    $this->response[$class] = $model;
    $this->response['showFavorite'] = "#".$class.'s-'.$model->id;
    $this->response['showTab'] = "#".$class."s";
  }

  // To be called at the end of implementing classes run() function
  public function run()
  {
    $this->attachBehavior('loadModel', 'loadControllerARModelBehavior');
    $class = null;
    if($this->create === FALSE)
    {
      // Update command
      $this->response['dialog']['header'] = 'Update Favorite';
      $model = $this->asa('loadModel')->loadModel();
    }
    else
    {
      $this->response['dialog']['header'] = 'Create Favorite';
      $class = $this->asa('loadModel')->getControllerARClass();
      $model = new $class;
      if(!isset($_POST[$class]))
      {
        // No item data, show creation form
        Yii::app()->getController()->render('show', array(
            'model'=>$model,
            'feedsListData'=>feed::getCHtmlListData(),
            'genresListData'=>genre::getCHtmlListData(),
            'qualitysListData'=>quality::getCHtmlListData(),
        ));
        return;
      }
    }

    $this->updateModel($model, $class);
    $app = Yii::app();
    $app->getUser()->setFlash('response', $this->response);
    $app->getController()->redirect(array('/ajax/fullResponse', 'response'=>1));
  }
}
