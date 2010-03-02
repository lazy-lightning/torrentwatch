
<div class="updateFeed clearFix" id="feed-<?php echo $model->id; ?>">
  <?php echo html::beginForm(array('update', 'id'=>$model->id), 'post', array('class'=>'feedform'));
        if($model->hasErrors()) echo html::errorSummary($model);
        if($success) echo "<div class='saved'>Saved</div>"; ?>

  <div>
    <label class='item'>Feed Title</label>:
    <span class='static'><?php echo html::encode($model->title); ?></span>
  </div>
  <div>
    <label class='item'>Status</label>:
    <span class='static'><?php echo html::encode($model->getStatusText()); ?></span>
  </div>
  <div>
    <?php echo html::activeLabel($model, 'userTitle', array('class'=>'item')).': '.
               html::activeTextField($model, 'userTitle', array('gray'=>'Enter your own title')); ?>
  </div>
  <div class="url">
    <?php echo html::activeLabel($model, 'url', array('class'=>'item')).': '.
               html::activeTextField($model, 'url'); ?>
  </div>
  <div>
    <?php echo html::activeLabel($model, 'downloadType', array('class'=>'item')).': '.
               html::dropDownList('feed[downloadType]', $model->downloadType, array(feedItem::TYPE_NZB=>'NZB', feedItem::TYPE_TORRENT=>'Torrent')); ?>
  </div>
  <a class="submitForm button" href="#">Update</a>
  <?php echo CHtml::link('Hide', array('list', 'id'=>$model->id), array('class'=>'ajaxSubmit button'));
        echo html::endForm(); ?>
</div>
