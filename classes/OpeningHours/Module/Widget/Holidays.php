<?php
/**
 * Opening Hours: Module: Widget: Overview
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\Holidays as HolidaysShortcode;

class Holidays extends AbstractWidget {

    const SHORTCODE     = 'op-holidays';

    /**
     * Initializer
     *
     * @access          protected
     */
    protected function init () {

        $this->setShortcode( HolidaysShortcode::getInstance() );

        $this->setWidgetId( 'widget_op_holidays' );

        $this->setTitle( __( 'Opening Hours: Holidays', I18n::TEXTDOMAIN ) );

        $this->setDescription( __( 'Lists up all Holidays in the selected Set.', I18n::TEXTDOMAIN ) );

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

        /** Field: Set Id */
        $this->addField( 'set_id', array(
            'type'          => 'select',
            'caption'       => __( 'Set', I18n::TEXTDOMAIN ),
            'options'       => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' ),

            'options_strategy'  => 'callback'
        ) );

        /** Field: Highlight */
        $this->addField( 'highlight', array(
            'type'          => 'checkbox',
            'caption'       => __( 'Highlight active Holiday', I18n::TEXTDOMAIN )
        ) );

        /**
         * Extended Fields
         */

        /** Field: Class Holiday */
        $this->addField( 'class_holiday', array(
            'type'          => 'text',
            'caption'       => __( 'Holiday <tr> class', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Class Highlighted */
        $this->addField( 'class_highlighted', array(
            'type'          => 'text',
            'caption'       => __( 'class for highlighted Holiday', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Date Format */
        $this->addField( 'date_format', array(
            'type'          => 'text',
            'caption'       => __( 'Date Format', I18n::TEXTDOMAIN ),
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

        echo $this->getShortcode()->renderShortcode( array_merge( $args, $instance ) );

    }

}