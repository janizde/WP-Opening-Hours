<?php

use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours as MetaBox;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\ViewRenderer;
use OpeningHours\Util\Weekdays;

MetaBox::getInstance()->nonceField();
?>

<div class="opening-hours">
	<table class="form-table form-opening-hours">
		<tbody>
		<?php foreach ( Weekdays::getWeekdays() as $weekday ) : ?>
			<tr class="periods-day">
				<td class="col-name" valign="top">
					<?php echo $weekday->getName(); ?>
				</td>

				<td class="col-times" colspan="2" valign="top">
					<div class="period-container" data-day="<?php echo $weekday->getIndex(); ?>"
					     data-set="<?php echo OpeningHours::getCurrentSet()->getId(); ?>">

						<table class="period-table">
							<tbody>
							<?php foreach ( OpeningHours::getCurrentSet()->getPeriodsByDay( $weekday->getIndex() ) as $period ) {
								$vr = new ViewRenderer(op_view_path(MetaBox::TEMPLATE_PATH_SINGLE), array(
									'period' => $period
								) );
								$vr->render();
							} ?>
							</tbody>
						</table>

					</div>
				</td>

				<td class="col-options" valign="top">
					<a class="button add-period green has-icon">
						<i class="dashicons dashicons-plus"></i>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>