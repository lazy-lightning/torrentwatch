<?php

abstract class BaseController extends CController {
  private $_resolution;
  public $imageRoot;

  /**
   * @return array list of items to be used as the side bar menu
   */
  public function getMainMenuItems() {
    return array(
        array('name'=>'index', 'label'=>'NMTDVR Home', 'url'=>array('site/index')),
        array('name'=>'favorites', 'label'=>'Favorites', 'url'=>array('site/favorites')),
        array('name'=>'config', 'label'=>'Configuration', 'url'=>array('configuration/list')),
    );
  }

  /**
   * @return string the resolution(hd/sd) of the nmt requestin the page
   */
  public function getResolution() {
    return $this->_resolution;
  }

  /**
   * Point the imageRoot and layout to the appropriate pages for this request
   * @return none
   */
  public function init() {
    date_default_timezone_set(Yii::app()->dvrConfig->timezone);
    // sd if reported in user agent, otherwise default to hd
    $this->_resolution = stristr($_SERVER['HTTP_USER_AGENT'], 'Res720x576') === False?'hd':'sd';
    // if the user agent is an NMT give it local filepath, otherwise based off script
    if(stristr($_SERVER['HTTP_USER_AGENT'], 'Syabas') === False) 
      $this->imageRoot = dirname($_SERVER['SCRIPT_NAME']).'/assets/images/';
    else
      $this->imageRoot = 'file:///opt/sybhttpd/localhost.images/';

    $this->imageRoot .= $this->_resolution.'/';

    // Switch to ajax view when required
    if(Yii::app()->request->isAjaxRequest)
      $this->layout = 'ajax';
    else
      $this->layout = 'main_'.$this->_resolution;
  }

  /**
   * Prepares a set of links to be used by the NMT view
   */
  public function prepareListItems($in, $index = 1) {
    $out = array();
    $mWidth = $this->_resolution==='hd'?560:290;
    foreach($in as $item) {
      $item2 = array();
      $item2['icon'] =$this->imageRoot.(isset($item['icon'])?$item['icon']:'list_folder').'.png';
      $item2['index'] = $index++;
      $item2['label'] = "<marquee behavior='focus' width='$mWidth'>&nbsp;{$item['label']}</marquee>";
      $item2['name'] =  isset($item['name'])?$item['name']:strtok($item['label'], ' ');
      $item2['tvid'] = isset($item['tvid'])?$item['tvid']:$item2['index'];
      $item2['url'] = $item['url'];
      $out[] = $item2;
    }
    return $out;
  }
}
