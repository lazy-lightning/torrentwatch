<h2>View feedItem <?php echo $feeditem->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('feedItem List',array('list')); ?>]
[<?php echo CHtml::link('New feedItem',array('create')); ?>]
[<?php echo CHtml::link('Update feedItem',array('update','id'=>$feeditem->id)); ?>]
[<?php echo CHtml::linkButton('Delete feedItem',array('submit'=>array('delete','id'=>$feeditem->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage feedItem',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('feed_id')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->feed_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('tvEpisode_id')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->tvEpisode_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('url')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->url); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->title); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('description')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->description); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->status); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('pubDate')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->pubDate); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('lastUpdated')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->lastUpdated); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('hash')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->hash); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('imdbId')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->imdbId); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('other_id')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->other_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('movie_id')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->movie_id); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('downloadType')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->downloadType); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('hasDuplicates')); ?>
</th>
    <td><?php echo CHtml::encode($feeditem->hasDuplicates); ?>
</td>
</tr>
</table>
