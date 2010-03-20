<?php

class TVcomTvShowScraper extends Scraper 
{
  public $siteUrl = "http://www.tv.com";
  public $searchUrl = "/search.php?type=Search&stype=ajax_search&search_type=all&qs=";

  public $banner = false;
  public $bannerFormat;
  public $description = '';
  public $name;
// not detected, copied from input
  public $title;

  public function __construct($title, $accuracyLimit = 75)
  {
    Yii::trace('Searching for details about '.$title, 'application.components.scrapers.TVcomTvShowScraper');
    $this->name = $title;

    $detailsUrl = $this->getDetailsUrl();
    if($detailsUrl && $this->accuracy >= $accuracyLimit)
    {
      $details = @file_get_contents($detailsUrl);
      if(!$details)
      {
        Yii::log('Failed fetching details page for '.$title.' at '.$detailsUrl, 
            CLogger::LEVEL_ERROR, 'application.components.scrapers.TVcomTvShowScraper');
        return;
      }

      if(preg_match('|/show/(\d+)/|', $detailsUrl, $idMatches))
        $this->id = $idMatches[1];
      $this->title = $title;
      $this->updateBanner($details);
      $this->updateDescription($details);
    }
  }

  public function getDetailsUrl()
  {
    $searchUrl = $this->siteUrl.$this->searchUrl;
    $html = @file_get_contents($searchUrl.urlencode($this->name));
    $searchMatches = $this->get_urls_from_html($html, '\/show\/\d+\/summary.html');
    $index = $this->best_match($this->name, $searchMatches[2]);
    return (isset($searchMatches[1][$index]) ? $searchMatches[1][$index] : false);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->title;
  }

  protected function updateBanner($details)
  {
    if(preg_match('/top_image.*url\(([^)]+)\)/', $details, $matches))
    {
      Yii::trace('Found banner for '.$this->name, 'application.components.scrapers.TVcomTvShowScraper');
      $this->banner = tempnam('./cache', 'TVcomTvShowScraper.');
      $this->bannerFormat = substr($matches[1], strrpos($matches[1], '.')+1);
      file_put_contents($this->banner, @file_get_contents($matches[1]));
    }
  }

  protected function updateDescription($details)
  {
    if(preg_match('|<p class="show_description[^"]*">(.*)</p>|', $details, $matches))
    {
      $this->description = strip_tags(preg_replace('|<span class="truncater">.*</span><span>|', '', $matches[1]));
      $this->description = trim($this->description, " \r\n\"'");
    }
  }
}
