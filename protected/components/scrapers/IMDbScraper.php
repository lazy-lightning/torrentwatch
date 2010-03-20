<?php
/**************************************************************************************************
   SWISScenter Source

   This is one of a selection of scripts all designed to obtain information from the internet
   relating to the movies that the user has added to their database. It typically collects
   information such as title, genre, year of release, certificate, synopsis, directors and actors.

 *************************************************************************************************/

class IMDbScraper extends Scraper {

  protected $site_url    = 'http://www.imdb.com/';
  protected $search_append  = 'find?s=tt;q=';

  var $accuracy = 0;
  var $imdbId;
  var $title;
  var $year;
  var $plot;
  var $rating;
  var $genres;

  /**
   * __construct 
   * 
   * @param string $title 
   * @param string $description 
   * @return void
   */
  public function __construct($title, $description = '', $accuracyLimit = 75)
  {
    $html = $this->searchFor($title, $description);
    $html = $this->examineResult($html, $title, $accuracyLimit);
    // Attempt to capture the fact that the website has changed and we are unable to get movie information.
    if (strlen($html) == 0)
    {
      Yii::log('UNABLE TO GET MOVIE INFORMATION FROM WWW.IMDB.COM', CLogger::LEVEL_ERROR, 'application.components.IMDbScraper');
      Yii::log('This may be due to IMDB changing their page format', CLogger::LEVEL_ERROR, 'application.components.IMDbScraper');
      return;
    }
    // Determine attributes for the movie and update the object properties
    elseif ($this->accuracy >= $accuracyLimit)
    {
      $this->updateId($html);
      $this->updateTitle($html);
      $this->updateYear($html);
      $this->updateRuntime($html);
      // Limit the rest of the searches to a subsection of the page
      $start = strpos($html,"<div class=\"photo\">");
      $end = strpos($html,"<a name=\"comment\">");
      $html = substr($html,$start,$end-$start+1);
      $this->updatePlot($html);
      $this->updateRating($html);
      $this->updateGenres($html);
    }
  }

  public function decodeHtml($html)
  {
    // Decode HTML entities found on page
    $html = html_entity_decode($html, ENT_QUOTES);
    // replace characters like &#x27; with '
    // FIXME: technically this is evil eval, but seems necessary
    return preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $html);
  }

  public function examineResult($html, $title, $accuracyLimit)
  {
    $lhtml = strtolower($html);
    if (strpos($lhtml,"no matches") !== false ||
        strpos($lhtml, "enter a word or phrase to search on") !== false)
    {
      // There are no matches found... do nothing
      $this->accuracy = 0;
      Yii::log("No Match found.", CLogger::LEVEL_WARNING, 'application.components.IMDbScraper');
    }
    else if (strpos($html, "<title>IMDb Title Search</title>") > 0)
    {
      $html = $this->processTitleSearch($html, $title, $accuracyLimit);
    }
    else
    {
      // Direct hit on the title
      $this->accuracy = 100;
    }
    return $html;
  }

  public function getId()
  {
    return $this->imdbId;
  }

  public function getName()
  {
    return $this->title;
  }

  protected function processTitleSearch($html, $title, $accuracyLimit)
  {
    // There are multiple matches found... process them
    $html    = substr($html,strpos($html,"Titles"));
    $matches = $this->get_urls_from_html($html, '\/title\/tt\d+\/');
    $index   = $this->best_match($title, $matches[2], $accuracyLimit);

    // If we are sure that we found a good result, then get the file details.
    if ($this->accuracy > $accuracyLimit)
    {
      $url_imdb = $this->add_site_to_url($matches[1][$index],$this->site_url);
      $url_imdb = substr($url_imdb, 0, strpos($url_imdb,"?fr=")-1);
      $this->imdbId = substr($matches[1][$index], 9, 7);
      $html = $this->decodeHtml(file_get_contents($url_imdb));
    }
    return $html;
  }

  protected function searchFor(&$title, $description)
  {
    // Perform search for matching titles
    Yii::trace("Searching for details about ".$title." online at '$this->site_url'", 'application.components.IMDbScraper');
    // Filename includes an explicit IMDb title such as '[tt0076759]', use that to find the movie
    if (preg_match("/\[(tt\d+)\]/",$title, $imdbtt) != 0)
    {
      $html = file_get_contents($this->site_url.$this->search_append.$imdbtt[1]);
    }
    // description includes an imdb url, use that to find the movie
    elseif (preg_match("/imdb.com\/title\/(tt\d+)/",$description, $imdbtt) != 0)
    {
      $html = file_get_contents($this->site_url.$this->search_append.$imdbtt[1]);
    }
    // Use IMDb's internal search to get a list a possible matches
    // IMDB doesn't like year in search, so save it for later reference
    else
    {
      if (preg_match("/(.*)(\d\d\d\d)/",$title,$title_regs) != 0) 
      {
        $title = $title_regs[1];
        $title_year = $title_regs[2];
      }
      $html = file_get_contents($this->site_url.$this->search_append.str_replace('%20','+',urlencode($title)));
    }
    $html = $this->decodeHtml($html);
    // If the title contains a year then adjust the returned page to include year in search
    if(isset($title_year))
    {
      $html = preg_replace('/<\/a>\s+\((\d\d\d\d).*\)/Ui',' ($1)</a>',$html);
      $title .= ' ('.$title_year.')';
    }
    return $html;
  }

  public function updateId($html)
  {
    $this->imdbId = $this->preg_get('#/title/tt(\d+)/#', $html);
  }

  public function updateTitle($html)
  {
    preg_match("/<title>([^(<]+)/",$html,$title);
    $this->title = trim($title[1]);
  }

  public function updateYear($html)
  {
    $this->year = $this->preg_get('#\((\d\d\d\d)\)(?: \([A-Z]+\))?</title>#i',$html);
  }

  public function updateRuntime($html)
  {
    if(preg_match("#\n(\d+) min#",$html,$runtime))
      $this->runtime = trim($runtime[1]);
  }


  public function updatePlot($html)
  {
    // Find Synopsis
    if(preg_match("#Plot:[^\n]+\n[^\n]+\n([^|<]+)#",$html,$synopsis))
      $this->plot = trim($synopsis[1]);
  }

  public function updateRating($html, $multiplier = 10)
  {
    $rating = $this->preg_get("/<h5>User Rating:<\/h5>.*?<b>(.*)\/10<\/b>/sm",$html);
    $this->rating = empty($rating) ? '' : intval($rating * $multiplier);
  }

  public function updateGenres($html)
  {
    // Genre
    $start = strpos($html,"<h5>Genre:</h5>");
    $end = strpos($html,"</div>",$start+1);
    $html_genres = substr($html,$start,$end-$start);
    $matches = $this->get_urls_from_html($html_genres,"\/Sections\/Genres\/");
    $this->genres = $matches[2];
  }

  public function updateAlbumArt($html, $filename)
  {
    // Download and store Albumart if there is none present.
    if ( file_albumart($filename, false) == '')
    {
      $matches = get_images_from_html($html);
      $img_addr = $matches[1][0];
      if (file_ext($img_addr)=='jpg')
      {
        // Replace resize attributes with maximum allowed
        $img_addr = preg_replace('/SX\d+_/','SX450_',$img_addr);
        $img_addr = preg_replace('/SY\d+_/','SY700_',$img_addr);
        file_save_albumart( add_site_to_url($img_addr, $this->site_url),
                            dirname($filename).'/'.file_noext($filename).'.'.file_ext($img_addr),
                            $title);
      }
    }
  }

  public function updateDirectors($html)
  {
    // Director(s)
    $start = strpos($html,"<h5>Director");
    $end = strpos($html,"<h5>",$start+1);
    $html_directed = substr($html,$start,$end-$start);
    $matches = get_urls_from_html($html_directed,"\/name\/nm\d+\/");
    $new_directors = $matches[2];
  }

  public function updateActors($html)
  {
    // Actor(s)
    $start = strpos($html,"<table class=\"cast\">");
    $end = strpos($html,"</table>",$start+1);
    $html_actors = substr($html,$start,$end-$start);
    $matches = get_urls_from_html($html_actors,"\/name\/nm\d+\/");
    for ($i=0; $i<count($matches[2]); $i++)
    {
      if (strlen($matches[2][$i]) == 0)
      {
        array_splice($matches[2],$i,1);
        $i--;
      }
    }
    $new_actors = $matches[2];
  }

  public function updateLanguages($html)
  {
    // Languages
    $start = strpos($html,"<h5>Language:</h5>");
    $end = strpos($html,"</div>",$start+1);
    $html_langs = str_replace("\n","",substr($html,$start,$end-$start));
    $matches = get_urls_from_html($html_langs,"\/Sections\/Languages\/");
    $new_languages = $matches[2];
  }
}
?>
