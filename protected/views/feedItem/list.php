<ul>
  <?php 
    foreach($feeditemList as $n=>$model) {
      echo "<li class='torrent match_".strtok($model->getStatusText(), ' ').(++$n%2?' alt':' notalt')."' ".

           "    title='".CHtml::encode($model->description)."'>".
           "  <input type='hidden' name='itemId' class='itemId' value='".$model->id."'>".
           "  <span class='torrent_name'>".CHtml::encode($model->title)."</span>".
           "  <span class='torrent_feed'>".CHtml::encode($model->feed->title)."</span>".
           "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $model->pubDate))."</span>".
           "</li>";
    } 
  ?>
</ul>
<br/>
<?php /*$this->widget('CLinkPager',array('pages'=>$pages));*/ ?>
