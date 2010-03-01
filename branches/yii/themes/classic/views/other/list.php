<ul id="other_container">
<?php
foreach($otherList as $n => $model) {
  echo "<li id='other-".$model->id."' class='torrent hasDuplicates match_".strtok($model->feedItem[0]->getStatusText(), ' ').($n%2?' alt':' notalt')."' >".
       "<div class='itemButtons'>".
         CHtml::link('Related FeedItems', array('/feedItem/list', 'related'=>'other', 'id'=>$model->id),
             array('class'=>'loadDuplicates')).
         CHtml::link('&nbsp;', array('startDownload', 'id'=>$model->id), 
             array('class'=>'startDownload ajaxSubmit', 'title'=>'Start Download')).
         CHtml::link('&nbsp;', array('makeFavorite', 'id'=>$model->id), 
             array('class'=>'makeFavorite ajaxSubmit', 'title'=>'Make Favorite')).
       "</div><div class='itemDetails'>".
       "  <span class='name'>".CHtml::encode($model->title)."</span>".
       "  <span class='torrent_pubDate'>".CHtml::encode(date("M d h:i a", $model->lastUpdated))."</span>".
       "</div></li>";
} ?>

<ul>
