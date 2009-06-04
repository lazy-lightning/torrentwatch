<h2>View favorite <?php echo $favorite->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favorite List',array('list')); ?>]
[<?php echo CHtml::link('New favorite',array('create')); ?>]
[<?php echo CHtml::link('Update favorite',array('update','id'=>$favorite->id)); ?>]
[<?php echo CHtml::linkButton('Delete favorite',array('submit'=>array('delete','id'=>$favorite->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage favorite',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($favorite->getAttributeLabel('tvShow_id')); ?>
</th>
    <td><?php echo CHtml::link($favorite->tvShow->title, array('tvShow/show', 'id'=>$favorite->tvShow->id)); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favorite->getAttributeLabel('quality_id')); ?>
</th>
    <td><?php echo CHtml::encode($favorite->quality->title); ?>
</td>
</tr>
</table>
