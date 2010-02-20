<ul id="movie_container">
<?php 
foreach($movieList as $n => $model) {
  echo "<li id='movie-".$model->id."' class='torrent hasDuplicates match_".strtok($model->feedItem[0]->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
     CHtml::link('', array('inspect', 'id'=>$model->id), array('class'=>'loadInspector ajaxSubmit', 'title'=>'Get More Information')).
     "<div class='itemButtons'>".
       CHtml::link('Related FeedItems', array('/feedItem/list', 'related'=>'movie', 'id'=>$model->id),
           array('class'=>'loadDuplicates')).
       CHtml::link('&nbsp;', array('startDownload', 'id'=>$model->id), 
           array('class'=>'startDownload ajaxSubmit', 'title'=>'Start Download')).
       CHtml::link('&nbsp',  array('makeFavorite', 'id'=>$model->id), 
           array('class'=>'makeFavorite ajaxSubmit', 'title'=>'Make Favorite')).
     "</div><div class='itemDetails'>".
     "  <span class='name'>".CHtml::encode($model->fullTitle)."</span>".
     "  <span class='rating'>".CHtml::encode($model->rating)." / 100</span>".
     "</div></li>";
} ?>
</ul>
 
