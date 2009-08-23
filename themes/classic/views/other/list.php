<div id="feedItems_container">
  <div id="tv_container">
    <ul>
    <?php 
    foreach($tvepisodeList as $n => $model) {
      echo "<li class='torrent hasDuplicates match_".strtok(feedItem::getStatusText($model->status), ' ').($n%2?' alt':' notalt')."' >".
           "  <input type='hidden' name='itemId' class='itemId' value='tvEpisode-".$model->id."'>".
           "  <span class='name'>".CHtml::encode($model->tvShow->title)."</span>".
           "  <span class='episode'>".CHtml::encode($model->getEpisodeString())."</span>".
           (empty($model->title)?'':"  <span class='epTitle'>".CHtml::encode($model->title)."</span>").
           "</li>";
    } ?>
    <ul>
  </div>
</div>
 
