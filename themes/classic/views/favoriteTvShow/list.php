<div id="favoriteTvShow_container">
  <ul class="favorite loadContent">
    <?php 
    echo "<li>".CHtml::link("New Favorite", array('show'), array('rel'=>'#favoriteTvShow'))."</li>";
    foreach($favoriteList as $model) {
      echo "<li>".CHtml::link($model->name, array('show', 'id'=>$model->id), array('rel'=>'#favoriteTvShow'))."</li>";
    } ?>
  </ul>
  <?php 
  if($pages===null) 
    $this->renderPartial('show', array('model'=>new favoriteTvShow()));
  else
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
