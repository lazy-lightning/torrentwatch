<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($other); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($other,'title'); ?>
<?php echo CHtml::activeTextArea($other,'title',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($other,'status'); ?>
<?php echo CHtml::activeTextField($other,'status'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($other,'lastImdbUpdate'); ?>
<?php echo CHtml::activeTextField($other,'lastImdbUpdate'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->