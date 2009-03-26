<?php

require_once SYSPATH.'Template.php';

class ajaxController extends templateController {

  var $tw;

  public $template = 'index_responce';

  function __construct() {
    parent::__construct();

    $this->tw = $this->_newModel('TorrentWatch');

    $this->template->set(array(
          'tw'      => $this->tw,
          'baseuri' => SimpleMvc::$base_uri,
    ));

  }

  function __call($func, $args) {
    SimpleMvc::log("Invalid method called: $func ( $args )");
  }

  function index() {
    // This function is a no-op
    // because we have a default view
    $this->success = True;
  }

  function addFeed() {
    if(empty($this->options['link']))
      return;

    $newFeed = new rss(array('url' => $this->options['link']));
    $this->success = $this->tw->feeds->add($newFeed);

    if($this->success === False) {
      SimpleMvc::log('Failure adding new feed');
      return;
    }

    SimpleMvc::log('running first update on new feed');
    $newFeed->updateItems();
  }

  function addFavorite() {
    if(empty($this->options['filter']) || empty($this->options['name']))
      return;

    if($this->options['name'] == 'New Favorite')
      $this->options['name'] = $this->options['filter'];

    SimpleMvc::log('Creating new favorite: '.$this->options['name']);

    $this->success = $this->tw->favorites->add(new tvFavorite($this->options));
  }

  function clearCache($cmds) {
    if(count($cmds) !== 1)
      return;

    $this->success = True;
    switch($cmds[0]) {
      case 'feeds':
        $this->tw->feeds->resetFeedItems();
        break;
      case 'history':
        $this->tw->history->emptyArray();
        break;
      case 'all':
        $this->tw->history->emptyArray();
        $this->tw->feeds->resetFeedItems();
        break;
      default:
        $this->success = False;
        break;
    }
  }

  function delFavorite($cmds) {
    if($count($cmds) !== 1 || !is_numeric($cmds[0]))
      return;

    $this->success = $this->tw->favorites->del($cmds[0]);
  }

  function deleteFeed($cmds) {
    if(count($cmds) != 1 || !is_numeric($cmds[0]))
      return;

    $this->success = $this->tw->feeds->del($cmds[0]);
  }

  function dlTorrent($cmds) {
    if(count($cmds) != 2) {
      return;
    }
    $this->template->set_filename = 'dlTorrent_responce';

    $feed = $this->tw->feeds->get($cmds[0]);
    $feedItem = @$feed->getFeedItem($cmds[1]);

    if(empty($feedItem)) {
      SimpleMvc::log('No matching feedItem');
      return;
    } 

    if(!$this->tw->downloadFeedItem($cmds[0], $feedItem)) {
      SimpleMvc::log('Manual Download Failed');
      return;
    }

    // This shouldnt be here, should be in model somewhere, somehow
    $this->tw->history->add($feedItem, $feed);
    $feedItem->update(array('status' => 'manualDownload'));

    $this->success = True;
  }

  function firstRun() {
    $this->setGlobals();
    if(isset($this->options['link'])) {
      $this->options['idx'] = 'New Feed';
      $this->updateItems();
    }
    $this->success = True;
  }


  function matchTitle($cmds) {
    if(count($cmds) != 2 || !is_numeric($cmds[0]) || !is_numeric($cmds[1])) {
      SimpleMvc::log('Invalid options passed');
      return;
    }

    $feedItem = @$this->tw->feeds->get($cmds[0])->getFeedItem($cmds[1]);

    if(empty($feedItem)) {
      SimpleMvc::log('no matching feedItem');
      return;
    }

    $title = empty($feedItem->shortTitle) ? $feedItem->title : $feedItem->shortTitle;
    $fav = new favorite(array('name' => $title,
                              'filter' => $title,
                              'feed' => $cmds[0],
                              'quality' => empty($feedItem->quality) ? '' : $feedItem->quality));

    $success = $this->tw->favorites->add($fav);
  }

  function setGlobals() {
    $this->tw->config->update($this->options);
    $this->success = True;
  }

  function updateFavorite($cmds) {
    // one option, either a number or empty
    $c = count($cmds);
    if($c === 0) 
      return $this->addFavorite();
    elseif($c > 1 || !is_numeric($cmds[0]))
      return;

    $fav = $this->tw->favorites->get($cmds[0]);
    if(!$fav) {
      SimpleMvc::Log('Not found '.$cmds[0]);
      return;
    }
    $fav->update($this->options);
    $this->success = True;
  }
}

