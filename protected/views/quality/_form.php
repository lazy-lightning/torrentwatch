<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($quality); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($quality,'title'); ?>
<?php echo CHtml::activeTextField($quality,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->
