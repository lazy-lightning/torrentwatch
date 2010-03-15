<?php 
if(!isset($lastFeedUpdate)) {
  $lastFeedUpdate = Yii::app()->getDb()->createCommand(
      'SELECT max(lastUpdated) FROM feed'
  )->queryScalar();
} 
$lastFeedUpdate = date('D h:i a', $lastFeedUpdate); 
?>
<li id='version'><?php echo Yii::app()->name.' Version '.Yii::app()->params['version'].' --- Last Updated: '.$lastFeedUpdate; ?></li>
