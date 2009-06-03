<h2>Update configuration <?php echo $configuration->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('configuration List',array('list')); ?>]
[<?php echo CHtml::link('New configuration',array('create')); ?>]
[<?php echo CHtml::link('Manage configuration',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'configuration'=>$configuration,
	'update'=>true,
)); ?>