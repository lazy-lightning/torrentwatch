<?php

require_once SYSPATH.'Template.php';
require_once MODPATH.'TVDB.php';

class inspectorController extends templateController {

  var $template = 'ajax_inspector';

  function __construct() {
    parent::__construct();

    $this->_addModel('TorrentWatch');
  }

  function index() {
    $this->template->error = "Type in a TV Show Name";
    return;
  }

  function ajax() {
    if(!isset($_GET['title'])) {
      $this->index();
      return False;
    }

    $this->template->title = $title = $_GET['title'];;

    // borow the guess function from the feedItem
    $guess = feedItem::guessTvData($title, TRUE);

    if($guess === False) {
      $this->template->error = "Couldn't guess Tv Data for {$title}";
      return False;
    }
    $this->template->guess = $guess;

    $tvShows = TV_Shows::search($guess['shortTitle']);

    // TVDB doesnt do it for us, so make a second search with and converted to &
    if(!$tvShows && stristr($guess['shortTitle'], 'and') !== False) {
    	$tvShows = TV_Shows::search(strtr(strtolower($guess['shortTitle']), array("and" => '&')));
    }

    if(!$tvShows) {
      $this->template->error = "No Records Found.";
      return False;
    }

    $tvShow = $tvShows[0];
    $this->template->tvShow = $tvShow;

    // Search for an episode if found as well
    if($guess['season'] > 0) {
      $this->template->tvEpisode = $tvShow->getEpisode($guess['season'], $guess['episode']);
      $this->template->episode = $guess['season'].'x'.$guess['episode'];
    }
  }
}

?>
