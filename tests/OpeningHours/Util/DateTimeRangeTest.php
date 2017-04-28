<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Entity\Holiday;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\DateTimeRange;

class DateTimeRangeTest extends OpeningHoursTestCase  {

  public function testSortObjectsSortsObjects() {
    $holidays = array(
      new Holiday('H1', new \DateTime('2017-04-18'), new \DateTime('2017-04-19')),
      new Holiday('H2', new \DateTime('2017-05-18'), new \DateTime('2017-05-19')),
      new Holiday('H3', new \DateTime('2017-04-17'), new \DateTime('2017-04-18')),
      new Holiday('H4', new \DateTime('2017-04-18'), new \DateTime('2017-04-19')),
    );

    /** @var $sorted Holiday[] */
    $sorted = DateTimeRange::sortObjects($holidays, false);

    $this->assertEquals('H3', $sorted[0]->getName());
    $this->assertEquals('H1', $sorted[1]->getName());
    $this->assertEquals('H4', $sorted[2]->getName());
    $this->assertEquals('H2', $sorted[3]->getName());
  }

  public function testSortObjectsRemovedPast() {
    $holidays = array(
      new Holiday('H1', new \DateTime('2017-04-18'), new \DateTime('2017-04-19')),
      new Holiday('H2', new \DateTime('2017-05-18'), new \DateTime('2017-05-19')),
      new Holiday('H3', new \DateTime('2017-04-17'), new \DateTime('2017-04-18')),
      new Holiday('H4', new \DateTime('2017-04-18'), new \DateTime('2017-04-19')),
    );

    /** @var $sorted Holiday[] */
    $sorted = DateTimeRange::sortObjects($holidays, true, new \DateTime('2014-04-20'));
    $this->assertEquals(4, count($sorted));

    $sorted = DateTimeRange::sortObjects($holidays, true, new \DateTime('2017-04-20 00:00:01'));
    $this->assertEquals(1, count($sorted));
  }
}
