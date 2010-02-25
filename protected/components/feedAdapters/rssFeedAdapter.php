<?php

require_once('simplepie.inc');
require_once('feedAdapter_File.php');

class rssFeedAdapter implements IFeedAdapter {
  // the feed active record class the adapter is to work with
  private $feedModel;
  protected $simplePie;

  public function __construct($feed, $cacheLocation = null, $simplePie = null) {
    $this->feedModel = $feed;
    if(is_string($simplePie)) {
      try {
        $simplePie = new $simplePie($feed->url, $cacheLocation);
      } catch (Exception $e) {
        $simplePie = null;
      }
    }
    if($simplePie === null)
      $simplePie = new SimplePie($feed->url, $cacheLocation);

    if(defined('UNIT_TEST'))
      $simplePie->cache = false;

    $simplePie->set_feed_url($feed->url);

    // feedAdapter_File implements the :COOKIE: portion of url's
    $simplePie->set_item_class('feedAdapter_Item');
    $simplePie->set_file_class('feedAdapter_File');

    // Allows for broken feeds that dont encode & as &amp;
    $simplePie->set_parser_class('feedAdapter_Parser');

    $this->simplePie = $simplePie;
  }

  public function __destruct()
  {
    if($this->simplePie)
    {
      $this->simplePie->__destruct();
      unset($this->simplePie);
      $this->simplePie = null;
    }
  }

  public function init() {
    // time limit 0 so we dont timeout doing long network operation
    set_time_limit(0);

    // get feed and populate object
    $this->simplePie->init();

    if($this->simplePie->error()) {
      $this->feedModel->status = feed::STATUS_ERROR;
      Yii::log("Error loading feed {$this->feedModel->title}: ".$this->simplePie->error());
      if(empty($this->feedModel->title)) {
        // new record
        $this->feedModel->title = 'Error initializing feed';
      }
      return False;
    }

    $this->feedModel->status = feed::STATUS_OK;
    Yii::log($this->simplePie->get_title());
    $this->feedModel->title = $this->simplePie->get_title();
    $this->feedModel->description = $this->simplePie->get_description();
    return true;
  }

  protected function addFeedItem($item, $hash, $factory)
  {
    $title = $item->get_title();
    $transaction = Yii::app()->db->beginTransaction();
    try {
      $factory->feedItemByAttributes(array(
            'hash'        => $hash,
            'feed_id'     => $this->feedModel->id,
            'downloadType'=> $this->feedModel->downloadType,
            'imdbId'      => $item->get_imdbId(),
            'title'       => $title,
            'url'         => $item->get_link(),
            'description' => $item->get_description(),
            'pubDate'     => $item->get_date('U'),
      ));
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      Yii::log('feedItem failed to save: '.$title.' : '.$e->getMessage(), CLogger::LEVEL_ERROR);
    }
  }

  public function checkFeedItems($factory = null)
  {
    if($factory === null)
      $factory = Yii::app()->modelFactory;
    foreach($this->simplePie->get_items() as $item) 
    {
      $hash = md5($item->get_id());
      if(false === feedItem::model()->exists('hash=:hash', array(':hash'=>$hash))) 
        $this->addFeedItem($item, $hash, $factory);
    }
  }
}

class feedAdapter_Item extends SimplePie_Item {
  // attempt to detect imdb from description, can be overridden for a better match
  function get_imdbId() {
    $desc = $this->get_description();
    if(preg_match('/imdb.com\/title\/tt(\d+)/i', $desc, $regs))
      return (int) $regs[1];
    else
      return 0;
  }

  function get_link() {
    // Prefer a link from enclosure over link from item
    // TDOO: only if application/x-bittorrent 
    //            or application/whatever nzb is  ?
    $link = '';
    $enclosure = $this->get_enclosure();
    if($enclosure)
      $link = $enclosure->get_link();
    if(empty($link))
      $link = parent::get_link();
    return $link;
  }

  function get_title() {
    return html_entity_decode(parent::get_title());
  }

  function get_description() {
    return html_entity_decode(parent::get_description());
  }
}

// Allows for broken feeds that dont encode & as &amp;
class feedAdapter_Parser extends SimplePie_Parser
{
  function parse(&$data, $encoding)
  {
    $data = str_replace(' & ', '&#38;', $data);
    return parent::parse($data, $encoding);
  }
}
