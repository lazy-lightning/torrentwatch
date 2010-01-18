<?php
class mediaTitleParser {

  static protected $titleMatchers = array(
    'Full',
    'Date',
    'Partial',
    'Short',
  );

  /**
   * This is the main programatic entrance from outside this object
   * When passed a title this function will reaturn an array indicating
   * the information it was able to detect from the title.
   * NOTE: Perhaps this should instead be an object to be instantiated with
   *       the title in the constructor, and the objects properties would reflect
   *       detected information.
   * @return array 6 element array in the following order:
   *     show title, episode title, season, episode, network, quality
   */
  static public function detect($title)
  {
    Yii::import('application.components.mediaTitleParser.*');

    list($shortTitle, $quality) = qualityMatch::run($title);

    foreach(self::getMatchers() as $matcher)
    {
      $result = $matcher->run($shortTitle);
      if($result)
      {
        $result[] = $quality;
        return $result;
      }
    }

    // default detect if no match found
    return array($shortTitle, '', 0, 0, '', $quality);
  }

  static public function getMatchers()
  {
    if(is_string(self::$titleMatchers[0]))
    {
      foreach(self::$titleMatchers as $index => $matcher)
      {
        $class = 'titleMatch'.$matcher;
        self::$titleMatchers[$index] = new $class;
      }
    }
    return self::$titleMatchers;
  }
}
