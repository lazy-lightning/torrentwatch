<div id="feedItems_container">
  <div id="movie_container">
    <ul>
    <?php 
    foreach($movieList as $n => $model) {
      echo "<li id='movie-".$model->id."' class='torrent hasDuplicates match_".strtok($model->getStatusText(), ' ').($n%2?' alt':' notalt')."' >";
      if(empty($model->name))
        echo "  <span class='name'>".CHtml::encode($model->title)."</span>";
      else
      {
        echo "  <span class='name'>".CHtml::encode($model->name)."</span>".
             "  <span class='year'>".CHtml::encode($model->year)."</span>";
      }
      echo "  <span class='rating'>".CHtml::encode($model->rating)."/100</span>".
           "</li>";
    } ?>
    <ul>
  </div>
</div>
 
