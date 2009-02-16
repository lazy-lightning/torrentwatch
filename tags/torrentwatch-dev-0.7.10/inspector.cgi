#!/usr/bin/php-cgi
<?php
ini_set('include_path', '.:./php');
require_once('TVDB.php');
require_once('guess.php');

function _die($errmsg) {
?>
<div class='tvshow'>
  <h2 id='tvshow_title' class='inspector_heading'><?php echo $errmsg; ?></h2>
  <form action="inspector.cgi" type="get">
    <input type="text" name="title" value="<?php echo $_GET['title'] ?>">
  </form>
</div>
<?php die();
}

if(!isset($_GET['title']))
	_die("Bad Query");
file_put_contents('/tmp/twlog', 'Inspecto Called: '.$_GET['title']."\n", FILE_APPEND);
$guess = guess_match($_GET['title'], TRUE);
if($guess === False)
	$guess = array('key' =>$_GET['title']);
	//_die("Couldn't guess ".$_GET['title']);

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

?>
<div class='tvshow'>
  <h2 id='tvshow_title' class='inspector_heading'>
    <?php echo $tvShow->seriesName; ?> - <?php echo $tvShow->network; ?>
  </h2>
  <ul id='tvshow_series'>
    <?php if(!empty($tvShow->daysOfWeek)): ?>
      <li class='item' id='tvshow_airday'>
        <?php echo $tvShow->daysOfWeek." ".$tvShow->airTime; ?>
      </li>
    <?php elseif (!empty($tvShow->dayOfWeek)): ?>
      <li class='item' id='tvshow_airday'>
        <?php echo $tvShow->dayOfWeek." ".$tvShow->airTime; ?>
      </li>
    <?php endif; ?>
    <?php if(!empty($tvShow->rating)): ?>
      <li class='item' id='tvshow_rating'>
        <?php echo $tvShow->rating ?> out of 10 stars
      </li>
    <?php endif; ?>
    <?php if(!empty($tvShow->genres)): ?>
      <li class='item' id='tvshow_genres'>
        <?php echo implode($tvShow->genres, " / "); ?>
      </li>
    <?php endif; ?>
    <?php if(!empty($tvShow->overview)): ?>
      <li class='item' id='tvshow_overview'>
        <?php echo $tvShow->overview; ?>
      </li>
    <?php endif; ?>
  </ul>
</div>
<?php if(!empty($tvEpisode)): ?>
  <div class='tvepisode'>
    <h2 id='tvepisode_title' class='inspector_heading'>
      <?php echo $tvEpisode->name; ?>
    </h2>
    <ul id='tvepisode'>
      <?php if(!empty($episode)): ?>
        <li id='tvepisode_number'><?php echo $episode; ?></li>
      <?php endif; ?>
      <?php if(!empty($tvEpisode->overview)): ?>
        <li id='tvepisode_overview'><?php echo $tvEpisode->overview; ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>
<div id='inspector_credits'>
  Results provided by <a href='http://www.thetvdb.com'>The TVDB</a>
</div>
