<?php
/**
 * Opening Hours: Module: Custom Post Type: Meta Box: Holidays
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\I18n;

use WP_Post;

class Holidays extends AbstractMetaBox {

    const ID                = 'op_meta_box_holidays';
    const POST_TYPE         = Set::CPT_SLUG;
    const TEMPLATE_PATH     = 'op-set-meta-box-holidays.php';
    const CONTEXT           = 'advanced';
    const PRIORITY          = 'core';

    const WP_NONCE_NAME     = 'op-set-holidays';
    const WP_NONCE_ACTION   = 'save_data';

    const PERIODS_META_KEY  = '_op_set_holidays';

    /**
     * Register Meta Box
     *
     * @access          public
     * @static
     */
    public static function registerMetaBox () {

        add_meta_box(
            static::ID,
            __( 'Holidays', I18n::TEXTDOMAIN ),
            array( get_called_class(), 'renderMetaBox' ),
            static::POST_TYPE,
            static::CONTEXT,
            static::PRIORITY
        );

    }

    /**
     * Render Meta Box
     *
     * @access          public
     * @static
     * @param           WP_Post         $post
     */
    public static function renderMetaBox ( WP_Post $post ) {

        $variables = array();

        echo static::renderTemplate( static::TEMPLATE_PATH, $variables, 'once' );

    }

    /**
     * Save Data
     *
     * @access          protected
     * @static
     * @param           int             $post_id
     * @param           WP_Post         $post
     * @param           bool            $update
     */
    protected static function saveData ( $post_id, WP_Post $post, $update ) {

    }


}