<h2>New history</h2>

<div class="actionBar">
[<?php echo CHtml::link('history List',array('list')); ?>]
[<?php echo CHtml::link('Manage history',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'history'=>$history,
	'update'=>false,
)); ?>