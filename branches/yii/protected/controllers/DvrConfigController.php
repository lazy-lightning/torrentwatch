<?php

class DvrConfigController extends BaseController
{

  /**
   * @var string specifies the default action to be 'globals'.
   */
  public $defaultAction='globals';

  /**
   * @var array response data to be passed to the view to construct an actionResponseWidget
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
        'actions'=>array('update', 'welcome', 'wizardClient', 'wizardFeed',
                         'globals', 'nzbClient', 'torClient', 'feeds',
                         'wizardSettings'),
        'users'=>array('@'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  public function actionGlobals()
  {
    $config = Yii::app()->dvrConfig;
    $success = false;
    if(isset($_POST['dvrConfig']))
    {
      $config->attributes = $_POST['dvrConfig'];
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $success = $config->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
    $this->render('global', array('config'=>$config, 'successfullSave' => $success));
  }

  public function actionNzbClient()
  {
    $config = Yii::app()->dvrConfig;
    if(isset($_GET['id']) && Yii::app()->request->isPostRequest &&
       $config->contains($_GET['id']))
    {
      $config->nzbClient = $_GET['id'];
      if(isset($_POST['dvrConfigCategory']))
        $config->{$_GET['id']}->attributes = $_POST['dvrConfigCategory'];
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $config->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
    $this->render('nzbClient', array(
          'availClients'=>Yii::app()->dlManager->availClients[feedItem::TYPE_NZB],
          'config'=>$config
    ));
  }

  public function actionTorClient()
  {
    $config = Yii::app()->dvrConfig;
    if(isset($_GET['id']) && Yii::app()->request->isPostRequest &&
       $config->contains($_GET['id']))
    {
      // $_GET['id'] is considered safe as it is verified to be a member of
      // $config 
      $config->torClient = $_GET['id'];
      if(isset($_POST['dvrConfigCategory']))
        $config->{$_GET['id']}->attributes = $_POST['dvrConfigCategory'];
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $config->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
    $this->render('torClient', array(
          'availClients'=>Yii::app()->dlManager->availClients[feedItem::TYPE_TORRENT],
          'config'=>$config
    ));
  }

  public function actionWelcome()
  {
    $this->render('welcome');
  }

  public function actionWizardClient()
  {
    $app = Yii::app();
    $config = $app->dvrConfig;
    $success = false;
    if($app->request->isPostRequest && isset($_POST['dvrConfig']))
    {
      if(isset($_POST['dvrConfig']['torClient']))
        $config->torClient = $_POST['dvrConfig']['torClient'];
      if(isset($_POST['dvrConfig']['nzbClient']))
        $config->nzbClient = $_POST['dvrConfig']['nzbClient'];
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $success = $config->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
    if($success)
      $this->redirect(array('wizardFeed'));
    else
    {
      $this->render('wizardClient', array(
            'availClients'=>$app->dlManager->getAvailClients(),
            'config'=>$config, 
       ));
    }
  }

  public function actionWizardFeed()
  {
    $torFeed = $nzbFeed = null;
    $app = Yii::app();
    if($app->request->isPostRequest && isset($_POST['feed']))
    {
      $success = true;
      $transaction = Yii::app()->db->beginTransaction();
      try {
        if(isset($_POST['feed']['nzbUrl']))
        {
          $nzbFeed = new feed;
          $nzbFeed->setAttributes(array(
                'url'=>$_POST['feed']['nzbUrl'],
                'downloadType'=>feedItem::TYPE_NZB,
          ));
          $success = $nzbFeed->save();
        }
        if(isset($_POST['feed']['torUrl']))
        {
          $torFeed = new feed;
          $torFeed->setAttributes(array(
                'url'=>$_POST['feed']['torUrl'],
                'downloadType'=>feedItem::TYPE_TORRENT,
          ));
          $success = $torFeed->save() && $success;
        }
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      if($success)
        return $this->redirect(array('wizardSettings'));
    }

    $this->render('wizardFeed', array(
        'nzbFeed'=>$nzbFeed,
        'torFeed'=>$torFeed,
    ));
  }

  public function actionWizardSettings()
  {
    $app = Yii::app();
    $config = $app->dvrConfig;
    if($app->request->isPostRequest && isset($_POST['dvrConfig']))
    {
      $config->setAttributes($_POST['dvrConfig']);
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $success = $config->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      if($success)
        return $this->render('wizardFinished');
    }

    $this->render('wizardSettings', array('config'=>$config));
  }
}
