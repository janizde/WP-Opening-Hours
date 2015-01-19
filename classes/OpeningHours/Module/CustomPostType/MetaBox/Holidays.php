<?php
/**
 * Opening Hours: Module: Custom Post Type: Meta Box: Holidays
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Holiday;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Module\CustomPostType\Set;

use WP_Post;

class Holidays extends AbstractMetaBox {

    const ID                    = 'op_meta_box_holidays';
    const POST_TYPE             = Set::CPT_SLUG;
    const TEMPLATE_PATH         = 'meta-box/holidays.php';
    const TEMPLATE_PATH_SINGLE  = 'ajax/op-set-holiday.php';
    const CONTEXT               = 'advanced';
    const PRIORITY              = 'core';

    const WP_NONCE_NAME         = 'op-set-holidays-nonce';
    const WP_NONCE_ACTION       = 'save_data';

    const HOLIDAYS_META_KEY     = '_op_set_holidays';

    const GLOBAL_POST_KEY       = 'opening-hours-holidays';

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

        OpeningHoursModule::setCurrentSetId( $post->ID );

        $set    = OpeningHoursModule::getCurrentSet();

        if ( !count( $set->getHolidays() ) )
            $set->getHolidays()->addElement( Holiday::createDummyPeriod() );

        $variables = array(
            'holidays'      => $set->getHolidays()
        );

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

        $config     = $_POST[ static::GLOBAL_POST_KEY ];

        $config     = static::filterPostConfig( $config );

        global $post;

        if ( is_array( $config ) and count( $config ) )
            update_post_meta( $post->ID, static::HOLIDAYS_META_KEY, $config );

    }

    /**
     * Filter Post Config
     * filters config array passed via $_POST
     *
     * @access          public
     * @static
     * @param           array           $config
     * @return          array
     */
    public static function filterPostConfig ( array $config ) {

        $new_config     = array();

        for ( $i = 0; $i < count( $config['name'] ); $i++ ) :

            if (
                !isset( $config['name'][$i] )
                or empty( $config['name'][$i] )

                or !isset( $config['dateStart'][$i] )
                or !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateStart'][$i] )

                or !isset( $config['dateEnd'][$i] )
                or !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateEnd'][$i] )
            ) :
                continue;
            endif;

            $new_config[]   = array(
                'name'          => $config['name'][$i],
                'dateStart'     => $config['dateStart'][$i],
                'dateEnd'       => $config['dateEnd'][$i]
            );

        endfor;

        return $new_config;

    }

}