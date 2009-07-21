<?php

class feedItem extends ARwithQuality
{

  // Higher numbers so they can be sorted as "better" matches
  const STATUS_NEW = 0;
  const STATUS_NOMATCH = 1;
  const STATUS_MATCH = 2;
  const STATUS_DUPLICATE = 6;
  const STATUS_OLD = 7;
  const STATUS_QUEUED = 15;
  const STATUS_FAILED_DL = 19;
  const STATUS_AUTO_DL = 20;
  const STATUS_MANUAL_DL = 21;

  // Download Types
  const TYPE_TORRENT = 0;
  const TYPE_NZB = 1;

  /**
   * Returns the static model of the specified AR class.
   * @return CActiveRecord the static model class
   */
  public static function model($className=__CLASS__)
  {
    return parent::model($className);
  }

  /**
   * @return string the associated database table name
   */
  public function tableName()
  {
    return 'feedItem';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
      array('hash', 'length', 'allowEmpty'=>false, 'is'=>32),
      array('feed_id', 'default', 'value'=>0),
      array('feed_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'feed'),
      array('movie_id', 'exist', 'attributeName'=>'id', 'className'=>'movie'),
      array('other_id', 'exist',  'attributeName'=>'id', 'className'=>'other'),
      array('tvEpisode_id', 'exist', 'attributeName'=>'id', 'className'=>'tvEpisode'),
      array('pubDate', 'default', 'value'=>time()),
      array('pubDate', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('status', 'in', 'allowEmpty'=>false, 'range'=>array_keys($this->getStatusOptions())),
      array('downloadType', 'in', 'allowEmpty'=>false, 'range'=>array_keys(feedItem::getStatusOptions())),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feed'=>array(self::BELONGS_TO, 'feed', 'feed_id'),
        'quality'=>array(self::MANY_MANY, 'quality', 'feedItem_quality(feedItem_id, quality_id)'),
        // Belongs to only one of the next 3
        'tvEpisode'=>array(self::BELONGS_TO, 'tvEpisode', 'tvEpisode_id'),
        'movie'=>array(self::BELONGS_TO, 'movie', 'movie_id'),
        'other'=>array(self::BELONGS_TO, 'other', 'other_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
    );
  }

  /**
   * all valid download types and their string mappings
   * @return array number=>string pairs 
   */
  public static function getDownloadTypeOptions() {
    return array(
        self::TYPE_TORRENT=>'Torrent',
        self::TYPE_NZB=>'NZB',
    );
  }

  /**
   * @return string String representation of download type
   */
  public static function getDownloadTypeText($downloadType=null) {
    if($downloadType===null)
      $downloadType = $this->downloadType;

    $options=$this->getDownloadTypeOptions();
    return isset($options[$downloadType]) ? $options[$downloadType]
        : "unknown ($downloadType)";
  }

  /**
   * all valid statuses and their string mappings
   * @return array number=>string pairs
   */
  public static function getStatusOptions() {
    return array(
        self::STATUS_AUTO_DL=>'Automatic Download',
        self::STATUS_DUPLICATE=>'Duplicate Episode',
        self::STATUS_FAILED_DL=>'Failed Download',
        self::STATUS_NEW=>'New',
        self::STATUS_NOMATCH=>'Unmatched',
        self::STATUS_MANUAL_DL=>'Manual Download',
        self::STATUS_MATCH=>'Matched',
        self::STATUS_OLD=>'Old Episode',
        self::STATUS_QUEUED=>'Queued for User',
    );
  }

  // static to allow translation directly from query row in a view without AR model
  public static function getStatusText($status = null) {
    if($status === null)
      $status = $this->status;
    $options=self::getStatusOptions();
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }
      
  public function beforeValidate($type) {
    $this->lastUpdated = time();

    if($this->isNewRecord) {
      list($shortTitle, $quality, $season, $episode, $network) = $this->detectTitleParams();
  
      if(!empty($network))
        $this->network_id = factory::networkByTitle($network)->id;

      $this->qualityIds = factory::qualityIdsByTitleArray($quality);
     
      if(($season >= 0 && $episode > 0) ||
         ($season > 0 && $episode == 0)) 
      {
        // Found a season and episode for this item
        $this->tvEpisode_id = factory::tvEpisodeByEpisode($shortTitle, $season, $episode)->id;
      }
      elseif($this->imdbId > 1000) 
      {
        // IMdB id is not best differentiator, but will work for now
        $this->movie_id = factory::movieByImdbId($this->imdbId, $shortTitle)->id;
      }
      else 
      {
        $this->other_id = factory::otherByTitle($shortTitle)->id;
      }
    }
    return parent::beforeValidate($type);
  }

  protected function detectTitleParams() {
    // strtr values
    $from = "._-";
    $to = "   ";
    // Series Title
    $title_reg =
           '^([^-\(]+)' // Series title: string not including - or (
          .'(?:.+)?'; // Episode title: optinal, length is determined by the episode match
    // Episode
    $episode_reg =
           '\b('  // must be a word boundry before the episode to prevent turning season 13 into season 3
          .'S\d+[. _]?E\d+'.'|'  // S12E1 or S1.E22 or S4 E1
          .'\d+x\d+' .'|'  // 1x23
          .'\d+[. ]?of[. ]?\d+'  // 03of18
          .')';
    $episode_reg2 = '\b([SE])(\d+)\b';
    $episode_reg3 = '\b(0?\d\d\d)\b..'; // three digits (four hits movie years, optional 0 to catch single digit season) with a 
                                        // word boundry on each side, ex: some.show.402.hdtv
                                        // with at least some data after it to not match a group name at the end
    $episode_reg4 =
           '\b('
          .'\d\d\d\d[- ._]\d\d[- _.]\d\d'.'|' // 2008-03-23
          .'\d\d[- _.]\d\d[- _.]\d\d\d\d'.'|' // 03.23.2008
          .'\d\d[- _.]\d\d[- _.]\d\d'         // 03 23 08 
          .')';

    // Possible Qualitys
    $qual_reg ='(DVB' .'|'
             .'720p'   .'|'
             .'DSR(ip)?|'
             .'DVBRip'  .'|'
             .'DVDR(ip)?|'
             .'DVDScr'  .'|'
             .'HR.HDTV' .'|'
             .'HDTV'    .'|'
             .'HR.PDTV' .'|'
             .'PDTV'    .'|'
             .'SatRip'  .'|'
             .'SVCD'    .'|'
             .'TVRip'   .'|'
             .'WebRip'  .'|'
             .'WS'      .'|'
             .'1080i'   .'|'
             .'1080p'   .'|'
             .'DTS'     .'|'
             .'AC3'     .'|'
             .'XViD'    .'|'
             .'internal'.'|'
             .'limited' .'|'
             .'proper'  .'|'
             .'repack'  .'|'
             .'subbed'  .'|'
             .'x264'    .'|'
             .'Blue?Ray)';
 
    $quality = array('Unknown');
    if(preg_match_all("/$qual_reg/i", $this->title, $qregs)) {
      // if 720p and hdtv strip hdtv to make hdtv more unique
      $q = array_change_key_case(array_flip($qregs[1]));
      if(isset($q['720p'], $q['hdtv'])) {
        unset($qregs[1][$q['hdtv']]);
      }
      $quality = $qregs[1];
    }

    Yii::log($this->title);
    $network = null;
    if(preg_match("/$title_reg$episode_reg/i", $this->title, $regs)) 
    {
      Yii::log('episode match'.print_r($regs, TRUE));
      $shortTitle = trim($regs[1]);
      $episode_guess = trim(strtr($regs[2], $from, $to));
      // if match was a date season will receive it, guaranteed no x in the date from previous regexp so episode will be empty
      list($season,$episode) = explode('x', preg_replace('/(S(\d+) ?E(\d+)|(\d+)x(\d+)|(\d+) ?of ?(\d+))/i', '\2\4\6x\3\5\7', $episode_guess));
    }
    elseif(preg_match("/$title_reg$episode_reg4/i", $this->title, $regs))
    {
      Yii::log('date based episode '.print_r($regs, TRUE));
      // Item is a date based episode
      $shortTitle = trim($regs[1]);

      // Use UTC for strtotime measurements
      $tz = date_default_timezone_get();
      date_default_timezone_set('UTC');
      $date = strtotime(str_replace(' ', '/', trim(strtr($regs[2], $from, $to))));
      date_default_timezone_set($tz);

      $season = 0;
      $episode = (integer) $date; // cast false to 0

      // strtotime failed, append given date to title
      if($date === False)
        $shortTitle .= ' '.$season;
      Yii::log("season: $season episode: $episode shortTitle: $shortTitle");
    }
    elseif(preg_match("/$title_reg$episode_reg2/i", $this->title, $regs))
    {
      Yii::log('episode or season, not both'.print_r($regs, TRUE));
      // only episode or season, not both
      $shortTitle = trim($regs[1]);
      $season  = $regs[2] == 'S' ? trim($regs[3]) : 1;
      $episode = $regs[2] == 'E' ? trim($regs[3]) : 0;
    }
    elseif(preg_match("/$title_reg$episode_reg3/i", $this->title, $regs)) 
    {
      Yii::log('3 digit season/episode identifier'.print_r($regs, TRUE));
      // 3 digit season/episode identifier
      $shortTitle = trim($regs[1]);
      $episode_guess = $regs[2];
      $episode = substr($episode_guess, -2);
      $season = ($episode_guess-$episode)/100;
    } 
    else 
    {
      Yii::log('no match, strip quality');
      // No match, just strip everything after the quality
      $shortTitle = preg_replace("/$qual_reg.*/i", "", $this->title);
      $season = $episode = 0;
    }
    // Convert . and _ to spaces, and trim result
    $shortTitle = trim(strtr(str_replace("'", "&#39;", $shortTitle), $from, $to));
    // Remove any marking of a second or third posting of an item
    $shortTitle = trim(preg_replace('/\([23]\)/', '', $shortTitle));

    // Custom handling for a few networks that show up as 'Foo.Channel.Show.Title.S02E02.Bar-ASDF'
    if(preg_match('/^([a-zA-Z]+\bchannel)\b(.*)/i', $shortTitle, $regs))
    {
      $network = $regs[1];
      $shortTitle = $regs[2];
    }

    return array($shortTitle, $quality, $season, $episode, $network);
  }

  /**
   * Create a favorite that would match this feed item
   * @return object an unsaved favorite
   */
  public function generateFavorite() {
    $fav = null;
    $itemType = $this->itemTypeRecord;
    if($itemType === null || get_class($itemType) === 'other')
    {
      $fav = new favoriteString;
      $fav->filter = $fav->name = $feedItem->title;
    }
    else
    {
      $fav = $itemType->generateFavorite($this);
    }

    if($fav) {
      $ids = array();
      foreach($this->quality as $quality)
        $ids[] = $quality->id;
      $fav->qualityIds = $ids;
    }

    return $fav;
  }

  public function getItemTypeRecord()
  {
    if(!empty($this->tvEpisode_id))
      return $this->tvEpisode;
    elseif(!empty($this->movie_id))
      return $this->movie;
    elseif(!empty($this->other_id))
      return $this->other;
    return null;
  }
}
