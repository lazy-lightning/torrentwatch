<div class="dialog_window" id="feeds">
 <h2 class="dialog_heading">Feeds</h2>
 <?php if($tw->feeds->get()): ?>
  <?php foreach($tw->feeds->get() as $feed): ?>
   <div class="feeditem">
     <a class="button" id="Delete" href="<?php echo $baseuri; ?>/ajax/deleteFeed/<?php echo $feed->id; ?>">
      Delete
     </a>
     <label class="item"><?php echo $feed->title.': '.$feed->url; ?></label>
   </div>
  <?php endforeach; ?>
 <?php endif; ?>
 <div class="feeditem">
  <form action="<?php echo $baseuri; ?>/ajax/addFeed/" class="feedform">
   <a class="submitForm button" id="Add" href="#">Add</a>
   <label class="item">New Feed</label>
   <input type="text" name="link" >
  </form>
 </div>
</div>

