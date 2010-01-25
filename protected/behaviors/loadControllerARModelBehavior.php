<?php

// This action can be attached to any controller which has a relation to
// feedItem. The corresponding runAction class will be called with an AR
// feedItem.
class loadControllerARModelBehavior extends CBehavior
{

  /**
   * Lower cases the first letter of a string
   */
  public function lcFirst($string)
  {
    $string[0] = strtolower($string[0]);
    return $string;
  }

  public function getControllerARClass($controller = null)
  {
    if($controller === null)
      $controller = Yii::app()->getController();
    // Get the controller class name strip controller from the end and lower case the first letter
    return substr($this->lcFirst(get_class($controller)), 0, -strlen('Controller'));
  }

  /**
   * Returns the AR model specified in the http request from the current controller
   */
  public function loadModel($id = null)
  {
    $controller = Yii::app()->getController();
    $function = 'load'.$this->getControllerARClass($controller);
    // $function should be something like: loadfeedItem
    return $controller->$function($id);
  }
}

