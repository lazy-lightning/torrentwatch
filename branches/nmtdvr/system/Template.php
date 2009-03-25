<?php defined('SYSPATH') or die('No Direct access allowed.');

abstract class templateController extends Controller {

  public $template = 'template';
  public $auto_render = True;

  public function __construct() {
    parent::__construct();

    $this->template = new View($this->template);

    if($this->auto_render === True) {
      Event::add('system.post_controller', array($this, '_render'));
    }
  }

  public function _render() {
    if($this->auto_render === True) { 
      return $this->template->render(True);
    }
  }
}

