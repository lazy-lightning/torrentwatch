<ul id="tv_container" class='loadContent'>
<?php
function fakenew($class)
{
  // from PHPUnit_Framework_TestCase::getMock()
  return unserialize(sprintf('O:%d:"%s":0:{}',strlen($class), $class));
}
// This is the first view loaded when opening the page, so most of the 
// convenience methods were skipped to speed up rendering     
$feedItem = fakenew('feedItem');
$tvEpisode = fakenew('tvEpisode');
$baseurl = Yii::app()->request->getScriptUrl();
$charset = Yii::app()->charset;
foreach($tvepisodeList as $n => $row) {
  $id = $row['id'];
  $epString = htmlspecialchars($tvEpisode->getEpisodeString($row['season'], $row['episode']), ENT_QUOTES, $charset);
  $status = strtok($feedItem->getStatusText($row['feedItem_status']), ' ');
  $alt = $n%2?' alt':' notalt';
  $title = htmlspecialchars($row['tvShow_title'], ENT_QUOTES, $charset);
  $epTitle = empty($row['title'])?'':(":  <span class='epTitle'>".htmlspecialchars($row['title'], ENT_QUOTES, $charset)."</span>");
  $pubDate = date("M d h:i a", $row['lastUpdated']);
  $i = "$baseurl?r=tvEpisode/inspect&id=$id";
  $l = "$baseurl?r=tvEpisode/list&id=$id";
  $s = "$baseurl?r=tvEpisode/startDownload&id=$id";
  $m = "$baseurl?r=tvEpisode/makeFavorite&id=$id";
  $h = "$baseurl?r=tvShow/hide&id=".$row['tvShow_id'];
  echo <<<EOD
<li id='tvEpisode-$id' class='torrent hasDuplicates match_$status $alt'>
  <a href='$i' class='loadInspector ajaxSubmit' title='Get Detailed Media Information'></a>
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
 
