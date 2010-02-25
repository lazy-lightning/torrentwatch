<?php

abstract class favoriteManagerFilterTest extends DbTestCase
{
  public $testClass;

  protected function realTest($attributes, $expected, $fav=null)
  {
    if($fav === null)
      $fav = CActiveRecord::model($this->testClass)->findByPk(1);
    $this->assertType($this->testClass, $fav);

    // dont use $fav->setAttributes because that limits the available setters
    foreach($attributes as $key => $value)
      $fav->$key = $value;

    $this->assertTrue($fav->save(), 'Save object before check favorite');
    Yii::app()->dlManager->checkFavorite($fav);

    foreach($expected as $status => $count)
      $this->assertEquals($count, feedItem::model()->count('status = :status', array(':status'=>constant("feedItem::$status"))), $status);
  }

}
