<h2>Managing feedItem</h2>

<div class="actionBar">
[<?php echo CHtml::link('feedItem List',array('list')); ?>]
[<?php echo CHtml::link('New feedItem',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('feed_id'); ?></th>
    <th><?php echo $sort->link('tvEpisode_id'); ?></th>
    <th><?php echo $sort->link('url'); ?></th>
    <th><?php echo $sort->link('title'); ?></th>
    <th><?php echo $sort->link('status'); ?></th>
    <th><?php echo $sort->link('pubDate'); ?></th>
    <th><?php echo $sort->link('lastUpdated'); ?></th>
  <th>Actions</th>
  </tr>
<?php foreach($feeditemList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->feed_id); ?></td>
    <td><?php echo CHtml::encode($model->tvEpisode_id); ?></td>
    <td><?php echo CHtml::encode($model->url); ?></td>
    <td><?php echo CHtml::encode($model->title); ?></td>
    <td><?php echo CHtml::encode($model->statusText); ?></td>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($model->pubDate); ?></td>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($model->lastUpdated); ?></td>
    <td>
      <?php echo CHtml::link('Update',array('update','id'=>$model->id)); ?>
      <?php echo CHtml::linkButton('Delete',array(
          'submit'=>'',
          'params'=>array('command'=>'delete','id'=>$model->id),
          'confirm'=>"Are you sure to delete #{$model->id}?")); ?>
  </td>
  </tr>
<?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>