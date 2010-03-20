<?php

class TVcomTvShowAdapter extends ScraperAdapter {

  public $table = 'TVcomTvShowAdapter';
  public $modelClass = 'tvShow';

  public $accuracyLimit = 75;

  public function createScanCommand($timeLimit, $attributes=array())
  {
    return parent::createScanCommand($timeLimit, array_merge($attributes, array('title')));
  }

  public function getScraper($resultRow)
  {
    $scraper = new TVcomTvShowScraper($resultRow['title'], $this->accuracyLimit);
    if($scraper->accuracy < $this->accuracyLimit)
      $scraper = false;
    return $scraper;
  }

  public function updateFromScraper($model_id, $scraper)
  {
    $model = CActiveRecord::model($this->modelClass)->findByPk($model_id);
    if(!$model instanceof $this->modelClass)
      return false;

    $retVal = true;
    if(empty($model->description) || strlen($scraper->description) > strlen($model->description))
    {
      $model->description = $scraper->description;
      $retVal = $model->save();
    }
    if(file_exists($scraper->banner))
    {
      if(!file_exists($model->getBannerLocation()))
        rename($scraper->banner, $model->getBannerLocation());
      else
        unlink($scraper->banner);
    }
    return $retVal;
  }
}
