<?php
/**
 * Opening Hours: View: Meta Box: Holiday
 */

use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\ViewRenderer;

/**
 * Pre-defined variables
 *
 * @var         $holidays           ArrayObject w/ Holiday objects
 */
?>

<div id="op-holidays-wrap">
	<?php Holidays::getInstance()->nonceField(); ?>
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

		foreach ( $this->data['holidays'] as $holiday ) {
			$vr = new ViewRenderer( op_plugin_path() . Holidays::TEMPLATE_PATH_SINGLE, array(
				'holiday' => $holiday
			) );
			$vr->render();
		}
		?>
		</tbody>
	</table>

	<button class="button button-primary button-add add-holiday">
		<?php _e( 'Add New Holiday', I18n::TEXTDOMAIN ); ?>
	</button>

</div>