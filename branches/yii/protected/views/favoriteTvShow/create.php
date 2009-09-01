<h2>New favorite</h2>

<div class="actionBar">
[<?php echo CHtml::link('favorite List',array('list')); ?>]
[<?php echo CHtml::link('Manage favorite',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'favorite'=>$favorite,
  'update'=>false,
)); ?>
