<li class='torrent <?php echo "match_$matched $alt"; ?>' title='<?php echo _isset($item, 'description'); ?>'>
<a class='context_link' 
   href='<?php echo "{$_SERVER['PHP_SELF']}/matchTitle?rss=$feed&title=$utitle"; ?>'>
</a>
<a class='context_link' 
   href='<?php echo $_SERVER['PHP_SELF']; ?>/dlTorrent?title=<?echo $utitle; ?>&link=<?php echo $ulink; ?>'>
</a>
<span class='torrent_pubDate'><?php echo _isset($item, 'pubDate'); ?></span>
<span class='torrent_name'><?php echo $title; ?></span>
</li>
