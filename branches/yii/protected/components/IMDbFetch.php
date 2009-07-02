<?php
/**********************************\
||================================||
||                                ||
||        IMDbFetch v0.3          ||
||     IMDbFetch.class.php        ||
||              |                 ||
||          - class -             ||
||   by teo rusu                  ||
||   spinicrus@gmail.com          ||
||   http://- :) not yet, no time ||
||                                ||
||================================||
\\************************/////////
// small updates by erik bernhardson
////////
/////////
//
ini_set("max_execution_time","20000");
ini_set("max_input_time","20000");
//define some functions we need:
function cleanString($imputString) {
    $whatToCleanArray = array(chr(13),chr(10),chr(13).chr(10),chr(10).chr(13),"\n","  ","   ","    ","\n\n","\n\r");
    $cleanWithArray = array("","","","","","","","","","");
    $cleaned = str_replace($whatToCleanArray,$cleanWithArray,$imputString);
    $cleaned = trim($cleaned);
    return $cleaned;
}
//
function getExt($filename) {
    return substr(strrchr($filename,"."),1);
}
//
function extractStringFromString($string, $start, $end, $reverse = False) {
    //
    if($reverse === True) {
      $stringEndTagPos = strpos_reverse_way($string,$end);
      $startPos = strpos_reverse_way($string,$start,$stringEndTagPos);
    } else {
      $startPos = strpos($string,$start);
      $stringEndTagPos = strpos($string,$end,$startPos);
    }
    $stringBetween = substr($string,$startPos+strlen($start),$stringEndTagPos-$startPos-strlen($start));
    //
    if (strlen($stringBetween) != 0) {
        //
        return $stringBetween;
        return true;
    }
    else {
        //
        return false;
    }
}
//
function strpos_reverse_way($string,$charToFind,$relativePos = null) {
    //
    if($relatePos === null)
      $relativePos = strlen($string);
    $searchPos = $relativePos;
    $searchChar = '';
    //
    while ($searchChar != $charToFind && $newPos >= 0) {
        $newPos = $searchPos-1;
        $searchChar = substr($string,$newPos,strlen($charToFind));
        $searchPos = $newPos;
    }
    //
    if (!empty($searchChar)) {
        //
        return $searchPos;
        return TRUE;
    }
    else {
        return FALSE;
    }
    //
}

//
// next couple functions from swisscenter base/utils.php by robert taylor
//

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

// Next function from SwissCenter video_obtain_info.php by robert taylor
  // ----------------------------------------------------------------------------------------
  // Given a string to search for ($needle) and an array of possible matches ($haystack) this
  // function will return the index number of the best match and set $accuracy to the value
  // determined (0-100). If no match is found, then this function returns FALSE
  // ----------------------------------------------------------------------------------------

  function best_match ( $needle, $haystack, &$accuracy )
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
      Yii::log('Possible matches are:'.implode(', ', $haystack));;
      Yii::log('Best guess: ['.$best_match["id"].'] - '.$haystack[$best_match["id"]]);
      $accuracy = $best_match["pc"];
      return $best_match["id"];
    }
    else
    {
      Yii::log("Multiple Matches found, No match > 75%\n".implode(', ', $haystack), CLogger::LEVEL_WARNING);
      return false;
    }
  }

//define imdb fetch class:
class IMDbFetch {
    var $notavailable =  '- not available at the moment -';
    //
    var $addressLink;
    var $id;
    var $name;
    var $year;
    var $directed_by;
    var $writing_credits;
    var $genre;
    var $rating;
    var $runtime;
    var $comment;
    var $plot;
    var $poster_image;
    var $poster_image_link;
    //
    var $success;
    //

    function IMDbFetch($imputLink) {
        //
        if(empty($imputLink)) {
            $this->addressLink = "http://www.imdb.com/title/tt0259711/";
        }
        else {
            if (substr($imputLink, strlen($imputLink)-1, 1) != '/') {
                $imputLink = $imputLink.'/';
            }
            $this->addressLink = $imputLink;
        }
        //
        $file = new feedAdapter_File($this->addressLink, 10, 0);
        $this->success = $file->success;
        if(!$this->success)
          return;

        $searchPage = html_entity_decode($file->body, ENT_QUOTES);
        //
        //IMDB ID:
        //
        $imdbIDStartTag    = 'title/tt';
        $imdbIDStartTagPos = strpos($this->addressLink,$imdbIDStartTag);
        $imdbIDEndTag      = '/';
        $imdbIDEndTagPos   = strpos($this->addressLink,$imdbIDEndTag,$imdbIDStartTagPos);
        $imdbID = substr($this->addressLink,$imdbIDStartTagPos+strlen($imdbIDStartTag),$imdbIDEndTagPos-$imdbIDStartTagPos-strlen($imdbIDStartTag)+2);
        $this->id = cleanString($imdbID);
        //
        //IMDB NAME:
        //
        $imdbNameStartTag = '<div id="tn15title">';
        $imdbNameEndTag   = '<span>';
        $this->name = cleanString(strip_tags(extractStringFromString($searchPage, $imdbNameStartTag, $imdbNameEndTag)));
        //
        if ($this->name === false) {
            $this->name = $this->notavailable;;
        }
        //
        //IMDB YEAR:
        //
        $imdbYearStartTag = '<span>(<a href="/Sections/Years/';
        $imdbYearEndTag   = '/">';
        $this->year = cleanString(strip_tags(extractStringFromString($searchPage, $imdbYearStartTag, $imdbYearEndTag)));
        //
        if ($this->year === false) {
            $this->year = $this->notavailable;;
        }
        //
        //IMDB PLOT:
        //
        $imdbPlotStartTag = '<h5>Plot:</h5>';
        $imdbPlotEndTag   = '<a';
        $this->plot = cleanString(extractStringFromString($searchPage, $imdbPlotStartTag, $imdbPlotEndTag));
        if ($this->plot === false ||
            substr($this->plot, 0, 6) === 'L PUBL') {
          $this->plot = $this->notavailable;;
        }
        //
        //IMDB DIRECTED BY:
        //
        $imdbDirectedStartTag = '<h5>Director:</h5>';
        $imdbDirectedEndTag   = '</a><br/>';
        $this->directed_by = cleanString(strip_tags(extractStringFromString($searchPage, $imdbDirectedStartTag, $imdbDirectedEndTag)));
        //
        if ($this->directed_by === false) {
            $this->directed_by = $this->notavailable;;
        }
        //
        //IMDB WRITING CREDITS:
        //
        $imdbWritingCreditsStartTag = '<h5>Writers';
        $imdbWritingCreditsEndTag   = '</div>';
        $this->writing_credits = cleanString(strip_tags(str_replace("<br/>", " ", extractStringFromString($searchPage, $imdbWritingCreditsStartTag, $imdbWritingCreditsEndTag))));
        //
        if ($this->writing_credits === false) {
            $this->writing_credits = $this->notavailable;;
        }
        //
        //IMDB GENRE:
        //
        $imdbGenreStartTag = '<h5>Genre:</h5>';
        $imdbGenreEndTag   = '</div>';
        $this->genre = cleanString(strip_tags(extractStringFromString($searchPage, $imdbGenreStartTag, $imdbGenreEndTag)));
        //
        if ($this->genre === false || substr($this->genre, 0, 6) == '-//W3C' || substr($this->genre, 0, 6) == 'PUBLIC') {
            $this->genre = $this->notavailable;;
        }
        //
        //IMDB RATING:
        //
        $imdbRatingStartTag = '<div class="general rating">';
        $imdbRatingEndTag   = '</b>';
        $this->rating = cleanString(strip_tags(extractStringFromString($searchPage, $imdbRatingStartTag, $imdbRatingEndTag)));
        //
        if ($this->rating === false ||
            substr($this->rating, 1, 8) === 'awaiting') {
            $this->rating = '0/10';
        }
        //
        //IMDB RUNTIME:
        //
        $imdbRuntimeStartTag = '<h5>Runtime:</h5>';
        $imdbRuntimeEndTag   = '</div>';
        $this->runtime = cleanString(strip_tags(extractStringFromString($searchPage, $imdbRuntimeStartTag, $imdbRuntimeEndTag)));
        //
        if ($this->runtime === false ||
           substr($this->runtime, 0, 5) === 'UBLIC') {
            $this->runtime = '0 min';
        }
        //
        //IMDB COMMENT:
        //
        $imdbCommentStartTag = ' people found the following comment useful:-';
        $imdbCommentEndTag   = 'Was the above comment useful to you?';
        $this->comment = strip_tags(extractStringFromString($searchPage, $imdbCommentStartTag, $imdbCommentEndTag),'<br/> <br><p>');
        //
        if ($this->comment === false) {
            $this->comment = $this->notavailable;;
        }
        //
        //IMDB IMAGE:
        //
        /*
        $imdbPosterURLStartTag = '<img border="0" alt="'.$this->name.'" title="'.$this->name.'" src="';
        $imdbPosterURLEndTag   = '" />';
        $imdbPosterURL = cleanString(extractStringFromString($searchPage, $imdbPosterURLStartTag, $imdbPosterURLEndTag));
        //
        if ($imdbPosterURL === false) {
            echo $imdbPosterURL."\n";
            //$imdbPosterURL = $this->notavailable;;
            $this->poster_image = array($this->notavailable,'- not available at the moment -');
            $this->poster_image_link = $this->notavailable;
        }
        else {
            //
            $imdbImageExtension = getExt($imdbPosterURL);
            //copy($imdbImageLink,$this->instance_id.'_'."tempimdbimage");
            $instr = fopen($imdbPosterURL,"rb");
            if($instr !== False) {
                //
                $imdbImageData = '';
                while (!feof($instr)) {
                    $imdbImageData .= fread($instr, 8192);
                }
                //$imdbImageData = addslashes(fread($instr,filesize($imdbPosterURL)));
                fclose($instr);
                $imdbImageArray = array($imdbImageData,$imdbImageExtension);
                $this->poster_image = $imdbImageArray;
                $this->poster_image_link = $imdbPosterURL;
                //
            }
        }
        */
        //
    }
    //
    //CAST:
    //
    function getCast() {
        //
        $htmlString = file_get_contents($this->addressLink.'fullcredits');
        //
        $imdbCastStartTag = '<table class="cast">';
        $imdbCastEndTag   = '</table>';
        $imdbCastString = extractStringFromString($htmlString, $imdbCastStartTag, $imdbCastEndTag);
        $imdbCastArray1 = explode('<td class="nm">', $imdbCastString);
        $imdbCastArray2 = array();
        //
        for ($i=1; $i<count($imdbCastArray1); $i++) {
            //name id:
            $imdbCastNameID = extractStringFromString($imdbCastArray1[$i], 'nm', '/">');
            //name:
            $imdbCastName = extractStringFromString($imdbCastArray1[$i], 'nm'.$imdbCastNameID.'/">', '</a>');
            //character:
            $imdbCastCharacter = strip_tags(extractStringFromString($imdbCastArray1[$i], '<td class="char">', '</td>'));
            //
            $thisCastArray = array('name_id'=>$imdbCastNameID, 'name'=>$imdbCastName, 'character'=>$imdbCastCharacter);
            array_push($imdbCastArray2, $thisCastArray);
            //
        }
        //
        return $imdbCastArray2;
    }
    //function to display poster image:
    function displayPoster() {
        if ($this->poster_image_link == "- not available at the moment -") {
            echo $this->notavailable;;
        }
        else {
            echo '<img src="'.$this->poster_image_link.'" alt="'.$this->name.'"/>';
        }
    }

    //
    static public function find($title) {
      // sometimes 1080 is stuck at the end, should filter it out before the items are created
      // and ever end up at this function
      if(substr($title, -4) === '1080')
        $title = trim(substr($title, 0, -4));

      $title = strip_title($title);

      // If the title contains a year then remove it from search(imdb doesn't like it) and
      // adjust the returned page to include year in search
      if(preg_match('/(.*)(\d\d\d\d/)', $title, $regs) != 0)
      {
        $title = $regs[1];
        $title_year = $regs[2];
      }

      $findUrl = 'http://www.imdb.com/find?s=tt&q='.str_replace('%20', '+', urlencode($title));
      $file = new feedAdapter_File($findUrl, 10, 0);
      if(!$file->success)
        return False;
  
      $searchPage = html_entity_decode($file->body, ENT_QUOTES);

      if(isset($title_year)) 
      {
        $searchPage = preg_replace('/<\/a>\s+\((\d\d\d\d).*\)/Ui', ' ($1)</a>', $searchPage);
        $title .= " $title_year";
      }

      // Check if no matches were found
      if(strpos(strtolower($searchPage), "no matches") !== false)
        return False;

      $searchPage = substr($searchPage, strpos($searchPage, "Titles"));
      $matches = get_urls_from_html($searchPage, '\/title\/tt\d+\/');
      $index = best_match($title, $matches[2], $accuracy);

      if($accuracy <= 75)
        return False;

      $imdbFindStartTag = '<b>Popular Titles';
      $imdbFindEndTag   = '</table>';
      $imdbFindString = extractStringFromString($searchPage, $imdbFindStartTag, $imdbFindEndTag);
      $imdbFindArray1 = explode('<td valign="top"><a', $imdbFindString);
      $imdbFindArray2 = array();
      // pop off 'displaying x result' row
      array_shift($imdbFindArray1);

      foreach($imdbFindArray1 as $itemHtml) {
        //
        // Extract id
        //
        $imdbFindIdStartTag = 'href="/title/tt';
        $imdbFindIdEndTag = '/" ';
        $id = extractStringFromString($itemHtml, $imdbFindIdStartTag, $imdbFindIdEndTag);
        if(strlen($id) !== 7) {
          var_dump($itemHtml);
        }
        //
        // Extract Title
        //
        $imdbFindTitleStartTag = sprintf('link=/title/tt%07d/\';">', $id);
        $imdbFindTitleEndTag = '</a>';
        $title = extractStringFromString($itemHtml, $imdbFindTitleStartTag, $imdbFindTitleEndTag, true);
  
        // Add to final array
        $imdbFindArray2[] = array('id'=>$id, 'title'=>$title);
      }
      return $imdbFindArray2;
      //
      
  }
}

?> 
