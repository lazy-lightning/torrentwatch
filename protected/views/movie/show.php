<h2>View movie <?php echo $movie->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('movie List',array('list')); ?>]
[<?php echo CHtml::link('New movie',array('create')); ?>]
[<?php echo CHtml::link('Update movie',array('update','id'=>$movie->id)); ?>]
[<?php echo CHtml::linkButton('Delete movie',array('submit'=>array('delete','id'=>$movie->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage movie',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($movie->title); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('imdbId')); ?>
</th>
    <td><?php echo CHtml::encode($movie->imdbId); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($movie->status); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('name')); ?>
</th>
    <td><?php echo CHtml::encode($movie->name); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('year')); ?>
</th>
    <td><?php echo CHtml::encode($movie->year); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('runtime')); ?>
</th>
    <td><?php echo CHtml::encode($movie->runtime); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('rating')); ?>
</th>
    <td><?php echo CHtml::encode($movie->rating); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('plot')); ?>
</th>
    <td><?php echo CHtml::encode($movie->plot); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($movie->getAttributeLabel('lastImdbUpdate')); ?>
</th>
    <td><?php echo CHtml::encode($movie->lastImdbUpdate); ?>
</td>
</tr>
</table>
