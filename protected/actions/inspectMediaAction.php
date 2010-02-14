<?php

/**
 * showFavoriteAction 
 * 
 * @uses CAction
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class InspectMediaAction extends CAction
{

  /**
   * view 
   * 
   * @var string view to be rendered by the requesting controller
   */
  public $view = 'inspect';

  /**
   * notFound
   * 
   * @var string Sent as 500 error code on model not found
   */
  public $notFound = 'The requested model could not be found.';

  /**
   * modelVar 
   * 
   * @var string name of variable passed into view representing the loaded model
   */
  public $modelVar = 'model';

  /**
   * run 
   * 
   * @return void
   * @throws CException on view not set
   * @throws CHttpException 500 on requested model not found
   */
  public function run()
  {
    if(empty($this->view))
      throw new CException('view attribute must be set in '.__CLASS__);
    $this->attachBehavior('loadModel', array('class'=>'loadControllerARModelBehavior'));
    $model=$this->asa('loadModel')->loadModel();
    if(!$model)
      throw new CHttpException(500, $this->notFound);
    $this->getController()->render($this->view, array($this->modelVar=>$model));
  }
}

