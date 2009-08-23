<h2>movie List</h2>

<div class="actionBar">
[<?php echo CHtml::link('New movie',array('create')); ?>]
[<?php echo CHtml::link('Manage movie',array('admin')); ?>]
</div>

<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>

<?php foreach($movieList as $n=>$model): ?>
<div class="item">
<?php echo CHtml::encode($model->getAttributeLabel('id')); ?>:
<?php echo CHtml::link($model->id,array('show','id'=>$model->id)); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('title')); ?>:
<?php echo CHtml::encode($model->title); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('imdbId')); ?>:
<?php echo CHtml::encode($model->imdbId); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('status')); ?>:
<?php echo CHtml::encode($model->status); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('name')); ?>:
<?php echo CHtml::encode($model->name); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('year')); ?>:
<?php echo CHtml::encode($model->year); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('runtime')); ?>:
<?php echo CHtml::encode($model->runtime); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('rating')); ?>:
<?php echo CHtml::encode($model->rating); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('plot')); ?>:
<?php echo CHtml::encode($model->plot); ?>
<br/>
<?php echo CHtml::encode($model->getAttributeLabel('lastImdbUpdate')); ?>:
<?php echo CHtml::encode($model->lastImdbUpdate); ?>
<br/>

</div>
<?php endforeach; ?>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>