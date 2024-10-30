(function ($) {
    'use strict';

    $(document).ready(function () {

    });

    /*jQuery firing on checkout payment method option change*/
    $('form.checkout').on('change', 'input[name="payment_method"]', function () {
        $(document.body).trigger("update_checkout");
    });



})(jQuery);

// Other code using $ as an alias to the other library