<?php

class DbTestCase extends CDbTestCase
{
  protected function setUp()
  {
    $this->getFixtureManager()->setSubFixture(explode('::', $this->toString()));
    parent::setUp();
  }

  protected function tearDown()
  {
    parent::tearDown();
    $this->getFixtureManager()->resetSubFixture();
  }
}
