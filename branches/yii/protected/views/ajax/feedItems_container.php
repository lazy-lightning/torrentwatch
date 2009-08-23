<div id="feedItems_container">
<?php
  // attempts to speed up since this function uses 45% of a the load time for ajax/loadFeedItems request w/ 50 items
  // c=class,n=increment
  function outputItem($item, $c, &$n) {
    static $charset = null;
    if($charset === null) $charset = Yii::app()->charset;
    unset($item['count']);
    // extracts:
    //   count(maybee),feed_title,feedItem_status,feedItem_description
    //   feedItem_id,feedItem_title,feedItem_pubDate
    extract($item);
    $s = strtok(feedItem::getStatusText($feedItem_status, ''), ' ').(++$n%2?' alt':' notalt');
    $d = htmlspecialchars($feedItem_description, ENT_QUOTES, $charset);
    $t = htmlspecialchars($feedItem_title, ENT_QUOTES, $charset);
    $ft = htmlspecialchars($feed_title, ENT_QUOTES, $charset);
    $p = htmlspecialchars(date("Y M d h:i a", $feedItem_pubDate), ENT_QUOTES, $charset); // does this need to be encoded?

    echo "<li class='$c match_$s' title='$t'><input type='hidden' name='itemId' class='itemId' value='$feedItem_id'><span class='torrent_name'>$t</span><span class='torrent_feed'>$ft</span><span class='torrent_pubDate'>$p</span></li>";
  } 

  $app = Yii::app();
  echo "<div class='feedItems' id='{$type}_container'><ul>";
  $n=0;
  foreach($items as $item1) {
    $class = 'torrent';
    if(isset($item1['feedItem_title']))
    {
      outputItem($item1, $class, $n);
    }
    else
    {
      $items = $item1;
      $class .= ' duplicate';
      echo "<li class='torrent hasDuplicates match_".strtok(feedItem::getStatusText($items[0]['feedItem_status']), ' ').(++$n%2?' alt':'')."' ".
           "  <span class='torrent_name'>".htmlspecialchars($items[0]['feedItem_title'],ENT_QUOTES,$app->charset)."</span>".
           "<ul class='duplicates'>";
      $m = $n;

      foreach($items as $item2)
      {
        outputItem($item2, $class, $m);
      }
      echo "</ul></li>";
    }
  } 
  ?>
  <li class='torrent loadMore <?php echo (++$n%2?'alt':'notalt'); ?>'>
     <span class='torrent_name'>
       <?php echo isset($item2) ? CHtml::link('Load more '.$name, array('loadFeedItems', 'type'=>$type, 'before'=>$item2['feedItem_pubDate'])) : ''; ?>
     </span>
  </li>
</ul></div>
</div>
