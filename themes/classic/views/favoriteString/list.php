<div id="favoriteString_container">
  <ul id="favoriteStringList" class="favorite loadContent">
    <?php 
    echo "<li id='favoriteString-li-'>".CHtml::link(
        'New Favorite', 
        array('create'),
        array('rel'=>'#favoriteString-')
    )."</li>";
    foreach($favoriteList as $model) {
      echo "<li id='favoriteString-li-{$model->id}'>".CHtml::link(
          $model->name, 
          array('show', 'id'=>$model->id),
          array('rel'=>'#favoriteString-'.$model->id)
      )."</li>";
    } ?>
  </ul>
  <?php 
  if($pages!==null) 
    $this->widget('CLinkPager',array('pages'=>$pages)); 
  ?> 
</div>
<?php if(isset($response)) echo $response; ?>
