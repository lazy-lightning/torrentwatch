<h2>Update tvShow <?php echo $tvshow->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('tvShow List',array('list')); ?>]
[<?php echo CHtml::link('New tvShow',array('create')); ?>]
[<?php echo CHtml::link('Manage tvShow',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'tvshow'=>$tvshow,
  'update'=>true,
)); ?>
