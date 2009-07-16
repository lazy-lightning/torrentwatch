
<div>
  <h2><?php echo CHtml::encode($other->title); ?></h2>
</div>
<?php if(!empty($item->qualityString)): ?>
  <div>
    <span>Quality:</span>
    <span class='content'><?php echo CHtml::encode($item->qualityString); ?></span>
  </div>
<?php endif;
