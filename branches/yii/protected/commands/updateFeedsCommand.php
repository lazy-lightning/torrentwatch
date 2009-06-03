<?php

class updateFeedsCommand extends CConsoleCommand {
  public function run($args) {
    $feeds = feed::model()->findAll();
   
    foreach($feeds as $feed) {
      echo "Updating {$feed->title} from {$feed->url}\n";
      $feed->updateFeedItems(False); // false skips the check for favorites so we can check all at once
    }

    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
  }
}
