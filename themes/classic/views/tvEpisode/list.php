<ul id="tv_container" class='loadContent'>
<?php 
// This is the first view loaded when opening the page, so most of the 
// convenience methods were skipped to speed up rendering     
$url = Yii::app()->getUrlManager();
foreach($tvepisodeList as $n => $model) {
  echo "<li id='tvEpisode-".$model->id."' class='torrent hasDuplicates match_".strtok($model->asa('statusText')->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
         "<div class='itemButtons'>".
           "<a href='".$url->createUrl('tvEpisode/startDownload', array('id'=>$model->id))."' class='startDownload ajaxSubmit'>".
           "<img src='images/tor_start.png' alt='Start' height='10'></a>".
           "<a href='".$url->createUrl('tvEpisode/makeFavorite', array('id'=>$model->id))."' class='makeFavorite ajaxSubmit'>".
           "<img src='images/tor_fav.png' alt='Favorite' height='10'></a>".
         "</div><div class='hideButton'>".
           "<a href='".$url->createUrl('tvEpisode/hide', array('id'=>$model->id))."' class='hideTvShow ajaxSubmit'>".
           "<img src='images/hide.png' alt='Hide'></a>".
         "</div><div class='itemDetails'>".
         "  <span class='name'>".(empty($model->tvShow) ? $model->tvShow_id : CHtml::encode($model->tvShow->title))."</span>".
         " - <span class='episode'>".CHtml::encode($model->getEpisodeString())."</span>".
         (empty($model->title)?'':":  <span class='epTitle'>".CHtml::encode($model->title)."</span>").
       "</div></li>";
} ?>
<ul>
 
