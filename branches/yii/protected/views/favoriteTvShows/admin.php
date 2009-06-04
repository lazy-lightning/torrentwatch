<h2>Managing favorite</h2>

<div class="actionBar">
[<?php echo CHtml::link('favorite List',array('list')); ?>]
[<?php echo CHtml::link('New favorite',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('tvShow_id'); ?></th>
    <th><?php echo $sort->link('quality_id'); ?></th>
	<th>Actions</th>
  </tr>
<?php foreach($favoriteList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->tvShow_id); ?></td>
    <td><?php echo CHtml::encode($model->quality_id); ?></td>
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