<?php $this->pageTitle=Yii::app()->name . ' - Login'; ?>
<div class='login_form' id='login_form'>
<h1>Login</h1>

<div class="yiiForm">
<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($form); ?>

<div class="simple">
<?php echo CHtml::activeLabel($form,'username'); ?>
<?php echo CHtml::activeTextField($form,'username') ?>
</div>

<div class="simple">
<?php echo CHtml::activeLabel($form,'password'); ?>
<?php echo CHtml::activePasswordField($form,'password') ?>
<p class="hint">
Hint: You may login with <tt>demo/demo</tt> or <tt>admin/admin</tt>.
</p>
</div>

<div class="action">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?>
<?php echo CHtml::activeLabel($form,'rememberMe'); ?>
<br/>
<?php echo CHtml::submitButton('Login', array('class'=>'submitForm')); ?>
</div>
<?php CHtml::endForm(); ?>>

</div><!-- yiiForm -->
</div>
