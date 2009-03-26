<?php


class updateFeedsController extends Controller {

  var $tw;

  function __construct() {
    parent::__construct();

    // Get our model
    $this->tw = $this->_newModel('TorrentWatch');
    Event::add('system.post_controller', array($this, '_render'));
  }

  function _render() {
    echo date('Y M D h:m a')."\n".($this->success ? 'Success' : 'Failure');
  }

  function index() {
    $this->tw->feeds->update();
    $this->success = True;
    return;
  }

}

