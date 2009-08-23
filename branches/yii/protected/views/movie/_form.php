<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($movie); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'title'); ?>
<?php echo CHtml::activeTextArea($movie,'title',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'imdbId'); ?>
<?php echo CHtml::activeTextField($movie,'imdbId'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'status'); ?>
<?php echo CHtml::activeTextField($movie,'status'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'name'); ?>
<?php echo CHtml::activeTextArea($movie,'name',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'year'); ?>
<?php echo CHtml::activeTextArea($movie,'year',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'runtime'); ?>
<?php echo CHtml::activeTextArea($movie,'runtime',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'rating'); ?>
<?php echo CHtml::activeTextField($movie,'rating'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'plot'); ?>
<?php echo CHtml::activeTextArea($movie,'plot',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($movie,'lastImdbUpdate'); ?>
<?php echo CHtml::activeTextField($movie,'lastImdbUpdate'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->