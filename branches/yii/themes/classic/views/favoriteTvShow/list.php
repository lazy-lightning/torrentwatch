<div id="favoriteTvShow_container">
  <ul id="favoriteTvShowList" class="favorite loadContent">
    <?php 
    echo "<li id='favoriteTvShow-li-'>".CHtml::link(
        "New Favorite", 
        array('create'),
        array('rel'=>'#favoriteTvShow-')
    )."</li>";
    foreach($favoriteList as $model) {
      echo "<li id='favoriteTvShow-li-{$model->id}'>".CHtml::link(
          $model->name, 
          array('show', 'id'=>$model->id), 
          array('rel'=>'#favoriteTvShow-'.$model->id)
      )."</li>";
    } ?>
  </ul>
  <?php 
  if($pages!==null) 
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
<?php echo isset($response) ? $response : ''; ?>
