<?php
/**
 * Opening Hours: View: Meta Box: Holiday
 */

use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Misc\ArrayObject;

/**
 * Pre-defined variables
 *
 * @var         $holidays           ArrayObject w/ Holiday objects
 */
?>

<div id="op-holidays-wrap">

    <?php Holidays::nonceField(); ?>

    <table class="op-holidays" id="op-holidays-table">
        <thead>
            <th>
                <?php _e( 'Name', I18n::TEXTDOMAIN ); ?>
            </th>

            <th>
                <?php _e( 'Date Start', I18n::TEXTDOMAIN ); ?>
            </th>

            <th>
                <?php _e( 'Date End', I18n::TEXTDOMAIN ); ?>
            </th>
        </thead>

        <tbody>
        <?php

        foreach ( $holidays as $holiday ) :

            echo Holidays::renderTemplate( Holidays::TEMPLATE_PATH_SINGLE, array(
                'holiday'   => $holiday
            ), 'always' );

        endforeach;

        ?>
        </tbody>
    </table>

    <button class="button button-primary add-holiday">
        <?php _e( 'Add New Holiday', I18n::TEXTDOMAIN ); ?>
    </button>

</div>