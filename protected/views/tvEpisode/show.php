<h2>View tvEpisode <?php echo $tvepisode->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('tvEpisode List',array('list')); ?>]
[<?php echo CHtml::link('New tvEpisode',array('create')); ?>]
[<?php echo CHtml::link('Update tvEpisode',array('update','id'=>$tvepisode->id)); ?>]
[<?php echo CHtml::linkButton('Delete tvEpisode',array('submit'=>array('delete','id'=>$tvepisode->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage tvEpisode',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
  <th class="label">Tv Show
</th>
    <td><?php echo CHtml::link($tvepisode->tvShow->title, array('tvShow/show', 'id'=>$tvepisode->tvShow->id)); ?>
</td>
</tr>
<tr>
  <th class="label">Episode
</th>
    <td><?php echo CHtml::encode($tvepisode->episodeString); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvepisode->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($tvepisode->title); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvepisode->getAttributeLabel('description')); ?>
</th>
    <td><?php echo CHtml::encode($tvepisode->description); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvepisode->getAttributeLabel('lastUpdated')); ?>
</th>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($tvepisode->lastUpdated); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvepisode->getAttributeLabel('status')); ?>
</th>
    <td><?php echo CHtml::encode($tvepisode->statusText); ?>
</td>
</tr>
<tr>
  <th class="label">Available Quailtys</th>
<?php $x=False;foreach($tvepisode->feedItems as $item): ?>
  <?php if($x):?>
  <tr>
    <th>&nbsp;</th>
  <?php else: $x = True; endif;?>
    <td><?php echo CHtml::encode($item->feed->title)." ".CHtml::link($item->qualityString, array('feedItem/show', 'id'=>$item->id)); ?></td>
  </tr>
<?php endforeach; ?>

</table>
