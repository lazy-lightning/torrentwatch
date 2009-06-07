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
    while ($searchChar != $charToFind) {
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

        $searchPage = $file->body;
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
        if ($this->genre === false) {
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
            substr($this->rating, 1, 9) === 'awaiting') {
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
}

class IMDBFinder {
  static public function find($title) {
    //
    $findUrl = 'http://www.imdb.com/find?s=tt&q='.$title.'&x=0&y=0'
    //
    $file = new feedAdapter_File($this->findUrl, 10, 0);
    $this->success = $file->success;
    if(!$this->success)
      return;

    $searchPage = $file->body;
    //

    $imdbFindStartTag = '<b>Popular Titles';
    $imdbFindEndTag   = '</table>';
    $imdbFindString = extractStringFromString($htmlString, $imdbFindStartTag, $imdbFindEndTag);
    $imdbFindArray1 = explode('<td valign="top">\n<a', $imdbFindString);
    $imdbFindArray2 = array();
    foreach($imdbFindArray1 as $itemHtml) {
      //
      // Extract id
      //
      $imdbFindIdStartTag = '<a href="/title/tt';
      $imdbFindIdEndTag = '" ';
      $id = extractStringFromString($itemHtml, $imdbFindIdStartTag, $imdbFindIdEndTag);
      //
      // Extract Title
      //
      $imdbFindTitleStartTag = sprintf('link=/title/tt%07d/\';">', $id);
      $imdbFindTitleEndTag = '</a>';
      $title = extractStringFromString($itemHtml, $imdbFindTitleStartTag, $imdbFindTitleEndTag, true);

      // Add to final array
      $imdbFindArray2[] = array('id'=>$id, 'title'=>$title);
      
    //
  }
}

?> 
