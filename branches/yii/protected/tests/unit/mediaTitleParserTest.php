<?php

/**
 * mediaTitleParserTest 
 * 
 * @uses CUnitTest
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class mediaTitleParserTest extends CTestCase
{
  public function testConstruct()
  {
    $parser = new mediaTitleParser('BBC.The.Frankincense.Trail.1of4.Omen.Yemen.Saudi.Arabia.XviD.AC3.MVGroup.org.avi');
    $this->assertEquals('BBC The Frankincense Trail', $parser->shortTitle);
    $this->assertEquals(1, $parser->season);
    $this->assertEquals(4, $parser->episode);
    $this->assertEquals('Omen Yemen Saudi Arabia', $parser->epTitle);
  }
}
