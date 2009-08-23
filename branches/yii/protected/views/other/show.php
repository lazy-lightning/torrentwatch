<h2>View other <?php echo $other->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('other List',array('list')); ?>]
[<?php echo CHtml::link('New other',array('create')); ?>]
[<?php echo CHtml::link('Update other',array('update','id'=>$other->id)); ?>]
[<?php echo CHtml::linkButton('Delete other',array('submit'=>array('delete','id'=>$other->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage other',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($other->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($other->title); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($other->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($other->status); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($other->getAttributeLabel('lastImdbUpdate')); ?>
</th>
    <td><?php echo CHtml::encode($other->lastImdbUpdate); ?>
</td>
</tr>
</table>
