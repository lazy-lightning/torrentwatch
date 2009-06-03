<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($configuration); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'client'); ?>
<?php echo CHtml::activeTextField($configuration,'client',array('size'=>32,'maxlength'=>32)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'downloadDir'); ?>
<?php echo CHtml::activeTextField($configuration,'downloadDir',array('size'=>60,'maxlength'=>256)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'fileExtension'); ?>
<?php echo CHtml::activeTextField($configuration,'fileExtension',array('size'=>16,'maxlength'=>16)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'matchStyle'); ?>
<?php echo CHtml::activeTextField($configuration,'matchStyle'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'onlyNewer'); ?>
<?php echo CHtml::activeTextField($configuration,'onlyNewer'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'saveFile'); ?>
<?php echo CHtml::activeTextField($configuration,'saveFile'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($configuration,'watchDir'); ?>
<?php echo CHtml::activeTextField($configuration,'watchDir',array('size'=>60,'maxlength'=>256)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->