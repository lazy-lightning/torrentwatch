<div id="favoriteTvShow_container">
  <ul id="favoriteTvShowList" class="favorite loadContent">
    <?php 
    $baseurl = Yii::app()->getRequest()->getScriptUrl();
    echo "<li id='favoriteTvShow-li-'><a href='$baseurl?r=favoriteTvShow/create' rel='#favoriteTvShow-'>New Favorite</a></li>";
    foreach($favoriteList as $model) {
      $id = $model->id;
      echo <<<EOD
    <li id='favoriteTvShows-li-$id'>
      <a href='$baseurl?r=favoriteTvShow/show&id=$id' rel='#favoriteTvShow-$id'>
        {$model->name}
      </a>
    </li>
EOD
      ;
    } ?>
  </ul>
  <?php 
  if($pages!==null) 
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
<?php echo isset($response) ? $response : ''; ?>
