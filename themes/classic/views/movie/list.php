<ul id="movie_container">
<?php 
foreach($movieList as $n => $model) {
echo "<li id='tvEpisode-".$model->id."' class='torrent hasDuplicates match_".strtok($model->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
     "<div class='itemButtons'>".
       CHtml::link(CHtml::image('images/tor_start.png', 'Start', array('height'=>10)),
           array('startDownload', 'id'=>$model->id), array('class'=>'startDownload ajaxSubmit')).
       CHtml::link(CHtml::image('images/tor_fav.png', 'Favorite', array('height'=>10)),
           array('makeFavorite', 'id'=>$model->id), array('class'=>'makeFavorite ajaxSubmit')).
     "</div><div class='hideButton'>".
       CHtml::link(CHtml::image('images/hide.png', 'Hide'),
         array('hideTvShow', 'id'=>$model->id), array('class'=>'hideTvShow ajaxSubmit')).
     "</div><div class='itemDetails'>".
     "  <span class='name'>".CHtml::encode($model->title)."</span>".
     "</div></li>";
} ?>
<ul>
 
