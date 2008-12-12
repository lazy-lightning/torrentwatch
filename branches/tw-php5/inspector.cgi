#!/usr/bin/php-cgi
<?php
ini_set('include_path', '.:./php');
require_once('TVDB.php');
require_once('guess.php');

function _die($errmsg) {
	echo "
<body>
  <head>
    <title>Inspector Error</title>
    <script type='text/javascript' src='javascript/torrentwatch.js'>
  </head>
  <body>
    <div id='inspector_container'>$errmsg</div>
    <script type='text/javascript'>
      updateFrameCopyDiv('inspector_container');
      updateFrameFinished();
    </script>
  </body>";
	die();
}

if(!isset($_GET['title']))
	_die("Bad Query");
$guess = guess_match($_GET['title'], TRUE);
if($guess === False)
	_die("Couldn't guess ".$_GET['title']);

if(stristr($guess['key'], 'and') !== FALSE)
	$guess2 = strtr(strtolower($guess['key']), array("and" => '&'));

$tvShows = TV_Shows::search($guess['key']);
if(!$tvShows && isset($guess2))
	$tvShows = TV_Shows::search($guess2);
$tvShow = $tvShows[0];
if(!$tvShow)
	_die("No Records in theTvDB");

if(preg_match('/(\d+)x(\d+)/i',$guess['episode'], $regs)) {
	$tvEpisode = $tvShow->getEpisode($regs['1'], $regs['2']);
	$episode = $regs['1']."x".$regs['2'];
}

echo "
<html>
  <head>
    <title>TV Show Inspector</title>
    <script type='text/javascript' src='javascript/torrentwatch.js'></script>
  </head>
  <body>
    <div id='inspector_container'>
      <div class='tvshow'>
        <h2 id='tvshow_title' class='inspector_heading'>".$tvShow->seriesName." - ".$tvShow->network."</h2>
        <ul id='tvshow_series'>";
if(!empty($tvShow->daysOfWeek)) echo "
          <li class='item' id='tvshow_airday'>".$tvShow->daysOfWeek." ".$tvShow->airTime."</li>";
else if (!empty($tvShow->dayOfWeek)) echo "
          <li class='item' id='tvshow_airday'>".$tvShow->dayOfWeek." ".$tvShow->airTime."</li>";
if(!empty($tvShow->rating)) echo "
          <li class='item' id='tvshow_rating'>".$tvShow->rating." out of 10 stars</li>";
if(!empty($tvShow->genres)) echo "
          <li class='item' id='tvshow_genres'>".implode($tvShow->genres, " / ")."</li>";
if(!empty($tvShow->overview)) echo "
          <li class='item' id='tvshow_overview'>".$tvShow->overview."</li>";
echo "
        </ul>
      </div>";
if(!empty($tvEpisode)) {
	echo "
      <div class='tvepisode'>
        <h2 id='tvepisode_title' class='inspector_heading'>".$tvEpisode->name."</h2>
        <ul id='tvepisode'>";
	if(!empty($episode)) echo "          <li id='tvepisode_number'>$episode</li>";
	if(!empty($tvEpisode->overview)) echo "          <li id='tvepisode_overview'>".$tvEpisode->overview."</li>";
	echo "
        </ul>
      </div>";
}

echo "
      <div id='inspector_credits'>
        Results provided by <a href='http://www.thetvdb.com'>The TVDB</a>
      </div>
    </div>
    <script type='text/javascript'>
      updateFrameCopyDiv('inspector_container');
      updateFrameFinished();
    </script>
  </body>
</html>";

?>
