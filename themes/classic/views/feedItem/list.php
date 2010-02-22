<ul>
  <?php 
    foreach($feeditemList as $n => $model) {
      echo "<li class='torrent match_".strtok(feedItem::getStatusText($model->status), ' ').($n%2?' alt':' notalt')."' ".
           "    title='".CHtml::encode($model->description)."'>".
           "<div class='itemButtons'>".
             CHtml::link('&nbsp;', array('startDownload', 'id'=>$model->id), 
                 array('class'=>'startDownload ajaxSubmit')).
             CHtml::link('&nbsp;', array('makeFavorite', 'id'=>$model->id), 
                 array('class'=>'makeFavorite ajaxSubmit')).
           "</div><div class='itemDetails'>".
           "  <span class='torrent_name'>".CHtml::encode($model->title)."</span>".
           "  <span class='torrent_feed'>".CHtml::encode($model->feed->title)."</span>".
           "  <span class='torrent_pubDate'>".CHtml::encode(date("M d h:i a", $model->pubDate))."</span>".
           "</div></li>";
    }
  ?>
</ul>
