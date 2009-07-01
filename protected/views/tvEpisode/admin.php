<h2>Managing tvEpisode</h2>

<div class="actionBar">
[<?php echo CHtml::link('tvEpisode List',array('list')); ?>]
[<?php echo CHtml::link('New tvEpisode',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('tvShow_id', 'Tv Show'); ?></th>
    <th><?php echo $sort->link('season'); ?></th>
    <th><?php echo $sort->link('episode'); ?></th>
    <th><?php echo $sort->link('title'); ?></th>
    <th><?php echo $sort->link('lastUpdated'); ?></th>
    <th><?php echo $sort->link('status'); ?></th>
  <th>Actions</th>
  </tr>
<?php foreach($tvepisodeList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->tvShow->title); ?></td>
    <td><?php echo CHtml::encode($model->season); ?></td>
    <td><?php echo CHtml::encode($model->episode); ?></td>
    <td><?php echo CHtml::encode($model->title); ?></td>
    <td><?php echo Yii::app()->dateFormatter->formatDateTime($model->lastUpdated); ?></td>
    <td><?php echo CHtml::encode($model->status); ?></td>
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
