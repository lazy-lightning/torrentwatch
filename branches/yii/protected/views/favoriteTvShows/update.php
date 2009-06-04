<h2>Update favorite <?php echo $favorite->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favorite List',array('list')); ?>]
[<?php echo CHtml::link('New favorite',array('create')); ?>]
[<?php echo CHtml::link('Manage favorite',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'favorite'=>$favorite,
	'update'=>true,
)); ?>