<h2>Managing favoriteString</h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteString List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteString',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('feed_id'); ?></th>
    <th><?php echo $sort->link('queue'); ?></th>
	<th>Actions</th>
  </tr>
<?php foreach($favoritestringList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->feed_id); ?></td>
    <td><?php echo CHtml::encode($model->queue); ?></td>
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