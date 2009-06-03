<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($feed); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($feed,'title'); ?>
<?php echo CHtml::activeTextField($feed,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feed,'description'); ?>
<?php echo CHtml::activeTextArea($feed,'description',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feed,'url'); ?>
<?php echo CHtml::activeTextField($feed,'url',array('size'=>60,'maxlength'=>256)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->