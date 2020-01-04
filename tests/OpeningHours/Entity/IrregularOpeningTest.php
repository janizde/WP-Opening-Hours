<?php

namespace OpeningHours\Test\Entity;

use DateTime;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

class IrregularOpeningTest extends OpeningHoursTestCase {
  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructExceptionTimeStartInvalid() {
    new IrregularOpening('Test', '2016-01-03', '00:234', '23:59');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructExceptionTimeEndInvalid() {
    new IrregularOpening('Test', '2016-01-03', '00:23', '23:579');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructExceptionDateInvalid() {
    new IrregularOpening('Test', '2016-01-033', '00:23', '23:57');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructExceptionNameEmpty() {
    new IrregularOpening('', '2016-01-03', '00:23', '23:57');
  }

  public function testConstruct() {
    $io = new IrregularOpening('Test', '2016-02-03', '12:15', '23:20');
    $this->assertEquals('Test', $io->getName());
    $this->assertEquals(new DateTime('2016-02-03 00:00:00'), $io->getDate());
    $this->assertEquals(new DateTime('2016-02-03 12:15'), $io->getStart());
    $this->assertEquals(new DateTime('2016-02-03 23:20'), $io->getEnd());
    $this->assertFalse($io->isDummy());
  }

  public function testTimeEndNextDay() {
    $io = new IrregularOpening('Test', '2016-02-03', '13:00', '01:00');
    $this->assertEquals(new DateTime('2016-02-03 13:00'), $io->getStart());
    $this->assertEquals(new DateTime('2016-02-04 01:00'), $io->getEnd());
  }

  public function testIsInEffect() {
    $io = new IrregularOpening('Test', '2017-05-30', '07:30', '19:00');

    $this->assertFalse($io->isInEffect(new DateTime('2017-05-29 23:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 00:00:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 07:29:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 07:30:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 18:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 19:00:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 23:59:59')));
    $this->assertFalse($io->isInEffect(new DateTime('2017-05-31 00:00:00')));
  }

  public function testIsInEffectNextDay() {
    $io = new IrregularOpening('Test', '2017-05-30', '12:00', '01:00');

    $this->assertFalse($io->isInEffect(new DateTime('2017-05-29 23:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 00:00:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 07:29:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 07:30:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 18:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 19:00:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-30 23:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-31 00:00:00')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-31 00:59:59')));
    $this->assertTrue($io->isInEffect(new DateTime('2017-05-31 01:00:00')));
    $this->assertFalse($io->isInEffect(new DateTime('2017-05-31 01:00:01')));
  }

  public function testIsOpen() {
    $io = new IrregularOpening('Test', '2016-02-03', '13:00', '01:00');
    $before = new DateTime('2016-02-03 12:59');
    $first = new DateTime('2016-02-03 13:00');
    $mid = new DateTime('2016-02-03 17:00');
    $last = new DateTime('2016-02-04 00:59');
    $after = new DateTime('2016-02-04 01:01');

    $this->assertFalse($io->isOpen($before));
    $this->assertTrue($io->isOpen($first));
    $this->assertTrue($io->isOpen($mid));
    $this->assertTrue($io->isOpen($last));
    $this->assertFalse($io->isOpen($after));
  }

  public function testGetFormattedTimeRange() {
    $io = new IrregularOpening('Test', '2016-02-03', '13:00', '01:00');

    $this->assertEquals('13:00 - 01:00', $io->getFormattedTimeRange());
    $this->assertEquals('1300 - 0100', $io->getFormattedTimeRange('Hi'));
    $this->assertEquals('01:00 // 13:00', $io->getFormattedTimeRange(null, '%2$s // %1$s'));
  }

  public function testSortStrategy() {
    $io1 = new IrregularOpening('Test', '2016-02-03', '13:00', '01:00');
    $io2 = new IrregularOpening('Test', '2016-01-03', '14:00', '01:00');
    $io3 = new IrregularOpening('Test', '2016-04-03', '01:00', '01:00');
    $io4 = new IrregularOpening('Test', '2016-04-03', '01:00', '03:00');

    $ios = array($io1, $io2, $io3);
    usort($ios, array(get_class($io1), 'sortStrategy'));

    $this->assertEquals(array($io2, $io1, $io3), $ios);
    $this->assertEquals(0, IrregularOpening::sortStrategy($io3, $io4));
  }

  public function testCreateDummy() {
    $io = IrregularOpening::createDummy();

    $expectedDate = new DateTime('now');
    $expectedDate->setTime(0, 0, 0);

    $this->assertEquals('', $io->getName());
    $this->assertEquals($expectedDate, $io->getDate());
    $this->assertEquals($expectedDate, $io->getStart());
    $this->assertEquals($expectedDate, $io->getStart());
    $this->assertTrue($io->isDummy());
  }

  public function testCreatePeriod() {
    $io = new IrregularOpening('IO 1', '2016-09-24', '13:00', '03:00');
    $period = $io->createPeriod();

    $this->assertEquals(6, $period->getWeekday());
    $this->assertEquals(new DateTime('2016-09-24 13:00', Dates::getTimezone()), $period->getTimeStart());
    $this->assertEquals(new DateTime('2016-09-25 03:00', Dates::getTimezone()), $period->getTimeEnd());
  }

  public function testIsPast() {
    $io = new IrregularOpening('IO 1', '2017-04-28', '13:00', '19:00');

    $this->assertFalse($io->isPast(new DateTime('2017-04-27 23:59:59')));
    $this->assertFalse($io->isPast(new DateTime('2017-04-28 00:00:00')));
    $this->assertFalse($io->isPast(new DateTime('2017-04-28 13:00:00')));
    $this->assertFalse($io->isPast(new DateTime('2017-04-28 19:00:01')));
    $this->assertFalse($io->isPast(new DateTime('2017-04-28 19:00:01')));
    $this->assertFalse($io->isPast(new DateTime('2017-04-28 23:59:59')));
    $this->assertTrue($io->isPast(new DateTime('2017-04-29 00:00:00')));
    $this->assertTrue($io->isPast(new DateTime('2017-04-30 00:00:00')));
  }

  public function testHappensOnDate() {
    $io = new IrregularOpening('IO 1', '2017-04-28', '13:00', '19:00');

    $this->assertFalse($io->happensOnDate(new DateTime('2017-04-27')));
    $this->assertTrue($io->happensOnDate(new DateTime('2017-04-28')));
    $this->assertTrue($io->happensOnDate(new DateTime('2017-04-28 00:00:00')));
    $this->assertTrue($io->happensOnDate(new DateTime('2017-04-28 23:59:59')));
    $this->assertFalse($io->happensOnDate(new DateTime('2017-04-29')));
  }
}
