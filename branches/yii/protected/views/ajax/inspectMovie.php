
<div>
  <span>Title:</span>
  <span><?php echo CHtml::link($movie->title, $movie->imdbLink); ?></span>
</div>
<div>
  <span>Year:</span>
  <span><?php echo CHtml::encode($movie->year); ?></span>
</div>
<div>
  <span>Genre:</span>
  <span><?php echo CHtml::encode($movie->genreString); ?></span>
<div>
  <span>Rating:</span>
  <span><?php echo CHtml::encode($movie->rating); ?> / 100</span>
</div>
<div>
  <span>Plot:</span>
  <span><?php echo CHtml::encode($movie->plot); ?></span>
</div>
<div>
  <span>Runtime:</span>
  <span><?php echo CHtml::encode($movie->runtime); ?></span>
</div>
