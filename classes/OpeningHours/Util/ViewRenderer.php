<?php

namespace OpeningHours\Util;

/**
 * Class ViewRenderer
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class ViewRenderer {

  /**
   * Path to the template which shall be rendered
   * @var       string
   */
  protected $template;

  /**
   * Associative data array
   * @var       array
   */
  protected $data;

  public function __construct ( $template, array $data ) {
    $this->template = $template;
    $this->data = $data;
  }

  /** Renders the template */
  public function render () {
    if (!file_exists($this->template))
      return;

    include $this->template;
  }

  /**
   * Returns the contents produced by the render method
   * @return    string    The contents of the rendered template
   */
  public function getContents () {
    ob_start();
    $this->render();
    $markup = ob_get_contents();
    ob_end_clean();
    return $markup;
  }
}