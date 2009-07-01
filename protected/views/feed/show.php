<h2>View feed <?php echo $feed->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('feed List',array('list')); ?>]
[<?php echo CHtml::link('New feed',array('create')); ?>]
[<?php echo CHtml::link('Update feed',array('update','id'=>$feed->id)); ?>]
[<?php echo CHtml::linkButton('Delete feed',array('submit'=>array('delete','id'=>$feed->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage feed',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
  <th class="label"><?php echo CHtml::encode($feed->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($feed->statusText); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($feed->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($feed->title); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($feed->getAttributeLabel('description')); ?>
</th>
    <td><?php echo CHtml::encode($feed->description); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($feed->getAttributeLabel('url')); ?>
</th>
    <td><?php echo CHtml::encode($feed->url); ?>
</td>
</tr>
</table>
