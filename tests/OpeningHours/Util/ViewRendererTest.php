<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\ViewRenderer;

class ViewRendererTest extends OpeningHoursTestCase {
  public function test_viewRenderer() {
    $data = array(
      'firstName' => 'Peter',
      'foo' => 'Cat',
      'bar' => 'Dog'
    );

    $template = __DIR__ . '/../../views/test-view.php';

    $viewRenderer = new ViewRenderer($template, $data);
    $expected = "Hello Peter,\nCat Dog.";

    $this->assertEquals($expected, $viewRenderer->getContents());
  }
}
