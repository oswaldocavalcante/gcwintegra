(function ($) {
    $input_variation_id         = document.querySelector('input[name="variation_id"]');
    $gcw_add_to_quote_button    = document.querySelector('#gcw_add_to_quote_button');

    function disableButton() 
    {
        $gcw_add_to_quote_button.classList.add('disabled');
    };

    function enableButton()
    {
        $gcw_add_to_quote_button.classList.remove('disabled');
    }
    
    $($input_variation_id).change(function () 
    {
        $variation_id = $input_variation_id.getAttribute('value');

        if ($variation_id) {
            enableButton();
        } else {
            disableButton();
        }
    });

    $('#gcw_add_to_quote_button').on('click', function()
    {
        var variation_id = $input_variation_id.getAttribute('value');
        var product_id = document.querySelector('input[name="product_id"]').getAttribute('value');

        if(!$variation_id) {
            alert('Selecione uma variação para adicionar à cotação.');
        } else {
            var quantity = $('.input-text.qty').val();
            $.ajax({
                url: gcw_add_to_quote_variation.url,
                type: 'POST',
                data: {
                    action: 'gcw_add_to_quote_variation',
                    nonce: gcw_add_to_quote_variation.nonce,
                    parent_id: product_id,
                    variation_id: variation_id,
                    quantity: quantity,
                },
                success: function (response) {
                    // console.log(response);
                    location.href = response.data.redirect_url;
                },
                error: function (error) {
                    console.error('Error: ', error);
                }
            });
        }
    });
})(jQuery);