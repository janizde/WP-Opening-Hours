<?php

namespace OpeningHours\Module;

class ShortcodeBuilder extends AbstractModule {

  public function __construct () {
    if (is_admin()) {
      add_filter('mce_external_plugins', function ($plugins) {
        $plugins['op_shortcode_builder'] = plugins_url('assets/tinyMCE.js', op_bootstrap_file());
        return $plugins;
      });

      add_filter('mce_buttons', function ($buttons) {
        $buttons[] = 'op_shortcode_builder';
        return $buttons;
      });
    }
  }
}