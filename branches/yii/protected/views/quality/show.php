<h2>View quality <?php echo $quality->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('quality List',array('list')); ?>]
[<?php echo CHtml::link('New quality',array('create')); ?>]
[<?php echo CHtml::link('Update quality',array('update','id'=>$quality->id)); ?>]
[<?php echo CHtml::linkButton('Delete quality',array('submit'=>array('delete','id'=>$quality->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage quality',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($quality->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($quality->title); ?>
</td>
</tr>
</table>
