<form action="<?php echo $baseuri; ?>/ajax/updateFavorite/<?php echo $favorite->id; ?>"
      class="favinfo" id="favorite-<?php echo $i++; ?>">
 <div class="favorite_name">
  <label class="item" title="Name of the Favorite">Name:</label>
  <input type="text" name="name" value="<?php echo $favorite->name; ?>" />
 </div>
 <div class="favorite_filter">
  <label class="item" title="Regexp filter, use .* to match all">Filter:</label>
  <input type="text" name="filter" value="<?php echo $favorite->filter; ?>" />
 </div>
 <div class="favorite_not">
  <label class="item" title="Regexp Not Filter">Not:</label>
  <input type="text" name="not" value="<?php echo $favorite->not; ?>" />
 </div>
 <div class="favorite_savein">
  <label class="item" title="Save Directory or Default">Save In:</label>
  <input type="text" name="savein" value="<?php echo $favorite->saveIn; ?>" />
 </div>
 <div class="favorite_episodes">
  <label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>
  <input type="text" name="episodes" value="<?php echo $favorite->episodes; ?>" />
 </div>
 <div class="favorite_feed">
  <label class="item" title="Feed to match against">Feed:</label>
  <select name="feed">
   <option value="all">All</option>
    <?php foreach($tw->feeds->get() as $feed): ?>
     <option value="<?php echo urlencode($feed->url) ?>"<?php if($favorite->feed == $feed->url): ?>selected="selected"<?php endif; ?>><?php echo $feed->title; ?></option>
   <?php endforeach; ?>
  </select>
 </div>
 <div class="favorite_quality">
  <label class="item" title="Regexp Filter against full title">Quality:</label>
  <input type="text" name="quality" value="<?php echo $favorite->quality; ?>" />
 </div>
 <div class="favorite_seedratio">
  <label class="item" title="Maximum seeding ratio, set to -1 to disable">Seed Ratio:</label>
  <input type="text" name="seedratio" value="<?php echo $favorite->seedRatio; ?>" />
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>
