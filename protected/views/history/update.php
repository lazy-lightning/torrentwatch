<h2>Update history <?php echo $history->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('history List',array('list')); ?>]
[<?php echo CHtml::link('New history',array('create')); ?>]
[<?php echo CHtml::link('Manage history',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'history'=>$history,
	'update'=>true,
)); ?>