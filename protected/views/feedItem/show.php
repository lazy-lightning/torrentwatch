<h2>View feedItem <?php echo $feeditem->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('feedItem List',array('list')); ?>]
[<?php echo CHtml::linkButton('Delete feedItem',array('submit'=>array('delete','id'=>$feeditem->id),'confirm'=>'Are you sure?')); ?>]
[<?php echo CHtml::link('Manage feedItem',array('admin')); ?>]
[<?php echo CHtml::link('Add to Favorites',array('favorite/create', 'feedItem_id'=>$feeditem->id)); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label">Feed
</th>
    <td><?php echo CHtml::link($feeditem->feed->title, array('feed/show', 'id'=>$feeditem->feed->id)); ?>
</td>
</tr>
<tr>
	<th class="label">TV Show
</th>
    <td><?php echo CHtml::link($feeditem->tvEpisode->tvShow->title, array('tvShow/show', 'id'=>$feeditem->tvEpisode->tvShow->id)); ?>
</td>
</tr>
<tr>
	<th class="label">Episode
</th>
    <td><?php echo CHtml::link($feeditem->tvEpisode->episodeString, array('tvEpisode/show', 'id'=>$feeditem->tvEpisode->id)); ?>
</td>
</tr>
<tr>
	<th class="label">Quality
</th>
    <td><?php echo CHtml::encode($feeditem->qualityString); ?>
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
    <td><?php echo CHtml::encode($feeditem->statusText); ?>
</td>
</tr>
<tr>
	<th class="label">Published
</th>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($feeditem->pubDate); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($feeditem->getAttributeLabel('lastUpdated')); ?>
</th>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($feeditem->lastUpdated); ?>
</td>
</tr>
</table>
