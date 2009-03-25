<tr height=400><td>
  <form action="../updateFavorite/<?php echo $feedItem->id;?>" type="GET">
    <table>
      <tr>
        <td>Status:</td>
        <td><?php echo $feedItem->status; ?></td>
      </tr>
      <?php if($feedItem->matchingFavorite): ?>
        <tr>
          <td>Matching Fav:</td>
          <td><?php echo $feedItem->matchingFavorite; ?></td>
        </tr>
      <?php endif; ?>
      <tr>
        <td>Title:</td>
        <td><?php echo $feedItem->title; ?></td>
      </tr>
      <tr>
        <td>Pub Date:</td>
        <td><?php echo date('D M j g:ia', $feedItem->pubDate); ?></td>
      </tr>
      <tr>
        <td>Description:</td>
        <td><?php echo $feedItem->description; ?></td>
      </tr>
      <tr>
        <td>Detected Info: </td>
        <td>
          <?php if($feedItem->shortTitle): ?>
            <?php echo "{$feedItem->shortTitle} - {$feedItem->season}x{$feedItem->episode} - {$feedItem->quality}"; ?>
          <?php else: ?>
            Unable to parse Title
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="2"><font color="user2">
          <a href="<?php echo "../../addFavoriteByItem/".SimpleMvc::$arguments[0]."/".SimpleMvc::$arguments[1]; ?>">Add to Favorites</a>
          <a href="<?php echo "../../downloadFeedItem/".SimpleMvc::$arguments[0]."/".SimpleMvc::$arguments[1]; ?>">Download</a>
        </td>
    </table>
  </form>
</td></tr>
