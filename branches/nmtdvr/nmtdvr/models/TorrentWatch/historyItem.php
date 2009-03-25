<?php
class historyItem extends cacheItem {
  public $title;
  public $shortTitle;
  public $feedId;
  public $feedTitle;
  public $date;
  public $favId;
  public $favName;
  public $season;
  public $episode;

  public function __construct($options) {
    parent::__construct($options);
    $this->date = time();
  }

  public function __sleep() {
    return array_merge(parent::__sleep(), array(
        "title", "shortTitle", "feedId", "feedTitle", "date", "favId", "favName",
        "season", "episode"
    ));
  }

  function update($options) {
    // update disbled, history doesnt change
    if(isset($this->title))
      return False;

    return parent::update($options);
  }

}

