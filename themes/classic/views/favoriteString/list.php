<div id="favoriteString_container">
  <ul class="favorite loadContent">
    <?php 
    echo "<li>".CHtml::link('New Favorite', array('show'), array('rel'=>'#favoriteString'))."</li>";
    foreach($favoriteList as $model) {
      echo "<li>".CHtml::link($model->name, array('show', 'id'=>$model->id), array('rel'=>'#favoriteString'))."</li>";
    } ?>
  </ul>
  <?php 
  if($pages===null) 
    $this->renderPartial('show', array('model'=>new favoriteString()));
  else
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>

