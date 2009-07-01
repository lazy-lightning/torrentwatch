<h2>New feed</h2>

<div class="actionBar">
[<?php echo CHtml::link('feed List',array('list')); ?>]
[<?php echo CHtml::link('Manage feed',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'feed'=>$feed,
  'update'=>false,
)); ?>
