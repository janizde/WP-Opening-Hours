<?php
/**
 * Opening Hours: View: Meta Box: IrregularOpenings
 */

use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as MetaBox;
use OpeningHours\Util\ArrayObject;

/**
 * Pre-defined variables
 *
 * @var         $irregular_openings           ArrayObject w/ IrregularOpening objects
 */
?>

<div id="op-irregular-openings-wrap">

	<?php MetaBox::nonceField(); ?>

	<table class="op-irregular-openings" id="op-io-table">
		<thead>
		<th>
			<?php _e( 'Name', I18n::TEXTDOMAIN ); ?>
		</th>

		<th>
			<?php _e( 'Date', I18n::TEXTDOMAIN ); ?>
		</th>

		<th>
			<?php _e( 'Time Start', I18n::TEXTDOMAIN ); ?>
		</th>

		<th>
			<?php _e( 'Time End', I18n::TEXTDOMAIN ); ?>
		</th>
		</thead>

		<tbody>
		<?php

		foreach ( $irregular_openings as $io ) :

			echo MetaBox::renderTemplate( MetaBox::TEMPLATE_PATH_SINGLE, array(
				'io' => $io
			), 'always' );

		endforeach;

		?>
		</tbody>
	</table>

	<button class="button button-primary button-add add-io">
		<?php _e( 'Add New Irregular Opening', I18n::TEXTDOMAIN ); ?>
	</button>

</div>