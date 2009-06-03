<h2>favorite List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New favorite',array('create')); ?>]
[<?php echo CHtml::link('Manage favorite',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($favoriteList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('tvShow_id')); ?>:
<?php echo CHtml::link($model->tvShow->title, array('tvShow/show', 'id'=>$model->tvShow->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('quality_id')); ?>:
<?php echo CHtml::encode($model->quality->title); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
