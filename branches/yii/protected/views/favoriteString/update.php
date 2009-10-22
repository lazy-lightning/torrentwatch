<h2>Update favoriteString <?php echo $favoritestring->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteString List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteString',array('create')); ?>]
[<?php echo CHtml::link('Manage favoriteString',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'favoritestring'=>$favoritestring,
	'update'=>true,
)); ?>