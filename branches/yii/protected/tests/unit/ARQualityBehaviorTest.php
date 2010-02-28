<?php

class ARQualityBehaviorTest extends DbTestCase
{

  public $fixtures = array(
      'favoriteTvShow' => 'favoriteTvShow',
      'tvShow' => 'tvShow',
  );

  public function testNoNukeOnUnset()
  {
    // fetch a favorite, ensure it has a quality
    $fav = favoriteTvShow::model()->findByPk(1);
    $this->assertType('favoriteTvShow', $fav);
    $this->assertNotEquals(0, count($fav->asa('quality')->getQualityIds()), 'initial test');

    // re-fetch from db so ids arn't pre loaded
    $fav = favoriteTvShow::model()->findByPk(1);
    // save without fetching the ids manually
    $this->assertTrue($fav->save());
    // verify the ids didn't dissapear
    $this->assertNotEquals(0, count($fav->asa('quality')->getQualityIds()), 'after save');
  }
}

