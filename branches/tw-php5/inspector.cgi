#!/usr/bin/php-cgi
<?php
ini_set('include_path', '.:./php');
require_once('TVDB.php');
require_once('guess.php');

if(!isset($_GET['title']))
	die("Bad Query");
$guess = guess_match($_GET['title'], TRUE);
if($guess === False)
	die("Couldn't guess ".$_GET['title']);

$tvShows = TV_Shows::search($guess['key']);
$tvShow = $tvShows[0];

if(preg_match('/(\d+)x(\d+)/i',$guess['episode'], $regs)) {
	$tvEpisode = $tvShow->getEpisode($regs['1'], $regs['2']);
	$episode = $tvEpisode->season."x".$tvEpisode->episode;
}

echo "
<html>
  <head>
    <title>TV Show Inspector</title>
    <link rel='Stylesheet' type='text/css' href='css/inspector.css?".time()."'></link>
  </head>
  <body>
    <div class='tvshow'>
      <h2 id='tvshow_title'>".$tvShow->seriesName." - ".$tvShow->network."</h2>
      <ul id='tvshow_series'>
        <li class='item' id='tvshow_airday'>".$tvShow->daysOfWeek." ".$tvShow->airTime."</li>
        <li class='item' id='tvshow_rating'>".$tvShow->rating." out of 10 stars</li>
        <li class='item' id='tvshow_genres'>".implode($tvShow->genres, " / ")."</li>
        <li class='item' id='tvshow_overview'>".$tvShow->overview."</li>
      </ul>
    </div>";
if(isset($tvEpisode)) {
	echo "
    <div class='tvepisode'>
      <h2 id='tvepisode_title'>".$tvEpisode->name."</h2>
      <ul id='tvepisode'>
        <li id='tvepisode_number'>$episode</li>
        <li id='tvepisode_overview'>".$tvEpisode->overview."</li>
      </ul>
    </div>";
}
echo "
  </body>
</html>";

?>
