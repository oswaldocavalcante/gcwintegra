(function ($) {

    const quote_id = document.querySelector('#gcw-quote-form').getAttribute('data-quote_id');

    // Remove quote item
    $('.gcw-button-remove').on('click', (event) => {
        const item_id = event.target.getAttribute('data-product_id');
        
        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_remove_quote_item',
                nonce: gcw_quote_ajax_object.nonce,
                quote_id: quote_id,
                item_id: item_id,
            },
            success: function (response) {
                document.getElementById('gcw-quote-row-item-' + item_id).remove();
            },
            error: function (error) {
                console.error('Error: ', error);
            }
        });
    });

    // Update quote shipping
    $('#gcw-update-shipping-button').on('click', function () {
        var shipping_postcode = $('#shipping_postcode').val();

        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_update_shipping',
                nonce: gcw_quote_ajax_object.nonce,
                quote_id: quote_id,
                shipping_postcode: shipping_postcode
            },
            success: function (response) {
                $('#gcw-quote-shipping-options').html(response);
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

})(jQuery);
