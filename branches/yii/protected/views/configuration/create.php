<h2>New configuration</h2>

<div class="actionBar">
[<?php echo CHtml::link('configuration List',array('list')); ?>]
[<?php echo CHtml::link('Manage configuration',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'configuration'=>$configuration,
	'update'=>false,
)); ?>