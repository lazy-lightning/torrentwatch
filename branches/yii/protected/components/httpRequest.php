<?php

/**
 * httpRequest 
 * extend CHttpRequest so that when an ajax requst causes a redirect the
 * redirected request will still return true from getIsAjaxRequest()
 * as 
 * @uses CHttpRequest
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class httpRequest extends CHttpRequest
{
  /**
   * ajaxRedirectKey 
   * key used in relation to CWebUser flash variables
   * 
   * @var string
   */
  private $ajaxRedirectKey = 'httpRequest.isAjaxRedirect';

  /**
   * @return boolean
   */
  public function getIsAjaxRequest() {
    if(true === Yii::app()->getUser()->getFlash($this->ajaxRedirectKey))
      return true;
    else
      return parent::getIsAjaxRequest();
  }

  /**
   * redirect 
   * 
   * @param string $url 
   * @param boolean $terminate 
   * @param int $statusCode 
   * @return void
   */
  public function redirect($url, $terminate = true, $statusCode = 302)
  {
    if($this->getIsAjaxRequest())
      Yii::app()->getUser()->setFlash($this->ajaxRedirectKey, true);
    parent::redirect($url, $terminate, $statusCode);
  }

  /**
   * setAjaxRedirectKey 
   *
   * @param string $value key to use
   * @return mixed returns false if {@link getIsInitialized}, otherwise false
   */
  public function setAjaxRedirectKey($value)
  {
    return $this->getIsInitialized() ? false : $this->ajaxRedirectKey = $value;
  }
}
