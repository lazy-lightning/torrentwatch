<ul id="tv_container" class='loadContent'>
<?php 
foreach($tvepisodeList as $n => $model) {
  echo "<li id='tvEpisode-".$model->id."' class='torrent hasDuplicates match_".strtok($model->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
       CHtml::link('', array('show', 'id'=>$model->id)).
       "  <span class='name'>".CHtml::encode($model->tvShow->title)."</span>".
       "  <span class='episode'>".CHtml::encode($model->getEpisodeString())."</span>".
       (empty($model->title)?'':"  <span class='epTitle'>".CHtml::encode($model->title)."</span>").
       "</li>";
} ?>
<ul>
 
