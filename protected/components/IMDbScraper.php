<?
/**************************************************************************************************
   SWISScenter Source

   This is one of a selection of scripts all designed to obtain information from the internet
   relating to the movies that the user has added to their database. It typically collects
   information such as title, genre, year of release, certificate, synopsis, directors and actors.

 *************************************************************************************************/

/**
 * Translated into class for NMTDVR
 * very raw translation, not object oriented just contained
 */

class Scraper {
  // many of these functions are from base/utils.c or video_something_something.php in swisscenter

  /**
   * Simple function to search a string using a regular expression and then
   * return the first captured pattern
   *
   * @param string $pattern - Pattern to use when searching
   * @param string $subject - The string to search
   * @return string
   */
  
  function preg_get( $pattern, $subject )
  {
    preg_match( $pattern, $subject, $matches);
    return (isset($matches[1]) ? $matches[1] : '');
  }

  //-------------------------------------------------------------------------------------------------
  // Makes the given filepath acceptable to the webserver (\ become /)
  //-------------------------------------------------------------------------------------------------
  
  function make_url_path( $fsp )
  {
    // On linux/unix systems, we need to modify the path to access the file via the symbolic link
    // rather than trying to access it directly
    if ( is_unix() )
    {
      foreach ( db_toarray("select name,concat('media/',location_id) dir from media_locations") as $dir)
      {
        $pos = strpos($fsp, $dir["NAME"]);
        if ( $pos == 0 and $pos !== false)
          $fsp = $dir["DIR"].substr($fsp, strlen($dir["NAME"]));
      }
    }
  
    $parts = split('/',str_replace('\\','/',$fsp));
  
    // On windows, we should ensure that the drive letter is converted to uppercase
    if ( is_windows() )
      $parts[0] = strtoupper($parts[0]);
  
    for ($i=0; $i<count($parts); $i++)
      $parts[$i] = rawurlencode($parts[$i]);
  
    return join('/',$parts);
  }
  
  // ----------------------------------------------------------------------------------------
  // Removes common parts of filenames that we don't want to search for...
  // (eg: file extension, file suffix ("CD1",etc) and non-alphanumeric chars.
  // ----------------------------------------------------------------------------------------
  
  function strip_title ($title)
  {
    $search  = array ( '/\.[^.]*$/U'
                     , '/\(.*\)/'
                     , '/\[.*]/'
                     , '/\s[^\w&$]/'
                     , '/[^\w&$]\s/'
                     , '/\sCD[^\w].*/i'
                     , '/ +$/'
                     , '/_/'
                     , '/\./');
  
    $replace = array ( ''
                     , ' '
                     , ' '
                     , ' '
                     , ' '
                     , ' '
                     , ''
                     , ' '
                     , ' ');
  
    return preg_replace($search, $replace, $title);
  }
  
  // ----------------------------------------------------------------------------------
  // Returns the text between two given strings
  // ----------------------------------------------------------------------------------
  
  function substr_between_strings( &$string, $startstr, $endstr)
  {
    $start  = ( empty($startstr) ? 0 : strpos($string,$startstr));
    $end    = strpos($string,$endstr, $start+strlen($startstr));
  
    if ($start === false || $end === false)
    {
      return '';
    }
    else
    {
      $text  = strip_tags(substr($string,$start+strlen($startstr),$end-$start-strlen($startstr)));
  
      if (strpos($text,'>') === false)
        return ltrim(rtrim($text));
      else
        return ltrim(rtrim(substr($text,strpos($text,'>')+1)));
    }
  }
  
  // ----------------------------------------------------------------------------------
  // Returns all the hyperlinks that are in the given string that match the specified
  // regular expression ($search) within the href portion of the link.
  // ----------------------------------------------------------------------------------
  
  function get_urls_from_html ($string, $search )
  {
    preg_match_all ('/<a.*href="(.*'.$search.'[^"]*)"[^>]*>(.*)<\/a>/Ui', $string, &$matches);
  
    for ($i = 0; $i<count($matches[2]); $i++)
      $matches[2][$i] = preg_replace('/<[^>]*>/','',$matches[2][$i]);
  
    return $matches;
  }
  
  // ----------------------------------------------------------------------------------
  // Returns the given URL ($url) as a properly formatted URL, using $site as the site
  // address if one is not present.
  // ----------------------------------------------------------------------------------
  
  function add_site_to_url ( $url, $site )
  {
    if ( strpos($url,'http:/') === false)
      return rtrim($site,'/').'/'.ltrim($url,'/');
    else
      return $url;
  }
  
  // ----------------------------------------------------------------------------------
  // Returns all the hyperlinks is the given string
  // ----------------------------------------------------------------------------------
  
  function get_images_from_html ($string)
  {
    preg_match_all ('/<img.*src="([^"]*)"[^>]*>/i', $string, &$matches);
    return $matches;
  }

  // ----------------------------------------------------------------------------------------
  // Gets the value of an attrbute for a particluar tag (often the "alt" of an "img" tag)
  // ----------------------------------------------------------------------------------------

  function get_html_tag_attrib( $html, $tag, $find, $attribute )
  {
    preg_match ('¬<.*'.$tag.'.*'.$find.'.*>¬Ui', $html, &$tag_html);
    preg_match ('¬'.$attribute.'="(.*)"¬Ui',$tag_html[0],$val);
    if (isset($val[1]) && !empty($val[1]))
      return $val[1];
    else
      return false;
  }

  function get_html_tag_value( $html, $tag, $find)
  {
    preg_match ('¬<.*'.$tag.'.*'.$find.'.*>(.*)</'.$tag.'>¬Ui', $html, &$val);
    if (isset($val[1]) && !empty($val[1]))
      return $val[1];
    else
      return false;
  }

  // ----------------------------------------------------------------------------------------
  // Given a string to search for ($needle) and an array of possible matches ($haystack) this
  // function will return the index number of the best match and set $this->accuracy to the value
  // determined (0-100). If no match is found, then this function returns FALSE
  // ----------------------------------------------------------------------------------------

  function best_match ( $needle, $haystack)
  {
    $best_match = array("id" => 0, "chars" => 0, "pc" => 0);

    for ($i=0; $i<count($haystack); $i++)
    {
      $chars = similar_text(trim($needle),trim($haystack[$i]),$pc);
      $haystack[$i] .= " (".round($pc,2)."%)";

      if ( ($chars > $best_match["chars"] && $pc >= $best_match["pc"]) || $pc > $best_match["pc"])
        $best_match = array("id" => $i, "chars" => $chars, "pc" => $pc);
    }

    // If we are sure that we found a good result, then get the file details.
    if ($best_match["pc"] > 75)
    {
      Yii::log('Possible matches are:',$haystack);
      Yii::log('Best guess: ['.$best_match["id"].'] - '.$haystack[$best_match["id"]]);
      $this->accuracy = $best_match["pc"];
      return $best_match["id"];
    }
    else
    {
      Yii::log('Multiple Matches found, No match > 75%',$haystack);
      return false;
    }
  }
}

class IMDbScraper extends Scraper {

  var $accuracy = 0;
  var $imdbId;
  var $title;
  var $year;
  var $plot;
  var $rating;
  var $genres;

  function __construct($title, $description = '')
  {
    // Perform search for matching titles
    $site_url    = 'http://www.imdb.com/';
    $search_url  = $site_url.'find?s=tt;q=';

    Yii::log("Searching for details about ".$title." online at '$site_url'");

    if (preg_match("/\[(tt\d+)\]/",$title, $imdbtt) != 0)
    {
      // Filename includes an explicit IMDb title such as '[tt0076759]', use that to find the movie
      $html = file_get_contents($search_url.$imdbtt[1]);
    }
    elseif (preg_match("/imdb.com\/title\/(tt\d+)/",$description, $imdbtt) != 0)
    {
      // description includes an imdb url, use that to find the movie
      $html = file_get_contents($search_url.$imdbtt[1]);
    }
    else
    {
      // User IMDb's internal search to get a list a possible matches
      // IMDB doesn't like year in search, so save it for later reference
      if (preg_match("/(.*)(\d\d\d\d)/",$title,$title_regs) != 0) 
      {
        $title = $title_regs[1];
        $title_year = $title_regs[2];
      }
      $html = file_get_contents($search_url.str_replace('%20','+',urlencode($title)));
    }

    // Decode HTML entities found on page
    $html = html_entity_decode($html, ENT_QUOTES);
    // replace characters like &#x27; with '
    $html = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $html)

    // If the title contains a year then adjust the returned page to include year in search
    if(isset($title_year))
    {
      $html = preg_replace('/<\/a>\s+\((\d\d\d\d).*\)/Ui',' ($1)</a>',$html);
      $title .= ' ('.$title_year[0].')';
    }

    // Examine returned page
    if (strpos(strtolower($html),"no matches") !== false)
    {
      // There are no matches found... do nothing
      $this->accuracy = 0;
      Yii::log("No Match found.", CLogger::LEVEL_WARNING);
    }
    else if (strpos($html, "<title>IMDb Title Search</title>") > 0)
    {
      // There are multiple matches found... process them
      $html    = substr($html,strpos($html,"Titles"));
      $matches = $this->get_urls_from_html($html, '\/title\/tt\d+\/');
      $index   = $this->best_match($title, $matches[2]);

      // If we are sure that we found a good result, then get the file details.
      if ($this->accuracy > 75)
      {
        $url_imdb = $this->add_site_to_url($matches[1][$index],$site_url);
        $url_imdb = substr($url_imdb, 0, strpos($url_imdb,"?fr=")-1);
        $this->imdbId = substr($matches[1][$index], 9, 7);
        $html = html_entity_decode(file_get_contents( $url_imdb ), ENT_QUOTES);
      }
    }
    else
    {
      // Direct hit on the title
      $this->accuracy = 100;
    }

    // Determine attributes for the movie and update the object properties
    if ($this->accuracy >= 75)
    {
      // Find Title
      preg_match("/<title>([^(<]+)/",$html,$title);
      $this->title = trim($title[1]);
      // Find Year
      $this->year = $this->preg_get('#href=\"/Sections/Years/(\d+)#',$html);

      // Find Runtime
      preg_match("/<h5>Runtime:<\/h5>/",$html,$runtime);
      $this->runtime = trim($runtime[1]);

      // Limit the rest of the searches to a subsection of the page
      $start = strpos($html,"<div class=\"photo\">");
      $end = strpos($html,"<a name=\"comment\">");
      $html = substr($html,$start,$end-$start+1);

      // Find Synopsis
      preg_match("/<h5>Plot(| Outline| Summary):<\/h5>([^<]*)</sm",$html,$synopsis);
      $this->plot = trim(trim($synopsis[2], " |"));

      // Find User Rating
      $user_rating = $this->preg_get("/<h5>User Rating:<\/h5>.*?<b>(.*)\/10<\/b>/sm",$html);
      $this->rating = empty($user_rating) ? '' : intval($user_rating * 10);

      /* Dont download images, yet
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
          file_save_albumart( add_site_to_url($img_addr, $site_url),
                              dirname($filename).'/'.file_noext($filename).'.'.file_ext($img_addr),
                              $title);
        }
      } */

      // Attempt to capture the fact that the website has changed and we are unable to get movie information.
      if (strlen($html) == 0)
      {
        Yii::log('UNABLE TO GET MOVIE INFORMATION FROM WWW.IMDB.COM', CLogger::LEVEL_ERROR);
        Yii::log('This may be due to IMDB changing their page format', CLogger::LEVEL_ERROR);
        return false;
      }
      else
      {
        /* Unused for now
        // Director(s)
        $start = strpos($html,"<h5>Director");
        $end = strpos($html,"<h5>",$start+1);
        $html_directed = substr($html,$start,$end-$start);
        $matches = get_urls_from_html($html_directed,"\/name\/nm\d+\/");
        $new_directors = $matches[2];

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
        */
        // Genre
        $start = strpos($html,"<h5>Genre:</h5>");
        $end = strpos($html,"</div>",$start+1);
        $html_genres = substr($html,$start,$end-$start);
        $matches = $this->get_urls_from_html($html_genres,"\/Sections\/Genres\/");
        $this->genres    = $matches[2];
        
        /* Unused for now
        // Languages
        $start = strpos($html,"<h5>Language:</h5>");
        $end = strpos($html,"</div>",$start+1);
        $html_langs = str_replace("\n","",substr($html,$start,$end-$start));
        $matches = get_urls_from_html($html_langs,"\/Sections\/Languages\/");
        $new_languages = $matches[2];
        */
        return true;
      }
    }
    else
    {
      return false;
    }
  }
}
/**************************************************************************************************
                                               End of file
 **************************************************************************************************/
?>
