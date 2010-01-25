<?php

class updateFavoriteAction extends CAction
{
  // The response data
  protected $response;

  // If the save was successfull or not
  protected $success = False;

  // When set to true will create a favorite rather than update
  public $create = False;

  // Updates the model from POST data
  protected function updateModel($model, $attributes)
  {
    if(isset($_POST['quality_id']))
      $model->qualityIds = $_POST['quality_id'];
    $model->attributes = $attributes;
    try {
      $transaction = $model->dbConnection->beginTransaction();
      $this->success = $model->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    $class = get_class($model);
  }

  public function run()
  {
    $this->response = new actionResponseWidget;
    $this->attachBehavior('loadModel', 'loadControllerARModelBehavior');
    $class = null;
    if($this->create === FALSE)
    {
      // Update command
      $this->response->dialog['header'] = 'Update Favorite';
      $model = $this->asa('loadModel')->loadModel();
    }
    else
    {
      $this->response->dialog['header'] = 'Create Favorite';
      $class = $this->asa('loadModel')->getControllerARClass();
      $model = new $class;
    }

    if(isset($_GET[$class]))
      $this->updateModel($model, $_GET[$class]);

    $this->response->showDialog = '#favorites';
    $this->response->showTab = "#".$class."s";
    $this->response->showFavorite = "#".$class.'s-'.$model->id;
    Yii::app()->getController()->render('show', array(
        'response'=>$this->response->getContent(),
        'model'=>$model,
        'feedsListData'=>feed::getCHtmlListData(),
        'genresListData'=>genre::getCHtmlListData(),
        'qualitysListData'=>quality::getCHtmlListData(),
    ));
  }
}
