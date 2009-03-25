<?php foreach(array_reverse($feed->getFeedItem()) as $feedItem): ?>
 <li class='torrent match_<?php echo $feedItem->status; ?>' title='<?php echo htmlspecialchars($feedItem->description, ENT_NOQUOTES); ?>'>
  <a class='context_link' href='<?php echo $baseuri.'/ajax/matchTitle/'.$feed->id.'/'.$feedItem->id; ?>'></a>
  <a class='context_link' href='<?php echo $baseuri.'/ajax/dlTorrent/'.$feed->id.'/'.$feedItem->id; ?>'></a>
  <span class='torrent_name'><?php echo $feedItem->title; ?></span>
  <span class='torrent_pubDate'><?php echo date("Y M d h:i a", $feedItem->pubDate); ?></span>
 </li>
<?php endforeach; ?>
