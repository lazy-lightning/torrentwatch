<h2>View favoriteMovie <?php echo $favoritemovie->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteMovie List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteMovie',array('create')); ?>]
[<?php echo CHtml::link('Update favoriteMovie',array('update','id'=>$favoritemovie->id)); ?>]
[<?php echo CHtml::linkButton('Delete favoriteMovie',array('submit'=>array('delete','id'=>$favoritemovie->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage favoriteMovie',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('name')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->name); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('genre_id')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->genre_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('feed_id')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->feed_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('rating')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->rating); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('saveIn')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->saveIn); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('minYear')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->minYear); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('maxYear')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->maxYear); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($favoritemovie->getAttributeLabel('queue')); ?>
</th>
    <td><?php echo CHtml::encode($favoritemovie->queue); ?>
</td>
</tr>
</table>
