<?php

class TV_Episodes extends TVDB
{
  public static function search($tvShowId, $season, $episode)
  {
    $params = array('action' => 'get_episode',
            'season' => (int)$season,
            'episode' => (int)$episode,
            'show_id' => $tvShowId);

    $data = self::request($params);

    if ($data) {
      libxml_use_internal_errors(true);
      $xml = simplexml_load_string($data);
      if($xml)
        return new TV_Episode($xml->Episode);
    }
    return false;
  }
}
