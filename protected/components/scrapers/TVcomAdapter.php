<?php

class TVcomAdapter {

  public function getTvShowScraperByTitle($title, $accuracyLimit = 75)
  {
    try {
      $scraper = new TVcomScraper($title, $accuracyLimit);
    } catch (Exception $e) {
      return false;
    }
    if($scraper->accuracy < $accuracyLimit)
    {
      Yii::log('Failed scrape of $title', CLogger::LEVEL_ERROR, 'application.components.TVcomAdapter');
      $scraper = false;
    }
    return $scraper;
  }

  public function updateTvShowFromScraper($tvShow, $scraper)
  {
    if($scraper->banner && file_exists($scraper->banner))
    {
      $dest = $tvShow->getBannerLocation();
      if(file_exists($dest))
        unlink($dest);
      rename($scraper->banner, $dest);
      chmod($dest, 0644);
      return true;
    }
    return false;
  }
}
