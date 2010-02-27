<?php

interface IFeedAdapter
{
  /**
   * __construct 
   * 
   * @param feed $feed the model to work with
   * @return void
   */
  public function __construct($feed);

  /**
   * init will contain all network activity and prepare the
   * object for updating the database
   * 
   * @return boolean true on successfull init
   */
  public function init();

  /**
   * checkFeedItems will contain all database activity including
   * adding any new items from a feed
   * if a factory is not provided Yii::app()->modelFactory is to be used
   *
   * @param modelFactory the factory with which to initialize feed items
   * @return void
   */
  public function checkFeedItems($factory = null);
}
