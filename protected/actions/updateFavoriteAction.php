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
  }

  public function run()
  {
    $this->response = new actionResponseWidget;
    $this->attachBehavior('loadModel', 'loadControllerARModelBehavior');
    $class = null;
    if($this->create === FALSE)
    {
      // Update command
      $model = $this->asa('loadModel')->loadModel();
      $class = get_class($model);
    }
    else
    {
      // Create command
      $class = $this->asa('loadModel')->getControllerARClass();
      $model = new $class;
    }

    if(isset($_POST[$class]))
    {
      $this->updateModel($model, $_POST[$class]);
      if($this->create && $this->success)
      {
        $this->response->append = array(
            array(
              'parent'=>"#{$class}List",
              'selector'=>"#{$class}-{$model->id} li",
            ),
            array(
              'parent'=>"#{$class}_container",
              'selector'=>"#{$class}-{$model->id}",
            ),
        );
      }
    }

    if($this->success) 
      $this->response->resetFeedItems = true;

    Yii::app()->getController()->render('show', array(
        'response'=>$this->response->getContent(),
        'model'=>$model,
        'addLi'=>($this->create && $this->success),
        'feedsListData'=>feed::getCHtmlListData(),
        'genresListData'=>genre::getCHtmlListData(),
        'qualitysListData'=>quality::getCHtmlListData(),
    ));
  }
}
