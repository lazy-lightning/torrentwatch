<h2>favoriteString List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New favoriteString',array('create')); ?>]
[<?php echo CHtml::link('Manage favoriteString',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($favoritestringList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('filter')); ?>:
<?php echo CHtml::encode($model->filter); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('notFilter')); ?>:
<?php echo CHtml::encode($model->notFilter); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('saveIn')); ?>:
<?php echo CHtml::encode($model->saveIn); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feed_id')); ?>:
<?php echo CHtml::encode($model->feed_id); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('name')); ?>:
<?php echo CHtml::encode($model->name); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('queue')); ?>:
<?php echo CHtml::encode($model->queue); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>