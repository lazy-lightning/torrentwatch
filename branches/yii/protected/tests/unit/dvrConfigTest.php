<?php
class dvrConfigTest extends DbTestCase
{

  public $fixtures = array(
      'dvrConfig' => ':dvrConfig',
  );

  public function testSaveSubCategory($config = null)
  {
    if($config === null)
    {
      $config = new dvrConfig;
      $config->_apcKey = '';
      $config->init();
    }

    $string = 'http://192.168.0.51:9091/transmission/';
    $this->assertNotEquals($string, $config->clientTransRPC->baseApi);
    $config->clientTransRPC->baseApi = $string;
    $this->assertTrue($config->save());
    unset($config);

    $config = new dvrConfig;
    $config->_apcKey = '';
    $config->init();

    $this->assertEquals($string, $config->clientTransRPC->baseApi);

    // get the corresponding row from the db
    $this->assertEquals($string, Yii::app()->getDb()->createCommand(
        'SELECT dvrConfig.value FROM dvrConfig, dvrConfigCategory'.
        ' WHERE dvrConfigCategory_id = dvrConfigCategory.id'.
        '   AND dvrConfigCategory.title = "clientTransRPC"'.
        '   AND dvrConfig.key = "baseApi"'
      )->queryScalar()
    );
  }

  public function testDvrConfigSerialize()
  {
    // there was bug where sub categorys parent would be incorectly set after unserializing
    // it would initialize, and allow saving of main config, but saving to a sub category didn't work
    $config = new dvrConfig;
    $config->_apcKey = '';
    $config->init();
    $x = unserialize(serialize($config));
    $y = unserialize(serialize($x));
    $z = unserialize(serialize($y));

    $string = 'http://192.168.0.51:9091/transmission/';
    // if we start with the same string the test is pointless
    $this->assertNotEquals($string, $z->clientTransRPC->baseApi);
    $z->clientTransRPC->baseApi = $string;
    $this->assertTrue($z->save());
    $config = unserialize(serialize($z));

    $this->assertEquals($string, $config->clientTransRPC->baseApi);
  }
}
