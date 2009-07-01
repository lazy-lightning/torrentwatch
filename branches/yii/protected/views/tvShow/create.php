<h2>New tvShow</h2>

<div class="actionBar">
[<?php echo CHtml::link('tvShow List',array('list')); ?>]
[<?php echo CHtml::link('Manage tvShow',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'tvshow'=>$tvshow,
  'update'=>false,
)); ?>
