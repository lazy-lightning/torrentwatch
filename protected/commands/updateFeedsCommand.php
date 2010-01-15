<?php

class updateFeedsCommand extends BaseConsoleCommand {
  public function run($args) {
    $app = Yii::app();
    $feeds = feed::model()->findAll();

    foreach($feeds as $feed) {
      echo "Updating {$feed->title} from {$feed->url}\n";
      $feed->updateFeedItems(False); // false skips the check for favorites so we can check all at once
    }
    $app->dlManager->checkFavorites(feedItem::STATUS_NEW);
  }
}
