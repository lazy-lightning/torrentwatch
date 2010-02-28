<?php 
  if($fullList) {
    echo<<<EOD
<div id="feeds">
  <h2 class="dialog_heading">Feeds</h2>
  <div id="feedsRestraint">
EOD
    ;
  }
  $charset = Yii::app()->charset;
  foreach($feedList as $n=>$model) {
    if($model->id == 0) 
      continue; // the generic 'all' feeds
    $tooltip = htmlentities($model->url, ENT_QUOTES, $charset);
    $deleteLink = CHtml::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit'));
    $titleLink = CHtml::link($model->getTitle(), array('update', 'id'=>$model->id), array('class'=>'ajaxSubmit'));
    echo <<<EOD
  <div id="feed-{$model->id}" class="activeFeed" title="{$tooltip}">
    $deleteLink
    <span>$titleLink</span>
  </div>
EOD
    ;
  }
  if($fullList) {
    $this->renderPartial('create', array('model'=>new feed));
    echo <<<EOD
  </div>
  <div class="buttonContainer">
    <a class='toggleDialog button' href='#'>Close</a>
  </div>
  <div class='clear'></div>
</div>
EOD
    ;
  }
  if(is_array($response)) $this->widget('actionResponseWidget', $response); 
?>
