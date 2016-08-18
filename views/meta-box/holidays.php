<?php
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;

$holidays = Holidays::getInstance();
?>

<div id="op-holidays-wrap">
	<?php Holidays::getInstance()->nonceField(); ?>
	<table class="op-holidays" id="op-holidays-table">
		<thead>
		<th>
			<?php _e( 'Name', 'opening-hours' ); ?>
		</th>

		<th>
			<?php _e( 'Date Start', 'opening-hours' ); ?>
		</th>

		<th>
			<?php _e( 'Date End', 'opening-hours' ); ?>
		</th>
		</thead>

		<tbody>
		<?php foreach ( $this->data['holidays'] as $holiday ) $holidays->renderSingleHoliday( $holiday ); ?>
		</tbody>
	</table>

	<button class="button button-primary button-add add-holiday">
		<?php _e( 'Add New Holiday', 'opening-hours' ); ?>
	</button>
</div>