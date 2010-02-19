<ul id="tvshow_container" class='loadContent'>
<?php 
foreach($tvshowList as $n => $model) {
  echo "<li id='tvShow-".$model->id."' class='torrent ".($n%2?' alt':' notalt')."' >".
       CHtml::link('', array('/tvEpisode/list', 'tvShow'=>$model->id)).
       "  <span class='name'>".CHtml::encode($model->title)."</span>".
       "</li>";
} ?>
</ul>
 
