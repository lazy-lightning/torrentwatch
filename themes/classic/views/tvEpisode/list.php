<ul id="tv_container" class='loadContent'>
<?php
function fakenew($class)
{
  // from PHPUnit_Framework_TestCase::getMock()
  return unserialize(sprintf('O:%d:"%s":0:{}',strlen($class), $class));
}
// This is the first view loaded when opening the page, so most of the 
// convenience methods were skipped to speed up rendering     
$url = Yii::app()->getUrlManager();
$feedItem = fakenew('feedItem');
$tvEpisode = fakenew('tvEpisode');
$inspect = $url->createUrl('tvEpisode/inspect', array('id'=>'{id}'));
$list = $url->createUrl('feedItem/list', array('related'=>'tvEpisode', 'id'=>'{id}'));
$start = $url->createUrl('tvEpisode/startDownload', array('id'=>'{id}'));
$makeFav = $url->createUrl('tvEpisode/makeFavorite', array('id'=>'{id}'));
$hide = $url->createUrl('tvShow/hide', array('tid'=>'{tid}'));
foreach($tvepisodeList as $n => $row) {
  $id = $row['id'];
  $epString = CHtml::encode($tvEpisode->getEpisodeString($row['season'], $row['episode']));
  $status = strtok($feedItem->getStatusText($row['feedItem_status']), ' ');
  $alt = $n%2?' alt':' notalt';
  $title = CHtml::encode($row['tvShow_title']);
  $epTitle = empty($row['title'])?'':(":  <span class='epTitle'>".CHtml::encode($row['title'])."</span>");
  $pubDate = date("M d h:i a", $row['lastUpdated']);
  $l = str_replace('%7Bid%7D', $id, $list);
  $s = str_replace('%7Bid%7D', $id, $start);
  $m = str_replace('%7Bid%7D', $id, $makeFav);
  $h = str_replace('%7Btid%7D', $row['tvShow_id'], $hide);
  echo <<<EOD
<li id='tvEpisode-$id' class='torrent hasDuplicates match_$status $alt'>
  <a href='".str_replace('%7Bid%7D', $id, $inspect)."' class='loadInspector ajaxSubmit' title='Get Detailed Media Information'></a>
  <div class='itemButtons'>
    <a href='$l' class='loadDuplicates'>Related FeedItems</a>
    <a href='$s' class='startDownload ajaxSubmit' title='Start Download'>&nbsp;</a>
    <a href='$m' class='makeFavorite ajaxSubmit' title='Make Favorite'>&nbsp;</a>
  </div><div class='hideButton'>
    <a href='$h' class='hideTvShow ajaxSubmit' title='Hide from listing'>&nbsp;</a>
  </div><div class='itemDetails'>
    <span class='name'>$title</span>
    <span class='episode'>$epString</span>
    $epTitle
    <span class='torrent_pubDate'>$pubDate</span>
  </div>
</li>
EOD
  ;
} ?>
</ul>
 
