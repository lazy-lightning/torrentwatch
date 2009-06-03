<h2>tvShow List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New tvShow',array('create')); ?>]
[<?php echo CHtml::link('Manage tvShow',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<table class="item">
  <tr>
    <th>Tv Show</th>
    <th>Available Episodes</th>
  </tr>
  <?php foreach($tvshowList as $n=>$model): ?>
    <tr>
      <td><?php echo CHtml::link($model->title,array('show','id'=>$model->id)); ?></td>
      <td>
        <?php foreach($model->tvEpisodes as $episode): ?>
          <?php echo CHtml::link($episode->getEpisodeString(false), array('tvEpisode/show', 'id'=>$episode->id)); ?>
        <?php endforeach; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
