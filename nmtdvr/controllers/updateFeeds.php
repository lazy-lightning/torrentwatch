<?php


class updateFeedsController extends Controller {

  var $tw;

  function __construct() {
    parent::__construct();

    // Get our model
    $this->tw = $this->_newModel('TorrentWatch');
  }

  function index() {
    // This function is a no-op
    // because we have a default view
    $this->tw->feeds->update();
    $this->success = True;
    return;
  }

}

