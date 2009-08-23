<h2>New other</h2>

<div class="actionBar">
[<?php echo CHtml::link('other List',array('list')); ?>]
[<?php echo CHtml::link('Manage other',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'other'=>$other,
	'update'=>false,
)); ?>