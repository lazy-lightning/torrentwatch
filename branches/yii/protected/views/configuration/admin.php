<h2>Managing configuration</h2>

<div class="actionBar">
[<?php echo CHtml::link('configuration List',array('list')); ?>]
[<?php echo CHtml::link('New configuration',array('create')); ?>]
</div>

<table class="dataGrid">
  <tr>
    <th><?php echo $sort->link('id'); ?></th>
    <th><?php echo $sort->link('client'); ?></th>
    <th><?php echo $sort->link('downloadDir'); ?></th>
    <th><?php echo $sort->link('fileExtension'); ?></th>
    <th><?php echo $sort->link('matchStyle'); ?></th>
    <th><?php echo $sort->link('onlyNewer'); ?></th>
    <th><?php echo $sort->link('saveFile'); ?></th>
    <th><?php echo $sort->link('watchDir'); ?></th>
	<th>Actions</th>
  </tr>
<?php foreach($configurationList as $n=>$model): ?>
  <tr class="<?php echo $n%2?'even':'odd';?>">
    <td><?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?></td>
    <td><?php echo CHtml::encode($model->client); ?></td>
    <td><?php echo CHtml::encode($model->downloadDir); ?></td>
    <td><?php echo CHtml::encode($model->fileExtension); ?></td>
    <td><?php echo CHtml::encode($model->matchStyle); ?></td>
    <td><?php echo CHtml::encode($model->onlyNewer); ?></td>
    <td><?php echo CHtml::encode($model->saveFile); ?></td>
    <td><?php echo CHtml::encode($model->watchDir); ?></td>
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