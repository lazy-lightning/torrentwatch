<h2>New favoriteMovie</h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteMovie List',array('list')); ?>]
[<?php echo CHtml::link('Manage favoriteMovie',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'favoritemovie'=>$favoritemovie,
	'update'=>false,
)); ?>