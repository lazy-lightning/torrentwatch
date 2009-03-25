<?php

	/**
	 * TV shows class, basic searching functionality
	 * 
	 * @package PHP::TVDB
	 * @author Ryan Doherty <ryan@ryandoherty.com>
	 */

	class TV_Shows extends TVDB {

		/**
		 * Searches for tv shows based on show name
		 * 
		 * @var string $showName the show name to search for
		 * @access public 
		 * @return array An array of TV_Show objects matching the show name
		 **/
		public static function search($showName) {
      if(!$shows = DataCache::Get(__CLASS__.__FUNCTION__, $showName)) {
  			$params = array('action' => 'search_tv_shows', 'show_name' => $showName);
	  		$data = self::request($params);
		  	
			  if($data) {
  				$xml = simplexml_load_string($data);
	  			$shows = array();
		  		foreach($xml->Series as $show) {
			  		$shows[] = self::findById((string)$show->seriesid);
				  }
	      }

        if(!empty($shows))
          DataCache::Put(__CLASS__.__FUNCTION__, $showName, 60*60*24*30, $shows);
      }
  		return $shows;
		}
		
		/**
		 * Find a tv show by the id from thetvdb.com
		 *
		 * @return TV_Show|false A TV_Show object or false if not found
		 **/
		public static function findById($showId) {
      if(!$show = DataCache::Get(__CLASS__.__FUNCTION__, $showId)) {
  			$params = array('action' => 'show_by_id', 'id' => $showId);
	  		$data = self::request($params);
		
			
		  	if ($data) {
			  	$xml = simplexml_load_string($data);
				  $show = new TV_Show($xml->Series);
  			} else {
  				return false;
  			}
        DataCache::Put(__CLASS__.__FUNCTION__, $showId, 60*60*24*30, $show);
      }
  		return $show;
		}
	}

?>
