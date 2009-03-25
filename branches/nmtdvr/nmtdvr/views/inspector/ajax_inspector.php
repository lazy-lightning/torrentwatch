<div class='tvshow'>
  <h2 id='tvshow_title' class='inspector_heading'>
    <?php 
      if(isset($error))
        echo $error;
      else
        echo $tvShow->seriesName." - ".$tvShow->network;
    ?>
  </h2>
  <ul id='tvshow_series'>
    <? if(!empty($tvShow->daysOfWeek)): ?>
      <li class='item' id='tvshow_airday'><?php echo $tvShow->daysOfWeek." ".$tvShow->airTime; ?></li>
    <? elseif (!empty($tvShow->dayOfWeek)): ?>
      <li class='item' id='tvshow_airday'><?php echo $tvShow->dayOfWeek." ".$tvShow->airTime; ?></li>
    <?php endif; ?>
    <? if(!empty($tvShow->rating)): ?>
      <li class='item' id='tvshow_rating'><?php echo $tvShow->rating; ?> out of 10 stars</li>
    <?php endif; ?>
    <? if(!empty($tvShow->genres)): ?>
      <li class='item' id='tvshow_genres'><?php echo implode($tvShow->genres, " / "); ?></li>
    <?php endif; ?>
    <? if(!empty($tvShow->overview)): ?>
      <li class='item' id='tvshow_overview'><?php echo $tvShow->overview; ?></li>
    <?php endif; ?>
  </ul>
</div>
<?php if(!empty($tvEpisode)): ?>
  <div class='tvepisode'>
    <h2 id='tvepisode_title' class='inspector_heading'><?php echo $tvEpisode->name; ?></h2>
    <ul id='tvepisode'>
    <?php if(!empty($episode)): ?>
      <li id='tvepisode_number'>$episode</li>
    <?php endif; ?>
    <?php if(!empty($tvEpisode->overview)): ?>
      <li id='tvepisode_overview'><?php echo $tvEpisode->overview; ?></li>
    <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>
<div id='inspector_credits'>
  <form action="<?php echo SimpleMvc::$base_uri; ?>/inspector/ajax" type="get">
    <input type="text"  name="title" value="<?php echo isset($title)?$title:''; ?>">
  </form>
  <p>Results provided by <a href='http://www.thetvdb.com'>The TVDB</a></p>
</div>

