<ul id="movie_container">
<?php 
foreach($movieList as $n => $model) {
  echo "<li id='movie-".$model->id."' class='torrent hasDuplicates match_".strtok($model->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
     CHtml::link('', array('inspect', 'id'=>$model->id), array('class'=>'loadInspector ajaxSubmit', 'title'=>'Get More Information')).
     "<div class='itemButtons'>".
       CHtml::link('Related FeedItems', array('/feedItem/list', 'related'=>'movie', 'id'=>$model->id),
           array('class'=>'loadDuplicates')).
       CHtml::link(CHtml::image('images/tor_start.png', 'Start', array('height'=>10)),
           array('startDownload', 'id'=>$model->id), array('class'=>'startDownload ajaxSubmit', title=>'Start Download')).
       CHtml::link(CHtml::image('images/tor_fav.png', 'Favorite', array('height'=>10)),
           array('makeFavorite', 'id'=>$model->id), array('class'=>'makeFavorite ajaxSubmit', 'title'=>'Make Favorite')).
     "</div><div class='itemDetails'>".
     "  <span class='name'>".CHtml::encode($model->title)."</span>".
     "</div></li>";
} ?>
<ul>
 
