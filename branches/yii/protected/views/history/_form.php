<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($history); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($history,'feedItem_id'); ?>
<?php echo CHtml::activeTextField($history,'feedItem_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'feedItem_title'); ?>
<?php echo CHtml::activeTextArea($history,'feedItem_title',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'feed_id'); ?>
<?php echo CHtml::activeTextField($history,'feed_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'feed_title'); ?>
<?php echo CHtml::activeTextArea($history,'feed_title',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'favorite_name'); ?>
<?php echo CHtml::activeTextArea($history,'favorite_name',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'status'); ?>
<?php echo CHtml::activeTextField($history,'status'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'date'); ?>
<?php echo CHtml::activeTextField($history,'date'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($history,'favorite_type'); ?>
<?php echo CHtml::activeTextArea($history,'favorite_type',array('rows'=>6, 'cols'=>50)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->