<?php

require_once SYSPATH.'Template.php';
require_once SYSPATH.'Pagination.php';

class nmtController extends templateController {

  var $tw;
  var $itemsPerPage;
  var $imageRoot = 'file:///opt/sybhttpd/localhost.images/';

  var $contentLinksSpliced = False;
  var $contentLinks = array();

  function __construct() {
    // Detect resolution
    if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
      $vop_mode = preg_match('/^vop_mode=(.*)$/', file_get_contents('/tmp/setting.txt'));
      if(substr($vop_mode, 3) == '480') {
        //SD
        define('RESOLUTION', 'Sd');
        $this->itemsPerPage = 9;
        $this->imageRoot .= 'sd/';
      } else {
        //HD
        define('RESOLUTION', 'Sd'); // no hd templates yet
        $this->itemsPerPage = 11;
        $this->imageRoot .= 'hd/';
      }
    } else {
      //PC Browser
      define('RESOLUTION', 'Sd');
      $this->itemsPerPage = 11;
      $this->imageRoot = 'http://localhost.images:8883/hd/';
    }

    Event::add('system.post_controller', array($this, '_paginate'));
    $this->_addModel('TorrentWatch');

    // Set the template and initialize it
    $this->template = 'template'.RESOLUTION;
    parent::__construct();

    $this->template->set(array
    (
      'title'            => 'NMT DVR',
      'background'       => $this->imageRoot.'bg.jpg',
      'onloadset'        => '',
      'paginationTop'    => new Pagination(array(
                              'style'          => 'top'.RESOLUTION,
                              'query_string'   => 'page',
                              'items_per_page' => $this->itemsPerPage,
                              'auto_hide'      => False)),
      'paginationBottom' => 'bottom'.RESOLUTION,
      'tvidRed'          => $this->self_uri.'findNewShows',
      'tvidGreen'        => 'http://localhost.drives:8883/HARD_DISK/Video/?filter=3',
      'tvidYellow'       => $this->self_uri,
      'tvidBlue'         => $this->self_uri.'showConfig',
    ));

    // Cant be set above, pagination doesn't accept arbitrary
    // items in its constructor
    $this->template->paginationTop->item_type = 'items';

    $this->template->sidebar = new View('sidebar'.RESOLUTION, array
    (
      'home'       => $this->self_uri,
      'image'      => 'side_default_server',
      'heading'    => 'NMTDVR',
      'onkeyright' => '01',
      'links'      => array
      (
        'Main'      => $this->self_uri,
        'Favorites' => $this->self_uri.'showFavorites',
        'Config'    => $this->self_uri.'showConfig',
      ),
    ));

    $this->template->content = new View('listItem'.RESOLUTION, array
    (
      'onkeyleft'  => reset($this->template->sidebar->links), // first element of sidebar links
      'links'      => array(),
     )
    );

    // Distribute the imageRoot
    $this->template->content->imageRoot = $this->template->paginationTop->imageRoot =
        $this->template->sidebar->imageRoot = $this->template->imageRoot = $this->imageRoot;
  }

  public function _paginate() {
    $pagination = $this->template->paginationTop;
    if(!empty($this->contentLinks) OR isset($this->contentTitleKey)) {
      if($this->contentLinksSpliced !== False) {
        $pagination->initialize(array('total_items' => $this->contentLinksSpliced));
      } else {
        $c = count($this->contentLinks);
        $pagination->initialize(array('total_items' => $c));

        if($pagination->total_pages > 1) {
          $this->contentLinks = array_slice($this->contentLinks, $pagination->current_first_item, $this->itemsPerPage);
        }
      }

      $this->template->content->links = $this->contentLinks;
    }

    $this->template->paginationBottom = $pagination->render($this->template->paginationBottom);
  }

  public function index() {
    $this->template->title = 'NMT DVR Main Index';
    $this->_addContentLink('01', 'Find new Favorites',        'findNewFavorites/',    'list_video');
    $this->_addContentLink('02', 'Whats Downloaded Recently', 'showHistory/',         'list_video');
    $this->_addContentLink('03', 'Whats Downloading Now',     'showClientDownloads/', 'list_video');
    $this->_addContentLink('04', 'View your Favorites',       'showFavorite/',        'list_video');
    $this->_addContentLink('05', 'Current Feed Contents',     'showFeed/',            'list_video');
    $this->success = True;
  }

  function showFavorite() {
    $cmds = SimpleMvc::$arguments;
    if(empty(SimpleMvc::$arguments)) {
      return $this->showFavorites();
    }

    $fav = favorites::getInstance()->get(SimpleMvc::$arguments[0]);
    if(empty($fav)) {
      Event::run('system.404');
    }

    $this->template->paginationTop->initialize(array('total_items' => 1));
    $this->template->content->set_filename('favorite'.RESOLUTION);
    $this->template->content->fav = $fav;
    $this->success = True;
  }

  function showFavorites() {
    $this->template->title = 'NMT DVR Favorites';
    
    $this->_setupContentLink('name', 'showFavorite');
    $this->_addContentLinks(favorites::getInstance()->get());

    $this->template->paginationTop->item_type = 'Favorites';
  }

  function showFeed($args, $feed = NULL) {
    if(!$feed) {
      if(!$args) {
        $args = SimpleMvc::$arguments;
      }

      if(empty($args[0])) {
        return $this->showFeeds();
      }

      $feed = feeds::getInstance()->getItemByKey('title', urldecode($args[0]));
    }

    if(!$feed) {
      Event::run('system.404');
    }
    $this->_setupContentLink('title', "showFeedItem/".$feed->id);
    $this->_addContentLinks($feed->getFeedItem());

    $this->template->paginationTop->item_type = 'Feed Items';
  }

  function showFeeds() {
    $this->template->title = 'NMT DVR Feeds';

    $this->_setupContentLink('title', 'showFeed');
    $feeds = feeds::getInstance()->get();

    if(count($feeds) == 1) {
      return $this->showFeed(NULL, end($feeds));
    }

    $this->_addContentLinks(feeds::getInstance()->get());
    
    $this->template->paginationTop->item_type = 'Feeds';
  }

  function showFeedItem() {
    if(!isset(SimpleMvc::$arguments[1])) {
      Event::run('system.404');
    }
    $feed = feeds::getInstance()->get(SimpleMvc::$arguments[0]);
    if(empty($feed)) {
      Event::run('system.404');
    }
    $feedItem = $feed->getFeedItem(SimpleMvc::$arguments[1]);
    if(empty($feedItem)) {
      Event::run('system.404');
    }

    $this->template->title = 'NMT DVR Feed Item';
    $this->template->paginationTop->initialize(array('total_items' => 1));
    $this->template->content->set_filename('feedItem'.RESOLUTION);
    $this->template->content->feedItem = $feedItem;
  }

  function showHistory() {
    $this->template->title = 'NMT DVR History';
    
    $this->_setupContentLink('title', 'showHistoryItem');
    $this->_addContentLinks(history::getInstance()->get());

    $this->template->paginationTop->item_type = 'Downloads';
  }

  function showHistoryItem() {
    if(empty(SimpleMvc::$arguments)) {
      Event::run('system.404');
    }
    $historyItem = history::getInstance()->getItemByKey('title', urldecode(SimpleMvc::$arguments));
    if(empty($historyItem)) {
      Event::run('system.404');
    }

    $this->template->title = 'NMT DVR History Item';
    $this->template->paginationTop->initialize(array('total_items' => 1));
    $this->template->content->set_filename('historyItem'.RESOLUTION);
    $this->template->content->historyItem = $historyItem;
  }

  public function _setupContentLink($titleKey, $href, $icon = 'list_video') {
    $this->contentTitleKey = $titleKey;
    $this->contentHref = $href;
    $this->contentIcon = $icon;
  }

  public function _addContentLinks($items, $start_item = 1) {
    $key = $this->contentTitleKey;
    foreach($items as $item) {
      $this->_addContentLink(sprintf("%02d", $start_item++), $item->$key, 
                             $this->self_uri.$this->contentHref.'/'.$item->id, $this->contentIcon);
    }
  }

  public function _addContentLink($index, $title, $href, $icon = '', $alt = '') {
    if(empty($this->contentLinks)) {
      $this->template->sidebar->onkeyright = $index;
    }
    $this->contentLinks[$title] = array('index' => $index, 'href' => $href, 'icon' => $icon, 'alt' => $alt);
  }

}

