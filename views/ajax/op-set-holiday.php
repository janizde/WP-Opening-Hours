<?php use OpeningHours\Module\CustomPostType\MetaBox\Holidays; ?>
<tr class="op-holiday">
	<td class="col-name">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[name][]" class="widefat" value="<?php echo $this->data['name']; ?>" />
	</td>
	<td class="col-date-start">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[dateStart][]" class="widefat date-start input-gray" value="<?php echo $this->data['dateStart']; ?>" />
	</td>
	<td class="col-date-end">
		<input type="text" name="<?php echo Holidays::POST_KEY; ?>[dateEnd][]" class="widefat date-end input-gray" value="<?php echo $this->data['dateEnd']; ?>" />
	</td>
	<td class="col-remove">
		<button class="button button-remove remove-holiday has-icon"><i class="dashicons dashicons-no-alt"></i></button>
	</td>
</tr>