<?php
use OpeningHours\Entity\Holiday;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Util\Dates;

/** @var Holiday $holiday  */
$holiday = $this->data['holiday'];
$name = $holiday->getName();
$dateStart = ( $holiday->isDummy() ) ? null : $holiday->getDateStart()->format( Dates::STD_DATE_FORMAT );
$dateEnd = ( $holiday->isDummy() ) ? null : $holiday->getDateEnd()->format( Dates::STD_DATE_FORMAT );
?>
<tr class="op-holiday">
	<td class="col-name">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[name][]" class="widefat" value="<?php echo $name; ?>" />
	</td>
	<td class="col-date-start">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[dateStart][]" class="widefat date-start input-gray" value="<?php echo $dateStart; ?>" />
	</td>
	<td class="col-date-end">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[dateEnd][]" class="widefat date-end input-gray" value="<?php echo $dateEnd; ?>" />
	</td>
	<td class="col-remove">
		<button class="button button-remove remove-holiday has-icon"><i class="dashicons dashicons-no-alt"></i></button>
	</td>
</tr>