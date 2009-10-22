<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($favoritestring); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'filter'); ?>
<?php echo CHtml::activeTextArea($favoritestring,'filter',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'notFilter'); ?>
<?php echo CHtml::activeTextArea($favoritestring,'notFilter',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'saveIn'); ?>
<?php echo CHtml::activeTextArea($favoritestring,'saveIn',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'feed_id'); ?>
<?php echo CHtml::activeTextField($favoritestring,'feed_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'name'); ?>
<?php echo CHtml::activeTextArea($favoritestring,'name',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($favoritestring,'queue'); ?>
<?php echo CHtml::activeTextField($favoritestring,'queue'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->