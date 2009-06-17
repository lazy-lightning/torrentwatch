<div id="feedItems_container">
  <ul>
    <li><a href="#tvEpisodes_container"><span>Tv Episodes</span></a></li>
    <li><a href="#movies_container"><span>Movies</span></a></li>
    <li><a href="#others_container"><span>Others</span></a></li>
  </ul>
  <?php foreach(array('tvEpisodes', 'movies', 'others') as $type): ?>
  <div class="feedItems" id="<?php echo $type; ?>_container">
    <ul>
      <?php
        $n=0;
        foreach($$type as $item1) {
          if(isset($item1['feedItem_title']))
          {
            echo "<li class='torrent match_".strtok(feedItem::getStatusText($item1['feedItem_status']), ' ').(++$n%2?' alt':'')."' ".
                 "    title='".CHtml::encode($item1['feedItem_description'])."'>".
                 "  <input type='hidden' name='itemId' class='itemId' value='".$item1['feedItem_id']."'>".
                 "  <span class='torrent_name'>".CHtml::encode($item1['feedItem_title'])."</span>".
                 "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $item1['feedItem_pubDate']))."</span>".
                 "</li>";
          }
          else
          {
            echo "<li class='torrent hasDuplicates match_".strtok(feedItem::getStatusText($item1[0]['feedItem_status']), ' ').(++$n%2?' alt':'')."' ".
                 "  <span class='torrent_name'>".CHtml::encode($item1[0]['feedItem_title'])."</span>".
                 "<ul class='duplicates'>";
            $m=$n;
            foreach($item1 as $item2)
            {
              echo "<li class='torrent duplicate match_".strtok(feedItem::getStatusText($item2['feedItem_status']), ' ').(++$m%2?' alt':' notalt')."' ".
                   "    title='".CHtml::encode($item2['feedItem_description'])."'>".
                   "  <input type='hidden' name='itemId' class='itemId' value='".$item2['feedItem_id']."'>".
                   "  <span class='torrent_feed'>".CHtml::encode($item2['feed_title'])."</span>".
                   "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $item2['feedItem_pubDate']))."</span>".
                   "</li>";
            }
            echo "</ul></li>";
          }
        } 
      ?>
    </ul>
  </div>
  <?php endforeach; ?>
</div>

