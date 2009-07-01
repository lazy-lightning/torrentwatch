<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($genre); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($genre,'title'); ?>
<?php echo CHtml::activeTextField($genre,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->
