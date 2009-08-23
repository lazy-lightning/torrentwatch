<h2>Update other <?php echo $other->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('other List',array('list')); ?>]
[<?php echo CHtml::link('New other',array('create')); ?>]
[<?php echo CHtml::link('Manage other',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'other'=>$other,
	'update'=>true,
)); ?>