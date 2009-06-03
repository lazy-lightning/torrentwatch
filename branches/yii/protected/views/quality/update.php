<h2>Update quality <?php echo $quality->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('quality List',array('list')); ?>]
[<?php echo CHtml::link('New quality',array('create')); ?>]
[<?php echo CHtml::link('Manage quality',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'quality'=>$quality,
	'update'=>true,
)); ?>