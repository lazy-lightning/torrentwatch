<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($feeditem); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'feed_id'); ?>
<?php echo CHtml::activeTextField($feeditem,'feed_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'tvEpisode_id'); ?>
<?php echo CHtml::activeTextField($feeditem,'tvEpisode_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'url'); ?>
<?php echo CHtml::activeTextField($feeditem,'url',array('size'=>60,'maxlength'=>256)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'title'); ?>
<?php echo CHtml::activeTextField($feeditem,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'description'); ?>
<?php echo CHtml::activeTextArea($feeditem,'description',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'status'); ?>
<?php echo CHtml::activeTextField($feeditem,'status'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'pubDate'); ?>
<?php echo CHtml::activeTextField($feeditem,'pubDate'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'lastUpdated'); ?>
<?php echo CHtml::activeTextField($feeditem,'lastUpdated'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->