<h2>View network <?php echo $network->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('network List',array('list')); ?>]
[<?php echo CHtml::link('New network',array('create')); ?>]
[<?php echo CHtml::link('Update network',array('update','id'=>$network->id)); ?>]
[<?php echo CHtml::linkButton('Delete network',array('submit'=>array('delete','id'=>$network->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage network',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
  <th class="label"><?php echo CHtml::encode($network->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($network->title); ?>
</td>
</tr>
</table>
