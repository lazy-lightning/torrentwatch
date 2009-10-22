<h2>New favoriteString</h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteString List',array('list')); ?>]
[<?php echo CHtml::link('Manage favoriteString',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'favoritestring'=>$favoritestring,
	'update'=>false,
)); ?>