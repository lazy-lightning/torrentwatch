<div id="favoriteMovie_container">
  <ul class="favorite loadContent">
    <?php 
    echo "<li>".CHtml::link(
        'New Favorite', 
        array('create', '#'=>'favoriteMovies-')
    )."</li>";
    foreach($favoriteList as $model) {
      echo "<li>".CHtml::link(
          $model->name, 
          array('show', 'id'=>$model->id, '#'=>'favoriteMovies-'.$model->id),
          array('rel'=>'favoriteMovie')
      )."</li>";
    } ?>
  </ul>
  <?php 
  if($pages===null) 
    $this->renderPartial('show', array(
          'model'=>new favoriteMovie(),
          'feedsListData'=>feed::getCHtmlListData(),
          'genresListData'=>genre::getCHtmlListData(),
          'qualitysListData'=>quality::getCHtmlListData(),
    ));
  else
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>

