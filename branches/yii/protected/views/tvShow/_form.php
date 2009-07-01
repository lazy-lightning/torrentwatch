<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($tvshow); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($tvshow,'network_id'); ?>
<?php echo CHtml::activeTextField($tvshow,'network_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvshow,'title'); ?>
<?php echo CHtml::activeTextField($tvshow,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvshow,'description'); ?>
<?php echo CHtml::activeTextArea($tvshow,'description',array('rows'=>6, 'cols'=>50)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->
