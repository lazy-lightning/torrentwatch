<ul id="tv_container" class='loadContent'>
<?php 
// This is the first view loaded when opening the page, so most of the 
// convenience methods were skipped to speed up rendering     
$url = Yii::app()->getUrlManager();
$feedItem = feedItem::model();
$tvEpisode = tvEpisode::model();
$inspect = $url->createUrl('tvEpisode/inspect', array('id'=>'{id}'));
$list = $url->createUrl('feedItem/list', array('related'=>'tvEpisode', 'id'=>'{id}'));
$start = $url->createUrl('tvEpisode/startDownload', array('id'=>'{id}'));
$makeFav = $url->createUrl('tvEpisode/makeFavorite', array('id'=>'{id}'));
$hide = $url->createUrl('tvShow/hide', array('tid'=>'{tid}'));
foreach($tvepisodeList as $n => $row) {
  $id = $row['id'];
  $epString = CHtml::encode($tvEpisode->getEpisodeString($row['season'], $row['episode']));
  $status = strtok($feedItem->getStatusText($row['feedItem_status']), ' ');
  echo "<li id='tvEpisode-$id' class='torrent hasDuplicates match_$status".($n%2?' alt':' notalt')."' >".
         "<a href='".str_replace('{id}', $id, $inspect)."' class='loadInspector ajaxSubmit' title='Get More Information'></a>".
         "<div class='itemButtons'>".
           "<a href='".str_replace('{id}', $id, $list)."' class='loadDuplicates'>Related FeedItems</a>".
           "<a href='".str_replace('{id}', $id, $start)."' class='startDownload ajaxSubmit' title='Start Download'>&nbsp;</a>".
           "<a href='".str_replace('{id}', $id, $makeFav)."' class='makeFavorite ajaxSubmit' title='Make Favorite'>&nbsp;</a>".
         "</div><div class='hideButton'>".
           "<a href='".str_replace('{tid}', $row['tvShow_id'], $hide)."' class='hideTvShow ajaxSubmit'>&nbsp;</a>".
         "</div><div class='itemDetails'>".
         "  <span class='name'>".CHtml::encode($row['tvShow_title'])."</span>".
         " - <span class='episode'>$epString</span>".
         (empty($row['title'])?'':(":  <span class='epTitle'>".CHtml::encode($row['title'])."</span>")).
       "</div></li>";
} ?>
</ul>
 
