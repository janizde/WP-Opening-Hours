<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Helpers;

class HelpersTest extends OpeningHoursTestCase {

  public function testUnsetEmptyValues () {
    $expected = array(
      'foo' => 'bar',
      'bar' => 'baz',
      'baz' => 'foo'
    );

    $data = array(
      'cat' => '',
      'foo' => 'bar',
      'dog' => '',
      'bar' => 'baz',
      'donkey' => '',
      'baz' => 'foo'
    );

    $this->assertEquals($expected, Helpers::unsetEmptyValues($data));
  }
}
