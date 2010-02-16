<ul id="tv_container" class='loadContent'>
<?php 
// This is the first view loaded when opening the page, so most of the 
// convenience methods were skipped to speed up rendering     
$url = Yii::app()->getUrlManager();
foreach($tvepisodeList as $n => $model) {
  echo "<li id='tvEpisode-".$model->id."' class='torrent hasDuplicates match_".strtok($model->feedItem[0]->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
         "<a href='".$url->createUrl('tvEpisode/inspect', array('id'=>$model->id)),"' class='loadInspector ajaxSubmit' title='Get More Information'></a>".
         "<div class='itemButtons'>".
           "<a href='".$url->createUrl('feedItem/list', array('related'=>'tvEpisode', 'id'=>$model->id))."' class='loadDuplicates'>Related FeedItems</a>".
           "<a href='".$url->createUrl('tvEpisode/startDownload', array('id'=>$model->id))."' class='startDownload ajaxSubmit' title='Start Download'>&nbsp;</a>".
           "<a href='".$url->createUrl('tvEpisode/makeFavorite', array('id'=>$model->id))."' class='makeFavorite ajaxSubmit' title='Make Favorite'>&nbsp;</a>".
         "</div><div class='hideButton'>".
           "<a href='".$url->createUrl('tvShow/hide', array('id'=>$model->tvShow_id))."' class='hideTvShow ajaxSubmit'>&nbsp;</a>".
         "</div><div class='itemDetails'>".
         "  <span class='name'>".(empty($model->tvShow) ? $model->tvShow_id : CHtml::encode($model->tvShow->title))."</span>".
         " - <span class='episode'>".CHtml::encode($model->getEpisodeString())."</span>".
         (empty($model->title)?'':(":  <span class='epTitle'>".CHtml::encode($model->title)."</span>")).
       "</div></li>";
} ?>
<ul>
 
