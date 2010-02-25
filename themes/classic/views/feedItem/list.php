<ul>
  <?php 
    foreach($feeditemList as $n => $model) {
      echo "<li class='torrent match_".strtok(feedItem::getStatusText($model->status), ' ').($n%2?' alt':' notalt')."' ".
           "    title='".CHtml::encode($model->description)."'>";
      if($model->tvEpisode_id)
        $inspect = array('/tvEpisode/inspect', 'id'=>$model->tvEpisode_id);
      elseif($model->movie_id)
        $inspect = array('/movie/inspect', 'id'=>$model->movie_id);
      if(isset($inspect))
        echo CHtml::link('', $inspect, array('class'=>'loadInspector ajaxSubmit', 'title'=>'Get Detailed Media Information'));
      echo "<div class='itemButtons'>".
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
