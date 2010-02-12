<?php
Yii::import('application.components.mediaTitleParser.*');

/**
 * mediaTitleParser 
 * 
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class mediaTitleParser {

  /**
   *  @var array an array of matching class names.  They will be prepended with
   * 'titleMatch' to get the full class name
   */
  static protected $titleMatchers = array(
    'Full',
    'Date',
    'Partial',
    'Short',
  );

  /**
   * title 
   * 
   * @var string
   */
  public $title = '';

  /**
   * epTitle 
   * 
   * @var string
   */
  public $epTitle = '';

  /**
   * season 
   * 
   * @var int
   */
  public $season = 0;

  /**
   * episode 
   * 
   * @var int
   */
  public $episode = 0;

  /**
   * network 
   * 
   * @var string
   */
  public $network = '';

  /**
   * quality 
   * 
   * @var array
   */
  public $quality = array();

  /**
   * tvEpisode,$movie,$other
   * 
   * @var int id of the related model for a given type
   */
  public $tvEpisode, $movie, $other;

  /**
   * __construct 
   * 
   * @param string $title  The title to be parsed for media information
   * @return void
   */
  public function __construct($title)
  {
    // Start by splitting the quality and the title into seperate strings
    list($shortTitle, $this->quality) = qualityMatch::run($title);
    // Get ID's for all the qualitys
    $this->quality = factory::qualityIdsByTitleArray($this->quality);

    // Loop through our title matching objects untill we find some information
    foreach(self::getMatchers() as $matcher)
    {
      $result = $matcher->run($shortTitle);
      if($result)
      {
        list($this->title, $this->epTitle, $this->season, $this->episode, $this->network) = $result;
        if(!empty($this->network))
          $this->network = factory::networkByTitle($this->network);
        $this->initRelated();
        return;
      }
    }

    // default detect if no match found
    $this->title = $shortTitle;
  }

  protected function initRelated()
  {
    if(($this->season >= 0 && $this->episode >0) ||
       ($season > 0 && $this->episode === 0))
    {
      $model = $this->tvEpisode = factory::tvEpisodeByEpisode($this->title, $this->season, $this->episode); 
      if(!empty($this->epTitle))
        $this->tvEpisode->title = $this->epTitle;
    }
    elseif(null!==($model = movie::model()->find('title LIKE :title', array(':title'=>$this->title))))
    {
      $this->movie = $model;
    }
    else
    {
      $model = $this->other = factory::otherByTitle($this->title);
    }
    // Trigger lastUpdated to update itself
    $model->save();
  }

  /**
   * applyTo
   *
   * @var feedItem $model a feedItem to apply the detected data to
   * @return void
   */
  public function applyTo(feedItem $model)
  {
    $model->setAttributes(array(
        'title'=>$this->title,
        'network_id'   => empty($this->network)   ? null : $this->network->id,
        'tvEpisode_id' => empty($this->tvEpisode) ? null : $this->tvEpisode->id,
        'movie_id'     => empty($this->movie)     ? null : $this->movie->id,
        'other_id'     => empty($this->other)     ? null : $this->other->id,
    ));
    $model->qualityIds = $this->quality;
  }

  static public function getMatchers()
  {
    if(is_string(self::$titleMatchers[0]))
    {
      foreach(self::$titleMatchers as $index => $matcher)
      {
        $class = 'titleMatch'.$matcher;
        self::$titleMatchers[$index] = new $class;
      }
    }
    return self::$titleMatchers;
  }
}
