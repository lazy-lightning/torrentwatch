<h2>New feedItem</h2>

<div class="actionBar">
[<?php echo CHtml::link('feedItem List',array('list')); ?>]
[<?php echo CHtml::link('Manage feedItem',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
	'feeditem'=>$feeditem,
	'update'=>false,
)); ?>