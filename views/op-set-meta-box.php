<?php
/**
 *	Opening Hours: Template: Part: MetaBox OP Set
 */

use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours as MetaBox;

MetaBox::nonceField();
?>

<div class="opening-hours">

  <table class="form-table form-opening-hours">
    <tbody>
      <?php foreach ( I18n::getWeekdaysNumeric() as $id => $name ) : ?>
      <tr class="periods-day">
        <td class="col-name" valign="top">
          <?php echo $name; ?>
        </td>

        <td class="col-times" colspan="2" valign="top">
          <div class="period-container" data-day="<?php echo $id; ?>" data-set="<?php echo OpeningHours::getCurrentSet()->getId(); ?>">

            <table class="period-table">
              <tbody>

              <?php
              foreach ( OpeningHours::getCurrentSet()->getPeriodsByDay( $id ) as $period ) :
                echo OpeningHours::renderTemplate(
                  'ajax/op-set-period.php',
                  array(
                    'period'  => $period
                  ),
                  'always'
                );
              endforeach;

              ?>

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

<input type="hidden" name="op-controller-action" value="saveOpSet" />

<script type="text/html" id="opTemplatePeriodRow">
  <tr class="period">

    <td class="col-time-start">
      <input
        type="text"
        class="input-timepicker input-start-time" />
    </td>

    <td class="col-time-end">
      <input
        type="text"
        class="input-timepicker input-end-time" />
    </td>

    <td class="col-delete-period">
      <a class="button delete-period has-icon red">
        <i class="dashicons dashicons-no-alt"></i>
      </a>
    </td>

  </tr>
</script>
