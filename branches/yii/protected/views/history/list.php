<h2>history List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New history',array('create')); ?>]
[<?php echo CHtml::link('Manage history',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($historyList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feedItem_id')); ?>:
<?php echo CHtml::encode($model->feedItem_id); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feedItem_title')); ?>:
<?php echo CHtml::encode($model->feedItem_title); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feed_id')); ?>:
<?php echo CHtml::encode($model->feed_id); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feed_title')); ?>:
<?php echo CHtml::encode($model->feed_title); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('favorite_name')); ?>:
<?php echo CHtml::encode($model->favorite_name); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('status')); ?>:
<?php echo CHtml::encode($model->status); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('date')); ?>:
<?php echo CHtml::encode($model->date); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('favorite_type')); ?>:
<?php echo CHtml::encode($model->favorite_type); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>