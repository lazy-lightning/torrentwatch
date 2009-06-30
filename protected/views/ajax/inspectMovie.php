
<div>
  <h2><?php echo CHtml::link($movie->fullTitle, $movie->imdbLink); ?></h2>
</div>
<?php if(!empty($item->qualityString)): ?>
  <div>
    <span>Quality:</span>
    <span class='content'><?php echo CHtml::encode($item->qualityString); ?></span>
  </div>
<?php endif;
      if(!empty($movie->year)): ?>
  <div>
    <span>Year:</span>
    <span class='content'><?php echo CHtml::encode($movie->year); ?></span>
  </div>
<?php endif;
      if(!empty($movie->genreString)): ?>
  <div>
    <span>Genre:</span>
    <span class='content'><?php echo CHtml::encode($movie->genreString); ?></span>
  </div>
<?php endif;   
      if(!empty($movie->rating)): ?>
<div>
  <span>Rating:</span>
  <span class='content'><?php echo CHtml::encode($movie->rating); ?> / 100</span>
</div>
<?php endif;
      if(!empty($movie->plot)): ?>
  <div>
    <span>Plot:</span>
    <span class='content'><?php echo CHtml::encode($movie->plot); ?></span>
  </div>
<?php endif; 
      if(!empty($movie->runtime)): ?>
  <div>
    <span>Runtime:</span>
    <span class='content'><?php echo CHtml::encode($movie->runtime); ?></span>
  </div>
<?php endif; ?>
