<?php

/**
 * This action can be attached to any controller which has a relation to
 * feedItem. The corresponding runAction class will be called with an AR
 * feedItem.
 * 
 * @uses CBehavior
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class loadControllerARModelBehavior extends CBehavior
{

  /**
   * Lower cases the first letter of a string
   * @param string $string
   * @return string
   */
  public function lcFirst($string)
  {
    $string[0] = strtolower($string[0]);
    return $string;
  }

  /**
   * getControllerARClass 
   * 
   * @param CController $controller 
   * @return string
   */
  public function getControllerARClass($controller = null)
  {
    if($controller === null)
      $controller = Yii::app()->getController();
    // Get the controller class name strip controller from the end and lower case the first letter
    return substr($this->lcFirst(get_class($controller)), 0, -strlen('Controller'));
  }

  /**
   * Returns the AR model specified in the http request from the current controller
   * @param int $id the id of the model to find
   * @return CActiveRecord
   */
  public function loadModel($id = null)
  {
    $controller = Yii::app()->getController();
    $function = 'load'.$this->getControllerARClass($controller);
    // $function should be something like: loadfeedItem
    return $controller->$function($id);
  }
}

