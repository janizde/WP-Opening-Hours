<?php
/**
 * Opening Hours: Module: Custom Post Type: Meta Box: Donation
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\I18n;

use WP_Post;

class Donation extends AbstractMetaBox {

    /**
     * Constants
     */
    const ID                = 'op_meta_box_donation';
    const CONTEXT           = 'side';
    const PRIORITY          = 'high';
    const POST_TYPE         = Set::CPT_SLUG;

    const TEMPLATE_PATH     = 'meta-box/donation.php';

    const WP_NONCE_NAME     = 'op_meta_box_donation';
    const WP_NONCE_ACTION   = 'donate';

    /**
     * Register Meta Box
     *
     * @access          public
     * @static
     */
    public static function registerMetaBox () {

        add_meta_box(
            static::ID,
            __( 'Please Donate', I18n::TEXTDOMAIN ),
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
     * @param           WP_Post         $post
     * @static
     */
    public static function renderMetaBox ( WP_Post $post ) {

        echo static::renderTemplate( static::TEMPLATE_PATH );

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

        // Silence is golden

    }

}