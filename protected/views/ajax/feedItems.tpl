<?php $n=1;foreach($feedItems[$feed->id] as $feedItem): ?>
 <li class='torrent match_<?php echo reset(explode(' ',$feedItem->statusText)).(++$n%2?' alt':''); ?>' title='<?php echo CHtml::encode($feedItem->description); ?>'>
  <?php echo CHtml::link('', array('addFavorite', 'feedItem_id'=>$feedItem->id), array('class'=>'context_link')); ?>
  <?php echo CHtml::link('', array('dlFeedItem', 'id'=>$feedItem->id), array('class'=>'context_link')); ?>
  <span class='torrent_name'><?php echo CHtml::encode($feedItem->title); ?></span>
  <span class='torrent_pubDate'><?php echo CHtml::encode(date("Y M d h:i a", $feedItem->pubDate)); ?></span>
 </li>
<?php endforeach; ?>
