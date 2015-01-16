<?php
/**
 * Opening Hours: Module: CustomPostType: MetaBox: OpeningHours
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;

use WP_Post;

class OpeningHours extends AbstractMetaBox {

    const ID                = 'op_meta_box_opening_hours';
    const POST_TYPE         = Set::CPT_SLUG;
    const TEMPLATE_PATH     = 'op-set-meta-box.php';
    const CONTEXT           = 'advanced';
    const PRIORITY          = 'high';

    const WP_NONCE_NAME     = 'op-set-opening-hours';
    const WP_NONCE_ACTION   = 'save_data';

    const PERIODS_META_KEY  = '_op_set_periods';

    /**
     * Register Meta Box
     *
     * @access          public
     * @static
     */
    public static function registerMetaBox () {

        add_meta_box(
            static::ID,
            __( 'Opening Hours', I18n::TEXTDOMAIN ),
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

        OpeningHoursModule::setCurrentSetId( $post->ID );

        OpeningHoursModule::getCurrentSet()->addDummyPeriods();

        $variables = array(
            'post'      => $post,
            'set'       => OpeningHoursModule::getCurrentSet()
        );

        echo static::renderTemplate( static::TEMPLATE_PATH, $variables, 'always' );

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

        $config     = $_POST['opening-hours'];
        $newConfig  = array();

        foreach ( $config as $weekday => $dayConfig ) :
            for ( $i = 0; $i <= count( $dayConfig['start'] ); $i++ ) :

                if ( ( empty( $dayConfig['start'][ $i ] ) or empty( $dayConfig['end'][ $i ] ) ) or
                    ( $dayConfig['start'][ $i ] == '00:00' and $dayConfig['end'][ $i ] == '00:00' ) )
                    continue;

                $newConfig[]  = array(
                    'weekday'   => $weekday,
                    'timeStart' => $dayConfig['start'][ $i ],
                    'timeEnd'   => $dayConfig['end'][ $i ],
                    'dummy'     => false
                );

            endfor;
        endforeach;

        global $post;

        update_post_meta( $post->ID, static::PERIODS_META_KEY, $newConfig );

    }

}