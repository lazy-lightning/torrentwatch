<div id="tvshows_main">
 <div id="scroller">
  <img class="prev" height="53" width="42" alt="next" src="images/prev.gif"/>
  <div id="sections">
   <ul>
   <?php $i=0;foreach($tw->tvShows->array as $tvShow): ?>
    <?php $i++; ?>
    <li class="tvshow_container <?php echo ($tvShow->newEpisode) ? 'new_ep':'old_ep' ?>" alt='<?php echo $tvShow->lastMatch; ?>'>
     <div class="tvshow_title">
      <a href="#tvshow-<?php echo $i;?>">
       <?php if(!empty($tvShow->banner)): ?>
        <img src="<?php echo $tvShow->banner; ?>">
       <?php else: ?>
        <?php echo $tvShow->shortTitle ?>
       <?php endif;?>
      </a>
     </div>
    </li>
   <?php endforeach; ?>
   </ul>
  </div>
  <img class="next" height="42" width="42" alt="next" src="images/next.gif" />
 </div>
 <div id="tvshows">
  <?php $i=0;foreach($tw->tvShows->array as $tvShow): ?>
   <?php $i++; ?>
   <div class="tvshow" id="tvshow-<?php echo $i; ?>">
    <?php if(isset($tvShow->tvdbShow)): ?>
     <label class="tvshow_status"><?php echo $tvShow->tvdbShow->status; ?></label>
     <label class="tvshow_daysOfWeek"><?php echo $tvShow->tvdbShow->daysOfWeek; ?></label>
    <?php endif;?>
    <ul class="recentEpisodes">
     <?php foreach($tvShow->recentEpisodes as $episode): ?>
      <li class="tvshow_episode">
       <label class="tvshow_epnum">
        <?php printf('S%02dE%02d', $episode['season'], $episode['episode']); ?>
       </label>
       <label class="tvshow_epname">
        <?php if(isset($episode['tvdbEpisode']) && !empty($episode['tvdbEpisode']->name)): ?>
         <?php echo $episode['tvdbEpisode']->name; ?>
        <?php else: ?>
         Episode <?php echo $episode['episode'] ?>
        <?php endif; ?>
       </label>
      </li>
     <?php endforeach; ?>
    </ul>
    <div class="tvshow_detail">
     <label class="tvshow_category">Overview</label>
     <label class="tvshow_overview">
      <?php if(isset($tvShow->tvdbShow)): ?>
       <?php echo $tvShow->tvdbShow->overview; ?>
      <?php else: ?>
       Not Available
      <?php endif;?>
     </label>
    </div>
   </div>
  <?php endforeach; ?>
 </div>
</div>
       
       
