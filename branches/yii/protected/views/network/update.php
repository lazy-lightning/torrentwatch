<h2>Update network <?php echo $network->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('network List',array('list')); ?>]
[<?php echo CHtml::link('New network',array('create')); ?>]
[<?php echo CHtml::link('Manage network',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'network'=>$network,
	'update'=>true,
)); ?>