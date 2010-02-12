<?php

/**
 * updateFavoriteAction 
 * 
 * @uses CAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class updateFavoriteAction extends CAction
{
  /**
   * The response data
   * 
   * @var actionResponseWidget
   */
  protected $response;

  /**
   * @var boolean true when save is successfull
   */
  protected $success = False;

  /**
   * @var boolean When true will create a favorite rather than update
   */
  public $create = False;

  /**
   * Updates the model from POST data
   * 
   * @param mixed $model 
   * @param mixed $attributes 
   * @return void
   */
  protected function updateModel($model, $attributes)
  {
    if(isset($_POST['quality_id']))
      $model->qualityIds = $_POST['quality_id'];
    $model->attributes = $attributes;
    $transaction = $model->dbConnection->beginTransaction();
    try {
      $this->success = $model->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
  }

  /**
   * run 
   * 
   * @return void
   */
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
