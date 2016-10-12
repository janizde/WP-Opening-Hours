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
    if (!is_admin() || get_user_option('rich_editing') !== 'true')
      return;

    // Register external plugins
    add_filter('mce_external_plugins', function ($plugins) {
      $plugins['op_shortcode_builder'] = plugins_url('dist/scripts/tinyMCE.js', op_bootstrap_file());
      $plugins['noneditable'] = plugins_url('dist/scripts/noneditable.js', op_bootstrap_file());
      return $plugins;
    });

    add_filter('mce_buttons', function ($buttons) {
      $buttons[] = 'op_shortcode_builder';
      return $buttons;
    });

    $shortcodeBuilders = array(
      new ShortcodeBuilder('op-overview', __('Overview', 'wp-opening-hours'), new OverviewForm())
    );

    $scbData = array_map(function (ShortcodeBuilder $scb) {
      return $scb->getShortcodeBuilderData();
    }, $shortcodeBuilders);

    wp_localize_script(OpeningHours::PREFIX . 'js', 'openingHoursShortcodeBuilders', $scbData);

    add_filter('mce_css', function ($urls) {
      if (!empty($urls))
        $urls .= ',';

      $urls .= plugins_url('dist/styles/tiny-mce.css', op_bootstrap_file());
      return $urls;
    });
  }
}