<?php

class form {
  static $currentItem = False;
  static public function setItem($item) {
    self::$currentItem = $item;
  }

  static public function isSelected($item) {
    if($item == self::$currentItem)
      echo 'selected';
  }

  static public function displayTextInput($item, $key) {
    echo '
<tr>
  <td>'.ucwords($key).': </td>
  <td><input type="text" name="'.$key.'" value="'.$item->$key.'"></td>
</tr>
';
  }
}

