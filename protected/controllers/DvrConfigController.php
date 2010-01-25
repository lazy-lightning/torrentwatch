<?php

class DvrConfigController extends BaseController
{

  /**
   * @var string specifies the default action to be 'update'.
   */
  public $defaultAction='update';

  /**
   * @var array response data to be passed to the view
   */
  protected $response = array();

  /**
   * @return array action filters
   */
  public function filters()
  {
    return array(
      'accessControl', // perform access control for CRUD operations
    );
  }

  /**
   * Specifies the access control rules.
   * This method is used by the 'accessControl' filter.
   * @return array access control rules
   */
  public function accessRules()
  {
    return array(
      array('allow', // allow authenticated user
        'actions'=>array('update', 'wizard'),
        'users'=>array('@'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  public function actionUpdate()
  {
    $this->response = array('dialog'=>array('header'=>'Save Configuration'));

    $config = Yii::app()->dvrConfig;
    Yii::log(print_r($_POST, TRUE));
    if(isset($_POST['category']) && $config->contains($_POST['category']))
    {
      // empty dvrConfig allows still setting config client
      if(isset($_POST['dvrConfigCategory']))
        $config->$_POST['category']->attributes = $_POST['dvrConfigCategory'];

      // if this is a client category, also set the main config to use this client
      if(isset($_POST['type']) && in_array($_POST['type'], array('nzbClient', 'torClient')) &&
         substr($_POST['category'], 0, 6) === 'client')
        // $_POST['type'] is now guaranteed to be one of two values
        $config->$_POST['type'] = $_POST['category'];
    }
    elseif(isset($_POST['dvrConfig']))
    {
      $config->attributes = $_POST['dvrConfig'];
    }
    try {
      $transaction = Yii::app()->db->beginTransaction();
      if($config->save()) 
      {
        $this->response['dialog']['content'] = 'Configuration successfully saved';
      }
      else
      {
        $this->response['dialog']['error'] = True;
        $this->response['dialog']['content'] = 'There was an error saving the configuration';
        $this->response['dvrConfig'] = $config;
        $this->response['showDialog'] = '#configuration';
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    $this->redirectFullResponse();
  }

  public function actionWizard()
  {
    $this->response = array('dialog'=>array('header'=>'Initial Configuration', 'content'=>''));

    try {
      $transaction = $feed->dbConnection->beginTransaction();
      if(isset($_POST['dvrConfig']))
      {
        $config = Yii::app()->dvrConfig;
        $config->attributes = $_POST['dvrConfig'];
        $this->response['dialog']['content'] .= ($config->save() ? 'Saved configuration' : 'Failed saving configuration').'<br>';
      }

      if(isset($_POST['feed']))
      {
        $feeds = array();
        foreach(array('torUrl'=>feedItem::TYPE_TORRENT, 'nzbUrl'=>feedItem::TYPE_NZB) as $key => $type)
        {
          if(isset($_POST['feed'][$key]))
          {
            $feed = new feed;
            $feed->url = $_POST['feed'][$key];
            $feed->downloadType = $type;
            // FIXME: this will block the database while the update is being requested over the net
            $this->response['dialog']['content'] .= ($feed->save() ? "Saved feed {$feed->title}" : "Failed saving feed {$feed->url}").'<br>';
          }
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }

    if(empty($this->response['dialog']['content']))
      $this->response['dialog']['content'] = 'No valid attributes passed to wizard';
    $this->redirectFullResponse();
  }

  protected function redirectFullResponse()
  {
    Yii::app()->getUser()->setFlash('response', $this->response);
    $this->redirect(array('/ajax/fullResponse', 'response'=>1));
  }
}
