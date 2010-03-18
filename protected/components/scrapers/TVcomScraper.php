<?php

class TVcomScraper extends Scraper 
{
  public $siteUrl = "http://www.tv.com";
  public $searchUrl = "/search.php?type=Search&stype=ajax_search&search_type=all&qs=";

  public $banner = false;
  public $bannerFormat;
  public $name;

  public function __construct($title, $accuracyLimit)
  {
    Yii::trace('Searching for details about '.$title, 'application.components.TVcomScraper');
    $this->name = $title;
    $this->searchUrl = $this->siteUrl.$this->searchUrl;
    $html = file_get_contents($this->searchUrl.urlencode($title));
    $matches = $this->get_urls_from_html($html, '\/show\/\d+\/summary.html');
    $index = $this->best_match($title, $matches[2]);
    if($this->accuracy >= $accuracyLimit)
    {
      $details = @file_get_contents($matches[1][$index]);
      if(preg_match('/top_image.*url\(([^)]+)\)/', $details, $matches))
      {
        Yii::trace('Found banner for '.$title, 'application.components.TVcomScraper');
        $this->banner = tempnam('./cache', 'TVcomScraper');
        $this->bannerFormat = substr($matches[1], strrpos($matches[1], '.')+1);
        file_put_contents($this->banner, file_get_contents($matches[1]));
      }
    }
  }
}
