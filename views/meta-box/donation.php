<div class="paypal-donation-controls">

	<select id="op-paypal-select-amount">

		<?php

		foreach ( range( 5, 100, 5 ) as $a ) :

			$selected = ( $a == 10 ) ? 'selected="selected"' : null;

			echo '<option value="' . sprintf( '%d.00', $a ) . '" ' . $selected . '>$ ' . $a . '</option>';
		endforeach;

		$locale = get_locale();

		if ( ! preg_match( '/[a-z]{2}_[A-Z]{2}/', $locale ) ) {
			$locale = 'en_US';
		}

		?>

	</select>

	<img src="https://www.paypalobjects.com/<?php echo $locale; ?>/i/btn/btn_donateCC_LG.gif" style="cursor:pointer;"
	     id="op-paypal-donation-submit"/>

</div>

<script type="text/html" id="op-template-paypal-donation-form">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="hidden"
	      id="op-paypal-donation-form">
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="hello@jannikportz.de">
		<input type="hidden" name="lc" value="<?php echo substr( $locale, 3 ); ?>">
		<input type="hidden" name="item_name" value="WP Opening Hours Plugin for WordPress">
		<input type="hidden" name="amount" value="10.00" id="op-paypal-input-amount">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
	</form>
</script>