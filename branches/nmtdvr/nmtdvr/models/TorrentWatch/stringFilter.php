<?php
class stringFilter extends favFilterItem {
  static public function favFilter($favorite, $feedItem, $feedId) {

    $title = strtolower($feedItem->title);
    // Use the normalized short title against main filter where possible
    $shortTitle = strtolower(empty($feedItem->shortTitle) ? $title : $feedItem->shortTitle);

    // The main filter based on user matchstyle
    // should probably have TwConfig set a static variable in this class instead of fetching the config
    // or use a statefull approach?  might be overcomplicating things
    switch(TwConfig::getInstance()->matchStyle) {
      case 'simple':
      default:
        return (($favorite->filter != '' && strpos($shortTitle, strtolower($favorite->filter)) !== false) &&
         ($favorite->not == '' OR self::$my_strpos($title, strtolower($favorite->not)) === false) &&
         ($favorite->quality == 'All' OR $favorite->quality == '' OR self::$my_strpos($title, strtolower($favorite->quality)) !== false));
        break;
      case 'glob':
        return (($favorite->filter != '' && self::$fnmatch(strtolower('*'.$favorite->filter).'*', $shortTitle)) &&
         ($favorite->not == '' OR !self::$fnmatch('*'.strtolower($favorite->not).'*', $title)) &&
         ($favorite->quality == 'All' OR $favorite->quality == '' OR self::$fnmatch($title, '*'.strtolower($favorite->quality).'*') !== FALSE));
        break;
      case 'regexp':
        return (($favorite->filter != '' && preg_match('/'.strtolower($favorite->filter).'/', $shortTitle)) &&
         ($favorite->not == '' OR !preg_match('/'.strtolower($favorite->not).'/', $title)) &&
         ($favorite->quality == 'All' OR $favorite->quality == '' OR preg_match('/'.strtolower($favorite->quality).'/', $title)));
        break;
    }
  }

  // Custom strpos splits needle into space seperated tokens
  // returns True or False if one of those tokens is in the haystack
  static private function my_strpos($haystack, $needle) {
    $pieces = explode(" ", $needle);
    foreach($pieces as $n) {
      if(strpos($haystack, $n) !== False)
        return True;
    }
    return False;
  }

  // RegExp re-implementation when fnmatch doesn't exist
  static private function fnmatch($pattern, $string) {
    if(function_exists('fnmatch'))
      return fnmatch($pattern, $string);
    return @preg_match(
     '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
     array('*' => '.*', '?' => '.?', '[' => '\[', ']' => '\]')) . '$/i', $string
    );
  }

}

