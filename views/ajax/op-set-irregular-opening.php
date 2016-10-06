<?php

use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as MetaBox;
use OpeningHours\Util\Dates;

/** @var \OpeningHours\Entity\IrregularOpening $io */
$io = $this->data['io'];

/** @var \OpeningHours\Entity\IrregularOpening $io */
$name = $io->getName();
$date = ( $io->isDummy() ) ? null : $io->getDate()->format( Dates::STD_DATE_FORMAT );
$timeStart = ( $io->isDummy() ) ? null : $io->getTimeStart()->format( Dates::STD_TIME_FORMAT );
$timeEnd = ( $io->isDummy() ) ? null : $io->getTimeEnd()->format( Dates::STD_TIME_FORMAT );
?>

<tr class="op-irregular-opening">
  <td class="col-name">
    <input type="text" class="widefat name"
           name="<?php echo MetaBox::POST_KEY; ?>[name][]" value="<?php echo $name; ?>">
  </td>

  <td class="col-date">
    <input type="text" class="widefat date input-gray"
           name="<?php echo MetaBox::POST_KEY; ?>[date][]" value="<?php echo $date; ?>">
  </td>

  <td class="col-time-start">
    <input type="text" class="widefat time-start input-timepicker input-gray"
           name="<?php echo MetaBox::POST_KEY; ?>[timeStart][]" value="<?php echo $timeStart; ?>">
  </td>

  <td class="col-time-end">
    <input type="text" class="widefat time-end input-timepicker input-gray"
           name="<?php echo MetaBox::POST_KEY; ?>[timeEnd][]" value="<?php echo $timeEnd; ?>">
  </td>

  <td class="col-remove">
    <button class="button button-remove remove-io has-icon"><i class="dashicons dashicons-no-alt"></i></button>
  </td>
</tr>