<?php
/**
 * Opening Hours: Module: Widget: IsOpen
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\IsOpen as IsOpenShortcode;

class IsOpen extends AbstractWidget {

    const SHORTCODE     = 'op-is-open';

    /**
     * Init
     *
     * @access          protected
     */
    protected function init () {

        $this->setShortcode( IsOpenShortcode::getInstance() );

        $this->setWidgetId( 'widget_op_is_open' );

        $this->setTitle( __( 'Opening Hours: Is Open Status' ) );

        $this->setDescription( __( 'Shows a box saying whether a specific set is currently open or closed based on Periods.', I18n::TEXTDOMAIN ) );

    }

    /**
     * Register Fields
     *
     * @access          protected
     */
    protected function registerFields () {

        /**
         * Standard Fields
         */

        /** Field: Title */
        $this->addField( 'title', array(
            'type'          => 'text',
            'caption'       => __( 'Title', I18n::TEXTDOMAIN )
        ) );

        /** Field: Set */
        $this->addField( 'set_id', array(
            'type'          => 'select',
            'caption'       => __( 'Set', I18n::TEXTDOMAIN ),
            'options'       => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' ),

            'options_strategy'  => 'callback'
        ) );

        /**
         * Extended Fields
         */

        /** Field: Open Text */
        $this->addField( 'open_text', array(
            'type'          => 'text',
            'caption'       => __( 'Caption if open', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Closed Text */
        $this->addField( 'closed_text', array(
            'type'          => 'text',
            'caption'       => __( 'Caption if closed', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Span Classes */
        $this->addField( 'classes', array(
            'type'          => 'text',
            'caption'       => __( 'Class for span', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );
    }

    /**
     * Widget Content
     *
     * @access          protected
     * @param           array           $args
     * @param           array           $instance
     */
    protected function widgetContent ( array $args, array $instance ) {

        echo IsOpenShortcode::getInstance()->renderShortcode( array_merge( $args, $instance ) );

    }

}