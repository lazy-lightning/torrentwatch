<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($favoritemovie); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'name'); ?>
<?php echo CHtml::activeTextArea($favoritemovie,'name',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'genre_id'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'genre_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'feed_id'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'feed_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'rating'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'rating'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'saveIn'); ?>
<?php echo CHtml::activeTextArea($favoritemovie,'saveIn',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'minYear'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'minYear'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'maxYear'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'maxYear'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritemovie,'queue'); ?>
<?php echo CHtml::activeTextField($favoritemovie,'queue'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->