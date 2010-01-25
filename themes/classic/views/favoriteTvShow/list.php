<div id="favoriteTvShow_container">
  <ul class="favorite loadContent">
    <?php 
    echo "<li>".CHtml::link(
        "New Favorite", 
        array('create', '#'=>'favoriteTvShows-')
    )."</li>";
    foreach($favoriteList as $model) {
      echo "<li>".CHtml::link(
          $model->name, 
          array('show', 'id'=>$model->id, '#'=>'favoriteTvShows-'.$model->id), 
          array('rel'=>'#favoriteTvShow')
      )."</li>";
    } ?>
  </ul>
  <?php 
  if($pages!==null) 
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
