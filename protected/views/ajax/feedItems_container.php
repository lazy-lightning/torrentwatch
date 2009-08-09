<div id="feedItems_container">
  <?php if(count($tabs) > 1): ?>
    <ul>
      <?php 
        foreach($tabs as $title => $type) {
          $url = '#'.$type.'_container';
          $htmlAttrs = array();
          if(false === isset($$type)) {
            $htmlAttrs['rel'] = $url;
            $url = array('loadFeedItems', 'type'=>$type);
          }
          echo '<li>'.CHtml::link("<span>$title</span>", $url, $htmlAttrs).'</li>'; 
        }
      ?>
      <li><a href="#search_container"><span>Search</span></a></li>
    </ul>
  <?php 
    endif; 
    foreach($tabs as $title => $type): 
      echo "<div class='feedItems' id='{$type}_container'><ul>";
      if(false === isset($$type)) {
        echo '</ul></div>';
        continue;
      }
      $n=0;
      foreach($$type as $item1) {
        $class = 'torrent';
        if(isset($item1['feedItem_title']))
        {
          $items = array($item1);
          $incr = 'n';
        }
        else
        {
          $items = $item1;
          $incr = 'm';
          $class .= ' duplicate';
          echo "<li class='torrent hasDuplicates match_".strtok(feedItem::getStatusText($items[0]['feedItem_status']), ' ').(++$n%2?' alt':'')."' ".
               "  <span class='torrent_name'>".CHtml::encode($items[0]['feedItem_title'])."</span>".
               "<ul class='duplicates'>";
          $m = $n;
        }

        foreach($items as $item2)
        {
          echo "<li class='{$class} match_".strtok(feedItem::getStatusText($item2['feedItem_status']), ' ').(++$$incr%2?' alt':' notalt')."' ".
               "    title='".CHtml::encode($item2['feedItem_description'])."'>".
               "  <input type='hidden' name='itemId' class='itemId' value='".$item2['feedItem_id']."'>".
               "  <span class='torrent_name'>".CHtml::encode($item2['feedItem_title'])."</span>".
               "  <span class='torrent_feed'>".CHtml::encode($item2['feed_title'])."</span>".
               "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $item2['feedItem_pubDate']))."</span>".
               "</li>";
        }
        if($incr === 'm')
          echo "</ul></li>";
      } 
      ?>
      <li class='torrent loadMore <?php echo (++$n%2?'alt':'notalt'); ?>'>
         <span class='torrent_name'>
           <?php echo isset($item2) ? CHtml::link('Load more '.$title, array('loadFeedItems', 'type'=>$type, 'before'=>$item2['feedItem_pubDate'])) : ''; ?>
         </span>
      </li>
    </ul>
  </div>
  <?php 
    endforeach; 
    if(count($tabs) > 1)
      include(VIEWPATH.'search_container.php');
  ?>
</div>

