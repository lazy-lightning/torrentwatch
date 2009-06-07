<div id="feedItems_container">
 <?php 
  if($feeds) {
    echo '<ul>';
    foreach($feeds as $feed) { 
      if($feed->id==0)continue;
      echo '<li>'.
           CHtml::link('<span>'.$feed->getTitle().'</span>', '#feed-'.$feed->id).
           '</li>';
    }
    echo '</ul>';
    foreach($feeds as $feed) {
      if($feed->id==0) continue;
      include VIEWPATH.'feedItems.tpl';
    }
  } ?>
</div>

