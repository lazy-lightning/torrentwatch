<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($favorite); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($favorite,'tvShow_id'); ?>
<?php echo CHtml::activeTextField($favorite,'tvShow_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favorite,'quality_id'); ?>
<?php echo CHtml::activeTextField($favorite,'quality_id'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->
