<?php

namespace OpeningHours\Test\Module\Shortcode;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\Period;
use OpeningHours\Module\Shortcode\OverviewModel;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

class OverviewModelTest extends OpeningHoursTestCase {

  protected $oldStartOfWeek;

  public function setUp () {
    parent::setUp();
    $this->oldStartOfWeek = Dates::getStartOfWeek();
    Dates::setStartOfWeek(2);
  }

  public function tearDown () {
    parent::tearDown();
    Dates::setStartOfWeek($this->oldStartOfWeek);
  }

  public function testConstructAndPeriods () {

    /** @var Period[] $periods */
    $periods = array(
      new Period(2, '09:00', '13:00'),
      new Period(2, '14:00', '15:00'),
      new Period(0, '09:00', '13:00')
    );

    $dt = new \DateTime('2016-09-28');
    $model = new OverviewModel($periods, new \DateTime('2016-09-28'));
    $this->assertEquals(new \DateTime('2016-09-28'), $model->getNow());
    $this->assertEquals(new \DateTime('2016-09-27'), $model->getMinDate());
    $this->assertEquals(new \DateTime('2016-10-04'), $model->getMaxDate());

    $data = $model->getData();

    // Weekday Assertions
    $this->assertEquals(7, count($data));
    $this->assertEquals(2, $data[0]['days'][0]->getIndex());
    $this->assertEquals(3, $data[1]['days'][0]->getIndex());
    $this->assertEquals(4, $data[2]['days'][0]->getIndex());
    $this->assertEquals(5, $data[3]['days'][0]->getIndex());
    $this->assertEquals(6, $data[4]['days'][0]->getIndex());
    $this->assertEquals(0, $data[5]['days'][0]->getIndex());
    $this->assertEquals(1, $data[6]['days'][0]->getIndex());

    // Period Assertions
    $this->assertEquals(array($periods[0]->getCopyInDateContext($dt), $periods[1]->getCopyInDateContext($dt)), $data[0]['items']);
    $this->assertEquals(array(), $data[1]['items']);
    $this->assertEquals(array(), $data[2]['items']);
    $this->assertEquals(array(), $data[3]['items']);
    $this->assertEquals(array(), $data[4]['items']);
    $this->assertEquals(array($periods[2]->getCopyInDateContext($dt)), $data[5]['items']);
    $this->assertEquals(array(), $data[6]['items']);
  }

  public function testMergeHolidays () {
    $dt = new \DateTime('2016-09-28');
    $model = new OverviewModel(array(), $dt);
    $holiday = new Holiday('H', new \DateTime('2016-08-20'), new \DateTime('2016-10-20'));
    $model->mergeHolidays(array($holiday));
    $data = $model->getData();

    foreach ($data as $day) {
      $this->assertEquals($holiday, $day['items']);
    }

    $holiday = new Holiday('H', new \DateTime('2016-08-20'), new \DateTime('2016-10-01'));
    $model = new OverviewModel(array(), $dt);
    $model->mergeHolidays(array($holiday));
    $data = $model->getData();

    $this->assertEquals($holiday, $data[0]['items']);
    $this->assertEquals($holiday, $data[1]['items']);
    $this->assertEquals($holiday, $data[2]['items']);
    $this->assertEquals($holiday, $data[3]['items']);
    $this->assertEquals($holiday, $data[4]['items']);
    $this->assertEquals(array(), $data[5]['items']);
    $this->assertEquals(array(), $data[6]['items']);

    $holiday = new Holiday('H', new \DateTime('2016-09-29'), new \DateTime('2016-10-20'));
    $model = new OverviewModel(array(), $dt);
    $model->mergeHolidays(array($holiday));
    $data = $model->getData();

    $this->assertEquals(array(), $data[0]['items']);
    $this->assertEquals(array(), $data[1]['items']);
    $this->assertEquals($holiday, $data[2]['items']);
    $this->assertEquals($holiday, $data[3]['items']);
    $this->assertEquals($holiday, $data[4]['items']);
    $this->assertEquals($holiday, $data[5]['items']);
    $this->assertEquals($holiday, $data[6]['items']);

    $holiday = new Holiday('H', new \DateTime('2016-09-29'), new \DateTime('2016-10-02'));
    $model = new OverviewModel(array(), $dt);
    $model->mergeHolidays(array($holiday));
    $data = $model->getData();

    $this->assertEquals(array(), $data[0]['items']);
    $this->assertEquals(array(), $data[1]['items']);
    $this->assertEquals($holiday, $data[2]['items']);
    $this->assertEquals($holiday, $data[3]['items']);
    $this->assertEquals($holiday, $data[4]['items']);
    $this->assertEquals($holiday, $data[5]['items']);
    $this->assertEquals(array(), $data[6]['items']);

    $holiday1 = new Holiday('H1', new \DateTime('2016-09-29'), new \DateTime('2016-09-30'));
    $holiday2 = new Holiday('H2', new \DateTime('2016-10-02'), new \DateTime('2016-10-03'));
    $model = new OverviewModel(array(), $dt);
    $model->mergeHolidays(array($holiday1, $holiday2));
    $data = $model->getData();

    $this->assertEquals(array(), $data[0]['items']);
    $this->assertEquals(array(), $data[1]['items']);
    $this->assertEquals($holiday1, $data[2]['items']);
    $this->assertEquals($holiday1, $data[3]['items']);
    $this->assertEquals(array(), $data[4]['items']);
    $this->assertEquals($holiday2, $data[5]['items']);
    $this->assertEquals($holiday2, $data[6]['items']);
  }
}