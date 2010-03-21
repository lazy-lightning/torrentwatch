<?php

abstract class BaseController extends CController {
  private $_resolution;
  public $imageRoot;

  /**
   * runActionWithFilters 
   * Override normal access control filtering so only check for is/isnot
   * guest, skips the whole filter chain code set for simplicity and to
   * reduce framework setup time(important on A100 line)
   * 
   * @param CAction $action 
   * @param array $filters 
   * @return void
   */
  public function runActionWithFilters($action,$filters)
  {
    $user = Yii::app()->getUser();
    $msg = ' page:: '.$_SERVER['QUERY_STRING'];
    $cat = 'application.components.BaseController';
    if($user->getIsGuest()) {
      Yii::log('Denying'.$msg, CLogger::LEVEL_INFO, $cat);
      $user->loginRequired();
    }
    else
    {
      Yii::log('Serving'.$msg, CLogger::LEVEL_INFO, $cat);
      $this->runAction($action);
    }
  }

  /**
   * Returns a copy of the $attributes array containing only the keys
   * specified in $whitelist
   * 
   * @param array $attributes the array to be filtered ( key => value )
   * @param array $whitelist list of valid keys ( key1, key2, ..., keyn )
   * @return array the filtered $attributes array
   */
  protected function whitelist($attributes, $whitelist)
  {
    $out = array();
    foreach($whitelist as $key)
    {
      if(isset($attributes[$key]))
        $out[$key] = $attributes[$key];
    }
    return $out;
  }

  /**
   * applyAttributes applies and saves attributes to a model inside
   * a database transaction
   * 
   * @param CActiveRecord $model the record to be updated
   * @param array $attributes the values to be applied to $model
   * @param mixed $whiteList optional list of keys to set.  Default false
   *              means set all keys in $attributes
   * @return boolean true when $model is successfully saved to database
   */
  protected function applyAttributes($model, $attributes, $whiteList = false)
  {
    if($whiteList)
      $attributes = $this->whitelist($attributes, $whiteList);
    $transaction = $model->getDbConnection()->beginTransaction();
    try {
      $model->attributes=$attributes;
      $success = $model->save();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    return $success;
  }

  /**
   * deleteModel deletes a record inside a database transaction
   * 
   * @param CActiveRecord $model 
   * @return boolean true on successfull delete
   */
  protected function deleteModel($model)
  {
    $transaction = $model->getDbConnection()->beginTransaction();
    try {
      $success = $model->delete();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    return $success;
  }

  /**
   * @return array list of items to be used as the side bar menu ( n => ( ... ) )
   */
  public function getMainMenuItems() {
    return array(
        array('name'=>'index', 'label'=>'NMTDVR Home', 'url'=>array('site/index')),
        array('name'=>'favorites', 'label'=>'Favorites', 'url'=>array('site/favorites')),
        array('name'=>'config', 'label'=>'Configuration', 'url'=>array('configuration/list')),
    );
  }

  /**
   * @return string the resolution(hd/sd) of the nmt requestin the page
   */
  public function getResolution() {
    return $this->_resolution;
  }

  /**
   * Point the imageRoot and layout to the appropriate pages for this request
   * @return none
   */
  public function init() {
    error_reporting(E_ALL|E_STRICT);
    $app = Yii::app();

    date_default_timezone_set($app->getComponent('dvrConfig')->timezone);
    $this->setupTheme();

    // Auto-login hack from localhost 
    if(isset($_SERVER['REMOTE_ADDR']) && $app->user->getIsGuest() && $_SERVER['REMOTE_ADDR'] === '127.0.0.1')
      $app->user->login(new LocalBrowserHackIdentity(), 3600*24*30);
  }

  /**
   * Prepares a set of links to be used by the NMT view
   */
  public function prepareListItems($in, $index = 1) {
    $out = array();
    $mWidth = $this->_resolution==='hd'?560:290;
    foreach($in as $item) {
      $item2 = array();
      $item2['icon'] =$this->imageRoot.(isset($item['icon'])?$item['icon']:'list_folder').'.png';
      $item2['index'] = $index++;
      $item2['label'] = "<marquee behavior='focus' width='$mWidth'>&nbsp;{$item['label']}</marquee>";
      $item2['name'] =  isset($item['name'])?$item['name']:strtok($item['label'], ' ');
      $item2['tvid'] = isset($item['tvid'])?$item['tvid']:$item2['index'];
      $item2['url'] = $item['url'];
      $out[] = $item2;
    }
    return $out;
  }

  protected function setupTheme()
  {
    $app = Yii::app();
    if($app->getRequest()->getIsAjaxRequest())
    {
      $theme = $app->dvrConfig->webuiTheme;
      $this->layout = 'ajax';
    }
    else if(isset($_SERVER['HTTP_USER_AGENT']) && 
        // verify its a Syabas client
        false !== stripos($_SERVER['HTTP_USER_AGENT'], 'Syabas') &&
        // verify its an SD resolution Syabas client
        false !== stripos($_SERVER['HTTP_USER_AGENT'], 'Res720x576'))
    {
      $theme = $app->dvrConfig->gayauiTheme.'_SD';
    }
    else
    {
      $theme = $app->dvrConfig->gayauiTheme.'_HD';
    }
    $app->setTheme($theme);
  }
}

