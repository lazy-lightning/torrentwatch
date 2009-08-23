<h2>New movie</h2>

<div class="actionBar">
[<?php echo CHtml::link('movie List',array('list')); ?>]
[<?php echo CHtml::link('Manage movie',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'movie'=>$movie,
	'update'=>false,
)); ?>