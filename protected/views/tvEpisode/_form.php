<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($tvepisode); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'tvShow_id'); ?>
<?php echo CHtml::activeTextField($tvepisode,'tvShow_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'season'); ?>
<?php echo CHtml::activeTextField($tvepisode,'season'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'episode'); ?>
<?php echo CHtml::activeTextField($tvepisode,'episode'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'title'); ?>
<?php echo CHtml::activeTextField($tvepisode,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'description'); ?>
<?php echo CHtml::activeTextArea($tvepisode,'description',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'lastUpdated'); ?>
<?php echo CHtml::activeTextField($tvepisode,'lastUpdated'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($tvepisode,'status'); ?>
<?php echo CHtml::activeTextField($tvepisode,'status'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->