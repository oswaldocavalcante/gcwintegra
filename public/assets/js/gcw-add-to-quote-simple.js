(function ($) {
    $gcw_add_to_quote_button = document.querySelector('#gcw_add_to_quote_button');
    

    $(document).ready(function () {
        $gcw_add_to_quote_button.classList.remove('disabled');
    });

    $('#gcw_add_to_quote_button').on('click', function () {

        var product_id = $gcw_add_to_quote_button.getAttribute('product_id');
        var quantity = $('.input-text.qty').val();

        $.ajax({
            url: gcw_add_to_quote_simple.url,
            type: 'POST',
            data: {
                action:     'gcw_add_to_quote_simple',
                nonce:      gcw_add_to_quote_simple.nonce,
                product_id: product_id,
                quantity:   quantity,
            },
            success: function (response) {
                // console.log(response);
                location.href = response.data.redirect_url;
            },
            error: function (error) {
                console.error('Error: ', error);
            }
        });
    });
})(jQuery);