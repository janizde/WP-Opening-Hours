<?php
/**
 * Opening Hours: Module: Widget: Irregular Openings
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\IrregularOpenings as IrregularOpeningsShortcode;


class IrregularOpenings extends AbstractWidget {

    const SHORTCODE     = 'op-holidays';

    /**
     * Initializer
     *
     * @access          protected
     */
    protected function init () {

        $this->setShortcode( IrregularOpeningsShortcode::getInstance() );

        $this->setWidgetId( 'widget_op_irregular_openings' );

        $this->setTitle( __( 'Opening Hours: Irregular Openings', I18n::TEXTDOMAIN ) );

        $this->setDescription( __( 'Lists up all Irregular Openings in the selected Set.', I18n::TEXTDOMAIN ) );

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
            'caption'       => __( 'Highlight active Irregular Opening', I18n::TEXTDOMAIN )
        ) );

        /**
         * Extended Fields
         */

        /** Field: Class Irregular Opening */
        $this->addField( 'class_io', array(
            'type'          => 'text',
            'caption'       => __( 'Irregular Opening <tr> class', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Class Highlighted */
        $this->addField( 'class_highlighted', array(
            'type'          => 'text',
            'caption'       => __( 'class for highlighted Irregular Opening', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Date Format */
        $this->addField( 'date_format', array(
            'type'          => 'text',
            'caption'       => __( 'PHP Date Format', I18n::TEXTDOMAIN ),
            'extended'      => true,

            'default_placeholder'   => true
        ) );

        /** Field: Time Format */
        $this->addField( 'time_format', array(
            'type'          => 'text',
            'caption'       => __( 'PHP Time Format', I18n::TEXTDOMAIN ),
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