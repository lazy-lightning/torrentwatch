<div class="activeFeed" id="newFeed">
  <?php echo CHtml::beginForm(array('create'), 'post', array('class'=>'feedform')); ?>
  <a class="submitForm button" class="add" href="#">Add</a>
  <?php if($model->hasErrors()) echo CHtml::errorSummary($model); ?>
  <div>
    <label class="item">New Feed</label>
    <?php echo CHtml::dropDownList('feed[downloadType]', $model->downloadType, array(feedItem::TYPE_NZB=>'NZB', feedItem::TYPE_TORRENT=>'Torrent')); ?>
  </div>
  <div>
    <?php echo CHtml::activeTextField($model, 'url'); ?>
  </div>
  <?php echo CHtml::endForm(); ?>
</div>
