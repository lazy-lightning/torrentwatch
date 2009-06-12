<?php

class feedItem extends CActiveRecord
{

  const STATUS_NEW = 0;
  const STATUS_NOMATCH = 1;
  const STATUS_MATCH = 2;
  const STATUS_FAILED_DL = 5;
  const STATUS_DUPLICATE = 6;
  const STATUS_OLD = 7;
  // Higher numbers so they can be sorted as "better" matches
  const STATUS_AUTO_DL = 20;
  const STATUS_MANUAL_DL = 21;

  const TYPE_TORRENT = 0;
  const TYPE_NZB = 1;

  private $_qualityIds;

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
			array('url','length','max'=>256),
			array('title','length','max'=>128),
			array('status, pubDate, lastUpdated, hash', 'required'),
			array('status, pubDate, lastUpdated', 'numerical', 'integerOnly'=>true),
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

  public function getQualityIds() {
    if($this->_qualityIds === null) {
      $relations = feedItem_quality::model()->findAllByAttributes(array('quality_id' => $this->id));

      $ids = array();
      foreach($relations as $record) {
        $ids[] = $record->quality_id;
      }
      
      $this->_qualityIds = $ids;
    }
    return $this->_qualityIds;
  }

  public function getDownloadTypeOptions() {
    return array(
        self::TYPE_TORRENT=>'Torrent',
        self::TYPE_NZB=>'NZB',
    );
  }

  public function getDownloadTypeText() {
    $options=$this->getDownloadTypeOptions();
    return isset($options[$this->downloadType]) ? $options[$this->downloadType]
        : "unknown ({$this->downloadType})";
  }

  public static function getStatusOptions() {
    return array(
        self::STATUS_NEW=>'New',
        self::STATUS_NOMATCH=>'Unmatched',
        self::STATUS_MATCH=>'Matched',
        self::STATUS_AUTO_DL=>'Automatic Download',
        self::STATUS_MANUAL_DL=>'Manual Download',
        self::STATUS_FAILED_DL=>'Failed Download',
        self::STATUS_DUPLICATE=>'Duplicate Episode',
        self::STATUS_OLD=>'Old Episode',
    );
  }

  // static to allow translation directly from ql row in a view
  public static function getStatusText($status = null) {
    if($status === null)
      $status = $this->status;
    $options=self::getStatusOptions();
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }
      
  public function getQualityString() {
    $string = array();
    foreach($this->quality as $quality) { 
      $string[] = $quality->title;
    }
    return implode(' / ', $string);
  }

  public function setQualityIds($ids = array()) {
    $this->_qualityIds = $ids;
  }

  public function afterSave() {
    // update scenario
    // Clean out any quality relations if this isn't new
    if(!$this->isNewRecord) {
      feedItem_quality::model()->deleteAll('feedItem_id=:feedItem_id', array(':feedItem_id'=>$this->id));
    }

    // set quality relations
    foreach($this->qualityIds as $qualityId) {
      $relation = new feedItem_quality;
      $relation->feedItem_id = $this->id;
      $relation->quality_id = $qualityId;
      $relation->save();
    }

    parent::afterSave();
  }

  public function beforeValidate($type) {
    $this->lastUpdated = time();

    if($this->isNewRecord) {
      $this->status = self::STATUS_NEW;
      
      if($options = $this->detectTitleParams()) {
        list($shortTitle, $quality, $season, $episode) = $options;
      
        $qualityIds = array();
        if(is_array($quality) && count($quality) === 0) {
          $quality = array('Unknown');
        }
        foreach($quality as $item) {
          $record = factory::qualityByTitle($item);
          $qualityIds[] = $record->id;
        }
        $this->qualityIds = $qualityIds;
    
        // Date based episode
        if(!is_numeric($season)) {
          $episode = strtotime(str_replace(' ', '/', $season));
          Yii::log("Converting $season into $episode", CLogger::LEVEL_ERROR);
          if($episode === False) {
            $shortTitle .= ' '.$season;
            $episode = 0;
          }
          $season = 0;
  
        }
       
        if($season == 0 && $episode == 0) {
          // This is either movie or other
          // the fact of having imdbId isn't best differentiator
          if($this->imdbId > 1000) {
            $movie = factory::movieByImdbId($this->imdbId, $shortTitle);
            $this->movie_id = $movie->id;
          } else {
            $other = factory::otherByTitle($shortTitle);
            $this->other_id = $other->id;
          }
        } else {
          $tvEpisode = factory::tvEpisodeByEpisode($shortTitle, $season, $episode);
          $this->tvEpisode_id = $tvEpisode->id;
        }
      } else
        Yii::log('Failed to detect for title: '.$this->title, CLogger::LEVEL_ERROR);
    }
    return parent::beforeValidate($type);
  }

  protected function detectTitleParams() {
    // strtr values
    $from = "._";
    $to = "  ";
    // Series Title
    $title_reg =
           '^([^-\(]+)' // Series title: string not including - or (
          .'(?:.+)?'; // Episode title: optinal, length is determined by the episode match
    // Episode
    $episode_reg =
           '\b('  // must be a word boundry before the episode
          .'S\d+[. ]?E\d+'.'|'  // S12E1 or S1.E22 or S4 E1
          .'\d+x\d+' .'|'  // 1x23
          .'\d+[. ]?of[. ]?\d+'.'|'  // 03of18
          .'[\d -.]{10}'   // 2008-03-23 or 07.23.2008 or .20082306. etc
          .')';
    $episode_reg2 = '\b(\d\d\d)\b'; // three (four hits movie years) with a word boundry on each side, ex: some.show.402.hdtv
  
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
             .'internal'.'|'
             .'limited' .'|'
             .'proper'  .'|'
             .'repack'  .'|'
             .'subbed'  .'|'
             .'x264'    .'|'
             .'Blue?Ray)';
 
    $quality = array('Unknown');
    if(preg_match_all("/$qual_reg/i", $this->title, $qregs)) {
      $q = array_change_key_case(array_flip($qregs[1]));
      // if 720p and hdtv strip hdtv to make hdtv more unique
      if(isset($q['720p'], $q['hdtv'])) {
        unset($qregs[1][$q['hdtv']]);
      }
      $quality = $qregs[1];
    }

    if(preg_match("/$title_reg$episode_reg/i", $this->title, $regs)) {
      $episode_guess = trim($regs[2]);
      // Is this str_replace still needed?
      $shortTitle = trim($regs[1]);
      $episode_guess = trim(strtr($episode_guess, $from, $to));
      // if match was a date season will receive it, guaranteed no x in the date from previous regexp so episode will be empty
      list($season,$episode) = explode('x', preg_replace('/(S(\d+) ?E(\d+)|(\d+)x(\d+)|(\d+) ?of ?(\d+))/i', '\2\4\6x\3\5\7', $episode_guess));
    } elseif(preg_match("/$title_reg$episode_reg2/i", $this->title, $regs)) {
      $shortTitle = trim($regs[1]);
      $episode_guess = $regs[2];
      $episode = substr($episode_guess, -2);
      $season = ($episode_guess-$episode)/100;
    } else {
      // No match, just strip everything after the quality
      $shortTitle = preg_replace("/$qual_reg.*/i", "", $this->title);
      $season = $episode = 0;
    }
    // Convert . and _ to spaces, and trim result
    $shortTitle = trim(strtr(str_replace("'", "&#39;", $shortTitle), $from, $to));

    return array($shortTitle, $quality, $season, $episode);
  }

  // Called from feedAdapter and extending classes to create new feed items
  public static function factory($data) {
    $item = new feedItem;
    $item->attributes = $data;
    if($item->save()) {
      return $item;
    } else {
      Yii::log("feedItem::factory() failed to create item\n".CHtml::errorSummary($item), CLogger::LEVEL_ERROR);
      return False;
    }
  }

}
