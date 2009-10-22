<h2>View history <?php echo $history->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('history List',array('list')); ?>]
[<?php echo CHtml::link('New history',array('create')); ?>]
[<?php echo CHtml::link('Update history',array('update','id'=>$history->id)); ?>]
[<?php echo CHtml::linkButton('Delete history',array('submit'=>array('delete','id'=>$history->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage history',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('feedItem_id')); ?>
</th>
    <td><?php echo CHtml::encode($history->feedItem_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('feedItem_title')); ?>
</th>
    <td><?php echo CHtml::encode($history->feedItem_title); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('feed_id')); ?>
</th>
    <td><?php echo CHtml::encode($history->feed_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('feed_title')); ?>
</th>
    <td><?php echo CHtml::encode($history->feed_title); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('favorite_name')); ?>
</th>
    <td><?php echo CHtml::encode($history->favorite_name); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($history->status); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('date')); ?>
</th>
    <td><?php echo CHtml::encode($history->date); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($history->getAttributeLabel('favorite_type')); ?>
</th>
    <td><?php echo CHtml::encode($history->favorite_type); ?>
</td>
</tr>
</table>
