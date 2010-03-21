<?php

class tvEpisodeBlock extends Block
{
  public $tvEpisode;

  public function __construct($tvEpisode)
  {
    if(is_array($tvEpisode)) 
    {
      $title = $tvEpisode['tvShow_title']." : ".tvEpisode::model()->getEpisodeString($tvEpisode['season'], $tvEpisode['episode']);
      // fake being an object so we dont have to keep testing
      $this->tvEpisode = (object) $tvEpisode;
    }
    elseif($tvEpisode instanceof tvEpisode)
    {
      $title = $tvEpisode->tvShow->title." : ".$tvEpisode->getEpisodeString();
      $this->tvEpisode = $tvEpisode;
    }
    else
      throw new CException('Attempt to initialize '.__CLASS__.' with bad type: '.gettype($tvEpisode));

    parent::__construct($title, 1);
  }

  public function getName($length = false)
  {
    $name = $this->name;
    if($length && strlen($name) > $length)
      $name = substr($name,0,$length-4)." ...";
    return $name;
  }

  public function getDescription($lineCount = 6, $maxLength = 56, $elipse = '...')
  {
    $description = $this->tvEpisode->description;
    if(empty($description) && $this->tvEpisode instanceof tvEpisode)
      $description = $this->tvEpisode->tvShow->description;
    $description = array($description);
    for($i=0;isset($description[$i]);++$i)
    {
      if(strlen($description[$i]) > $maxLength)
      {
        $pos = strrpos(substr($description[$i], 0, $maxLength), ' ');
        $description[$i+1] = substr($description[$i], $pos+1);
        $description[$i] = substr($description[$i], 0, $pos);
        if($i === $lineCount)
        {
          unset($description[$i+1]);
          
          $description[$i] = substr($description[$i], 0, strlen($description[$i])-strlen($elipse)).$elipse;
        }
      }
    } 
    return $description;
  }
  public function getTime()
  {
    return $this->tvEpisode->lastUpdated;
  }
}
