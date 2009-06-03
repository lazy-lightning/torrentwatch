<h2>tvEpisode List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New tvEpisode',array('create')); ?>]
[<?php echo CHtml::link('Manage tvEpisode',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<table class="item">
  <tr>
    <th>Episode</th>
    <th>Status</th>
    <th>Tv Show</th>
    <th>Available Qualitys</th>
  </tr>
  <?php foreach($tvepisodeList as $n=>$model): ?>
    <tr>
      <td><?php echo CHtml::link($model->getEpisodeString(false), array('show', 'id'=>$model->id)); ?></td>
      <td><?php echo CHtml::encode($model->statusText); ?></td>
      <td><?php echo CHtml::link($model->tvShow->title, array('tvShow/show', 'id'=>$model->tvShow->id)); ?></td>
      <td>
        <?php 
          foreach($model->feedItems as $item) {
            echo CHtml::link($item->qualityString, array('feedItem/show', 'id'=>$item->id))." ";
        } ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
