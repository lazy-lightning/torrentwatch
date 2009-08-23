<h2>Update feedItem <?php echo $feeditem->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('feedItem List',array('list')); ?>]
[<?php echo CHtml::link('New feedItem',array('create')); ?>]
[<?php echo CHtml::link('Manage feedItem',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'feeditem'=>$feeditem,
	'update'=>true,
)); ?>