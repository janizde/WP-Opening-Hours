<?php
/** @var Period $period */
use OpeningHours\Entity\Period;

$period = $this->data['period'];
?>

<tr class="period">

	<td class="col-time-start">
		<input
			name="opening-hours[<?php echo $period->getWeekday(); ?>][start][]"
			type="text"
			class="input-timepicker input-time-end"
			value="<?php echo $period->getTimeStart( true ); ?>"/>
	</td>

	<td class="col-time-end">
		<input
			name="opening-hours[<?php echo $this->data['period']->getWeekday(); ?>][end][]"
			type="text"
			class="input-timepicker input-time-end"
			value="<?php echo $period->getTimeEnd( true ); ?>"/>
	</td>

	<td class="col-delete-period">
		<a class="button delete-period has-icon red">
			<i class="dashicons dashicons-no-alt"></i>
		</a>
	</td>

</tr>