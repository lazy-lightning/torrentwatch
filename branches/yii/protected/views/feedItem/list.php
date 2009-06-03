<h2>feedItem List</h2>

<div class="actionBar">
[<?php echo CHtml::link('Manage feedItem',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<table class="item">
  <tr>
    <th>Status</th>
    <th>Feed</th>
    <th>Episode</th>
    <th>Quality</th>
    <th>Pub Date</th>
  </tr>
  <?php foreach($feeditemList as $n=>$model): ?>
    <tr>
      <td><?php echo CHtml::link($model->statusText, array('show', 'id'=>$model->id)); ?></td>
      <td><?php echo CHtml::encode($model->feed->title); ?></td>
      <td>
        <?php echo CHtml::link($model->tvEpisode->tvShow->title." ".$model->tvEpisode->episodeString,
                    array('show','id'=>$model->id)); ?>
      </td>
      <td><?php echo CHtml::encode($model->qualityString); ?></td>
      <td><?php echo Yii::app()->dateFormatter->formatDateTime($model->pubDate); ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
