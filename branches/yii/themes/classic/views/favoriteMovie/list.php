<div id="favoriteMovie_container">
  <ul class="favorite loadContent">
    <?php 
    echo "<li>".CHtml::link('New Favorite', array('create'), array('rel'=>'favoriteMovie')),"</li>";
    foreach($favoriteList as $model) {
      echo "<li>".CHtml::link($model->name, array('show', 'id'=>$model->id), array('rel'=>'favoriteMovie'))."</li>";
    } ?>
  </ul>
  <?php 
  if($pages===null) 
    $this->renderPartial('show', array('model'=>new favoriteMovie()));
  else
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>

