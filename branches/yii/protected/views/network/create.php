<h2>New network</h2>

<div class="actionBar">
[<?php echo CHtml::link('network List',array('list')); ?>]
[<?php echo CHtml::link('Manage network',array('admin')); ?>]
</div>

<?php echo $this->renderPartial('_form', array(
  'network'=>$network,
  'update'=>false,
)); ?>
