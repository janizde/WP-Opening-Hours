<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\ViewRenderer;
use WP_Post;

/**
 * Meta Box implementation for Holidays meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class IrregularOpenings extends AbstractMetaBox {

  const TEMPLATE_PATH = 'meta-box/irregular-openings.php';
  const TEMPLATE_PATH_SINGLE = 'ajax/op-set-irregular-opening.php';

  const POST_KEY = 'opening-hours-irregular-openings';

  public function __construct () {
    parent::__construct('op_meta_box_irregular_openings', __('Irregular Openings', 'wp-opening-hours'), self::CONTEXT_ADVANCED, self::PRIORITY_DEFAULT);
  }

  /** @inheritdoc */
  public function registerMetaBox () {
    if (!$this->currentSetIsParent())
      return;

    parent::registerMetaBox();
  }

  /** @inheritdoc */
  public function renderMetaBox (WP_Post $post) {
    $set = $this->getSet($post->ID);

    if (count($set->getIrregularOpenings()) < 1)
      $set->getIrregularOpenings()->append(IrregularOpening::createDummy());

    $variables = array(
      'irregular_openings' => $set->getIrregularOpenings()
    );

    $view = new ViewRenderer(op_view_path(self::TEMPLATE_PATH), $variables);
    $view->render();
  }

  /** @inheritdoc */
  protected function saveData ( $post_id, WP_Post $post, $update ) {
    $ios = (array_key_exists(self::POST_KEY, $_POST) && is_array($postData = $_POST[self::POST_KEY]))
      ? $this->getIrregularOpeningsFromPostData($postData)
      : array();

    $persistence = new Persistence($post);
    $persistence->saveIrregularOpenings($ios);
  }

  /**
   * Creates an array of Irregular Openings from the POST data
   *
   * @param     array $data The post data for irregular openings
   *
   * @return    IrregularOpening[]
   */
  public function getIrregularOpeningsFromPostData ( array $data ) {
    $ios = array();
    for ($i = 0; $i < count($data['name']); $i++) {
      try {
        $io = new IrregularOpening($data['name'][$i], $data['date'][$i], $data['timeStart'][$i], $data['timeEnd'][$i]);
        $ios[] = $io;
      } catch (\InvalidArgumentException $e) {
        // ignore item
      }
    }
    return $ios;
  }
}