<h2>View tvShow <?php echo $tvshow->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('tvShow List',array('list')); ?>]
[<?php echo CHtml::link('New tvShow',array('create')); ?>]
[<?php echo CHtml::link('Update tvShow',array('update','id'=>$tvshow->id)); ?>]
[<?php echo CHtml::linkButton('Delete tvShow',array('submit'=>array('delete','id'=>$tvshow->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage tvShow',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
  <th class="label"><?php echo CHtml::encode($tvshow->getAttributeLabel('network_id')); ?>
</th>
    <td><?php echo CHtml::encode($tvshow->network_id); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvshow->getAttributeLabel('title')); ?>
</th>
    <td><?php echo CHtml::encode($tvshow->title); ?>
</td>
</tr>
<tr>
  <th class="label"><?php echo CHtml::encode($tvshow->getAttributeLabel('description')); ?>
</th>
    <td><?php echo CHtml::encode($tvshow->description); ?>
</td>
</tr>
<tr>
  <th class="label">Auto Download
</th>
  <td><?php 
        foreach($tvshow->favorites as $fav) {
          echo CHtml::link($fav->quality->title, array('favorite/show', 'id'=>$fav->id))." ";
        } ?>
</td>
</tr>
<tr>
  <th class="label">Episodes
</th>
<?php $x=False;foreach($tvshow->tvEpisodes as $episode): ?>
  <?php if($x === False): $x=True; else: ?>
    <tr>
      <th class="label">&nbsp;</th>
  <?php endif;?>
    <td><?php echo CHtml::link($episode->getEpisodeString(false), array('tvEpisode/show', 'id'=>$episode->id)); ?></td>
  </tr>
<?php endforeach;?>
</table>
