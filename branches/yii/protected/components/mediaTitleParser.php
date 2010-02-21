<?php
Yii::import('application.components.mediaTitleParser.*');

/**
 * mediaTitleParser is called from the factory to help initialize the related
 * models of a feedItem based on the given title title.
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
  public $shortTitle = '';

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
   * tvEpisode
   * 
   * @var tvEpisode model of the related tvEpisode, or null
   */
  public $tvEpisode;
    
  /**
   * movie 
   * 
   * @var movie model of the related movie, or null
   */
  public $movie;
  
  /**
   * other 
   * 
   * @var other model of the related other, or null
   */
  public $other;

  /**
   * factory 
   * 
   * @var modelFactory
   */
  protected $factory;

  /**
   * __construct will take in a title and initialize its properties based
   * on the given title
   * 
   * @param string $title  The title to be parsed for media information
   * @param modelFactory an object to create 
   * @return void
   */
  public function __construct($title,$factory)
  {
    $this->factory = $factory;
    // Start by splitting the quality and the title into seperate strings
    list($this->shortTitle, $this->quality) = qualityMatch::run($title);
    // Get ID's for all the qualitys
    $this->quality = $this->factory->qualityIdsByTitleArray($this->quality);

    // Loop through our title matching objects untill we find some information
    foreach(self::getMatchers() as $matcher)
    {
      $result = $matcher->run($this->shortTitle);
      if($result)
      {
        list($this->shortTitle, $this->epTitle, $this->season, $this->episode, $this->network) = $result;
        if(!empty($this->network))
          $this->network = $this->factory->networkByTitle($this->network);
        break;
      }
    }
    $this->initRelated();
    return;
  }

  protected function initRelated()
  {
    if(($this->season >= 0 && $this->episode >0) ||
       ($this->season > 0 && $this->episode === 0))
    {
      $model = $this->tvEpisode = $this->factory->tvEpisodeByEpisode($this->shortTitle, $this->season, $this->episode); 
      if(!empty($this->epTitle))
        $this->tvEpisode->title = $this->epTitle;
    }
    elseif(null!==($model = movie::model()->find('title LIKE :title', array(':title'=>$this->shortTitle))))
    {
      $this->movie = $model;
    }
    else
    {
      // later these get scanned in imdb to be re-checked for movies
      $model = $this->other = $this->factory->otherByTitle($this->shortTitle);
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
    $model->qualityIds = $this->quality;
    $model->setAttributes(array(
        'network_id'   => empty($this->network)   ? null : $this->network->id,
        'tvEpisode_id' => empty($this->tvEpisode) ? null : $this->tvEpisode->id,
        'movie_id'     => empty($this->movie)     ? null : $this->movie->id,
        'other_id'     => empty($this->other)     ? null : $this->other->id,
    ));
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
