<h2>Update genre <?php echo $genre->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('genre List',array('list')); ?>]
[<?php echo CHtml::link('New genre',array('create')); ?>]
[<?php echo CHtml::link('Manage genre',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'genre'=>$genre,
	'update'=>true,
)); ?>