<h2>Update movie <?php echo $movie->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('movie List',array('list')); ?>]
[<?php echo CHtml::link('New movie',array('create')); ?>]
[<?php echo CHtml::link('Manage movie',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'movie'=>$movie,
	'update'=>true,
)); ?>