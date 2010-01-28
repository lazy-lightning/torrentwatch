<div id="favoriteMovie_container">
  <ul id="favoriteMovieList" class="favorite loadContent">
    <?php 
    echo "<li id='favoriteMovie-li-'>".CHtml::link(
        'New Favorite', 
        array('create'),
        array('rel'=>'#favoriteMovie-')
    )."</li>";
    foreach($favoriteList as $model) {
      echo "<li id='favoriteMovie-li-{$model->id}'>".CHtml::link(
          $model->name, 
          array('show', 'id'=>$model->id),
          array('rel'=>'#favoriteMovie-'.$model->id)
      )."</li>";
    } ?>
  </ul>
  <?php 
  if($pages!==null) 
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
<?php if(isset($response)) echo $response; ?>
