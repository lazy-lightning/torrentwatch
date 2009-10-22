<h2>favoriteMovie List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New favoriteMovie',array('create')); ?>]
[<?php echo CHtml::link('Manage favoriteMovie',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($favoritemovieList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('name')); ?>:
<?php echo CHtml::encode($model->name); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('genre_id')); ?>:
<?php echo CHtml::encode($model->genre_id); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('feed_id')); ?>:
<?php echo CHtml::encode($model->feed_id); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('rating')); ?>:
<?php echo CHtml::encode($model->rating); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('saveIn')); ?>:
<?php echo CHtml::encode($model->saveIn); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('minYear')); ?>:
<?php echo CHtml::encode($model->minYear); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('maxYear')); ?>:
<?php echo CHtml::encode($model->maxYear); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('queue')); ?>:
<?php echo CHtml::encode($model->queue); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>