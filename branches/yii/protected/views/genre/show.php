<h2>View genre <?php echo $genre->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('genre List',array('list')); ?>]
[<?php echo CHtml::link('New genre',array('create')); ?>]
[<?php echo CHtml::link('Update genre',array('update','id'=>$genre->id)); ?>]
[<?php echo CHtml::linkButton('Delete genre',array('submit'=>array('delete','id'=>$genre->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage genre',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($genre->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($genre->title); ?>
</td>
</tr>
</table>
