<h2>Update feed <?php echo $feed->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('feed List',array('list')); ?>]
[<?php echo CHtml::link('New feed',array('create')); ?>]
[<?php echo CHtml::link('Manage feed',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'feed'=>$feed,
  'update'=>true,
)); ?>
