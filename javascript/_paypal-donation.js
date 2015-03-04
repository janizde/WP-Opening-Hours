( function ( $ ) {

    $.fn.opPaypalDonation = function () {

        var template    = $('#op-template-paypal-donation-form');
        var btn_submit  = $('#op-paypal-donation-submit');
        var form;
        var amount_s    = $('#op-paypal-select-amount');
        var amount_i;

        if ( template.length < 1 )
            return;

        function placeForm () {

            form    = $( template.html() );
            form.css('display', 'none');
            $('body').append( form );

            amount_i    = form.find( '#op-paypal-input-amount' );

        }

        placeForm();

        btn_submit.click( function (e) {
            e.preventDefault();

            form.submit();
        } );

        amount_s.change( function () {
            amount_i.val( amount_s.val() );
        } );

    };

    $( document).ready( function () {

        $( window ).opPaypalDonation();

    } );

} )( jQuery );