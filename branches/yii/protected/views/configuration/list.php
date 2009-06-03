<h2>configuration List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New configuration',array('create')); ?>]
[<?php echo CHtml::link('Manage configuration',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($configurationList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('client')); ?>:
<?php echo CHtml::encode($model->client); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('downloadDir')); ?>:
<?php echo CHtml::encode($model->downloadDir); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('fileExtension')); ?>:
<?php echo CHtml::encode($model->fileExtension); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('matchStyle')); ?>:
<?php echo CHtml::encode($model->matchStyle); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('onlyNewer')); ?>:
<?php echo CHtml::encode($model->onlyNewer); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('saveFile')); ?>:
<?php echo CHtml::encode($model->saveFile); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('watchDir')); ?>:
<?php echo CHtml::encode($model->watchDir); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>