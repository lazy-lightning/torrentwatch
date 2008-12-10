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
if(!$tvShow)
	die("No Records in theTvDB");

if(preg_match('/(\d+)x(\d+)/i',$guess['episode'], $regs)) {
	$tvEpisode = $tvShow->getEpisode($regs['1'], $regs['2']);
	$episode = $regs['1']."x".$regs['2'];
}

echo "
<html>
  <head>
    <title>TV Show Inspector</title>
    <link rel='Stylesheet' type='text/css' href='css/torrentwatch.css?".time()."'></link>
  </head>
  <body>
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
if(!empty($tvEpisode)) echo "
    <div class='tvepisode'>
      <h2 id='tvepisode_title' class='inspector_heading'>".$tvEpisode->name."</h2>
      <ul id='tvepisode'>
        <li id='tvepisode_number'>$episode</li>
        <li id='tvepisode_overview'>".$tvEpisode->overview."</li>
      </ul>
    </div>";

echo "
  </body>
</html>";

?>
