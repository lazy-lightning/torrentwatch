<?

Mock::generatePartial('client', 'MockClient', array('addByFile'));

class testOfClient extends TwUnitTestCase {
  function setUp() {
    parent::setUp();
    $defaults = array('key' => 'value', 'other' => 'data');
    $this->client = new MockClient();
    $this->client->__construct('unittest', $defaults);
    $this->client->setReturnValue('addByFile', True);
  }

  function tearDown() {
    unset($this->client);
    parent::tearDown();
  }

  function testOfAddByUrl() {
    
  } 
}
?>
