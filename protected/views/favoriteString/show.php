<h2>View favoriteString <?php echo $favoritestring->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteString List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteString',array('create')); ?>]
[<?php echo CHtml::link('Update favoriteString',array('update','id'=>$favoritestring->id)); ?>]
[<?php echo CHtml::linkButton('Delete favoriteString',array('submit'=>array('delete','id'=>$favoritestring->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage favoriteString',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('filter')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->filter); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('notFilter')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->notFilter); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('saveIn')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->saveIn); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('feed_id')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->feed_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('name')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->name); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritestring->getAttributeLabel('queue')); ?>
</th>
    <td><?php echo CHtml::encode($favoritestring->queue); ?>
</td>
</tr>
</table>
