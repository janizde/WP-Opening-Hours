<?php

namespace OpeningHours\Persistence;

use OpeningHours\Core\DayOverride;
use OpeningHours\Core\Holiday;
use OpeningHours\Core\RecurringPeriod;
use OpeningHours\Core\RecurringPeriods;
use OpeningHours\Test\OpeningHoursTestCase;

class PostSpecStoreTest extends OpeningHoursTestCase {
  public function test__storeSpecification() {
    $rootEntry = getDeserializedSpecification();
    $store = new PostSpecStore(42);

    \WP_Mock::wpFunction('update_post_meta', [
      'times' => 1,
      'args' => [
        42,
        PostSpecStore::SPEC_META_KEY,
        getSerializedSpecification(),
      ]
    ]);

    $store->storeSpecification($rootEntry);
  }

  public function test__loadSpecification() {
    $store = new PostSpecStore(42);

    \WP_Mock::wpFunction('get_post_meta', [
      'times' => 1,
      'args' => [
        42,
        PostSpecStore::SPEC_META_KEY,
        true,
      ],
      'return' => getSerializedSpecification(),
    ]);

    $rootEntry = $store->loadSpecification();
    $this->assertEquals(getDeserializedSpecification(), $rootEntry);
  }
}

function getSerializedSpecification() {
  return [
    'kind' => RecurringPeriods::SPEC_KIND,
    'start' => '2020-03-02T00:00:00+00:00',
    'end' => '2020-10-01T00:00:00+00:00',
    'periods' => [
      [
        'startTime' => '12:00',
        'duration' => 6 * 60 * 60,
        'weekday' => 3,
      ]
    ],
    'children' => [
      [
        'kind' => RecurringPeriods::SPEC_KIND,
        'start' => '2020-05-01T00:00:00+00:00',
        'end' => '2020-08-01T00:00:00+00:00',
        'periods' => [],
        'children' => [],
      ],
      [
        'kind' => Holiday::SPEC_KIND,
        'name' => 'Foo Holiday',
        'start' => '2020-03-15T00:00:00+00:00',
        'end' => '2020-04-01T00:00:00+00:00',
      ],
      [
        'kind' => DayOverride::SPEC_KIND,
        'name' => 'Foo Override',
        'date' => '2020-04-15T00:00:00+00:00',
        'periods' => []
      ]
    ],
  ];
}

function getDeserializedSpecification() {
  $rpChild = new RecurringPeriods(new \DateTime('2020-05-01'), new \DateTime('2020-08-01'), [], []);
  $holiday = new Holiday('Foo Holiday', new \DateTime('2020-03-15'), new \DateTime('2020-04-01'));
  $dayOverride = new DayOverride('Foo Override', new \DateTime('2020-04-15'), []);

  return new RecurringPeriods(
    new \DateTime('2020-03-02'),
    new \DateTime('2020-10-01'),
    [
      new RecurringPeriod('12:00', 6 * 60 * 60, 3),
    ],
    [
      $rpChild,
      $holiday,
      $dayOverride,
    ]
  );
}
