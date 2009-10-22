<h2>Managing favoriteMovie</h2>

<div class="actionBar">
[<?php echo CHtml::link('favoriteMovie List',array('list')); ?>]
[<?php echo CHtml::link('New favoriteMovie',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('genre_id'); ?></th>
    <th><?php echo $sort->link('feed_id'); ?></th>
    <th><?php echo $sort->link('rating'); ?></th>
    <th><?php echo $sort->link('minYear'); ?></th>
    <th><?php echo $sort->link('maxYear'); ?></th>
    <th><?php echo $sort->link('queue'); ?></th>
	<th>Actions</th>
  </tr>
<?php foreach($favoritemovieList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->genre_id); ?></td>
    <td><?php echo CHtml::encode($model->feed_id); ?></td>
    <td><?php echo CHtml::encode($model->rating); ?></td>
    <td><?php echo CHtml::encode($model->minYear); ?></td>
    <td><?php echo CHtml::encode($model->maxYear); ?></td>
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