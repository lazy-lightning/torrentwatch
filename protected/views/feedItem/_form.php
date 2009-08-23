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
<?php echo CHtml::activeTextArea($feeditem,'url',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'title'); ?>
<?php echo CHtml::activeTextArea($feeditem,'title',array('rows'=>6, 'cols'=>50)); ?>
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
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'hash'); ?>
<?php echo CHtml::activeTextArea($feeditem,'hash',array('rows'=>6, 'cols'=>50)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'imdbId'); ?>
<?php echo CHtml::activeTextField($feeditem,'imdbId'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'other_id'); ?>
<?php echo CHtml::activeTextField($feeditem,'other_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'movie_id'); ?>
<?php echo CHtml::activeTextField($feeditem,'movie_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'downloadType'); ?>
<?php echo CHtml::activeTextField($feeditem,'downloadType'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabelEx($feeditem,'hasDuplicates'); ?>
<?php echo CHtml::activeTextField($feeditem,'hasDuplicates'); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create'); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div><!-- yiiForm -->