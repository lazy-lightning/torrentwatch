<h2>Update favoriteMovie <?php echo $favoritemovie->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteMovie List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteMovie',array('create')); ?>]
[<?php echo CHtml::link('Manage favoriteMovie',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'favoritemovie'=>$favoritemovie,
	'update'=>true,
)); ?>