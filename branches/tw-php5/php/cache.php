<?php
	  function cache_setup()
	  {
	    global $config_values, $test_run;
	    if($test_run)
	      return;
	    if(isset($config_values['Settings']['Cache Dir'])) {
	      _debug("Enabling Cache\n", 2);
	      if(!file_exists($config_values['Settings']['Cache Dir']) ||
	          !is_dir($config_values['Settings']['Cache Dir'])) {
	        if(file_exists($config_values['Settings']['Cache Dir']))
	          unlink($config_values['Settings']['Cache Dir']);
	        mkdir($config_values['Settings']['Cache Dir'], 777, TRUE);
	      }
	    }
	  }

	  function add_cache($title) {
	    global $config_values;
	    if (isset($config_values['Settings']['Cache Dir'])) {
	      $cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($title);
	      touch($cache_file);
	    }
	  }

	  function clear_cache_real($file) {
	    global $config_values;
	    $fileglob = $config_values['Settings']['Cache Dir'].'/'.$file;
	    _debug("Clear: $fileglob\n",2);
	    foreach(glob($fileglob) as $fn) {
	      _debug("Removing $fn\n",1);
	      unlink($fn);
	    }
	  }

	  function clear_cache() {
	    if(isset($_GET['type'])) {
	      switch($_GET['type']) {
	        case 'feeds':
	          clear_cache_real("rsscache_*");
	          clear_cache_real("atomcache_*");
	          break;
	        case 'torrents':
	          clear_cache_real("rss_dl_*");
	          break;
	        case 'all':
	          clear_cache_real("rss_dl_*");
	          clear_cache_real("rsscache_*");
	          clear_cache_real("atomcache_*");
	          break;
	      }
	    }
	  }
	  /*
	   * Returns 1 if there is no cache hit(dl now)
	   * Returns 0 if there is a hit
	   */
	  function check_cache_episode($title) {
	    global $config_values, $matched;
	    // Dont skip a proper/repack
	    if(preg_match('/proper|repack/i', $title))
	      return 1;
	    $guess = guess_match($title, TRUE);
	    if($guess == False) {
	      _debug("Unable to guess for $title\n");
	      return 1;
	    }
	    if($handle = opendir($config_values['Settings']['Cache Dir'])) {
	      while(false !== ($file = readdir($handle))) {
	        if(!(substr($file, 0,7) == "rss_dl_"))
	          continue;
	        if(!(substr($file, 7, strlen($guess['key'])) == $guess['key']))
	          continue;
	        $cacheguess = guess_match(substr($file, 7), TRUE);
	        if($cacheguess != false && $guess['episode'] == $cacheguess['episode']) {
	          _debug("Full Episode Match, ignoring\n",2);
	          $matched = "duplicate";
	          return 0;
	        }
	      }
	    } else {
	      _debug("Unable to open ".$config_values['Settings']['Cache Dir']."\n");
	    }
	    return 1;
	  }


	  /* Returns 1 if there is no cache hit(dl now)
	   * Returns 0 if there is a hit
	   */
	  function check_cache($title)
	  {
	    global $config_values, $matched;

	    if (isset($config_values['Settings']['Cache Dir'])) {
	      $cache_file = $config_values['Settings']['Cache Dir'].'/rss_dl_'.filename_encode($title);
	      if (!file_exists($cache_file)) {
	        $matched = "match";
	        if($config_values['Settings']['Verify Episode']) {
	          return check_cache_episode($title);
	        } else {
	          return 1;
	        }
	      } else {
	        $matched = "cachehit";
	        return 0;
	      }
	    } else {
	      // No Cache, Always download
	      $matched = "match";
	      return 1;
	    }
	  }
?>
