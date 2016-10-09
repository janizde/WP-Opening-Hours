<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Form\OverviewForm;
use OpeningHours\Module\AbstractModule;
use OpeningHours\OpeningHours;

class ShortcodeBuilders extends AbstractModule {

  public function __construct () {
    add_action('admin_head', array($this, 'registerHookCallbacks'));
  }

  public function registerHookCallbacks () {
    if (!is_admin())
      return;

    add_filter('mce_external_plugins', function ($plugins) {
      $plugins['op_shortcode_builder'] = plugins_url('assets/tinyMCE.js', op_bootstrap_file());
      return $plugins;
    });

    add_filter('mce_buttons', function ($buttons) {
      $buttons[] = 'op_shortcode_builder';
      return $buttons;
    });

    $shortcodeBuilders = array(
      new ShortcodeBuilder('op-overview', new OverviewForm())
    );

    $scbData = array_map(function (ShortcodeBuilder $scb) {
      return $scb->getShortcodeBuilderData();
    }, $shortcodeBuilders);

    wp_localize_script(OpeningHours::PREFIX . 'js', 'openingHoursShortcodeBuilders', $scbData);
  }
}