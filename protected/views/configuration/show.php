<h2>View configuration <?php echo $configuration->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('configuration List',array('list')); ?>]
[<?php echo CHtml::link('New configuration',array('create')); ?>]
[<?php echo CHtml::link('Update configuration',array('update','id'=>$configuration->id)); ?>]
[<?php echo CHtml::linkButton('Delete configuration',array('submit'=>array('delete','id'=>$configuration->id),'confirm'=>'Are you sure?')); ?>
]
[<?php echo CHtml::link('Manage configuration',array('admin')); ?>]
</div>

<table class="dataGrid">
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('client')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->client); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('downloadDir')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->downloadDir); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('fileExtension')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->fileExtension); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('matchStyle')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->matchStyle); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('onlyNewer')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->onlyNewer); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('saveFile')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->saveFile); ?>
</td>
</tr>
<tr>
	<th class="label"><?php echo CHtml::encode($configuration->getAttributeLabel('watchDir')); ?>
</th>
    <td><?php echo CHtml::encode($configuration->watchDir); ?>
</td>
</tr>
</table>
