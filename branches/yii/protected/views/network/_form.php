<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($network); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($network,'title'); ?>
<?php echo CHtml::activeTextField($network,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->