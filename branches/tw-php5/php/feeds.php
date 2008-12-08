<?php
 function get_torrent_link($rs) {
  if(isset($rs['id'])) { // Atom
    if(stristr($rs['id'], 'torrent')) // torrent link in id
      $link = $rs['id'];
    else // torrent hidden in summary
      $link = guess_atom_torrent($rs['summary']);
  } else if(isset($rs['enclosure'])) { // RSS Enclosure
    $link = $rs['enclosure']['url'];
  } else {  // Standard RSS
    $link = $rs['link'];
  }
  return html_entity_decode($link);
}

function check_for_torrent(&$item, $key, $opts) {
  global $matched, $test_run, $config_values;

  if(!($item['Feed'] == 'all' || $item['Feed'] == $opts['URL']))
    return;

  $rs = $opts['Obj'];
  $title = strtolower($rs['title']);
  $guess = guess_match($title);
  switch(_isset($config_values['Settings'], 'MatchStyle')) {
    case 'simple':  
      $hit = (($item['Filter'] != '' && strpos($title, strtolower($item['Filter'])) !== FALSE) &&
       ($item['Not'] == '' OR my_strpos($title, strtolower($item['Not'])) === FALSE) &&
       ($item['Quality'] == 'All' OR $item['Quality'] == '' OR my_strpos($title, strtolower($item['Quality'])) !== FALSE) &&
       ($item['Episodes'] == '' OR preg_match('/^'.strtolower($item['Episodes']).'$/', $guess['episode'])) );
      break;
    case 'glob':
      $hit = (($item['Filter'] != '' && fnmatch(strtolower($item['Filter']), $title)) &&
       ($item['Not'] == '' OR !fnmatch(strtolower($item['Not']), $title)) &&
       ($item['Quality'] == 'All' OR $item['Quality'] == '' OR strpos($title, strtolower($item['Quality'])) !== FALSE) &&
       ($item['Episodes'] == '' OR preg_match('/^'.strtolower($item['Episodes']).'$/', $guess['episode'])) );
      break;
    case 'regexp':
    default:
      $hit = (($item['Filter'] != '' && preg_match('/'.strtolower($item['Filter']).'/', $title)) &&
       ($item['Not'] == '' OR !preg_match('/'.strtolower($item['Not']).'/', $title)) &&
       ($item['Quality'] == 'All' OR $item['Quality'] == '' OR preg_match('/'.strtolower($item['Quality']).'/', $title)) &&
       ($item['Episodes'] == '' OR preg_match('/^'.strtolower($item['Episodes']).'$/', $guess['episode'])) );
      break;
  }
  if($hit) {
    _debug('Match found for '.$rs['title']."\n");
    if(check_cache($rs['title'])) {
      if($test_run)
        return;
      if($link = get_torrent_link($rs)) {
        if(isset($config_values['Global']['HTMLOutput']))
          update_progress_bar(0, "Starting $title");
        if(stripos($config_values['Settings']['Client'],'nzb') !== FALSE) {
          if(client_add_nzb($link, $item, $opts['URL']))
            add_cache($rs['title']);
        } else if(client_add_torrent($link, NULL, $item, $opts['URL'])) {
          add_cache($rs['title']);
        } else {
          _debug("Failed adding torrent $link\n", -1);
          return FALSE;
        }

      } else {                     
        _debug("Unable to find URL for ".$rs['title']."\n", -1);
        $matched = "nourl";
      }
    }
  }
}

function parse_one_rss($feed) {
  global $config_values;
  $rss = new lastRSS;
  $rss->stripHTML = True;
  $rss->cache_time = 50*60;
  $rss->date_format = 'M j h:ia';

  if(isset($config_values['Settings']['Cache Dir']))
    $rss->cache_dir = $config_values['Settings']['Cache Dir'];
  if(!$config_values['Global']['Feeds'][$feed['Link']] = $rss->get($feed['Link']))
    _debug("Error creating rss parser for ".$feed['Link']."\n",-1);
  else {
    $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
    $config_values['Global']['Feeds'][$feed['Link']]['Feed Type'] = 'RSS';
  }
  return;
}
    
function parse_one_atom($feed) {
  global $config_values;
  if(isset($config_values['Settings']['Cache Dir']))
    $atom_parser = new myAtomParser($feed['Link'], $config_values['Settings']['Cache Dir']);
  else
    $atom_parser = new myAtomParser($feed['Link']);

  if(!$config_values['Global']['Feeds'][$feed['Link']] = $atom_parser->getRawOutput())
    _debug("Error creating atom parser for ".$feed['Link']."\n",-1);
  else {
    $config_values['Global']['Feeds'][$feed['Link']]['URL'] = $feed['Link'];
    $config_values['Global']['Feeds'][$feed['Link']]['Feed Type'] = 'Atom';
  }
  return;
}

function rss_perform_matching($rs, $idx) {
  global $config_values, $matched;
  $percPerFeed = 80/count($config_values['Feeds']);
  $percPerItem = $percPerFeed/count($rs['items']);
  if(isset($config_values['Global']['HTMLOutput'])) {
    show_feed_html($rs, $idx);
  }
  $alt = 'alt';
  // echo(print_r($rs));
  foreach($rs['items'] as $item) {
    $matched = "nomatch";
    if(isset($config_values['Favorites']))
      array_walk($config_values['Favorites'], 'check_for_torrent', 
                 array('Obj' =>$item, 'URL' => $rs['URL']));
    if($matched == "nomatch") {
      _Debug("No match for $item[title]\n", 2);
    }
    if(isset($config_values['Global']['HTMLOutput'])) {
      update_progress_bar($percPerItem, $item['title']);
      show_torrent_html($item, $rs['URL'], $alt);
    }
    
    if($alt=='alt') {
      $alt='';
    } else {
      $alt='alt';
    }
  }
  if(isset($config_values['Global']['HTMLOutput']))
    close_feed_html();
  unset($item);
}
function atom_perform_matching($atom, $idx) {
  global $config_values, $matched;
  $atom  = array_change_key_case_ext($atom, ARRAY_KEY_LOWERCASE);
  if(isset($config_values['Global']['HTMLOutput']))
    show_feed_html($atom['feed'], $idx);
  $alt='alt';
  
  foreach($atom['feed']['entry'] as $item) {
    $matched = "nomatch";
    array_walk($config_values['Favorites'], 'check_for_torrent', 
               array('Obj' =>$item, 'URL' => $atom['URL']));
    if($matched == "nomatch") {
      _debug("No match for ".$item['title']."\n");
    }
    if(isset($config_values['Global']['HTMLOutput'])) {
      show_torrent_html($item, $key, $alt);
    }

    if($alt=='alt') {
      $alt='';
    } else {
        $alt='alt';
    }
    unset($item);
  }
}

function feeds_perform_matching($feeds) {
  global $config_values;
  if(isset($config_values['Global']['HTMLOutput']))
    setup_rss_list_html();
  cache_setup();
  foreach($feeds as $key => $feed) {
    switch($feed['Type']) {
      case 'RSS':
        rss_perform_matching($config_values['Global']['Feeds'][$feed['Link']], $key);
        break;
      case 'Atom':
        atom_perform_matching($config_values['Global']['Feeds'][$feed['Link']], $key);
        break;
      default:
        _debug("Unknown Feed. Feed: ".$feed['Link']."Type: ".$feed['Type']."\n",-1);
        break;
    }
  }

  if(isset($config_values['Global']['HTMLOutput']))
    finish_rss_list_html();
}

function load_feeds($feeds) {
  global $config_values;
  $count = count($feeds);
  foreach($feeds as $feed) {
    if(isset($config_values['Global']['HTMLOutput']))
      update_progress_bar(10/$count, "Loading ".$feed['Type']." feed from ".$feed['Link']); // Load feeds uses 10%
    switch($feed['Type']){
      case 'RSS':
        parse_one_rss($feed);
        break;
      case 'Atom':
        parse_one_atom($feed);
        break;
      default:
        _debug("Unknown Feed. Feed: ".$feed['Link']."Type: ".$feed['Type']."\n",-1);
        break;
    }
  }
}

?>
