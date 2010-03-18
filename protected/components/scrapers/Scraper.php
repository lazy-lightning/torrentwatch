<?php
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
  
  public function preg_get( $pattern, $subject )
  {
    preg_match( $pattern, $subject, $matches);
    return (isset($matches[1]) ? $matches[1] : '');
  }

  
  /**
   * strip_title 
   * Removes common parts of filenames that we don't want to search for...
   * (eg: file extension, file suffix ("CD1",etc) and non-alphanumeric chars.
   * 
   * @param string $title 
   * @return string
   */
  public function strip_title ($title)
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
  
  
  /**
   * substr_between_strings 
   * Returns the text between two given strings
   * 
   * @param string $string 
   * @param string $startstr 
   * @param string $endstr 
   * @return string
   */
  public function substr_between_strings( &$string, $startstr, $endstr)
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
  
  
  /**
   * get_urls_from_html 
   * Returns all the hyperlinks that are in the given string that match the specified
   * regular expression ($search) within the href portion of the link.
   * 
   * @param string $string 
   * @param string $search 
   * @return array 
   */
  public function get_urls_from_html ($string, $search )
  {
    preg_match_all ('/<a.*href="(.*'.$search.'[^"]*)"[^>]*>(.*)<\/a>/Ui', $string, $matches);
  
    for ($i = 0; $i<count($matches[2]); $i++)
      $matches[2][$i] = preg_replace('/<[^>]*>/','',$matches[2][$i]);
  
    return $matches;
  }
  
  
  /**
   * add_site_to_url 
   * Returns the given URL ($url) as a properly formatted URL, using $site as the site
   * address if one is not present.
   * 
   * @param string $url 
   * @param string $site 
   * @return string
   */
  public function add_site_to_url ( $url, $site )
  {
    if ( strpos($url,'http:/') === false)
      return rtrim($site,'/').'/'.ltrim($url,'/');
    else
      return $url;
  }
  
  
  /**
   * get_images_from_html 
   * Returns all the hyperlinks is the given string
   * 
   * @param string $string 
   * @return array
   */
  public function get_images_from_html ($string)
  {
    preg_match_all ('/<img.*src="([^"]*)"[^>]*>/i', $string, $matches);
    return $matches;
  }

  /**
   * get_html_tag_attrib 
   * Gets the value of an attrbute for a particluar tag (often the "alt" of an "img" tag)
   * 
   * @param string $html the html to be searched
   * @param string $tag the tag to find in the html
   * @param string $find a string to match in the tag
   * @param string $attribute the attribute to find
   * @return string the value of the attribute, or false
   */
  public function get_html_tag_attrib( $html, $tag, $find, $attribute )
  {
    preg_match ('¬<.*'.$tag.'.*'.$find.'.*>¬Ui', $html, $tag_html);
    preg_match ('¬'.$attribute.'="(.*)"¬Ui',$tag_html[0],$val);
    if (isset($val[1]) && !empty($val[1]))
      return $val[1];
    else
      return false;
  }

  /**
   * get_html_tag_value 
   * 
   * @param string $html the html to search
   * @param string $tag the tag to find in the html
   * @param string $find a string to match in the tag
   * @return string the text between <tag> and </tag>
   */
  public function get_html_tag_value( $html, $tag, $find)
  {
    preg_match ('¬<.*'.$tag.'.*'.$find.'.*>(.*)</'.$tag.'>¬Ui', $html, $val);
    if (isset($val[1]) && !empty($val[1]))
      return $val[1];
    else
      return false;
  }

  /**
   * best_match 
   * Given a string to search for ($needle) and an array of possible matches ($haystack) this
   * function will return the index number of the best match and set $this->accuracy to the value
   * determined (0-100). If no match is found, then this function returns FALSE
   * 
   * @param string $needle  the string to search for
   * @param array $haystack possible matches 
   * @return integer the index of the best match in haystack, or false
   */
  public function best_match ( $needle, $haystack)
  {
    $best_match = array("id" => 0, "chars" => 0, "pc" => 0);

    for ($i=0; $i<count($haystack); $i++)
    {
      $chars = similar_text(trim($needle),trim($haystack[$i]),$pc);
      $haystack[$i] .= " (".round($pc,2)."%)";
      if ( ($chars > $best_match["chars"] && $pc >= $best_match["pc"]) || $pc > $best_match["pc"])
        $best_match = array("id" => $i, "chars" => $chars, "pc" => $pc);
      // if detcted title is title (subtitle) (year) retry as title (year)
      // TODO: does this effect regular matching?  does this even work entirely as intended
      //if(preg_match('/^([^(]+)\([A-Z]+[^)]+\) *(\(\d+\))? /', $haystack[$i], $regs))
      //  $haystack[$i--] = $regs[1].(isset($regs[2]) ? trim($regs[2]) : '');
    }

    // If we are sure that we found a good result, then get the file details.
    if ($best_match["pc"] > 75)
    {
      Yii::log('Best guess: ['.$best_match["id"].'] - '.$haystack[$best_match["id"]], CLogger::LEVEL_INFO);
      $this->accuracy = $best_match["pc"];
      return $best_match["id"];
    }
    else
    {
      Yii::log('Multiple Matches found, No match > 75%', CLogger::LEVEL_ERROR);
      return false;
    }
  }
}
