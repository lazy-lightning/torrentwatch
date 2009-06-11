<div id="feedItems_container">
  <ul>
    <li><a href="#tvEpisodes_container"><span>Tv Episodes</span></a></li>
    <li><a href="#movies_container"><span>Movies</span></a></li>
    <li><a href="#others_container"><span>Others</span></a></li>
  </ul>
  <div id="tvEpisodes_container">
    <ul>
      <?php
        $n=0;
        $dlFeedItemLink = CHtml::link('', array('dlFeedItem', 'id'=>'{id}'), array('class'=>'context_link'));
        $addFavoriteLink =  CHtml::link('', array('addFavorite', 'feedItem_id'=>'{id}'), array('class'=>'context_link'));
        foreach($tvEpisodes as $tvEpisode) {
          echo "<li class='torrent_match_".reset(explode(' ', feedItem::getStatusText($tvEpisode['feedItem_status']))).(++$n%2?' alt':'')."' ".
               "    title='".CHtml::encode($tvEpisode['feedItem_description'])."'>".
              str_replace('{id}', $tvEpisode['feedItem_id'], $addFavoriteLink).
              str_replace('{id}', $tvEpisode['feedItem_id'], $dlFeedItemLink).
              "  <span class='torrent_name'>".CHtml::encode($tvEpisode['feedItem_title'])."</span>".
              "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $tvEpisode['feedItem_pubDate']))."</span>".
              "</li>";
        } ?>
    </ul>
  </div>
  <div id="movies_container">
    <ul>
      <?php
        $n=0;
        foreach($movies as $movie) {
          echo "<li class='torrent_match_".reset(explode(' ', feedItem::getStatusText($movie['feedItem_status']))).(++$n%2?' alt':'')."' ".
               "    title='".CHtml::encode($movie['feedItem_description'])."'>".
              str_replace('{id}', $movie['feedItem_id'], $addFavoriteLink).
              str_replace('{id}', $movie['feedItem_id'], $dlFeedItemLink).
             "  <span class='torrent_name'>".CHtml::encode($movie['feedItem_title'])."</span>".
              "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $movie['feedItem_pubDate']))."</span>".
              "</li>";
        } ?>
    </ul>
  </div>
  <div id="others_container">
    <ul>
      <?php 
        $n=0;
        foreach($others as $other) {
          echo "<li class='torrent_match_".reset(explode(' ', feedItem::getStatusText($other['feedItem_status']))).(++$n%2?' alt':'')."' ".
               "    title='".CHtml::encode($other['feedItem_description'])."'>".
              str_replace('{id}', $other['feedItem_id'], $addFavoriteLink).
              str_replace('{id}', $other['feedItem_id'], $dlFeedItemLink).
             "  <span class='torrent_name'>".CHtml::encode($other['feedItem_title'])."</span>".
              "  <span class='torrent_pubDate'>".CHtml::encode(date("Y M d h:i a", $other['feedItem_pubDate']))."</span>".
              "</li>";
        } ?>
    </ul>
  </div>
</div>

