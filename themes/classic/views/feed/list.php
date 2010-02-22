<div id="feeds">
  <h2 class="dialog_heading">Feeds</h2>
  <?php foreach($feedList as $n=>$model): if($model->id === '0') continue; // the generic 'all' feeds ?>
    <?php if($model->id == 0) continue; ?>
    <div class="activeFeed" title="<?php echo CHtml::encode($model->url); ?>">
      <?php echo CHtml::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit', 'id'=>'Delete')); ?>
      <span><?php echo CHtml::encode($model->title); ?></span>
    </div>
  <?php endforeach; ?>
  <?php $this->renderPartial('update', array('model'=>new feed)); ?>
  <div class="buttonContainer">
    <a class='toggleDialog button' href='#'>Close</a>
  </div>
  <div class='clear'></div>
</div>
<?php if(is_array($response)) $this->widget('actionResponseWidget', $response); ?>
