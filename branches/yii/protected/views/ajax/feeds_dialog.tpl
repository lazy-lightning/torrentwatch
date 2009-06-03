<div class="dialog_window" id="feeds">
 <h2 class="dialog_heading">Feeds</h2>
 <?php if($feeds): ?>
  <?php foreach($feeds as $feed): 
          if($feed->id === '0') continue; // the generic 'all' feeds ?>
   <div class="feeditem" title="<?php echo CHtml::encode($feed->url); ?>">
     <?php echo CHtml::link('Delete', array('deleteFeed', 'id'=>$feed->id), array('class'=>'button', 'id'=>'Delete')); ?>
     <?php echo CHtml::encode($feed->title); ?>
   </div>
  <?php endforeach; ?>
 <?php endif; ?>
 <div class="feeditem">
  <?php echo CHtml::beginForm(array('addFeed'), 'post', array('class'=>'feedform')); ?>
   <a class="submitForm button" id="Add" href="#">Add</a>
   <label class="item">New Feed</label>
   <input type="text" name="feed[url]" id="feed_url">
  </form>
 </div>
</div>

